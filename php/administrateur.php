<?php

require('session.php');
check_admin_auth('connexion.php');
$_SESSION['current_url'] = current_url();

// Paramètres de filtrage et tri
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

$displayed_users = 0;

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
                            <input type="text" placeholder="Chercher un voyageur" name="search" id="search"
                                value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" style="background: none; border: none; cursor: pointer;">
                                <img src="../img/svg/loupe.svg" alt="loupe">
                            </button>
                            <!-- Conserver les autres paramètres lors de la recherche -->
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        </form>

                        <a href="profil.php" class="redir-text">
                            <span>Quitter le mode administrateur</span>
                            <img src="../img/svg/fleche-redir.svg" alt="fleche">
                        </a>
                    </div>

                    <div class="main-content">
                        <!-- Titre et compteur -->
                        <div class="titre-content">
                            <span>Voyageurs</span>
                            <span id="users-count"></span>
                        </div>

                        <!-- Barre de filtres et options de tri -->
                        <div class="filters-bar">
                            <div class="filter-buttons">
                                <a href="?filter=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>&sort=<?php echo $sort; ?>"
                                    class="filter-button <?php echo $filter == 'all' ? 'active3' : ''; ?>">
                                    Tous
                                </a>
                                <a href="?filter=active<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>&sort=<?php echo $sort; ?>"
                                    class="filter-button <?php echo $filter == 'active' ? 'active3' : ''; ?>">
                                    Actifs
                                </a>
                                <a href="?filter=blocked<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>&sort=<?php echo $sort; ?>"
                                    class="filter-button <?php echo $filter == 'blocked' ? 'active3' : ''; ?>">
                                    Bloqués
                                </a>
                                <a href="?filter=vip<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>&sort=<?php echo $sort; ?>"
                                    class="filter-button <?php echo $filter == 'vip' ? 'active3' : ''; ?>">
                                    VIP
                                </a>
                            </div>

                            <div class="view-options">
                                <select name="sort" class="sort-select" id="sort-select"
                                    onchange="updateSort(this.value)">
                                    <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>Plus récents
                                    </option>
                                    <option value="name-asc" <?php echo $sort == 'name-asc' ? 'selected' : ''; ?>>Nom
                                        (A-Z)</option>
                                    <option value="name-desc" <?php echo $sort == 'name-desc' ? 'selected' : ''; ?>>Nom
                                        (Z-A)</option>
                                    <option value="date-asc" <?php echo $sort == 'date-asc' ? 'selected' : ''; ?>>Date
                                        d'inscription (ancien)</option>
                                    <option value="date-desc" <?php echo $sort == 'date-desc' ? 'selected' : ''; ?>>Date
                                        d'inscription (récent)</option>
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

                                <tbody
                                    class="<?php echo ($displayed_users === 0 && !empty($search_query)) ? 'no-height' : ''; ?>">
                                    <?php
                                    $json_file = "../json/users.json";
                                    $users = json_decode(file_get_contents($json_file), true) ?? [];
                                    $displayed_users = 0;
                                    $total_users = 0;

                                    // Filtrer et trier les utilisateurs
                                    $filtered_users = [];
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

                                            // Filtrer par la recherche
                                            $full_name = strtolower($user['first_name'] . ' ' . $user['last_name']);
                                            $email = strtolower($user['email'] ?? '');
                                            $search_term = strtolower($search_query);

                                            // Si une recherche est active et que l'utilisateur ne correspond pas, passer au suivant
                                            if (!empty($search_query) && strpos($full_name, $search_term) === false && strpos($email, $search_term) === false) {
                                                continue;
                                            }

                                            $filtered_users[] = $user;
                                        }
                                    }

                                    // Trier les utilisateurs
                                    switch ($sort) {
                                        case 'name-asc':
                                            usort($filtered_users, function ($a, $b) {
                                                return strcasecmp($a['last_name'], $b['last_name']);
                                            });
                                            break;
                                        case 'name-desc':
                                            usort($filtered_users, function ($a, $b) {
                                                return strcasecmp($b['last_name'], $a['last_name']);
                                            });
                                            break;
                                        case 'date-asc':
                                            usort($filtered_users, function ($a, $b) {
                                                return strtotime($a['date_inscription']) - strtotime($b['date_inscription']);
                                            });
                                            break;
                                        case 'date-desc':
                                            usort($filtered_users, function ($a, $b) {
                                                return strtotime($b['date_inscription']) - strtotime($a['date_inscription']);
                                            });
                                            break;
                                        default: // recent (défaut)
                                            usort($filtered_users, function ($a, $b) {
                                                return strtotime($b['date_inscription']) - strtotime($a['date_inscription']);
                                            });
                                    }

                                    $displayed_users = count($filtered_users);

                                    foreach ($filtered_users as $user) {
                                        echo '<tr data-email="' . htmlspecialchars($user['email']) . '">';
                                        echo '<td class="nom">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
                                        echo '<td>';
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
                                        echo '<td>';
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
                                        echo '<td class="date">' . $user['date_inscription'] . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($displayed_users === 0): ?>
                            <div class="no-res">
                                <?php if (!empty($search_query)): ?>
                                    <img src="../img/svg/empty-voyages.svg" alt="Aucun résultat" class="no-res-icon">
                                    <p>Aucun utilisateur ne correspond à votre recherche
                                        "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                                    </p>
                                    <a href="administrateur.php" class="reset-search">Effacer la recherche</a>
                                <?php elseif ($filter != 'all'): ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Afficher le nombre d'utilisateurs
            const userCount = document.querySelectorAll('tbody tr').length;
            document.getElementById('users-count').textContent = userCount + ' voyageurs';

            // Gestion des clics sur les status (bloqué/actif)
            document.querySelectorAll('.toggle-status').forEach(statusElement => {
                statusElement.addEventListener('click', function () {
                    // Vérifier l'état actuel
                    const isBlocked = this.getAttribute('data-status') === 'blocked';

                    // Simuler un délai de mise à jour
                    this.classList.add('updating');

                    setTimeout(() => {
                        // Basculer l'état
                        if (isBlocked) {
                            // Changer en Actif
                            this.classList.remove('status-pending');
                            this.classList.add('status-ok');
                            this.setAttribute('data-status', 'active');
                            this.innerHTML = 'Actif<img src="../img/svg/check.svg" alt="check"><span class="tooltip">Cliquez pour bloquer</span>';
                        } else {
                            // Changer en Bloqué
                            this.classList.remove('status-ok');
                            this.classList.add('status-pending');
                            this.setAttribute('data-status', 'blocked');
                            this.innerHTML = 'Bloqué<img src="../img/svg/block.svg" alt="block"><span class="tooltip">Cliquez pour débloquer</span>';
                        }

                        this.classList.remove('updating');

                        // Afficher une notification
                        showNotification(isBlocked ? 'Utilisateur débloqué avec succès' : 'Utilisateur bloqué avec succès', 'success');
                    }, 1000);
                });
            });

            // Gestion des clics sur les badges VIP
            document.querySelectorAll('.toggle-vip').forEach(vipElement => {
                vipElement.addEventListener('click', function () {
                    // Vérifier l'état actuel
                    const isVip = this.getAttribute('data-vip') === '1';

                    // Simuler un délai de mise à jour
                    this.classList.add('updating');

                    setTimeout(() => {
                        // Basculer l'état
                        if (isVip) {
                            // Changer en non-VIP
                            this.classList.remove('vip-badge');
                            this.classList.add('novip-badge');
                            this.setAttribute('data-vip', '0');
                            this.innerHTML = 'Non<img src="../img/svg/no.svg" alt="croix"><span class="tooltip">Cliquez pour ajouter le VIP</span>';
                        } else {
                            // Changer en VIP
                            this.classList.remove('novip-badge');
                            this.classList.add('vip-badge');
                            this.setAttribute('data-vip', '1');
                            this.innerHTML = 'VIP<img src="../img/svg/etoile.svg" alt="etoile"><span class="tooltip">Cliquez pour retirer le VIP</span>';
                        }

                        this.classList.remove('updating');

                        // Afficher une notification
                        showNotification(isVip ? 'Statut VIP retiré avec succès' : 'Statut VIP ajouté avec succès', 'success');
                    }, 300);
                });
            });

            // Fonction pour afficher les notifications
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.textContent = message;

                document.body.appendChild(notification);

                // Afficher avec fade-in
                setTimeout(() => notification.style.opacity = '1', 10);

                // Cacher après 3 secondes
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 500);
                }, 3000);
            }

            // Gestion du tri
            window.updateSort = function (value) {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('sort', value);
                window.location.href = currentUrl.toString();
            };
        });
    </script>
</body>

</html>