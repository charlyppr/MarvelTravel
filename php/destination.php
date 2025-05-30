<?php
// Inclure le fichier session.php pour accéder à la fonction load_user_theme
require_once 'session.php';

// Initialiser la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Charger les données des voyages
$json_file = "../json/voyages.json";
$voyages_array = json_decode(file_get_contents($json_file), true);

// Restructurer le tableau pour qu'il soit indexé correctement par ID
$voyages = [];
foreach ($voyages_array as $voyage) {
    $voyages[$voyage['id']] = $voyage;
}

// Récupérer la catégorie sélectionnée
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Traitement de la recherche
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : '';
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : '';
$budget = isset($_GET['budget']) ? (int) $_GET['budget'] : 0;

// Construction des paramètres de recherche pour les liens de pagination
$search_params = '';
if (!empty($category) && $category != 'all') {
    $search_params .= '&category=' . urlencode($category);
}
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
$voyages_filtered = array_filter($voyages, function ($voyage) use ($recherche, $date_debut, $date_fin, $budget, $category) {
    // Recherche dans le titre ou dans les catégories
    $titre_match = empty($recherche) || stripos($voyage['titre'], $recherche) !== false;
    $categorie_match = false;
    
    // Recherche dans les catégories du voyage
    if (!empty($recherche) && isset($voyage['categories'])) {
        foreach ($voyage['categories'] as $cat) {
            if (stripos($cat, $recherche) !== false) {
                $categorie_match = true;
                break;
            }
        }
    }
    
    $budget_match = $budget <= 0 || $voyage['prix'] <= $budget;
    
    // Filtrer par catégorie si une catégorie spécifique est sélectionnée
    $category_match = true;
    if ($category != 'all') {
        $category_match = false;
        if (isset($voyage['categories'])) {
            foreach ($voyage['categories'] as $cat) {
                if (strtolower($cat) == strtolower($category)) {
                    $category_match = true;
                    break;
                }
            }
        }
    }

    // Considérer la recherche comme réussie si le texte correspond au titre OU aux catégories
    return ($titre_match || $categorie_match) && $budget_match && $category_match;
});

// Pagination pour les résultats de recherche ou tous les voyages
$voyages_per_page = 6;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$total_voyages = count($voyages_filtered);
$total_pages = ceil($total_voyages / $voyages_per_page);
$page = min($page, $total_pages);
$offset = ($page - 1) * $voyages_per_page;

// Obtenir les voyages pour la page actuelle
// Si une catégorie est sélectionnée ou une recherche est effectuée, afficher tous les voyages
if ($category != 'all' || !empty($recherche) || !empty($date_debut) || !empty($date_fin) || $budget > 0) {
    $voyages_page = $voyages_filtered;
} else {
    $voyages_page = array_slice($voyages_filtered, $offset, $voyages_per_page);
}

// Sélectionner les 4 premiers voyages pour la section "Meilleures destinations"
$best_voyages = array_slice($voyages, 0, 4);

// Récupérer le thème depuis le cookie
$theme = load_user_theme();
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

