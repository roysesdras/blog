<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
$host = 'localhost';
$db = 'blog_sterna';
$user = 'roys_web';
$pass = '@roys';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer le mois et l'année à partir des paramètres d'URL
    $month_year = isset($_GET['month_year']) ? urldecode($_GET['month_year']) : '';

    // Requête pour récupérer les articles publiés pour le mois et l'année sélectionnés
    $stmt = $pdo->prepare("
        SELECT * FROM articles 
        WHERE DATE_FORMAT(date_publication, '%M %Y') = :month_year AND status = 'publié'
        ORDER BY date_publication DESC
    ");
    $stmt->execute(['month_year' => $month_year]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>Archives : <?php echo htmlspecialchars($month_year); ?></title>
    <link rel="canonical" href="">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../blog.css">
</head>
    <style>
        .card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #333;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body data-bs-theme="dark">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Archive for <?php echo htmlspecialchars($month_year); ?></h2>

        <?php if ($articles): ?>
            <div class="row g-4">
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <!-- Image de l'article -->
                            <?php if (!empty($article['image_upload'])): ?>
                                <img src="../<?php echo htmlspecialchars($article['image_upload']); ?>" class="card-img-top" alt="Image de l'article">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150x100" class="card-img-top" alt="Image par défaut">
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-info"><?php echo htmlspecialchars($article['titre']); ?></h5>
                                <p class="card-text text-muted small mb-2">
                                    <?php echo date('F j, Y', strtotime($article['date_publication'])); ?> par 
                                    <strong class="text-success"><?php echo htmlspecialchars($article['auteur']); ?></strong>
                                </p>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($article['contenu'], 0, 100)); ?>...
                                </p>
                                <a href="details_article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary mt-auto">Lire la suite</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun article trouvé pour cette période.</p>
        <?php endif; ?>
    </div>


    <footer class="py-2 text-center mt-4 text-body-secondary bg-body-tertiary">
        <p>Blog for <a href="https://getbootstrap.com/">Association Sterna Africa</a> directed by <a href="mailto:roys.esdras@outlook.com">RoysEsdras</a>.</p>
    </footer>

    <!-- Lien vers le script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
