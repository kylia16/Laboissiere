-- Base de données pour La Boissière
-- Système de gestion des réservations, clients, ventes et factures

CREATE DATABASE IF NOT EXISTS la_boissiere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE la_boissiere;

-- Table des clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    pays VARCHAR(100) DEFAULT 'France',
    origine_prospect ENUM('internet', 'bouche-a-oreille', 'partenaire', 'autre') DEFAULT 'autre',
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_nom_prenom (nom, prenom)
);

-- Table des chambres
CREATE TABLE chambres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    capacite INT NOT NULL DEFAULT 2,
    tarif_base DECIMAL(10,2) NOT NULL,
    equipements JSON,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    chambre_id INT NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    heure_arrivee TIME DEFAULT '15:00:00',
    heure_depart TIME DEFAULT '11:00:00',
    nombre_nuitees INT NOT NULL,
    nombre_personnes INT NOT NULL DEFAULT 1,
    tarif_nuitee DECIMAL(10,2) NOT NULL,
    montant_hebergement DECIMAL(10,2) NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    arrhes DECIMAL(10,2) DEFAULT 0,
    statut ENUM('en-attente', 'confirmee', 'annulee', 'terminee') DEFAULT 'en-attente',
    notes TEXT,
    date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (chambre_id) REFERENCES chambres(id) ON DELETE RESTRICT,
    INDEX idx_dates (date_arrivee, date_depart),
    INDEX idx_client (client_id),
    INDEX idx_chambre (chambre_id),
    INDEX idx_statut (statut)
);

-- Table des catégories de produits
CREATE TABLE categories_produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    actif BOOLEAN DEFAULT TRUE
);

-- Table des produits/accessoires
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) DEFAULT 'unité',
    stock_actuel INT DEFAULT 0,
    stock_minimum INT DEFAULT 0,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories_produits(id) ON DELETE RESTRICT,
    INDEX idx_categorie (categorie_id),
    INDEX idx_nom (nom)
);

-- Table des ventes (accessoires vendus aux clients)
CREATE TABLE ventes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE RESTRICT,
    INDEX idx_reservation (reservation_id),
    INDEX idx_produit (produit_id),
    INDEX idx_date (date_vente)
);

-- Table des factures
CREATE TABLE factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_facture VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    reservation_id INT,
    date_facture DATE NOT NULL,
    date_echeance DATE,
    montant_ht DECIMAL(10,2) NOT NULL DEFAULT 0,
    taux_tva DECIMAL(5,2) DEFAULT 0,
    montant_tva DECIMAL(10,2) NOT NULL DEFAULT 0,
    montant_ttc DECIMAL(10,2) NOT NULL,
    statut ENUM('brouillon', 'envoyee', 'payee', 'annulee') DEFAULT 'brouillon',
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_numero (numero_facture),
    INDEX idx_client (client_id),
    INDEX idx_date (date_facture),
    INDEX idx_statut (statut)
);

-- Table des lignes de facture
CREATE TABLE lignes_facture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT NOT NULL,
    type_ligne ENUM('hebergement', 'produit', 'service', 'autre') NOT NULL,
    reference_id INT, -- ID de la réservation ou du produit selon le type
    description TEXT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
    INDEX idx_facture (facture_id)
);

-- Table des paiements
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT,
    reservation_id INT,
    montant DECIMAL(10,2) NOT NULL,
    moyen_paiement ENUM('especes', 'carte', 'cheque', 'virement', 'paypal') NOT NULL,
    reference_paiement VARCHAR(100),
    date_paiement DATE NOT NULL,
    date_encaissement DATE,
    statut ENUM('en-attente', 'encaisse', 'rejete') DEFAULT 'encaisse',
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_facture (facture_id),
    INDEX idx_reservation (reservation_id),
    INDEX idx_date (date_paiement)
);

-- Table des paramètres système
CREATE TABLE parametres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) UNIQUE NOT NULL,
    valeur TEXT,
    description TEXT,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des données de base

