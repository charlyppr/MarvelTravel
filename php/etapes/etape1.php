<?php
require_once '../session.php';
check_auth('../connexion.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Vérifier si nous venons d'une réinitialisation de recherche
$from_reset = isset($_GET['reset']) && $_GET['reset'] == 1;

// Récupérer les dates depuis l'URL si disponibles
$url_date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$url_date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';

// Vérifier si l'utilisateur change de voyage ou si l'URL contient des paramètres explicites
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
$reset_session = false;

// Si l'URL contient des paramètres de date (même vides), cela indique une nouvelle recherche
$date_params_in_url = isset($_GET['date_debut']) || isset($_GET['date_fin']);

// Effacer les données de session si:
// - Changement de voyage
// - Nouvelle requête avec des paramètres de date explicites
// - Vient d'une réinitialisation de recherche
if ($current_voyage_id !== $id || $date_params_in_url || $from_reset) {
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;

    // Si des dates sont passées dans l'URL et que ce n'est pas une réinitialisation, les stocker en session
    if ($date_params_in_url && !$from_reset) {
        $form_data = [
            'date_debut' => $url_date_debut,
            'date_fin' => $url_date_fin,
            'nb_personne' => 1 // Valeur par défaut
        ];
        store_form_data('etape1', $form_data);
    }
}

// Charger le panier pour récupérer les données
$panierJson = file_get_contents('../../json/panier.json');
$panier = json_decode($panierJson, true);

// Récupérer l'email de l'utilisateur connecté
$userEmail = $_SESSION['email'];

// Simplifier la gestion du panier - Mettre à jour l'étape atteinte
if (isset($_GET['from_cart']) && isset($_GET['cart_index'])) {
    $cart_index = (int) $_GET['cart_index'];

    // Vérifier que l'élément existe dans le panier de l'utilisateur
    if (isset($panier[$userEmail]['items'][$cart_index])) {
        // Forcer l'étape atteinte à 1
        $panier[$userEmail]['items'][$cart_index]['etape_atteinte'] = 1;
        // Stocker les données pour les étapes
        store_form_data('etape1', [
            'date_debut' => $panier[$userEmail]['items'][$cart_index]['date_debut'],
            'date_fin' => $panier[$userEmail]['items'][$cart_index]['date_fin'],
            'nb_personne' => $panier[$userEmail]['items'][$cart_index]['nb_personnes']
        ]);
        // Sauvegarder le panier mis à jour
        file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    }
}

// Récupérer les données du voyage dans le panier si on vient du panier
$panier_data = null;
if (isset($_GET['from_cart']) && isset($_GET['cart_index'])) {
    $cart_index = (int) $_GET['cart_index'];
    if (isset($panier[$userEmail]['items'][$cart_index])) {
        $panier_data = $panier[$userEmail]['items'][$cart_index];
        $panier[$userEmail]['items'][$cart_index]['etape_atteinte'] = 1;
        file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    }
}

// Récupération des données avec priorité à l'URL ou à la réinitialisation
if ($from_reset) {
    // Si on vient d'une réinitialisation explicite, utiliser des valeurs vides
    $date_debut_value = '';
    $date_fin_value = '';
    $nb_personne_value = 1;
} else if ($panier_data && $panier_data['voyage_id'] === $id) {
    // Si on vient du panier et que c'est le bon voyage, utiliser ces données
    $date_debut_value = $panier_data['date_debut'];
    $date_fin_value = $panier_data['date_fin'];
    $nb_personne_value = $panier_data['nb_personnes'];
} else if ($date_params_in_url) {
    // Si l'URL contient des paramètres de date, les utiliser en priorité
    $date_debut_value = $url_date_debut;
    $date_fin_value = $url_date_fin;
    $nb_personne_value = 1;
} else {
    // Sinon, vérifier s'il y a des données en session
    $form_data = get_form_data('etape1');
    if ($form_data) {
        $date_debut_value = $form_data['date_debut'];
        $date_fin_value = $form_data['date_fin'];
        $nb_personne_value = $form_data['nb_personne'];
    } else {
        // Valeurs par défaut
        $date_debut_value = '';
        $date_fin_value = '';
        $nb_personne_value = 1;
    }
}

// Stocker ces valeurs en session
store_form_data('etape1', [
    'date_debut' => $date_debut_value,
    'date_fin' => $date_fin_value,
    'nb_personne' => $nb_personne_value
]);

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

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $nb_personne = $_POST['nb_personne'];

    // Stocker les données pour les étapes suivantes
    store_form_data('etape1', [
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'nb_personne' => $nb_personne
    ]);


    // Ajouter au panier de manière simplifiée
    if (!isset($_GET['from_cart'])) {
        // Charger le panier existant
        $panierJson = file_get_contents('../../json/panier.json');
        $panier = json_decode($panierJson, true);

        // S'assurer que le panier est un tableau et que l'utilisateur y a une entrée
        if (!is_array($panier)) {
            $panier = [];
        }
        if (!isset($panier[$userEmail])) {
            $panier[$userEmail] = ['items' => []];
        }

        // Créer le nouvel élément avec la structure complète mais simplifiée
        $nouvel_item = [
            'voyage_id' => $id,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'nb_personnes' => $nb_personne,
            'prix_unitaire' => $voyage['prix'],
            'voyageurs' => $voyageurs,
            'options' => [],
            'prix_options' => 0,
            'etape_atteinte' => 1
        ];

        // Vérifier si le voyage existe déjà
        $item_index = null;
        if (isset($panier[$userEmail]['items'])) {
            foreach ($panier[$userEmail]['items'] as $index => $item) {
                if ($item['voyage_id'] === $id) {
                    $item_index = $index;
                    break;
                }
            }
        }

        // Mettre à jour ou ajouter l'item
        if ($item_index !== null) {
            $panier[$userEmail]['items'][$item_index] = $nouvel_item;
        } else {
            $panier[$userEmail]['items'][] = $nouvel_item;
        }

        // Sauvegarder le panier
        file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
    }

    // Rediriger vers l'étape 2 avec le bon chemin
    header(
        'Location: etape2.php?id=' . $id .
        ((isset($_GET['from_cart']) && isset($_GET['cart_index'])) ?
            '&from_cart=1&cart_index=' . $_GET['cart_index'] :
            (isset($_POST['from_cart']) && isset($_POST['cart_index']) ?
                '&from_cart=1&cart_index=' . $_POST['cart_index'] : '')
        )
    );
    exit;
}

