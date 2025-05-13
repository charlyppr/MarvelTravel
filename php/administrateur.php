<?php

require('session.php');
check_admin_auth('connexion.php');
$_SESSION['current_url'] = current_url();

// Paramètres de filtrage uniquement (le tri et la recherche seront gérés en JS)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$displayed_users = 0;

// Récupérer le thème depuis le cookie
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Administrateur</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/administrateur.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    <script src="../js/administrateur.js" defer></script>
</head>

<body class="<?php echo $theme; ?>-theme">

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <div class="header">
                        <div class="search-bar">
                            <input type="text" placeholder="Chercher un voyageur" id="search">
                            <button type="button" style="background: none; border: none; cursor: pointer;">
                                <img src="../img/svg/loupe.svg" alt="loupe">
                            </button>
                        </div>

                        <a href="destination.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="fleche">
                        </a>
                    </div>

                    <div class="main-content">
                        <div class="titre-content">
                            <span>Voyageurs</span>
                            <span id="voyage-count"></span>
                        </div>

                        <div class="filters-bar">
                            <div class="filter-buttons">
                                <a href="?filter=all"
                                    class="filter-button <?php echo $filter == 'all' ? 'active3' : ''; ?>">
                                    Tous
                                </a>
                                <a href="?filter=active"
                                    class="filter-button <?php echo $filter == 'active' ? 'active3' : ''; ?>">
                                    Actifs
                                </a>
                                <a href="?filter=blocked"
                                    class="filter-button <?php echo $filter == 'blocked' ? 'active3' : ''; ?>">
                                    Bloqués
                                </a>
                                <a href="?filter=vip"
                                    class="filter-button <?php echo $filter == 'vip' ? 'active3' : ''; ?>">
                                    VIP
                                </a>
                            </div>

                            <div class="view-options">
                                <select id="sort-select" class="sort-select">
                                    <option value="recent">Plus récents</option>
                                    <option value="oldest">Plus anciens</option>
                                    <option value="name-asc">Nom (A-Z)</option>
                                    <option value="name-desc">Nom (Z-A)</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="tab-voyageurs">
                                <thead>
                                    <tr>
                                        <th class="nom-head">Nom</th>
                                        <th>Status</th>
                                        <th>VIP</th>
                                        <th class="date">Membre depuis</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $json_file = "../json/users.json";
                                    $users = json_decode(file_get_contents($json_file), true) ?? [];
                                    $total_users = 0;
                                    $displayed_users = 0;

                                    // Filtrer les utilisateurs par le filtre sélectionné
                                    foreach ($users as $user) {
                                        if ($user['role'] === 'user') {
                                            $total_users++;

                                            // Filtrer par statut
                                            if (
                                                ($filter === 'blocked' && !$user['blocked']) ||
                                                ($filter === 'active' && $user['blocked']) ||
                                                ($filter === 'vip' && !$user['vip'])
                                            ) {
                                                continue;
                                            }

                                            $displayed_users++;

                                            echo '<tr data-email="' . htmlspecialchars($user['email']) . '">';
                                            echo '<td class="nom">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
                                            echo '<td data-label="Status">';
                                            // Bouton interactif pour le statut bloqué/actif
                                            if ($user['blocked']) {
                                                echo '<div class="status status-pending toggle-status" data-status="blocked">';
                                                echo 'Bloqué<img src="../img/svg/block.svg" alt="block">';
                                                echo '<span class="tooltip">Cliquez pour débloquer</span>';
                                                echo '</div>';
                                            } else {
                                                echo '<div class="status status-ok toggle-status" data-status="active">';
                                                echo 'Actif<img src="../img/svg/check.svg" alt="check">';
                                                echo '<span class="tooltip">Cliquez pour bloquer</span>';
                                                echo '</div>';
                                            }
                                            echo '</td>';
                                            echo '<td data-label="VIP">';
                                            // Bouton interactif pour le statut VIP/non VIP
                                            if ($user['vip']) {
                                                echo '<div class="status vip-badge toggle-vip" data-vip="1">';
                                                echo 'VIP<img src="../img/svg/etoile.svg" alt="etoile">';
                                                echo '<span class="tooltip">Cliquez pour retirer le VIP</span>';
                                                echo '</div>';
                                            } else {
                                                echo '<div class="status novip-badge toggle-vip" data-vip="0">';
                                                echo 'Non<img src="../img/svg/no.svg" alt="croix">';
                                                echo '<span class="tooltip">Cliquez pour ajouter le VIP</span>';
                                                echo '</div>';
                                            }
                                            echo '</td>';
                                            // Formatage de la date au format français (JJ/MM/AAAA)
                                            $date_inscription = new DateTime($user['date_inscription']);
                                            $date_formatted = $date_inscription->format('d/m/Y');
                                            echo '<td class="date" data-label="Membre depuis" data-date="' . $user['date_inscription'] . '">' . $date_formatted . '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="no-res" id="search-no-results" style="display: none;">
                            <img src="../img/svg/empty-voyages.svg" alt="Aucun résultat" class="no-res-icon">
                            <p>Aucun utilisateur ne correspond à votre recherche "<strong id="search-term"></strong>"
                            </p>
                            <button class="reset-search" id="reset-search">Effacer la recherche</button>
                        </div>

                        <?php if ($displayed_users === 0): ?>
                            <div class="no-res">
                                <?php if ($filter != 'all'): ?>
                                    <img src="../img/svg/filter-empty.svg" alt="Aucun résultat" class="no-res-icon">
                                    <p>Aucun utilisateur ne correspond au filtre sélectionné</p>
                                    <a href="administrateur.php" class="reset-search">Voir tous les utilisateurs</a>
                                <?php else: ?>
                                    <img src="../img/svg/empty-voyages.svg" alt="Aucun utilisateur" class="no-res-icon">
                                    <p>Aucun utilisateur trouvé</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>