<?php
require_once 'config.php';

setJSONHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['categories'])) {
                handleGetCategories($pdo);
            } else {
                handleGetProduits($pdo);
            }
            break;
            
        case 'POST':
            handleCreateProduit($pdo, $input);
            break;
            
        case 'PUT':
            handleUpdateProduit($pdo, $input);
            break;
            
        case 'DELETE':
            handleDeleteProduit($pdo);
            break;
            
        default:
            jsonError('Méthode non autorisée', 405);
    }
    
} catch (Exception $e) {
    error_log("Erreur API produits: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleGetCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM categories_produits WHERE actif = 1 ORDER BY nom");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    jsonSuccess($categories);
}

function handleGetProduits($pdo) {
    $search = $_GET['search'] ?? '';
    $categorie = $_GET['categorie'] ?? '';
    $actif = $_GET['actif'] ?? '';
    
    $sql = "SELECT p.*, c.nom as categorie_nom 
            FROM produits p 
            JOIN categories_produits c ON p.categorie_id = c.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND p.nom LIKE :search";
        $params['search'] = "%$search%";
    }
    
    if (!empty($categorie)) {
        $sql .= " AND p.categorie_id = :categorie";
        $params['categorie'] = $categorie;
    }
    
    if ($actif !== '') {
        $sql .= " AND p.actif = :actif";
        $params['actif'] = $actif;
    }
    
    $sql .= " ORDER BY c.nom, p.nom";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    
    $produits = $stmt->fetchAll();
    
    jsonSuccess($produits);
}

function handleCreateProduit($pdo, $input) {
    $required = ['nom', 'categorie_id', 'prix_unitaire'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonError("Le champ '$field' est obligatoire");
        }
    }
    
    $data = [
        'nom' => cleanInput($input['nom']),
        'categorie_id' => $input['categorie_id'],
        'description' => cleanInput($input['description'] ?? ''),
        'prix_unitaire' => $input['prix_unitaire'],
        'unite' => cleanInput($input['unite'] ?? 'unité'),
        'stock_actuel' => $input['stock_actuel'] ?? 0,
        'stock_minimum' => $input['stock_minimum'] ?? 0
    ];
    
    $sql = "INSERT INTO produits (nom, categorie_id, description, prix_unitaire, unite, stock_actuel, stock_minimum) 
            VALUES (:nom, :categorie_id, :description, :prix_unitaire, :unite, :stock_actuel, :stock_minimum)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    $produitId = $pdo->lastInsertId();
    
    // Récupérer le produit créé
    $getStmt = $pdo->prepare("
        SELECT p.*, c.nom as categorie_nom 
        FROM produits p 
        JOIN categories_produits c ON p.categorie_id = c.id 
        WHERE p.id = ?
    ");
    $getStmt->execute([$produitId]);
    $produit = $getStmt->fetch();
    
    jsonSuccess($produit, 'Produit créé avec succès');
}

function handleUpdateProduit($pdo, $input) {
    if (empty($input['id'])) {
        jsonError('ID du produit manquant');
    }
    
    $data = [
        'id' => $input['id'],
        'nom' => cleanInput($input['nom']),
        'description' => cleanInput($input['description'] ?? ''),
        'prix_unitaire' => $input['prix_unitaire'],
        'unite' => cleanInput($input['unite'] ?? 'unité'),
        'stock_actuel' => $input['stock_actuel'] ?? 0,
        'stock_minimum' => $input['stock_minimum'] ?? 0,
        'actif' => isset($input['actif']) ? (bool)$input['actif'] : true
    ];
    
    $sql = "UPDATE produits SET 
            nom = :nom, description = :description, prix_unitaire = :prix_unitaire,
            unite = :unite, stock_actuel = :stock_actuel, stock_minimum = :stock_minimum, actif = :actif
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    jsonSuccess([], 'Produit mis à jour avec succès');
}

function handleDeleteProduit($pdo) {
    $produitId = $_GET['id'] ?? null;
    
    if (empty($produitId)) {
        jsonError('ID du produit manquant');
    }
    
    // Vérifier s'il y a des ventes liées
    $ventesStmt = $pdo->prepare("SELECT COUNT(*) FROM ventes WHERE produit_id = ?");
    $ventesStmt->execute([$produitId]);
    $ventesCount = $ventesStmt->fetchColumn();
    
    if ($ventesCount > 0) {
        // Désactiver au lieu de supprimer
        $stmt = $pdo->prepare("UPDATE produits SET actif = 0 WHERE id = ?");
        $stmt->execute([$produitId]);
        jsonSuccess([], 'Produit désactivé (des ventes existent)');
    } else {
        $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->execute([$produitId]);
        jsonSuccess([], 'Produit supprimé avec succès');
    }
}
?>
