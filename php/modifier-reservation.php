<?php

require_once 'session.php';
    
$page_title = "Modification de réservation";
$maintenance_message = "Cette fonctionnalité sera bientôt disponible";
$completion_date = "PHASE 4";

// Récupérer le thème de la session
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Modification de réservation</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>
<style>
    .modifier-reservation-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin-left: 260px;
    }
</style>

<body class="<?php echo $theme; ?>-theme">
<?php
    include('sidebar.php');
    ?>
    <div class="modifier-reservation-container">
        <?php include('maintenance.php'); ?>
    </div>
</body>
</html> 