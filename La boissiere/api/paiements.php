<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            handleGetPaiements($pdo);
            break;
            
        case 'POST':
            handleCreatePaiement($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdatePaiement($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeletePaiement($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API paiements: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetPaiements($pdo) {
    $factureId = $_GET['facture_id'] ?? '';
    $reservationId = $_GET['reservation_id'] ?? '';
    $dateDebut = $_GET['date_debut'] ?? '';
    $dateFin = $_GET['date_fin'] ?? '';
    $moyenPaiement = $_GET['moyen_paiement'] ?? '';
    
    $sql = "SELECT p.*, 
                   f.numero_facture,
                   CONCAT(c.nom, ' ', c.prenom) as client_nom_complet
            FROM paiements p
            LEFT JOIN factures f ON p.facture_id = f.id
            LEFT JOIN reservations r ON p.reservation_id = r.id
            LEFT JOIN clients c ON (f.client_id = c.id OR r.client_id = c.id)
            WHERE 1=1";
    $params = [];
    
    if (!empty($factureId)) {
        $sql .= " AND p.facture_id = :facture_id";
        $params['facture_id'] = $factureId;
    }
    
    if (!empty($reservationId)) {
        $sql .= " AND p.reservation_id = :reservation_id";
        $params['reservation_id'] = $reservationId;
    }
    
    if (!empty($dateDebut)) {
        $sql .= " AND p.date_paiement >= :date_debut";
        $params['date_debut'] = $dateDebut;
    }
    
    if (!empty($dateFin)) {
        $sql .= " AND p.date_paiement <= :date_fin";
        $params['date_fin'] = $dateFin;
    }
    
    if (!empty($moyenPaiement)) {
        $sql .= " AND p.moyen_paiement = :moyen_paiement";
        $params['moyen_paiement'] = $moyenPaiement;
    }
    
    $sql .= " ORDER BY p.date_paiement DESC";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    
    $paiements = $stmt->fetchAll();
    
    jsonSuccess($paiements);
}

function handleCreatePaiement($pdo, $input) {
    $required = ['montant', 'moyen_paiement', 'date_paiement'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    if (empty($input['facture_id']) && empty($input['reservation_id'])) {
        jsonError('Une facture ou une réservation doit être spécifiée');
    }
    
    $data = [
        'facture_id' => $input['facture_id'] ?? null,
        'reservation_id' => $input['reservation_id'] ?? null,
        'montant' => $input['montant'],
        'moyen_paiement' => $input['moyen_paiement'],
        'reference_paiement' => cleanInput($input['reference_paiement'] ?? ''),
        'date_paiement' => $input['date_paiement'],
        'date_encaissement' => $input['date_encaissement'] ?? $input['date_paiement'],
        'statut' => $input['statut'] ?? 'encaisse',
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "INSERT INTO paiements (facture_id, reservation_id, montant, moyen_paiement, reference_paiement, date_paiement, date_encaissement, statut, notes) 
            VALUES (:facture_id, :reservation_id, :montant, :moyen_paiement, :reference_paiement, :date_paiement, :date_encaissement, :statut, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $paiementId = $pdo->lastInsertId();
    
    // Mettre à jour le statut de la facture si applicable
    if (!empty($input['facture_id'])) {
        updateFactureStatut($pdo, $input['facture_id']);
    }
    
    jsonSuccess(['id' => $paiementId], 'Paiement enregistré avec succès');
}

function handleUpdatePaiement($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID du paiement manquant');
    }
    
    $data = [
        'id' => $input['id'],
        'montant' => $input['montant'],
        'moyen_paiement' => $input['moyen_paiement'],
        'reference_paiement' => cleanInput($input['reference_paiement'] ?? ''),
        'date_paiement' => $input['date_paiement'],
        'date_encaissement' => $input['date_encaissement'],
        'statut' => $input['statut'] ?? 'encaisse',
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "UPDATE paiements SET 
            montant = :montant, moyen_paiement = :moyen_paiement, reference_paiement = :reference_paiement,
            date_paiement = :date_paiement, date_encaissement = :date_encaissement, statut = :statut, notes = :notes
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    jsonSuccess([], 'Paiement mis à jour avec succès');
}

function handleDeletePaiement($pdo) {
    $paiementId = $_GET['id'] ?? null;
    
    if (empty($paiementId)) {
        jsonError('ID du paiement manquant');
    }
    
    // Récupérer l'ID de la facture avant suppression
    $factureStmt = $pdo->prepare("SELECT facture_id FROM paiements WHERE id = ?");
    $factureStmt->execute([$paiementId]);
    $paiement = $factureStmt->fetch();
    
    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = ?");
    $stmt->execute([$paiementId]);
    
    // Mettre à jour le statut de la facture si applicable
    if ($paiement && $paiement['facture_id']) {
        updateFactureStatut($pdo, $paiement['facture_id']);
    }
    
    jsonSuccess([], 'Paiement supprimé avec succès');
}

function updateFactureStatut($pdo, $factureId) {
    // Calculer le total des paiements pour cette facture
    $paiementsStmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) as total_paiements 
        FROM paiements 
        WHERE facture_id = ? AND statut = 'encaisse'
    ");
    $paiementsStmt->execute([$factureId]);
    $totalPaiements = $paiementsStmt->fetchColumn();
    
    // Récupérer le montant de la facture
    $factureStmt = $pdo->prepare("SELECT montant_ttc FROM factures WHERE id = ?");
    $factureStmt->execute([$factureId]);
    $montantFacture = $factureStmt->fetchColumn();
    
    // Déterminer le nouveau statut
    $nouveauStatut = 'envoyee';
    if ($totalPaiements >= $montantFacture) {
        $nouveauStatut = 'payee';
    }
    
    // Mettre à jour le statut
    $updateStmt = $pdo->prepare("UPDATE factures SET statut = ? WHERE id = ?");
    $updateStmt->execute([$nouveauStatut, $factureId]);
}
?>
