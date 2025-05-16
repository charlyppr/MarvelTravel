<?php
require_once 'session.php';
check_auth('connexion.php');

// Paramètres de filtrage
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if (isset($_GET['view'])) {
    $view = $_GET['view'];
    $_SESSION['preferred_view'] = $view;
} else {
    $view = isset($_SESSION['preferred_view']) ? $_SESSION['preferred_view'] : 'cards';
}

$displayed_voyages = 0;

// Récupérer le thème depuis le cookie
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Mes voyages</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/profil.css">
    <link rel="stylesheet" href="../css/mes-voyages.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <div class="main-container">
        <?php include_once 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <div class="header">
                        <div class="search-bar">
                            <input type="text" placeholder="Rechercher une destination, une date..." id="search">
                            <button type="button" aria-label="Rechercher">
                                <img src="../img/svg/loupe.svg" alt="Rechercher">
                            </button>
                        </div>

                        <a href="destination.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="Retour">
                        </a>
                    </div>

                    <div class="main-content">
                        <div class="titre-content">
                            <span>Mes Voyages</span>
                            <span id="voyage-count"></span>
                        </div>

                        <div class="filters-bar">
                            <div class="filter-buttons">
                                <a href="?view=<?php echo $view; ?>&filter=all" 
                                    class="filter-button <?php echo $filter == 'all' ? 'active3' : ''; ?>">
                                    Tous
                                </a>
                                <a href="?view=<?php echo $view; ?>&filter=confirmed" 
                                    class="filter-button <?php echo $filter == 'confirmed' ? 'active3' : ''; ?>">
                                    Confirmés
                                </a>
                                <a href="?view=<?php echo $view; ?>&filter=pending" 
                                    class="filter-button <?php echo $filter == 'pending' ? 'active3' : ''; ?>">
                                    En attente
                                </a>
                                <a href="?view=<?php echo $view; ?>&filter=upcoming" 
                                    class="filter-button <?php echo $filter == 'upcoming' ? 'active3' : ''; ?>">
                                    À venir
                                </a>
                            </div>

                            <div class="view-options">
                                <select id="sort-select" class="sort-select">
                                    <option value="recent">Plus récents</option>
                                    <option value="price-asc">Prix croissant</option>
                                    <option value="price-desc">Prix décroissant</option>
                                    <option value="date-asc">Date croissante</option>
                                    <option value="date-desc">Date décroissante</option>
                                </select>

                                <div class="view-toggles">
                                    <a href="?view=table&filter=<?php echo $filter; ?>" 
                                        class="view-toggle <?php echo $view == 'table' ? 'active3' : ''; ?>"
                                        title="Vue tableau">
                                        <img src="../img/svg/list.svg" alt="Vue tableau">
                                    </a>
                                    <a href="?view=cards&filter=<?php echo $filter; ?>" 
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

                                        // Ajouter les métadonnées pour le tri et la recherche
                                        $commande['is_upcoming'] = $is_upcoming;
                                        $commande['jours_restants'] = $is_upcoming ? $today->diff($date_debut)->days : 0;
                                        $commande['est_recent'] = isset($commande['date_achat']) &&
                                            (time() - strtotime($commande['date_achat'])) < 1 * 24 * 60 * 60; // 1 jour
                                        
                                        $user_commandes[] = $commande;
                                        $total_voyages++;
                                    }
                                }

                                $displayed_voyages = count($user_commandes);
                                
                                // Préparation pour le tri par défaut (Plus récents)
                                // Tri des commandes par date d'achat (plus récent en premier)
                                usort($user_commandes, function($a, $b) {
                                    $time_a = isset($a['date_achat']) ? strtotime($a['date_achat']) : 0;
                                    $time_b = isset($b['date_achat']) ? strtotime($b['date_achat']) : 0;
                                    return $time_b - $time_a; // Ordre décroissant (plus récent en premier)
                                });
                            }
                        }
                        ?>

                        <?php if ($view == 'table' && $displayed_voyages > 0): ?>
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
                                            <tr class="<?php echo $row_class; ?>" data-date-achat="<?php echo isset($commande['date_achat']) ? $commande['date_achat'] : ''; ?>">
                                                <td class="destination">
                                                    <?php if ($commande['est_recent']): ?>
                                                        <div class="badge badge-new">Nouveau</div>
                                                    <?php endif; ?>
                                                    <?php if ($commande['is_upcoming'] && $commande['jours_restants'] < 7): ?>
                                                        <div class="badge badge-soon">Imminent</div>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($commande['voyage']); ?>
                                                </td>
                                                <td class="dates" data-label="Dates">
                                                    <?php echo date('d/m/Y', strtotime($commande['date_debut'])) . ' au ' . date('d/m/Y', strtotime($commande['date_fin'])); ?>
                                                    <?php if ($commande['is_upcoming']): ?>
                                                        <div class="countdown">Dans <?php echo $commande['jours_restants'] + 1; ?>
                                                            jour<?php echo $commande['jours_restants'] > 1 ? 's' : ''; ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="travelers" data-label="Voyageurs"><?php echo $commande['nb_personne']; ?>
                                                    personne<?php echo $commande['nb_personne'] > 1 ? 's' : ''; ?></td>
                                                <td class="price" data-label="Prix"><?php echo number_format($commande['montant'], 2, ',', ' '); ?> €</td>
                                                <td data-label="Statut">
                                                    <div class="status <?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                        <img src="../img/svg/<?php echo $status_icon; ?>" alt="statut">
                                                    </div>
                                                </td>
                                                <td data-label="Actions">
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
                                        class="voyage-card <?php echo $card_class; ?>" 
                                        data-date-achat="<?php echo isset($commande['date_achat']) ? $commande['date_achat'] : ''; ?>">
                                        <div class="voyage-header">
                                            <h3 class="destination">
                                                <?php if ($commande['est_recent']): ?>
                                                    <div class="badge badge-new">Nouveau</div>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($commande['voyage']); ?>
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
                                                <?php if ($commande['status'] == 'accepted'): ?>
                                                <span class="ticket-action">
                                                    <button class="generate-ticket-btn small" data-transaction="<?php echo $commande['transaction']; ?>"
                                                        title="Télécharger mon billet">
                                                        <img src="../img/svg/download.svg" alt="Billet">
                                                        Billet
                                                    </button>
                                                </span>
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
                            <div class="no-res">
                                <img src="../img/svg/empty-voyages.svg" alt="Aucun voyage" class="no-res-icon">
                                <p>Vous n'avez pas encore de voyages réservés</p>
                                <a href="../php/destination.php" class="action-button primary-button">
                                    Réserver mon premier voyage
                                    <img src="../img/svg/plane.svg" alt="Réserver">
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="no-res" id="search-no-results" style="display: none;">
                            <img src="../img/svg/empty-voyages.svg" alt="Aucun résultat" class="no-res-icon">
                            <p>Aucun voyage ne correspond à votre recherche "<strong id="search-term"></strong>"</p>
                            <button class="reset-search" id="reset-search">Effacer la recherche</button>
                        </div>

                        <?php if ($displayed_voyages > 0): ?>
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
    <script src="../js/mes-voyages.js"></script>
    <script src="../js/ticket-generator.js"></script>
</body>

</html>