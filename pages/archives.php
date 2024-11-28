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

    if ($articles) {
        echo '<h2>Articles pour ' . htmlspecialchars($month_year) . '</h2>';
        foreach ($articles as $article) {
            // Affichage de l'article
            echo '<article class="blog-post">';
            echo '<h3>' . htmlspecialchars($article['titre']) . '</h3>';
            echo '<p class="blog-post-meta">' . date('F j, Y', strtotime($article['date_publication'])) . ' by <a href="#">' . htmlspecialchars($article['auteur']) . '</a></p>';
            echo '<p>' . nl2br(htmlspecialchars(substr($article['contenu'], 0, 200))) . '...</p>'; // Les premiers 200 caractères
            echo '<a href="article.php?id=' . $article['id'] . '">Lire la suite</a>'; // Lien vers l'article complet
            echo '</article>';
        }
    } else {
        echo '<p>Aucun article trouvé pour cette période.</p>';
    }
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
?>
