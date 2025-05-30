<?php
require_once 'session.php';
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
            $userExists = false;
            
            foreach ($users as $user) {
                if ($user['email'] === $login_mail) {
                    $userExists = true;
                    
                    if (password_verify($login_pass, $user['password'])) {
                        // Vérifier si l'utilisateur est bloqué
                        if (isset($user['blocked']) && $user['blocked'] === true) {
                            $_SESSION['login_mail'] = $login_mail;
                            $_SESSION['connexion'] = 2; // Code spécifique pour utilisateur bloqué
                            header("Location: connexion.php");
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
                        
                        // Récupérer et appliquer le thème de l'utilisateur depuis users.json
                        if (isset($user['theme'])) {
                            $userTheme = $user['theme'];
                            // Définir le cookie avec le thème de l'utilisateur
                            setcookie('theme', $userTheme, time() + (30 * 24 * 60 * 60), '/');
                        }

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
                    } else {
                        // Mot de passe incorrect
                        $_SESSION['connexion'] = 1; // Code pour connexion échouée
                        header("Location: connexion.php");
                        exit();
                    }
                }
            }
            
            if (!$userExists) {
                // Email introuvable dans la base de données
                $_SESSION['connexion'] = 3; // Nouveau code pour email inexistant
                header("Location: connexion.php");
                exit();
            }
        }
    } else {
        // Champs vides
        $_SESSION['connexion'] = 4; // Nouveau code pour champs vides
        header("Location: connexion.php");
        exit();
    }
}

// Récupérer le thème (en utilisant la fonction helper)
$theme = load_user_theme();
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

                <a href="mot_de_passe_oublie.php" class="redir-text" id="forgotPasswordLink">Mot de passe oublié dans la galaxie ?</a>

                <button class="next-button" type="submit">Entrer dans le Multivers<img src="../img/svg/sparkle.svg"
                        alt="etoile"></button>

                <a href="connexion_code.php" class="code-text" id="codeLink">Se connecter avec un code unique</a>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Ajouter l'email au lien quand il est disponible
                        const emailInput = document.getElementById('email');
                        const codeLink = document.getElementById('codeLink');
                        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
                        
                        // Mise à jour initiale si l'email est déjà rempli
                        updateLinks();
                        
                        // Mise à jour à chaque modification du champ email
                        emailInput.addEventListener('input', updateLinks);
                        
                        function updateLinks() {
                            if (emailInput.value) {
                                const encodedEmail = encodeURIComponent(emailInput.value);
                                codeLink.href = "connexion_code.php?email=" + encodedEmail;
                                forgotPasswordLink.href = "mot_de_passe_oublie.php?email=" + encodedEmail;
                            } else {
                                codeLink.href = "connexion_code.php";
                                forgotPasswordLink.href = "mot_de_passe_oublie.php";
                            }
                        }
                    });
                </script>

                <div class="other-text">
                    <a href="inscription.php">Pas de passeport Multiversel ?&nbsp;<span>Créer un compte</span></a>
                </div>
                <?php
                // Affichage du message si une connexion a été tentée
                if (isset($_SESSION['connexion'])) {
                    $alertType = '';
                    $alertMsg = '';
                    if ($_SESSION['connexion'] == 0) {
                        $alertType = 'success';
                        $alertMsg = "Connexion réussie ! <a href='../index.php'><span>Retour à l'accueil</span></a>";
                    } elseif ($_SESSION['connexion'] == 1) {
                        $alertType = 'error';
                        $alertMsg = "Identifiant ou mot de passe incorrect";
                    } elseif ($_SESSION['connexion'] == 2) {
                        $alertType = 'error';
                        $alertMsg = "Votre compte a été bloqué. Veuillez contacter l'administrateur.";
                    } elseif ($_SESSION['connexion'] == 3) {
                        $alertType = 'error';
                        $alertMsg = "Cet email n'existe pas dans notre base de données. <a href='inscription.php'><span>Créer un compte</span></a>";
                    } elseif ($_SESSION['connexion'] == 4) {
                        $alertType = 'error';
                        $alertMsg = "Veuillez remplir tous les champs";
                    }
                    echo '<div class="message ' . $alertType . '">' . $alertMsg . '</div>';
                    unset($_SESSION['connexion']);
                }
                ?>
            </form>
        </div>
    </div>

</body>

<script src="../js/password-toggle.js"></script>
<script src="../js/form-validation.js"></script>

</html>