<?php
session_start();
require('getapikey.php');

$transaction = $_GET['transaction'];
$montant = $_GET['montant'];
$vendeur = $_GET['vendeur'];
$status = $_GET['status']; 
$controlRecu = $_GET['control'];

$api_key = getAPIKey($vendeur);

$controlCalcule = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $status . "#");

$json_file = "../json/commandes.json";
$commandes = json_decode(file_get_contents($json_file), true);
foreach ($commandes as $key => $commande) {
    if ($commande['transaction'] === $transaction) {
        $commandes[$key]['control'] = $controlRecu;
        $commandes[$key]['status'] = $status;
        file_put_contents($json_file, json_encode($commandes, JSON_PRETTY_PRINT));
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/retour_reservation.css">
    <title>Document</title>
</head>
<div class="default"></div>

    <header class="nav">
        <a href="../index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>

        <div class="menu">
            <ul>
                <a href="../index.php" class="menu-li">
                    <li>Accueil</li>
                </a>
                <a href="destination.php" class="active menu-li">
                    <li>Destinations</li>
                </a>
                <a href="contact.php" class="menu-li">
                    <li>Contact</li>
                </a>
                <?php 
                        if (isset($_SESSION['user'])) { 
                            echo "<a href='profil.php' class='menu-li'>
                            <li>Profil</li></a>";
                        }else {
                            echo "<a href='connexion.php' class='nav-button'>
                            <li>Se connecter</li></a>";
                        }
                ?>
            </ul>
        </div>
    </header>
<body>
    <div class="container">
        <div class="texte">
        <?php
        if ($controlRecu === $controlCalcule) {
            if ($status === 'accepted') {
                echo "<h1>Paiement accepte !</h1><h1>Votre voyage est confirme.</h1>";
            } else {
                echo "<h1>Paiement refuse.</h1><h1>Veuillez réessayer.</h1>";
            }
        } else {
            echo "<h1>Erreur : l'intégrite des données n'est pas vérifiee.</h1>";
        }
        ?>
        </div>
    </div>
</body>
</html>

