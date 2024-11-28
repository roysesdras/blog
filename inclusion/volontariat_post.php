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

    // Requête pour récupérer l'article le plus récent de la catégorie "Volunteering"
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS categorie_name 
        FROM articles a
        JOIN categories c ON a.categorie = c.id
        WHERE c.name = :categorie AND a.status = 'publié'
        ORDER BY a.date_publication DESC
        LIMIT 1
    ");
    $stmt->execute(['categorie' => 'Volunteering']);
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
        $chunk_size = 500; // Nombre de caractères à afficher par clic
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
                var offset = ' . $chunk_size . ';

                function showMore() {
                    const articleContent = document.getElementById("article-content");
                    const nextPart = contenuComplet.substr(offset, 500); // Obtenir les 900 caractères suivants

                    if (nextPart) {
                        const p = document.createElement("p");
                        p.innerHTML = nextPart;
                        articleContent.appendChild(p); // Ajouter le nouveau paragraphe

                        offset += 500;

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
        echo '<p>Aucun article trouvé dans la catégorie "Volunteering".</p>';
    }
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
// Après avoir affiché l'article
echo '</article>';

// Affichage des commentaires
$stmt = $pdo->prepare("SELECT * FROM commentaires WHERE article_id = :article_id ORDER BY created_at DESC");
$stmt->execute(['article_id' => $article['id']]);
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($commentaires) {
    echo '<h4>Comments :</h4>';
    foreach ($commentaires as $commentaire) {
        echo '<div class="comment">';
        echo '<h5 class="text-success">' . htmlspecialchars($commentaire['nom']) . '</h5>';
        echo '<p>' . nl2br(htmlspecialchars($commentaire['commentaire'])) . '</p>';
        echo '<hr>';
        echo '</div>';
    }
} else {
    echo '<p>Aucun commentaire pour cet article.</p>';
}


// Formulaire de commentaire
echo '<div class="comment-section pt-3">';
//echo '<h6>Laisser un commentaire</h6>';
echo '<form method="POST" action="traiter_commentaire.php">';
echo '<input type="hidden" name="article_id" value="' . htmlspecialchars($article['id']) . '">';
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
