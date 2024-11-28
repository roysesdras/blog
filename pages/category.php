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

// Récupération de l'ID de la catégorie
if (!isset($_GET['id'])) {
    die('ID de catégorie non spécifié.');
}

$category_id = $_GET['id'];

// Récupération du nom de la catégorie
$stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die('Catégorie introuvable.');
}

$category_name = $category['name'];

// Récupération des articles de la catégorie
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS categorie_name 
    FROM articles a
    JOIN categories c ON a.categorie = c.id
    WHERE c.id = ? AND a.status = 'publié'
    ORDER BY a.date_publication DESC
");
$stmt->execute([$category_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>Articles de la catégorie : <?php echo htmlspecialchars($category_name); ?></title>
    <link rel="canonical" href="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../blog.css">
</head>

<style>
    .article-card {
    list-style: none; /* Supprime le style de liste */
    border-radius: 8px; /* Coins arrondis */
    margin-bottom: 15px; /* Espacement entre les cartes */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.article-card:hover {
    transform: translateY(-5px); /* Légère élévation au survol */
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1); /* Ombre légère */
}

.card-link {
    text-decoration: none; /* Supprime les soulignements */
    color: inherit; /* Utilise les couleurs héritées */
}

.article-image {
    width: 100px; /* Taille fixe pour l'image */
    height: 80px; /* Taille fixe pour l'image */
    border-radius: 4px; /* Coins légèrement arrondis */
    object-fit: cover; /* Coupe l'image pour s'adapter au cadre */
    flex-shrink: 0; /* Empêche la réduction sur petits écrans */
}

.article-content {
    flex: 1; /* Permet de s'étendre pour occuper l'espace restant */
}

.article-title {
    font-size: 1rem; /* Taille adaptée pour un titre compact */
    color: #333; /* Couleur sombre pour le titre */
    font-weight: bold;
}

.article-excerpt {
    font-size: 0.875rem; /* Taille légèrement réduite */
}

.article-date {
    font-size: 0.75rem; /* Texte petit pour la date */
}

</style>
<body data-bs-theme="dark">
    <div class="container">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <h3 class="pt-4 pb-4">Articles de la catégorie : <?php echo htmlspecialchars($category_name); ?></h3>

                <?php if ($articles): ?>
                        <?php foreach ($articles as $article): ?>
                            <li class="article-card">
                                <a href="details_article.php?id=<?php echo htmlspecialchars($article['id']); ?>" class="card-link">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Image -->
                                        <img src="../<?php echo htmlspecialchars($article['image_upload']); ?>" alt="Image de l'article" class="article-image">

                                        <!-- Contenu -->
                                        <div class="article-content">
                                            <h6 class="article-title mb-1 text-info"><?php echo htmlspecialchars($article['titre']); ?></h6>
                                            <p class="article-excerpt text-muted mb-1">
                                                <?php echo htmlspecialchars(substr($article['contenu'], 0, 50)); ?>...
                                            </p>
                                            <small class="article-date text-secondary">
                                                <?php echo date('F d, Y', strtotime($article['date_publication'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <hr style="color:#fff;">
                        <?php endforeach; ?>
                    
                <?php else: ?>
                    <p>Aucun article trouvé dans cette catégorie.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>
    

    <footer class="py-2 text-center mt-4 text-body-secondary bg-body-tertiary">
        <p>Blog for <a href="https://getbootstrap.com/">Association Sterna Africa</a> directed by <a href="mailto:roys.esdras@outlook.com">RoysEsdras</a>.</p>
    </footer>

<script src="../assets/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>