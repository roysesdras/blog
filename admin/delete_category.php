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

session_start();

// Vérification si l'administrateur est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];

    // Supprimer la catégorie
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$category_id])) {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Erreur lors de la suppression de la catégorie.";
    }
}
?>
