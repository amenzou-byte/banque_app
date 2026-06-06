<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

$id     = (int)($_GET['id'] ?? 0);
$compte = getCompteById($pdo, $id);
if (!$compte) { setFlash("Compte introuvable", "error"); redirect('dashboard.php'); }
verifierAccesCompte($pdo, $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash("Requête invalide (CSRF)", "error");
        redirect("deposit.php?id=$id");
    }

    $montant = floatval($_POST['montant'] ?? 0);
    if ($montant <= 0) {
        setFlash("Le montant doit être supérieur à 0", "error");
        redirect("deposit.php?id=$id");
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE compte SET solde = solde + ? WHERE id = ?")
            ->execute([$montant, $id]);
        $pdo->prepare(
            "INSERT INTO operation (type, compte_id, contrepartie, montant, date_operation)
             VALUES ('depot', ?, NULL, ?, NOW())"
        )->execute([$id, $montant]);
        $pdo->commit();
        setFlash("Dépôt de " . formatMontant($montant) . " effectué avec succès");
        redirect('dashboard.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash("Erreur lors du dépôt", "error");
        redirect("deposit.php?id=$id");
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>💰 Dépôt</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div class="form-group">
        <label>Compte</label>
        <input type="text"
               value="<?= htmlspecialchars($compte['numero_compte']) ?> — <?= htmlspecialchars($compte['titulaire']) ?>"
               disabled>
    </div>
    <div class="form-group">
        <label>Solde actuel</label>
        <input type="text" value="<?= formatMontant($compte['solde']) ?>" disabled
               class="<?= classeSolde($compte['solde']) ?>">
    </div>
    <div class="form-group">
        <label for="montant">Montant à déposer (DH)</label>
        <input type="number" id="montant" name="montant" step="0.01" min="0.01" required>
    </div>
    <div class="form-actions">
        <button type="submit">✅ Valider le dépôt</button>
        <a href="dashboard.php"><button type="button" class="cancel">Annuler</button></a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>