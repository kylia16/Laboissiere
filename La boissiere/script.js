const signUpButton = document.getElementById('signUpbutton');
const signInButton = document.getElementById('signInbutton');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signUp');

signUpButton.addEventListener('click', function() {
    signInForm.style.display = "none";
    signUpForm.style.display = "block";
    // Supprimer les messages d'erreur lorsqu'on change de formulaire
    const existingError = document.querySelector('.alert-danger');
    if (existingError) {
        existingError.remove();
    }
});

signInButton.addEventListener('click', function() {
    signInForm.style.display = "block";
    signUpForm.style.display = "none";
    // Supprimer les messages d'erreur lorsqu'on change de formulaire
    const existingError = document.querySelector('.alert-danger');
    if (existingError) {
        existingError.remove();
    }
});

// Afficher les messages d'erreur
const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('error');

if (error) {
    let errorMessage = '';

    switch (error) {
        case 'invalid_credentials':
            errorMessage = 'Email ou mot de passe incorrect.';
            break;
        case 'email_exists':
            errorMessage = 'Cette adresse email existe déjà.';
            break;
        case 'unknown_error':
            errorMessage = 'Une erreur s\'est produite. Veuillez réessayer.';
            break;
        default:
            errorMessage = 'Une erreur s\'est produite.';
    }

    // Créer et afficher le message d'erreur
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.style.marginBottom = '20px';
    errorDiv.textContent = errorMessage;

    // Vérifier si on est sur la bonne page avant d'afficher
    if (error === 'invalid_credentials') {
        // Insérer le message au début du formulaire de connexion
        const signInFormElement = document.querySelector('#signIn form');
        if (signInFormElement) {
            signInFormElement.insertBefore(errorDiv, signInFormElement.firstChild);
            // S'assurer que le formulaire de connexion est visible
            document.getElementById('signIn').style.display = 'block';
            document.getElementById('signUp').style.display = 'none';
        }
    } else if (error === 'email_exists') {
        // Insérer le message au début du formulaire d'inscription
        const signUpFormElement = document.querySelector('#signUp form');
        if (signUpFormElement) {
            signUpFormElement.insertBefore(errorDiv, signUpFormElement.firstChild);
            // S'assurer que le formulaire d'inscription est visible
            document.getElementById('signUp').style.display = 'block';
            document.getElementById('signIn').style.display = 'none';
        }
    } else {
        // Pour les autres erreurs, afficher dans le formulaire de connexion par défaut
        const signInFormElement = document.querySelector('#signIn form');
        if (signInFormElement) {
            signInFormElement.insertBefore(errorDiv, signInFormElement.firstChild);
        }
    }
}

// Gérer aussi les messages de succès si nécessaire
const success = urlParams.get('success');
if (success === 'account_created') {
    alert('Compte créé avec succès! Veuillez vous connecter.');
    // Afficher le formulaire de connexion après création de compte
    document.getElementById('signIn').style.display = 'block';
    document.getElementById('signUp').style.display = 'none';
}