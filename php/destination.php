<?php
require('session.php');
$_SESSION['current_url'] = current_url();
// Récupérer la liste des fichiers JSON
$json_file = '../json/voyages.json';
$voyages = json_decode(file_get_contents($json_file), true);
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

    <?php include 'nav.php'; ?>

    <section class="destination-container">
        <div class="destination-landing">
            <div class="destination-titre">
                <h1>Vers quelles destinations vous envolerez-vous ?</h1>
                <p>Asgard, Knowhere, New York, Wakanda... plus d'une vingtaine de destinations...</p>
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
            <?php for ($i = 0; $i < 4; $i++): ?>
                <?php $voyage = $voyages[$i]; ?>
                <!-- Modification ici pour pointer vers etape1.php -->
                <a href='etapes/etape1.php?id=<?php echo htmlspecialchars($voyage['id']) - 1; ?>' class='card-best'>
                    <img src='<?php echo htmlspecialchars($voyage['image']); ?>'
                        alt='<?php echo htmlspecialchars($voyage['titre']); ?>'>
                    <div class='card-text'>
                        <p><?php echo htmlspecialchars($voyage['titre']); ?></p>
                        <p><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></p>
                    </div>
                </a>
            <?php endfor; ?>
        </div>


        <div class="discover-more-container">
            <a href="" class="discover-more">Voir toutes nos destinations</a>
        </div>
        <div class="all-destination">
            <div class="top-section">
                <p class="sous-titre-2">Toutes nos destinations</p>
                <h1 class="titre">Le choix est vôtre</h1>
            </div>
            <div class="all-destination-cards">
                <?php for ($i = 5; $i < count($voyages); $i++): ?>
                    <?php $voyage = $voyages[$i]; ?>
                    <a href='voyage.php?id=<?php echo htmlspecialchars($voyage['id']) - 1; ?>' class='card-all'>
                        <img src='<?php echo htmlspecialchars($voyage['image']); ?>'
                            alt='<?php echo htmlspecialchars($voyage['titre']); ?>'>
                        <div class='card-text'>
                            <p><?php echo htmlspecialchars($voyage['titre']); ?></p>
                            <p><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></p>
                        </div>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html>