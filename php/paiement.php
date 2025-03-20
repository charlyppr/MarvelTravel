<!DOCTYPE html>
<?php

require_once('session.php');
check_auth('connexion.php');
$_SESSION['current_url'] = current_url();

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/base.css">
    <title>paiement</title>
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
    <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' class="carte">
        <input type="text" name="carte" placeholder="Numero de carte">
        <input type="text" name="date" placeholder="Date d'expiration">
        <input type="text" name="crypto" placeholder="Cryptogramme">
        <input type='hidden' name='transaction' value='154632ABCD'>
        <input type='hidden' name='montant' value='18000.99'>
        <input type='hidden' name='vendeur' value='TEST'>
        <input type='hidden' name='retour' value='http://localhost/retour_paiement.php?session=s'>
        <input type='hidden' name='control' value='01c06955b2d4ad0ccdedd4aad0ab68bf'>
        <input type='submit' value="Valider et payer">
    </form>
    <footer>
        <?php
            include('footer.php');
        ?>
    </footer>
</body>
</html>