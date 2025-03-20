<?php

require_once('session.php');
$_SESSION['current_url'] = current_url();

// Vérifier si un ID est fourni dans l'URL
if (!isset($_GET['id'])) {
    die("<h1>Erreur 🚨</h1><p>ID de voyage manquant ! <a href='destination.php'>Retour</a></p>");
}

$id = (int) $_GET['id'];
$filename = "../json/voyages.json";
$voyages = json_decode(file_get_contents($filename), true);

// Vérifier si le fichier JSON du voyage existe
if (!isset($voyages[$id])) {
    die("<h1>Erreur 404 🚀</h1><p>Ce voyage n'existe pas encore dans notre base.</p><p><a href='destination.php'>Retour aux destinations</a></p>");
}

// Récupérer le voyage
$voyage = $voyages[$id];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../css/base.css">
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
                    <a href="index.php" class="active menu-li">
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
    <h2>📍 Étapes du voyage</h2>
    <ul class="etapes">
        <?php foreach ($voyage['etapes'] as $etape): ?>
            <li>
                <strong><?php echo htmlspecialchars($etape['lieu']); ?></strong> (<?php echo $etape['duree']; ?>)
                <br>
                Options : <?php echo implode(", ", $etape['options']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="reserver_container">
            <a href="paiement.php?id=<?php echo $id; ?>" class="reserver">Réserver</a>
    </div>
    
    <a href="destination.php">⬅ Retour aux voyages</a>
</body>
<footer>
    <?php
    include('footer.php');    
    ?>
</footer>

</html>