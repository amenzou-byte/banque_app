<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash("Requête invalide (CSRF)", "error");
        redirect('create.php');
    }

    $numero    = trim($_POST['numero_compte'] ?? '');
    $nom       = trim($_POST['nom'] ?? '');
    $titulaire = trim($_POST['titulaire'] ?? '');
    $solde     = floatval($_POST['solde_initial'] ?? 0);

    if (empty($numero) || empty($nom) || empty($titulaire) || $solde < 0) {
        setFlash("Tous les champs sont requis et le solde doit être ≥ 0", "error");
        redirect('create.php');
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            "INSERT INTO compte (numero_compte, nom, titulaire, solde, date_creation, user_id)
             VALUES (?, ?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([$numero, $nom, $titulaire, $solde, getUtilisateurId()]);
        $compteId = $pdo->lastInsertId();

        if ($solde > 0) {
            $pdo->prepare(
                "INSERT INTO operation (type, compte_id, contrepartie, montant, date_operation)
                 VALUES ('depot', ?, NULL, ?, NOW())"
            )->execute([$compteId, $solde]);
        }
        $pdo->commit();
        setFlash("Compte créé avec succès !");
        redirect('dashboard.php');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $msg = ($e->errorInfo[1] == 1062) ? "Ce numéro de compte existe déjà" : "Erreur lors de la création";
        setFlash($msg, "error");
        redirect('create.php');
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>➕ Créer un compte</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div class="form-group">
        <label for="numero_compte">Numéro de compte *</label>
        <input type="text" id="numero_compte" name="numero_compte" required placeholder="CPF004">
    </div>
    <div class="form-group">
        <label for="nom">Nom du compte *</label>
        <input type="text" id="nom" name="nom" required placeholder="Compte courant">
    </div>
    <div class="form-group">
        <label for="titulaire">Titulaire *</label>
        <input type="text" id="titulaire" name="titulaire" required placeholder="Nom complet">
    </div>
    <div class="form-group">
        <label for="solde_initial">Solde initial (DH)</label>
        <input type="number" id="solde_initial" name="solde_initial" step="0.01" min="0" value="0" required>
    </div>
    <div class="form-actions">
        <button type="submit">✅ Créer</button>
        <a href="dashboard.php"><button type="button" class="cancel">Annuler</button></a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>