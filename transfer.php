<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

$comptes   = getComptes($pdo);
$source_id = (int)($_GET['src'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash("Requête invalide (CSRF)", "error");
        redirect('transfer.php');
    }

    $source  = (int)($_POST['source_id'] ?? 0);
    $cible   = (int)($_POST['cible_id']  ?? 0);
    $montant = floatval($_POST['montant'] ?? 0);

    if ($source === $cible) {
        setFlash("Les comptes source et cible doivent être différents", "error");
        redirect('transfer.php');
    }
    if ($montant <= 0) {
        setFlash("Le montant doit être supérieur à 0", "error");
        redirect('transfer.php');
    }

    try {
        $pdo->beginTransaction();

        // ── Vérification droits sur le compte source ─────────────────────────
        if (!estBanquier()) {
            $stmtCheck = $pdo->prepare("SELECT user_id FROM compte WHERE id = ?");
            $stmtCheck->execute([$source]);
            $userId = $stmtCheck->fetchColumn();
            // CORRECTION : utiliser une requête propre au lieu du double-prepare cassé
            if ($userId != getUtilisateurId()) {
                throw new Exception("Accès refusé au compte source");
            }
        }

        // ── Lire compte source avec verrou ────────────────────────────────────
        $stmtSrc = $pdo->prepare("SELECT numero_compte, solde FROM compte WHERE id = ? FOR UPDATE");
        $stmtSrc->execute([$source]);
        $src = $stmtSrc->fetch();
        // CORRECTION : requête propre au lieu du double-prepare cassé

        if (!$src) {
            throw new Exception("Compte source introuvable");
        }
        if ($src['solde'] < $montant) {
            throw new Exception("Solde insuffisant : disponible " . formatMontant($src['solde']));
        }

        // ── Lire compte cible avec verrou ─────────────────────────────────────
        $stmtCib = $pdo->prepare("SELECT numero_compte FROM compte WHERE id = ? FOR UPDATE");
        $stmtCib->execute([$cible]);
        $cib = $stmtCib->fetch();
        // CORRECTION : requête propre au lieu du double-prepare cassé

        if (!$cib) {
            throw new Exception("Compte cible introuvable");
        }

        // ── Effectuer le virement ─────────────────────────────────────────────
        $pdo->prepare("UPDATE compte SET solde = solde - ? WHERE id = ?")
            ->execute([$montant, $source]);
        $pdo->prepare("UPDATE compte SET solde = solde + ? WHERE id = ?")
            ->execute([$montant, $cible]);

        $pdo->prepare(
            "INSERT INTO operation (type, compte_id, contrepartie, montant, date_operation)
             VALUES ('virement_emetteur', ?, ?, ?, NOW())"
        )->execute([$source, $cib['numero_compte'], $montant]);

        $pdo->prepare(
            "INSERT INTO operation (type, compte_id, contrepartie, montant, date_operation)
             VALUES ('virement_recepteur', ?, ?, ?, NOW())"
        )->execute([$cible, $src['numero_compte'], $montant]);

        $pdo->commit();
        setFlash("Virement de " . formatMontant($montant) . " effectué avec succès");
        redirect('dashboard.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash("Erreur : " . $e->getMessage(), "error");
        redirect('transfer.php');
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>🔄 Virement</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div class="form-group">
        <label for="source_id">Compte source</label>
        <select id="source_id" name="source_id" required>
            <option value="">-- Choisir le compte source --</option>
            <?php foreach ($comptes as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ($source_id === (int)$c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['numero_compte']) ?> — <?= htmlspecialchars($c['titulaire']) ?>
                (<?= formatMontant($c['solde']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="cible_id">Compte cible</label>
        <select id="cible_id" name="cible_id" required>
            <option value="">-- Choisir le compte cible --</option>
            <?php foreach ($comptes as $c): ?>
            <option value="<?= (int)$c['id'] ?>">
                <?= htmlspecialchars($c['numero_compte']) ?> — <?= htmlspecialchars($c['titulaire']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="montant">Montant (DH)</label>
        <input type="number" id="montant" name="montant" step="0.01" min="0.01" required>
    </div>
    <div class="form-actions">
        <button type="submit">🔄 Valider le virement</button>
        <a href="dashboard.php"><button type="button" class="cancel">Annuler</button></a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>