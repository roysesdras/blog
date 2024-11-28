<div class="p-4">
    <h4 class="fst-italic">Archives</h4>
    <ol class="list-unstyled mb-0">
        <?php
        // Connexion à la base de données
        $host = 'localhost';
        $db = 'blog_sterna';
        $user = 'roys_web';
        $pass = '@roys';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Récupérer les mois et les années des articles publiés
            $stmt = $pdo->query("
                SELECT DATE_FORMAT(date_publication, '%M %Y') AS archive
                FROM articles
                WHERE status = 'publié'
                GROUP BY archive
                ORDER BY MAX(date_publication) DESC
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Créer un lien dynamique vers la page archive.php
                $archive = urlencode($row['archive']); // Encoder pour l'URL
                echo '<li><a href="./pages/archives.php?month_year=' . $archive . '">' . htmlspecialchars($row['archive']) . '</a></li>';
            }
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
        ?>
    </ol>
</div>
