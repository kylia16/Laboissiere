<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement et connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=" https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css ">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" id="signUp" style="display:none;">
        <img src="assets/img/logo.png.png" class="d-block mx-auto" alt="Logo" style="height: 70px;">
        <h1 class="form-title">Créer un compte</h1>
        <form method="post" action="register.php">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="Nom" required>
                <label for="fName">Nom</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="Prénom" id="lname" placeholder="Prénom" required>
                <label for="lname">Prénom</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <label for="password">Mot de passe</label>
            </div>
            <br>
            <input type="submit" class="btn" value="Créer un compte" name="signup">
        </form>
        <br>
        <p class="or">
            -----------ou-----------
        </p>
        <div class="links">
            <p>Avez-vous déjà un compte?</p>
            <button id="signInbutton">Connexion</button>
        </div>
    </div>


    <div class="container" id="signIn">
        <img src="assets/img/logo.png.png" class="d-block mx-auto" alt="Logo" style="height: 70px; ">
        <h1 class="form-title">Connectez-vous</h1>
        <form method="post" action="register.php">
            <!-- Message d'erreur pour la connexion -->
            <div id="login-error" class="error-message">
                <?php 
                if (isset($login_error)) {
                    echo $login_error;
                }
                ?>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                <label for="password">Mot de passe</label>
            </div>

            <p class="recover">
                <a href="password_recovery.php">Mot de passe oublié?</a>
            </p>
            <input type="submit" class="btn" value="Connexion" name="signIn">
        </form>
        <br>
        <p class="or">
            -----------ou-----------
        </p>
        <div class="links">
            <p>Vous n'avez pas de compte?</p>
            <button id="signUpbutton">Créer un compte</button>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>