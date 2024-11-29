<?php
session_start();

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

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Requête pour récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Vérifier le mot de passe
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
    button{
        padding: 8px;
        border: solid 1px #37cb;
        border-radius: 5px;
        background-color: #37cb;
    }
</style>

<body data-bs-theme="dark">
    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4 pt-5">
                <h2 class="pb-4">User Login</h2>
                <form method="POST">
                    <input type="text" name="username" placeholder="User name" class="form-control mb-3" required>

                    <input type="password" name="password" placeholder="Password"class="form-control mb-4" required>

                    <button type="submit" class="mb-3">Login</button>
                </form>

                <p>you don't have an account, <a href="register.php">Register here </a></p>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
    


    <script src="../assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if (isset($error)) {
    echo "<p style='color: red;'>$error</p>";
}
?>
