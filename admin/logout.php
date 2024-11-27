<?php
// Démarrer la session
session_start();

// Supprimer toutes les données de la session
session_unset();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: admin_login.php');
exit();
?>
