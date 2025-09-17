<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            handleGetVentes($pdo);
            break;
            
        case 'POST':
            handleCreateVente($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdateVente($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeleteVente($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API ventes: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetVentes($pdo) {
    $reservationId = $_GET['reservation_id'] ?? '';
    $dateDebut = $_GET['date_debut'] ?? '';
    $dateFin = $_GET['date_fin'] ?? '';
    $produit = $_GET['produit'] ?? '';
    
    $sql = "SELECT v.*, 
                   p.nom as produit_nom,
                   p.unite as produit_unite,
                   cat.nom as categorie_nom,
                   r.date_arrivee,
                   r.date_depart,
                   CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
                   ch.nom as chambre_nom
            FROM ventes v
            JOIN produits p ON v.produit_id = p.id
            JOIN categories_produits cat ON p.categorie_id = cat.id
            JOIN reservations r ON v.reservation_id = r.id
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE 1=1";
    $params = [];
    
    if (!empty($reservationId)) {
        $sql .= " AND v.reservation_id = :reservation_id";
        $params['reservation_id'] = $reservationId;
    }
    
    if (!empty($dateDebut)) {
        $sql .= " AND v.date_vente >= :date_debut";
        $params['date_debut'] = $dateDebut;
    }
    
    if (!empty($dateFin)) {
        $sql .= " AND v.date_vente <= :date_fin";
        $params['date_fin'] = $dateFin;
    }
    
    if (!empty($produit)) {
        $sql .= " AND v.produit_id = :produit";
        $params['produit'] = $produit;
    }
    
    $sql .= " ORDER BY v.date_vente DESC";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    
    $ventes = $stmt->fetchAll();
    
    jsonSuccess($ventes);
}

function handleCreateVente($pdo, $input) {
    $required = ['reservation_id', 'produit_id', 'quantite'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    // Vérifier que la réservation existe
    $reservationStmt = $pdo->prepare("SELECT id FROM reservations WHERE id = ?");
    $reservationStmt->execute([$input['reservation_id']]);
    if (!$reservationStmt->fetch()) {
        jsonError('Réservation introuvable');
    }
    
    // Récupérer le prix du produit
    $produitStmt = $pdo->prepare("SELECT prix_unitaire FROM produits WHERE id = ? AND actif = 1");
    $produitStmt->execute([$input['produit_id']]);
    $produit = $produitStmt->fetch();
    
    if (!$produit) {
        jsonError('Produit introuvable ou inactif');
    }
    
    $prixUnitaire = $input['prix_unitaire'] ?? $produit['prix_unitaire'];
    $quantite = $input['quantite'];
    $montantTotal = $quantite * $prixUnitaire;
    
    $data = [
        'reservation_id' => $input['reservation_id'],
        'produit_id' => $input['produit_id'],
        'quantite' => $quantite,
        'prix_unitaire' => $prixUnitaire,
        'montant_total' => $montantTotal,
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "INSERT INTO ventes (reservation_id, produit_id, quantite, prix_unitaire, montant_total, notes) 
            VALUES (:reservation_id, :produit_id, :quantite, :prix_unitaire, :montant_total, :notes)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $venteId = $pdo->lastInsertId();
    
    // Récupérer la vente créée avec les détails
    $getStmt = $pdo->prepare("
        SELECT v.*, 
               p.nom as produit_nom,
               p.unite as produit_unite
        FROM ventes v
        JOIN produits p ON v.produit_id = p.id
        WHERE v.id = ?
    ");
    $getStmt->execute([$venteId]);
    $vente = $getStmt->fetch();
    
    jsonSuccess($vente, 'Vente enregistrée avec succès');
}

function handleUpdateVente($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID de la vente manquant');
    }
    
    $quantite = $input['quantite'];
    $prixUnitaire = $input['prix_unitaire'];
    $montantTotal = $quantite * $prixUnitaire;
    
    $data = [
        'id' => $input['id'],
        'quantite' => $quantite,
        'prix_unitaire' => $prixUnitaire,
        'montant_total' => $montantTotal,
        'notes' => cleanInput($input['notes'] ?? '')
    ];
    
    $sql = "UPDATE ventes SET 
            quantite = :quantite, prix_unitaire = :prix_unitaire, 
            montant_total = :montant_total, notes = :notes
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    jsonSuccess([], 'Vente mise à jour avec succès');
}

function handleDeleteVente($pdo) {
    $venteId = $_GET['id'] ?? null;
    
    if (empty($venteId)) {
        jsonError('ID de la vente manquant');
    }
    
    $stmt = $pdo->prepare("DELETE FROM ventes WHERE id = ?");
    $stmt->execute([$venteId]);
    
    jsonSuccess([], 'Vente supprimée avec succès');
}
?>
