<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupérer l'ID du voyage en premier
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Vérification de la validité de l'ID
if ($id === null) {
    header('Location: ../destination.php');
    exit;
}

// Récupération des données du voyage
$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID du voyage est valide
if (!isset($voyages[$id])) {
    header('Location: ../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupérer l'email de l'utilisateur connecté
$userEmail = $_SESSION['email'];

// Utiliser un identifiant de secours si l'email n'est pas disponible
if (empty($userEmail)) {
    $userEmail = 'user_' . $_SESSION['user'];
}

// Vérifier si on vient du panier
$from_cart = isset($_GET['from_cart']) && isset($_GET['cart_index']);
$cart_index = $from_cart ? (int) $_GET['cart_index'] : null;

// Charger le panier
$panierJson = file_get_contents('../../json/panier.json');
$panier = json_decode($panierJson, true);

// Si on vient du panier, récupérer les données directement du panier
if ($from_cart && isset($panier[$userEmail]['items'][$cart_index])) {
    $item = $panier[$userEmail]['items'][$cart_index];
    
    // Vérifier que c'est bien le même voyage
    if ($item['voyage_id'] === $id) {
        // Stocker les données de l'étape 1 en session
        store_form_data('etape1', [
            'date_debut' => $item['date_debut'],
            'date_fin' => $item['date_fin'],
            'nb_personne' => $item['nb_personnes']
        ]);
        
        // Stocker les données de l'étape 2 en session si disponibles
        if (isset($item['voyageurs']) && is_array($item['voyageurs'])) {
            store_form_data('etape2', [
                'voyageurs' => $item['voyageurs']
            ]);
        }
        
        // Stocker les données des options si disponibles
        if (isset($item['options']) && is_array($item['options'])) {
            store_form_data('etape3', [
                'options' => $item['options']
            ]);
        }
    }
}

// Récupérer les données des étapes précédentes
$form_data1 = get_form_data('etape1');
$form_data2 = get_form_data('etape2');

// recuperer les options
$optionsData = get_form_data('etape3')['options'] ?? [];

if (!$form_data1 || !$form_data2) {
    header('Location: ../destination.php');
    exit;
}

$date_debut = $form_data1['date_debut'];
$date_fin = $form_data1['date_fin'];
$nb_personne = (int) $form_data1['nb_personne'];
$voyageurs = $form_data2['voyageurs'];


// Calculer les dates en format lisible
$date_debut_obj = new DateTime($date_debut);
$date_fin_obj = new DateTime($date_fin);
$date_debut_format = $date_debut_obj->format('d/m/Y');
$date_fin_format = $date_fin_obj->format('d/m/Y');

// Calculer la durée du séjour
$interval = $date_debut_obj->diff($date_fin_obj);
$duree_sejour = $interval->days;

// Calculer un prix total (base + options)
$prix_base = $voyage['prix'] * $nb_personne;
$prix_options = 0;

// Calculer le prix des options déjà sélectionnées
if (!empty($optionsData)) {
    foreach ($optionsData as $etape_index => $etape_options) {
        foreach ($etape_options as $option_index => $option_data) {
            if (isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])) {
                $nb_participants = count($option_data['voyageurs']);
                if ($nb_participants > 0 && isset($voyage['etapes'][$etape_index]['options'][$option_index]['prix'])) {
                    $option_prix = $voyage['etapes'][$etape_index]['options'][$option_index]['prix'];
                    $prix_options += $option_prix * $nb_participants;
                }
            }
        }
    }
}

$prix_total_avec_options = $prix_base + $prix_options;

// Fonction pour vérifier si une étape a des options disponibles
function hasOptions($etape) {
    return !empty($etape['options']);
}

// Récupérer l'email de l'utilisateur connecté
$userEmail = $_SESSION['email'];

// Mettre à jour l'étape dans le panier
$panierJson = file_get_contents('../../json/panier.json');
if ($panierJson !== false) {
    $panier = json_decode($panierJson, true);
    
    // S'assurer que le panier est un tableau et que l'utilisateur y a une entrée
    if (!is_array($panier)) {
        $panier = [];
    }
    if (!isset($panier[$userEmail])) {
        $panier[$userEmail] = ['items' => []];
    }
    
    // Chercher le voyage correspondant dans le panier
    if (isset($panier[$userEmail]['items'])) {
        foreach ($panier[$userEmail]['items'] as $index => $item) {
            if ($item['voyage_id'] === $id) {
                // Mettre à jour l'étape
                $panier[$userEmail]['items'][$index]['etape_atteinte'] = 3;
                
                // Sauvegarder le panier mis à jour
                file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
                break;
            }
        }
    }
}

