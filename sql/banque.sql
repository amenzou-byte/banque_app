CREATE DATABASE IF NOT EXISTS banque_app_v2;
USE banque_app_v2;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('client', 'banquier') DEFAULT 'client',
    date_inscription DATETIME NOT NULL
);

-- Table des comptes (avec user_id)
CREATE TABLE IF NOT EXISTS compte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_compte VARCHAR(50) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    titulaire VARCHAR(100) NOT NULL,
    solde DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    date_creation DATETIME NOT NULL,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE SET NULL
);

-- Table des opérations
CREATE TABLE IF NOT EXISTS operation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('depot', 'retrait', 'virement_emetteur', 'virement_recepteur') NOT NULL,
    compte_id INT NOT NULL,
    contrepartie VARCHAR(50) DEFAULT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_operation DATETIME NOT NULL,
    FOREIGN KEY (compte_id) REFERENCES compte(id) ON DELETE CASCADE
);

-- Utilisateurs de test (mot de passe = 123456)
INSERT INTO utilisateur (nom, email, mot_de_passe, role, date_inscription) VALUES
('Ahmed Alqaab', 'ahmed@example.com', '$2y$10$kNCIh9XgQTRpuAzThJnkau7tsD7Gpiol58etqXhCn7/lg9xHfP8mu', 'client', NOW()),
('Fatima Zahra', 'fatima@example.com', '$2y$10$kNCIh9XgQTRpuAzThJnkau7tsD7Gpiol58etqXhCn7/lg9xHfP8mu', 'client', NOW()),
('Admin Banque', 'admin@banque.com', '$2y$10$kNCIh9XgQTRpuAzThJnkau7tsD7Gpiol58etqXhCn7/lg9xHfP8mu', 'banquier', NOW());

-- Comptes
INSERT INTO compte (numero_compte, nom, titulaire, solde, date_creation, user_id) VALUES
('CPF001', 'Compte courant', 'Ahmed Alqaab', 5000.53, NOW(), 1),
('CPF002', 'Compte épargne', 'Fatima Zahra', 2200.50, NOW(), 2),
('CPF003', 'Compte professionnel', 'Youssef Benali', 1800.00, NOW(), 3);

-- Opérations initiales
INSERT INTO operation (type, compte_id, contrepartie, montant, date_operation) VALUES
('depot', 1, NULL, 5000.53, NOW()),
('depot', 2, NULL, 2200.50, NOW()),
('depot', 3, NULL, 1800.00, NOW());