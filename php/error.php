<?php
$error_type = isset($_GET['error']) ? $_GET['error'] : 'unknown';

$error_messages = [
    'invalid_transaction' => 'Transaction invalide ou paramètres manquants.',
    'missing_data' => 'Données de réservation manquantes ou invalides.',
    'manque_etape' => 'Une étape de la réservation est manquante.',
    'manque_voyage' => 'Le voyage demandé n\'existe pas.',
    'manque_voyage_id' => 'L\'identifiant du voyage est invalide.',
    'already_processed' => 'Cette commande a déjà été traitée. Merci de ne pas recharger la page de confirmation.',
    'unknown' => 'Une erreur inattendue s\'est produite.'
];

$error_message = $error_messages[$error_type] ?? $error_messages['unknown'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Erreur • Marvel Travel</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/confirmation.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <div class="confirmation-container">
        <div class="confirmation-header error-header">
            <div class="error-icon">
                <img src="../img/svg/alert-circle.svg" alt="Erreur">
            </div>
            <h1>Une erreur est survenue</h1>
            <p class="confirmation-subtitle"><?php echo htmlspecialchars($error_message); ?></p>
        </div>

        <div class="confirmation-content">
            <div class="confirmation-card error-card">
                <div class="card-header">
                    <img src="../img/svg/info.svg" alt="Information" class="card-icon">
                    <h2>Que faire ?</h2>
                </div>
                <div class="card-content">
                    <ul class="error-reasons">
                        <li>Vérifiez que vous avez suivi le processus de réservation correctement</li>
                        <li>Ne modifiez pas l'URL manuellement</li>
                        <li>Si le problème persiste, contactez notre service client</li>
                    </ul>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="destination.php" class="action-button home-button">
                    <img src="../img/svg/home.svg" alt="Accueil">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script src="../js/nav.js"></script>
</body>

</html>