<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['generate'])) {
                handleGenerateFacture($pdo);
            } else {
                handleGetFactures($pdo);
            }
            break;
            
        case 'POST':
            handleCreateFacture($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdateFacture($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeleteFacture($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API factures: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetFactures($pdo) {
    $search = $_GET['search'] ?? '';
    $statut = $_GET['statut'] ?? '';
    $dateDebut = $_GET['date_debut'] ?? '';
    $dateFin = $_GET['date_fin'] ?? '';
    
    $sql = "SELECT f.*, 
                   CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
                   c.email as client_email
            FROM factures f
            JOIN clients c ON f.client_id = c.id
            WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (f.numero_facture LIKE :search OR c.nom LIKE :search OR c.prenom LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    if (!empty($statut)) {
        $sql .= " AND f.statut = :statut";
        $params['statut'] = $statut;
    }
    
    if (!empty($dateDebut)) {
        $sql .= " AND f.date_facture >= :date_debut";
        $params['date_debut'] = $dateDebut;
    }
    
    if (!empty($dateFin)) {
        $sql .= " AND f.date_facture <= :date_fin";
        $params['date_fin'] = $dateFin;
    }
    
    $sql .= " ORDER BY f.date_facture DESC";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    
    $factures = $stmt->fetchAll();
    
    jsonSuccess($factures);
}

function handleGenerateFacture($pdo) {
    $reservationId = $_GET['reservation_id'] ?? null;
    
    if (empty($reservationId)) {
        jsonError('ID de réservation manquant');
    }
    
    // Récupérer les détails de la réservation
    $reservationStmt = $pdo->prepare("
        SELECT r.*, 
               CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
               c.email, c.adresse, c.code_postal, c.ville,
               ch.nom as chambre_nom
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE r.id = ?
    ");
    $reservationStmt->execute([$reservationId]);
    $reservation = $reservationStmt->fetch();
    
    if (!$reservation) {
        jsonError('Réservation introuvable');
    }
    
    // Vérifier si une facture existe déjà
    $existingStmt = $pdo->prepare("SELECT id FROM factures WHERE reservation_id = ?");
    $existingStmt->execute([$reservationId]);
    if ($existingStmt->fetch()) {
        jsonError('Une facture existe déjà pour cette réservation');
    }
    
    // Créer la facture
    $numeroFacture = genererNumeroFacture();
    $dateFacture = date('Y-m-d');
    $dateEcheance = date('Y-m-d', strtotime('+30 days'));
    
    $factureData = [
        'numero_facture' => $numeroFacture,
        'client_id' => $reservation['client_id'],
        'reservation_id' => $reservationId,
        'date_facture' => $dateFacture,
        'date_echeance' => $dateEcheance,
        'taux_tva' => 0,
        'statut' => 'brouillon'
    ];
    
    $factureStmt = $pdo->prepare("
        INSERT INTO factures (numero_facture, client_id, reservation_id, date_facture, date_echeance, taux_tva, statut) 
        VALUES (:numero_facture, :client_id, :reservation_id, :date_facture, :date_echeance, :taux_tva, :statut)
    ");
    $factureStmt->execute($factureData);
    
    $factureId = $pdo->lastInsertId();
    
    // Ajouter la ligne hébergement
    $ligneHebergement = [
        'facture_id' => $factureId,
        'type_ligne' => 'hebergement',
        'reference_id' => $reservationId,
        'description' => "Hébergement chambre {$reservation['chambre_nom']} du " . formatDate($reservation['date_arrivee']) . " au " . formatDate($reservation['date_depart']) . " ({$reservation['nombre_nuitees']} nuitées)",
        'quantite' => $reservation['nombre_nuitees'],
        'prix_unitaire' => $reservation['tarif_nuitee'],
        'montant_total' => $reservation['montant_hebergement']
    ];
    
    $ligneStmt = $pdo->prepare("
        INSERT INTO lignes_facture (facture_id, type_ligne, reference_id, description, quantite, prix_unitaire, montant_total) 
        VALUES (:facture_id, :type_ligne, :reference_id, :description, :quantite, :prix_unitaire, :montant_total)
    ");
    $ligneStmt->execute($ligneHebergement);
    
    // Ajouter les ventes d'accessoires
    $ventesStmt = $pdo->prepare("
        SELECT v.*, p.nom as produit_nom, p.unite
        FROM ventes v
        JOIN produits p ON v.produit_id = p.id
        WHERE v.reservation_id = ?
    ");
    $ventesStmt->execute([$reservationId]);
    $ventes = $ventesStmt->fetchAll();
    
    foreach ($ventes as $vente) {
        $ligneVente = [
            'facture_id' => $factureId,
            'type_ligne' => 'produit',
            'reference_id' => $vente['id'],
            'description' => $vente['produit_nom'] . ($vente['notes'] ? ' - ' . $vente['notes'] : ''),
            'quantite' => $vente['quantite'],
            'prix_unitaire' => $vente['prix_unitaire'],
            'montant_total' => $vente['montant_total']
        ];
        
        $ligneStmt->execute($ligneVente);
    }
    
    // Récupérer la facture complète
    $factureCompleteStmt = $pdo->prepare("
        SELECT f.*, 
               CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
               c.email, c.adresse, c.code_postal, c.ville
        FROM factures f
        JOIN clients c ON f.client_id = c.id
        WHERE f.id = ?
    ");
    $factureCompleteStmt->execute([$factureId]);
    $factureComplete = $factureCompleteStmt->fetch();
    
    // Récupérer les lignes
    $lignesStmt = $pdo->prepare("SELECT * FROM lignes_facture WHERE facture_id = ? ORDER BY id");
    $lignesStmt->execute([$factureId]);
    $lignes = $lignesStmt->fetchAll();
    
    $factureComplete['lignes'] = $lignes;
    
    jsonSuccess($factureComplete, 'Facture générée avec succès');
}

function handleCreateFacture($pdo, $input) {
    $required = ['client_id', 'date_facture'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    $numeroFacture = genererNumeroFacture();
    
    $data = [
        'numero_facture' => $numeroFacture,
        'client_id' => $input['client_id'],
        'reservation_id' => $input['reservation_id'] ?? null,
        'date_facture' => $input['date_facture'],
        'date_echeance' => $input['date_echeance'] ?? date('Y-m-d', strtotime($input['date_facture'] . ' +30 days')),
        'taux_tva' => $input['taux_tva'] ?? 0,
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "INSERT INTO factures (numero_facture, client_id, reservation_id, date_facture, date_echeance, taux_tva, notes) 
            VALUES (:numero_facture, :client_id, :reservation_id, :date_facture, :date_echeance, :taux_tva, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $factureId = $pdo->lastInsertId();
    
    jsonSuccess(['id' => $factureId, 'numero_facture' => $numeroFacture], 'Facture créée avec succès');
}

function handleUpdateFacture($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID de la facture manquant');
    }
    
    $data = [
        'id' => $input['id'],
        'statut' => $input['statut'] ?? 'brouillon',
        'date_echeance' => $input['date_echeance'],
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "UPDATE factures SET statut = :statut, date_echeance = :date_echeance, notes = :notes WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    jsonSuccess([], 'Facture mise à jour avec succès');
}

function handleDeleteFacture($pdo) {
    $factureId = $_GET['id'] ?? null;
    
    if (empty($factureId)) {
        jsonError('ID de la facture manquant');
    }
    
    // Vérifier le statut
    $checkStmt = $pdo->prepare("SELECT statut FROM factures WHERE id = ?");
    $checkStmt->execute([$factureId]);
    $facture = $checkStmt->fetch();
    
    if (!$facture) {
        jsonError('Facture introuvable', 404);
    }
    
    if ($facture['statut'] === 'payee') {
        jsonError('Impossible de supprimer une facture payée');
    }
    
    $stmt = $pdo->prepare("DELETE FROM factures WHERE id = ?");
    $stmt->execute([$factureId]);
    
    jsonSuccess([], 'Facture supprimée avec succès');
}
?>
