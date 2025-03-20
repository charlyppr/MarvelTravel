<?php
require_once('session.php');
check_auth('connexion.php');

$json_file = "../json/voyages.json";

$id = (int) $_GET['id'];
$voyages = json_decode(file_get_contents($json_file), true);
$voyages = $voyages[$id];

$date = $_POST['date'];
$lieux = isset($_POST['lieux']) ? (array) $_POST['lieux'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/reservation.css">
    <title>reservation</title>
</head>
<body>
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
    <h1 class="titre">Recapitulatif de la commande</h1>
    <div class="information">
        <p>Destination: <?php echo $voyages['titre']?></p>
        <p>Description: <?php echo $voyages['resume']?></p>
        <p>Durée: <?php echo $voyages['dates']['duree']?></p>
        <p>Montant: <?php echo $voyages['prix']?></p>
        <p>acheteur: <?php echo $_SESSION['first_name'].' '.$_SESSION['last_name']?></p>
        <p>Date de départ : <?php echo $date?>
        <p>lieux visité : <?php echo implode(", ", $lieux);?></p>
    </div>
    <div class='paie_container'><a href="paiement.php">Confirmer et payer</a></div>
    
    <footer>
        <?php
            include("footer.php");
        ?>
    </footer>
</body>
</html>