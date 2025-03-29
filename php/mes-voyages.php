<?php
require('session.php');
check_auth('connexion.php');

// Paramètres de filtrage et tri
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';
$view = isset($_GET['view']) ? $_GET['view'] : 'table';
$displayed_voyages = 0;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Mes voyages</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="stylesheet" href="../css/mes-voyages.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <!-- En-tête avec recherche et navigation -->
                    <div class="header">
                        <form class="search-bar" method="GET" action="">
                            <input type="text" placeholder="Rechercher une destination, une date..." name="search"
                                id="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" aria-label="Rechercher">
                                <img src="../img/svg/loupe.svg" alt="Rechercher">
                            </button>
                            <!-- Conserver les autres paramètres lors de la recherche -->
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                        </form>

                        <a href="profil.php" class="redir-text">
                            <span>Retour au profil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="Retour">
                        </a>
                    </div>

                    <div class="main-content">
                        <!-- Titre et compteur -->
                        <div class="titre-content">
                            <span>Mes Voyages</span>
                            <span id="voyage-count"></span>
                        </div>

                        <!-- Barre de filtres et options de tri -->
                        <div class="filters-bar">
                            <div class="filter-buttons">
                                <a href="?view=<?php echo $view; ?>&sort=<?php echo $sort; ?>&filter=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                    class="filter-button <?php echo $filter == 'all' ? 'active3' : ''; ?>">
                                    Tous
                                </a>
                                <a href="?view=<?php echo $view; ?>&sort=<?php echo $sort; ?>&filter=confirmed<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                    class="filter-button <?php echo $filter == 'confirmed' ? 'active3' : ''; ?>">
                                    Confirmés
                                </a>
                                <a href="?view=<?php echo $view; ?>&sort=<?php echo $sort; ?>&filter=pending<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                    class="filter-button <?php echo $filter == 'pending' ? 'active3' : ''; ?>">
                                    En attente
                                </a>
                                <a href="?view=<?php echo $view; ?>&sort=<?php echo $sort; ?>&filter=upcoming<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                    class="filter-button <?php echo $filter == 'upcoming' ? 'active3' : ''; ?>">
                                    À venir
                                </a>
                            </div>

                            <div class="view-options">
                                <select name="sort" class="sort-select" id="sort-select"
                                    onchange="updateSort(this.value)">
                                    <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>Plus récents
                                    </option>
                                    <option value="price-asc" <?php echo $sort == 'price-asc' ? 'selected' : ''; ?>>Prix
                                        croissant</option>
                                    <option value="price-desc" <?php echo $sort == 'price-desc' ? 'selected' : ''; ?>>Prix
                                        décroissant</option>
                                    <option value="date-asc" <?php echo $sort == 'date-asc' ? 'selected' : ''; ?>>Date
                                        (croissant)</option>
                                    <option value="date-desc" <?php echo $sort == 'date-desc' ? 'selected' : ''; ?>>Date
                                        (décroissant)</option>
                                </select>

                                <div class="view-toggles">
                                    <a href="?view=table&sort=<?php echo $sort; ?>&filter=<?php echo $filter; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                        class="view-toggle <?php echo $view == 'table' ? 'active3' : ''; ?>"
                                        title="Vue tableau">
                                        <img src="../img/svg/list.svg" alt="Vue tableau">
                                    </a>
                                    <a href="?view=cards&sort=<?php echo $sort; ?>&filter=<?php echo $filter; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                        class="view-toggle <?php echo $view == 'cards' ? 'active3' : ''; ?>"
                                        title="Vue cartes">
                                        <img src="../img/svg/grid.svg" alt="Vue cartes">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php
                        $json_file_path = '../json/commandes.json';
                        $commandes = false;
                        $total_voyages = 0;
                        $user_commandes = [];
                        $today = new DateTime();

                        if (file_exists($json_file_path)) {
                            $json_file = file_get_contents($json_file_path);
                            $data = json_decode($json_file, true);

                            if (!empty($data) && is_array($data)) {
                                // Filtrer les commandes de l'utilisateur actuel
                                foreach ($data as $commande) {
                                    if ($commande['acheteur'] == $_SESSION['email']) {
                                        // Vérifier les filtres
                                        $date_debut = new DateTime($commande['date_debut']);
                                        $is_upcoming = $today < $date_debut;

                                        if (
                                            ($filter == 'confirmed' && $commande['status'] != 'accepted') ||
                                            ($filter == 'pending' && $commande['status'] != 'pending') ||
                                            ($filter == 'upcoming' && !$is_upcoming)
                                        ) {
                                            continue;
                                        }

                                        // Ajouter les métadonnées pour le tri
                                        $commande['is_upcoming'] = $is_upcoming;
                                        $commande['jours_restants'] = $is_upcoming ? $today->diff($date_debut)->days : 0;
                                        $commande['est_recent'] = isset($commande['date_achat']) &&
                                            (time() - strtotime($commande['date_achat'])) < 1 * 24 * 60 * 60; // 1 jour
                        
                                        // Filtrer par la recherche
                                        if (!empty($search_query)) {
                                            $voyage_info = strtolower($commande['voyage']);
                                            $search_term = strtolower($search_query);

                                            if (strpos($voyage_info, $search_term) === false) {
                                                // Vérifier aussi les dates
                                                $date_debut_str = date('d/m/Y', strtotime($commande['date_debut']));
                                                $date_fin_str = date('d/m/Y', strtotime($commande['date_fin']));

                                                if (
                                                    strpos($date_debut_str, $search_term) === false &&
                                                    strpos($date_fin_str, $search_term) === false
                                                ) {
                                                    continue;
                                                }
                                            }
                                        }

                                        $user_commandes[] = $commande;
                                        $total_voyages++;
                                    }
                                }

                                // Trier les commandes selon le critère choisi
                                switch ($sort) {
                                    case 'price-asc':
                                        usort($user_commandes, function ($a, $b) {
                                            return $a['montant'] - $b['montant'];
                                        });
                                        break;
                                    case 'price-desc':
                                        usort($user_commandes, function ($a, $b) {
                                            return $b['montant'] - $a['montant'];
                                        });
                                        break;
                                    case 'date-asc':
                                        usort($user_commandes, function ($a, $b) {
                                            return strtotime($a['date_debut']) - strtotime($b['date_debut']);
                                        });
                                        break;
                                    case 'date-desc':
                                        usort($user_commandes, function ($a, $b) {
                                            return strtotime($b['date_debut']) - strtotime($a['date_debut']);
                                        });
                                        break;
                                    default: // 'recent' (défaut)
                                        usort($user_commandes, function ($a, $b) {
                                            if (isset($a['date_achat']) && isset($b['date_achat'])) {
                                                return strtotime($b['date_achat']) - strtotime($a['date_achat']);
                                            }
                                            return strcmp($b['transaction'], $a['transaction']);
                                        });
                                }

                                $displayed_voyages = count($user_commandes);
                            }
                        }
                        ?>

                        <?php if ($view == 'table' && $displayed_voyages > 0): ?>
                            <!-- Vue tableau -->
                            <div class="table-container">
                                <table class="tab-voyages">
                                    <thead>
                                        <tr>
                                            <th class="destination">Destination</th>
                                            <th>Dates</th>
                                            <th>Voyageurs</th>
                                            <th>Prix</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_commandes as $commande): ?>
                                            <?php
                                            $status_class = ($commande['status'] == 'accepted') ? 'status-ok' : 'status-pending';
                                            $status_text = ($commande['status'] == 'accepted') ? 'Confirmé' : 'En attente';
                                            $status_icon = ($commande['status'] == 'accepted') ? 'check.svg' : 'warning.svg';
                                            $row_class = '';

                                            if ($commande['is_upcoming'] && $commande['jours_restants'] < 7) {
                                                $row_class = 'row-highlight';
                                            }
                                            ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="destination">
                                                    <?php echo htmlspecialchars($commande['voyage']); ?>
                                                    <?php if ($commande['est_recent']): ?>
                                                        <span class="badge badge-new">Nouveau</span>
                                                    <?php endif; ?>
                                                    <?php if ($commande['is_upcoming'] && $commande['jours_restants'] < 7): ?>
                                                        <span class="badge badge-soon">Imminent</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($commande['date_debut'])) . ' au ' . date('d/m/Y', strtotime($commande['date_fin'])); ?>
                                                    <?php if ($commande['is_upcoming']): ?>
                                                        <div class="countdown">Dans <?php echo $commande['jours_restants'] + 1; ?>
                                                            jour<?php echo $commande['jours_restants'] > 1 ? 's' : ''; ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $commande['nb_personne']; ?>
                                                    personne<?php echo $commande['nb_personne'] > 1 ? 's' : ''; ?></td>
                                                <td><?php echo number_format($commande['montant'], 2, ',', ' '); ?> €</td>
                                                <td>
                                                    <div class="status <?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                        <img src="../img/svg/<?php echo $status_icon; ?>" alt="statut">
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="commande.php?transaction=<?php echo $commande['transaction']; ?>"
                                                        class="view-button">
                                                        Détails
                                                        <img src="../img/svg/fleche-droite.svg" alt="voir">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php elseif ($view == 'cards' && $displayed_voyages > 0): ?>
                            <!-- Vue cartes -->
                            <div class="voyages-list">
                                <?php foreach ($user_commandes as $commande): ?>
                                    <?php
                                    $status_class = ($commande['status'] == 'accepted') ? 'status-ok' : 'status-pending';
                                    $status_text = ($commande['status'] == 'accepted') ? 'Confirmé' : 'En attente';
                                    $status_icon = ($commande['status'] == 'accepted') ? 'check.svg' : 'warning.svg';
                                    $card_class = '';

                                    if ($commande['is_upcoming'] && $commande['jours_restants'] < 7) {
                                        $card_class = 'card-highlight';
                                    }
                                    ?>
                                    <a href="commande.php?transaction=<?php echo $commande['transaction']; ?>"
                                        class="voyage-card <?php echo $card_class; ?>">
                                        <div class="voyage-header">
                                            <h3>
                                                <?php echo htmlspecialchars($commande['voyage']); ?>
                                                <?php if ($commande['est_recent']): ?>
                                                    <span class="badge badge-new">Nouveau</span>
                                                <?php endif; ?>
                                            </h3>

                                            <div class="status <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                                <img src="../img/svg/<?php echo $status_icon; ?>" alt="statut">
                                            </div>
                                        </div>
                                        <div class="voyage-details">
                                            <div class="voyage-info">
                                                <span class="dates">
                                                    <?php echo date('d/m/Y', strtotime($commande['date_debut'])) . ' au ' . date('d/m/Y', strtotime($commande['date_fin'])); ?>
                                                </span>
                                                <span
                                                    class="price"><?php echo number_format($commande['montant'], 2, ',', ' '); ?>
                                                    €</span>
                                                <span class="travelers"><?php echo $commande['nb_personne']; ?>
                                                    personne<?php echo $commande['nb_personne'] > 1 ? 's' : ''; ?></span>
                                                <?php if ($commande['is_upcoming']): ?>
                                                    <span class="countdown">Départ dans <?php echo $commande['jours_restants']; ?>
                                                        jour<?php echo $commande['jours_restants'] > 1 ? 's' : ''; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="voyage-action">
                                                Voir détails
                                                <img src="../img/svg/fleche-droite.svg" alt="voir">
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <!-- Message si aucun voyage -->
                            <div class="no-res">
                                <?php if (!empty($search_query)): ?>
                                    <img src="../img/svg/search-not-found.svg" alt="Aucun résultat" class="no-res-icon">
                                    <p>Aucun voyage ne correspond à votre recherche
                                        "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                                    <a href="mes-voyages.php" class="reset-search">Effacer la recherche</a>
                                <?php elseif ($filter != 'all'): ?>
                                    <img src="../img/svg/filter-empty.svg" alt="Aucun résultat" class="no-res-icon">
                                    <p>Aucun voyage ne correspond au filtre sélectionné</p>
                                    <a href="mes-voyages.php" class="reset-search">Voir tous mes voyages</a>
                                <?php else: ?>
                                    <img src="../img/svg/empty-voyages.svg" alt="Aucun voyage" class="no-res-icon">
                                    <p>Vous n'avez pas encore de voyages réservés</p>
                                    <a href="../php/destinations.php" class="action-button primary-button">
                                        Réserver mon premier voyage
                                        <img src="../img/svg/plane.svg" alt="Réserver">
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($displayed_voyages > 0): ?>
                            <!-- Bouton pour réserver un nouveau voyage -->
                            <div class="actions-container">
                                <a href="../php/destination.php" class="action-button primary-button">
                                    Réserver un nouveau voyage
                                    <img src="../img/svg/plane.svg" alt="Réserver" style="width: 20px;">
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>
    <script src="../js/modal-handlers.js"></script>
    <script>
        // Mettre à jour le compteur de voyages
        document.getElementById('voyage-count').textContent = "<?php echo $total_voyages; ?> au total";

        // Fonction pour mettre à jour le tri
        function updateSort(value) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', value);
            window.location = currentUrl;
        }
    </script>
</body>

</html>