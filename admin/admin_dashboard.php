<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$host = 'localhost';
$db = 'blog_sterna';
$user = 'roys_web';
$pass = '@roys';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer une session
session_start();

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Traitement des articles
if (isset($_POST['action']) && isset($_POST['article_id'])) {
    $article_id = $_POST['article_id'];
    $action = $_POST['action'];

    if ($action === 'publish') {
        $sql = "UPDATE articles SET status = 'publié' WHERE id = ?";
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM articles WHERE id = ?";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$article_id]);
}

// Traitement de l'ajout de catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $created_by = $_SESSION['admin_id']; // ID de l'admin connecté

        // Insérer la nouvelle catégorie dans la base de données
        $sql = "INSERT INTO categories (name, created_by) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_name, $created_by]);

        $category_message = "Catégorie ajoutée avec succès.";
    } else {
        $category_message = "Le nom de la catégorie est obligatoire.";
    }
}

// Récupérer les articles
$articles = $pdo->query("SELECT * FROM articles ORDER BY date_publication DESC")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        header a {
            transition: all 0.3s ease;
        }

        header a:hover {
            background-color: #e0e0e0;
            color: #000;
        }

        .table-container {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
        }

        .table {
            color: #e0e0e0;
        }

        .table thead {
            background-color: #343a40;
        }

        .table tbody tr {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .table tbody tr:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            background-color: #252525;
        }

        .content-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .badge {
            border-radius: 12px;
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body data-bs-theme="dark">
    <header class="text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Bienvenue, <?php echo $_SESSION['admin_username']; ?></h1>
        </div>
    </header>

    <div class="container my-4">
        <!-- Gestion des Articles -->
        <div class="table-container mb-5">
            <h2 class="mb-3">Gestion des Articles</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Titre</th>
                            <th>Contenu</th>
                            <th class="d-none d-md-table-cell">Auteur</th>
                            <th>D.Publ</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article) : ?>
                            <tr>
                                <td><?php echo $article['id']; ?></td>
                                <td><img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" class="img-fluid" style="max-width: 80px; border-radius: 5px;"></td>
                                <td><?php echo htmlspecialchars($article['titre']); ?></td>
                                <td class="content-preview">
                                    <?php echo substr($article['contenu'], 0, 100); ?>...
                                    <a href="#" class="text-primary" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($article['contenu']); ?>">Lire tout</a>
                                </td>
                                <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($article['auteur']); ?></td>
                                <td><?php echo $article['date_publication']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $article['status'] === 'publié' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="edit_article.php?id=<?php echo $article['id']; ?>" class="dropdown-item">Modifier</a></li>
                                            <?php if ($article['status'] === 'en attente') : ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                        <button type="submit" name="action" value="publish" class="dropdown-item">Publier</button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                                    <button type="submit" name="action" value="delete" class="dropdown-item text-danger">Supprimer</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gestion des Catégories -->
        <div class="table-container">
            <h2 class="mb-3" id="category">Gestion des Catégories</h2>
            <form method="POST" class="mb-3">
                <div class="input-group">
                    <input type="text" id="category_name" name="category_name" class="form-control bg-dark" placeholder="Nom de la Catégorie" required>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category) : ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning me-3">Modifier</a>
                                        <form method="POST" action="delete_category.php" class="d-inline">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="logout.php" class="btn btn-light mt-4 mb-4 float-end">Se déconnecter</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activer les tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    </script>
</body>
</html>
