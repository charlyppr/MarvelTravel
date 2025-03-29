<?php
require_once('../session.php');
require_once('../getapikey.php');
check_auth('../connexion.php');

// Récupérer l'ID du voyage
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

// Redirection si l'ID n'est pas valide
if ($id === null || $id === false) {
    header('Location: ../destination.php');
    exit;
}

// Vérification du changement de voyage
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données du voyage
$json_file = "../../json/voyages.json";
if (!file_exists($json_file)) {
    header('Location: ../destination.php');
    exit;
}

$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupération des données des étapes précédentes
$form_data1 = get_form_data('etape1');
$form_data2 = get_form_data('etape2');
$form_data3 = get_form_data('etape3');

// Redirection si les données des étapes précédentes sont manquantes
if (!$form_data1) {
    header('Location: etape1.php?id=' . $id);
    exit;
}

if (!$form_data2) {
    header('Location: etape2.php?id=' . $id);
    exit;
}

// Extraire les données des étapes précédentes
$date_debut = $form_data1['date_debut'];
$date_fin = $form_data1['date_fin'];
$nb_personne = (int) $form_data1['nb_personne'];
$voyageurs = $form_data2['voyageurs'];

// Calculer la durée du séjour
$date_debut_obj = new DateTime($date_debut);
$date_fin_obj = new DateTime($date_fin);
$interval = $date_debut_obj->diff($date_fin_obj);
$duree = $interval->days;

// Calculer les prix
$prix_base = $voyage['prix'] * $nb_personne;
$prix_options = 0;
$details_options = [];

// Récupérer les options de l'étape 3
if ($form_data3 && isset($form_data3['options']) && !empty($form_data3['options'])) {
    // Calculer le prix des options
    foreach ($form_data3['options'] as $etape_index => $etape_options) {
        // Vérifier que l'index etape_index existe dans le tableau etapes
        if (!isset($voyage['etapes'][$etape_index])) {
            continue;
        }

        foreach ($etape_options as $option_index => $participants) {
            // Vérifier que l'index option_index existe dans le tableau options de cette étape
            if (!isset($voyage['etapes'][$etape_index]['options'][$option_index])) {
                continue;
            }

            if (!empty($participants) && isset($participants['voyageurs']) && is_array($participants['voyageurs'])) {
                $nb_participants = count($participants['voyageurs']);
                $option = $voyage['etapes'][$etape_index]['options'][$option_index];
                $prix_unitaire = $option['prix'];
                $option_prix_total = $prix_unitaire * $nb_participants;
                $prix_options += $option_prix_total;

                // Détails pour l'affichage
                $nom_option = isset($option['nom']) ? $option['nom'] : 'Option sans nom';
                $titre_etape = isset($voyage['etapes'][$etape_index]['lieu']) ? $voyage['etapes'][$etape_index]['lieu'] : 'Étape ' . ($etape_index + 1);

                $details_options[] = [
                    'etape' => $titre_etape,
                    'option' => $nom_option,
                    'prix_unitaire' => $prix_unitaire,
                    'nb_participants' => $nb_participants,
                    'total' => $option_prix_total
                ];
            }
        }
    }
}

// Calculer le prix total (base + options)
$prix_total = $prix_base + $prix_options;
$prix_total_base = $prix_total;

$reduction = 0;

// Vérifier si un code promo a été soumis
$promo_code = '';
$promo_message = '';
$promo_status = '';

