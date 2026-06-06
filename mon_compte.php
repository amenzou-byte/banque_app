<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
exigerConnexion();

// Banquiers redirigés vers leur propre tableau de bord
if (estBanquier()) redirect('dashboard.php');

$user = $_SESSION['utilisateur'];

// CORRECTION : requête propre au lieu du double-prepare cassé
$stmt = $pdo->prepare("SELECT * FROM compte WHERE user_id = ? ORDER BY id");
$stmt->execute([$user['id']]);
$mesComptes = $stmt->fetchAll();

// CORRECTION : date_inscription stockée en session depuis login.php
// Fallback si ancienne session sans date_inscription
$dateInscription = $user['date_inscription'] ?? null;
?>
<?php include 'includes/header.php'; ?>
<h2>👤 Mon profil</h2>
<div class="card profil-card">
    <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p>
        <strong>Membre depuis :</strong>
        <?= $dateInscription
            ? date('d/m/Y', strtotime($dateInscription))
            : '<em>Information non disponible</em>' ?>
    </p>
</div>

<h3 style="margin: 2rem 0 1rem">🏦 Mes comptes</h3>
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
            <a href="history.php?compte_id=<?= (int)$c['id'] ?>" class="btn-history">📜 Historique</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>