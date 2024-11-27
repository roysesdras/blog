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

<h1>Bienvenue, <?php echo $_SESSION['username']; ?> !</h1>

<!-- Formulaire d'ajout d'article -->
<form action="" method="POST" enctype="multipart/form-data">
    <label for="titre">Titre de l'article :</label>
    <input type="text" name="titre" required>
    
    <label for="contenu">Contenu de l'article :</label>
    <textarea name="contenu" required></textarea>
    
    <label for="date_publication">Date de publication :</label>
    <input type="datetime-local" name="date_publication">
    
    <label for="categorie">Catégorie :</label>
    <select name="categorie" required>
        <option value="">-- Choisissez une catégorie --</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
        <?php endforeach; ?>
    </select>
    
    <label for="auteur">Auteur :</label>
    <input type="text" name="auteur" value="<?php echo $_SESSION['username']; ?>" readonly>
    
    <label for="image_upload">Téléchargez une image :</label>
    <input type="file" name="image_upload" accept="image/*">
    
    <button type="submit">Ajouter l'article</button>
</form>

<!-- Tableau des articles -->
<h2>Vos articles</h2>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Image</th>
            <th>Contenu</th>
            <th>Catégorie</th>
            <th>Date de publication</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?php echo $article['id']; ?></td>
                <td><?php echo $article['titre']; ?></td>
                <td>
                    <?php if ($article['image_upload']): ?>
                        <img src="<?php echo '../' . $article['image_upload']; ?>" alt="Image de l'article" width="100">
                    <?php else: ?>
                        Pas d'image
                    <?php endif; ?>
                </td>
                <td>
                    <span class="contenu-preview">
                        <?php echo substr($article['contenu'], 0, 500); ?>
                        <?php if (strlen($article['contenu']) > 500): ?>
                            <a href="#" class="lire-plus" data-id="<?php echo $article['id']; ?>">Lire plus</a>
                            <span class="contenu-complet" style="display: none;"><?php echo $article['contenu']; ?></span>
                            <a href="#" class="lire-moins" style="display: none;">Lire moins</a>
                        <?php endif; ?>
                    </span>
                </td>
                <td><?php echo $article['categorie']; ?></td>
                <td><?php echo $article['date_publication']; ?></td>
                <td><?php echo $article['status']; ?></td>
                <td>
                    <!-- Lien vers la page de modification -->
                    <a href="edit_article.php?id=<?php echo $article['id']; ?>">Modifier</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<a href="logout.php">Se déconnecter</a>

<script>
document.querySelectorAll('.lire-plus').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.closest('.contenu-preview');
        parent.querySelector('.contenu-complet').style.display = 'inline';
        this.style.display = 'none';
        parent.querySelector('.lire-moins').style.display = 'inline';
    });
});

document.querySelectorAll('.lire-moins').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.closest('.contenu-preview');
        parent.querySelector('.contenu-complet').style.display = 'none';
        parent.querySelector('.lire-plus').style.display = 'inline';
        this.style.display = 'none';
    });
});
</script>
