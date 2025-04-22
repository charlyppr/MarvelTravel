<?php
// Initialiser la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour nettoyer les données de réservation
function clear_reservation_data()
{
    if (isset($_SESSION['reservation'])) {
        unset($_SESSION['reservation']);
    }
    if (isset($_SESSION['current_voyage_id'])) {
        unset($_SESSION['current_voyage_id']);
    }
}

// Charger les données des voyages
$json_file = "../json/voyages.json";
$voyages_array = json_decode(file_get_contents($json_file), true);

// Restructurer le tableau pour qu'il soit indexé correctement par ID
$voyages = [];
foreach ($voyages_array as $voyage) {
    $voyages[$voyage['id']] = $voyage;
}

// Traitement de la recherche
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$budget = isset($_GET['budget']) ? (int) $_GET['budget'] : 0;

// Construction des paramètres de recherche pour les liens de pagination
$search_params = '';
if (!empty($recherche)) {
    $search_params .= '&recherche=' . urlencode($recherche);
}
if (!empty($date_debut)) {
    $search_params .= '&date_debut=' . urlencode($date_debut);
}
if (!empty($date_fin)) {
    $search_params .= '&date_fin=' . urlencode($date_fin);
}
if ($budget > 0) {
    $search_params .= '&budget=' . urlencode($budget);
}

// Filtrer les voyages selon les critères de recherche
$voyages_filtered = $voyages;
if (!empty($recherche) || !empty($date_debut) || !empty($date_fin) || $budget > 0) {
    $voyages_filtered = array_filter($voyages, function ($voyage) use ($recherche, $date_debut, $date_fin, $budget) {
        $titre_match = empty($recherche) || stripos($voyage['titre'], $recherche) !== false;
        $budget_match = $budget <= 0 || $voyage['prix'] <= $budget;

        // Considérer la recherche comme réussie si le texte correspond et le budget est dans la plage
        return $titre_match && $budget_match;
    });
}

// Pagination pour les résultats de recherche ou tous les voyages
$voyages_per_page = 6;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$total_voyages = count($voyages_filtered);
$total_pages = ceil($total_voyages / $voyages_per_page);
$page = min($page, $total_pages);
$offset = ($page - 1) * $voyages_per_page;

// Obtenir les voyages pour la page actuelle
$voyages_page = array_slice($voyages_filtered, $offset, $voyages_per_page);

// Sélectionner les 4 premiers voyages pour la section "Meilleures destinations"
$best_voyages = array_slice($voyages, 0, 4);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos destinations • Marvel Travel</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/destination.css">
    <link rel="stylesheet" href="../css/calendar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    
</head>

