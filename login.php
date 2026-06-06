<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (estConnecte()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash("Requête invalide (CSRF)", "error");
        redirect('login.php');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash("Tous les champs sont obligatoires", "error");
        redirect('login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['utilisateur'] = [
            'id'               => $user['id'],
            'nom'              => $user['nom'],
            'email'            => $user['email'],
            'role'             => $user['role'],
            'date_inscription' => $user['date_inscription'],
        ];
        setFlash("Bienvenue, " . htmlspecialchars($user['nom']) . " !");
        redirect('dashboard.php');
    } else {
        setFlash("Email ou mot de passe incorrect", "error");
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion à Banque PDO — Application bancaire sécurisée">
    <title>Connexion — Banque PDO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-auth">
    <!-- Header minimal sans navigation pour les pages auth -->
    <header style="justify-content:center; height:52px;">
        <div class="header-brand">
            <h1>🏦 Banque PDO</h1>
        </div>
    </header>

    <main class="container">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>" role="alert"
                 style="max-width:440px;margin:0 auto 1rem;">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="auth-card">
            <!-- En-tête de la carte -->
            <div class="auth-card-header">
                <span class="auth-icon">🏦</span>
                <h2>Bienvenue</h2>
                <p>Connectez-vous à votre espace bancaire</p>
            </div>

            <!-- Corps -->
            <div class="auth-card-body">
                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email"
                               required autocomplete="email"
                               placeholder="votre@email.com">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password"
                               required autocomplete="current-password"
                               placeholder="••••••••">
                    </div>

                    <button type="submit">🔐 Se connecter</button>
                </form>

                <div class="auth-footer">
                    Pas encore de compte ?
                    <a href="register.php">Créer un compte</a>
                </div>

                <div class="auth-hint">
                    <strong>Comptes de démonstration :</strong><br>
                    👤 ahmed@example.com / <code>123456</code> — Client<br>
                    🏛️ admin@banque.com / <code>123456</code> — Banquier
                </div>
            </div>
        </div>

        <p style="text-align:center;margin-top:1.5rem;color:rgba(255,255,255,0.5);font-size:0.82rem;">
            &copy; <?= date('Y') ?> Banque PDO — Application sécurisée
        </p>
    </main>
</body>
</html>