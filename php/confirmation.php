<?php
require_once('session.php');
require_once('getapikey.php');

// Initialisation des variables par défaut
$payment_validated = false;
$transaction_id = '';
$result = '';
$montant = '';
$session = '';
$vendeur = '';
$control = '';
$voyage = null;
$voyage_id = null;
$reservation_id = '';
$date_debut = '';
$date_fin = '';
$nb_personne = 0;
$voyageurs = [];
$duree = 0;
$promo_code = '';
$reduction = 0;
$form_data1 = [];
$form_data2 = [];
$form_data3 = [];

// Fonction de sécurité pour rediriger en cas d'erreur
function redirect_error($error_code)
{
    header("Location: error.php?error={$error_code}");
    exit;
}

// 1. Vérifier s'il s'agit d'une requête légitime de retour de paiement
$is_legitimate_request = false;

// Vérifier la présence des paramètres de paiement nécessaires
if (isset($_GET['transaction']) && isset($_GET['status'])) {
    $transaction_id = htmlspecialchars($_GET['transaction']);

    // Vérifier si cette transaction a déjà été traitée
    if (!isset($_SESSION['processed_transactions']) || !in_array($transaction_id, $_SESSION['processed_transactions'])) {
        $is_legitimate_request = true;

        // Enregistrer cette transaction comme traitée
        if (!isset($_SESSION['processed_transactions'])) {
            $_SESSION['processed_transactions'] = [];
        }
        $_SESSION['processed_transactions'][] = $transaction_id;
    } else {
        // Transaction déjà traitée - vérifier si on a une confirmation à afficher
        if (isset($_SESSION['confirmation_viewed']) && $_SESSION['confirmation_viewed'] === true) {
            redirect_error('already_processed');
        }
    }
} else {
    // Pas de paramètres de transaction - accès direct interdit
    redirect_error('invalid_transaction');
}

// Si ce n'est pas une requête légitime, rediriger
if (!$is_legitimate_request) {
    redirect_error('invalid_transaction');
}

// 2. Récupérer tous les paramètres du retour de paiement
$result = htmlspecialchars($_GET['status']);
$montant = isset($_GET['montant']) ? htmlspecialchars($_GET['montant']) : '';
$session = isset($_GET['session']) ? htmlspecialchars($_GET['session']) : '';
$vendeur = isset($_GET['vendeur']) ? htmlspecialchars($_GET['vendeur']) : '';
$control = isset($_GET['control']) ? $_GET['control'] : '';

// 3. Récupérer les données de réservation depuis la session
if (!isset($_SESSION['reservation_pending']) || empty($_SESSION['reservation_pending'])) {
    // Si aucune donnée de réservation en attente n'est trouvée, essayer de récupérer l'ID du voyage
    if (isset($_GET['voyage_id'])) {
        $voyage_id = (int) $_GET['voyage_id'];
    } else {
        $voyage_id = $_SESSION['current_voyage_id'] ?? null;
    }

    if (!$voyage_id) {
        redirect_error('manque_voyage_id');
    }
} else {
    // Utiliser les données de réservation pré-stockées
    $pending = $_SESSION['reservation_pending'];
    $voyage_id = $pending['voyage_id'];

    // Vérifier la cohérence de la transaction
    if ($pending['transaction_id'] !== $transaction_id) {
        redirect_error('transaction_mismatch');
    }
}

// 4. Déterminer si le paiement est validé
$payment_validated = ($result === 'accepted');

// 5. Charger les données du voyage
$json_file = "../json/voyages.json";
if (!file_exists($json_file)) {
    redirect_error('manque_voyage');
}

$voyages = json_decode(file_get_contents($json_file), true);
if (!isset($voyages[$voyage_id])) {
    redirect_error('manque_voyage_id');
}

$voyage = $voyages[$voyage_id];

// 6. Récupérer toutes les données du formulaire
$form_data1 = get_form_data('etape1') ?: [];
$form_data2 = get_form_data('etape2') ?: [];
$form_data3 = get_form_data('etape3') ?: [];

// Si ces données ne sont pas disponibles, essayer de les récupérer de la réservation en attente
if (empty($form_data1) || empty($form_data2)) {
    if (isset($_SESSION['reservation_pending'])) {
        $pending = $_SESSION['reservation_pending'];

        // Reconstituer les données d'étape 1
        if (empty($form_data1) && isset($pending['date_debut']) && isset($pending['date_fin']) && isset($pending['nb_personne'])) {
            $form_data1 = [
                'date_debut' => $pending['date_debut'],
                'date_fin' => $pending['date_fin'],
                'nb_personne' => $pending['nb_personne']
            ];
        }

        // Reconstituer les données d'étape 2
        if (empty($form_data2) && isset($pending['voyageurs'])) {
            $form_data2 = ['voyageurs' => $pending['voyageurs']];
        }
    }
}

