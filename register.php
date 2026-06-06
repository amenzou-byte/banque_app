<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (estConnecte()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash("Requête invalide (CSRF)", "error");
        redirect('register.php');
    }

    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($email) || empty($password)) {
        setFlash("Tous les champs sont obligatoires", "error");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash("Adresse email invalide", "error");
    } elseif ($password !== $confirm) {
        setFlash("Les mots de passe ne correspondent pas", "error");
    } elseif (strlen($password) < 6) {
        setFlash("Mot de passe minimum 6 caractères", "error");
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO utilisateur (nom, email, mot_de_passe, role, date_inscription)
                 VALUES (?, ?, ?, 'client', NOW())"
            );
            $stmt->execute([$nom, $email, $hashed]);
            setFlash("Inscription réussie ! Connectez-vous.");
            redirect('login.php');
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                setFlash("Cet email est déjà utilisé", "error");
            } else {
                setFlash("Erreur lors de l'inscription", "error");
            }
        }
    }
    redirect('register.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inscription à Banque PDO — Application bancaire sécurisée">
    <title>Inscription — Banque PDO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-auth">
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
            <div class="auth-card-header">
                <span class="auth-icon">✨</span>
                <h2>Créer un compte</h2>
                <p>Rejoignez Banque PDO gratuitement</p>
            </div>

            <div class="auth-card-body">
                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom"
                               required autocomplete="name"
                               placeholder="Jean Dupont">
                    </div>
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email"
                               required autocomplete="email"
                               placeholder="votre@email.com">
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe <small style="font-weight:400;color:#94a3b8">(min. 6 caractères)</small></label>
                        <input type="password" id="password" name="password"
                               required autocomplete="new-password"
                               placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               required autocomplete="new-password"
                               placeholder="••••••••">
                    </div>

                    <button type="submit">🚀 S'inscrire</button>
                </form>

                <div class="auth-footer">
                    Déjà inscrit ?
                    <a href="login.php">Se connecter</a>
                </div>
            </div>
        </div>

        <p style="text-align:center;margin-top:1.5rem;color:rgba(255,255,255,0.5);font-size:0.82rem;">
            &copy; <?= date('Y') ?> Banque PDO — Application sécurisée
        </p>
    </main>
</body>
</html>