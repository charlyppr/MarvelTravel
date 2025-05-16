<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

// Inclure le fichier session.php pour accéder à la fonction load_user_theme
require_once 'session.php';

$page_title = "Création d'utilisateur";
$maintenance_message = "Cette fonctionnalité sera bientôt disponible";
$completion_date = "PHASE 4";

// Vérifier si l'utilisateur est connecté et est administrateur
if ($_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas admin
    header('Location: connexion.php');
    exit;
}

// Récupérer le thème de la session
$theme = load_user_theme();
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

<style>
    .user-creation-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin-left: 260px;
    }

    @media screen and (max-width: 992px) {
        .user-creation-container {
            margin-left: 0;
        }
    }
</style>

<body class="<?php echo $theme; ?>-theme">
    <?php include 'sidebar.php'; ?>
        
    <div class="user-creation-container">
        <?php include('maintenance.php'); ?>
    </div>

</body>
</html>