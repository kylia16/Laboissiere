<?php
require_once 'config.php';

setJSONHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Méthode non autorisée', 405);
}

try {
    $pdo = getDBConnection();
    
    $stats = getDashboardStats($pdo);
    $recentActivity = getRecentActivity($pdo);
    $todayArrivals = getTodayArrivals($pdo);
    $monthlyChart = getMonthlyChart($pdo);
    
    jsonSuccess([
        'stats' => $stats,
        'recent_activity' => $recentActivity,
        'today_arrivals' => $todayArrivals,
        'monthly_chart' => $monthlyChart
    ]);
    
} catch (Exception $e) {
    error_log("Erreur API dashboard: " . $e->getMessage());
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}

function getDashboardStats($pdo) {
    $currentMonth = date('Y-m');
    $currentYear = date('Y');
    $today = date('Y-m-d');
    
    // Réservations du mois
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE DATE_FORMAT(date_arrivee, '%Y-%m') = ? 
        AND statut IN ('confirmee', 'terminee')
    ");
    $stmt->execute([$currentMonth]);
    $monthlyReservations = $stmt->fetchColumn();
    
    // Chiffre d'affaires du mois
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant_total), 0) as total 
        FROM reservations 
        WHERE DATE_FORMAT(date_arrivee, '%Y-%m') = ? 
        AND statut IN ('confirmee', 'terminee')
    ");
    $stmt->execute([$currentMonth]);
    $monthlyRevenue = $stmt->fetchColumn();
    
    // Chambres occupées aujourd'hui
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE date_arrivee <= ? 
        AND date_depart > ? 
        AND statut = 'confirmee'
    ");
    $stmt->execute([$today, $today]);
    $occupiedRooms = $stmt->fetchColumn();
    
    // Total des chambres
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chambres WHERE actif = 1");
    $stmt->execute();
    $totalRooms = $stmt->fetchColumn();
    
    // Nouveaux clients du mois
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM clients 
        WHERE DATE_FORMAT(date_creation, '%Y-%m') = ?
    ");
    $stmt->execute([$currentMonth]);
    $newClients = $stmt->fetchColumn();
    
    // Taux d'occupation du mois
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT DATE(d.date)) as jours_occupes
        FROM (
            SELECT date_arrivee + INTERVAL n.n DAY as date
            FROM reservations r
            CROSS JOIN (
                SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
                SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION 
                SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION 
                SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION 
                SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION 
                SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION 
                SELECT 30
            ) n
            WHERE r.statut = 'confirmee'
            AND DATE_FORMAT(r.date_arrivee, '%Y-%m') = ?
            AND date_arrivee + INTERVAL n.n DAY < date_depart
            AND DATE_FORMAT(date_arrivee + INTERVAL n.n DAY, '%Y-%m') = ?
        ) d
    ");
    $stmt->execute([$currentMonth, $currentMonth]);
    $occupiedDays = $stmt->fetchColumn();
    
    $daysInMonth = date('t');
    $occupationRate = $totalRooms > 0 ? round(($occupiedDays / ($daysInMonth * $totalRooms)) * 100, 1) : 0;
    
    // Réservations en attente
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE statut = 'en-attente'");
    $stmt->execute();
    $pendingReservations = $stmt->fetchColumn();
    
    return [
        'monthly_reservations' => (int)$monthlyReservations,
        'monthly_revenue' => (float)$monthlyRevenue,
        'occupied_rooms' => (int)$occupiedRooms,
        'total_rooms' => (int)$totalRooms,
        'new_clients' => (int)$newClients,
        'occupation_rate' => $occupationRate,
        'pending_reservations' => (int)$pendingReservations
    ];
}

function getRecentActivity($pdo) {
    $activities = [];
    
    // Dernières réservations
    $stmt = $pdo->prepare("
        SELECT 'reservation' as type, 
               CONCAT('Nouvelle réservation - ', ch.nom, ' par ', c.nom, ' ', c.prenom) as message,
               r.date_reservation as date_activite,
               r.id as reference_id
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE r.date_reservation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY r.date_reservation DESC
        LIMIT 5
    ");
    $stmt->execute();
    $reservations = $stmt->fetchAll();
    
    // Derniers paiements
    $stmt = $pdo->prepare("
        SELECT 'payment' as type,
               CONCAT('Paiement reçu - ', FORMAT(p.montant, 2), '€') as message,
               p.date_creation as date_activite,
               p.id as reference_id
        FROM paiements p
        WHERE p.date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY p.date_creation DESC
        LIMIT 5
    ");
    $stmt->execute();
    $payments = $stmt->fetchAll();
    
    // Nouveaux clients
    $stmt = $pdo->prepare("
        SELECT 'client' as type,
               CONCAT('Nouveau client - ', c.nom, ' ', c.prenom) as message,
               c.date_creation as date_activite,
               c.id as reference_id
        FROM clients c
        WHERE c.date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY c.date_creation DESC
        LIMIT 5
    ");
    $stmt->execute();
    $clients = $stmt->fetchAll();
    
    // Fusionner et trier toutes les activités
    $activities = array_merge($reservations, $payments, $clients);
    
    // Trier par date décroissante
    usort($activities, function($a, $b) {
        return strtotime($b['date_activite']) - strtotime($a['date_activite']);
    });
    
    // Garder seulement les 10 plus récentes
    $activities = array_slice($activities, 0, 10);
    
    // Formater les dates
    foreach ($activities as &$activity) {
        $date = new DateTime($activity['date_activite']);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                $activity['time_ago'] = $diff->i . ' min';
            } else {
                $activity['time_ago'] = $diff->h . 'h';
            }
        } else {
            $activity['time_ago'] = $diff->days . 'j';
        }
        
        // Ajouter l'icône et la couleur selon le type
        switch ($activity['type']) {
            case 'reservation':
                $activity['icon'] = 'bed';
                $activity['color'] = 'success';
                break;
            case 'payment':
                $activity['icon'] = 'euro-sign';
                $activity['color'] = 'info';
                break;
            case 'client':
                $activity['icon'] = 'user-plus';
                $activity['color'] = 'primary';
                break;
        }
    }
    
    return $activities;
}

function getTodayArrivals($pdo) {
    $today = date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT r.*, 
               CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
               c.telephone as client_telephone,
               ch.nom as chambre_nom
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN chambres ch ON r.chambre_id = ch.id
        WHERE r.date_arrivee = ? 
        AND r.statut = 'confirmee'
        ORDER BY r.heure_arrivee
    ");
    $stmt->execute([$today]);
    
    return $stmt->fetchAll();
}

function getMonthlyChart($pdo) {
    $months = [];
    $revenues = [];
    $reservations = [];
    
    // Données des 12 derniers mois
    for ($i = 11; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $monthName = date('M', strtotime("-$i months"));
        
        // Chiffre d'affaires du mois
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(montant_total), 0) as total 
            FROM reservations 
            WHERE DATE_FORMAT(date_arrivee, '%Y-%m') = ? 
            AND statut IN ('confirmee', 'terminee')
        ");
        $stmt->execute([$date]);
        $revenue = $stmt->fetchColumn();
        
        // Nombre de réservations du mois
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE DATE_FORMAT(date_arrivee, '%Y-%m') = ? 
            AND statut IN ('confirmee', 'terminee')
        ");
        $stmt->execute([$date]);
        $reservationCount = $stmt->fetchColumn();
        
        $months[] = $monthName;
        $revenues[] = (float)$revenue;
        $reservations[] = (int)$reservationCount;
    }
    
    return [
        'months' => $months,
        'revenues' => $revenues,
        'reservations' => $reservations
    ];
}
?>
