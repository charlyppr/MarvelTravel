<?php
require_once('../session.php');
check_auth('../connexion.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Vérifier si l'utilisateur consulte une nouvelle destination
// et effacer les données si c'est le cas
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données sauvegardées en session
$form_data = get_form_data('etape1');
$date_debut_value = $form_data ? $form_data['date_debut'] : '';
$date_fin_value = $form_data ? $form_data['date_fin'] : '';
$nb_personne_value = $form_data ? $form_data['nb_personne'] : 1;

$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Convertir la durée recommandée en nombre de jours pour la validation
$duree_recommandee = 7; // Valeur par défaut
if (isset($voyage['dates']['duree'])) {
    $duree_str = $voyage['dates']['duree'];
    $duree_parts = explode(' ', $duree_str);
    if (count($duree_parts) >= 2 && is_numeric($duree_parts[0])) {
        $duree_recommandee = (int) $duree_parts[0];
    }
}

// Date minimum = aujourd'hui
$date_min = date('Y-m-d');

// Calculer un prix total en fonction du nombre de personnes
$prix_base = $voyage['prix'];
$prix_total = $prix_base * $nb_personne_value;
$prix_total_formatte = number_format($prix_total, 2, ',', ' ');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Réserver <?php echo htmlspecialchars($voyage['titre']); ?> • Marvel Travel</title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape1.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <div class="reservation-container">
        <div class="booking-header">
            <div class="breadcrumb">
                <a href="../destination.php" class="breadcrumb-link">
                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                    <span>Destinations</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Étape 1: Dates et voyageurs</span>
            </div>

            <div class="booking-progress">
                <div class="progress-step active4">
                    <div class="step-indicator">1</div>
                    <div class="step-label">Dates</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-indicator">2</div>
                    <div class="step-label">Voyageurs</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-indicator">3</div>
                    <div class="step-label">Options</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-indicator">4</div>
                    <div class="step-label">Paiement</div>
                </div>
            </div>
        </div>

        <div class="booking-content">
            <div class="destination-info">
                <h1 class="destination-title"><?php echo htmlspecialchars($voyage['titre']); ?></h1>

                <div class="destination-image">
                    <img src="../<?php echo htmlspecialchars($voyage['image']); ?>"
                        alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="destination-photo">

                    <div class="destination-highlights">
                        <div class="highlight-item">
                            <img src="../../img/svg/clock.svg" alt="Durée" class="highlight-icon">
                            <span>Durée recommandée: <?php echo htmlspecialchars($voyage['dates']['duree']); ?></span>
                        </div>
                        <div class="highlight-item">
                            <img src="../../img/svg/tag.svg" alt="Prix" class="highlight-icon">
                            <span>À partir de <?php echo number_format($voyage['prix'], 2, ',', ' '); ?> € par
                                personne</span>
                        </div>
                    </div>
                </div>

                <div class="destination-description">
                    <h2>À propos de cette aventure</h2>
                    <p><?php echo htmlspecialchars($voyage['resume']); ?></p>
                </div>
            </div>

            <div class="booking-form-container">
                <form action="etape2.php?id=<?php echo $id; ?>" method="post" id="reservationForm" class="booking-form">
                    <div class="card form-card">
                        <div class="card-header">
                            <img src="../../img/svg/calendar.svg" alt="Dates" class="card-icon">
                            <h3 class="card-title">Choisissez vos dates</h3>
                        </div>

                        <div class="card-content">
                            <div class="form-group">
                                <label for="date_debut">Date de début</label>
                                <div class="form-field">
                                    <img src="../../img/svg/calendar.svg" alt="Date" class="field-icon">
                                    <input type="date" name="date_debut" id="date_debut" min="<?php echo $date_min; ?>"
                                        value="<?php echo $date_debut_value; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="date_fin">Date de fin</label>
                                <div class="form-field">
                                    <img src="../../img/svg/calendar.svg" alt="Date" class="field-icon">
                                    <input type="date" name="date_fin" id="date_fin" min="<?php echo $date_min; ?>"
                                        value="<?php echo $date_fin_value; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nb_personne">Nombre de voyageurs</label>
                                <div class="form-field">
                                    <img src="../../img/svg/users.svg" alt="Voyageurs" class="field-icon">
                                    <input type="number" name="nb_personne" id="nb_personne" min="1" max="10"
                                        value="<?php echo $nb_personne_value; ?>" required>
                                </div>
                            </div>

                            <div class="price-summary">
                                <div class="price-row">
                                    <span>Prix par personne</span>
                                    <span><?php echo number_format($voyage['prix'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="price-row">
                                    <span>Nombre de voyageurs</span>
                                    <span id="nb_personnes_display"><?php echo $nb_personne_value; ?></span>
                                </div>
                                <div class="price-total">
                                    <span>Total</span>
                                    <span id="prix_total"><?php echo $prix_total_formatte; ?> €</span>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="../destination.php" class="secondary-button">
                                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                                    Retour aux destinations
                                </a>
                                <button type="submit" class="primary-button">
                                    Continuer vers les voyageurs
                                    <img src="../../img/svg/arrow-right.svg" alt="Continuer">
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
    <script>
        // Mettre à jour dynamiquement le nombre de personnes et le prix total
        document.getElementById('nb_personne').addEventListener('input', function () {
            const nbPersonnes = parseInt(this.value);
            const prixBase = <?php echo $voyage['prix']; ?>;
            const prixTotal = nbPersonnes * prixBase;

            document.getElementById('nb_personnes_display').textContent = nbPersonnes;
            document.getElementById('prix_total').textContent = new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(prixTotal).replace('€', '') + ' €';
        });
    </script>
</body>

</html>