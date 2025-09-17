<?php
require_once 'config.php';

setJSONHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Méthode non autorisée', 405);
}

try {
    $pdo = getDBConnection();
    
    $type = $_GET['type'] ?? 'synthese';
    
    switch ($type) {
        case 'synthese':
            handleRapportSynthese($pdo);
            break;
        case 'ventes':
            handleRapportVentes($pdo);
            break;
        case 'occupation':
            handleRapportOccupation($pdo);
            break;
        case 'clients':
            handleRapportClients($pdo);
            break;
        case 'financier':
            handleRapportFinancier($pdo);
            break;
        default:
            jsonError('Type de rapport non reconnu');
    }
    
} catch (Exception $e) {
    error_log("Erreur API rapports: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function handleRapportSynthese($pdo) {
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-01');
    $dateFin = $_GET['date_fin'] ?? date('Y-m-t');
    
    // Réservations
    $reservationsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reservations,
            SUM(CASE WHEN statut = 'confirmee' THEN 1 ELSE 0 END) as reservations_confirmees,
            SUM(CASE WHEN statut = 'en-attente' THEN 1 ELSE 0 END) as reservations_attente,
            SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as reservations_annulees,
            SUM(nombre_nuitees) as total_nuitees,
            AVG(montant_total) as panier_moyen
        FROM reservations 
        WHERE date_arrivee BETWEEN ? AND ?
    ");
    $reservationsStmt->execute([$dateDebut, $dateFin]);
    $reservations = $reservationsStmt->fetch();
    
    // Chiffre d'affaires
    $caStmt = $pdo->prepare("
        SELECT 
            SUM(montant_hebergement) as ca_hebergement,
            SUM(montant_total - montant_hebergement) as ca_accessoires,
            SUM(montant_total) as ca_total
        FROM reservations 
        WHERE date_arrivee BETWEEN ? AND ? AND statut IN ('confirmee', 'terminee')
    ");
    $caStmt->execute([$dateDebut, $dateFin]);
    $chiffreAffaires = $caStmt->fetch();
    
    // Top chambres
    $chambresStmt = $pdo->prepare("
        SELECT 
            ch.nom,
            COUNT(*) as nb_reservations,
            SUM(r.montant_total) as ca_chambre
        FROM reservations r
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE r.date_arrivee BETWEEN ? AND ? AND r.statut IN ('confirmee', 'terminee')
        GROUP BY ch.id, ch.nom
        ORDER BY ca_chambre DESC
    ");
    $chambresStmt->execute([$dateDebut, $dateFin]);
    $topChambres = $chambresStmt->fetchAll();
    
    // Top produits
    $produitsStmt = $pdo->prepare("
        SELECT 
            p.nom,
            SUM(v.quantite) as quantite_vendue,
            SUM(v.montant_total) as ca_produit
        FROM ventes v
        JOIN produits p ON v.produit_id = p.id
        JOIN reservations r ON v.reservation_id = r.id
        WHERE r.date_arrivee BETWEEN ? AND ?
        GROUP BY p.id, p.nom
        ORDER BY ca_produit DESC
        LIMIT 10
    ");
    $produitsStmt->execute([$dateDebut, $dateFin]);
    $topProduits = $produitsStmt->fetchAll();
    
    jsonSuccess([
        'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
        'reservations' => $reservations,
        'chiffre_affaires' => $chiffreAffaires,
        'top_chambres' => $topChambres,
        'top_produits' => $topProduits
    ]);
}

function handleRapportVentes($pdo) {
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-01');
    $dateFin = $_GET['date_fin'] ?? date('Y-m-t');
    $categorie = $_GET['categorie'] ?? '';
    
    $sql = "SELECT 
                v.date_vente,
                p.nom as produit_nom,
                cat.nom as categorie_nom,
                v.quantite,
                v.prix_unitaire,
                v.montant_total,
                CONCAT(c.nom, ' ', c.prenom) as client_nom,
                ch.nom as chambre_nom
            FROM ventes v
            JOIN produits p ON v.produit_id = p.id
            JOIN categories_produits cat ON p.categorie_id = cat.id
            JOIN reservations r ON v.reservation_id = r.id
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE v.date_vente BETWEEN ? AND ?";
    
    $params = [$dateDebut, $dateFin];
    
    if (!empty($categorie)) {
        $sql .= " AND cat.id = ?";
        $params[] = $categorie;
    }
    
    $sql .= " ORDER BY v.date_vente DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventes = $stmt->fetchAll();
    
    // Synthèse par catégorie
    $syntheseStmt = $pdo->prepare("
        SELECT 
            cat.nom as categorie,
            COUNT(*) as nb_ventes,
            SUM(v.quantite) as quantite_totale,
            SUM(v.montant_total) as ca_categorie
        FROM ventes v
        JOIN produits p ON v.produit_id = p.id
        JOIN categories_produits cat ON p.categorie_id = cat.id
        WHERE v.date_vente BETWEEN ? AND ?
        GROUP BY cat.id, cat.nom
        ORDER BY ca_categorie DESC
    ");
    $syntheseStmt->execute([$dateDebut, $dateFin]);
    $synthese = $syntheseStmt->fetchAll();
    
    jsonSuccess([
        'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
        'ventes' => $ventes,
        'synthese_categories' => $synthese
    ]);
}

function handleRapportOccupation($pdo) {
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-01');
    $dateFin = $_GET['date_fin'] ?? date('Y-m-t');
    
    // Taux d'occupation par chambre
    $occupationStmt = $pdo->prepare("
        SELECT 
            ch.nom as chambre,
            COUNT(DISTINCT r.id) as nb_reservations,
            SUM(r.nombre_nuitees) as nuitees_vendues,
            DATEDIFF(?, ?) as jours_periode,
            ROUND((SUM(r.nombre_nuitees) / DATEDIFF(?, ?)) * 100, 2) as taux_occupation
        FROM chambres ch
        LEFT JOIN reservations r ON ch.id = r.chambre_id 
            AND r.date_arrivee BETWEEN ? AND ? 
            AND r.statut IN ('confirmee', 'terminee')
        WHERE ch.actif = 1
        GROUP BY ch.id, ch.nom
        ORDER BY taux_occupation DESC
    ");
    $occupationStmt->execute([$dateFin, $dateDebut, $dateFin, $dateDebut, $dateDebut, $dateFin]);
    $occupation = $occupationStmt->fetchAll();
    
    // Évolution mensuelle
    $evolutionStmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date_arrivee, '%Y-%m') as mois,
            COUNT(*) as nb_reservations,
            SUM(nombre_nuitees) as nuitees,
            SUM(montant_total) as chiffre_affaires
        FROM reservations
        WHERE date_arrivee BETWEEN ? AND ? AND statut IN ('confirmee', 'terminee')
        GROUP BY DATE_FORMAT(date_arrivee, '%Y-%m')
        ORDER BY mois
    ");
    $evolutionStmt->execute([$dateDebut, $dateFin]);
    $evolution = $evolutionStmt->fetchAll();
    
    jsonSuccess([
        'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
        'occupation_chambres' => $occupation,
        'evolution_mensuelle' => $evolution
    ]);
}

function handleRapportClients($pdo) {
    $dateDebut = $_GET['date_debut'] ?? date('Y-01-01');
    $dateFin = $_GET['date_fin'] ?? date('Y-12-31');
    
    // Top clients
    $topClientsStmt = $pdo->prepare("
        SELECT 
            CONCAT(c.nom, ' ', c.prenom) as client_nom,
            c.email,
            c.ville,
            COUNT(r.id) as nb_sejours,
            SUM(r.nombre_nuitees) as total_nuitees,
            SUM(r.montant_total) as ca_client,
            MAX(r.date_arrivee) as derniere_visite
        FROM clients c
        JOIN reservations r ON c.id = r.client_id
        WHERE r.date_arrivee BETWEEN ? AND ? AND r.statut IN ('confirmee', 'terminee')
        GROUP BY c.id
        ORDER BY ca_client DESC
        LIMIT 20
    ");
    $topClientsStmt->execute([$dateDebut, $dateFin]);
    $topClients = $topClientsStmt->fetchAll();
    
    // Répartition par origine
    $origineStmt = $pdo->prepare("
        SELECT 
            c.origine_prospect,
            COUNT(DISTINCT c.id) as nb_clients,
            COUNT(r.id) as nb_reservations,
            SUM(r.montant_total) as ca_origine
        FROM clients c
        LEFT JOIN reservations r ON c.id = r.client_id 
            AND r.date_arrivee BETWEEN ? AND ? 
            AND r.statut IN ('confirmee', 'terminee')
        GROUP BY c.origine_prospect
        ORDER BY ca_origine DESC
    ");
    $origineStmt->execute([$dateDebut, $dateFin]);
    $repartitionOrigine = $origineStmt->fetchAll();
    
    // Répartition géographique
    $geoStmt = $pdo->prepare("
        SELECT 
            c.ville,
            COUNT(DISTINCT c.id) as nb_clients,
            SUM(r.montant_total) as ca_ville
        FROM clients c
        JOIN reservations r ON c.id = r.client_id
        WHERE r.date_arrivee BETWEEN ? AND ? AND r.statut IN ('confirmee', 'terminee')
        AND c.ville IS NOT NULL AND c.ville != ''
        GROUP BY c.ville
        ORDER BY ca_ville DESC
        LIMIT 15
    ");
    $geoStmt->execute([$dateDebut, $dateFin]);
    $repartitionGeo = $geoStmt->fetchAll();
    
    jsonSuccess([
        'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
        'top_clients' => $topClients,
        'repartition_origine' => $repartitionOrigine,
        'repartition_geographique' => $repartitionGeo
    ]);
}

function handleRapportFinancier($pdo) {
    $dateDebut = $_GET['date_debut'] ?? date('Y-m-01');
    $dateFin = $_GET['date_fin'] ?? date('Y-m-t');
    
    // Chiffre d'affaires détaillé
    $caStmt = $pdo->prepare("
        SELECT 
            DATE(r.date_arrivee) as date_sejour,
            COUNT(r.id) as nb_reservations,
            SUM(r.montant_hebergement) as ca_hebergement,
            SUM(r.montant_total - r.montant_hebergement) as ca_accessoires,
            SUM(r.montant_total) as ca_total
        FROM reservations r
        WHERE r.date_arrivee BETWEEN ? AND ? AND r.statut IN ('confirmee', 'terminee')
        GROUP BY DATE(r.date_arrivee)
        ORDER BY date_sejour
    ");
    $caStmt->execute([$dateDebut, $dateFin]);
    $chiffreAffaires = $caStmt->fetchAll();
    
    // Paiements par moyen
    $paiementsStmt = $pdo->prepare("
        SELECT 
            p.moyen_paiement,
            COUNT(*) as nb_paiements,
            SUM(p.montant) as montant_total
        FROM paiements p
        WHERE p.date_paiement BETWEEN ? AND ? AND p.statut = 'encaisse'
        GROUP BY p.moyen_paiement
        ORDER BY montant_total DESC
    ");
    $paiementsStmt->execute([$dateDebut, $dateFin]);
    $paiements = $paiementsStmt->fetchAll();
    
    // Factures impayées
    $impayesStmt = $pdo->prepare("
        SELECT 
            f.numero_facture,
            f.date_facture,
            f.date_echeance,
            f.montant_ttc,
            CONCAT(c.nom, ' ', c.prenom) as client_nom,
            DATEDIFF(CURDATE(), f.date_echeance) as jours_retard
        FROM factures f
        JOIN clients c ON f.client_id = c.id
        WHERE f.statut IN ('envoyee', 'brouillon') 
        AND f.date_facture BETWEEN ? AND ?
        ORDER BY f.date_echeance
    ");
    $impayesStmt->execute([$dateDebut, $dateFin]);
    $factures_impayees = $impayesStmt->fetchAll();
    
    jsonSuccess([
        'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
        'chiffre_affaires_detaille' => $chiffreAffaires,
        'paiements_par_moyen' => $paiements,
        'factures_impayees' => $factures_impayees
    ]);
}
?>