// Au début du fichier
$cart_index = isset($_GET['cart_index']) ? $_GET['cart_index'] : null;

// Récupérer les options existantes
$options_selectionnees = [];
if ($cart_index !== null && isset($panier[$userEmail]['items'][$cart_index]['options'])) {
    $options_selectionnees = $panier[$userEmail]['items'][$cart_index]['options'];
}

// Gérer la soumission du formulaire des options
if (isset($_POST['submit_options'])) {
    $options = [];
    
    // Parcourir toutes les options soumises
    if (isset($_POST['options']) && is_array($_POST['options'])) {
        foreach ($_POST['options'] as $etape_index => $etape_options) {
            if (!isset($options[$etape_index])) {
                $options[$etape_index] = [];
            }
            
            foreach ($etape_options as $option_index => $option_data) {
                if (isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])) {
                    $options[$etape_index][$option_index] = [
                        'voyageurs' => array_map('intval', $option_data['voyageurs'])
                    ];
                }
            }
        }
    }
    
    $form_data3 = [
        'options' => $options
    ];
    
    // Stocker les données en session
    store_form_data('etape3', $form_data3);
    
    // Mettre à jour le panier
    $panierJson = file_get_contents('../../json/panier.json');
    $panier = json_decode($panierJson, true);
    
    // S'assurer que le panier est un tableau et que l'utilisateur y a une entrée
    if (!is_array($panier)) {
        $panier = [];
    }
    if (!isset($panier[$userEmail])) {
        $panier[$userEmail] = ['items' => []];
    }
    
    // Déterminer l'index de l'item dans le panier
    $item_index = null;
    if (isset($panier[$userEmail]['items'])) {
        foreach ($panier[$userEmail]['items'] as $index => $item) {
            if ($item['voyage_id'] === $id) {
                $item_index = $index;
                break;
            }
        }
    }

    if ($item_index !== null) {
        // Mettre à jour les options dans le panier
        $panier[$userEmail]['items'][$item_index]['options'] = $options;
        
        // Calculer le prix des options
        $prix_options_total = 0;
        if (!empty($options)) {
            foreach ($options as $etape_index => $etape_options) {
                foreach ($etape_options as $option_index => $option_data) {
                    if (isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])) {
                        $nb_participants = count($option_data['voyageurs']);
                        if ($nb_participants > 0 && isset($voyage['etapes'][$etape_index]['options'][$option_index]['prix'])) {
                            $option_prix = $voyage['etapes'][$etape_index]['options'][$option_index]['prix'];
                            $prix_options_total += $option_prix * $nb_participants;
                        }
                    }
                }
            }
        }
        
        // Mettre à jour le prix des options et l'étape atteinte
        $panier[$userEmail]['items'][$item_index]['prix_options'] = $prix_options_total;
        
        // Sauvegarder le panier mis à jour
        $success = file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
        if ($success === false) {
            error_log("Impossible d'écrire dans le fichier panier.json");
        }
    }
    
    // Rediriger vers l'étape 4
    header("Location: etape4.php?id=" . $id);
    exit;
}

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Options du voyage - <?php echo htmlspecialchars($voyage['titre']); ?> • Marvel Travel</title>

    <script src="../../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../../css/theme.css" id="theme">
    <link rel="stylesheet" href="../../css/reservation.css">
    
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <?php include '../nav.php'; ?>

    <div class="reservation-container">
        <!-- En-tête avec fil d'Ariane et progression -->
        <div class="booking-header">
            <div class="breadcrumb">
                <a href="../destination.php" class="breadcrumb-link">
                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                    <span>Destinations</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="etape1.php?id=<?php echo $id; ?>" class="breadcrumb-link">
                    <span>Étape 1: Dates</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="etape2.php?id=<?php echo $id; ?>" class="breadcrumb-link">
                    <span>Étape 2: Voyageurs</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Étape 3: Options</span>
            </div>

            <div class="booking-progress">
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Completed">
                    </div>
                    <div class="step-label">Dates</div>
                </div>
                <div class="progress-line completed"></div>
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Completed">
                    </div>
                    <div class="step-label">Voyageurs</div>
                </div>
                <div class="progress-line active4"></div>
                <div class="progress-step active4">
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
            <!-- Colonne de gauche : récapitulatif du voyage -->
            <div class="booking-summary">
                <div class="destination-info">
                    <h1 class="destination-title"><?php echo htmlspecialchars($voyage['titre']); ?></h1>

                    <div class="destination-image">
                        <img src="../<?php echo htmlspecialchars($voyage['image']); ?>"
                            alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="destination-photo">
                    </div>

                    <div class="booking-details">
                        <div class="booking-detail">
                            <img src="../../img/svg/calendar.svg" alt="Dates" class="detail-icon">
                            <div>
                                <div class="detail-label">Dates du voyage</div>
                                <div class="detail-value">
                                    <?php echo $date_debut_format . ' - ' . $date_fin_format; ?>
                                    <span class="detail-tag"><?php echo $duree_sejour; ?> jours</span>
                                </div>
                            </div>
                        </div>
                        <div class="booking-detail">
                            <img src="../../img/svg/users.svg" alt="Voyageurs" class="detail-icon">
                            <div>
                                <div class="detail-label">Voyageurs</div>
                                <div class="detail-value">
                                    <?php echo $nb_personne; ?> personne<?php echo $nb_personne > 1 ? 's' : ''; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="travelers-summary">
                        <h2 class="summary-title">Participants</h2>
                        <div class="travelers-list">
                            <?php foreach ($voyageurs as $index => $voyageur): ?>
                                <div class="traveler-item">
                                    <div class="traveler-avatar">
                                        <?= substr($voyageur['prenom'], 0, 1) . substr($voyageur['nom'], 0, 1) ?>
                                    </div>
                                    <div class="traveler-info">
                                        <div class="traveler-name">
                                            <?php echo htmlspecialchars($voyageur['civilite'] . ' ' . $voyageur['prenom'] . ' ' . $voyageur['nom']); ?>
                                            <?php if ($index === 0): ?>
                                                <span class="primary-tag">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="traveler-details">
                                            <span
                                                class="nationality"><?php echo htmlspecialchars($voyageur['nationalite']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="instructions-panel">
                        <h2 class="instructions-title">Options de voyage</h2>
                        <p class="instructions-text">
                            Personnalisez votre aventure en sélectionnant des options exclusives pour chaque étape. Ces
                            expériences spéciales rendront votre voyage dans le multivers Marvel encore plus mémorable.
                        </p>
                        <div class="instructions-note">
                            <img src="../../img/svg/info.svg" alt="Note" class="note-icon">
                            <p>Cliquez sur les participants pour lesquels vous souhaitez réserver chaque option.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite : sélection des options -->
            <div class="options-container">
                <form action="etape3.php?id=<?php echo $id; ?>" method="post" id="optionsForm" class="options-form">
                    <!-- Notez que l'action pointe maintenant vers etape3.php au lieu de etape4.php -->
                    
                    <!-- Données cachées des étapes précédentes -->
                    <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>">
                    <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
                    <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">
                    <input type="hidden" name="submit_options" value="1">

                    <?php foreach ($voyageurs as $index => $voyageur): ?>
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

                    <?php
                    $hasAnyOptions = false;
                    foreach ($voyage['etapes'] as $etape) {
                        if (hasOptions($etape)) {
                            $hasAnyOptions = true;
                            break;
                        }
                    }

                    if (!$hasAnyOptions): ?>
                        <div class="no-options-message card">
                            <div class="card-content">
                                <div class="no-options-icon">
                                    <img src="../../img/svg/alert-circle.svg" alt="Information">
                                </div>
                                <h3>Aucune option disponible</h3>
                                <p>Ce voyage ne propose pas d'options supplémentaires actuellement.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($voyage['etapes'] as $etape_index => $etape): ?>
                            <?php if (hasOptions($etape)): ?>
                                <div class="option-section card" data-animation-delay="<?php echo $etape_index * 0.1; ?>">
                                    <div class="card-header section-header">
                                        <div class="header-content">
                                            <h3 class="section-title"><?php echo htmlspecialchars($etape['lieu']); ?></h3>
                                            <div class="section-duration">
                                                <img src="../../img/svg/clock.svg" alt="Durée" class="duration-icon">
                                                <span><?php echo htmlspecialchars($etape['duree']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-content">
                                        <?php foreach ($etape['options'] as $option_index => $option): ?>
                                            <div class="option-item">
                                                <div class="option-info">
                                                    <div class="option-header">
                                                        <h4 class="option-title"><?php echo htmlspecialchars($option['nom']); ?></h4>
                                                        <div class="option-price">
                                                            <?php echo number_format($option['prix'], 2, ',', ' '); ?> € <span
                                                                class="price-per">par personne</span></div>
                                                    </div>
                                                    <?php if (isset($option['description']) && !empty($option['description'])): ?>
                                                        <div class="option-description">
                                                            <?php echo htmlspecialchars($option['description']); ?></div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="option-participants">
                                                    <div class="participants-label">Sélectionner les participants :</div>
                                                    <div class="participants-list">
                                                        <?php foreach ($voyageurs as $index => $voyageur):
                                                            // Vérifier si cette option était précédemment sélectionnée pour ce voyageur
                                                            $isSelected = false;
                                                            if (
                                                                isset($optionsData[$etape_index][$option_index]['voyageurs']) &&
                                                                in_array($index, $optionsData[$etape_index][$option_index]['voyageurs'])
                                                            ) {
                                                                $isSelected = true;
                                                            }
                                                            ?>
                                                            <div class="participant-toggle <?php echo $isSelected ? 'selected' : ''; ?>">
                                                                <input type="checkbox"
                                                                    name="options[<?php echo $etape_index; ?>][<?php echo $option_index; ?>][voyageurs][]"
                                                                    value="<?php echo $index; ?>"
                                                                    id="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>"
                                                                    <?php if ($isSelected)
                                                                        echo 'checked'; ?> class="participant-checkbox">
                                                                <label
                                                                    for="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>"
                                                                    class="participant-label">
                                                                    <span class="participant-avatar">
                                                                        <img src="../../img/svg/users.svg" alt="Avatar"
                                                                            class="participant-icon">
                                                                        <div class="selected-indicator">
                                                                            <img src="../../img/svg/check.svg" alt="Sélectionné"
                                                                                class="indicator-icon">
                                                                        </div>
                                                                    </span>
                                                                    <span
                                                                        class="participant-name"><?php echo htmlspecialchars($voyageur['prenom']); ?></span>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="order-summary card">
                        <div class="card-header">
                            <img src="../../img/svg/credit-card.svg" alt="Paiement" class="card-icon">
                            <h3 class="card-title">Récapitulatif de votre réservation</h3>
                        </div>
                        
                        <div class="card-content">
                            <div class="price-rows">
                                <div class="price-row">
                                    <span>Prix du voyage (<?php echo $nb_personne; ?> personne<?php echo $nb_personne > 1 ? 's' : ''; ?>)</span>
                                    <span><?php echo number_format($prix_base, 2, ',', ' '); ?> €</span>
                                </div>
                                
                                <div class="price-row">
                                    <span>Durée du séjour</span>
                                    <span><?php echo $duree_sejour; ?> jours</span>
                                </div>
                                
                                <?php if ($prix_options > 0): ?>
                                <div class="price-row options-row">
                                    <span>Options sélectionnées</span>
                                    <span><?php echo number_format($prix_options, 2, ',', ' '); ?> €</span>
                                </div>
                                <?php else: ?>
                                <div class="price-row options-row">
                                    <span>Options sélectionnées</span>
                                    <span>0,00 €</span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="price-total">
                                    <span>Total</span>
                                    <span><?php echo number_format($prix_total_avec_options, 2, ',', ' '); ?> €</span>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="etape2.php?id=<?php echo $id; ?>&back=1" class="secondary-button">
                                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                                    Retour aux voyageurs
                                </a>
                                <button type="submit" name="submit_options" class="primary-button">
                                    Continuer vers le paiement
                                    <img src="../../img/svg/arrow-right.svg" alt="Continuer">
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../js/reservation.js"></script>

</body>
</html>