<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

$comptes     = getComptes($pdo);
$selected_id = (int)($_GET['compte_id'] ?? 0);
$operations  = [];

if ($selected_id) {
    verifierAccesCompte($pdo, $selected_id);
    // CORRECTION : une seule requête JOIN pour éviter les N+1 queries dans la boucle
    $stmt = $pdo->prepare(
        "SELECT o.*, c.numero_compte
         FROM operation o
         JOIN compte c ON o.compte_id = c.id
         WHERE o.compte_id = ?
         ORDER BY o.date_operation DESC"
    );
    $stmt->execute([$selected_id]);
    $operations = $stmt->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>
<h2>📜 Historique des opérations</h2>

<form method="get" style="margin-bottom:2rem">
    <div class="form-group">
        <label for="compte_id">Filtrer par compte</label>
        <select id="compte_id" name="compte_id" onchange="this.form.submit()">
            <option value="">-- Tous les comptes --</option>
            <?php foreach ($comptes as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ($selected_id === (int)$c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['numero_compte']) ?> — <?= htmlspecialchars($c['titulaire']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<?php if ($selected_id && empty($operations)): ?>
    <p>Aucune opération pour ce compte.</p>
<?php elseif ($selected_id): ?>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Type</th>
            <th>N° Compte</th>
            <th>Contrepartie</th>
            <th>Montant</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($operations as $op): ?>
    <?php
        $isCredit = ($op['type'] === 'depot' || $op['type'] === 'virement_recepteur');
        $typeLabel = match($op['type']) {
            'depot'              => '✅ Dépôt',
            'retrait'            => '💸 Retrait',
            'virement_emetteur'  => '🔄 Virement émis',
            'virement_recepteur' => '🔄 Virement reçu',
            default              => htmlspecialchars($op['type']),
        };
    ?>
    <tr>
        <td><?= (int)$op['id'] ?></td>
        <td><?= date('d/m/Y H:i', strtotime($op['date_operation'])) ?></td>
        <td><?= $typeLabel ?></td>
        <td><?= htmlspecialchars($op['numero_compte']) ?></td>
        <!-- CORRECTION : plus d'appel getCompteById() dans la boucle (N+1 supprimé) -->
        <td><?= htmlspecialchars($op['contrepartie'] ?? '---') ?></td>
        <td class="<?= $isCredit ? 'positif' : 'negatif' ?>">
            <?= $isCredit ? '+' : '-' ?> <?= formatMontant($op['montant']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php else: ?>
    <p>Sélectionnez un compte pour afficher son historique.</p>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>