// Traitement des données d'options ou du code promo si POST est défini
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Si on vient directement de l'étape 3 avec le bouton Continuer
    if (isset($_POST['submit_to_etape4'])) {
        // Traiter immédiatement les options
        if (isset($_POST['options'])) {
            $options_data = $_POST['options'];

            // Stockage des options en session avant tout autre traitement
            $form_data3 = ['options' => $options_data];
            store_form_data('etape3', $form_data3);

            // Rafraîchir les données dans le script
            $form_data3 = get_form_data('etape3');
        }
    }

    // Traitement du code promo
    if (isset($_POST['apply_promo']) && isset($_POST['promo_code'])) {
        $promo_code = htmlspecialchars(trim($_POST['promo_code']));

        // Vérification du code promo
        if ($promo_code === 'MARVEL10') {
            // Code promo valide, appliquer 10% de réduction
            $reduction = $prix_total_base * 0.1;
            $prix_total = $prix_total_base - $reduction;
            $promo_message = "Code promo appliqué ! -10% sur votre commande";
            $promo_status = "success";

            // Stocker le code promo en session pour le conserver
            $_SESSION['promo_code'] = $promo_code;
            $_SESSION['promo_reduction'] = $reduction;
        } else {
            // Code promo invalide
            $promo_message = "Code promo invalide";
            $promo_status = "error";
            $promo_code = '';  // Réinitialiser le code promo invalide
        }
    }

    // Traitement des options si présentes
    if (isset($_POST['options'])) {
        // Vérifier si options est une chaîne JSON et la décoder si nécessaire
        $options_data = $_POST['options'];
        if (is_string($options_data)) {
            $options_data = json_decode($options_data, true);
        }

        // Validation et nettoyage des données d'options
        $cleaned_options = [];

        // Vérifier que options_data est bien un tableau après décodage
        if (is_array($options_data)) {
            foreach ($options_data as $etape_index => $etape_options) {
                $etape_index = (int) $etape_index;
                $cleaned_options[$etape_index] = [];

                foreach ($etape_options as $option_index => $option_data) {
                    $option_index = (int) $option_index;
                    $cleaned_options[$etape_index][$option_index] = [
                        'voyageurs' => isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])
                            ? array_map('intval', $option_data['voyageurs'])
                            : []
                    ];
                }
            }
        }

        // Stockage des options en session
        $options_data = ['options' => $cleaned_options];
        store_form_data('etape3', $options_data);
    }

    // Transmettre les données des voyageurs si présentes
    if (isset($_POST['date_debut']) && isset($_POST['date_fin']) && isset($_POST['nb_personne'])) {
        $form_data1 = [
            'date_debut' => htmlspecialchars(trim($_POST['date_debut'])),
            'date_fin' => htmlspecialchars(trim($_POST['date_fin'])),
            'nb_personne' => max(1, min(10, (int) $_POST['nb_personne']))
        ];
        store_form_data('etape1', $form_data1);

        // Récupération des infos voyageurs
        $voyageurs = [];
        for ($i = 1; $i <= $form_data1['nb_personne']; $i++) {
            if (isset($_POST['nom_' . $i]) && isset($_POST['prenom_' . $i])) {
                $voyageurs[] = [
                    'civilite' => htmlspecialchars(trim($_POST['civilite_' . $i])),
                    'nom' => htmlspecialchars(trim($_POST['nom_' . $i])),
                    'prenom' => htmlspecialchars(trim($_POST['prenom_' . $i])),
                    'date_naissance' => htmlspecialchars(trim($_POST['date_naissance_' . $i])),
                    'nationalite' => htmlspecialchars(trim($_POST['nationalite_' . $i])),
                    'passport' => htmlspecialchars(trim($_POST['passport_' . $i]))
                ];
            }
        }

        $form_data2 = ['voyageurs' => $voyageurs];
        store_form_data('etape2', $form_data2);
    }
}

// Formater le prix total pour l'API de paiement (après application de la réduction)
$prix_total_api = number_format($prix_total, 2, '.', '');

// Génération des données pour la simulation de paiement
$transaction = substr(bin2hex(random_bytes(12)), 0, 24);
$vendeur = 'MEF-2_F';

// Déterminer le chemin de base pour l'URL de retour
$script_name = $_SERVER['SCRIPT_NAME'];
$project_root = str_replace('\\', '/', dirname(dirname(dirname(__FILE__)))); // Chemin absolu du projet
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path;

$retour = "{$base_url}/php/confirmation.php?transaction={$transaction}";

