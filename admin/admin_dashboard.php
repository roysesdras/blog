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
    <style>
        .contenu-complet { display: none; }
    </style>
</head>
<body>
    <h1>Bienvenue, <?php echo $_SESSION['admin_username']; ?></h1>
    <a href="logout.php">Se déconnecter</a>

    <!-- Gestion des Articles -->
    <h2>Gestion des Articles</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Titre</th>
                <th>Contenu</th>
                <th>Auteur</th>
                <th>Date de Publication</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) : ?>
                <tr>
                    <td><?php echo $article['id']; ?></td>
                    <td><img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" width="100"></td>
                    <td><?php echo $article['titre']; ?></td>
                    <td>
                        <span class="contenu-preview">
                            <?php 
                            // Afficher les 500 premiers caractères du contenu
                            echo substr($article['contenu'], 0, 500); 
                            ?>
                            
                            <?php if (strlen($article['contenu']) > 500): ?>
                                <a href="#" class="lire-plus" data-id="<?php echo $article['id']; ?>">Lire plus</a>
                                <span class="contenu-complet" data-id="<?php echo $article['id']; ?>"><?php echo $article['contenu']; ?></span>
                                <a href="#" class="lire-moins" data-id="<?php echo $article['id']; ?>" style="display: none;">Lire moins</a>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td><?php echo $article['auteur']; ?></td>
                    <td><?php echo $article['date_publication']; ?></td>
                    <td><?php echo $article['status']; ?></td>
                    <td>
                        <!-- Lien vers la page de modification -->
                        <a href="edit_article.php?id=<?php echo $article['id']; ?>">Modifier</a>
                        <?php if ($article['status'] === 'en attente') : ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <button type="submit" name="action" value="publish">Publier</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            <button type="submit" name="action" value="delete">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Gestion des Catégories -->
    <h2>Gestion des Catégories</h2>
    <?php if (isset($category_message)): ?>
        <p style="color: green;"><?php echo $category_message; ?></p>
    <?php endif; ?>
    
    <!-- Formulaire d'ajout de catégorie -->
    <form method="POST">
        <label for="category_name">Nom de la Catégorie :</label>
        <input type="text" id="category_name" name="category_name" required>
        <button type="submit">Ajouter la Catégorie</button>
    </form>

    <!-- Liste des catégories -->
    <h3>Catégories existantes</h3>
    <table border="1">
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
                    <td><?php echo $category['name']; ?></td>
                    <td>
                        <a href="edit_category.php?id=<?php echo $category['id']; ?>">Modifier</a>
                        <form method="POST" action="delete_category.php" style="display: inline;">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const lirePlusLinks = document.querySelectorAll('.lire-plus');
        const lireMoinsLinks = document.querySelectorAll('.lire-moins');
        
        lirePlusLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                
                const id = link.getAttribute('data-id');
                const contenuComplet = document.querySelector(`.contenu-complet[data-id="${id}"]`);
                const lirePlus = link;
                const lireMoins = document.querySelector(`.lire-moins[data-id="${id}"]`);
                
                // Afficher le contenu complet
                contenuComplet.style.display = 'inline';
                lirePlus.style.display = 'none';
                lireMoins.style.display = 'inline';
            });
        });
        
        lireMoinsLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                
                const id = link.getAttribute('data-id');
                const contenuComplet = document.querySelector(`.contenu-complet[data-id="${id}"]`);
                const lirePlus = document.querySelector(`.lire-plus[data-id="${id}"]`);
                const lireMoins = link;
                
                // Réduire le contenu
                contenuComplet.style.display = 'none';
                lirePlus.style.display = 'inline';
                lireMoins.style.display = 'none';
            });
        });
    });
    </script>
</body>
</html>
