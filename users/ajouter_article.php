<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Démarrer une session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  // Rediriger l'utilisateur vers la page de connexion s'il n'est pas connecté
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];  // ID de l'utilisateur connecté

// Traitement du formulaire d'ajout d'article
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $date_publication = $_POST['date_publication'] ? $_POST['date_publication'] : date('Y-m-d H:i:s');  // Si aucune date fournie, utiliser la date actuelle
    $categorie = $_POST['categorie'];
    $auteur = $user_id;  // Utiliser l'ID de l'utilisateur comme auteur
    
    
    // Si une image est téléchargée
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == 0) {
        $upload_dir = '../uploads/';  // Dossier pour stocker les images
        $image_upload = $upload_dir . basename($_FILES['image_upload']['name']);
        
        // Déplacer l'image téléchargée vers le dossier de destination
        if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $image_upload)) {
            echo "Image téléchargée avec succès.";
        } else {
            echo "Erreur lors du téléchargement de l'image.";
        }
    }
    
    // Insertion de l'article dans la base de données
    $sql = "INSERT INTO articles (image_upload, titre, contenu, date_publication, categorie, auteur, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'en attente')";
    $stmt = $pdo->prepare($sql);
    
    // Exécution de la requête
    if ($stmt->execute([$image_upload, $titre, $contenu, $date_publication, $categorie, $auteur])) {

        echo "Article ajouté avec succès!";
    } else {
        echo "Erreur lors de l'ajout de l'article.";
    }
}
?>

