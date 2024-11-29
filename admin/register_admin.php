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

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérification que les mots de passe correspondent
    if ($password !== $confirm_password) {
        echo "Les mots de passe ne correspondent pas.";
        exit();
    }

    // Vérification que l'email est unique
    $check_email = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $check_email->execute([$email]);
    if ($check_email->rowCount() > 0) {
        echo "Cet email est déjà utilisé.";
        exit();
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertion des données dans la table admins
    $sql = "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$username, $email, $hashed_password])) {
        echo "Inscription réussie. Vous pouvez maintenant vous connecter.";
        // Redirection vers la page de connexion des administrateurs
        header('Location: admin_login.php');
        exit();
    } else {
        echo "Erreur lors de l'inscription. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Administrateur</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343a40; /* Couleur de fond sombre */
            color: #ffffff; /* Couleur du texte */
        }
        .registration-container {
            max-width: 400px; /* Largeur maximale de la boîte d'inscription */
            margin: auto; /* Centrer horizontalement */
            padding: 2rem; /* Espacement interne */
            border-radius: 10px; /* Coins arrondis */
            background-color: #495057; /* Couleur de fond du conteneur */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Ombre */
        }
    </style>
</head>
<body>
    <div class="registration-container mt-5">
        <h2 class="text-center mb-4">Inscription Administrateur</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur :</label>
                <input type="text" name="username" id="username" class="form-control bg-dark text-white" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" name="email" id="email" class="form-control bg-dark text-white" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" name="password" id="password" class="form-control bg-dark text-white" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmez le mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control bg-dark text-white" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
        </form>
    </div>

    <!-- Lien vers Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
