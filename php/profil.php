<?php
require('session.php');
check_auth('connexion.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Profil</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

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
                                <svg width="16" height="18" viewBox="0 0 163 175" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M17.9736 174.814H144.868C156.106 174.814 162.842 169.366 162.842 160.373C162.842 135.709 131.137 101.903 81.4209 101.903C31.6678 101.903 0 135.709 0 160.373C0 169.366 6.73547 174.814 17.9736 174.814ZM81.458 84.9999C102.929 84.9999 120.494 66.0819 120.494 42.0149C120.494 18.6194 102.743 0 81.458 0C60.1725 0 42.4594 18.8433 42.4594 42.1269C42.4594 66.0819 60.0237 84.9999 81.458 84.9999Z"
                                        fill="#0D0D0D" />
                                </svg>
                                <span class="titre-card">Mon profil</span>
                            </div>


                            <div class="card-content">
                                <div class="user-info">
                                    <img src="../img/svg/spiderman-pin.svg" alt="photo de profil">
                                    <div class="info">
                                        <span
                                            class="nom"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></span>
                                        <span class="mail"><?= $_SESSION['email'] ?></span>
                                    </div>
                                </div>

                                <span class="trait"></span>

                                <div class="modif-info">
                                    <div class="row">
                                        <div class="row-user-info">
                                            <span>Prénom :</span>
                                            <span><?= $_SESSION['first_name'] ?></span>
                                        </div>
                                        <img src="../img/svg/edit.svg" alt="modification">
                                    </div>

                                    <div class="row">
                                        <div class="row-user-info">
                                            <span>Nom :</span>
                                            <span><?= $_SESSION['last_name'] ?></span>
                                        </div>
                                        <img src="../img/svg/edit.svg" alt="modification">
                                    </div>

                                    <div class="row">
                                        <div class="row-user-info">
                                            <span>Email :</span>
                                            <span><?= $_SESSION['email'] ?></span>
                                        </div>
                                        <img src="../img/svg/edit.svg" alt="modification">
                                    </div>

                                    <div class="row">
                                        <div class="row-user-info">
                                            <span>Mot de passe :</span>
                                            <span>•••••••••••</span>
                                        </div>
                                        <img src="../img/svg/edit.svg" alt="modification">
                                    </div>
                                </div>
                            </div>




                            <span class="sous-texte"><span style="font-weight: 600;">Modifier </span>vos informations
                                juste ici</span>
                        </div>


                        <div class="card">
                            <div class="header-text">
                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10.3864 20.9883C16.1227 20.9883 20.7766 16.287 20.7766 10.4961C20.7766 4.70126 16.1227 0 10.3864 0C4.64996 0 0 4.70126 0 10.4961C0 16.287 4.64996 20.9883 10.3864 20.9883ZM10.3864 18.6338C5.93285 18.6338 2.33076 14.995 2.33076 10.4961C2.33076 5.99722 5.93285 2.35452 10.3864 2.35452C14.8398 2.35452 18.4419 5.99722 18.4419 10.4961C18.4419 14.995 14.8398 18.6338 10.3864 18.6338Z"
                                        fill="#0D0D0D" />
                                    <path
                                        d="M7.62404 15.7539C7.89369 15.7539 8.07863 15.6216 8.4446 15.2596L10.34 13.341C10.3632 13.3137 10.3978 13.3137 10.4209 13.341L12.324 15.2596C12.6862 15.6216 12.8634 15.7539 13.1408 15.7539C13.5569 15.7539 13.8381 15.4504 13.8381 14.9716V6.81842C13.8381 5.82989 13.3026 5.28503 12.3318 5.28503H8.42534C7.45451 5.28503 6.92285 5.82989 6.92285 6.81842V14.9716C6.92285 15.4504 7.20411 15.7539 7.62404 15.7539Z"
                                        fill="#0D0D0D" />
                                </svg>

                                <span class="titre-card">Mes commandes</span>
                            </div>

                            <div class="card-content">
                                <div class="user-info">
                                    <?php
                                    $json_file_path = '../json/commandes.json';
                                    $commandes = false;
                                    if (file_exists($json_file_path)) {
                                        $json_file = file_get_contents($json_file_path);
                                        $data = json_decode($json_file, true);
                                        if (empty($data)) {
                                            echo "<div class='info-commande'>
                                                    <span class='nom-commande'>Aucune commande</span>
                                                </div>";
                                        }
                                        if (is_array($data)) {
                                            foreach ($data as $commande) {
                                                if ($commande['acheteur'] == $_SESSION['email']) {
                                                    $commandes = true;
                                                    echo "<a href='commande.php?transaction=" . $commande['transaction'] . "' class='info-commande'>
                                                                    <span class='nom-commande'>" . $commande['voyage'] . "</span>-
                                                                    <span class='nom-commande'>" . $commande['date_debut'] . "</span>
                                                                </a>";
                                                }
                                            }
                                        }
                                    }
                                    if (!$commandes) {
                                    } else if (!$commandes) {
                                        echo "<div class='info-commande'>
                                                    <span class='nom-commande'>Aucune commande</span>
                                                </div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <span class="sous-texte"> <span style="font-weight: 600;">Retrouver </span>ici tous vos
                                voyages</span>

                            <div class="header-text">
                                <span class="titre-card">Mes messages</span>
                            </div>

                            <div class="card-content">
                                <div class="user-info">
                                    <?php
                                    $json_file_path = '../json/messages.json';
                                    $messages = false;
                                    if (file_exists($json_file_path)) {
                                        $json_file = file_get_contents($json_file_path);
                                        $data = json_decode($json_file, true);
                                        if (is_array($data)) {
                                            foreach ($data as $message) {
                                                if ($message['email'] == $_SESSION['email']) {
                                                    $messages = true;
                                                    echo "<div class='info-commande'>
                                                        <span class='nom-commande'>" . $message['objet'] . "</span>-
                                                    </div>";

                                                }
                                            }
                                        }
                                        if (!$messages) {
                                        }
                                    }
                                    if (!$messages) {
                                        echo "<div class='info-commande'>
                                                    <span class='nom-commande'>Aucun message</span>
                                                </div>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <span class="sous-texte"> <span style="font-weight: 600;">Retrouver </span>ici tous vos
                                messages</span>
                        </div>
                    </div>
                </div>
            </div>


        </div>

    </div>

    <?php include 'modals.php'; ?>

    <script src="../js/custom-cursor.js"></script>
    <script src="../js/modal-handlers.js"></script>
</body>

</html>