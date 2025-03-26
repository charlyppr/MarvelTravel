<?php
require_once('session.php');
check_auth('connexion.php');

$json_file = "../json/voyages.json";

$id = (int) $_GET['id'];

$voyages = json_decode(file_get_contents($json_file), true);
$voyage = $voyages[$id];

$date = $_POST['date'];
$lieux = isset($_POST['lieux']) ? (array) $_POST['lieux'] : [];
$options = isset($_POST['options']) ? (array) $_POST['options'] : [];
$nb_personne = (int) $_POST['nb_personne'];

$transaction = uniqid();
$montant = 0;

foreach ($lieux as $lieu) {
    foreach ($voyage['etapes'] as $etape) {
        
        if (isset($etape['prix'])) {
            $montant += $etape['prix'];
        }
    
        foreach ($options as $option) {
            if (isset($etape['prix'])) {
                $montant += $etape['prix'];
            }
        }
    }
}

$montant = $montant * $nb_personne;
$vendeur = 'MEF-2_F';

// Déterminer le chemin de base pour l'URL de retour
$script_name = $_SERVER['SCRIPT_NAME'];
$project_root = str_replace('\\', '/', dirname(dirname(__FILE__))); // Chemin absolu du projet
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path;

$retour = "{$base_url}/php/retour_reservation.php?transaction={$transaction}";

$json_file = "../json/commandes.json";

$commandes = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];
if (!is_array($commandes)) {
    $commandes = [];
}


$new_commande[] = [
    "transaction" => $transaction,
    "montant" => $montant,
    "vendeur" => $vendeur,
    "status" => 'pending',
    "control" => 'pending',
    "acheteur" => $_SESSION['email'],
    "voyage" => $voyage['titre'],
    "date" => $date,
    "nb_personne" => $nb_personne,
    "etapes" => $lieux,
    "options" => $options
];

file_put_contents($json_file, json_encode($new_commande, JSON_PRETTY_PRINT));

require('getapikey.php');
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/reservation.css">
    <title>reservation</title>
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <h1 class="titre">Recapitulatif de la commande</h1>
    <div class="information">
        <p>Destination: <?php echo $voyage['titre'] ?></p>
        <p>Description: <?php echo $voyage['resume'] ?></p>
        <p>Durée: <?php echo $voyage['dates']['duree'] ?></p>
        <p>Montant: <?php echo $montant ?></p>
        <p>acheteur: <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></p>
        <p>Date de départ : <?php echo $date ?>
        <p>lieux visité : <?php echo implode(", ", $lieux); ?></p>
        <p>Nombre de personnes : <?php echo $nb_personne ?></p>
    </div>

    <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST' class="carte">
        <input type='hidden' name='transaction' value='<?php echo $transaction; ?>'>
        <input type='hidden' name='montant' value='<?php echo $montant; ?>'>
        <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
        <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
        <input type='hidden' name='control' value='<?php echo $control; ?>'>
        <input type='submit' value="Valider et payer">
    </form>

    <footer>
        <?php
        include("footer.php");
        ?>
    </footer>
</body>

</html>