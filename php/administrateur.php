<?php

require('session.php');
check_admin_auth('connexion.php');
$_SESSION['current_url'] = current_url();

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

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
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="header">
                    <form class="search-bar" method="GET" action="">
                        <input type="text" placeholder="Chercher un voyageur" name="search" id="search"
                            value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" style="background: none; border: none; cursor: pointer;">
                            <img src="../img/svg/loupe.svg" alt="loupe">
                        </button>
                    </form>

                    <a href="javascript:history.back()" class="redir-text">
                        <span>Quitter le mode administrateur</span>
                        <img src="../img/svg/fleche-redir.svg" alt="fleche">
                    </a>
                </div>

                <div class="main-content">
                    <div class="titre-content">
                        <span>Voyageurs</span>
                        <span>12 au totals</span>
                    </div>

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

                            foreach ($users as $user) {
                                if ($user['role'] === 'user') {
                                    // Filtrer par la recherche
                                    $full_name = strtolower($user['first_name'] . ' ' . $user['last_name']);
                                    $email = strtolower($user['email'] ?? '');
                                    $search_term = strtolower($search_query);

                                    // Si une recherche est active et que l'utilisateur ne correspond pas, passer au suivant
                                    if (!empty($search_query) && strpos($full_name, $search_term) === false && strpos($email, $search_term) === false) {
                                        continue;
                                    }

                                    $displayed_users++;

                                    echo '<tr>';
                                    echo '<td class="nom">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
                                    echo '<td>';
                                    if ($user['blocked']) {
                                        echo '<div class="block">Bloqué<img src="../img/svg/block.svg" alt="block"></div>';
                                    } else {
                                        echo '<div class="unblock">Non bloqué<img src="../img/svg/check.svg" alt="check"></div>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    if ($user['vip']) {
                                        echo '<div class="vip">VIP<img src="../img/svg/etoile.svg" alt="etoile"></div>';
                                    } else {
                                        echo '<div class="vip novip">Non<img src="../img/svg/no.svg" alt="croix"></div>';
                                    }
                                    echo '</td>';
                                    echo '<td class="date">' . $user['date_inscription'] . '</td>';
                                    echo '</tr>';
                                }
                            }

                            ?>
                        </tbody>
                    </table>
                    <?php if ($displayed_users === 0 && !empty($search_query)): ?>
                        <div class="no-res">
                            <p>Aucun utilisateur ne correspond à votre recherche</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

    <?php include 'modals.php'; ?>

    <script src="../js/custom-cursor.js"></script>
    <script src="../js/modal-handlers.js"></script>
    <script src="../js/admin.js"></script>

</body>

</html>