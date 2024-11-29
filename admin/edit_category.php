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

        // Redirection avec message de succès
        $_SESSION['message'] = "Catégorie mise à jour avec succès.";
        header('Location: admin_dashboard.php#category'); // Redirige vers le tableau de bord
        exit();
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
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343a40; /* Couleur de fond sombre */
            color: #ffffff; /* Couleur du texte */
        }
        .category-container {
            max-width: 400px; /* Largeur maximale de la boîte de modification */
            margin: auto; /* Centrer horizontalement */
            padding: 2rem; /* Espacement interne */
            border-radius: 10px; /* Coins arrondis */
            background-color: #495057; /* Couleur de fond du conteneur */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Ombre */
        }
    </style>
</head>
<body>
    <div class="category-container mt-5">
        <h1 class="text-center mb-4">Modifier la Catégorie</h1>

        <!-- Message de confirmation -->
        <?php if (isset($message)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <form method="POST">
            <div class="mb-3">
                <label for="category_name" class="form-label">Nom de la Catégorie :</label>
                <input type="text" id="category_name" name="category_name" 
                       class="form-control bg-dark text-white" 
                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Mettre à jour la Catégorie</button>
        </form>

        <!-- <br>
        <a href="admin_dashboard.php" class="btn btn-secondary w-100">Retour au tableau de bord</a> -->
    </div>

    <!-- Lien vers Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
