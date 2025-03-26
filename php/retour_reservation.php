<?php
session_start();
require('getapikey.php');

// Récupération des paramètres
$transaction = $_GET['transaction'];
$montant = $_GET['montant'];
$vendeur = $_GET['vendeur'];
$status = $_GET['status'];
$controlRecu = $_GET['control'];

$api_key = getAPIKey($vendeur);

$controlCalcule = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $status . "#");

// Récupération des informations de la commande
$json_file = "../json/commandes.json";
$commandes = json_decode(file_get_contents($json_file), true);
$commande = null;

foreach ($commandes as $key => $cmd) {
    if ($cmd['transaction'] === $transaction) {
        $commandes[$key]['control'] = $controlRecu;
        $commandes[$key]['status'] = $status;
        $commande = $commandes[$key];
        file_put_contents($json_file, json_encode($commandes, JSON_PRETTY_PRINT));
        break;
    }
}

// Vérifier si la commande existe
if (!$commande) {
    header('Location: ../destination.php');
    exit;
}

// Récupérer les informations sur le voyage
$json_voyages = "../json/voyages.json";
$voyages = json_decode(file_get_contents($json_voyages), true);
$voyage = null;
foreach ($voyages as $v) {
    if ($v['titre'] === $commande['voyage']) {
        $voyage = $v;
        break;
    }
}

// Calculer la durée du séjour
$date_debut = new DateTime($commande['date_debut']);
$date_fin = new DateTime($commande['date_fin']);
$duree = $date_debut->diff($date_fin)->days;

$paiement_valide = $controlRecu === $controlCalcule;
$paiement_accepte = $paiement_valide && $status === 'accepted';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation - Marvel Travel</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/reservation.css">
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

        <div class="recap-section">
            <h2>Détails du voyage</h2>
            <div class="recap-info">
                <div class="recap-row">
                    <span class="recap-label">Destination:</span>
                    <span class="recap-value"><?php echo htmlspecialchars($commande['voyage']); ?></span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Période:</span>
                    <span class="recap-value">Du <?php echo date('d/m/Y', strtotime($commande['date_debut'])); ?> au
                        <?php echo date('d/m/Y', strtotime($commande['date_fin'])); ?></span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Durée:</span>
                    <span class="recap-value"><?php echo $duree; ?> jour(s)</span>
                </div>
            </div>
        </div>

        <div class="recap-section">
            <h2>Voyageurs</h2>
            <div class="recap-info">
                <?php foreach ($commande['voyageurs'] as $index => $voyageur): ?>
                    <div class="voyageur-recap">
                        <h3>Voyageur <?php echo $index + 1; ?></h3>
                        <div class="recap-row">
                            <span class="recap-label">Nom complet:</span>
                            <span
                                class="recap-value"><?php echo htmlspecialchars($voyageur['civilite'] . ' ' . $voyageur['prenom'] . ' ' . $voyageur['nom']); ?></span>
                        </div>
                        <div class="recap-row">
                            <span class="recap-label">Date de naissance:</span>
                            <span
                                class="recap-value"><?php echo date('d/m/Y', strtotime($voyageur['date_naissance'])); ?></span>
                        </div>
                        <div class="recap-row">
                            <span class="recap-label">Nationalité:</span>
                            <span class="recap-value"><?php echo htmlspecialchars($voyageur['nationalite']); ?></span>
                        </div>
                        <div class="recap-row">
                            <span class="recap-label">N° passeport:</span>
                            <span class="recap-value"><?php echo htmlspecialchars($voyageur['passport']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($commande['options'])): ?>
            <div class="recap-section">
                <h2>Options sélectionnées</h2>
                <div class="recap-info">
                    <?php foreach ($commande['options'] as $option): ?>
                        <div class="option-recap">
                            <div class="recap-row">
                                <span class="recap-label">Étape:</span>
                                <span class="recap-value"><?php echo htmlspecialchars($option['etape']); ?></span>
                            </div>
                            <div class="recap-row">
                                <span class="recap-label">Option:</span>
                                <span class="recap-value"><?php echo htmlspecialchars($option['option']); ?></span>
                            </div>
                            <div class="recap-row">
                                <span class="recap-label">Participants:</span>
                                <span class="recap-value"><?php echo $option['nb_participants']; ?> personne(s)</span>
                            </div>
                            <div class="recap-row">
                                <span class="recap-label">Prix:</span>
                                <span class="recap-value"><?php echo number_format($option['total'], 2, ',', ' '); ?> €</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

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
                    <a href="javascript:history.back()" class="action-button primary-button">
                        Réessayer le paiement
                        <img src="../img/svg/refresh.svg" alt="refresh" width="20">
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html>