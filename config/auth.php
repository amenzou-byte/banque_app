<?php
// Protection contre le double appel de session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estConnecte() { return isset($_SESSION['utilisateur']); }
function estBanquier() { return estConnecte() && $_SESSION['utilisateur']['role'] === 'banquier'; }
function getUtilisateurId() { return $_SESSION['utilisateur']['id'] ?? null; }
function getUtilisateurNom() { return $_SESSION['utilisateur']['nom'] ?? ''; }

function exigerConnexion() {
    if (!estConnecte()) {
        $_SESSION['flash'] = ['message' => 'Veuillez vous connecter', 'type' => 'error'];
        header('Location: login.php');
        exit;
    }
}

function exigerBanquier() {
    exigerConnexion();
    if (!estBanquier()) {
        $_SESSION['flash'] = ['message' => 'Accès réservé aux banquiers', 'type' => 'error'];
        header('Location: dashboard.php');
        exit;
    }
}
?>