// Vérifier si les données nécessaires sont présentes
if (empty($form_data1) || empty($form_data2)) {
    redirect_error('manque_etape');
}

// 7. Extraire les données nécessaires
$date_debut = $form_data1['date_debut'] ?? '';
$date_fin = $form_data1['date_fin'] ?? '';
$nb_personne = isset($form_data1['nb_personne']) ? (int) $form_data1['nb_personne'] : 0;
$voyageurs = $form_data2['voyageurs'] ?? [];

// 8. Calculer la durée du séjour
if (!empty($date_debut) && !empty($date_fin)) {
    try {
        $date_debut_obj = new DateTime($date_debut);
        $date_fin_obj = new DateTime($date_fin);
        $interval = $date_debut_obj->diff($date_fin_obj);
        $duree = $interval->days;
    } catch (Exception $e) {
        $duree = 0;
    }
}

// 9. Récupérer les informations de réduction
$promo_code = $_SESSION['promo_code'] ?? '';
$reduction = $_SESSION['promo_reduction'] ?? 0;

// 10. Si le paiement est validé, enregistrer la réservation
if ($payment_validated) {
    // Générer un numéro de réservation unique
    $reservation_id = 'MT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Préparer les données de la réservation
    $reservation_data = [
        'id' => $reservation_id,
        'voyage_id' => $voyage_id,
        'transaction_id' => $transaction_id,
        'date_reservation' => date('Y-m-d H:i:s'),
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'voyageurs' => $voyageurs,
        'options' => $form_data3['options'] ?? [],
        'promo_code' => $promo_code,
        'reduction' => $reduction
    ];

    // Enregistrer la réservation dans la session
    $_SESSION['last_reservation'] = $reservation_data;

    // Ajouter la commande au fichier JSON
    $commandes_file = "../json/commandes.json";

    // Préparer les données de la commande pour le fichier JSON
    $commande = [
        'transaction' => $transaction_id,
        'reservation_id' => $reservation_id,
        'montant' => (float) $montant,
        'vendeur' => $vendeur,
        'status' => $result,
        'control' => $control,
        'acheteur' => $_SESSION['email'] ?? 'utilisateur_inconnu',
        'voyage' => $voyage['titre'],
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'nb_personne' => $nb_personne,
        'voyageurs' => $voyageurs,
        'date_achat' => date('Y-m-d H:i:s')
    ];

    // Ajouter les options formatées
    $options_formatted = [];
    if (isset($form_data3['options']) && is_array($form_data3['options'])) {
        foreach ($form_data3['options'] as $etape_index => $etape_options) {
            foreach ($etape_options as $option_index => $option_data) {
                if (isset($option_data['voyageurs']) && !empty($option_data['voyageurs'])) {
                    // Récupérer les informations de l'étape et de l'option
                    $etape_nom = $voyage['etapes'][$etape_index]['lieu'] ?? "Étape $etape_index";
                    $option_nom = $voyage['etapes'][$etape_index]['options'][$option_index]['nom'] ?? "Option $option_index";
                    $prix_unitaire = $voyage['etapes'][$etape_index]['options'][$option_index]['prix'] ?? 0;

                    $nb_participants = count($option_data['voyageurs']);
                    $total = $prix_unitaire * $nb_participants;

                    $options_formatted[] = [
                        'etape' => $etape_nom,
                        'option' => $option_nom,
                        'prix_unitaire' => $prix_unitaire,
                        'nb_participants' => $nb_participants,
                        'total' => $total
                    ];
                }
            }
        }
    }
    $commande['options'] = $options_formatted;

    // Lire le fichier existant ou créer un tableau vide
    $commandes = [];
    if (file_exists($commandes_file)) {
        $json_content = file_get_contents($commandes_file);
        if (!empty($json_content)) {
            $commandes = json_decode($json_content, true) ?: [];
        }
    }

    // Ajouter la nouvelle commande
    $commandes[] = $commande;

    // Écrire dans le fichier
    file_put_contents($commandes_file, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Supprimer le voyage du panier
    $panierJson = file_get_contents('../json/panier.json');
    if ($panierJson !== false) {
        $panier = json_decode($panierJson, true) ?: [];
        $userEmail = $_SESSION['email'] ?? '';

        // Rechercher et supprimer le voyage correspondant
        if (!empty($userEmail) && isset($panier[$userEmail]['items'])) {
            foreach ($panier[$userEmail]['items'] as $index => $item) {
                if ($item['voyage_id'] == $voyage_id) {
                    array_splice($panier[$userEmail]['items'], $index, 1);
                    break;
                }
            }
            // Sauvegarder le panier mis à jour
            file_put_contents('../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
        }
    }

    // Nettoyer les données de réservation temporaires
    unset($_SESSION['current_voyage_id']);
    unset($_SESSION['promo_code']);
    unset($_SESSION['promo_reduction']);
    unset($_SESSION['reservation_pending']);
    unset($_SESSION['payment_started']);
    clear_reservation_data();
}

// 11. Marquer cette page comme vue pour éviter les rechargements
$_SESSION['confirmation_viewed'] = true;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title><?php echo $payment_validated ? 'Confirmation de réservation' : 'Échec du paiement'; ?> • Marvel Travel
    </title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/confirmation.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <div class="confirmation-container">
        <?php if ($payment_validated): ?>
            <!-- AFFICHAGE EN CAS DE PAIEMENT RÉUSSI -->
            <div class="confirmation-header">
                <div class="success-icon">
                    <img src="../img/svg/check-circle.svg" alt="Succès">
                </div>
                <h1>Réservation confirmée !</h1>
                <p class="confirmation-subtitle">Merci pour votre confiance. Votre aventure Marvel est validée.</p>
                <div class="reservation-number">
                    <span>N° de réservation :</span>
                    <strong><?php echo $reservation_id; ?></strong>
                </div>
            </div>

            <div class="confirmation-content">
                <div class="confirmation-card voyage-summary">
                    <div class="card-header">
                        <img src="../img/svg/map-pin.svg" alt="Voyage" class="card-icon">
                        <h2>Votre voyage</h2>
                    </div>
                    <div class="card-content">
                        <div class="destination-image">
                            <img src="<?php echo htmlspecialchars($voyage['image']); ?>"
                                alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                        </div>
                        <div class="voyage-details">
                            <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <span class="label">Dates</span>
                                    <span class="value"><?php echo date('d/m/Y', strtotime($date_debut)); ?> -
                                        <?php echo date('d/m/Y', strtotime($date_fin)); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Durée</span>
                                    <span class="value"><?php echo $duree; ?> jours</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Voyageurs</span>
                                    <span class="value"><?php echo $nb_personne; ?> personne(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="confirmation-card payment-summary">
                    <div class="card-header">
                        <img src="../img/svg/credit-card.svg" alt="Paiement" class="card-icon">
                        <h2>Détails du paiement</h2>
                    </div>
                    <div class="card-content">
                        <div class="payment-details">
                            <div class="payment-row">
                                <span>Statut</span>
                                <span class="payment-status status-confirmed">
                                    <span class="status-indicator"></span>
                                    Paiement confirmé
                                </span>
                            </div>
                            <div class="payment-row">
                                <span>Montant</span>
                                <span><?php echo $montant; ?> €</span>
                            </div>
                            <div class="payment-row">
                                <span>Date</span>
                                <span><?php echo date('d/m/Y H:i'); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Méthode</span>
                                <span>Carte bancaire</span>
                            </div>
                            <?php if (!empty($promo_code)): ?>
                                <div class="payment-row discount">
                                    <span>Code promo appliqué</span>
                                    <span><?php echo htmlspecialchars($promo_code); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="confirmation-card next-steps">
                    <div class="card-header">
                        <img src="../img/svg/info.svg" alt="Prochaines étapes" class="card-icon">
                        <h2>Prochaines étapes</h2>
                    </div>
                    <div class="card-content">
                        <ul class="steps-list">
                            <li>
                                <div class="step-icon">1</div>
                                <div class="step-content">
                                    <h4>Email de confirmation</h4>
                                    <p>Un email contenant tous les détails de votre réservation a été envoyé à votre
                                        adresse.</p>
                                </div>
                            </li>
                            <li>
                                <div class="step-icon">2</div>
                                <div class="step-content">
                                    <h4>Préparation du voyage</h4>
                                    <p>Vérifiez la validité de vos documents de voyage et préparez vos bagages.</p>
                                </div>
                            </li>
                            <li>
                                <div class="step-icon">3</div>
                                <div class="step-content">
                                    <h4>Contact pré-départ</h4>
                                    <p>Notre équipe vous contactera quelques jours avant le départ pour finaliser les
                                        détails.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <a href="#" class="action-button download-button">
                        <img src="../img/svg/download.svg" alt="Télécharger">
                        Télécharger le récapitulatif
                    </a>
                    <a href="destination.php" class="action-button home-button">
                        <img src="../img/svg/home.svg" alt="Accueil">
                        Retour à l'accueil
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- AFFICHAGE EN CAS D'ÉCHEC DU PAIEMENT -->
            <div class="confirmation-header error-header">
                <div class="error-icon">
                    <img src="../img/svg/alert-circle.svg" alt="Erreur">
                </div>
                <h1>Paiement non complété</h1>
                <p class="confirmation-subtitle">Votre paiement n'a pas pu être traité. Veuillez réessayer.</p>
            </div>

            <div class="confirmation-content">
                <div class="confirmation-card error-card">
                    <div class="card-header">
                        <img src="../img/svg/info.svg" alt="Information" class="card-icon">
                        <h2>Que s'est-il passé ?</h2>
                    </div>
                    <div class="card-content">
                        <p class="error-message">
                            Votre transaction a été refusée par le système de paiement. Cela peut être dû à plusieurs
                            raisons :
                        </p>
                        <ul class="error-reasons">
                            <li>Fonds insuffisants sur votre compte</li>
                            <li>Informations de carte incorrectes</li>
                            <li>Problème temporaire avec votre banque</li>
                            <li>Transaction considérée comme suspecte par votre banque</li>
                        </ul>
                        <p>Aucun montant n'a été débité de votre compte. Vous pouvez réessayer le paiement ou utiliser une
                            autre méthode de paiement.</p>
                    </div>
                </div>

                <div class="confirmation-card voyage-summary">
                    <div class="card-header">
                        <img src="../img/svg/map-pin.svg" alt="Voyage" class="card-icon">
                        <h2>Rappel de votre voyage</h2>
                    </div>
                    <div class="card-content">
                        <div class="destination-image">
                            <img src="<?php echo htmlspecialchars($voyage['image']); ?>"
                                alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                        </div>
                        <div class="voyage-details">
                            <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                            <div class="detail-row">
                                <div class="detail-item">
                                    <span class="label">Dates</span>
                                    <span class="value"><?php echo date('d/m/Y', strtotime($date_debut)); ?> -
                                        <?php echo date('d/m/Y', strtotime($date_fin)); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Durée</span>
                                    <span class="value"><?php echo $duree; ?> jours</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Voyageurs</span>
                                    <span class="value"><?php echo $nb_personne; ?> personne(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nouvelle carte avec des conseils pour réussir le paiement -->
                <div class="confirmation-card payment-tips">
                    <div class="card-header">
                        <img src="../img/svg/help-circle.svg" alt="Conseils" class="card-icon">
                        <h2>Conseils pour réussir votre paiement</h2>
                    </div>
                    <div class="card-content">
                        <ul class="tips-list">
                            <li>
                                <div class="tip-icon"><img src="../img/svg/credit-card.svg" alt="Carte"></div>
                                <div class="tip-content">
                                    <h4>Vérifiez vos informations de carte</h4>
                                    <p>Assurez-vous que le numéro de carte, la date d'expiration et le code de sécurité sont
                                        correctement saisis.</p>
                                </div>
                            </li>
                            <li>
                                <div class="tip-icon"><img src="../img/svg/shield.svg" alt="Sécurité"></div>
                                <div class="tip-content">
                                    <h4>Utilisez une connexion sécurisée</h4>
                                    <p>Effectuez votre paiement sur un réseau sécurisé et évitez les réseaux Wi-Fi publics.
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="tip-icon"><img src="../img/svg/phone.svg" alt="Contact"></div>
                                <div class="tip-content">
                                    <h4>Contactez votre banque si nécessaire</h4>
                                    <p>Si le problème persiste, votre banque pourrait avoir bloqué la transaction par mesure
                                        de sécurité.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <a href="etapes/etape4.php?id=<?php echo $voyage_id; ?><?php echo !empty($promo_code) ? '&promo_code=' . urlencode($promo_code) : ''; ?>"
                        class="action-button retry-button">
                        <img src="../img/svg/refresh.svg" alt="Réessayer">
                        Réessayer le paiement
                    </a>
                    <a href="destination.php" class="action-button home-button">
                        <img src="../img/svg/home.svg" alt="Accueil">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../js/nav.js"></script>
</body>

</html>