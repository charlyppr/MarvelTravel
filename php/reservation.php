<?php
require_once('session.php');
check_auth('connexion.php');

$json_file = "../json/voyages.json";

$id = (int) $_GET['id'];
$voyages = json_decode(file_get_contents($json_file), true);
$voyages = $voyages[$id];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reservation</title>
</head>
<body>
    <form action='paiement.php' method='post' class="commande">
        <div class="information">
            <h1 class="titre">Recapitulatif de la commande</h1>
            <p>Destination: <?php echo $voyages['titre']?></p>
            <p>Description: <?php echo $voyages['resume']?></p>
            <p>Durée: <?php echo $voyages['dates']['duree']?></p>
            <p>Montant: <?php echo $voyages['prix']?></p>
            <p>acheteur: <?php echo $_SESSION['first_name'].' '.$_SESSION['last_name']?></p>
        </div>
        <div class="information">
            <img src="../img/svg/calendar.svg" alt="calendrier" />
            <input type="date" name="date" id="date" />
        </div>
        <div class="information">
            <?php
                foreach($voyages['etapes'] as $etapes){
                    echo "<input type='checkbox' name='lieux[]' value='".$etapes['lieu']."'>".$etapes['lieu']."</input>";
                }
            ?>
        </div>
        <input type='submit' value="Valider">
    </form>
</body>
</html>