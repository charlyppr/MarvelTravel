<?php
require_once 'session.php';

// Vérification du statut de bannissement
session_start();

if (isset($_SESSION['user']) && isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];
    $json_file = dirname(__FILE__) . '/../json/users.json';
    
    if (file_exists($json_file)) {
        $users = json_decode(file_get_contents($json_file), true);
        if ($users) {
            $userStillBanned = false;
            
            foreach ($users as $user) {
                if ($user['email'] === $userEmail && $user['blocked']) {
                    $userStillBanned = true;
                    break;
                }
            }
            
            // Si l'utilisateur n'est plus banni, le rediriger vers la page d'accueil
            if (!$userStillBanned) {
                header("Location: ../index.php");
                exit();
            }
        }
    }
}
?>
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

<body>

    <div class="confirmation-container">
        <div class="confirmation-header error-header">
            <div class="error-icon">
                <img src="../img/svg/alert-circle.svg" alt="Erreur">
            </div>
            <h1>Vous êtes banni. Votre compte ne respecte pas notre politique.</h1>
            <p class="confirmation-subtitle"></p>
        </div>

        <div class="confirmation-content">
            <div class="confirmation-card error-card">
                <div class="card-header">
                    <img src="../img/svg/info.svg" alt="Information" class="card-icon">
                    <h2>Que faire ?</h2>
                </div>
                <div class="card-content">
                    <ul class="error-reasons">
                        <li>Contactez notre service client</li>
                        <li>Vous pouvez consulter notre <a class="link" href="cgv-light.php">politique de confidentialité</a></li>
                        <li>Vous pouvez consulter nos <a class="link" href="cvg.php">conditions générales d'utilisation</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</body>

</html>