-- Chambres
INSERT INTO chambres (nom, description, capacite, tarif_base, equipements) VALUES
('Lavande', 'Chambre romantique avec vue sur les champs de lavande', 2, 80.00, '["wifi", "tv", "climatisation", "balcon"]'),
('Rose', 'Chambre élégante décorée dans les tons roses', 2, 85.00, '["wifi", "tv", "climatisation", "minibar"]'),
('Jasmin', 'Grande chambre familiale avec lit supplémentaire', 3, 90.00, '["wifi", "tv", "climatisation", "lit-bebe"]'),
('Orchidée', 'Suite luxueuse avec jacuzzi privé', 2, 95.00, '["wifi", "tv", "climatisation", "jacuzzi", "balcon"]'),
('Pivoine', 'Chambre familiale spacieuse pour 4 personnes', 4, 100.00, '["wifi", "tv", "climatisation", "kitchenette"]');

-- Catégories de produits
INSERT INTO categories_produits (nom, description) VALUES
('Boissons', 'Boissons chaudes et froides'),
('Repas', 'Petits déjeuners, déjeuners et dîners'),
('Massages', 'Services de massage et bien-être'),
('Activités sportives', 'Location matériel et cours'),
('Activités culturelles', 'Stages et ateliers'),
('Autres', 'Autres services et produits');

-- Produits de base
INSERT INTO produits (categorie_id, nom, description, prix_unitaire, unite) VALUES
(1, 'Café', 'Café expresso', 2.50, 'tasse'),
(1, 'Thé', 'Thé bio de la région', 2.00, 'tasse'),
(1, 'Jus d\'orange frais', 'Jus pressé minute', 4.00, 'verre'),
(1, 'Eau minérale', 'Bouteille 50cl', 2.00, 'bouteille'),
(2, 'Petit déjeuner complet', 'Pain, confiture, beurre, boisson chaude', 12.00, 'personne'),
(2, 'Panier pique-nique', 'Sandwich, fruit, boisson, dessert', 15.00, 'personne'),
(3, 'Massage relaxant', 'Massage de 60 minutes', 65.00, 'séance'),
(3, 'Massage dos', 'Massage ciblé de 30 minutes', 35.00, 'séance'),
(4, 'Location vélo', 'Vélo tout terrain pour la journée', 20.00, 'jour'),
(5, 'Stage yoga', 'Cours de yoga matinal', 25.00, 'séance');

-- Paramètres système
INSERT INTO parametres (cle, valeur, description, type) VALUES
('nom_etablissement', 'La Boissière', 'Nom de l\'établissement', 'string'),
('adresse_etablissement', '123 Route de la Boissière, 84000 Avignon', 'Adresse complète', 'string'),
('telephone_etablissement', '04 90 12 34 56', 'Numéro de téléphone', 'string'),
('email_etablissement', 'contact@laboissiere.fr', 'Adresse email', 'string'),
('siret', '12345678901234', 'Numéro SIRET', 'string'),
('taux_tva_defaut', '0', 'Taux de TVA par défaut (%)', 'number'),
('delai_paiement_defaut', '30', 'Délai de paiement par défaut (jours)', 'number'),
('heure_arrivee_defaut', '15:00', 'Heure d\'arrivée par défaut', 'string'),
('heure_depart_defaut', '11:00', 'Heure de départ par défaut', 'string');

-- Triggers pour mettre à jour automatiquement les montants

DELIMITER //

-- Trigger pour calculer le montant total des réservations
CREATE TRIGGER tr_reservation_montant_total 
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    SET NEW.nombre_nuitees = DATEDIFF(NEW.date_depart, NEW.date_arrivee);
    SET NEW.montant_hebergement = NEW.nombre_nuitees * NEW.tarif_nuitee;
    SET NEW.montant_total = NEW.montant_hebergement;
END//

CREATE TRIGGER tr_reservation_montant_total_update
BEFORE UPDATE ON reservations
FOR EACH ROW
BEGIN
    SET NEW.nombre_nuitees = DATEDIFF(NEW.date_depart, NEW.date_arrivee);
    SET NEW.montant_hebergement = NEW.nombre_nuitees * NEW.tarif_nuitee;
    -- Recalculer le montant total en ajoutant les ventes
    SET NEW.montant_total = NEW.montant_hebergement + IFNULL((
        SELECT SUM(montant_total) 
        FROM ventes 
        WHERE reservation_id = NEW.id
    ), 0);
