<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

$uid = getUtilisateurId();

// Correction : utiliser getComptes() directement (le code mort des lignes 8-9 est supprimé)
$mesComptes  = getComptes($pdo);
$totalSoldes = getSommeSoldes($pdo);
$nbComptes   = count($mesComptes);

$stmtOps = $pdo->prepare(
    "SELECT o.*, c.numero_compte
     FROM operation o
     JOIN compte c ON o.compte_id = c.id
     WHERE c.user_id = ?
     ORDER BY o.date_operation DESC
     LIMIT 5"
);
$stmtOps->execute([$uid]);
$recentOps = $stmtOps->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<div class="stats">
    <div class="card">
        <h3>📊 Mes comptes</h3>
        <div class="number"><?= $nbComptes ?></div>
    </div>
    <div class="card">
        <h3>💰 Total soldes</h3>
        <div class="number"><?= formatMontant($totalSoldes) ?></div>
    </div>
    <div class="card">
        <h3>🔄 Dernières opérations</h3>
        <div class="number"><?= count($recentOps) ?></div>
    </div>
</div>

<h2>🏦 Mes comptes</h2>
<?php if (empty($mesComptes)): ?>
    <p>Vous n'avez aucun compte. <a href="create.php">Créer un compte</a></p>
<?php else: ?>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>N° compte</th>
            <th>Nom</th>
            <th>Titulaire</th>
            <th>Solde</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($mesComptes as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['numero_compte']) ?></td>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td><?= htmlspecialchars($c['titulaire']) ?></td>
        <td class="<?= classeSolde($c['solde']) ?>"><?= formatMontant($c['solde']) ?></td>
        <td class="actions">
            <a href="deposit.php?id=<?= (int)$c['id'] ?>" class="btn-deposit">💰 Dépôt</a>
            <a href="withdraw.php?id=<?= (int)$c['id'] ?>" class="btn-withdraw">💸 Retrait</a>
            <a href="transfer.php?src=<?= (int)$c['id'] ?>" class="btn-transfer">🔄 Virement</a>
            <a href="history.php?compte_id=<?= (int)$c['id'] ?>" class="btn-history">📜 Historique</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<h2>📋 Dernières opérations</h2>
<?php if (empty($recentOps)): ?>
    <p>Aucune opération récente.</p>
<?php else: ?>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Compte</th>
            <th>Contrepartie</th>
            <th>Montant</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($recentOps as $op): ?>
    <tr>
        <td><?= date('d/m/Y H:i', strtotime($op['date_operation'])) ?></td>
        <td><?= match($op['type']) {
            'depot'               => '✅ Dépôt',
            'retrait'             => '💸 Retrait',
            'virement_emetteur'   => '🔄 Virement émis',
            'virement_recepteur'  => '🔄 Virement reçu',
            default               => htmlspecialchars($op['type']),
        } ?></td>
        <td><?= htmlspecialchars($op['numero_compte']) ?></td>
        <td><?= htmlspecialchars($op['contrepartie'] ?? '---') ?></td>
        <td class="<?= ($op['type'] === 'depot' || $op['type'] === 'virement_recepteur') ? 'positif' : 'negatif' ?>">
            <?= ($op['type'] === 'depot' || $op['type'] === 'virement_recepteur') ? '+' : '-' ?>
            <?= formatMontant($op['montant']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p style="margin-top:1rem"><a href="history.php">Voir tout l'historique →</a></p>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>