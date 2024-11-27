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

// Vérifier si un ID de catégorie est passé dans l'URL
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Récupérer la catégorie de la base de données
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la catégorie n'existe pas
    if (!$category) {
        echo "Catégorie non trouvée.";
        exit();
    }
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);

    // Vérifier si le nom de la catégorie est non vide
    if (!empty($category_name)) {
        // Mettre à jour la catégorie dans la base de données
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_name, $category_id]);

        $message = "Catégorie mise à jour avec succès.";
    } else {
        $message = "Le nom de la catégorie est obligatoire.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Catégorie</title>
</head>
<body>
    <h1>Modifier la Catégorie</h1>

    <!-- Message de confirmation -->
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form method="POST">
        <label for="category_name">Nom de la Catégorie :</label>
        <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        <button type="submit">Mettre à jour la Catégorie</button>
    </form>

    <br>
    <a href="admin_dashboard.php">Retour au tableau de bord</a>
</body>
</html>
