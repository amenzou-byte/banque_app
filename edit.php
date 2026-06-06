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
        redirect("create.php");
    }

    $numero  = trim($_POST['numero_compte'] ?? '');
    $nom     = trim($_POST['nom'] ?? '');
    $titulaire = trim($_POST['titulaire'] ?? '');

    if (empty($nom) || empty($titulaire)) {
        setFlash("Nom et titulaire sont requis", "error");
        redirect("edit.php?id=$id");
    }

    $pdo->prepare("UPDATE compte SET nom = ?, titulaire = ? WHERE id = ?")
        ->execute([$nom, $titulaire, $id]);
    setFlash("Compte modifié avec succès");
    redirect('dashboard.php');
}
?>
<?php include 'includes/header.php'; ?>
<h2>✏️ Modifier le compte</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div class="form-group">
        <label>Numéro de compte</label>
        <input type="text" value="<?= htmlspecialchars($compte['numero_compte']) ?>" disabled>
    </div>
    <div class="form-group">
        <label for="nom">Nom du compte *</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($compte['nom']) ?>" required>
    </div>
    <div class="form-group">
        <label for="titulaire">Titulaire *</label>
        <input type="text" id="titulaire" name="titulaire" value="<?= htmlspecialchars($compte['titulaire']) ?>" required>
    </div>
    <div class="form-actions">
        <button type="submit">💾 Enregistrer</button>
        <a href="dashboard.php"><button type="button" class="cancel">Annuler</button></a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>