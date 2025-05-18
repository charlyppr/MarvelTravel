<?php
require_once 'session.php';
check_none_auth($_SESSION['current_url'] ?? "../index.php");
require_once 'send_email.php';

// Initialiser les variables
$message = "";
$alertType = "";
$showCodeForm = false;
$email = "";

// Récupérer l'email saisi précédemment (s'il existe)
// Priorité donnée à l'email passé en paramètre URL
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email_value = trim($_GET['email']);
    $_SESSION['login_code_email'] = $email_value; // Sauvegarder en session pour les futures requêtes
} else {
    $email_value = $_SESSION['login_code_email'] ?? '';
}

// Récupérer également l'email de la page de connexion si disponible
if (empty($email_value) && isset($_SESSION['login_mail']) && !empty($_SESSION['login_mail'])) {
    $email_value = $_SESSION['login_mail'];
}

// Fonction pour générer un code à 6 chiffres
function generate_login_code() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Récupérer les messages de session (toujours vérifier, qu'on soit en mode formulaire de code ou non)
if (isset($_SESSION['code_message']) && isset($_SESSION['code_alert_type'])) {
    $message = $_SESSION['code_message'];
    $alertType = $_SESSION['code_alert_type'];
    
    // Nettoyer les messages de session après utilisation
    unset($_SESSION['code_message']);
    unset($_SESSION['code_alert_type']);
}

// Vérifier si on a été redirigé après envoi d'un code
if (isset($_GET['show_code_form']) && $_GET['show_code_form'] == '1') {
    $showCodeForm = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Étape 1 : Demande d'envoi du code
    if (isset($_POST['submit_email'])) {
        $email = trim($_POST['email'] ?? '');
        
        // Mémoriser l'email saisi pour le réafficher
        $_SESSION['login_code_email'] = $email;
        $email_value = $email;
        
        if (!empty($email)) {
            $json_file = "../json/users.json";
            
            if (file_exists($json_file)) {
                $users = json_decode(file_get_contents($json_file), true) ?? [];
                $userFound = false;
                
                foreach ($users as $key => $user) {
                    if ($user['email'] === $email) {
                        $userFound = true;
                        
                        // Générer un code à 6 chiffres
                        $code = generate_login_code();
                        
                        // Stocker le code et sa date d'expiration
                        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $users[$key]['login_code'] = $code;
                        $users[$key]['login_code_expiry'] = $expiry;
                        file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));
                        
                        // Envoyer l'email avec le code
                        $full_name = $user['first_name'] . ' ' . $user['last_name'];
                        $sent = send_login_code_email($email, $full_name, $code);
                        
                        if ($sent) {
                            // Rediriger vers une URL GET pour éviter les renvois lors des rechargements
                            $_SESSION['code_message'] = "Un code de connexion a été envoyé à votre adresse email.";
                            $_SESSION['code_alert_type'] = "success";
                            header("Location: connexion_code.php?show_code_form=1&email=" . urlencode($email));
                            exit();
                        } else {
                            $message = "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer.";
                            $alertType = "error";
                        }
                        break;
                    }
                }
                
                if (!$userFound) {
                    // Indiquer clairement que l'email n'existe pas
                    $_SESSION['code_message'] = "Cet email n'existe pas dans notre base de données. <a href='inscription.php'><span>Créer un compte</span></a>";
                    $_SESSION['code_alert_type'] = "error";
                    header("Location: connexion_code.php");
                    exit();
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
    // Étape 2 : Vérification du code
    else if (isset($_POST['submit_code'])) {
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        
        if (!empty($email) && !empty($code)) {
            $json_file = "../json/users.json";
            
            if (file_exists($json_file)) {
                $users = json_decode(file_get_contents($json_file), true) ?? [];
                $authenticated = false;
                
                foreach ($users as $key => $user) {
                    if ($user['email'] === $email && 
                        isset($user['login_code']) && 
                        $user['login_code'] === $code && 
                        isset($user['login_code_expiry']) && 
                        strtotime($user['login_code_expiry']) > time()) {
                        
                        // Code valide, authentifier l'utilisateur
                        session_start();
                        $_SESSION['user'] = $email;
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['email'] = $email;
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        $_SESSION['civilite'] = $user['civilite'] ?? '';
                        $_SESSION['date_naissance'] = $user['date_naissance'] ?? '';
                        $_SESSION['nationalite'] = $user['nationalite'] ?? '';
                        $_SESSION['passport_id'] = $user['passport_id'] ?? '';
                        
                        // Récupérer et appliquer le thème de l'utilisateur
                        if (isset($user['theme'])) {
                            setcookie('theme', $user['theme'], time() + (30 * 24 * 60 * 60), '/');
                        }
                        
                        // Nettoyer les codes utilisés
                        unset($users[$key]['login_code']);
                        unset($users[$key]['login_code_expiry']);
                        
                        // Enregistrer la date de dernière connexion
                        $users[$key]['last_login'] = date("Y-m-d H:i:s");
                        file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));
                        
                        // Supprimer les données de session temporaires
                        unset($_SESSION['login_code_email']);
                        
                        // Redirection
                        if (isset($_SESSION['current_url'])) {
                            header('Location: ' . $_SESSION['current_url']);
                        } else {
                            header('Location: ../index.php');
                        }
                        exit();
                    }
                }
                
                // Si on arrive ici, le code est invalide
                $message = "Code invalide ou expiré. Veuillez réessayer.";
                $alertType = "error";
                $showCodeForm = true;
            } else {
                $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
                $alertType = "error";
                $showCodeForm = true;
            }
        } else {
            $message = "Veuillez saisir votre code de connexion.";
            $alertType = "error";
            $showCodeForm = true;
        }
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
    <title>Marvel Travel • Connexion par code</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    <style>
        .code-input {
            display: flex;
            align-items: center;
            background-color: var(--background-secondary);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .code-input img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        .code-input input {
            background: none;
            border: none;
            font-size: 16px;
            width: 100%;
            color: var(--color-text-light);
            letter-spacing: 4px;
            font-family: monospace;
            font-weight: bold;
        }
        
        .code-input input:focus {
            outline: none;
        }
        
        .resend-container {
            text-align: center;
            margin-top: 15px;
        }
        
        .resend-button {
            color: var(--color-text-light);
            border: none;
            transition: all 0.3s ease;
            font-size: var(--font-size-xs);
            opacity: 0.5;
            background: none;
            cursor: not-allowed;
        }

        .code-input-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .code-box {
            width: 50px;
            height: 60px;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: var(--background-secondary);
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: var(--color-text-light);
            margin: 0 5px;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        .code-box:focus {
            border-color: #e23636;
            box-shadow: 0 0 0 2px rgba(226, 54, 54, 0.3);
            outline: none;
        }
        
        .code-box.active {
            border-color: #e23636;
            box-shadow: 0 0 0 2px rgba(226, 54, 54, 0.3);
        }
        
        .code-title {
            text-align: center;
            margin-bottom: 15px;
            color: var(--color-text-light);
        }
        
        .resend-container {
            text-align: center;
            margin-top: 15px;
        }

        .resend-button.active {
            opacity: 1;
            cursor: pointer;
        }

        .resend-button.active:hover {
            color: var(--color-text-muted);
        }
        
    </style>
</head>

<body class="<?php echo $theme; ?>-theme">
    
    <div class="card">
        <div class="card-content">
            <img src="../img/svg/spiderman-pin.svg" alt="spiderman pin" class="spiderman-pin">
            <img src="../img/svg/hulk-pin.svg" alt="hulk-pin" class="hulk-pin">
            <a href="connexion.php" class="retour"><img src="../img/svg/fleche-gauche.svg" alt="fleche retour"></a>

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Connexion par code unique</span>
                <span class="sous-titre-3"><?php echo $showCodeForm ? "Entrez le code reçu par email" : "Recevez un code de connexion par email"; ?></span>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $alertType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!$showCodeForm): ?>
            <!-- Formulaire pour demander le code -->
            <form class="form" action="connexion_code.php" method="post">
                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="email" placeholder="Votre email" required autocomplete="email" value="<?php echo htmlspecialchars($email_value); ?>">
                </div>

                <button class="next-button" type="submit" name="submit_email">Recevoir un code<img src="../img/svg/sparkle.svg" alt="etoile"></button>

                <div class="other-text">
                    <a href="connexion.php">Revenir à la connexion par mot de passe</a>
                </div>
            </form>
            <?php else: ?>
            <!-- Formulaire pour entrer le code -->
            <form class="form" action="connexion_code.php" method="post" id="codeForm">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_value); ?>">
                <input type="hidden" name="code" id="codeInput" value="">
                
                <p class="code-title">Saisissez le code envoyé à<br><strong><?php echo htmlspecialchars($email_value); ?></strong></p>
                
                <div class="code-input-container">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                    <input type="text" class="code-box" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                </div>

                <button class="next-button" type="submit" name="submit_code">Vérifier le code<img src="../img/svg/sparkle.svg" alt="etoile"></button>
                
                <div class="resend-container">
                    <button type="button" id="resendBtn" class="resend-button" disabled>
                        Renvoyer un code (<span id="countdown">30</span>s)
                    </button>
                </div>

                <div class="other-text">
                    <a href="connexion.php">Revenir à la connexion par mot de passe</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    <?php if ($showCodeForm): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const resendBtn = document.getElementById('resendBtn');
        const countdownEl = document.getElementById('countdown');
        const codeForm = document.getElementById('codeForm');
        const codeBoxes = document.querySelectorAll('.code-box');
        const codeInput = document.getElementById('codeInput');
        
        // Compte à rebours pour le renvoi de code
        let countdown = 30;
        const timer = setInterval(function() {
            countdown--;
            countdownEl.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.classList.add('active');
                resendBtn.textContent = 'Renvoyer un code';
            }
        }, 1000);
        
        // Gestion du renvoi de code
        resendBtn.addEventListener('click', function() {
            if (resendBtn.disabled) return;
            
            // Créer un formulaire pour demander un nouveau code
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'connexion_code.php';
            
            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'email';
            emailInput.value = '<?php echo htmlspecialchars($email_value); ?>';
            
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'submit_email';
            submitInput.value = '1';
            
            form.appendChild(emailInput);
            form.appendChild(submitInput);
            document.body.appendChild(form);
            form.submit();
        });
        
        // Gestion des champs de code
        codeBoxes.forEach((box, index) => {
            // Focus sur le premier champ au chargement
            if (index === 0) {
                setTimeout(() => box.focus(), 100);
            }
            
            // Gestion de la saisie
            box.addEventListener('input', function(e) {
                // Valider que seuls les chiffres sont entrés
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Passer au champ suivant si un chiffre est entré
                if (this.value.length === 1 && index < codeBoxes.length - 1) {
                    codeBoxes[index + 1].focus();
                }
                
                // Mettre à jour le champ caché avec le code complet
                updateHiddenInput();
                
            });
            
            // Gérer le collage de code
            box.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                
                // Ne traiter que les chiffres
                const digits = pastedData.replace(/[^0-9]/g, '');
                
                // Distribuer les chiffres dans les cases
                if (digits.length > 0) {
                    for (let i = 0; i < codeBoxes.length; i++) {
                        if (i < digits.length) {
                            codeBoxes[i].value = digits[i];
                        }
                    }
                    
                    // Focus sur la dernière case remplie ou la suivante si le code est incomplet
                    const focusIndex = Math.min(digits.length, codeBoxes.length - 1);
                    codeBoxes[focusIndex].focus();
                    
                    // Mettre à jour le champ caché
                    updateHiddenInput();
                
                }
            });
            
            // Gestion des touches spéciales
            box.addEventListener('keydown', function(e) {
                // Effacer et revenir au champ précédent sur Backspace
                if (e.key === 'Backspace') {
                    if (this.value.length === 0 && index > 0) {
                        codeBoxes[index - 1].focus();
                        codeBoxes[index - 1].select();
                    }
                }
                
                // Naviguer entre les champs avec les flèches
                if (e.key === 'ArrowLeft' && index > 0) {
                    codeBoxes[index - 1].focus();
                    e.preventDefault();
                }
                
                if (e.key === 'ArrowRight' && index < codeBoxes.length - 1) {
                    codeBoxes[index + 1].focus();
                    e.preventDefault();
                }
            });
            
            // Sélectionner tout le contenu au focus
            box.addEventListener('focus', function() {
                this.select();
                this.classList.add('active');
            });
            
            box.addEventListener('blur', function() {
                this.classList.remove('active');
            });
        });
        
        // Soumettre le formulaire
        codeForm.addEventListener('submit', function(e) {
            updateHiddenInput();
            
            // Empêcher la soumission si le code n'est pas complet
            if (!isCodeComplete()) {
                e.preventDefault();
                codeBoxes[0].focus();
            }
        });
        
        // Mettre à jour le champ caché avec le code complet
        function updateHiddenInput() {
            let code = '';
            codeBoxes.forEach(box => {
                code += box.value;
            });
            codeInput.value = code;
        }
        
        // Vérifier si le code est complet (6 chiffres)
        function isCodeComplete() {
            for (let box of codeBoxes) {
                if (box.value.length === 0) return false;
            }
            return true;
        }
    });
    <?php endif; ?>
    </script>

</body>

</html> 