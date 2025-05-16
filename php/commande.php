<?php
require_once 'session.php';
$_SESSION['current_url'] = current_url();
check_auth($_SESSION['current_url'] ?? "../index.php");

if (!isset($_GET['transaction'])) {
    die("<h1>Erreur</h1><p>ID de transaction manquant ! <a href='profil.php'>Retour</a></p>");
}

$transaction = $_GET['transaction'];
$json_file_path = '../json/commandes.json';
$voyages_file_path = '../json/voyages.json';

// Vérifier si les fichiers existent et peuvent être lus
if (!file_exists($json_file_path) || !is_readable($json_file_path)) {
    die("<h1>Erreur</h1><p>Fichier de commandes introuvable ! <a href='profil.php'>Retour</a></p>");
}

if (!file_exists($voyages_file_path) || !is_readable($voyages_file_path)) {
    die("<h1>Erreur</h1><p>Fichier de voyages introuvable ! <a href='profil.php'>Retour</a></p>");
}

$json_file = file_get_contents($json_file_path);
$json_data = json_decode($json_file, true);
$voyages_file = file_get_contents($voyages_file_path);
$voyages_data = json_decode($voyages_file, true);

// Vérifier si les JSON sont valides
if (!is_array($json_data)) {
    die("<h1>Erreur</h1><p>Erreur de lecture des commandes ! <a href='profil.php'>Retour</a></p>");
}

if (!is_array($voyages_data)) {
    die("<h1>Erreur</h1><p>Erreur de lecture des voyages ! <a href='profil.php'>Retour</a></p>");
}

// Rechercher la transaction dans le tableau
$commande = null;
foreach ($json_data as $cmd) {
    if ($cmd['transaction'] === $transaction) {
        $commande = $cmd;
        break;
    }
}

// Vérifier si la transaction a été trouvée
if (!$commande) {
    die("<h1>Erreur</h1><p>Transaction introuvable ! <a href='profil.php'>Retour</a></p>");
}

// Récupérer l'image et les informations du voyage depuis le fichier voyages.json
$image_path = "../img/destinations/default.jpg"; // Image par défaut
$voyage_info = null;
foreach ($voyages_data as $voyage) {
    if ($voyage['titre'] === $commande['voyage']) {
        $image_path = $voyage['image'];
        $voyage_info = $voyage;
        break;
    }
}

// Formatage des dates et calculs
$date_debut = date('d/m/Y', strtotime($commande['date_debut']));
$date_fin = date('d/m/Y', strtotime($commande['date_fin']));
$debut = new DateTime($commande['date_debut']);
$fin = new DateTime($commande['date_fin']);
$duree = $debut->diff($fin)->days;
$montant_formatte = number_format($commande['montant'], 2, ',', ' ');
$statut_class = ($commande['status'] == 'accepted') ? 'status-success' : 'status-pending';
$statut_texte = ($commande['status'] == 'accepted') ? 'Confirmé' : 'En attente';
$status_icon = ($commande['status'] == 'accepted') ? 'check-circle' : 'alert-circle';
$date_achat = isset($commande['date_achat']) ?
    date('d/m/Y à H:i', strtotime($commande['date_achat'])) :
    'Non spécifiée';

// Nombre de jours restants avant le départ
$today = new DateTime();
$jours_restants = $today < $debut ? $today->diff($debut)->days : 0;

