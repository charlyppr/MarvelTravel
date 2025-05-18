<?php
require_once 'session.php';
check_none_auth($_SESSION['current_url'] ?? "../index.php");

$message = "";
$alertType = "";

// Récupérer l'email de l'URL ou de la session
$email_value = '';

// Priorité à l'email dans l'URL
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email_value = trim($_GET['email']);
}
// Puis la session de connexion si disponible
else if (isset($_SESSION['login_mail']) && !empty($_SESSION['login_mail'])) {
    $email_value = $_SESSION['login_mail'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($email)) {
        $json_file = "../json/users.json";
        
        if (file_exists($json_file)) {
            $users = json_decode(file_get_contents($json_file), true) ?? [];
            $userExists = false;
            
            foreach ($users as $key => $user) {
                if ($user['email'] === $email) {
                    $userExists = true;
                    // Générer un token unique
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Stocker le token dans le fichier users.json
                    $users[$key]['reset_token'] = $token;
                    $users[$key]['reset_expiry'] = $expiry;
                    file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));
                    
                    // URL de réinitialisation - Adaptation pour fonctionner à la fois en local et en production
                    $is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], 'mamp') !== false);
                    $base_path = $is_local ? "/MarvelTravel" : "";
                    $protocol = $is_local ? "http" : "https";
                    $resetUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $base_path . "/php/reinitialiser_mot_de_passe.php?token=" . $token . "&email=" . urlencode($email);
                    
                    // Envoyer l'email via Brevo
                    require_once 'send_email.php';
                    $subject = "Réinitialisation de votre mot de passe Marvel Travel";
                    $htmlContent = "
                        <html>
                        <head>
                            <title>Réinitialisation de votre mot de passe</title>
                            <meta charset='utf-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        </head>
                        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f7f7f7;'>
                            <div style='max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                                <!-- Header -->
                                <div style='text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eeeeee;'>
                                    <img src='{$protocol}://{$_SERVER['HTTP_HOST']}{$base_path}/img/svg/logo.svg' alt='Marvel Travel Logo' style='height: 60px; margin-bottom: 15px;'>                                    
                                </div>
                                
                                <!-- Content -->
                                <div style='color: #444444; line-height: 1.6; font-size: 16px;'>
                                    <h2 style='color: #222222; margin-top: 0;'>Réinitialisation de mot de passe</h2>
                                    
                                    <p style='margin-bottom: 20px;'>Bonjour <strong>{$user['first_name']}</strong>,</p>
                                    
                                    <p style='margin-bottom: 20px;'>Vous avez demandé la réinitialisation de votre mot de passe sur Marvel Travel.</p>
                                    
                                    <p style='margin-bottom: 25px;'>Pour définir un nouveau mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
                                    
                                    <div style='text-align: center; margin: 30px 0;'>
                                        <a href='{$resetUrl}' style='display: inline-block; padding: 12px 24px; background-color: #e23636; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);'>Réinitialiser mon mot de passe</a>
                                    </div>
                                    
                                    <p style='margin-top: 25px; font-size: 14px;'>Ce lien est valable pendant <strong>1 heure</strong>. Après cette période, vous devrez faire une nouvelle demande.</p>
                                    
                                    <p style='font-size: 14px;'>Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.</p>
                                </div>
                                
                                <!-- Footer -->
                                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee; text-align: center; color: #777777; font-size: 14px;'>
                                    <p>&copy; 2025 Marvel Travel. Tous droits réservés.</p>
                                    <div style='margin-top: 15px;'>
                                        <img src='{$protocol}://{$_SERVER['HTTP_HOST']}{$base_path}/img/svg/spiderman-pin.svg' alt='Spiderman pin' style='height: 60px; margin-bottom: 15px;'>
                                    </div>
                                    <p style='margin-top: 15px;'>Pour toute question, contactez notre support client. <a href='mailto:contact@marveltravel.shop'>contact@marveltravel.shop</a></p>
                                </div>
                            </div>
                        </body>
                        </html>";
                    
                    $sent = send_password_reset_email($email, $user['first_name'], $subject, $htmlContent);
                    
                    if ($sent) {
                        $message = "Un email de réinitialisation a été envoyé à votre adresse email si celle-ci existe dans notre base de données.";
                        $alertType = "success";
                    } else {
                        $message = "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer.";
                        $alertType = "error";
                    }
                    break;
                }
            }
            
            if (!$userExists) {
                // Pour des raisons de sécurité, ne pas indiquer si l'email existe ou non
                $message = "Un email de réinitialisation a été envoyé à votre adresse email si celle-ci existe dans notre base de données.";
                $alertType = "success";
            }
        } else {
            $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
            $alertType = "error";
        }
    } else {
        $message = "Veuillez saisir votre adresse email.";
        $alertType = "error";
    }
}

// Récupérer le thème
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Mot de passe oublié</title>

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
                <span class="titre-2">Mot de passe oublié</span>
                <span class="sous-titre-3">Saisissez votre email pour recevoir un lien de réinitialisation</span>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $alertType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="form" action="mot_de_passe_oublie.php" method="post">
                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="email" placeholder="Votre email" required autocomplete="email" value="<?php echo htmlspecialchars($email_value); ?>">
                </div>

                <button class="next-button" type="submit">Recevoir le lien<img src="../img/svg/sparkle.svg" alt="etoile"></button>

                <div class="other-text">
                    <a href="connexion.php">Retour à la connexion</a>
                </div>
            </form>
        </div>
    </div>

</body>

<script src="../js/form-validation.js"></script>

</html>