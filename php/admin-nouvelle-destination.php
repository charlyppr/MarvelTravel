<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

// Vérifier si l'utilisateur est connecté et est administrateur
if ($_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas admin
    header('Location: connexion.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Création d'utilisateur</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <?php
    include('sidebar.php');
    ?>
        
    <?php include('maintenance.php'); ?>

</body>
</html>