$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $prix_total_api . "#" . $vendeur . "#" . $retour . "#");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Paiement - <?php echo htmlspecialchars($voyage['titre']); ?> • Marvel Travel</title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape4.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

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
                <a href="etape3.php?id=<?php echo $id; ?>" class="breadcrumb-link">
                    <span>Étape 3: Options</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Étape 4: Paiement</span>
            </div>

            <div class="booking-progress">
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Check">
                    </div>
                    <div class="step-label">Dates</div>
                </div>
                <div class="progress-line completed"></div>
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Check">
                    </div>
                    <div class="step-label">Voyageurs</div>
                </div>
                <div class="progress-line completed"></div>
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Check">
                    </div>
                    <div class="step-label">Options</div>
                </div>
                <div class="progress-line active4"></div>
                <div class="progress-step active4">
                    <div class="step-indicator">4</div>
                    <div class="step-label">Paiement</div>
                </div>
            </div>
        </div>

        <div class="booking-content">
            <!-- Colonne de gauche: Résumé du voyage -->
            <div class="booking-summary">
                <div class="destination-info">
                    <h1 class="destination-title">Récapitulatif de votre réservation</h1>

                    <!-- Image de la destination -->
                    <div class="destination-image">
                        <img src="../<?php echo htmlspecialchars($voyage['image']); ?>"
                            alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="destination-photo">
                    </div>

                    <div class="booking-details">
                        <div class="booking-detail">
                            <img src="../../img/svg/map-pin.svg" alt="Destination" class="detail-icon">
                            <div>
                                <div class="detail-label">Destination</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($voyage['titre']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="booking-detail">
                            <img src="../../img/svg/calendar.svg" alt="Dates" class="detail-icon">
                            <div>
                                <div class="detail-label">Dates du voyage</div>
                                <div class="detail-value">
                                    <?php echo date('d/m/Y', strtotime($date_debut)); ?> -
                                    <?php echo date('d/m/Y', strtotime($date_fin)); ?>
                                    <span class="detail-tag"><?php echo $duree; ?> jours</span>
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

                    <div class="important-info">
                        <div class="info-title">
                            <img src="../../img/svg/alert-circle.svg" alt="Information" class="info-icon">
                            <h3>Informations importantes</h3>
                        </div>
                        <ul class="info-list">
                            <li>Vérifiez attentivement tous les détails de votre réservation</li>
                            <li>Le paiement sera traité de manière sécurisée par notre partenaire</li>
                            <li>Vous recevrez une confirmation par email après le paiement</li>
                            <li>Pour toute question, contactez notre équipe d'assistance</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite: Détails de la réservation et paiement -->
            <div class="payment-container">
                <!-- Détails voyageurs -->
                <div class="card travelers-card">
                    <div class="card-header">
                        <img src="../../img/svg/users.svg" alt="Voyageurs" class="card-icon">
                        <h3 class="card-title">Détails des voyageurs</h3>
                    </div>

                    <div class="card-content">
                        <div class="travelers-list">
                            <?php foreach ($voyageurs as $index => $voyageur): ?>
                                <div class="traveler-item">
                                    <div class="traveler-header">
                                        <div class="traveler-avatar">
                                            <?= substr($voyageur['prenom'], 0, 1) . substr($voyageur['nom'], 0, 1) ?>
                                        </div>
                                        <div class="traveler-name">
                                            <?php echo htmlspecialchars($voyageur['civilite'] . ' ' . $voyageur['prenom'] . ' ' . $voyageur['nom']); ?>
                                            <?php if ($index === 0): ?>
                                                <span class="primary-tag">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="traveler-details">
                                        <div class="detail-row">
                                            <div class="detail-item">
                                                <span class="detail-label">Date de naissance</span>
                                                <span
                                                    class="detail-value"><?php echo date('d/m/Y', strtotime($voyageur['date_naissance'])); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Nationalité</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($voyageur['nationalite']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">N° Passeport</span>
                                                <span
                                                    class="detail-value"><?php echo htmlspecialchars($voyageur['passport']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Options sélectionnées -->
                <?php if (!empty($details_options)): ?>
                    <div class="card options-card">
                        <div class="card-header">
                            <img src="../../img/svg/package.svg" alt="Options" class="card-icon">
                            <h3 class="card-title">Options sélectionnées</h3>
                        </div>

                        <div class="card-content">
                            <div class="options-list">
                                <?php foreach ($details_options as $option): ?>
                                    <div class="option-item">
                                        <div class="option-header">
                                            <h4 class="option-title">
                                                <?php echo htmlspecialchars($option['etape']); ?> -
                                                <?php echo htmlspecialchars($option['option']); ?>
                                            </h4>
                                            <div class="option-price">
                                                <?php echo number_format($option['total'], 2, ',', ' '); ?> €
                                            </div>
                                        </div>
                                        <div class="option-details">
                                            <div class="option-detail">
                                                <span class="detail-label">Prix unitaire</span>
                                                <span
                                                    class="detail-value"><?php echo number_format($option['prix_unitaire'], 2, ',', ' '); ?>
                                                    €</span>
                                            </div>
                                            <div class="option-detail">
                                                <span class="detail-label">Participants</span>
                                                <span class="detail-value"><?php echo $option['nb_participants']; ?>
                                                    personne(s)</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Récapitulatif de prix -->
                <div class="card price-card">
                    <div class="card-header">
                        <img src="../../img/svg/credit-card.svg" alt="Paiement" class="card-icon">
                        <h3 class="card-title">Récapitulatif du prix</h3>
                    </div>

                    <div class="card-content">
                        <div class="price-rows">
                            <div class="price-row">
                                <span>Prix du voyage (<?php echo $nb_personne; ?>
                                    personne<?php echo $nb_personne > 1 ? 's' : ''; ?>)</span>
                                <span><?php echo number_format($prix_base, 2, ',', ' '); ?> €</span>
                            </div>
                            <div class="price-row">
                                <span>Options sélectionnées</span>
                                <span><?php echo number_format($prix_options, 2, ',', ' '); ?> €</span>
                            </div>

                            <?php if ($reduction > 0): ?>
                                <div class="price-row discount-row">
                                    <span>Réduction (code: <?php echo htmlspecialchars($promo_code); ?>)</span>
                                    <span>-<?php echo number_format($reduction, 2, ',', ' '); ?> €</span>
                                </div>
                            <?php endif; ?>

                            <!-- Formulaire de code promo -->
                            <form action="etape4.php?id=<?php echo $id; ?>" method="post" class="promo-code-form">
                                <div class="promo-code-container">
                                    <div class="promo-code-input">
                                        <input type="text" name="promo_code" placeholder="Code promo" id="promo-code"
                                            value="<?php echo htmlspecialchars($promo_code); ?>">
                                        <button type="submit" name="apply_promo" class="promo-button">Appliquer</button>
                                    </div>
                                    <?php if (!empty($promo_message)): ?>
                                        <div class="promo-message <?php echo htmlspecialchars($promo_status); ?>"
                                            id="promo-message">
                                            <?php echo htmlspecialchars($promo_message); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Répliquer les données nécessaires pour maintenir les données de session -->
                                <input type="hidden" name="date_debut"
                                    value="<?php echo htmlspecialchars($date_debut); ?>">
                                <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
                                <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">

                                <?php foreach ($voyageurs as $i => $voyageur): ?>
                                    <input type="hidden" name="civilite_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['civilite']); ?>">
                                    <input type="hidden" name="nom_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['nom']); ?>">
                                    <input type="hidden" name="prenom_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['prenom']); ?>">
                                    <input type="hidden" name="date_naissance_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['date_naissance']); ?>">
                                    <input type="hidden" name="nationalite_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['nationalite']); ?>">
                                    <input type="hidden" name="passport_<?php echo $i + 1; ?>"
                                        value="<?php echo htmlspecialchars($voyageur['passport']); ?>">
                                <?php endforeach; ?>

                                <!-- Ajouter aussi les options si nécessaire -->
                                <?php if ($form_data3 && isset($form_data3['options'])): ?>
                                    <input type="hidden" name="options"
                                        value='<?php echo htmlspecialchars(json_encode($form_data3['options'])); ?>'>
                                <?php endif; ?>
                            </form>

                            <div class="price-total">
                                <span>Total</span>
                                <span><?php echo number_format($prix_total, 2, ',', ' '); ?> €</span>
                            </div>
                        </div>

                        <div class="payment-methods">
                            <div class="payment-methods-title">Méthodes de paiement sécurisées</div>
                            <div class="payment-icons">
                                <img src="../../img/cards/visa.svg" alt="Visa" class="payment-icon">
                                <img src="../../img/cards/mastercard.svg" alt="Mastercard" class="payment-icon">
                                <img src="../../img/cards/amex.svg" alt="American Express" class="payment-icon">
                                <img src="../../img/cards/paypal.svg" alt="PayPal" class="payment-icon">
                                <img src="../../img/cards/asgard.svg" alt="Asgard" class="payment-icon">
                                <img src="../../img/cards/wakanda.svg" alt="Wakanda" class="payment-icon">
                            </div>
                            <div class="secure-payment">
                                <img src="../../img/svg/lock.svg" alt="Secure" class="secure-icon">
                                <span>Paiement 100% sécurisé</span>
                            </div>
                        </div>

                        <!-- Formulaire de paiement -->
                        <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' id="payment-form">
                            <input type='hidden' name='transaction' value='<?php echo $transaction; ?>'>
                            <input type='hidden' name='montant' value='<?php echo $prix_total_api; ?>'>
                            <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
                            <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
                            <input type='hidden' name='control' value='<?php echo $control; ?>'>

                            <div class="form-actions">
                                <a href="etape3.php?id=<?php echo $id; ?>" class="secondary-button">
                                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                                    Retour aux options
                                </a>
                                <button type="submit" class="primary-button" form="payment-form">
                                    Procéder au paiement
                                    <img src="../../img/svg/credit-card.svg" alt="Payer">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>