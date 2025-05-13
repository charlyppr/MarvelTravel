<?php
require_once "session.php";
check_none_auth($_SESSION['current_url'] ?? "../index.php");

// Récupérer l'email saisi précédemment (s'il existe)
$login_mail_value = $_SESSION['login_mail'] ?? '';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_mail = trim($_POST['login_mail'] ?? '');
    $login_pass = trim($_POST['login_pass'] ?? '');

    // Mémoriser l'email saisi pour le réafficher en cas d'erreur
    $_SESSION['login_mail'] = $login_mail;

    // Mise à jour immédiate de $login_mail_value pour l'afficher en cas d'erreur
    $login_mail_value = $login_mail;

    $json_file = "../json/users.json";
    $connexion = 1;

    if (!empty($login_mail) && !empty($login_pass)) {
        if (file_exists($json_file)) {
            // Lire le fichier JSON et le convertir en tableau PHP
            $users = json_decode(file_get_contents($json_file), true) ?? [];
            foreach ($users as $user) {
                if ($user['email'] === $login_mail && password_verify($login_pass, $user['password'])) {
                    // Vérifier si l'utilisateur est bloqué
                    if (isset($user['blocked']) && $user['blocked'] === true) {
                        $_SESSION['login_mail'] = $login_mail;
                        $_SESSION['connexion'] = 2; // Code spécifique pour utilisateur bloqué
                        exit();
                    }
                    
                    session_start();
                    $_SESSION['user'] = $login_mail;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $login_mail;
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    // Ajouter ces lignes pour stocker toutes les données utilisateur
                    $_SESSION['civilite'] = $user['civilite'] ?? '';
                    $_SESSION['date_naissance'] = $user['date_naissance'] ?? '';
                    $_SESSION['nationalite'] = $user['nationalite'] ?? '';
                    if (empty($_SESSION['nationalite']) && isset($user['nationalite'])) {
                        $_SESSION['nationalite'] = $user['nationalite'];
                    }
                    $_SESSION['passport_id'] = $user['passport_id'] ?? '';

                    // En cas de connexion réussie, supprimer l'email mémorisé
                    unset($_SESSION['login_mail']);

                    // Mise à jour de la date de dernière connexion
                    $user['last_login'] = date("Y-m-d H:i:s");

                    // Enregistrer la mise à jour dans le fichier JSON
                    foreach ($users as $key => $u) {
                        if ($u['email'] === $login_mail) {
                            $users[$key]['last_login'] = $user['last_login'];
                            break;
                        }
                    }
                    file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));

                    if (isset($_SESSION['error'])) {
                        unset($_SESSION['error']);
                    }

                    // Redirection
                    if (isset($_SESSION['current_url'])) {
                        header('Location: ' . $_SESSION['current_url']);
                    } else {
                        header('Location: ../index.php');
                    }
                    exit();
                }
            }
        }
    }

    // Gérer l'échec de connexion
    $_SESSION['connexion'] = 1; // Code pour connexion échouée

}

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Connexion</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="stylesheet" href="../css/form-validation.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">
    
    <div class="card">
        <div class="card-content">
            <img src="../img/svg/spiderman-pin.svg" alt="spiderman pin" class="spiderman-pin">
            <img src="../img/svg/hulk-pin.svg" alt="hulk-pin" class="hulk-pin">
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
                <span class="titre-2">Se connecter avec l'email</span>
                <span class="sous-titre-3">Déjà explorateur ? Voyage avec nous dans le Multivers</span>
            </div>

            <form class="form" action="connexion.php" method="post">
                
                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="login_mail" placeholder="Email" required autocomplete="email"
                        value="<?php echo htmlspecialchars($login_mail_value); ?>">
                </div>

                <div class="mdp password-field-container">
                    <img src="../img/svg/lock.svg" alt="Lock Icon">
                    <input type="password" id="password" name="login_pass" placeholder="Mot de passe" required>
                    <button type="button" class="password-toggle-btn" title="Afficher le mot de passe">
                        <img src="../img/svg/eye-slash.svg" alt="Afficher le mot de passe" class="eye-icon">
                    </button>
                </div>

                <a href="#" class="redir-text">Mot de passe oublié dans la galaxie ?</a>

                <button class="next-button" type="submit">Entrer dans le Multivers<img src="../img/svg/sparkle.svg"
                        alt="etoile"></button>

                <div class="other-text">
                    <a href="inscription.php">Pas de passeport Multiversel ?&nbsp;<span>Créer un compte</span></a>
                </div>
                <div class="other-text">
                    <?php
                    // Affichage du message si une connexion a été tentée
                    if (isset($_SESSION['connexion'])) {
                        if ($_SESSION['connexion'] == 0) {
                            echo "<a href='../index.php'>Connexion réussi !&nbsp<span>Retour à l'acceuil</span></a>";
                        } elseif ($_SESSION['connexion'] == 1) {
                            echo "<p>Identifiant ou mot de passe incorrect</p>";
                        } elseif ($_SESSION['connexion'] == 2) {
                            echo "<p>Votre compte a été bloqué. Veuillez contacter l'administrateur.</p>";
                        }
                        unset($_SESSION['connexion']);
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>

</body>

<script src="../js/password-toggle.js"></script>
<script src="../js/form-validation.js"></script>

</html>