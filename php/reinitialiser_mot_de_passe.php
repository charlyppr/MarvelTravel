<?php
require_once 'session.php';
check_none_auth($_SESSION['current_url'] ?? "../index.php");

$message = "";
$alertType = "";
$showForm = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = "Token invalide ou manquant. Veuillez faire une nouvelle demande de réinitialisation.";
    $alertType = "error";
} else {
    $json_file = "../json/users.json";
    
    if (file_exists($json_file)) {
        $users = json_decode(file_get_contents($json_file), true) ?? [];
        $validToken = false;
        $tokenUserKey = -1;
        
        foreach ($users as $key => $user) {
            if (isset($user['reset_token']) && $user['reset_token'] === $token) {
                // Vérifier si le token n'a pas expiré
                if (isset($user['reset_expiry']) && strtotime($user['reset_expiry']) > time()) {
                    $validToken = true;
                    $tokenUserKey = $key;
                    $showForm = true;
                } else {
                    $message = "Le lien de réinitialisation a expiré. Veuillez faire une nouvelle demande.";
                    $alertType = "error";
                }
                break;
            }
        }
        
        if (!$validToken && empty($message)) {
            $message = "Token invalide ou expiré. Veuillez faire une nouvelle demande de réinitialisation.";
            $alertType = "error";
        }
        
        // Traitement du formulaire de réinitialisation
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
            $new_password = trim($_POST['new_password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');
            
            if (empty($new_password) || empty($confirm_password)) {
                $message = "Veuillez remplir tous les champs.";
                $alertType = "error";
                $showForm = true;
            } elseif ($new_password !== $confirm_password) {
                $message = "Les mots de passe ne correspondent pas.";
                $alertType = "error";
                $showForm = true;
            } elseif (strlen($new_password) < 8) {
                $message = "Le mot de passe doit contenir au moins 8 caractères.";
                $alertType = "error";
                $showForm = true;
            } else {
                // Mettre à jour le mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $users[$tokenUserKey]['password'] = $hashed_password;
                
                // Supprimer le token de réinitialisation
                unset($users[$tokenUserKey]['reset_token']);
                unset($users[$tokenUserKey]['reset_expiry']);
                
                // Sauvegarder les modifications
                if (file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT))) {
                    $message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.";
                    $alertType = "success";
                    $showForm = false;
                } else {
                    $message = "Une erreur est survenue lors de la réinitialisation du mot de passe.";
                    $alertType = "error";
                    $showForm = true;
                }
            }
        }
    } else {
        $message = "Une erreur est survenue. Fichier utilisateurs non trouvé.";
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
    <title>Marvel Travel • Réinitialisation du mot de passe</title>

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
            <a href="../index.php" class="retour"><img src="../img/svg/fleche-gauche.svg" alt="fleche retour"></a>

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Réinitialiser votre mot de passe</span>
                <span class="sous-titre-3">Saisissez votre nouveau mot de passe</span>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $alertType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($showForm): ?>
                <form class="form" action="reinitialiser_mot_de_passe.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
                    <div class="mdp password-field-container">
                        <img src="../img/svg/lock.svg" alt="Lock Icon">
                        <input type="password" id="new_password" name="new_password" placeholder="Nouveau mot de passe" required>
                        <button type="button" class="password-toggle-btn" title="Afficher le mot de passe">
                            <img src="../img/svg/eye-slash.svg" alt="Afficher le mot de passe" class="eye-icon">
                        </button>
                    </div>

                    <div class="mdp password-field-container">
                        <img src="../img/svg/lock.svg" alt="Lock Icon">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                        <button type="button" class="password-toggle-btn" title="Afficher le mot de passe">
                            <img src="../img/svg/eye-slash.svg" alt="Afficher le mot de passe" class="eye-icon">
                        </button>
                    </div>

                    <button class="next-button" type="submit">Réinitialiser mon mot de passe<img src="../img/svg/sparkle.svg" alt="etoile"></button>
                </form>
            <?php else: ?>
                <div class="other-text" style="margin-top: 20px;">
                    <a href="connexion.php">Retour à la connexion</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

<script src="../js/password-toggle.js"></script>
<script src="../js/form-validation.js"></script>

</html>