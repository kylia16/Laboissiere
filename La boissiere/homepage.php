<?php
session_start();
include("connect.php");

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);
    echo $user['firstName'] . ' ' . $user['lastName'];
} else {
    // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
    header("Location: index.php"); 
    exit();
}
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>La Boissière - Système de Gestion</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
    </head>

    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#dashboard">
                    <img src="assets/img/logo.png.png" alt="Logo" style="height: 65px;" class="me-2">La Boissière
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-section="dashboard">
                                <i class="fas fa-chart-line me-2"></i>Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#calendar" data-section="calendar">
                                <i class="fas fa-calendar-alt me-2"></i>Calendrier
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#clients" data-section="clients">
                                <i class="fas fa-users me-2"></i>Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reservations" data-section="reservations">
                                <i class="fas fa-bed me-2"></i>Réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#products" data-section="products">
                                <i class="fas fa-shopping-cart me-2"></i>Produits
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#sales" data-section="sales">
                                <i class="fas fa-cash-register me-2"></i>Ventes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#invoices" data-section="invoices">
                                <i class="fas fa-file-invoice me-2"></i>Factures
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reports" data-section="reports">
                                <i class="fas fa-chart-bar me-2"></i>Rapports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid main-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section active">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-chart-line me-2"></i>Tableau de bord</h2>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Réservations du mois</h6>
                                        <h3 id="monthly-reservations">0</h3>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Chiffre d'affaires</h6>
                                        <h3 id="monthly-revenue">0€</h3>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-euro-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Chambres occupées</h6>
                                        <h3 id="occupied-rooms">0/5</h3>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Nouveaux clients</h6>
                                        <h3 id="new-clients">0</h3>
                                    </div>
                                    <div class="stats-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clock me-2"></i>Activité récente</h5>
                            </div>
                            <div class="card-body">
                                <div id="recent-activity">
                                    <p class="text-muted">Aucune activité récente</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar-check me-2"></i>Arrivées aujourd'hui</h5>
                            </div>
                            <div class="card-body">
                                <div id="today-arrivals">
                                    <p class="text-muted">Aucune arrivée prévue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div id="calendar-section" class="content-section">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2><i class="fas fa-calendar-alt me-2"></i>Calendrier des réservations</h2>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="view-month">Mois</button>
                            <button type="button" class="btn btn-outline-primary" id="view-week">Semaine</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary" id="prev-period"><i class="fas fa-chevron-left"></i></button>
                        <span id="current-period" class="mx-3 fw-bold"></span>
                        <button class="btn btn-primary" id="next-period"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div id="calendar-container">
                            <!-- Calendar will be generated here -->
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6>Légende :</h6>
                                <span class="badge bg-success me-2">Confirmé</span>
                                <span class="badge bg-warning me-2">En attente</span>
                                <span class="badge bg-danger me-2">Annulé</span>
                                <span class="badge bg-secondary me-2">Libre</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients Section -->
            <div id="clients-section" class="content-section">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2><i class="fas fa-users me-2"></i>Gestion des clients</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                        <i class="fas fa-plus me-1"></i>Nouveau client
                    </button>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="client-search" placeholder="Rechercher un client...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="client-filter">
                        <option value="">Tous les clients</option>
                        <option value="active">Clients actifs</option>
                        <option value="inactive">Clients inactifs</option>
                    </select>
                    </div>
                </div>

                <!-- Clients Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="clients-table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Ville</th>
                                        <th>Dernière visite</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Clients will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other sections will be added similarly -->
            <div id="reservations-section" class="content-section">
                <h2><i class="fas fa-bed me-2"></i>Réservations</h2>
                <p>Module en cours de développement...</p>
            </div>

            <div id="products-section" class="content-section">
                <h2><i class="fas fa-shopping-cart me-2"></i>Produits et accessoires</h2>
                <p>Module en cours de développement...</p>
            </div>

            <div id="sales-section" class="content-section">
                <h2><i class="fas fa-cash-register me-2"></i>Ventes</h2>
                <p>Module en cours de développement...</p>
            </div>

            <div id="invoices-section" class="content-section">
                <h2><i class="fas fa-file-invoice me-2"></i>Factures</h2>
                <p>Module en cours de développement...</p>
            </div>

            <div id="reports-section" class="content-section">
                <h2><i class="fas fa-chart-bar me-2"></i>Rapports</h2>
                <p>Module en cours de développement...</p>
            </div>
        </div>

        <!-- Client Modal -->
        <div class="modal fade" id="clientModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="client-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client-nom" class="form-label">Nom *</label>
                                        <input type="text" class="form-control" id="client-nom" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client-prenom" class="form-label">Prénom *</label>
                                        <input type="text" class="form-control" id="client-prenom" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client-email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="client-email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client-telephone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="client-telephone">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="client-adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="client-adresse">
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="client-code-postal" class="form-label">Code postal</label>
                                        <input type="text" class="form-control" id="client-code-postal">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="client-ville" class="form-label">Ville</label>
                                        <input type="text" class="form-control" id="client-ville">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="client-origine" class="form-label">Origine prospect</label>
                                <select class="form-select" id="client-origine">
                                <option value="">Sélectionner...</option>
                                <option value="internet">Internet</option>
                                <option value="bouche-a-oreille">Bouche à oreille</option>
                                <option value="partenaire">Partenaire</option>
                                <option value="autre">Autre</option>
                            </select>
                            </div>
                            <div class="mb-3">
                                <label for="client-notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="client-notes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="save-client">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/app.js"></script>

        <?php if(isset($_SESSION['email'])){
            $email=$_SESSION['email'];
            $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
            while($row=mysqli_fetch_array($query)){
                echo$row['firstName'].''.$row['lastName'];
            }
        }
        ?>
        <a href="logout.php">logout</a>
    </body>
    </html>