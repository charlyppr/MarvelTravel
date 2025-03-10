<?php
    $connexion = 1;
    if(empty($_POST['login_mail']) == False && empty($_POST['login_pass']) == False){
        $login_mail = $_POST['login_mail'];
        $login_pass = $_POST['login_pass'];
        $file = fopen("../csv/data.csv", "r", ",");
        while(($line = fgetcsv($file)) !== FALSE){
            if($line[0] == $login_mail && $line[1] == $login_pass){
                $connexion = 0;
                break;
            }
        }
        fclose($file);
    }
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Connexion</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/spiderman-pin.svg" alt="spiderman pin" class="spiderman-pin">
            <img src="../img/svg/hulk-pin.svg" alt="hulk-pin" class="hulk-pin">

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
                <span class="titre-2">Se connecter avec l'email</span>
                <span class="sous-titre-3">Déjà explorateur ? Voyage avec nous dans le Multivers</span>
            </div>

            <form class="form" action="connexion.php" method="post">
                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="login_mail" placeholder="Email" required autocomplete="email">
                </div>

                <div class="mdp">
                    <img src="../img/svg/lock.svg" alt="Lock Icon">
                    <input type="password" id="password" name="login_pass" placeholder="Mot de passe" required>
                </div>

                <a href="#" class="redir-text">Mot de passe oublié dans la galaxie ?</a>

                <button class="next-button" type="submit">Entrer dans le Multivers<img src="../img/svg/sparkle.svg"
                        alt="etoile"></button>

                <div class="other-text">
                <?php
                        if($connexion == 0){
                            echo "<p>Vous êtes connecté</p>";
                        }
                ?>
                </div>
                <div class="other-text">
                    <a href="inscription.html">Pas de passeport Multiversel ?&nbsp;<span>Créer un compte</span></a>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>

</body>

</html>