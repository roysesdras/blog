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

// Récupération des 5 articles récents approuvés et publiés
$stmt = $pdo->prepare("
    SELECT id, titre, contenu, date_publication, auteur, image_upload
    FROM articles
    WHERE status = 'publié'
    ORDER BY date_publication DESC
    LIMIT 5
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($articles): ?>
<div id="carouselExampleSlidesOnly" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($articles as $index => $article): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="p-4 p-md-5 mb-4 rounded text-body-emphasis bg-body-secondary"
                     style="position: relative; background-image: url('<?php echo $article['image_upload']; ?>'); background-size: cover; background-position: center; color: white;">
                    
                    <!-- Couche sombre semi-transparente -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>

                    <div class="col-lg-6 px-0" style="position: relative; z-index: 2;">
                        <h1 class="display-4 fst-italic"><?php echo htmlspecialchars($article['titre']); ?></h1>
                        <p class="lead my-3">
                            <?php echo htmlspecialchars(substr($article['contenu'], 0, 150)); ?>...
                        </p>
                        <p class="lead mb-0">
                            <a href="details_article.php?id=<?php echo $article['id']; ?>" class="text-body-emphasis fw-bold">Continue reading...</a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
