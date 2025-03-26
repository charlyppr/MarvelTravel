<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupération des données des étapes précédentes
if (!isset($_POST['date_debut']) || !isset($_POST['date_fin']) || !isset($_POST['nb_personne'])) {
    header('Location: ../../destination.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$nb_personne = (int) $_POST['nb_personne'];

// Vérification de la validité de l'ID
if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Récupération des infos voyageurs
$voyageurs = [];
for ($i = 1; $i <= $nb_personne; $i++) {
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

// Si aucun voyageur n'est enregistré, redirection
if (count($voyageurs) == 0) {
    header('Location: ../../destination.php');
    exit;
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

// Récupération des options sélectionnées
$options_selection = isset($_POST['options']) ? $_POST['options'] : [];

// Calcul du prix total
$prix_total = 0;
$prix_base = 0;
$prix_options = 0;
$details_options = [];

// Prix de base pour chaque étape (pour tous les voyageurs)
foreach ($voyage['etapes'] as $etape) {
    $prix_base += $etape['prix'] * $nb_personne;
}

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

$commandes[] = $nouvelle_commande;

file_put_contents($commande_json_file, json_encode($commandes, JSON_PRETTY_PRINT));

// Génération du code de contrôle pour la transaction
require_once('../getapikey.php');
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $prix_total . "#" . $vendeur . "#" . $retour . "#");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif et paiement - <?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/reservation.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <h1 class="titre">Récapitulatif et paiement</h1>

    <div class="recap-container">
        <div class="recap-section">
            <h2>Votre voyage</h2>
            <div class="recap-info">
                <div class="recap-row">
                    <span class="recap-label">Destination:</span>
                    <span class="recap-value"><?php echo htmlspecialchars($voyage['titre']); ?></span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Période:</span>
                    <span class="recap-value">Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au
                        <?php echo date('d/m/Y', strtotime($date_fin)); ?></span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Durée:</span>
                    <span class="recap-value"><?php echo htmlspecialchars($voyage['dates']['duree']); ?></span>
                </div>
            </div>
        </div>

        <div class="recap-section">
            <h2>Voyageurs</h2>
            <div class="recap-info">
                <?php foreach ($voyageurs as $index => $voyageur): ?>
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

        <?php if (!empty($details_options)): ?>
            <div class="recap-section">
                <h2>Options sélectionnées</h2>
                <div class="recap-info">
                    <?php foreach ($details_options as $option): ?>
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
                <div class="recap-row">
                    <span class="recap-label">Prix de base (<?php echo $nb_personne; ?> personne(s)):</span>
                    <span class="recap-value"><?php echo number_format($prix_base, 2, ',', ' '); ?> €</span>
                </div>
                <div class="recap-row">
                    <span class="recap-label">Options:</span>
                    <span class="recap-value"><?php echo number_format($prix_options, 2, ',', ' '); ?> €</span>
                </div>
                <div class="recap-row total-row">
                    <span class="recap-label">Total:</span>
                    <span class="recap-value"><?php echo number_format($prix_total, 2, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>

        <div class="payment-section">
            <h2>Procéder au paiement</h2>
            <p>Vous allez être redirigé vers notre plateforme de paiement sécurisée pour finaliser votre réservation.
            </p>

            <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' class="payment-form">
                <input type='hidden' name='transaction' value='<?php echo $transaction; ?>'>
                <input type='hidden' name='montant' value='<?php echo $prix_total; ?>'>
                <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
                <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
                <input type='hidden' name='control' value='<?php echo $control; ?>'>

                <div class="payment-buttons">
                    <a href="javascript:history.back()" class="back-button">Modifier ma réservation</a>
                    <button type="submit" class="payment-button">
                        <img src="../../img/svg/credit-card.svg" alt="carte bancaire" class="card-icon">
                        Payer <?php echo number_format($prix_total, 2, ',', ' '); ?> €
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>