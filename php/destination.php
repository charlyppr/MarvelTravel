<?php
require('session.php');
$_SESSION['current_url'] = current_url();

// Effacer les données de réservation précédentes quand on arrive sur cette page
clear_reservation_data();

// Récupérer la liste des fichiers JSON
$json_file = '../json/voyages.json';
$voyages = json_decode(file_get_contents($json_file), true);

// Filtrage des voyages en fonction de la recherche
$search_destination = isset($_GET['destination']) ? strtolower(trim($_GET['destination'])) : '';
$search_date = isset($_GET['date']) ? $_GET['date'] : '';
$search_budget = isset($_GET['budget']) ? floatval($_GET['budget']) : 0;

// Par défaut, on affiche tous les voyages
$filtered_voyages = $voyages;

// Si une recherche est effectuée
if (!empty($search_destination) || !empty($search_date) || $search_budget > 0) {
    $filtered_voyages = array_filter($voyages, function ($voyage) use ($search_destination, $search_date, $search_budget) {
        $match_destination = true;
        $match_date = true;
        $match_budget = true;

        // Vérifier la correspondance du titre
        if (!empty($search_destination)) {
            $match_destination = strpos(strtolower($voyage['titre']), $search_destination) !== false;
        }

        // Vérifier la correspondance de la date
        if (!empty($search_date)) {
            $match_date = $voyage['dates']['debut'] <= $search_date && $voyage['dates']['fin'] >= $search_date;
        }

        // Vérifier la correspondance du budget
        if ($search_budget > 0) {
            $match_budget = $voyage['prix'] <= $search_budget;
        }

        return $match_destination && $match_date && $match_budget;
    });
}

// Mode d'affichage : normal ou recherche
$is_search_mode = !empty($search_destination) || !empty($search_date) || $search_budget > 0;
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
            <form class="search-container" method="get" action="destination.php">
                <div class="filtres-container">
                    <div class="filtre">
                        <img src="../img/svg/globe.svg" alt="globe">
                        <input type="search" name="destination" placeholder="Destination"
                            value="<?php echo htmlspecialchars($search_destination); ?>">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                    <div class="filtre">
                        <img src="../img/svg/calendar.svg" alt="calendrier">
                        <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($search_date); ?>">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                    <div class="filtre">
                        <img src="../img/svg/budget.svg" alt="budget euro">
                        <input type="number" name="budget" id="budget" placeholder="Budget"
                            value="<?php echo $search_budget > 0 ? htmlspecialchars($search_budget) : ''; ?>">
                    </div>
                    <img src="../img/svg/line-haut.svg" alt="separateur">
                </div>
                <button class="search-button" type="submit">Rechercher</button>
            </form>
        </div>
    </section>

    <?php if ($is_search_mode): ?>
        <!-- Mode recherche : afficher tous les résultats filtrés -->
        <section class="all-destination">
            <div class="top-section">
                <p class="sous-titre-2">Résultats de recherche</p>
                <h1 class="titre">Destinations trouvées</h1>
            </div>
            <div class="all-destination-cards">
                <?php if (!empty($filtered_voyages)): ?>
                    <?php foreach ($filtered_voyages as $voyage): ?>
                        <a href='etapes/etape1.php?id=<?php echo htmlspecialchars($voyage['id']) - 1; ?>' class='card-all'>
                            <img src='<?php echo htmlspecialchars($voyage['image']); ?>'
                                alt='<?php echo htmlspecialchars($voyage['titre']); ?>'>
                            <div class='card-text'>
                                <p><?php echo htmlspecialchars($voyage['titre']); ?></p>
                                <p><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">Aucun voyage ne correspond à votre recherche.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Mode normal : afficher best-sellers et toutes les destinations -->
        <section class="best-seller">
            <div class="top-section">
                <p class="sous-titre-2">Nos best-sellers</p>
                <h1 class="titre">Les destinations les plus vendues</h1>
            </div>

            <div class="best-seller-cards">
                <?php for ($i = 0; $i < min(4, count($voyages)); $i++): ?>
                    <?php $voyage = $voyages[$i]; ?>
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
                    <?php for ($i = 4; $i < count($voyages); $i++): ?>
                        <?php $voyage = $voyages[$i]; ?>
                        <a href='etapes/etape1.php?id=<?php echo htmlspecialchars($voyage['id']) - 1; ?>' class='card-all'>
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
    <?php endif; ?>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html>