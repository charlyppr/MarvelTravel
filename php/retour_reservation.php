<?php
session_start();
require('getapikey.php');

// Récupération des paramètres
$transaction = isset($_GET['transaction']) ? $_GET['transaction'] : '';
$montant = isset($_GET['montant']) ? $_GET['montant'] : '';
$vendeur = isset($_GET['vendeur']) ? $_GET['vendeur'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$controlRecu = isset($_GET['control']) ? $_GET['control'] : '';

// Vérifier que tous les paramètres sont présents
if (!$transaction || !$montant || !$vendeur || !$status || !$controlRecu) {
    header('Location: ../destination.php');
    exit;
}

// Vérifier que la commande en attente existe en session
if (!isset($_SESSION['commande_en_attente'])) {
    header('Location: ../destination.php');
    exit;
}

// Récupérer la commande en attente
$commande = $_SESSION['commande_en_attente'];

// Vérifier que la transaction correspond
if ($commande['transaction'] !== $transaction) {
    header('Location: ../destination.php');
    exit;
}

// Vérifier l'intégrité de la transaction
$api_key = getAPIKey($vendeur);
$controlCalcule = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $status . "#");
$paiement_valide = $controlRecu === $controlCalcule;
$paiement_accepte = $paiement_valide && $status === 'accepted';

// Si le paiement est accepté, enregistrer la commande dans le fichier JSON
if ($paiement_accepte) {
    $commande_json_file = "../json/commandes.json";
    $commandes = [];

    if (file_exists($commande_json_file) && filesize($commande_json_file) > 0) {
        $commandes = json_decode(file_get_contents($commande_json_file), true);
    }

    // Mettre à jour le statut de la commande
    $commande['status'] = $status;
    $commande['control'] = $controlRecu;

    // Ajouter la commande au tableau
    $commandes[] = $commande;

    // Enregistrer dans le fichier
    file_put_contents($commande_json_file, json_encode($commandes, JSON_PRETTY_PRINT));
}

// Récupérer les informations du voyage pour l'affichage
$json_voyages = "../json/voyages.json";
$voyages_array = json_decode(file_get_contents($json_voyages), true);
$voyage = null;

foreach ($voyages_array as $v) {
    if ($v['titre'] === $commande['voyage']) {
        $voyage = $v;
        break;
    }
}

// Calculer la durée du séjour
$date_debut = new DateTime($commande['date_debut']);
$date_fin = new DateTime($commande['date_fin']);
$duree = $date_debut->diff($date_fin)->days;

// Nettoyer la session uniquement si le paiement est accepté
if ($paiement_accepte) {
    unset($_SESSION['commande_en_attente']);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation - Marvel Travel</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/retour_reservation.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <div class="recap-container">
        <div class="confirmation-header">
            <h1>Confirmation de votre reservation</h1>
        </div>

        <?php if ($paiement_accepte): ?>
            <div class="status-banner success">
                <img src="../img/svg/check.svg" alt="succès" width="30">
                <span>Votre paiement a été accepté et votre réservation est confirmée !</span>
            </div>
        <?php else: ?>
            <div class="status-banner error">
                <img src="../img/svg/warning.svg" alt="erreur" width="30">
                <span><?php echo $paiement_valide ? "Votre paiement a été refusé." : "Erreur : l'intégrité des données n'est pas vérifiée."; ?></span>
            </div>
        <?php endif; ?>

        <div class="transaction-info">
            <p><strong>Numéro de réservation:</strong> <span
                    class="transaction-number"><?php echo $transaction; ?></span></p>
            <p><strong>Date de réservation:</strong> <?php echo date('d/m/Y H:i'); ?></p>
            <?php if ($paiement_accepte): ?>
                <p>Un email de confirmation a été envoyé à <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <?php endif; ?>
        </div>

        <div class="recap-section prix-section">
            <h2>Détail du prix</h2>
            <div class="recap-info">
                <?php
                $prix_base = $commande['montant'];
                $prix_options = 0;

                if (!empty($commande['options'])) {
                    foreach ($commande['options'] as $option) {
                        $prix_options += $option['total'];
                    }
                    $prix_base -= $prix_options;
                }
                ?>
                <div class="recap-row">
                    <span class="recap-label">Prix de base (<?php echo $commande['nb_personne']; ?> personne(s)):</span>
                    <span class="recap-value"><?php echo number_format($prix_base, 2, ',', ' '); ?> €</span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Options:</span>
                    <span class="recap-value"><?php echo number_format($prix_options, 2, ',', ' '); ?> €</span>
                </div>
                <div class="recap-row total-row">
                    <span class="recap-label">Total:</span>
                    <span class="recap-value"><?php echo number_format($commande['montant'], 2, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>

        <div class="actions">
            <?php if ($paiement_accepte): ?>
                <a href="profil.php" class="action-button secondary-button">
                    <img src="../img/svg/person.svg" alt="profil" width="20">
                    Voir mes réservations
                </a>
                <a href="../index.php" class="action-button primary-button">
                    Retour à l'accueil
                    <img src="../img/svg/fleche-droite.svg" alt="flèche" width="20">
                </a>
            <?php else: ?>
                <a href="destination.php" class="action-button secondary-button">
                    <img src="../img/svg/fleche-gauche.svg" alt="flèche" width="20">
                    Retour aux destinations
                </a>
                <?php if ($paiement_valide): ?>
                    <?php
                    // Récupérer l'ID du voyage depuis les informations stockées
                    $voyage_id = null;
                    $json_voyages = "../json/voyages.json";
                    $voyages_array = json_decode(file_get_contents($json_voyages), true);

                    foreach ($voyages_array as $index => $v) {
                        if ($v['titre'] === $commande['voyage']) {
                            $voyage_id = $index;
                            break;
                        }
                    }

                    // Générer une nouvelle transaction
                    $new_transaction = uniqid();

                    // Préparer les paramètres pour le nouveau paiement
                    $montant = $commande['montant'];
                    $vendeur = $commande['vendeur'];

                    // Créer l'URL de retour
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                    $host = $_SERVER['HTTP_HOST'];
                    $script_name = $_SERVER['SCRIPT_NAME'];
                    $base_path = dirname(dirname($script_name));
                    $base_url = $protocol . "://" . $host . $base_path;
                    $retour = "{$base_url}/php/retour_reservation.php?transaction={$new_transaction}";

                    // Générer le nouveau code de contrôle
                    require_once('getapikey.php');
                    $api_key = getAPIKey($vendeur);
                    $control = md5($api_key . "#" . $new_transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");

                    // Mettre à jour la transaction dans la commande en attente
                    $_SESSION['commande_en_attente']['transaction'] = $new_transaction;
                    ?>

                    <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' id="retryPaymentForm">
                        <input type='hidden' name='transaction' value='<?php echo $new_transaction; ?>'>
                        <input type='hidden' name='montant' value='<?php echo $montant; ?>'>
                        <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
                        <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
                        <input type='hidden' name='control' value='<?php echo $control; ?>'>

                        <button type="submit" class="action-button primary-button">
                            Réessayer le paiement
                            <img src="../img/svg/refresh.svg" alt="refresh" width="20">
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>


    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html>