<body class="<?php echo $theme; ?>-theme">
    <?php include 'nav.php'; ?>

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
                            <a href="?category=all#toutes-destinations" class="search-tab <?php echo ($category == 'all' && !empty($_GET)) ? 'active' : ''; ?>">Tous les voyages</a>
                            <a href="?category=all#best-seller" class="search-tab <?php echo ($category == 'best-sellers') ? 'active' : ''; ?>">Best-sellers</a>
                            <div class="tab-indicator"></div>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                        </div>

                        <div class="search-fields-container">
                            <div class="field-focus-indicator"></div>
                            <div class="search-field">
                                <div class="field-icon">
                                    <img src="../img/svg/map-pin.svg" alt="Destination" class="field-icon-img">
                                </div>
                                <div class="field-content">
                                    <label for="destination-search">Destination ou catégorie</label>
                                    <input type="search" id="destination-search" name="recherche"
                                        placeholder="Dite nous tout" value="<?= htmlspecialchars($recherche) ?>" autocomplete="off">
                                    
                                    <div class="destination-suggestions" id="destination-suggestions">
                                        <div class="destination-suggestions-div">
                                            <h3>Suggestions de destinations</h3>
                                            
                                            <?php 
                                            // Parcourir tous les voyages et générer dynamiquement les suggestions
                                            foreach ($voyages_array as $voyage):
                                                // Détermine l'icône à utiliser - utilise une icône par défaut si non spécifiée
                                                $icon_name = strtolower(str_replace(' ', '-', $voyage['titre']));
                                                $icon_path = "../img/icone-voyage/{$icon_name}-icon.png";
                                                // Vérifier si l'icône existe, sinon utiliser une icône par défaut
                                                $real_path = $_SERVER['DOCUMENT_ROOT'] . '/MarvelTravel/img/icone-voyage/' . $icon_name . '-icon.png';
                                                $icon_path = file_exists($real_path) 
                                                    ? $icon_path 
                                                    : "../img/icone-voyage/default-icon.png";
                                                
                                                // Déterminer l'information complémentaire à afficher
                                                $info_text = "";
                                                if (isset($voyage['resume']) && !empty($voyage['resume'])) {
                                                    $info_text = $voyage['resume'];
                                                } elseif (isset($voyage['categories']) && !empty($voyage['categories'])) {
                                                    $info_text = "Catégories : " . implode(", ", array_slice($voyage['categories'], 0, 2));
                                                } else {
                                                    $info_text = "Une destination unique dans l'univers Marvel";
                                                }
                                                
                                                // Déterminer le suffixe de localisation
                                                $location_suffix = "";
                                                if (isset($voyage['localisation']['type'])) {
                                                    switch ($voyage['localisation']['type']) {
                                                        case 'earth':
                                                            $location_suffix = ", Terre";
                                                            break;
                                                        case 'space':
                                                            $location_suffix = ", Espace";
                                                            break;
                                                        case 'dimension':
                                                            $location_suffix = ", Dimension alternative";
                                                            break;
                                                    }
                                                }
                                            ?>
                                            <div class="suggestion-item">
                                                <div class="suggestion-icon city-icon"><img src="<?php echo $icon_path; ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?> Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4><?php echo htmlspecialchars($voyage['titre']) . $location_suffix; ?></h4>
                                                    <p><?php echo htmlspecialchars($info_text); ?></p>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            
                                            <h3 style="margin-top: 15px;">Catégories populaires</h3>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/aventure-icon.png" alt="Aventure Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Aventure</h4>
                                                    <p>Voyages d'action et d'exploration</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/nature-icon.png" alt="Nature Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Nature</h4>
                                                    <p>Découvrez des paysages époustouflants</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/ville-icon.png" alt="Ville Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Urbain</h4>
                                                    <p>Explorez les métropoles de l'univers Marvel</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/aventure-icon.png" alt="Espace Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Espace</h4>
                                                    <p>Voyages intersidéraux et mondes lointains</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/nature-icon.png" alt="Culture Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Culture</h4>
                                                    <p>Immersion dans les civilisations Marvel</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/ville-icon.png" alt="Combat Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Combat</h4>
                                                    <p>Expériences de combat et entraînement</p>
                                                </div>
                                            </div>
                                            
                                            <div class="suggestion-item category-suggestion">
                                                <div class="suggestion-icon category-icon"><img src="../img/icone-voyage/aventure-icon.png" alt="Détente Icon"></div>
                                                <div class="suggestion-content">
                                                    <h4>Détente</h4>
                                                    <p>Relaxation et vacances paisibles</p>
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
                                            <img src="../img/svg/arrival.svg" alt="Dates" class="field-icon-img">
                                        </div>

                                        <div class="field-content"> 
                                            <label for="date-debut-visible">Départ</label>
                                            <input type="text" id="date-debut-visible" readonly placeholder="Départ" class="date-input" value="<?php echo !empty($date_debut) ? date('d M', strtotime($date_debut)) : ''; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="search-divider"></div>

                                    <div class="date-inputs">
                                        <div class="field-icon date-fin-icon">
                                            <img src="../img/svg/departure.svg" alt="Dates" class="field-icon-img">
                                        </div>
                                        <div class="field-content">
                                            <label for="date-fin-visible">Arrivée</label>
                                            <input type="text" id="date-fin-visible" readonly placeholder="Arrivée" class="date-input" value="<?php echo !empty($date_fin) ? date('d M', strtotime($date_fin)) : ''; ?>" autocomplete="off">
                                        </div>
                                    </div>

                                    <input type="hidden" id="date-debut" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" autocomplete="off">
                                    <input type="hidden" id="date-fin" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" autocomplete="off">
                                </div>
                                
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
                    <a href="?recherche=Aventure#toutes-destinations" class="search-tag">Aventure</a>
                    <a href="?recherche=Nature#toutes-destinations" class="search-tag">Nature</a>
                    <a href="?recherche=Culture#toutes-destinations" class="search-tag">Culture</a>
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
    
    <?php if (empty($_GET) || ($category == 'all' && empty($recherche) && empty($date_debut) && empty($date_fin) && $budget <= 0)): ?>
        <section class="best-seller-section" id="best-seller">
            <div class="container">
                <div class="section-header">
                    <div class="section-title-group">
                        <span class="section-subtitle">Top destinations</span>
                        <h2 class="section-title">Meilleures destinations</h2>
                    </div>
                    <a href="#toutes-destinations" class="view-all-link">
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
                                    
                                    <p class="card-description">
                                        <?php 
                                        $description = isset($voyage['description']) ? $voyage['description'] : 'Une expérience unique dans l\'univers Marvel';
                                        echo htmlspecialchars(substr($description, 0, 120)) . '...'; 
                                        ?>
                                    </p>
                                    
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


    <section id="toutes-destinations" class="all-destination-section">
        <div class="container">
            <div class="section-header">
                <div class="section-title-group">
                    <?php if (!empty($recherche) || !empty($date_debut) || !empty($date_fin) || $budget > 0 || $category != 'all'): ?>
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

                <?php if (!empty($recherche) || !empty($date_debut) || !empty($date_fin) || $budget > 0 || $category != 'all'): ?>
                <div class="sort-filter">
                    <label for="sort">Trier par:</label>
                    <select id="sort" class="sort-select">
                        <option value="popular">Popularité</option>
                        <option value="price-asc">Prix: croissant</option>
                        <option value="price-desc">Prix: décroissant</option>
                        <option value="name-asc">Nom: A-Z</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <?php if (empty($voyages_page)): ?>
                <div class="no-results">
                    <img src="../img/svg/filter-empty.svg" alt="Aucun résultat" class="no-results-icon">
                    <h3>Aucune destination trouvée</h3>
                    <p>Essayez de modifier vos critères de recherche</p>
                    <a href="destination.php" class="btn-reset">Voir toutes les destinations</a>
                </div>
            <?php else: ?>
                <div class="all-destination-cards">
                    <?php foreach ($voyages_page as $voyage): 
                        // Récupérer l'ID du voyage directement depuis le tableau du voyage
                        $real_index = $voyage['id'];
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

                <?php if ($category == 'all' && empty($recherche) && empty($date_debut) && empty($date_fin) && $budget <= 0): ?>
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

    <script src="../js/calendar.js"></script>
    <script src="../js/destination.js"></script>

</body>

</html>