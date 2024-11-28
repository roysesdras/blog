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

    // Requête pour récupérer l'article le plus récent de la catégorie "Interculturality"
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS categorie_name 
        FROM articles a
        JOIN categories c ON a.categorie = c.id
        WHERE c.name = :categorie AND a.status = 'publié'
        ORDER BY a.date_publication DESC
        LIMIT 1
    ");
    $stmt->execute(['categorie' => 'Interculturality']);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($article) {
        // Affichage de l'article
        echo '<article class="blog-post">';
        echo '<h2 class="display-5 link-body-emphasis mb-1">' . htmlspecialchars($article['titre']) . '</h2>';
        echo '<p class="blog-post-meta">' . date('F j, Y', strtotime($article['date_publication'])) . ' by <a href="#">' . htmlspecialchars($article['auteur']) . '</a></p>';
        
        // Affichage de l'image
        echo '<img src="' . htmlspecialchars($article['image_upload']) . '" alt="Image de l\'article" class="img-fluid" style="border-radius:5px;"/>';
        
        // Récupération et affichage du contenu par morceaux
        $contenu = nl2br(htmlspecialchars($article['contenu']));
        $longueur = strlen($contenu);
        $chunk_size = 200; // Nombre de caractères à afficher par clic
        $offset = 0; // Position actuelle

        // Affichage des 200 premiers caractères
        echo '<div id="article-content">';
        echo '<p>' . substr($contenu, $offset, $chunk_size) . '</p>'; // Affiche les 200 premiers caractères
        echo '</div>';

        // Lien "Continuer" s'il y a plus de contenu
        if ($longueur > $chunk_size) {
            echo '<a href="#" id="continuer" onclick="showMore(); return false;">Continue.....</a>';
        }

        echo '</article>';

        // JavaScript pour gérer le chargement progressif
        echo '<script>
                var contenuComplet = ' . json_encode($contenu) . ';
                var offset = ' . $chunk_size . '; // Initialiser à 200
                var chunkSize = 200; // Nombre de caractères à ajouter à chaque clic

                function showMore() {
                    const articleContent = document.getElementById("article-content");
                    const nextPart = contenuComplet.substr(offset, chunkSize); // Obtenir les 200 caractères suivants

                    if (nextPart) {
                        const p = document.createElement("p");
                        p.innerHTML = nextPart;
                        articleContent.appendChild(p); // Ajouter le nouveau paragraphe

                        offset += chunkSize; // Mettre à jour l\'offset

                        // Si plus de contenu, mettre à jour le lien "Continuer"
                        if (offset < contenuComplet.length) {
                            document.getElementById("continuer").innerHTML = "Continue....."; // Réafficher le lien
                        } else {
                            document.getElementById("continuer").style.display = "none"; // Cacher le lien s\'il n\'y a plus de contenu
                        }
                    }
                }
              </script>';
    } else {
        echo '<p>Aucun article trouvé dans la catégorie "Interculturality".</p>';
    }
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}


?>
