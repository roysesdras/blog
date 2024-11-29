<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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

// Traitement du formulaire d'ajout d'article
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $date_publication = $_POST['date_publication'] ?: date('Y-m-d H:i:s');
    $categorie = $_POST['categorie'];
    $auteur = $_POST['auteur'];
    $user_id = $_SESSION['user_id'];
    $image_path = '';

    // Gestion de l'upload de l'image
    if (!empty($_FILES['image_upload']['name'])) {
        $upload_dir = '../uploads/';
        $image_name = time() . '_' . basename($_FILES['image_upload']['name']);
        $image_path = $upload_dir . $image_name;

        if (!move_uploaded_file($_FILES['image_upload']['tmp_name'], $image_path)) {
            die("Erreur lors du téléchargement de l'image.");
        }

        // Stocker le chemin relatif pour l'affichage
        $image_path = str_replace('../', '', $image_path);
    }

    // Insertion de l'article
    $stmt = $pdo->prepare("
        INSERT INTO articles (titre, contenu, date_publication, categorie, auteur, user_id, image_upload, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'en attente')
    ");
    $stmt->execute([$titre, $contenu, $date_publication, $categorie, $auteur, $user_id, $image_path]);
}

// Récupérer les catégories
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$articles = $pdo->prepare("
    SELECT a.id, a.titre, a.contenu, a.date_publication, a.status, c.name AS categorie, a.image_upload
    FROM articles a
    LEFT JOIN categories c ON a.categorie = c.id
    WHERE a.user_id = ?
    ORDER BY a.date_publication DESC
");
$articles->execute([$user_id]);
$articles = $articles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <!-- Lien vers Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../sum.css">
    <style>
        .contenu-preview span {
            display: block;
        }
        .contenu-preview .contenu-complet {
            display: none;
        }
        /* Personnalisation pour le mode sombre */
        .note-editor {
            background-color: #343a40; /* Couleur de fond sombre */
            color: #ffffff; /* Couleur de texte claire */
        }
    </style>
</head>
<body data-bs-theme="dark">

<div class="container-fluid py-5">
    <!-- Titre de bienvenue -->
    <div class="text-center mb-4">
        <h1 class="fw-bold">Bienvenue, <?php echo $_SESSION['username']; ?> !</h1>
    </div>

    <!-- Formulaire d'ajout d'article -->
    <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Ajouter un nouvel article</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="titre" class="form-label">Titre de l'article</label>
                        <input type="text" class="form-control bg-dark" name="titre" required>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="date_publication" class="form-label">Date de publication</label>
                        <input type="datetime-local" class="form-control bg-dark" name="date_publication">
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <select class="form-select bg-dark" name="categorie" required>
                            <option value="">-- Choisissez une catégorie --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="auteur" class="form-label">Auteur</label>
                        <input type="text" class="form-control bg-dark" name="auteur" value="<?php echo $_SESSION['username']; ?>" readonly>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="image_upload" class="form-label">Téléchargez une image</label>
                        <input type="file" class="form-control bg-dark" name="image_upload" accept="image/*">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="contenu" class="form-label">Contenu de l'article</label>
                    <textarea class="form-control bg-dark" name="contenu" id="summernote" rows="5" required>

                    </textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Ajouter l'article</button>
            </form>
        </div>
    </div>

    <!-- Tableau des articles -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Vos articles</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Image</th>
                            <th>Contenu</th>
                            <th>Catégorie</th>
                            <th>D. publication</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?php echo $article['id']; ?></td>
                                <td><?php echo htmlspecialchars($article['titre']); ?></td>
                                <td>
                                    <?php if ($article['image_upload']): ?>
                                        <img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" class="img-thumbnail" style="max-width: 100px;">
                                    <?php else: ?>
                                        <span class="text-muted">Pas d'image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="contenu-preview">
                                        <?php echo nl2br(($article['contenu'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($article['categorie']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($article['date_publication'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $article['status'] === 'publié' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
    </div>
</div>

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- JavaScript Summernote -->
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 300,                 // hauteur de l'éditeur
            toolbar: [                   // toolbar configuration
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            placeholder: 'Écrivez votre contenu ici...'
        });
    });
</script>

</body>
</html>

