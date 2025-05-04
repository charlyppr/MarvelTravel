<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

$page_title = "A propos de nous";
$maintenance_message = "Si vous souhaitez en savoir plus sur notre histoire, attendez la prochaine mise à jour !";
$completion_date = "PHASE 4";

// Récupérer le thème de la session
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • A propos de nous</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>
<body class="<?php echo $theme; ?>-theme">
    <?php include('maintenance.php'); ?>
</body>
</html> 