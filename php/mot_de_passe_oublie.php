<?php
require_once 'session.php';
check_none_auth($_SESSION['current_url'] ?? "../index.php");

$message = "";
$alertType = "";

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
                    
                    // URL de réinitialisation
                    $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/MarvelTravel/php/reinitialiser_mot_de_passe.php?token=" . $token;
                    
                    // Envoyer l'email via Brevo
                    require_once 'send_email.php';
                    $subject = "Réinitialisation de votre mot de passe Marvel Travel";
                    $htmlContent = "
                        <html>
                        <head>
                            <title>Réinitialisation de votre mot de passe</title>
                        </head>
                        <body>
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                <h2 style='color: #e23636;'>Marvel Travel - Réinitialisation de mot de passe</h2>
                                <p>Bonjour {$user['first_name']},</p>
                                <p>Vous avez demandé la réinitialisation de votre mot de passe sur Marvel Travel.</p>
                                <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe:</p>
                                <p><a href='{$resetUrl}' style='padding: 10px 15px; background-color: #e23636; color: white; text-decoration: none; border-radius: 5px;'>Réinitialiser mon mot de passe</a></p>
                                <p>Ce lien est valable pendant 1 heure.</p>
                                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.</p>
                                <p>L'équipe Marvel Travel</p>
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
    <link rel="stylesheet" href="../css/alert.css">
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
                    <input type="email" id="email" name="email" placeholder="Votre email" required autocomplete="email">
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