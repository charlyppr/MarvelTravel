<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>bannissement • Marvel Travel</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/confirmation.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <?php include 'nav.php'; ?>

    <div class="confirmation-container">
        <div class="confirmation-header error-header">
            <div class="error-icon">
                <img src="../img/svg/alert-circle.svg" alt="Erreur">
            </div>
            <h1>Vous êtes banni. Votre compte ne respecte pas notre politique.</h1>
            <p class="confirmation-subtitle"><?php echo htmlspecialchars($error_message); ?></p>
        </div>

        <div class="confirmation-content">
            <div class="confirmation-card error-card">
                <div class="card-header">
                    <img src="../img/svg/info.svg" alt="Information" class="card-icon">
                    <h2>Que faire ?</h2>
                </div>
                <div class="card-content">
                    <ul class="error-reasons">
                        <li>contactez notre service client</li>
                        <li>vous pouvez consulter notre <a href="cgv-light.php">politique de confidentialité</a></li>
                        <li>vous pouvez consulter nos <a href="cvg.php">conditions générales d'utilisation</a></li>
                    </ul>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="destination.php" class="action-button home-button">
                    <img src="../img/svg/home.svg" alt="Accueil">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

</body>

</html>