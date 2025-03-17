<?php
require('session.php');
// Récupérer la liste des fichiers JSON
$jsonDir = __DIR__ . '/../json/voyages';
$voyages = [];

// Vérifier si le dossier JSON existe et contient des fichiers
if (is_dir($jsonDir)) {
    foreach (glob("$jsonDir/*.json") as $filename) {
        $data = json_decode(file_get_contents($filename), true);

        // Vérifier si les données sont bien un tableau et contiennent les clés attendues
        if (is_array($data) && isset($data['id'], $data['titre'], $data['image'], $data['prix'])) {
            $voyages[] = $data;
        } else {
            echo "<p style='color:red;'>Erreur : Fichier JSON corrompu ou format incorrect ($filename)</p>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Destinations</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/destination.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <header class="nav">
        <a href="../index.php" class="logo-container">
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
                <a href="destination.php" class="active menu-li">
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

    <section class="destination-container">
        <div class="destination-landing">
            <div class="destination-titre">
                <h1>Vers quelles destinations vous envolerez-vous ?</h1>
                <p>Asgard, Knowhere, New York, Wakanda... plus d’une vingtaine de destinations...</p>
            </div>
            <form class="search-container">
                <div class="filtres-container">
                    <div class="filtre">
                        <img src="../img/svg/globe.svg" alt="globe">
                        <input type="search" name="destination" placeholder="Destination">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                    <div class="filtre">
                        <img src="../img/svg/calendar.svg" alt="calendrier">
                        <input type="date" name="date" id="date">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                    <div class="filtre">
                        <img src="../img/svg/budget.svg" alt="budget euro">
                        <input type="number" name="budget" id="budget" placeholder="Budget">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                    <div class="filtre">
                        <img src="../img/svg/double-person.svg" alt="deux personne">
                        <input type="number" name="voyageurs" id="voyageurs" placeholder="Voyageurs">
                    </div>
                </div>
                <button class="search-button" type="submit">Rechercher</button>
            </form>
        </div>
    </section>

    <section class="best-seller">
        <div class="top-section">
            <p class="sous-titre-2">Nos best-sellers</p>
            <h1 class="titre">Les destinations les plus vendues</h1>
        </div>

        <div class="best-seller-cards">
            <?php foreach ($voyages as $voyage): ?>
                <a class="card-best" href="voyage.php?id=<?php echo urlencode($voyage['id']); ?>">
                    <img src="<?php echo htmlspecialchars($voyage['image']); ?>"
                        alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                    <div class="card-text">
                        <p><?php echo htmlspecialchars($voyage['titre']); ?></p>
                        <p><?php echo number_format($voyage['prix'], 2, ',', ' ') . "€"; ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="discover-more-container">
            <a href="" class="discover-more">Voir toutes nos destinations</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html>