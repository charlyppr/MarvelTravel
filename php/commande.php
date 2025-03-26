<?php
require('session.php');
$_SESSION['current_url'] = current_url();
check_auth($_SESSION['current_url'] ?? "../index.php");
$transaction = (int) $_GET['transaction'];
if (!isset($_GET['transaction'])) {
    die("<h1>Erreur </h1><p>ID de transaction manquant ! <a href='commande.php'>Retour</a></p>");
}

$json_file_path = '../json/commandes.json';
$json_file = file_get_contents($json_file_path);
$json_data = json_decode($json_file, true);

if (!isset($json_data[$transaction])) {
    die("<h1>Erreur </h1><p>Transaction introuvable ! <a href='profil.php'>Retour</a></p>");
}
$commande = $json_data[$transaction];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Commandes</title>
    <link rel="stylesheet" href="../css/commande.css">
    <link rel="stylesheet" href="../css/base.css">
</head>
<body>
<div class="default"></div>

<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-top">
                <a href="../index.php" class="logo-container">
                    <div class="logo-gauche">
                        <span class="logo mar">MAR</span>
                        <span class="logo tra">TRA</span>
                    </div>
                    <span class="logo vel">VEL</span>
                </a>

                <span class="trait"></span>

            <div class="sidebar-bottom-deco">
                <div class="sidebar-bottom">
                    <img class="photo-admin" src="../img/svg/spiderman-pin.svg" alt="spiderman-pin">

                    <div class="info-admin">
                        <span class="nom-admin">
                            <?php echo $_SESSION['first_name'] ?>
                            <?php echo $_SESSION['last_name'] ?>
                        </span>
                        <span class="mail-admin"><?php echo $_SESSION['email'] ?></span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<div class="content-container-div">
        <div class="content-container">
                <div class="content">
                    <div class="top-text">
                        <span class="titre">Bonjour <?php echo $_SESSION['first_name'] ?>,</span>
                        <a href="../index.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="fleche">
                        </a>
                    </div>

                    <div class="cards">

                        <div class="card">
                            <div class="header-text">
                                <span class="titre-card">Mon voyage à <?=$commande['voyage']?></span>
                            </div>


                            <div class="card-content-container">
                                <div class="card-content">

                                    <div class="modif-info">
                                        <div class="row">
                                            <div class="row-user-info">
                                                <span>nombre de personnes :</span>
                                                <span><?= $commande['nb_personne']?></span>
                                            </div>
                                            <img src="../img/svg/edit.svg" alt="modification">
                                        </div>

                                        <div class="row">
                                            <div class="row-user-info">
                                                <span> Etapes du voyage :</span>
                                                <span><?php foreach($commande['options'] as $option){echo $option['etape'];}?></span>
                                            </div>
                                            <img src="../img/svg/edit.svg" alt="modification">
                                        </div>

                                        <div class="row">
                                            <div class="row-user-info">
                                                <span>Options du voyage :</span>
                                                <span><?php foreach($commande['options'] as $option){echo $option['etape'];}?></span>
                                            </div>
                                            <img src="../img/svg/edit.svg" alt="modification">
                                        </div>

                                        <div class="row">
                                            <div class="row-user-info">
                                                <span>Prix du voyage :</span>
                                                <span><?= $commande['montant']?></span>
                                            </div>
                                            <img src="../img/svg/edit.svg" alt="modification">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
