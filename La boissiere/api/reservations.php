<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            handleGetReservations($pdo);
            break;
            
        case 'POST':
            handleCreateReservation($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdateReservation($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeleteReservation($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API réservations: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetReservations($pdo) {
    $search = $_GET['search'] ?? '';
    $dateDebut = $_GET['date_debut'] ?? '';
    $dateFin = $_GET['date_fin'] ?? '';
    $chambre = $_GET['chambre'] ?? '';
    $statut = $_GET['statut'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT r.*, 
                   CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
                   c.email as client_email,
                   c.telephone as client_telephone,
                   ch.nom as chambre_nom
            FROM reservations r
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE 1=1";
    $params = [];
    
    // Recherche
    if (!empty($search)) {
        $sql .= " AND (c.nom LIKE :search OR c.prenom LIKE :search OR c.email LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    // Filtres par date
    if (!empty($dateDebut)) {
        $sql .= " AND r.date_arrivee >= :date_debut";
        $params['date_debut'] = $dateDebut;
    }
    
    if (!empty($dateFin)) {
        $sql .= " AND r.date_depart <= :date_fin";
        $params['date_fin'] = $dateFin;
    }
    
    // Filtre par chambre
    if (!empty($chambre)) {
        $sql .= " AND r.chambre_id = :chambre";
        $params['chambre'] = $chambre;
    }
    
    // Filtre par statut
    if (!empty($statut)) {
        $sql .= " AND r.statut = :statut";
        $params['statut'] = $statut;
    }
    
    $sql .= " ORDER BY r.date_arrivee DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $reservations = $stmt->fetchAll();
    
    // Compter le total
    $countSql = str_replace("SELECT r.*, CONCAT(c.nom, ' ', c.prenom) as client_nom_complet, c.email as client_email, c.telephone as client_telephone, ch.nom as chambre_nom", "SELECT COUNT(*)", $sql);
    $countSql = str_replace("LIMIT :limit OFFSET :offset", "", $countSql);
    
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue(":$key", $value);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    jsonSuccess([
        'reservations' => $reservations,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handleCreateReservation($pdo, $input) {
    // Validation
    $required = ['client_id', 'chambre_id', 'date_arrivee', 'date_depart', 'nombre_personnes'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    // Vérifier que les dates sont cohérentes
    if (strtotime($input['date_arrivee']) >= strtotime($input['date_depart'])) {
        jsonError('La date de départ doit être postérieure à la date d\'arrivée');
    }
    
    // Vérifier la disponibilité de la chambre
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations 
        WHERE chambre_id = ? 
        AND statut IN ('confirmee', 'en-attente')
        AND ((date_arrivee <= ? AND date_depart > ?) 
             OR (date_arrivee < ? AND date_depart >= ?))
    ");
    $checkStmt->execute([
        $input['chambre_id'],
        $input['date_arrivee'], $input['date_arrivee'],
        $input['date_depart'], $input['date_depart']
    ]);
    
    if ($checkStmt->fetchColumn() > 0) {
        jsonError('La chambre n\'est pas disponible pour ces dates');
    }
    
    // Récupérer le tarif de la chambre
    $chambreStmt = $pdo->prepare("SELECT tarif_base FROM chambres WHERE id = ?");
    $chambreStmt->execute([$input['chambre_id']]);
    $chambre = $chambreStmt->fetch();
    
    if (!$chambre) {
        jsonError('Chambre introuvable');
    }
    
    $data = [
        'client_id' => $input['client_id'],
        'chambre_id' => $input['chambre_id'],
        'date_arrivee' => $input['date_arrivee'],
        'date_depart' => $input['date_depart'],
        'heure_arrivee' => $input['heure_arrivee'] ?? '15:00:00',
        'heure_depart' => $input['heure_depart'] ?? '11:00:00',
        'nombre_personnes' => $input['nombre_personnes'],
        'tarif_nuitee' => $input['tarif_nuitee'] ?? $chambre['tarif_base'],
        'arrhes' => $input['arrhes'] ?? 0,
        'statut' => $input['statut'] ?? 'en-attente',
        'notes' => $input['notes'] ?? ''
    ];
    
    $sql = "INSERT INTO reservations (client_id, chambre_id, date_arrivee, date_depart, heure_arrivee, heure_depart, nombre_personnes, tarif_nuitee, arrhes, statut, notes) 
            VALUES (:client_id, :chambre_id, :date_arrivee, :date_depart, :heure_arrivee, :heure_depart, :nombre_personnes, :tarif_nuitee, :arrhes, :statut, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $reservationId = $pdo->lastInsertId();
    
    // Récupérer la réservation créée avec les détails
    $getStmt = $pdo->prepare("
        SELECT r.*, 
               CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
               ch.nom as chambre_nom
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE r.id = ?
    ");
    $getStmt->execute([$reservationId]);
    $reservation = $getStmt->fetch();
    
    jsonSuccess($reservation, 'Réservation créée avec succès');
}

function handleUpdateReservation($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID de la réservation manquant');
    }
    
    // Vérifier que la réservation existe
    $checkStmt = $pdo->prepare("SELECT id FROM reservations WHERE id = ?");
    $checkStmt->execute([$input['id']]);
    if (!$checkStmt->fetch()) {
        jsonError('Réservation introuvable', 404);
    }
    
    $data = [
        'id' => $input['id'],
        'statut' => $input['statut'] ?? 'en-attente',
        'arrhes' => $input['arrhes'] ?? 0,
        'notes' => $input['notes'] ?? ''
    ];
    
    // Mise à jour des champs modifiables
    if (isset($input['heure_arrivee'])) $data['heure_arrivee'] = $input['heure_arrivee'];
    if (isset($input['heure_depart'])) $data['heure_depart'] = $input['heure_depart'];
    if (isset($input['nombre_personnes'])) $data['nombre_personnes'] = $input['nombre_personnes'];
    if (isset($input['tarif_nuitee'])) $data['tarif_nuitee'] = $input['tarif_nuitee'];
    
    $sql = "UPDATE reservations SET statut = :statut, arrhes = :arrhes, notes = :notes";
    
    if (isset($data['heure_arrivee'])) $sql .= ", heure_arrivee = :heure_arrivee";
    if (isset($data['heure_depart'])) $sql .= ", heure_depart = :heure_depart";
    if (isset($data['nombre_personnes'])) $sql .= ", nombre_personnes = :nombre_personnes";
    if (isset($data['tarif_nuitee'])) $sql .= ", tarif_nuitee = :tarif_nuitee";
    
    $sql .= " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    jsonSuccess([], 'Réservation mise à jour avec succès');
}

function handleDeleteReservation($pdo) {
    $reservationId = $_GET['id'] ?? null;
    
    if (empty($reservationId)) {
        jsonError('ID de la réservation manquant');
    }
    
    // Vérifier que la réservation existe
    $checkStmt = $pdo->prepare("SELECT statut FROM reservations WHERE id = ?");
    $checkStmt->execute([$reservationId]);
    $reservation = $checkStmt->fetch();
    
    if (!$reservation) {
        jsonError('Réservation introuvable', 404);
    }
    
    // Ne pas supprimer les réservations confirmées ou terminées, les annuler
    if (in_array($reservation['statut'], ['confirmee', 'terminee'])) {
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?");
        $stmt->execute([$reservationId]);
        jsonSuccess([], 'Réservation annulée');
    } else {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$reservationId]);
        jsonSuccess([], 'Réservation supprimée avec succès');
    }
}
?>
