<?php

require_once('session.php');
$_SESSION['current_url'] = current_url();
$id = (int) $_GET['id'];

if (!isset($_GET['id'])) {
    die("<h1>Erreur </h1><p>ID de voyage manquant ! <a href='destination.php'>Retour</a></p>");
}

$json_file = "../json/voyages.json";

$voyage = json_decode(file_get_contents($json_file), true);
$voyage = $voyage[$id];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../css/voyage.css">
</head>

<body>
<header class="nav">
            <a href="index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="menu">
                <ul>
                    <a href="../index.php" class="menu-li">
                        <li>Accueil</li>
                    </a>
                    <a href="destination.php" class="menu-li">
                        <li>Destinations</li>
                    </a>
                    <a href="contact.php" class="menu-li">
                        <li>Contact</li>
                    </a>
                    <?php 
                        if (isset($_SESSION['user'])) { 
                            echo "<a href='profil.php' class='menu-li'>
                            <li>Profil</li></a>";
                        }else {
                            echo "<a href='connexion.php' class='nav-button'>
                            <li>Se connecter</li></a>";
                        }
                    ?>
                </ul>
            </div>
        </header>

    <h1 class="titre"><?php echo htmlspecialchars($voyage['titre']); ?></h1>
    <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>"
        width="50%">
    <p><strong>Prix :</strong> <?php echo number_format($voyage['prix'], 2, ',', ' ') . "€"; ?></p>
    <p><strong>Résumé :</strong> <?php echo htmlspecialchars($voyage['resume']); ?></p>
    <h2 class="sous-titre-2">📍 Étapes du voyage</h2>
    <ul class="etapes">
        <?php foreach ($voyage['etapes'] as $etape): ?>
            <li>
                <strong><?php echo htmlspecialchars($etape['lieu']); ?></strong> (<?php echo $etape['duree']; ?>)
                <br>
                Options : <?php 
                    for($i = 0; $i < count($etape['options']); $i++){
                        echo ' / '.$etape['options'][$i]['nom'];
                    }?>
                </li>
        <?php endforeach; ?>
    </ul>
    <div class="res">
        <?php
        if(isset($_SESSION['user'])){
            echo "<div class='information'><p>Durée: ".$voyage['dates']['duree']."</p><p>acheteur: ".$_SESSION['first_name'].' '.$_SESSION['last_name']."</p></div>";

            echo "<form action='reservation.php?id=".$id."' method='post' class='commande'>
                    <div class='commande_parametre'>
                        <img src='../img/svg/calendar.svg' alt='calendrier'/>
                            <input type='date' name='date' id='date' required>Date de départ</input>
                        </div>
                        <div class='commande_parametre'>
                            <input type='number' name='nb_personne' id='nb_personne' min='1' max='11'>Nombre de personnes :</input>
                        </div>
                        <div class='commande_parametre'>";
            echo "<br>Lieux :<br>";
            foreach($voyage['etapes'] as $etapes){
                echo "<br><input type='checkbox' name='lieux[]' value='".$etapes['lieu']."' checked>".$etapes['lieu']."</input><br>";            
                echo "Options :";
                    foreach($etapes['options'] as $options){
                        echo "<input type='checkbox' name='options[]' value='".$options['nom']."'>".$options['nom']."</input>";
                    }
            }

            
            echo "</div>
                <div class='reserver_container'>
                    <input type='submit' class='reserver' value='Réserver'/>
                </div>
                </form>";

        }
        else {
            echo "<div class='reserver_container'>
                <a href='connexion.php' class reserver>Réserver</a>
            </div>";
        }
        ?>
    </div>
    <a href="destination.php">⬅ Retour aux voyages</a>
</body>
<footer>
    <?php
    include('footer.php');    
    ?>
</footer>
</html>