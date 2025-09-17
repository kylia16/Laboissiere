<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'la_boissiere');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration générale
define('SITE_NAME', 'La Boissière - Système de Gestion');
define('SITE_URL', 'http://localhost');

// Paramètres de l'application
define('DEFAULT_TIMEZONE', 'Europe/Paris');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// Configuration des chambres
define('CHAMBRES', [
    'Lavande' => ['tarif' => 80, 'capacite' => 2],
    'Rose' => ['tarif' => 85, 'capacite' => 2],
    'Jasmin' => ['tarif' => 90, 'capacite' => 3],
    'Orchidée' => ['tarif' => 95, 'capacite' => 2],
    'Pivoine' => ['tarif' => 100, 'capacite' => 4]
]);

// Configuration des produits/accessoires
define('CATEGORIES_PRODUITS', [
    'boissons' => 'Boissons',
    'repas' => 'Repas',
    'massage' => 'Massages',
    'sport' => 'Activités sportives',
    'culture' => 'Activités culturelles',
    'autre' => 'Autres'
]);

// Configuration des moyens de paiement
define('MOYENS_PAIEMENT', [
    'especes' => 'Espèces',
    'carte' => 'Carte bancaire',
    'cheque' => 'Chèque',
    'virement' => 'Virement',
    'paypal' => 'PayPal'
]);

// Configuration des statuts
define('STATUTS_RESERVATION', [
    'en-attente' => 'En attente',
    'confirmee' => 'Confirmée',
    'annulee' => 'Annulée',
    'terminee' => 'Terminée'
]);

define('STATUTS_FACTURE', [
    'brouillon' => 'Brouillon',
    'envoyee' => 'Envoyée',
    'payee' => 'Payée',
    'annulee' => 'Annulée'
]);

// Fuseau horaire
date_default_timezone_set(DEFAULT_TIMEZONE);

// Fonction de connexion à la base de données
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        throw new Exception("Erreur de connexion à la base de données");
    }
}

// Fonction utilitaire pour formater les dates
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Fonction utilitaire pour formater les montants
function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' €';
}

// Fonction utilitaire pour générer un numéro de facture
function genererNumeroFacture() {
    return 'FAC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Fonction utilitaire pour valider un email
function validerEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fonction utilitaire pour nettoyer les données d'entrée
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Headers pour les réponses JSON
function setJSONHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Fonction pour retourner une réponse JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    setJSONHeaders();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction pour retourner une erreur JSON
function jsonError($message, $status = 400) {
    jsonResponse(['error' => $message, 'status' => $status], $status);
}

// Fonction pour retourner un succès JSON
function jsonSuccess($data = [], $message = 'Succès') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}
?>
