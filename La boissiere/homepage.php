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
                <i class="fas fa-home me-2"></i>La Boissière
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" data-section="dashboard">
                            <i class="fas fa-chart-line me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#calendar" data-section="calendar">
                            <i class="fas fa-calendar-alt me-1"></i>Calendrier
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#clients" data-section="clients">
                            <i class="fas fa-users me-1"></i>Clients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reservations" data-section="reservations">
                            <i class="fas fa-bed me-1"></i>Réservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products" data-section="products">
                            <i class="fas fa-shopping-cart me-1"></i>Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#sales" data-section="sales">
                            <i class="fas fa-cash-register me-1"></i>Ventes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#invoices" data-section="invoices">
                            <i class="fas fa-file-invoice me-1"></i>Factures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reports" data-section="reports">
                            <i class="fas fa-chart-bar me-1"></i>Rapports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
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
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-bed me-2"></i>Gestion des réservations</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reservationModal">
                        <i class="fas fa-plus me-1"></i>Nouvelle réservation
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="reservation-search" placeholder="Rechercher...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="reservation-statut-filter">
                        <option value="">Tous les statuts</option>
                        <option value="en-attente">En attente</option>
                        <option value="confirmee">Confirmée</option>
                        <option value="annulee">Annulée</option>
                        <option value="terminee">Terminée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="reservation-chambre-filter">
                        <option value="">Toutes les chambres</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="reservation-date-debut">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="reservation-date-fin">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary" id="reservation-filter-btn">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Table des réservations -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="reservations-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Chambre</th>
                                    <th>Arrivée</th>
                                    <th>Départ</th>
                                    <th>Nuitées</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Réservations will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="products-section" class="content-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-shopping-cart me-2"></i>Produits et accessoires</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#produitModal">
                        <i class="fas fa-plus me-1"></i>Nouveau produit
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="produit-search" placeholder="Rechercher un produit...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="produit-categorie-filter">
                        <option value="">Toutes les catégories</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="produit-actif-filter">
                        <option value="">Tous</option>
                        <option value="1">Actifs</option>
                        <option value="0">Inactifs</option>
                    </select>
                </div>
            </div>

            <!-- Table des produits -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="produits-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix unitaire</th>
                                    <th>Unité</th>
                                    <th>Stock</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Produits will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="sales-section" class="content-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-cash-register me-2"></i>Ventes d'accessoires</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#venteModal">
                        <i class="fas fa-plus me-1"></i>Nouvelle vente
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="vente-reservation-filter">
                        <option value="">Toutes les réservations</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="vente-produit-filter">
                        <option value="">Tous les produits</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="vente-date-debut">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="vente-date-fin">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary" id="vente-filter-btn">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Table des ventes -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="ventes-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Chambre</th>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ventes will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="invoices-section" class="content-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-file-invoice me-2"></i>Gestion des factures</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success me-2" id="generer-facture-btn">
                        <i class="fas fa-file-plus me-1"></i>Générer facture
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#factureModal">
                        <i class="fas fa-plus me-1"></i>Nouvelle facture
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="facture-search" placeholder="N° facture ou client...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="facture-statut-filter">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon">Brouillon</option>
                        <option value="envoyee">Envoyée</option>
                        <option value="payee">Payée</option>
                        <option value="annulee">Annulée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="facture-date-debut">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="facture-date-fin">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary" id="facture-filter-btn">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Table des factures -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="factures-table">
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Échéance</th>
                                    <th>Montant TTC</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Factures will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="payments-section" class="content-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-credit-card me-2"></i>Gestion des paiements</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paiementModal">
                        <i class="fas fa-plus me-1"></i>Nouveau paiement
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="paiement-moyen-filter">
                        <option value="">Tous les moyens</option>
                        <option value="especes">Espèces</option>
                        <option value="carte">Carte bancaire</option>
                        <option value="cheque">Chèque</option>
                        <option value="virement">Virement</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="paiement-date-debut">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="paiement-date-fin">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="paiement-facture-filter">
                        <option value="">Toutes les factures</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary" id="paiement-filter-btn">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Table des paiements -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="paiements-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Facture</th>
                                    <th>Montant</th>
                                    <th>Moyen</th>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Paiements will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="reports-section" class="content-section">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="fas fa-chart-bar me-2"></i>Rapports et analyses</h2>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" id="export-excel-btn">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </button>
                </div>
            </div>

            <!-- Sélection du type de rapport -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Type de rapport</label>
                                    <select class="form-select" id="rapport-type">
                                        <option value="synthese">Synthèse générale</option>
                                        <option value="ventes">Rapport des ventes</option>
                                        <option value="occupation">Taux d'occupation</option>
                                        <option value="clients">Analyse clients</option>
                                        <option value="financier">Rapport financier</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date début</label>
                                    <input type="date" class="form-control" id="rapport-date-debut">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date fin</label>
                                    <input type="date" class="form-control" id="rapport-date-fin">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button class="btn btn-primary d-block" id="generer-rapport-btn">
                                        <i class="fas fa-chart-line me-1"></i>Générer rapport
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu du rapport -->
            <div id="rapport-content">
                <div class="text-center text-muted">
                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                    <p>Sélectionnez un type de rapport et cliquez sur "Générer rapport"</p>
                </div>
            </div>
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

    <!-- Modal Réservation -->
    <div class="modal fade" id="reservationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reservation-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reservation-client" class="form-label">Client *</label>
                                    <select class="form-select" id="reservation-client" required>
                                        <option value="">Sélectionner un client...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reservation-chambre" class="form-label">Chambre *</label>
                                    <select class="form-select" id="reservation-chambre" required>
                                        <option value="">Sélectionner une chambre...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reservation-date-arrivee" class="form-label">Date d'arrivée *</label>
                                    <input type="date" class="form-control" id="reservation-date-arrivee" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reservation-date-depart" class="form-label">Date de départ *</label>
                                    <input type="date" class="form-control" id="reservation-date-depart" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-heure-arrivee" class="form-label">Heure d'arrivée</label>
                                    <input type="time" class="form-control" id="reservation-heure-arrivee" value="15:00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-heure-depart" class="form-label">Heure de départ</label>
                                    <input type="time" class="form-control" id="reservation-heure-depart" value="11:00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-personnes" class="form-label">Nombre de personnes *</label>
                                    <input type="number" class="form-control" id="reservation-personnes" min="1" value="2" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-tarif" class="form-label">Tarif par nuitée</label>
                                    <input type="number" class="form-control" id="reservation-tarif" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-arrhes" class="form-label">Arrhes</label>
                                    <input type="number" class="form-control" id="reservation-arrhes" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reservation-statut" class="form-label">Statut</label>
                                    <select class="form-select" id="reservation-statut">
                                        <option value="en-attente">En attente</option>
                                        <option value="confirmee">Confirmée</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reservation-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="reservation-notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-reservation">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Produit -->
    <div class="modal fade" id="produitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="produit-form">
                        <div class="mb-3">
                            <label for="produit-nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="produit-nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="produit-categorie" class="form-label">Catégorie *</label>
                            <select class="form-select" id="produit-categorie" required>
                                <option value="">Sélectionner...</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produit-prix" class="form-label">Prix unitaire *</label>
                                    <input type="number" class="form-control" id="produit-prix" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produit-unite" class="form-label">Unité</label>
                                    <input type="text" class="form-control" id="produit-unite" value="unité">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produit-stock" class="form-label">Stock actuel</label>
                                    <input type="number" class="form-control" id="produit-stock" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="produit-stock-min" class="form-label">Stock minimum</label>
                                    <input type="number" class="form-control" id="produit-stock-min" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="produit-description" class="form-label">Description</label>
                            <textarea class="form-control" id="produit-description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-produit">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Vente -->
    <div class="modal fade" id="venteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle vente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="vente-form">
                        <div class="mb-3">
                            <label for="vente-reservation" class="form-label">Réservation *</label>
                            <select class="form-select" id="vente-reservation" required>
                                <option value="">Sélectionner une réservation...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="vente-produit" class="form-label">Produit *</label>
                            <select class="form-select" id="vente-produit" required>
                                <option value="">Sélectionner un produit...</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vente-quantite" class="form-label">Quantité *</label>
                                    <input type="number" class="form-control" id="vente-quantite" step="0.01" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vente-prix" class="form-label">Prix unitaire</label>
                                    <input type="number" class="form-control" id="vente-prix" step="0.01" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="vente-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="vente-notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-vente">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Paiement -->
    <div class="modal fade" id="paiementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paiement-form">
                        <div class="mb-3">
                            <label for="paiement-facture" class="form-label">Facture</label>
                            <select class="form-select" id="paiement-facture">
                                <option value="">Sélectionner une facture...</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="paiement-montant" class="form-label">Montant *</label>
                                    <input type="number" class="form-control" id="paiement-montant" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="paiement-moyen" class="form-label">Moyen de paiement *</label>
                                    <select class="form-select" id="paiement-moyen" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="especes">Espèces</option>
                                        <option value="carte">Carte bancaire</option>
                                        <option value="cheque">Chèque</option>
                                        <option value="virement">Virement</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="paiement-date" class="form-label">Date de paiement *</label>
                                    <input type="date" class="form-control" id="paiement-date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="paiement-reference" class="form-label">Référence</label>
                                    <input type="text" class="form-control" id="paiement-reference">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="paiement-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="paiement-notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="save-paiement">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