<body>
    <?php include 'nav.php'; ?>

    <!-- Section d'en-tête avec bannière et recherche -->
    <section class="hero-section">
        <div class="hero-backdrop"></div>
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Explorez des <span class="highlight">destinations extraordinaires</span></h1>
                <p class="hero-subtitle">Découvrez nos univers Marvel et embarquez pour des aventures inoubliables à
                    travers le monde</p>

                <div class="search-container">
                    <form action="destination.php#toutes-destinations" method="GET" class="search-form">
                        <div class="search-tabs">
                            <button type="button" class="search-tab active">Tous les voyages</button>
                            <button type="button" class="search-tab">Aventures</button>
                            <button type="button" class="search-tab">Expériences</button>
                        </div>

                        <div class="search-fields-container">
                            <div class="search-field">
                                <div class="field-icon">
                                    <img src="../img/svg/map-pin.svg" alt="Destination" class="field-icon-img">
                                </div>
                                <div class="field-content">
                                    <label for="destination-search">Destination</label>
                                    <input type="search" id="destination-search" name="recherche"
                                        placeholder="Où voulez-vous aller?" value="<?= htmlspecialchars($recherche) ?>" autocomplete="off">
                                    
                                    <!-- Ajout du conteneur de suggestions -->
                                    <div class="destination-suggestions" id="destination-suggestions">
                                        <div class="destination-suggestions-div">
                                            <h3>Suggestions de destinations</h3>
                                            
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>New York, États-Unis</h4>
                                                    <p>Célèbre pour des sites comme : Tour Stark, Central Park</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Wakanda, Afrique</h4>
                                                    <p>Pour sa technologie de pointe et culture unique</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Asgard, Royaume des Dieux</h4>
                                                    <p>Destination mythologique prisée</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Xandar, Nova Corps</h4>
                                                    <p>Célèbre pour des sites comme : Bibliothèque Antique</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Hala, Planète Kree</h4>
                                                    <p>Pour ses spectacles d'arène et architecture</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="search-divider"></div>

                            <div class="search-field calendar-field">
                                <div class="field-content date-inputs-container">
                                    <div class="date-inputs">
                                        <div class="field-icon">
                                            <img src="../img/svg/calendar.svg" alt="Dates" class="field-icon-img">
                                        </div>

                                        <div class="field-content"> 
                                            <label for="date-inputs">Arrivée</label>
                                            <input type="text" id="date-debut-visible" readonly placeholder="Arrivée" class="date-input" value="<?php echo !empty($date_debut) ? date('d M', strtotime($date_debut)) : ''; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="search-divider"></div>

                                    <div class="date-inputs">
                                        <div class="field-icon">
                                            <img src="../img/svg/calendar.svg" alt="Dates" class="field-icon-img">
                                        </div>
                                        <div class="field-content">
                                            <label for="date-inputs">Départ</label>
                                            <input type="text" id="date-fin-visible" readonly placeholder="Départ" class="date-input" value="<?php echo !empty($date_fin) ? date('d M', strtotime($date_fin)) : ''; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                    <input type="hidden" id="date-debut" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" autocomplete="off">
                                    <input type="hidden" id="date-fin" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" autocomplete="off">
                                </div>
                                
                                <!-- Calendar Dropdown -->
                                <div class="calendar-dropdown" id="calendar-dropdown">
                                    <div class="calendar-header">
                                    <div class="calendar-div">
                                        <button type="button" class="prev-month">
                                            <img src="../img/svg/chevron-left.svg" alt="Mois précédent">
                                        </button>
                                        <div class="calendar-months">
                                            <div class="month-container">
                                                <h3 class="month-name"></h3>
                                                <div class="calendar-grid">
                                                    <div class="calendar-day-header">Lun.</div>
                                                    <div class="calendar-day-header">Mar.</div>
                                                    <div class="calendar-day-header">Mer.</div>
                                                    <div class="calendar-day-header">Jeu.</div>
                                                    <div class="calendar-day-header">Ven.</div>
                                                    <div class="calendar-day-header">Sam.</div>
                                                    <div class="calendar-day-header">Dim.</div>
                                                </div>
                                            </div>
                                            <div class="month-container">
                                                <h3 class="month-name"></h3>
                                                <div class="calendar-grid">
                                                    <div class="calendar-day-header">Lun.</div>
                                                    <div class="calendar-day-header">Mar.</div>
                                                    <div class="calendar-day-header">Mer.</div>
                                                    <div class="calendar-day-header">Jeu.</div>
                                                    <div class="calendar-day-header">Ven.</div>
                                                    <div class="calendar-day-header">Sam.</div>
                                                    <div class="calendar-day-header">Dim.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="next-month">
                                            <img src="../img/svg/chevron-right.svg" alt="Mois suivant">
                                        </button>
                                    </div>
                                    <div class="calendar-footer">
                                        <button type="button" class="reset-dates" id="reset-dates" style="<?php echo (empty($date_debut) && empty($date_fin)) ? 'display: none;' : ''; ?>">
                                            Réinitialiser
                                        </button>
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <div class="search-divider"></div>

                            <div class="search-field">
                                <div class="field-icon">
                                    <img src="../img/svg/euro.svg" alt="Budget" class="field-icon-img">
                                </div>
                                <div class="field-content">
                                    <label for="budget-search">Budget</label>
                                    <input type="number" id="budget-search" name="budget" placeholder="Max" min="0"
                                        value="<?= $budget > 0 ? htmlspecialchars($budget) : '' ?>" autocomplete="off">
                                </div>
                            </div>

                            <button type="submit" class="search-button">
                                <img src="../img/svg/loupe.svg" alt="Rechercher">
                            </button>
                        </div>
                    </form>
                </div>

                <div class="search-tags">
                    <span class="search-tag-title">Populaires:</span>
                    <a href="?recherche=New+York#toutes-destinations" class="search-tag">New York</a>
                    <a href="?recherche=Wakanda#toutes-destinations" class="search-tag">Wakanda</a>
                    <a href="?recherche=Asgard#toutes-destinations" class="search-tag">Asgard</a>
                    <a href="?recherche=Xandar#toutes-destinations" class="search-tag">Xandar</a>
                </div>
            </div>
        </div>

        <div class="destination-stats">
            <div class="stat-container">
                <div class="stat-card">
                    <h3>+50</h3>
                    <p>Destinations</p>
                </div>
                <div class="stat-card">
                    <h3>98%</h3>
                    <p>Clients satisfaits</p>
                </div>
                <div class="stat-card">
                    <h3>24/7</h3>
                    <p>Support</p>
                </div>
            </div>
        </div>
    </section>

    <?php
    $date_params = '';
    if (isset($_GET['reset']) && $_GET['reset'] == 1) {
        $date_params .= '&reset=1';
    } else {
        if (!empty($date_debut)) {
            $date_params .= '&date_debut=' . urlencode($date_debut);
        }
        if (!empty($date_fin)) {
            $date_params .= '&date_fin=' . urlencode($date_fin);
        }
    }
    ?>
    
    <!-- Section Best-Sellers -->
    <?php if (empty($recherche) && empty($date_debut) && empty($date_fin) && $budget <= 0): ?>
        <section class="best-seller-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-title-group">
                        <span class="section-subtitle">Top destinations</span>
                        <h2 class="section-title">Meilleures destinations</h2>
                    </div>
                    <a href="destination.php#toutes-destinations" class="view-all-link">
                        <span>Voir toutes les destinations</span>
                        <img src="../img/svg/arrow-right.svg" alt="Voir tout">
                    </a>
                </div>

                <div class="best-seller-cards">
                    <?php foreach ($best_voyages as $key => $voyage): ?>
                        <div class="destination-card featured">
                            <a href="voyage-detail.php?id=<?php echo $key; ?>" class="card-link">
                                <div class="card-image-container">
                                    <img src="<?php echo $voyage['image']; ?>"
                                         alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="card-image">
                                    <?php if (isset($voyage['promo']) && $voyage['promo']): ?>
                                        <div class="promo-badge">-<?php echo $voyage['promo']; ?>%</div>
                                    <?php endif; ?>
                                    <div class="card-overlay"></div>
                                    
                                    <!-- Badge pour les "best-sellers" -->
                                    <div class="feature-badge">
                                        <img src="../img/svg/crown.svg" alt="Top destination">
                                        <span>Top destination</span>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                        <div class="card-category">
                                            <?php 
                                            // Afficher les catégories du voyage
                                            $categories = isset($voyage['categories']) ? $voyage['categories'] : ['Aventure'];
                                            foreach(array_slice($categories, 0, 2) as $cat): ?>
                                                <span class="category-tag"><?php echo htmlspecialchars($cat); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Ajout d'une courte description -->
                                    <p class="card-description">
                                        <?php 
                                        $description = isset($voyage['description']) ? $voyage['description'] : 'Une expérience unique dans l\'univers Marvel';
                                        echo htmlspecialchars(substr($description, 0, 120)) . '...'; 
                                        ?>
                                    </p>
                                    
                                    <!-- Évaluation détaillée -->
                                    <div class="card-ratings-detailed">
                                        <?php if (isset($voyage['rating'])): ?>
                                            <div class="rating-stars">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <?php if($i <= floor($voyage['rating'])): ?>
                                                        <img src="../img/svg/star.svg" alt="★" class="star">
                                                    <?php elseif($i - 0.5 <= $voyage['rating']): ?>
                                                        <img src="../img/svg/star-half.svg" alt="½" class="star">
                                                    <?php else: ?>
                                                        <img src="../img/svg/star-empty.svg" alt="☆" class="star">
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="rating-count">
                                                <span><?php echo $voyage['rating']; ?></span>
                                                <span class="review-count">(<?php echo isset($voyage['reviews']) ? $voyage['reviews'] : rand(10, 50); ?> avis)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Caractéristiques principales -->
                                    <div class="card-features">
                                        <div class="feature-item">
                                            <img src="../img/svg/clock.svg" alt="Durée">
                                            <span><?php echo isset($voyage['duree']) ? $voyage['duree'] : '7 jours'; ?></span>
                                        </div>
                                        <div class="feature-item">
                                            <img src="../img/svg/map-pin.svg" alt="Étapes">
                                            <span><?php echo isset($voyage['etapes']) ? count($voyage['etapes']) : 0; ?> étapes</span>
                                        </div>
                                        <?php if (isset($voyage['famille']) && $voyage['famille']): ?>
                                        <div class="feature-item">
                                            <img src="../img/svg/users.svg" alt="Famille">
                                            <span>Adapté aux familles</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Points forts -->
                                    <div class="highlights">
                                        <h4>Points forts</h4>
                                        <ul class="highlights-list">
                                            <?php 
                                            $highlights = isset($voyage['highlights']) ? $voyage['highlights'] : ['Expérience immersive', 'Guide expert'];
                                            foreach(array_slice($highlights, 0, 2) as $highlight): ?>
                                                <li><img src="../img/svg/check.svg" alt="✓"> <?php echo htmlspecialchars($highlight); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="card-meta">
                                        <div class="meta-item price-meta">
                                            <img src="../img/svg/tag.svg" alt="Prix">
                                            <span class="price-value"><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></span>
                                            <span class="price-note">par personne</span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <span class="btn-details">Voir les détails</span>
                                        <a href="etapes/etape1.php?id=<?php echo $key . $date_params; ?>" class="btn-book">
                                            <span>Réserver</span>
                                            <img src="../img/svg/ticket.svg" alt="Réserver">
                                        </a>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Toutes les destinations / Résultats de recherche -->
    <section id="toutes-destinations" class="all-destination-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title-group">
                    <?php if (!empty($recherche) || !empty($date_debut) || !empty($date_fin) || $budget > 0): ?>
                        <span class="section-subtitle">Résultats de recherche</span>
                        <h2 class="section-title">
                            <?php echo count($voyages_filtered); ?>
                            destination<?php echo count($voyages_filtered) > 1 ? 's' : ''; ?>
                            trouvée<?php echo count($voyages_filtered) > 1 ? 's' : ''; ?>
                        </h2>
                        <a href="destination.php?date_debut=&date_fin=" class="reset-search">Réinitialiser la recherche</a>
                    <?php else: ?>
                        <span class="section-subtitle">Explorez</span>
                        <h2 class="section-title">Toutes nos destinations</h2>
                    <?php endif; ?>
                </div>

                <div class="sort-filter">
                    <label for="sort">Trier par:</label>
                    <select id="sort" class="sort-select">
                        <option value="popular">Popularité</option>
                        <option value="price-asc">Prix: croissant</option>
                        <option value="price-desc">Prix: décroissant</option>
                        <option value="name-asc">Nom: A-Z</option>
                    </select>
                </div>
            </div>

            <?php if (empty($voyages_page)): ?>
                <div class="no-results">
                    <img src="../img/svg/search-not-found.svg" alt="Aucun résultat" class="no-results-icon">
                    <h3>Aucune destination trouvée</h3>
                    <p>Essayez de modifier vos critères de recherche</p>
                    <a href="destination.php" class="btn-reset">Voir toutes les destinations</a>
                </div>
            <?php else: ?>
                <div class="all-destination-cards">
                    <?php foreach ($voyages_page as $real_index => $voyage):
                        // Récupérer l'index réel dans le tableau d'origine
                        $indexes = array_keys($voyages_filtered);
                        $real_index = $indexes[$offset + $real_index];
                        ?>
                        <div class="destination-card" style="--card-index: <?php echo $real_index; ?>">
                            <a href="voyage-detail.php?id=<?php echo $real_index; ?>" class="card-link">
                                <div class="card-image-container">
                                    <img src="<?php echo $voyage['image']; ?>"
                                        alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="card-image">
                                    <?php if (isset($voyage['promo']) && $voyage['promo']): ?>
                                        <div class="promo-badge">-<?php echo $voyage['promo']; ?>%</div>
                                    <?php endif; ?>
                                    <div class="card-overlay"></div>
                                </div>
                                <div class="card-content">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                        <?php if (isset($voyage['rating'])): ?>
                                            <div class="card-rating">
                                                <img src="../img/svg/star.svg" alt="Rating">
                                                <span><?php echo $voyage['rating']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-meta">
                                        <div class="meta-item">
                                            <img src="../img/svg/tag.svg" alt="Prix">
                                            <span
                                                class="price-value"><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <img src="../img/svg/map-pin.svg" alt="Étapes">
                                            <span><?php echo isset($voyage['etapes']) ? count($voyage['etapes']) : 0; ?>
                                                étapes</span>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        <span class="btn-details">Voir les détails</span>
                                        <a href="etapes/etape1.php?id=<?php echo $real_index . $date_params; ?>" class="btn-book">
                                            <span>Réserver</span>
                                            <img src="../img/svg/ticket.svg" alt="Réserver">
                                        </a>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1) . $search_params; ?>#toutes-destinations" class="page-arrow">
                                <img src="../img/svg/chevron-left.svg" alt="Page précédente">
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i . $search_params; ?>#toutes-destinations" 
                               class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1) . $search_params; ?>#toutes-destinations" class="page-arrow">
                                <img src="../img/svg/chevron-right.svg" alt="Page suivante">
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>


    <!-- Call to action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-container">
                <div class="cta-content">
                    <h2>Prêt à vivre votre prochaine aventure ?</h2>
                    <p>Rejoignez les milliers de voyageurs qui ont déjà vécu l'expérience Marvel Travel</p>
                    <a href="destination.php#toutes-destinations" class="cta-button">
                        <span>Explorer les destinations</span>
                        <img src="../img/svg/arrow-right.svg" alt="Explorer">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/destination.js"></script>

</body>

</html>