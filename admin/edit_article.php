<?php
session_start();

// // Vérifier si l'utilisateur est connecté et s'il est un administrateur
// if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header('Location: admin_login.php'); // Redirige vers la page de connexion si non connecté ou non admin
//     exit();
// }

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

// Récupérer l'article à modifier (aucune restriction pour un administrateur)
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Article introuvable.');
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

    header('Location: admin_dashboard.php'); // Redirige vers le tableau de bord après modification
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
    <!-- Lien vers Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../sum.css">

</head>
<body data-bs-theme="dark">
<div class="container py-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold text-primary">Modifier l'article</h1>
    </div>

    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Informations de l'article</h5>
        </div>
        <div class="card-body">
            <form action="edit_article.php?id=<?php echo htmlspecialchars($article['id']); ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Titre -->
                    <div class="mb-3 col-md-4">
                        <label for="titre" class="form-label">Titre</label>
                        <input type="text" class="form-control bg-dark" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>" required>
                    </div>

                    <!-- Date de publication -->
                    <div class="mb-3 col-md-4">
                        <label for="date_publication" class="form-label">Date de publication</label>
                        <input type="datetime-local" class="form-control bg-dark" name="date_publication" value="<?php echo date('Y-m-d\TH:i', strtotime($article['date_publication'])); ?>">
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-3 col-md-4">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <select class="form-select bg-dark" name="categorie" required>
                            <option value="">-- Choisissez une catégorie --</option>
                            <?php foreach ($categories as $id => $name): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo ($article['categorie'] == $id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Contenu -->
                <div class="mb-3">
                    <label for="contenu" class="form-label">Contenu</label>
                    <textarea class="form-control bg-dark" id="summernote" name="contenu" rows="5" required><?php echo htmlspecialchars($article['contenu']); ?></textarea>
                </div>

                <div class="row">
                    <!-- Image actuelle -->
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Image actuelle :</label><br>
                        <?php if (!empty($article['image_upload'])): ?>
                            <img src="<?php echo '../' . htmlspecialchars($article['image_upload']); ?>" alt="Image de l'article" class="img-thumbnail" style="max-width: 150px;"><br>
                        <?php else: ?>
                            <span class="text-muted">Pas d'image disponible</span><br>
                        <?php endif; ?>
                    </div>

                    <!-- Télécharger une nouvelle image -->
                    <div class="mb-3 col-md-6">
                        <label for="image_upload" class="form-label">Télécharger une nouvelle image</label>
                        <input type="file" class="form-control bg-dark" name="image_upload" accept="image/*">
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="d-flex justify-content-between">
                    <a href="admin_dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript Summernote -->
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function () {
        $('#summernote').summernote({
            placeholder: 'Écrivez ici votre contenu...',
            tabsize: 2,
            height: 500,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'emoji']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
</body>
</html>
