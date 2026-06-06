<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Banque PDO — Application bancaire sécurisée">
    <title>Banque PDO</title>
    <!-- Google Fonts : Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-brand">
            <h1>🏦 Banque PDO</h1>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false">☰</button>
        <nav id="mainNav">
            <?php if (estConnecte()): ?>
                <span class="nav-user">
                    👤 <?= htmlspecialchars(getUtilisateurNom()) ?>
                    <em class="role-badge <?= estBanquier() ? 'badge-banquier' : 'badge-client' ?>">
                        <?= estBanquier() ? 'Banquier' : 'Client' ?>
                    </em>
                </span>
                <a href="dashboard.php">🏠 Tableau de bord</a>
                <?php if (estBanquier()): ?>
                    <a href="admin_dashboard.php">📊 Admin</a>
                    <a href="create.php">➕ Nouveau compte</a>
                    <a href="transfer.php">🔄 Virement</a>
                    <a href="history.php">📜 Historique</a>
                <?php else: ?>
                    <a href="create.php">➕ Nouveau compte</a>
                    <a href="transfer.php">🔄 Virement</a>
                    <a href="history.php">📜 Historique</a>
                    <a href="mon_compte.php">👤 Mon compte</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-logout">🚪 Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>