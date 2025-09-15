<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "login";

$conn = new mysqli($host, $user, $pass, $db);

// Vérifie la connexion
if ($conn->connect_error) {
    echo "Échec de la connexion à la base de données : " . $conn->connect_error;
    exit(); // Facultatif mais recommandé
}
?>