END//

-- Trigger pour mettre à jour le montant total de la réservation après ajout/suppression de vente
CREATE TRIGGER tr_vente_update_reservation
AFTER INSERT ON ventes
FOR EACH ROW
BEGIN
    UPDATE reservations 
    SET montant_total = montant_hebergement + IFNULL((
        SELECT SUM(montant_total) 
        FROM ventes 
        WHERE reservation_id = NEW.reservation_id
    ), 0)
    WHERE id = NEW.reservation_id;
END//

CREATE TRIGGER tr_vente_delete_update_reservation
AFTER DELETE ON ventes
FOR EACH ROW
BEGIN
    UPDATE reservations 
    SET montant_total = montant_hebergement + IFNULL((
        SELECT SUM(montant_total) 
        FROM ventes 
        WHERE reservation_id = OLD.reservation_id
    ), 0)
    WHERE id = OLD.reservation_id;
END//

-- Trigger pour calculer le montant des lignes de facture
CREATE TRIGGER tr_ligne_facture_montant
BEFORE INSERT ON lignes_facture
FOR EACH ROW
BEGIN
    SET NEW.montant_total = NEW.quantite * NEW.prix_unitaire;
END//

CREATE TRIGGER tr_ligne_facture_montant_update
BEFORE UPDATE ON lignes_facture
FOR EACH ROW
BEGIN
    SET NEW.montant_total = NEW.quantite * NEW.prix_unitaire;
END//

-- Trigger pour mettre à jour les montants de la facture
CREATE TRIGGER tr_facture_update_montants
AFTER INSERT ON lignes_facture
FOR EACH ROW
BEGIN
    DECLARE total_ht DECIMAL(10,2);
    
    SELECT SUM(montant_total) INTO total_ht
    FROM lignes_facture 
    WHERE facture_id = NEW.facture_id;
    
    UPDATE factures 
    SET montant_ht = total_ht,
        montant_tva = total_ht * (taux_tva / 100),
        montant_ttc = total_ht + (total_ht * (taux_tva / 100))
    WHERE id = NEW.facture_id;
END//

CREATE TRIGGER tr_facture_update_montants_delete
AFTER DELETE ON lignes_facture
FOR EACH ROW
BEGIN
    DECLARE total_ht DECIMAL(10,2);
    
    SELECT IFNULL(SUM(montant_total), 0) INTO total_ht
    FROM lignes_facture 
    WHERE facture_id = OLD.facture_id;
    
    UPDATE factures 
    SET montant_ht = total_ht,
        montant_tva = total_ht * (taux_tva / 100),
        montant_ttc = total_ht + (total_ht * (taux_tva / 100))
    WHERE id = OLD.facture_id;
END//

DELIMITER ;

-- Vues utiles pour les rapports

-- Vue des réservations avec informations client et chambre
CREATE VIEW v_reservations_details AS
SELECT 
    r.*,
    CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
    c.email as client_email,
    c.telephone as client_telephone,
    ch.nom as chambre_nom,
    ch.capacite as chambre_capacite
FROM reservations r
JOIN clients c ON r.client_id = c.id
JOIN chambres ch ON r.chambre_id = ch.id;

-- Vue des ventes avec détails
CREATE VIEW v_ventes_details AS
SELECT 
    v.*,
    p.nom as produit_nom,
    p.unite as produit_unite,
    cat.nom as categorie_nom,
    r.date_arrivee,
    r.date_depart,
    CONCAT(c.nom, ' ', c.prenom) as client_nom_complet
FROM ventes v
JOIN produits p ON v.produit_id = p.id
JOIN categories_produits cat ON p.categorie_id = cat.id
JOIN reservations r ON v.reservation_id = r.id
JOIN clients c ON r.client_id = c.id;

-- Vue des factures avec détails client
CREATE VIEW v_factures_details AS
SELECT 
    f.*,
    CONCAT(c.nom, ' ', c.prenom) as client_nom_complet,
    c.email as client_email,
    c.adresse as client_adresse,
    c.code_postal as client_code_postal,
    c.ville as client_ville
FROM factures f
JOIN clients c ON f.client_id = c.id;