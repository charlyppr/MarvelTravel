<?php
require_once "session.php";

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

        // Rediriger vers l'accueil
        header("Location: ../index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Votre Passeport Multiversel</title>

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

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Félicitations!</span>
                <span class="sous-titre-3">Votre Passeport Multiversel est prêt</span>
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

            <a href="?auto_login=1" class="continue-button">
                Entrer dans le multivers
                <img src="../img/svg/fleche-droite.svg" alt="fleche">
            </a>
        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>
</body>

</html>