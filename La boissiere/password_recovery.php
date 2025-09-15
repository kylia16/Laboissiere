<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récupération de mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            height: 70px;
        }
        .form-title {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 14px;
            color: #6c757d;
        }
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
        }
        .input-group label {
            position: absolute;
            top: -10px;
            left: 35px;
            background: white;
            padding: 0 5px;
            font-size: 14px;
            color: #6c757d;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0b5ed7;
        }
        .btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
        }
        .step.active .step-number {
            background-color: #0d6efd;
            color: white;
        }
        .step.completed .step-number {
            background-color: #198754;
            color: white;
        }
        .step-label {
            font-size: 12px;
            color: #6c757d;
        }
        .step.active .step-label,
        .step.completed .step-label {
            color: #0d6efd;
            font-weight: bold;
        }
        .countdown {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #6c757d;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #0d6efd;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .code-display {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px dashed #6c757d;
        }
        .code-display h4 {
            margin-bottom: 10px;
            color: #0d6efd;
        }
        .code-value {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #198754;
        }
        .simulation-note {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/img/logo.png.png" class="logo" alt="Logo">
        
        <div class="step-indicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Email</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Code</div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-label">Nouveau mot de passe</div>
            </div>
        </div>
        
        <h1 class="form-title">Réinitialiser votre mot de passe</h1>
        
        <div id="alert-container"></div>
        
        <!-- Étape 1: Saisie de l'email -->
        <div id="step1-form">
            <p>Entrez votre adresse email pour recevoir un code de vérification.</p>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="recovery-email" placeholder="Votre adresse email" required>
                <label for="recovery-email">Email</label>
            </div>
            <button type="button" class="btn" id="send-code-btn">Envoyer le code de vérification</button>
        </div>
        
        <!-- Étape 2: Saisie du code -->
        <div id="step2-form" style="display: none;">
            <div class="alert alert-info">
                Un code de vérification a été envoyé à <span id="email-display"></span>
                <p class="simulation-note">(Simulation - en production, ce code serait envoyé par email)</p>
            </div>
            
            <div class="code-display">
                <h4>Votre code de vérification:</h4>
                <div class="code-value" id="verification-code-display"></div>
                <p class="simulation-note">En production, ce code serait reçu par email</p>
            </div>
            
            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="text" id="verification-code-input" placeholder="Code de vérification" required maxlength="6">
                <label for="verification-code-input">Code de vérification</label>
            </div>
            <div class="countdown" id="countdown">Le code expirera dans: <span id="timer">05:00</span></div>
            <button type="button" class="btn" id="verify-code-btn">Vérifier le code</button>
        </div>
        
        <!-- Étape 3: Nouveau mot de passe -->
        <div id="step3-form" style="display: none;">
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="new-password" placeholder="Nouveau mot de passe" required>
                <label for="new-password">Nouveau mot de passe</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm-password" placeholder="Confirmer le mot de passe" required>
                <label for="confirm-password">Confirmer le mot de passe</label>
            </div>
            <button type="button" class="btn" id="reset-password-btn">Réinitialiser le mot de passe</button>
        </div>
        
        <div class="links">
            <a href="index.php">Retour à la connexion</a>
        </div>
    </div>

    <script>
        // Éléments du DOM
        const step1Form = document.getElementById('step1-form');
        const step2Form = document.getElementById('step2-form');
        const step3Form = document.getElementById('step3-form');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const emailDisplay = document.getElementById('email-display');
        const alertContainer = document.getElementById('alert-container');
        const timerDisplay = document.getElementById('timer');
        const codeDisplay = document.getElementById('verification-code-display');

        // Boutons
        const sendCodeBtn = document.getElementById('send-code-btn');
        const verifyCodeBtn = document.getElementById('verify-code-btn');
        const resetPasswordBtn = document.getElementById('reset-password-btn');

        // Variables
        let verificationCode = '';
        let userEmail = '';
        let countdownInterval;
        let timeLeft = 300; // 5 minutes en secondes

        // Afficher une alerte
        function showAlert(message, type) {
            alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }

        // Générer un code aléatoire
        function generateVerificationCode() {
            return Math.floor(100000 + Math.random() * 900000).toString();
        }

        // Démarrer le compte à rebours
        function startCountdown() {
            clearInterval(countdownInterval);
            timeLeft = 300;
            
            countdownInterval = setInterval(() => {
                timeLeft--;
                
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    showAlert("Le code de vérification a expiré. Veuillez demander un nouveau code.", "danger");
                    verifyCodeBtn.disabled = true;
                }
            }, 1000);
        }

        // Simuler l'envoi d'email (en production, cela serait fait côté serveur)
        function sendVerificationCode(email) {
            return new Promise((resolve) => {
                // Simulation d'envoi d'email
                showAlert("Envoi du code en cours...", "info");
                
                setTimeout(() => {
                    verificationCode = generateVerificationCode();
                    
                    // Afficher le code à l'écran (pour la simulation)
                    codeDisplay.textContent = verificationCode;
                    
                    resolve(true);
                }, 2000);
            });
        }

        // Simuler la vérification du code (en production, cela serait fait côté serveur)
        function verifyCode(code) {
            return code === verificationCode;
        }

        // Simuler la réinitialisation du mot de passe (en production, cela serait fait côté serveur)
        function resetPassword(email, newPassword) {
            return new Promise((resolve) => {
                // Simulation de mise à jour en base de données
                showAlert("Réinitialisation du mot de passe en cours...", "info");
                
                setTimeout(() => {
                    resolve(true);
                }, 1500);
            });
        }

        // Événement: Envoi du code de vérification
        sendCodeBtn.addEventListener('click', async () => {
            const email = document.getElementById('recovery-email').value;
            
            if (!email) {
                showAlert("Veuillez entrer votre adresse email.", "danger");
                return;
            }
            
            // Validation basique de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert("Veuillez entrer une adresse email valide.", "danger");
                return;
            }
            
            userEmail = email;
            sendCodeBtn.disabled = true;
            sendCodeBtn.textContent = "Envoi en cours...";
            
            try {
                const response = await fetch('send_code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `email=${encodeURIComponent(email)}`
                    });
                    const result = await response.json();

                    if (result.success) {
                        step1Form.style.display = 'none';
                        step2Form.style.display = 'block';
                        // etc.
                    } else {
                        showAlert(result.message || "Erreur d'envoi du code", "danger");
                }

                
                if (success) {
                    // Afficher l'étape 2
                    step1Form.style.display = 'none';
                    step2Form.style.display = 'block';
                    step1.classList.remove('active');
                    step1.classList.add('completed');
                    step2.classList.add('active');
                    
                    emailDisplay.textContent = email;
                    startCountdown();
                    showAlert("Code de vérification généré avec succès. En production, il serait envoyé par email.", "success");
                }
            } catch (error) {
                showAlert("Une erreur s'est produite lors de l'envoi du code.", "danger");
            } finally {
                sendCodeBtn.disabled = false;
                sendCodeBtn.textContent = "Envoyer le code de vérification";
            }
        });

        // Événement: Vérification du code
        verifyCodeBtn.addEventListener('click', async () => {
    const code = document.getElementById('verification-code-input').value;

    if (!code || code.length !== 6) {
        showAlert("Veuillez entrer le code de vérification à 6 chiffres.", "danger");
        return;
    }

    if (timeLeft <= 0) {
        showAlert("Le code a expiré. Veuillez demander un nouveau code.", "danger");
        return;
    }

    try {
        const response = await fetch('verify_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `code=${encodeURIComponent(code)}`
        });

        const result = await response.json();

        if (result.success) {
            // Code correct, passer à l'étape 3
            step2Form.style.display = 'none';
            step3Form.style.display = 'block';
            step2.classList.remove('active');
            step2.classList.add('completed');
            step3.classList.add('active');

            clearInterval(countdownInterval);
            showAlert("Code vérifié avec succès. Vous pouvez maintenant définir un nouveau mot de passe.", "success");
        } else {
            showAlert("Code de vérification incorrect. Veuillez réessayer.", "danger");
        }
    } catch (error) {
        console.error("Erreur lors de la vérification du code :", error);
        showAlert("Une erreur est survenue lors de la vérification du code.", "danger");
    }
});


        // Événement: Réinitialisation du mot de passe
       resetPasswordBtn.addEventListener('click', async () => {
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (!newPassword || !confirmPassword) {
        showAlert("Veuillez remplir tous les champs.", "danger");
        return;
    }

    if (newPassword !== confirmPassword) {
        showAlert("Les mots de passe ne correspondent pas.", "danger");
        return;
    }

    if (newPassword.length < 6) {
        showAlert("Le mot de passe doit contenir au moins 6 caractères.", "danger");
        return;
    }

    resetPasswordBtn.disabled = true;
    resetPasswordBtn.textContent = "Traitement en cours...";

    try {
        const response = await fetch('reset_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `email=${encodeURIComponent(userEmail)}&password=${encodeURIComponent(newPassword)}`
        });

        const result = await response.json();

        if (result.success) {
            showAlert("Votre mot de passe a été réinitialisé avec succès. Vous allez être redirigé vers la page de connexion.", "success");

            setTimeout(() => {
                window.location.href = "index.php";
            }, 3000);
        } else {
            showAlert(result.message || "Une erreur s'est produite lors de la réinitialisation.", "danger");
            resetPasswordBtn.disabled = false;
            resetPasswordBtn.textContent = "Réinitialiser le mot de passe";
        }
    } catch (error) {
        console.error("Erreur lors de la réinitialisation :", error);
        showAlert("Une erreur s'est produite lors de la communication avec le serveur.", "danger");
        resetPasswordBtn.disabled = false;
        resetPasswordBtn.textContent = "Réinitialiser le mot de passe";
    }
});

    </script>
</body>
</html> 