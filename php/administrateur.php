<?php 
    require('session.php');
    check_admin_auth('connexion.php');
    $_SESSION['current_url'] = current_url();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Administrateur</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/administrateur.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

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

                    <div class="categories">
                        <div class="categories-content active-2">
                            <div class="categorie-img">
                                <img src="../img/svg/dashboard.svg" alt="crayon et rectangle">
                            </div>
                            <span class="categorie-text active-text">Dashboard</span>
                        </div>

                        <div class="categories-content">
                            <div class="categorie-img">
                                <img src="../img/svg/notif.svg" alt="cloche">
                            </div>
                            <span class="categorie-text">Notifications</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-bottom">
                    <img class="photo-admin" src="../img/svg/spiderman-pin.svg" alt="spiderman-pin">

                    <div class="info-admin">
                        <span class="nom-admin"><?= $_SESSION['first_name'].' '.$_SESSION['last_name']?></span>
                        <span class="mail-admin"><?= $_SESSION['email']?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-container-div">
            <div class="content-container">
                <div class="header">
                    <div class="search-bar">
                        <input type="text" placeholder="Chercher un voyageur" name="search" id="search">
                        <img src="../img/svg/loupe.svg" alt="loupe">
                    </div>

                    <a href="../index.php?logout=true" class="redir-text">
                        <span>Quitter le mode administrateur</span>
                        <img src="../img/svg/fleche-redir.svg" alt="fleche">
                    </a>
                </div>

                <div class="main-content">
                    <div class="titre-content">
                        <span>Voyageurs</span>
                        <span>12 au totals</span>
                    </div>

                    <table class="tab-voyageurs">
                        <thead>
                            <tr>
                                <th class="nom-head">Nom</th>
                                <th>Status</th>
                                <th>VIP</th>
                                <th class="date">Membre depuis</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $json_file = "../json/users.json";
                                $users = json_decode(file_get_contents($json_file), true) ?? [];

                                foreach ($users as $user) {
                                    if ($user['role'] === 'user') {
                                        echo '<tr>';
                                        echo '<td class="nom">'.$user['first_name'].' '.$user['last_name'].'</td>';
                                        echo '<td>';
                                        if ($user['blocked']) {
                                            echo '<div class="block">Bloqué<img src="../img/svg/block.svg" alt="block"></div>';
                                        } else {
                                            echo '<div class="unblock">Non bloqué<img src="../img/svg/check.svg" alt="check"></div>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($user['vip']) {
                                            echo '<div class="vip">VIP<img src="../img/svg/etoile.svg" alt="etoile"></div>';
                                        } else {
                                            echo '<div class="vip">Non VIP<img src="../img/svg/etoile.svg" alt="etoile"></div>';
                                        }
                                        echo '</td>';
                                        echo '<td class="date">'.$user['date_inscription'].'</td>';
                                        echo '</tr>';
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="../js/custom-cursor.js"></script>
    <script src="../js/admin.js"></script>

</body>

</html>