<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_firstname = trim($_POST['login_firstname'] ?? '');
    $login_lastname = trim($_POST['login_lastname'] ?? '');
    $login_mail = trim($_POST['login_mail'] ?? '');
    $login_pass = trim($_POST['login_pass'] ?? '');

    $csv_file = "../csv/data.csv";
    $inscri = 0;
    
    if ($login_firstname && $login_lastname && $login_mail && $login_pass) {
        if (!filter_var($login_mail, FILTER_VALIDATE_EMAIL)) {
            $inscri = 3; // Email invalide
        } else {
            if (file_exists($csv_file)) {
                $file = fopen($csv_file, "r");
                while (($line = fgetcsv($file, 1000, ",")) !== FALSE) {
                    if ($line[2] == $login_mail) {
                        $inscri = 2; // Email déjà utilisé
                        break;
                    }
                }
                fclose($file);
            }
            
            if ($inscri == 0) {
                $file = fopen($csv_file, "a", ",");
                if (filesize($csv_file) > 0) {
                    fwrite($file, PHP_EOL); // Ajoute un saut de ligne pour éviter que les données s’écrivent sur la même ligne
                }
                fputcsv($file, [$login_firstname, $login_lastname, $login_mail, password_hash($login_pass, PASSWORD_BCRYPT)]);
                fclose($file);
                $inscri = 1; // Inscription réussie
            }
        }
    }

    // Stocker le résultat dans une session et rediriger
    $_SESSION['inscri'] = $inscri;
    header("Location: inscription.php"); // Recharge la page sans réafficher le formulaire
    exit();
}
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

            <a href="../index.php" class="logo-container">
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
                            <?php
                                if (isset($_SESSION['inscri'])) {
                                    if ($_SESSION['inscri'] == 2) {
                                        echo "<p class='sous-titre-3'>Cette adresse email est déjà utilisée</p>";
                                    } elseif ($_SESSION['inscri'] == 3) {
                                        echo "<p class='sous-titre-3'>Adresse email invalide</p>";
                                    } elseif ($_SESSION['inscri'] == 1) {
                                        echo "<p class='sous-titre-3'>Inscription réussie</p>";
                                    }
                                    unset($_SESSION['inscri']); // Supprime la variable après affichage
                                }
                            ?>
                        </div>

                <div class="other-text">
                    <a href="connexion.php">Déjà membre chez nous ?&nbsp<span>Se connecter</span></a>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>

</body>

</html>