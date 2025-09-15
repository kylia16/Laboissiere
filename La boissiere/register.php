<?php 
include 'connect.php';

if (isset($_POST['signup'])) {
    // Récupération des données du formulaire
    $firstName = $_POST['fName'];
    $lastName = $_POST['lname']; 
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password); 

    // Vérifier si l'email existe déjà
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        header("Location: index.php?error=email_exists");
        exit();
    } else {
        // Requête INSERT
        $insertQuery = "INSERT INTO users (firstName, lastName, email, password) 
                VALUES ('$firstName', '$lastName', '$email', '$password')";

        if ($conn->query($insertQuery) === TRUE) {
            header("Location: index.php?success=account_created");
            exit();
        } else {
            header("Location: index.php?error=unknown_error");
            exit();
        }
    }
}

// Connexion
if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        session_start();
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        header("Location: homepage.php");
        exit();
    } else {
        header("Location: index.php?error=invalid_credentials");
        exit();
    }
}
?>