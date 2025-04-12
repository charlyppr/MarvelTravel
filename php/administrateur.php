<?php
require('session.php');
check_admin_auth('connexion.php');
$_SESSION['current_url'] = current_url();

// Paramètres de filtrage
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$displayed_users = 0;
$total_users = 0;

// Charger les données utilisateurs
$json_file = "../json/users.json";
$users = json_decode(file_get_contents($json_file), true) ?? [];

// Compter le nombre total d'utilisateurs (rôle user)
foreach ($users as $user) {
    if ($user['role'] === 'user') {
        $total_users++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Administrateur</title>

    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/base.css">
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
                <!-- En-tête avec recherche et navigation -->
                <div class="header">
                    <form class="search-bar" method="GET" action="">
                        <input type="text" placeholder="Rechercher un voyageur par nom ou email" name="search"
                            id="search" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" style="background: none; border: none; cursor: pointer;">
                            <img src="../img/svg/loupe.svg" alt="Rechercher">
                        </button>
                        <!-- Conserver les autres paramètres lors de la recherche -->
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    </form>

                    <a href="profil.php" class="redir-text">
                        <span>Quitter le mode administrateur</span>
                        <img src="../img/svg/fleche-redir.svg" alt="Quitter">
                    </a>
                </div>

                <div class="main-content">
                    <!-- Titre et compteur -->
                    <div class="titre-content">
                        <span>Voyageurs</span>
                        <span><?php echo $total_users; ?> au total</span>
                    </div>

                    <!-- Barre de filtres -->
                    <div class="filters-bar">
                        <div class="filter-buttons">
                            <a href="?filter=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                class="filter-button <?php echo $filter == 'all' ? 'active' : ''; ?>">
                                Tous
                            </a>
                            <a href="?filter=active<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                class="filter-button <?php echo $filter == 'active' ? 'active' : ''; ?>">
                                Actifs
                            </a>
                            <a href="?filter=blocked<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                class="filter-button <?php echo $filter == 'blocked' ? 'active' : ''; ?>">
                                Bloqués
                            </a>
                            <a href="?filter=vip<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                                class="filter-button <?php echo $filter == 'vip' ? 'active' : ''; ?>">
                                VIP
                            </a>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="tab-voyageurs">
                            <thead>
                                <tr>
                                    <th class="nom-head">Nom</th>
                                    <th>Status</th>
                                    <th>VIP</th>
                                    <th class="date-head">Membre depuis</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody
                                class="<?php echo ($displayed_users === 0 && !empty($search_query)) ? 'no-height' : ''; ?>">
                                <?php
                                $displayed_users = 0;

                                foreach ($users as $index => $user) {
                                    if ($user['role'] === 'user') {
                                        // Filtrer par la recherche
                                        $full_name = strtolower($user['first_name'] . ' ' . $user['last_name']);
                                        $email = strtolower($user['email'] ?? '');
                                        $search_term = strtolower($search_query);

                                        // Filtrer par le statut
                                        if (
                                            ($filter == 'active' && $user['blocked']) ||
                                            ($filter == 'blocked' && !$user['blocked']) ||
                                            ($filter == 'vip' && !$user['vip'])
                                        ) {
                                            continue;
                                        }

                                        // Si une recherche est active et que l'utilisateur ne correspond pas, passer au suivant
                                        if (!empty($search_query) && strpos($full_name, $search_term) === false && strpos($email, $search_term) === false) {
                                            continue;
                                        }

                                        $displayed_users++;

                                        echo '<tr>';
                                        echo '<td class="nom">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</td>';
                                        echo '<td>';
                                        if ($user['blocked']) {
                                            echo '<div class="block">Bloqué<img src="../img/svg/block.svg" alt="Bloqué"></div>';
                                        } else {
                                            echo '<div class="unblock">Actif<img src="../img/svg/check.svg" alt="Actif"></div>';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($user['vip']) {
                                            echo '<div class="vip">VIP<img src="../img/svg/etoile.svg" alt="VIP"></div>';
                                        } else {
                                            echo '<div class="novip">Non<img src="../img/svg/no.svg" alt="Non VIP"></div>';
                                        }
                                        echo '</td>';
                                        echo '<td class="date">' . htmlspecialchars($user['date_inscription']) . '</td>';
                                        echo '<td class="actions">';
                                        echo '<div class="action-icons">';
                                        echo '<button class="action-button edit-button" title="Modifier l\'utilisateur" onclick="editUser(' . $index . ')">';
                                        echo '<img src="../img/svg/edit.svg" alt="Modifier">';
                                        echo '</button>';

                                        if ($user['blocked']) {
                                            echo '<button class="action-button block-button" title="Débloquer l\'utilisateur" onclick="toggleBlock(' . $index . ', false)">';
                                            echo '<img src="../img/svg/unlock.svg" alt="Débloquer">';
                                        } else {
                                            echo '<button class="action-button block-button" title="Bloquer l\'utilisateur" onclick="toggleBlock(' . $index . ', true)">';
                                            echo '<img src="../img/svg/lock.svg" alt="Bloquer">';
                                        }
                                        echo '</button>';

                                        if ($user['vip']) {
                                            echo '<button class="action-button vip-button" title="Retirer VIP" onclick="toggleVIP(' . $index . ', false)">';
                                            echo '<img src="../img/svg/star-off.svg" alt="Retirer VIP">';
                                        } else {
                                            echo '<button class="action-button vip-button" title="Ajouter VIP" onclick="toggleVIP(' . $index . ', true)">';
                                            echo '<img src="../img/svg/star.svg" alt="Ajouter VIP">';
                                        }
                                        echo '</button>';
                                        echo '</div>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($displayed_users === 0): ?>
                        <div class="no-res">
                            <?php if (!empty($search_query)): ?>
                                <img src="../img/svg/empty-search.svg" alt="Aucun résultat" class="no-res-icon">
                                <p>Aucun utilisateur ne correspond à votre recherche
                                    "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                                <a href="administrateur.php" class="reset-search">Effacer la recherche</a>
                            <?php elseif ($filter != 'all'): ?>
                                <img src="../img/svg/filter-empty.svg" alt="Aucun résultat" class="no-res-icon">
                                <p>Aucun utilisateur ne correspond au filtre sélectionné</p>
                                <a href="administrateur.php" class="reset-search">Voir tous les utilisateurs</a>
                            <?php else: ?>
                                <img src="../img/svg/double-person.svg" alt="Aucun utilisateur" class="no-res-icon">
                                <p>Aucun utilisateur trouvé dans le système</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>

</html>