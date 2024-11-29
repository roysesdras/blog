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

// Démarrer une session
session_start();

$error_message = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Recherche de l'utilisateur dans la table admins
    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Stocker les informations de l'administrateur dans la session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        // Redirection vers le tableau de bord
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343a40; /* Couleur de fond sombre */
            color: #ffffff; /* Couleur du texte */
        }
        .login-container {
            max-width: 400px; /* Largeur maximale de la boîte de connexion */
            margin: auto; /* Centrer horizontalement */
            padding: 2rem; /* Espacement interne */
            border-radius: 10px; /* Coins arrondis */
            background-color: #495057; /* Couleur de fond du conteneur */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Ombre */
        }
    </style>
</head>
<body>
    <div class="login-container mt-5">
        <h1 class="text-center mb-4">Connexion Administrateur</h1>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" id="email" name="email" class="form-control bg-dark text-white" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" id="password" name="password" class="form-control bg-dark text-white" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
    </div>

    <!-- Lien vers Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
