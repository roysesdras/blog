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

    // Vérifier si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nom = htmlspecialchars($_POST['nom']);
        $commentaire = htmlspecialchars($_POST['commentaire']);
        $article_id = (int) $_POST['article_id']; // Récupérer l'ID de l'article

        // Insérer le commentaire dans la base de données
        $stmt = $pdo->prepare("INSERT INTO commentaires (nom, commentaire, article_id) VALUES (:nom, :commentaire, :article_id)");
        $stmt->execute(['nom' => $nom, 'commentaire' => $commentaire, 'article_id' => $article_id]);

        // Redirection vers l'article après soumission
        header("Location: index.php?id=" . $article_id); // Remplacez par l'URL correcte de votre article
        exit;
    }
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
?>
