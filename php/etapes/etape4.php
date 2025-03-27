<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupérer l'ID du voyage en premier
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Vérification de la validité de l'ID
if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Vérifier si l'utilisateur consulte une nouvelle destination
// et effacer les données si c'est le cas
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    // ID différent, on efface les données de réservation
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données du voyage
$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupération des données des étapes précédentes
if (isset($_POST['date_debut']) && isset($_POST['date_fin']) && isset($_POST['nb_personne'])) {
    // Si on reçoit des données POST, on les stocke en session
    $form_data1 = [
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'nb_personne' => $_POST['nb_personne']
    ];
    store_form_data('etape1', $form_data1);

    // Récupération des infos voyageurs depuis l'étape 2
    $voyageurs = [];
    for ($i = 1; $i <= $form_data1['nb_personne']; $i++) {
        if (isset($_POST['nom_' . $i]) && isset($_POST['prenom_' . $i])) {
            $voyageurs[] = [
                'civilite' => $_POST['civilite_' . $i],
                'nom' => $_POST['nom_' . $i],
                'prenom' => $_POST['prenom_' . $i],
                'date_naissance' => $_POST['date_naissance_' . $i],
                'nationalite' => $_POST['nationalite_' . $i],
                'passport' => $_POST['passport_' . $i]
            ];
        }
    }

    $form_data2 = [
        'voyageurs' => $voyageurs
    ];
    store_form_data('etape2', $form_data2);

    // Récupération des options depuis l'étape 3
    $options_selection = isset($_POST['options']) ? $_POST['options'] : [];
    $form_data3 = [
        'options' => $options_selection
    ];
    store_form_data('etape3', $form_data3);

    $date_debut = $form_data1['date_debut'];
    $date_fin = $form_data1['date_fin'];
    $nb_personne = (int) $form_data1['nb_personne'];

} else {
    // Sinon, on tente de récupérer les données de la session
    $form_data1 = get_form_data('etape1');
    $form_data2 = get_form_data('etape2');
    $form_data3 = get_form_data('etape3');

    if (!$form_data1 || !$form_data2) {
        header('Location: ../../destination.php');
        exit;
    }

    $date_debut = $form_data1['date_debut'];
    $date_fin = $form_data1['date_fin'];
    $nb_personne = (int) $form_data1['nb_personne'];
    $voyageurs = $form_data2['voyageurs'];
    $options_selection = $form_data3['options'] ?? [];
}

// Calcul de la durée du séjour en jours
$date_debut_obj = new DateTime($date_debut);
$date_fin_obj = new DateTime($date_fin);
$duree = $date_debut_obj->diff($date_fin_obj)->days;

// Calcul du prix total
$prix_total = 0;
$prix_base = 0;
$prix_options = 0;
$details_options = [];

// Prix de base du voyage pour tous les voyageurs
$prix_base = $voyage['prix'] * $nb_personne;

// Prix des options (uniquement pour les voyageurs sélectionnés)
if (!empty($options_selection)) {
    foreach ($options_selection as $etape_index => $etape_options) {
        foreach ($etape_options as $option_index => $option_data) {
            if (isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])) {
                $nb_participants = count($option_data['voyageurs']);
                if ($nb_participants > 0) {
                    $option_prix = $voyage['etapes'][$etape_index]['options'][$option_index]['prix'];
                    $prix_options += $option_prix * $nb_participants;

                    $details_options[] = [
                        'etape' => $voyage['etapes'][$etape_index]['lieu'],
                        'option' => $voyage['etapes'][$etape_index]['options'][$option_index]['nom'],
                        'prix_unitaire' => $option_prix,
                        'nb_participants' => $nb_participants,
                        'total' => $option_prix * $nb_participants
                    ];
                }
            }
        }
    }
}

$prix_total = $prix_base + $prix_options;

// Génération de l'identifiant unique pour la transaction
$transaction = uniqid();
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

$retour = "{$base_url}/php/retour_reservation.php?transaction={$transaction}";

// Préparation des données pour le fichier commandes.json
$commande_json_file = "../../json/commandes.json";
$commandes = [];
if (file_exists($commande_json_file) && filesize($commande_json_file) > 0) {
    $commandes = json_decode(file_get_contents($commande_json_file), true);
}

$nouvelle_commande = [
    "transaction" => $transaction,
    "montant" => $prix_total,
    "vendeur" => $vendeur,
    "status" => 'pending',
    "control" => 'pending',
    "acheteur" => $_SESSION['email'],
    "voyage" => $voyage['titre'],
    "date_debut" => $date_debut,
    "date_fin" => $date_fin,
    "nb_personne" => $nb_personne,
    "voyageurs" => $voyageurs,
    "options" => $details_options
];

