<?php
session_start();

$message = $_GET['message'] ?? '';
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
            if ($message === 'true') {
                echo "<h1>Envoie réussi !</h1><h1>Votre message à été reçu.</h1>";
            } else {
                echo "<h1>Echec de l'envoie.</h1><h1>Veuillez réessayer.</h1>";
            }
        ?>
        </div>
    </div>
</body>
</html>

