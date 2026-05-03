<?php
ob_start();
session_start();
require_once 'config.php';

if (isset($_SESSION['admin'])) {
    header("Location: accueil.php");
    exit();
}

$message_erreur = ""; 

if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data && $_POST['password'] === $user_data['password']) {
        $_SESSION['admin'] = $user_data['username'];
        header("Location: accueil.php");
        exit();
    } else {
        $message_erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 350px; }
        h2 { margin-top: 0; color: #111827; text-align: center; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #e5e7eb; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; background: #2563eb; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        button:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Connexion</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Utilisateur" required autofocus>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
    </div>
    <?php if ($message_erreur): ?><script>alert("<?= $message_erreur ?>");</script><?php endif; ?>
</body>
</html>