// Récupérer le thème depuis le cookie
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voyage à <?= htmlspecialchars($commande['voyage']) ?> - Marvel Travel</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/commande.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <header class="reservation-header">
                        <div class="hero-banner" style="background-image: url('<?= htmlspecialchars($image_path) ?>')">
                            <div class="overlay">
                                <div class="header-content">
                                    <div class="breadcrumb">
                                        <a href="mes-voyages.php" class="breadcrumb-link">
                                            <img src="../img/svg/arrow-left.svg" alt="Retour">
                                            <span>Mes voyages</span>
                                        </a>
                                        <span class="breadcrumb-separator">/</span>
                                        <span class="breadcrumb-current">Détails</span>
                                    </div>

                                    <div class="destination-title">
                                        <h1><?= htmlspecialchars($commande['voyage']) ?></h1>
                                    </div>

                                    <div class="reservation-meta">
                                        <div class="meta-item">
                                            <img src="../img/svg/calendar.svg" alt="Calendrier">
                                            <span><?= $date_debut ?> au <?= $date_fin ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <img src="../img/svg/clock.svg" alt="Durée">
                                            <span><?= $duree ?> jours</span>
                                        </div>
                                        <div class="meta-item">
                                            <img src="../img/svg/users.svg" alt="Voyageurs">
                                            <span><?= count($commande['voyageurs']) ?>
                                                voyageur<?= count($commande['voyageurs']) > 1 ? 's' : '' ?></span>
                                        </div>
                                        <?php if ($commande['status'] == 'accepted' && $jours_restants > 0): ?>
                                            <div class="meta-item meta-item-highlight">
                                                <img src="../img/svg/plane-departure.svg" alt="Départ">
                                                <span>Départ dans <?= $jours_restants ?>
                                                    jour<?= $jours_restants > 1 ? 's' : '' ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="transaction-bar">
                        <div class="transaction-info">
                            <div class="transaction-label">N° de transaction</div>
                            <div class="transaction-value"><?= $commande['transaction'] ?></div>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-label">Date de réservation</div>
                            <div class="transaction-value"><?= $date_achat ?></div>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-label">Montant total</div>
                            <div class="transaction-value price"><?= $montant_formatte ?> €</div>
                        </div>
                        <div class="transaction-status <?= $statut_class ?>">
                            <?= $statut_texte ?>
                        </div>
                    </div>

                    <div class="modif-reservation">
                        <div class="modif-header">
                            <h3>Actions rapides</h3>
                            <p>Gérez facilement votre réservation avec ces options</p>
                        </div>
                        <div class="modif-buttons">
                            <?php if ($commande['status'] == 'accepted'): ?>
                                <button class="action-button generate-ticket-btn" data-transaction="<?= $commande['transaction'] ?>">
                                    Télécharger mon billet
                                    <img src="../img/svg/download.svg" alt="Télécharger">
                                </button>
                                <a href="modifier-reservation.php?transaction=<?= $commande['transaction'] ?>" class="action-button secondary-button">
                                    Modifier ma réservation
                                    <img src="../img/svg/edit.svg" alt="Modifier">
                                </a>
                            <?php else: ?>
                                <div class="status-message">
                                    <img src="../img/svg/info.svg" alt="Info">
                                    <p>Vous pourrez télécharger votre billet et modifier la réservation une fois confirmée.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <section class="reservation-summary">
                        <div class="summary-header">
                            <h2>Résumé de votre aventure</h2>
                            <?php if (isset($voyage_info['resume'])): ?>
                                <p class="voyage-description"><?= htmlspecialchars($voyage_info['resume']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="cards-grid">
                            <div class="card travelers-card">
                                <div class="card-header">
                                    <img src="../img/svg/users.svg" alt="Voyageurs" class="card-icon">
                                    <h3 class="card-title">Équipe d'aventuriers</h3>
                                </div>
                                <div class="card-content">
                                    <div class="travelers-list">
                                        <?php foreach ($commande['voyageurs'] as $index => $voyageur): ?>
                                            <div class="traveler-item">
                                                <div class="traveler-avatar">
                                                    <?= substr($voyageur['prenom'], 0, 1) . substr($voyageur['nom'], 0, 1) ?>
                                                </div>
                                                <div class="traveler-info">
                                                    <span class="traveler-name">
                                                        <?= htmlspecialchars($voyageur['prenom']) . ' ' . htmlspecialchars(strtoupper($voyageur['nom'])) ?>
                                                        <?php if ($index === 0): ?>
                                                            <span class="traveler-badge">Principal</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="traveler-details">
                                                        <?= isset($voyageur['age']) ? $voyageur['age'] . ' ans' : 'Adulte' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card itinerary-card">
                                <div class="card-header">
                                    <img src="../img/svg/map-pin.svg" alt="Étapes" class="card-icon">
                                    <h3 class="card-title">Itinéraire du voyage</h3>
                                </div>
                                <div class="card-content">
                                    <div class="itinerary-container">
                                        <?php
                                        $unique_etapes = [];
                                        foreach ($commande['options'] as $option) {
                                            if (isset($option['etape']) && !in_array($option['etape'], $unique_etapes)) {
                                                $unique_etapes[] = $option['etape'];
                                            }
                                        }
                                        ?>

                                        <?php if (count($unique_etapes) > 0): ?>
                                            <div class="timeline">
                                                <?php foreach ($unique_etapes as $index => $etape): ?>
                                                    <div class="timeline-item">
                                                        <div class="timeline-point" data-step="<?= $index + 1 ?>"></div>
                                                        <div class="timeline-content">
                                                            <span class="etape-name"><?= htmlspecialchars($etape) ?></span>
                                                            <?php if ($index === 0): ?>
                                                                <span class="etape-badge">Départ</span>
                                                            <?php elseif ($index === count($unique_etapes) - 1): ?>
                                                                <span class="etape-badge end">Arrivée</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="empty-notice">Aucune étape définie pour ce voyage</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card options-card">
                                <div class="card-header">
                                    <img src="../img/svg/briefcase.svg" alt="Options" class="card-icon">
                                    <h3 class="card-title">Activités et services</h3>
                                </div>
                                <div class="card-content">
                                    <div class="options-container">
                                        <?php if (count($commande['options']) > 0): ?>
                                            <div class="options-grid">
                                                <?php foreach ($commande['options'] as $option): ?>
                                                    <div class="option-item">
                                                        <div class="option-icon">
                                                            <img src="../img/svg/check-circle.svg" alt="Inclus">
                                                        </div>
                                                        <div class="option-info">
                                                            <span class="option-name">
                                                                <?= isset($option['nom']) ? htmlspecialchars($option['nom']) : (isset($option['etape']) ? htmlspecialchars($option['etape']) : 'Option') ?>
                                                            </span>
                                                            <?php if (isset($option['description'])): ?>
                                                                <span
                                                                    class="option-desc"><?= htmlspecialchars($option['description']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="empty-notice">Aucune option sélectionnée pour ce voyage</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card payment-card">
                                <div class="card-header">
                                    <img src="../img/svg/credit-card.svg" alt="Paiement" class="card-icon">
                                    <h3 class="card-title">Détails du paiement</h3>
                                </div>
                                <div class="card-content">
                                    <div class="payment-details">
                                        <div class="payment-row">
                                            <span class="payment-label">Prix de base</span>
                                            <span class="payment-value">
                                                <?= isset($voyage_info['prix']) ? number_format($voyage_info['prix'], 2, ',', ' ') . ' €' : 'Non spécifié' ?>
                                            </span>
                                        </div>
                                        <div class="payment-row">
                                            <span class="payment-label">Nombre de voyageurs</span>
                                            <span class="payment-value"><?= count($commande['voyageurs']) ?></span>
                                        </div>
                                        <div class="payment-row">
                                            <span class="payment-label">Options supplémentaires</span>
                                            <span class="payment-value">
                                                <?php
                                                $prix_base = isset($voyage_info['prix']) ? $voyage_info['prix'] : 0;
                                                $supplements = $commande['montant'] - ($prix_base * count($commande['voyageurs']));
                                                echo $supplements > 0 ? '+ ' . number_format($supplements, 2, ',', ' ') . ' €' : 'Inclus';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="payment-row total">
                                            <span class="payment-label">Total payé</span>
                                            <span class="payment-value"><?= $montant_formatte ?> €</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <?php if ($commande['status'] == 'accepted'): ?>
                        <section class="tips-section">
                            <h2>Conseils pour votre voyage</h2>
                            <div class="tips-grid">
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <img src="../img/svg/clock.svg" alt="Horaires">
                                    </div>
                                    <div class="tip-content">
                                        <h3>Arrivez en avance</h3>
                                        <p>Prévoyez d'arriver au moins 3 heures avant le départ pour les formalités.</p>
                                    </div>
                                </div>
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <img src="../img/svg/briefcase.svg" alt="Bagages">
                                    </div>
                                    <div class="tip-content">
                                        <h3>Bagages</h3>
                                        <p>Limite de poids : 20kg par personne. N'oubliez pas vos documents d'identité!</p>
                                    </div>
                                </div>
                                <div class="tip-item">
                                    <div class="tip-icon">
                                        <img src="../img/svg/phone.svg" alt="Contact">
                                    </div>
                                    <div class="tip-content">
                                        <h3>Assistance 24/7</h3>
                                        <p>Contactez notre équipe au +33 1 23 45 67 89 en cas de besoin.</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <div class="actions-container">
                        <a href="mes-voyages.php" class="action-button secondary-button">
                            <img src="../img/svg/arrow-left.svg" alt="Retour">
                            Retour à mes voyages
                        </a>
                        <a href="../php/destination.php" class="action-button primary-button">
                            Réserver un nouveau voyage
                            <img src="../img/svg/plane.svg" alt="Voyage">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

<script src="../js/ticket-generator.js"></script>

</html>