<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirige vers la page de connexion si non connecté
    exit();
}

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

// Récupération des catégories
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);

// Récupérer l'ID de l'article à modifier
if (!isset($_GET['id'])) {
    die('ID d\'article non spécifié');
}

$article_id = $_GET['id'];

// Récupérer l'article à modifier
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND auteur = ?");
$stmt->execute([$article_id, $_SESSION['username']]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Article introuvable ou vous n\'êtes pas l\'auteur de cet article');
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $date_publication = $_POST['date_publication'];
    $categorie = $_POST['categorie'];

    $image_url = $article['image_upload']; // Garder l'image actuelle par défaut

    // Traitement de l'upload de l'image
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['image_upload']['name']);
        $file_path = $upload_dir . $file_name;

        // Validation : vérifier l'extension du fichier
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            die('Type de fichier non autorisé. Seules les images JPG, JPEG, PNG et GIF sont acceptées.');
        }

        // Déplacer le fichier uploadé
        if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $file_path)) {
            $image_url = str_replace('../', '', $file_path); // Stocker un chemin relatif
        } else {
            die('Échec du téléchargement de l\'image.');
        }
        
    }

    // Mise à jour de l'article dans la base de données
    $update_stmt = $pdo->prepare("UPDATE articles SET titre = ?, contenu = ?, date_publication = ?, categorie = ?, image_upload = ? WHERE id = ?");
    $update_stmt->execute([$titre, $contenu, $date_publication, $categorie, $image_url, $article_id]);

    header('Location: dashboard.php'); // Redirige vers le tableau de bord après modification
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
</head>
<body>
    <h1>Modifier l'article</h1>

    <form action="edit_article.php?id=<?php echo $article['id']; ?>" method="POST" enctype="multipart/form-data">
        <label for="titre">Titre :</label>
        <input type="text" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>" required>

        <label for="contenu">Contenu :</label>
        <textarea name="contenu" required><?php echo htmlspecialchars($article['contenu']); ?></textarea>

        <label for="date_publication">Date de publication :</label>
        <input type="datetime-local" name="date_publication" value="<?php echo date('Y-m-d\TH:i', strtotime($article['date_publication'])); ?>">

        <label for="categorie">Catégorie :</label>
        <select name="categorie" required>
            <option value="">-- Choisissez une catégorie --</option>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo ($article['categorie'] == $id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="image_upload">Image actuelle :</label>
        <?php if (!empty($article['image_upload'])): ?>
            <img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" width="100"><br>
        <?php endif; ?>
        <label for="image_upload">Télécharger une nouvelle image :</label>
        <input type="file" name="image_upload" accept="image/*">

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <a href="dashboard.php">Retour au tableau de bord</a>
</body>
</html>
