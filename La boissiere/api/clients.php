<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            handleGetClients($pdo);
            break;
            
        case 'POST':
            handleCreateClient($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdateClient($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeleteClient($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API clients: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetClients($pdo) {
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT * FROM clients WHERE 1=1";
    $params = [];
    
    // Recherche
    if (!empty($search)) {
        $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search OR ville LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    // Filtre
    if ($filter === 'active') {
        $sql .= " AND actif = 1";
    } elseif ($filter === 'inactive') {
        $sql .= " AND actif = 0";
    }
    
    // Tri
    $sql .= " ORDER BY nom, prenom";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $clients = $stmt->fetchAll();
    
    // Compter le total pour la pagination
    $countSql = "SELECT COUNT(*) FROM clients WHERE 1=1";
    if (!empty($search)) {
        $countSql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search OR ville LIKE :search)";
    }
    if ($filter === 'active') {
        $countSql .= " AND actif = 1";
    } elseif ($filter === 'inactive') {
        $countSql .= " AND actif = 0";
    }
    
    $countStmt = $pdo->prepare($countSql);
    if (!empty($search)) {
        $countStmt->bindValue(':search', "%$search%");
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Ajouter les statistiques pour chaque client
    foreach ($clients as &$client) {
        // Dernière réservation
        $lastReservationStmt = $pdo->prepare("
            SELECT MAX(date_arrivee) as derniere_visite 
            FROM reservations 
            WHERE client_id = ? AND statut IN ('confirmee', 'terminee')
        ");
        $lastReservationStmt->execute([$client['id']]);
        $lastReservation = $lastReservationStmt->fetch();
        $client['derniere_visite'] = $lastReservation['derniere_visite'];
        
        // Nombre total de réservations
        $countReservationsStmt = $pdo->prepare("
            SELECT COUNT(*) as total_reservations 
            FROM reservations 
            WHERE client_id = ?
        ");
        $countReservationsStmt->execute([$client['id']]);
        $client['total_reservations'] = $countReservationsStmt->fetchColumn();
        
        // Chiffre d'affaires total
        $totalCAStmt = $pdo->prepare("
            SELECT SUM(montant_total) as chiffre_affaires 
            FROM reservations 
            WHERE client_id = ? AND statut IN ('confirmee', 'terminee')
        ");
        $totalCAStmt->execute([$client['id']]);
        $client['chiffre_affaires'] = $totalCAStmt->fetchColumn() ?: 0;
    }
    
    jsonSuccess([
        'clients' => $clients,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handleCreateClient($pdo, $input) {
    // Validation
    $required = ['nom', 'prenom', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    if (!validerEmail($input['email'])) {
        jsonError('Adresse email invalide');
    }
    
    // Vérifier si l'email existe déjà
    $checkStmt = $pdo->prepare("SELECT id FROM clients WHERE email = ?");
    $checkStmt->execute([$input['email']]);
    if ($checkStmt->fetch()) {
        jsonError('Un client avec cette adresse email existe déjà');
    }
    
    // Nettoyer les données
    $data = [
        'nom' => cleanInput($input['nom']),
        'prenom' => cleanInput($input['prenom']),
        'email' => cleanInput($input['email']),
        'telephone' => cleanInput($input['telephone'] ?? ''),
        'adresse' => cleanInput($input['adresse'] ?? ''),
        'code_postal' => cleanInput($input['code_postal'] ?? ''),
        'ville' => cleanInput($input['ville'] ?? ''),
        'pays' => cleanInput($input['pays'] ?? 'France'),
        'origine_prospect' => $input['origine_prospect'] ?? 'autre',
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "INSERT INTO clients (nom, prenom, email, telephone, adresse, code_postal, ville, pays, origine_prospect, notes) 
            VALUES (:nom, :prenom, :email, :telephone, :adresse, :code_postal, :ville, :pays, :origine_prospect, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $clientId = $pdo->lastInsertId();
    
    // Récupérer le client créé
    $getStmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $getStmt->execute([$clientId]);
    $client = $getStmt->fetch();
    
    jsonSuccess($client, 'Client créé avec succès');
}

function handleUpdateClient($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID du client manquant');
    }
    
    // Vérifier que le client existe
    $checkStmt = $pdo->prepare("SELECT id FROM clients WHERE id = ?");
    $checkStmt->execute([$input['id']]);
    if (!$checkStmt->fetch()) {
        jsonError('Client introuvable', 404);
    }
    
    // Validation
    $required = ['nom', 'prenom', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    if (!validerEmail($input['email'])) {
        jsonError('Adresse email invalide');
    }
    
    // Vérifier si l'email existe déjà (sauf pour ce client)
    $checkEmailStmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
    $checkEmailStmt->execute([$input['email'], $input['id']]);
    if ($checkEmailStmt->fetch()) {
        jsonError('Un autre client avec cette adresse email existe déjà');
    }
    
    // Nettoyer les données
    $data = [
        'id' => $input['id'],
        'nom' => cleanInput($input['nom']),
        'prenom' => cleanInput($input['prenom']),
        'email' => cleanInput($input['email']),
        'telephone' => cleanInput($input['telephone'] ?? ''),
        'adresse' => cleanInput($input['adresse'] ?? ''),
        'code_postal' => cleanInput($input['code_postal'] ?? ''),
        'ville' => cleanInput($input['ville'] ?? ''),
        'pays' => cleanInput($input['pays'] ?? 'France'),
        'origine_prospect' => $input['origine_prospect'] ?? 'autre',
        'notes' => cleanInput($input['notes'] ?? ''),
        'actif' => isset($input['actif']) ? (bool)$input['actif'] : true
    ];
    
    $sql = "UPDATE clients SET 
            nom = :nom, prenom = :prenom, email = :email, telephone = :telephone,
            adresse = :adresse, code_postal = :code_postal, ville = :ville, pays = :pays,
            origine_prospect = :origine_prospect, notes = :notes, actif = :actif
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    // Récupérer le client mis à jour
    $getStmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $getStmt->execute([$input['id']]);
    $client = $getStmt->fetch();
    
    jsonSuccess($client, 'Client mis à jour avec succès');
}

function handleDeleteClient($pdo) {
    $clientId = $_GET['id'] ?? null;
    
    if (empty($clientId)) {
        jsonError('ID du client manquant');
    }
    
    // Vérifier que le client existe
    $checkStmt = $pdo->prepare("SELECT id FROM clients WHERE id = ?");
    $checkStmt->execute([$clientId]);
    if (!$checkStmt->fetch()) {
        jsonError('Client introuvable', 404);
    }
    
    // Vérifier s'il y a des réservations liées
    $reservationsStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE client_id = ?");
    $reservationsStmt->execute([$clientId]);
    $reservationsCount = $reservationsStmt->fetchColumn();
    
    if ($reservationsCount > 0) {
        // Ne pas supprimer, juste désactiver
        $stmt = $pdo->prepare("UPDATE clients SET actif = 0 WHERE id = ?");
        $stmt->execute([$clientId]);
        jsonSuccess([], 'Client désactivé (des réservations existent)');
    } else {
        // Supprimer définitivement
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$clientId]);
        jsonSuccess([], 'Client supprimé avec succès');
    }
}
?>