// Récupérer le thème depuis le cookie
$theme = load_user_theme();

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Réserver <?php echo htmlspecialchars($voyage['titre']); ?> • Marvel Travel</title>

    <script src="../../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../../css/theme.css" id="theme">
    <link rel="stylesheet" href="../../css/reservation.css">
    <link rel="stylesheet" href="../../css/calendar.css">

    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <?php include '../nav.php'; ?>

    <div class="reservation-container">
        <div class="booking-header">
            <div class="breadcrumb">
                <a href="../destination.php?id=<?php echo $id; ?>" class="breadcrumb-link">
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
                <form action="etape1.php?id=<?php echo $id; ?>" method="post" id="reservationForm" class="booking-form">
                    <?php if (isset($_GET['from_cart']) && isset($_GET['cart_index'])): ?>
                        <input type="hidden" name="from_cart" value="1">
                        <input type="hidden" name="cart_index" value="<?php echo $_GET['cart_index']; ?>">
                    <?php endif; ?>

                    <?php if (!empty($voyageurs_data)): ?>
                        <?php foreach ($voyageurs_data as $index => $voyageur): ?>
                            <input type="hidden" name="civilite_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['civilite']); ?>">
                            <input type="hidden" name="nom_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['nom']); ?>">
                            <input type="hidden" name="prenom_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['prenom']); ?>">
                            <input type="hidden" name="date_naissance_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['date_naissance']); ?>">
                            <input type="hidden" name="nationalite_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['nationalite']); ?>">
                            <input type="hidden" name="passport_<?php echo $index + 1; ?>"
                                value="<?php echo htmlspecialchars($voyageur['passport']); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="card form-card">
                        <div class="card-header">
                            <img src="../../img/svg/calendar.svg" alt="Dates" class="card-icon">
                            <h3 class="card-title">Choisissez vos dates</h3>
                        </div>

                        <div class="card-content">
                            <div class="form-group">
                                <label for="date-debut-visible">Date d'arrivé</label>
                                <div class="form-field">
                                    <img src="../../img/svg/calendar.svg" alt="Date" class="field-icon">
                                    <input type="text" id="date-debut-visible" class="date-input"
                                        placeholder="Sélectionner une date" readonly>
                                    <input type="hidden" name="date_debut" id="date-debut"
                                        min="<?php echo $date_min; ?>" value="<?php echo $date_debut_value; ?>"
                                        required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="date-fin-visible">Date de départ</label>
                                <div class="form-field">
                                    <img src="../../img/svg/calendar.svg" alt="Date" class="field-icon">
                                    <input type="text" id="date-fin-visible" class="date-input"
                                        placeholder="Sélectionner une date" readonly>
                                    <input type="hidden" name="date_fin" id="date-fin" min="<?php echo $date_min; ?>"
                                        value="<?php echo $date_fin_value; ?>" required>
                                </div>
                            </div>

                            <div class="calendar-dropdown" id="calendar-dropdown">
                                <div class="calendar-header">
                                    <div class="calendar-div">
                                        <button type="button" class="prev-month">
                                            <img src="../../img/svg/chevron-left.svg" alt="Mois précédent">
                                        </button>
                                        <div class="calendar-months">
                                            <div class="month-container">
                                                <h3 class="month-name"></h3>
                                                <div class="calendar-grid">
                                                    <div class="calendar-day-header">Lun.</div>
                                                    <div class="calendar-day-header">Mar.</div>
                                                    <div class="calendar-day-header">Mer.</div>
                                                    <div class="calendar-day-header">Jeu.</div>
                                                    <div class="calendar-day-header">Ven.</div>
                                                    <div class="calendar-day-header">Sam.</div>
                                                    <div class="calendar-day-header">Dim.</div>
                                                </div>
                                            </div>
                                            <div class="month-container">
                                                <h3 class="month-name"></h3>
                                                <div class="calendar-grid">
                                                    <div class="calendar-day-header">Lun.</div>
                                                    <div class="calendar-day-header">Mar.</div>
                                                    <div class="calendar-day-header">Mer.</div>
                                                    <div class="calendar-day-header">Jeu.</div>
                                                    <div class="calendar-day-header">Ven.</div>
                                                    <div class="calendar-day-header">Sam.</div>
                                                    <div class="calendar-day-header">Dim.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="next-month">
                                            <img src="../../img/svg/chevron-right.svg" alt="Mois suivant">
                                        </button>
                                    </div>
                                    <div class="calendar-footer">
                                        <button type="button" class="reset-dates" id="reset-dates"
                                            style="<?php echo (empty($date_debut) && empty($date_fin)) ? 'display: none;' : ''; ?>">
                                            Réinitialiser
                                        </button>
                                    </div>
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
                                <a href="../destination.php?id=<?php echo $id; ?>" class="secondary-button">
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

    <script src="../../js/calendar.js"></script>
    <script src="../../js/destination.js"></script>
    <script src="../../js/reservation.js"></script>
</body>

</html>