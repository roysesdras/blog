<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirige vers la page de connexion si non connecté
    exit();
}

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

// Récupération des catégories
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);

// Récupérer l'ID de l'article à modifier
if (!isset($_GET['id'])) {
    die('ID d\'article non spécifié');
}

$article_id = $_GET['id'];

// Récupérer l'article à modifier
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // Si l'utilisateur est admin, récupérer l'article sans condition d'auteur
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
} else {
    // Si l'utilisateur est un utilisateur classique, vérifier qu'il est l'auteur de l'article
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND auteur = ?");
    $stmt->execute([$article_id, $_SESSION['username']]);
}

$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Article introuvable ou vous n\'êtes pas autorisé à modifier cet article');
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $date_publication = $_POST['date_publication'];
    $categorie = $_POST['categorie'];

    $image_url = $article['image_upload']; // Garder l'image actuelle par défaut

    // Traitement de l'upload de l'image
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['image_upload']['name']);
        $file_path = $upload_dir . $file_name;

        // Validation : vérifier l'extension du fichier
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            die('Type de fichier non autorisé. Seules les images JPG, JPEG, PNG et GIF sont acceptées.');
        }

        // Déplacer le fichier uploadé
        if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $file_path)) {
            $image_url = str_replace('../', '', $file_path); // Stocker un chemin relatif
        } else {
            die('Échec du téléchargement de l\'image.');
        }
    }

    // Mise à jour de l'article dans la base de données
    $update_stmt = $pdo->prepare("UPDATE articles SET titre = ?, contenu = ?, date_publication = ?, categorie = ?, image_upload = ? WHERE id = ?");
    $update_stmt->execute([$titre, $contenu, $date_publication, $categorie, $image_url, $article_id]);

    header('Location: admin_dashboard.php'); // Redirige vers le tableau de bord après modification
    exit();
}
?>



<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
<title><?php echo htmlspecialchars($article['titre']); ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<link rel="canonical" href="">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
<link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../blog.css">
    <style>
        .article-header {
            text-align: left;
            margin-bottom: 20px;
            margin-top: 10px;
        }
        .article-image {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .article-content {
            line-height: 1.6;
        }
        .meta-info {
            font-size: 0.9em;
            color: gray;
        }
    </style>
</head>
<body data-bs-theme="dark"> 
    <div class="container">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
            <div class="article-header">
                <h1><?php echo htmlspecialchars($article['titre']); ?></h1>
                    <p class="meta-info">Publié le <?php echo date('d M Y à H:i', strtotime($article['date_publication'])); ?> par <span class="text-info"> <?php echo htmlspecialchars($article['auteur']); ?></span> </p>
                </div>

                <?php if (!empty($article['image_upload'])): ?>
                    <img src="<?php echo '../' . htmlspecialchars($article['image_upload']); ?>" alt="Image de l'article" class="article-image">
                <?php endif; ?>

                <div class="article-content">
                    <p><?php echo nl2br(($article['contenu'])); ?></p>
                </div>

                <?php 
                    // Affichage des commentaires
                    $stmt = $pdo->prepare("SELECT * FROM commentaires WHERE article_id = :article_id ORDER BY created_at DESC");
                    $stmt->execute(['article_id' => $article_id]);
                    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($commentaires) {
                        echo '<h4 class="pt-4">Commentaires :</h4>';
                        foreach ($commentaires as $commentaire) {
                            echo '<div class="comment pt-2">';
                            echo '<h5 class="text-info">' . htmlspecialchars($commentaire['nom']) . '</h5>';
                            echo '<p>' . nl2br(htmlspecialchars($commentaire['commentaire'])) . '</p>';
                            echo '<hr>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Aucun commentaire pour cet article.</p>';
                    }

                    // Formulaire de commentaire
                    echo '<div class="comment-section pt-3">';
                    echo '<form method="POST" action="traiter_commentaire.php">';
                    echo '<input type="hidden" name="article_id" value="' . htmlspecialchars($article_id) . '">';
                    echo '<div class="mb-3">';
                    echo '<label for="nom" class="form-label"></label>';
                    echo '<input type="text" class="form-control" id="nom" name="nom" required placeholder="Pseudo">';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="commentaire" class="form-label"></label>';
                    echo '<textarea class="form-control" id="commentaire" name="commentaire" rows="3" required placeholder="votre commentaire ici..."></textarea>';
                    echo '</div>';
                    echo '<button type="submit" class="btn btn-primary">Envoyer</button>';
                    echo '</form>';
                    echo '</div>';
                ?>
            </div>
            <div class="col-md-2"></div>
        </div>

    </div>
    
<footer class="py-2 text-center mt-4 text-body-secondary bg-body-tertiary">
  <p>Blog for <a href="https://getbootstrap.com/">Association Sterna Africa</a> directed by <a href="mailto:roys.esdras@outlook.com">RoysEsdras</a>.</p>
</footer>

<script src="../assets/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

