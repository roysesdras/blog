<?php
try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=blog_sterna', 'roys_web', '@roys');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL avec jointure
    $sql = "
    SELECT 
        articles.id AS article_id, 
        articles.titre, 
        articles.contenu, 
        articles.date_publication, 
        articles.auteur, 
        articles.image_upload, 
        categories.name AS categorie_name
    FROM 
        articles
    LEFT JOIN 
        categories 
    ON 
        articles.categorie = categories.id
    WHERE 
        articles.status = 'publié';
    ";

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Récupérer tous les résultats
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<style>

    /* Assurez-vous que la largeur des éléments ne dépasse pas */
.container {
    padding: 0 15px;
}

.col-12 {
    width: 100%;
}

.card-text {
    word-wrap: break-word; /* Permet au texte de se couper correctement dans un bloc */
    overflow-wrap: break-word; /* Compatibilité cross-browser */
}

/* Pour un affichage propre sur mobile */
@media (max-width: 767px) {
    .d-flex {
        flex-direction: wrap; /* Aligne les éléments de manière verticale sur petits écrans */
    }
    .col-md-6 {
        width: 100%; /* Les articles s'affichent sur toute la largeur de l'écran sur mobile */
    }
}

</style>

<div class="mt-2">
    <h2 class="mb-4">Recent articles</h2>
    <div class="d-flex overflow-auto">
        <?php foreach ($articles as $article): ?>
            <div class="col-md-6 flex-shrink-0 me-3">
                <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                    <div class="col p-4 d-flex flex-column position-static">
                        <strong class="mb-2 text-success-emphasis">
                            <?= htmlspecialchars($article['categorie_name'] ?? 'Non spécifié', ENT_QUOTES, 'UTF-8') ?>
                        </strong>
                        <h3 class="mb-0"><?= htmlspecialchars($article['titre'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="mb-1 text-body-secondary">
                            <?= htmlspecialchars(date("d M Y", strtotime($article['date_publication']))) ?>
                        </div>
                        <p class="card-text">
                            <?= htmlspecialchars(substr($article['contenu'], 0, 90)) . '...' ?>
                        </p>
                        <a href="article.php?id=<?= $article['article_id'] ?>" class="icon-link gap-1 icon-link-hover stretched-link">
                            Continue reading
                            <svg class="bi"><use xlink:href="#chevron-right"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
