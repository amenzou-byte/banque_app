<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

$id     = (int)($_GET['id'] ?? 0);
$compte = getCompteById($pdo, $id);
if (!$compte) { setFlash("Compte introuvable", "error"); redirect('dashboard.php'); }
verifierAccesCompte($pdo, $id);

// La suppression doit se faire via POST pour éviter les attaques CSRF par simple lien
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Afficher une page de confirmation
?>
<?php include 'includes/header.php'; ?>
<h2>🗑️ Confirmer la suppression</h2>
<div class="card" style="max-width:500px;margin:0 auto;padding:2rem">
    <p>Êtes-vous sûr de vouloir supprimer le compte <strong><?= htmlspecialchars($compte['numero_compte']) ?></strong>
    au nom de <strong><?= htmlspecialchars($compte['titulaire']) ?></strong> ?</p>
    <p>Solde actuel : <span class="<?= classeSolde($compte['solde']) ?>"><?= formatMontant($compte['solde']) ?></span></p>
    <?php if ($compte['solde'] > 0): ?>
        <p class="flash error">⚠️ Impossible de supprimer un compte avec un solde positif.</p>
        <a href="dashboard.php"><button type="button" class="cancel">Retour</button></a>
    <?php else: ?>
    <form method="post" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <button type="submit" class="btn-delete-btn">🗑️ Confirmer la suppression</button>
        <a href="dashboard.php"><button type="button" class="cancel">Annuler</button></a>
    </form>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<?php
    exit;
}

// Vérification CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash("Requête invalide (CSRF)", "error");
    redirect('dashboard.php');
}

// Re-lire le compte pour garantir la fraîcheur des données
$compte = getCompteById($pdo, $id);
if (!$compte) { setFlash("Compte introuvable", "error"); redirect('dashboard.php'); }

if ($compte['solde'] > 0) {
    setFlash("Impossible de supprimer : solde positif (" . formatMontant($compte['solde']) . ")", "error");
    redirect('dashboard.php');
}

$pdo->prepare("DELETE FROM compte WHERE id = ?")->execute([$id]);
setFlash("Compte supprimé avec succès");
redirect('dashboard.php');
?>