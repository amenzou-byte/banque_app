<?php
// session_start() est géré par auth.php (inclus ci-dessous)
require_once __DIR__ . '/../config/auth.php';

function redirect($url) { header("Location: $url"); exit; }
function setFlash($message, $type = 'success') { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function getFlash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function formatMontant($m) { return number_format((float)$m, 2, ',', ' ') . ' DH'; }
function classeSolde($s) { return $s < 0 ? 'negatif' : ($s == 0 ? 'zero' : 'positif'); }

// ── CSRF ──────────────────────────────────────────────────────────────────────
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// ── Comptes ───────────────────────────────────────────────────────────────────
function getComptes($pdo) {
    if (estBanquier()) return $pdo->query("SELECT * FROM compte ORDER BY id")->fetchAll();
    $stmt = $pdo->prepare("SELECT * FROM compte WHERE user_id = ? ORDER BY id");
    $stmt->execute([getUtilisateurId()]);
    return $stmt->fetchAll();
}

function getCompteById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM compte WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getOperationsByCompte($pdo, $compte_id) {
    $stmt = $pdo->prepare("SELECT * FROM operation WHERE compte_id = ? ORDER BY date_operation DESC");
    $stmt->execute([$compte_id]);
    return $stmt->fetchAll();
}

function getTotalComptes($pdo) {
    if (estBanquier()) return $pdo->query("SELECT COUNT(*) FROM compte")->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM compte WHERE user_id = ?");
    $stmt->execute([getUtilisateurId()]);
    return $stmt->fetchColumn();
}

function getSommeSoldes($pdo) {
    if (estBanquier()) return $pdo->query("SELECT SUM(solde) FROM compte")->fetchColumn() ?? 0;
    $stmt = $pdo->prepare("SELECT SUM(solde) FROM compte WHERE user_id = ?");
    $stmt->execute([getUtilisateurId()]);
    return $stmt->fetchColumn() ?? 0;
}

function getTotalOperations($pdo) {
    if (estBanquier()) return $pdo->query("SELECT COUNT(*) FROM operation")->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM operation o JOIN compte c ON o.compte_id = c.id WHERE c.user_id = ?");
    $stmt->execute([getUtilisateurId()]);
    return $stmt->fetchColumn();
}

function verifierAccesCompte($pdo, $compteId) {
    if (estBanquier()) return true;
    $stmt = $pdo->prepare("SELECT user_id FROM compte WHERE id = ?");
    $stmt->execute([$compteId]);
    $c = $stmt->fetch();
    if (!$c || $c['user_id'] != getUtilisateurId()) {
        setFlash("Accès refusé à ce compte", "error");
        redirect('dashboard.php');
    }
    return true;
}
?>