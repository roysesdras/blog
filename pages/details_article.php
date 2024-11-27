<?php
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

// Récupération de l'article à afficher
if (!isset($_GET['id'])) {
    die('ID d\'article non spécifié.');
}

$article_id = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT titre, contenu, date_publication, auteur, image_upload
    FROM articles
    WHERE id = ? AND status = 'approuvé' AND published = 1
");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Article introuvable ou non approuvé.');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['titre']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .article-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .article-image {
            display: block;
            margin: 0 auto;
            max-width: 100%;
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
<body>
    <div class="article-header">
        <h1><?php echo htmlspecialchars($article['titre']); ?></h1>
        <p class="meta-info">Publié le <?php echo date('d M Y à H:i', strtotime($article['date_publication'])); ?> par <?php echo htmlspecialchars($article['auteur']); ?></p>
    </div>

    <?php if (!empty($article['image_upload'])): ?>
        <img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" class="article-image">
    <?php endif; ?>

    <div class="article-content">
        <p><?php echo nl2br(htmlspecialchars($article['contenu'])); ?></p>
    </div>
</body>
</html>
