<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

$page_title = "Statistiques";
$maintenance_message = "Cette fonctionnalité sera bientôt disponible";
$completion_date = "PHASE 4";

// Vérifier si l'utilisateur est connecté et est administrateur
if ($_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas admin
    header('Location: connexion.php');
    exit;
}

// Récupérer le thème de la session
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Statistiques</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>

<style>
    .statistics-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin-left: 260px;
    }

    @media screen and (max-width: 992px) {
        .statistics-container {
            margin-left: 0;
        }
    }
</style>

<body class="<?php echo $theme; ?>-theme">
    <?php
    include('sidebar.php');
    ?>

    <div class="statistics-container">
        <?php include('maintenance.php'); ?>
    </div>

</body>
</html> 