// Stocker la commande en session au lieu de l'écrire dans le fichier
$_SESSION['commande_en_attente'] = $nouvelle_commande;

// Commenter ces lignes qui écrivent directement dans le fichier
// $commandes[] = $nouvelle_commande;
// file_put_contents($commande_json_file, json_encode($commandes, JSON_PRETTY_PRINT));

// Génération du code de contrôle pour la transaction
require_once('../getapikey.php');
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $prix_total . "#" . $vendeur . "#" . $retour . "#");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Récapitulatif du voyage - <?php echo htmlspecialchars($voyage['titre']); ?></title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape4.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <div class="rcapitulatif-du-voyage">
        <div class="header">
            <div class="title">Récapitulatif du voyage</div>
            <div class="subtitle">Vérifiez les détails de votre réservation avant de procéder au paiement</div>
        </div>

        <div class="main-content">
            <div class="info-section">
                <div class="info-container">
                    <div class="section-title">Votre Voyage</div>
                    <div class="details-container">
                        <div class="destination">
                            <span class="priode">Destination : </span>
                            <b><?php echo htmlspecialchars($voyage['titre']); ?></b>
                        </div>
                        <div class="period">
                            <span class="priode">Période : </span>
                            <b>du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au
                                <?php echo date('d/m/Y', strtotime($date_fin)); ?></b>
                        </div>
                        <div class="destination">
                            <span class="priode">Durée : </span>
                            <b><?php echo $duree; ?> jours / <?php echo $duree + 1; ?> nuits</b>
                        </div>
                    </div>
                </div>

                <div class="info-container">
                    <div class="section-title">Détails du prix</div>
                    <div class="details-container">
                        <div class="destination">
                            <span class="priode">Prix de base (<?php echo $nb_personne; ?>
                                personne<?php echo $nb_personne > 1 ? 's' : ''; ?>) : </span>
                            <b><?php echo number_format($prix_base, 2, ',', ' '); ?>€</b>
                        </div>
                        <div class="destination">
                            <span class="priode">Options : </span>
                            <b><?php echo number_format($prix_options, 2, ',', ' '); ?> €</b>
                        </div>
                        <div class="destination">
                            <span class="priode">Total : </span>
                            <b><?php echo number_format($prix_total, 2, ',', ' '); ?>€</b>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-section1">
                <div class="section-title">Voyageur(s)</div>
                <div class="details-container2">
                    <?php foreach ($voyageurs as $index => $voyageur): ?>
                        <div class="info-container2">
                            <div class="section-title">
                                <?php echo htmlspecialchars($voyageur['civilite'] . ' ' . $voyageur['prenom'] . ' ' . $voyageur['nom']); ?>
                            </div>
                            <div class="details-container3">
                                <div class="section-title">
                                    Date de naissance :
                                    <?php echo date('d/m/Y', strtotime($voyageur['date_naissance'])); ?>
                                </div>
                                <div class="section-title">
                                    Nationalité : <?php echo htmlspecialchars($voyageur['nationalite']); ?>
                                </div>
                                <div class="section-title">
                                    N°Passeport : <?php echo htmlspecialchars($voyageur['passport']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($details_options)): ?>
                <div class="info-section1">
                    <div class="section-title">Options sélectionnées</div>
                    <div class="details-container2">
                        <?php foreach ($details_options as $option): ?>
                            <div class="info-container2">
                                <div class="section-title">
                                    <?php echo htmlspecialchars($option['etape']); ?> -
                                    <?php echo htmlspecialchars($option['option']); ?>
                                </div>
                                <div class="details-container3">
                                    <div class="section-title">
                                        Prix unitaire :
                                        <?php echo number_format($option['prix_unitaire'], 2, ',', ' '); ?> €
                                    </div>
                                    <div class="section-title">
                                        Participants : <?php echo $option['nb_participants']; ?> personne(s)
                                    </div>
                                    <div class="section-title">
                                        Sous-total : <?php echo number_format($option['total'], 2, ',', ' '); ?> €
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' class="form-container">
            <input type='hidden' name='transaction' value='<?php echo $transaction; ?>'>
            <input type='hidden' name='montant' value='<?php echo $prix_total; ?>'>
            <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
            <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
            <input type='hidden' name='control' value='<?php echo $control; ?>'>

            <div class="total-price-container">
                <b class="total-price">Total : <?php echo number_format($prix_total, 2, ',', ' '); ?>€</b>
                <div class="promo-code">Ajouter un code promo</div>
                <div class="navigation-buttons">
                    <a href="etape3.php?id=<?php echo $id; ?>" class="back-button">
                        <div class="back-button-text">Retour</div>
                    </a>
                    <button type="submit" class="continue-button">
                        <div class="back-button-text">Payer
                            <?php echo number_format($prix_total, 2, ',', ' '); ?>€
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>