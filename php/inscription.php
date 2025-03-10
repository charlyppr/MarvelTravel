<?php
    $login_lastname = $_POST['login_lastname'];
    $login_pass = $_POST['login_pass'];
    $login_mail = $_POST['login_mail'];
    $login_firstname = $_POST['login_firstname'];
    $file = fopen("../csv/data.csv", "a", ",");
    fputcsv($file, array($login_name, $login_pass, $login_mail));
    fclose($file);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Inscription</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/shield.svg" alt="shield pin" class="shield-pin">
            <img src="../img/svg/captain.svg" alt="captain pin" class="captain-pin">

            <a href="javascript:history.back()" class="retour"><img src="../img/svg/fleche-gauche.svg"
                    alt="fleche retour"></a>

            <a href="../index.html" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">S'inscrire avec l'email</span>
                <span class="sous-titre-3">Première visite ? Obtenez votre Passeport Multiversel !</span>
            </div>

            <form class="form" action="inscription.php" method="post">
                <div class="form-row">
                    <input type="text" name="login_firstname" id="prenom" placeholder="Prénom" required autocomplete="name">
                    <input type="text" name="login_lastname" id="nom" placeholder="Nom" required autocomplete="family-name">
                </div>

                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="login_mail" placeholder="Email" required autocomplete="email">
                </div>

                <div class="mdp">
                    <img src="../img/svg/lock.svg" alt="Lock Icon">
                    <input type="password" id="mdp" name="login_pass" placeholder="Mot de passe" required>
                </div>

                <button class="next-button" type="submit">Suivant<img src="../img/svg/fleche-droite.svg"
                        alt="fleche"></button>
                <div class="other_text">
                <div class="other-text">
                    <a href="connexion.html">Déjà membre chez nous ?&nbsp<span>Se connecter</span></a>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>

</body>

</html>