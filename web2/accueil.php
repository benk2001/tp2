<?php
ob_start();
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) { header("Location: index.php"); exit(); }

if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit(); }

// --- LOGIQUE CRUD ---

// AJOUTER
if (isset($_POST['ajouter'])) {
    $stmt = $pdo->prepare("INSERT INTO etudiants (nom, postnom, email) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['postnom'], $_POST['email']]);
    header("Location: accueil.php"); exit();
}

// MODIFIER (UPDATE)
if (isset($_POST['modifier'])) {
    $stmt = $pdo->prepare("UPDATE etudiants SET nom = ?, postnom = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST['nom'], $_POST['postnom'], $_POST['email'], $_POST['id']]);
    header("Location: accueil.php"); exit();
}

// SUPPRIMER
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: accueil.php"); exit();
}

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE nom LIKE ? OR postnom LIKE ? ORDER BY id DESC");
$stmt->execute(["%$search%", "%$search%"]);
$etudiants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --danger: #dc2626; --text: #1f2937; }
        body { font-family: 'Inter', sans-serif; background: #f9fafb; color: var(--text); margin: 0; display: flex; }
        
        /* Sidebar */
        .sidebar { width: 240px; background: #111827; color: white; height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); font-size: 1.5rem; margin-bottom: 30px; }
        .sidebar a { color: #9ca3af; text-decoration: none; display: block; padding: 10px 0; transition: 0.3s; }
        .sidebar a:hover { color: white; }

        /* Main Content */
        .main { margin-left: 240px; padding: 40px; width: 100%; }
        .card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f3f4f6; color: #6b7280; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 16px 12px; border-bottom: 1px solid #f3f4f6; }
        
        /* Buttons */
        .btn { padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 500; text-decoration: none; }
        .btn-add { background: var(--primary); color: white; }
        .btn-edit { background: #f3f4f6; color: var(--text); margin-right: 5px; }
        .btn-edit:hover { background: #e5e7eb; }
        .btn-del { color: var(--danger); background: #fee2e2; }

        /* Modal (Pop-up) */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>UniManager</h2>
    <p>Admin: <?= htmlspecialchars($_SESSION['admin']) ?></p>
    <a href="accueil.php">🏠 Dashboard</a>
    <a href="?logout=1" style="margin-top: 50px; color: #ef4444;">🔴 Déconnexion</a>
</div>

<div class="main">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Étudiants</h1>
        <form method="GET"><input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" style="width: 200px;"></form>
    </div>

    <!-- Formulaire d'ajout -->
    <div class="card">
        <h3 style="margin-top:0">Nouvel Étudiant</h3>
        <form method="POST" style="display: flex; gap: 10px;">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="postnom" placeholder="Postnom" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" name="ajouter" class="btn btn-add">Enregistrer</button>
        </form>
    </div>

    <!-- Tableau -->
    <div class="card">
        <table>
            <thead><tr><th>Étudiant</th><th>Email</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($e['nom']) ?></strong> <?= htmlspecialchars($e['postnom']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td>
                        <!-- Bouton Modifier avec passage de données en JavaScript -->
                        <button class="btn btn-edit" onclick="openEditModal(<?= $e['id'] ?>, '<?= addslashes($e['nom']) ?>', '<?= addslashes($e['postnom']) ?>', '<?= addslashes($e['email']) ?>')">Modifier</button>
                        <a href="?delete=<?= $e['id'] ?>" class="btn btn-del" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fenêtre Modale de Modification -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Modifier l'étudiant</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <input type="text" name="nom" id="edit_nom" required>
            <input type="text" name="postnom" id="edit_postnom" required>
            <input type="email" name="email" id="edit_email" required>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" name="modifier" class="btn btn-add">Sauvegarder</button>
                <button type="button" class="btn btn-edit" onclick="closeModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, nom, postnom, email) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nom').value = nom;
        document.getElementById('edit_postnom').value = postnom;
        document.getElementById('edit_email').value = email;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>

</body>
</html>