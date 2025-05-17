<?php
require_once 'session.php';

// Vérifier si les informations du passeport sont disponibles
if (!isset($_SESSION['passport_info'])) {
    header("Location: connexion.php");
    exit();
}

$passport_info = $_SESSION['passport_info'];

// Stocker les identifiants en session pour la connexion automatique
if (isset($_SESSION['auto_login'])) {
    $email = $_SESSION['auto_login']['email'];
    $role = $_SESSION['auto_login']['role'];
    $first_name = $_SESSION['auto_login']['first_name'];
    $last_name = $_SESSION['auto_login']['last_name'];
    $civilite = $_SESSION['auto_login']['civilite'] ?? '';
    $date_naissance = $_SESSION['auto_login']['date_naissance'] ?? '';
    $nationalite = $_SESSION['auto_login']['nationalite'] ?? '';
    $passport_id = $_SESSION['auto_login']['passport_id'] ?? '';

    // Créer la session utilisateur si le bouton est cliqué
    if (isset($_GET['auto_login']) && $_GET['auto_login'] === '1') {
        // ENREGISTREMENT DANS LE FICHIER JSON À CETTE ÉTAPE UNIQUEMENT
        if (isset($_SESSION['user_data_complete']) && !isset($_SESSION['user_registered'])) {
            $user_data = $_SESSION['user_data_complete'];

            // Vérifier que le mot de passe est présent
            if (!isset($user_data['password'])) {
                // Rediriger vers l'inscription si le mot de passe est manquant
                session_destroy();
                header("Location: inscription.php?error=missing_password");
                exit();
            }

            // Vérifier si l'utilisateur existe déjà
            $json_file = "../json/users.json";
            $users = [];
            if (file_exists($json_file)) {
                $json_data = file_get_contents($json_file);
                $users = json_decode($json_data, true) ?? [];
            }

            // Vérifier si l'email est déjà utilisé (éviter les doublons)
            $email_exists = false;
            foreach ($users as $user) {
                if (isset($user['email']) && $user['email'] === $user_data['email']) {
                    $email_exists = true;
                    break;
                }
            }

            // Ajouter l'utilisateur seulement s'il n'existe pas déjà
            if (!$email_exists) {
                $users[] = $user_data;
                file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));
                
                // Envoi de l'email de bienvenue
                require_once 'send_email.php';
                send_welcome_email(
                    $user_data['email'],
                    $user_data['first_name'] . ' ' . $user_data['last_name'],
                    $passport_info['passport_id']
                );
            }

            // Marquer l'utilisateur comme enregistré
            $_SESSION['user_registered'] = true;
        }

        // Créer la session utilisateur
        $_SESSION['user'] = $email;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['civilite'] = $civilite;
        $_SESSION['date_naissance'] = $date_naissance;
        $_SESSION['nationalite'] = $nationalite;
        $_SESSION['passport_id'] = $passport_id;

        // Nettoyer les données temporaires
        unset($_SESSION['passport_info']);
        unset($_SESSION['auto_login']);
        unset($_SESSION['user_data_complete']);

        // Rediriger vers l'accueil
        header("Location: ../index.php");
        exit();
    }
}

// Récupérer le thème depuis le cookie
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Votre Passeport Multiversel</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/variables.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="stylesheet" href="../css/passeport.css">
    <link rel="stylesheet" href="../css/legal-pages.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    <script src="../js/passeport.js" defer></script>
</head>

<body class="<?php echo $theme; ?>-theme">

    <div class="passport-page">
        <div class="passport-wrapper">

            <div class="congratulation-header">
                <span class="title">Felicitations!</span>
                <span class="subtitle">Votre Passeport Multiversel est prêt</span>
            </div>

            <div class="passport-container">
                <img src="../img/svg/shield.svg" alt="Marvel Logo" class="watermark">
                <div class="passport-header">
                    <div class="passport-logo">MARVEL TRAVEL</div>
                    <div><?php echo date('d/m/Y'); ?></div>
                </div>

                <div class="passport-title">PASSEPORT MULTIVERSEL</div>

                <div class="passport-id">
                    <?php echo chunk_split($passport_info['passport_id'], 2, ' '); ?>
                </div>

                <div class="passport-data">
                    <div class="data-row">
                        <span class="data-label">NOM:</span>
                        <span class="data-value"><?php echo htmlspecialchars($passport_info['name']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">DATE DE NAISSANCE:</span>
                        <span
                            class="data-value"><?php echo date('d/m/Y', strtotime($passport_info['date_naissance'])); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">NATIONALITÉ:</span>
                        <span class="data-value"><?php echo htmlspecialchars($passport_info['nationalite']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">DATE D'ÉMISSION:</span>
                        <span
                            class="data-value"><?php echo date('d/m/Y', strtotime($passport_info['date_emission'])); ?></span>
                    </div>
                </div>

                <div class="passport-footer">
                    Ce passeport vous permet de voyager à travers tout le multivers Marvel.
                </div>
            </div>

            <div class="rules-checkbox">
                <input type="checkbox" id="rules-accept" required>
                <label for="rules-accept">
                    J'accepte les <a href="#" id="show-cgv">règles de Marvel Travel</a> et je m'engage à respecter les
                    lois du multivers
                </label>
            </div>

            <a href="?auto_login=1" class="continue-button" id="continue-btn"
                style="opacity: 0.5; pointer-events: none;">
                Entrer dans le multivers
                <img src="../img/svg/fleche-droite.svg" alt="fleche">
            </a>
        </div>
    </div>

    <!-- Modal des CGV -->
    <div id="cgv-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="titre">Conditions Générales de Vente</h1>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <?php include 'cgv-light.php'; ?>
            </div>
        </div>
    </div>

</body>

</html>