<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerBanquier();

$totalComptes = getTotalComptes($pdo);
$sommeSoldes  = getSommeSoldes($pdo);
$totalOps     = getTotalOperations($pdo);
$comptes      = getComptes($pdo);
$nbClients    = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role = 'client'")->fetchColumn();

// Pré-charger tous les utilisateurs en une seule requête (évite N+1)
$utilisateurs = [];
$stmtUsers = $pdo->query("SELECT id, nom FROM utilisateur");
foreach ($stmtUsers->fetchAll() as $u) {
    $utilisateurs[$u['id']] = $u['nom'];
}
?>
<?php include 'includes/header.php'; ?>
<div class="stats">
    <div class="card">
        <h3>📊 Clients</h3>
        <div class="number"><?= $nbClients ?></div>
    </div>
    <div class="card">
        <h3>🏦 Comptes</h3>
        <div class="number"><?= $totalComptes ?></div>
    </div>
    <div class="card">
        <h3>💰 Total soldes</h3>
        <div class="number"><?= formatMontant($sommeSoldes) ?></div>
    </div>
    <div class="card">
        <h3>🔄 Opérations</h3>
        <div class="number"><?= $totalOps ?></div>
    </div>
</div>

<h2>📋 Tous les comptes</h2>
<?php if (empty($comptes)): ?>
    <p>Aucun compte enregistré.</p>
<?php else: ?>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>N° compte</th>
            <th>Nom</th>
            <th>Titulaire</th>
            <th>Solde</th>
            <th>Client</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($comptes as $c): ?>
        <?php
        // Correction bug critique : utiliser le tableau pré-chargé, pas un double prepare()
        $nomClient = $utilisateurs[$c['user_id']] ?? '—';
        ?>
        <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= htmlspecialchars($c['numero_compte']) ?></td>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= htmlspecialchars($c['titulaire']) ?></td>
            <td class="<?= classeSolde($c['solde']) ?>"><?= formatMontant($c['solde']) ?></td>
            <td><?= htmlspecialchars($nomClient) ?></td>
            <td class="actions">
                <a href="edit.php?id=<?= (int)$c['id'] ?>" class="btn-edit" title="Modifier">✏️</a>
                <a href="delete.php?id=<?= (int)$c['id'] ?>" class="btn-delete" title="Supprimer"
                   onclick="return confirm('Supprimer définitivement ce compte ?')">🗑️</a>
                <a href="deposit.php?id=<?= (int)$c['id'] ?>" class="btn-deposit" title="Dépôt">💰</a>
                <a href="withdraw.php?id=<?= (int)$c['id'] ?>" class="btn-withdraw" title="Retrait">💸</a>
                <a href="transfer.php?src=<?= (int)$c['id'] ?>" class="btn-transfer" title="Virement">🔄</a>
                <a href="history.php?compte_id=<?= (int)$c['id'] ?>" class="btn-history" title="Historique">📜</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<div style="margin-top:1.5rem">
    <a href="create.php" class="btn-action">➕ Nouveau compte</a>
</div>
<?php include 'includes/footer.php'; ?>