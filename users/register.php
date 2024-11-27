<?php
// Connexion à la base de données
$host = 'localhost';
$db = 'blog_sterna';   // Nom de votre base de données
$user = 'roys_web';    // Nom d'utilisateur de la base de données
$pass = '@roys';       // Mot de passe de la base de données

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Vérification si le nom d'utilisateur ou l'email existe déjà
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Le nom d'utilisateur ou l'email existe déjà.";
        exit();
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertion de l'utilisateur dans la base de données
    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$username, $hashed_password, $email])) {
        // Message de débogage avant redirection
        echo "Inscription réussie, redirection vers login.php...";
        header('Location: login.php');  // Redirection vers la page de connexion
        exit();  // S'assurer que le script s'arrête après la redirection
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>

<!-- Formulaire d'inscription -->
<form method="POST">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>
