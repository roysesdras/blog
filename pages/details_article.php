<?php
// Connexion à la base de données
$host = 'localhost';
$db = 'blog_sterna';
$user = 'roys_web';
$pass = '@roys';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération de l'article à afficher
if (!isset($_GET['id'])) {
    die('ID d\'article non spécifié.');
}

$article_id = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT titre, contenu, date_publication, auteur, image_upload
    FROM articles
    WHERE id = ? AND status = 'publié'
");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Article introuvable ou non approuvé.');
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
                    <p><?php echo nl2br(htmlspecialchars($article['contenu'])); ?></p>
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

