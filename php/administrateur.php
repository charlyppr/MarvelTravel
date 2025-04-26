<?php

require('session.php');
check_admin_auth('connexion.php');
$_SESSION['current_url'] = current_url();

// Paramètres de filtrage uniquement (le tri et la recherche seront gérés en JS)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$displayed_users = 0;

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
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

<body class="<?php echo $theme; ?>-theme">

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <!-- En-tête avec recherche et navigation -->
                    <div class="header">
                        <div class="search-bar">
                            <input type="text" placeholder="Chercher un voyageur" id="search">
                            <button type="button" style="background: none; border: none; cursor: pointer;">
                                <img src="../img/svg/loupe.svg" alt="loupe">
                            </button>
                        </div>

                        <a href="../index.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="fleche">
                        </a>
                    </div>

                    <div class="main-content">
                        <!-- Titre et compteur -->
                        <div class="titre-content">
                            <span>Voyageurs</span>
                            <span id="voyage-count"></span>
                        </div>

                        <!-- Barre de filtres et options de tri -->
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
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Message de recherche sans résultats (caché par défaut) -->
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Afficher le nombre d'utilisateurs
            const userCount = document.querySelectorAll('tbody tr').length;
            document.getElementById('voyage-count').textContent = userCount + ' voyageurs';

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
                    }, 1000);
                });
            });

            // Fonction pour afficher les notifications
            function showNotification(message, type = 'success') {
                // Supprimer une notification existante si elle est déjà présente
                const existingNotification = document.querySelector('.notification');
                if (existingNotification) {
                    existingNotification.remove();
                }

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

            // Fonctionnalité de tri
            const sortSelect = document.getElementById('sort-select');
            sortSelect.addEventListener('change', function() {
                sortTable(this.value);
            });

            function sortTable(sortBy) {
                const tableBody = document.querySelector('.tab-voyageurs tbody');
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    switch(sortBy) {
                        case 'recent':
                            // Tri par date d'inscription (plus récent d'abord)
                            const dateA = new Date(a.querySelector('.date').textContent);
                            const dateB = new Date(b.querySelector('.date').textContent);
                            return dateB - dateA;
                        case 'oldest':
                            // Tri par date d'inscription (plus ancien d'abord)
                            const dateC = new Date(a.querySelector('.date').textContent);
                            const dateD = new Date(b.querySelector('.date').textContent);
                            return dateC - dateD;
                        case 'name-asc':
                            // Tri par nom (A-Z)
                            return a.querySelector('.nom').textContent.localeCompare(b.querySelector('.nom').textContent);
                        case 'name-desc':
                            // Tri par nom (Z-A)
                            return b.querySelector('.nom').textContent.localeCompare(a.querySelector('.nom').textContent);
                        default:
                            return 0;
                    }
                });
                
                // Vider et remplir le tableau avec les lignes triées
                tableBody.innerHTML = '';
                rows.forEach(row => tableBody.appendChild(row));
                
                // Mise à jour du compteur après le tri
                updateUserCount();
            }
            
            // Fonctionnalité de recherche
            const searchInput = document.getElementById('search');
            const searchButton = searchInput.nextElementSibling;
            const searchNoResults = document.getElementById('search-no-results');
            const searchTerm = document.getElementById('search-term');
            const resetSearch = document.getElementById('reset-search');
            
            // Fonction de recherche
            function performSearch() {
                const query = searchInput.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.tab-voyageurs tbody tr');
                let hasResults = false;
                
                rows.forEach(row => {
                    const name = row.querySelector('.nom').textContent.toLowerCase();
                    const email = row.getAttribute('data-email').toLowerCase();
                    
                    // Si la requête contient @ ou ressemble à un email, chercher dans l'email aussi
                    // Sinon chercher uniquement dans le nom
                    const matchFound = query.includes('@') 
                        ? email.includes(query) 
                        : name.includes(query);
                    
                    if (matchFound || query === '') {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Afficher le message "aucun résultat" si nécessaire
                if (!hasResults && query !== '') {
                    searchNoResults.style.display = 'flex';
                    searchTerm.textContent = query;
                } else {
                    searchNoResults.style.display = 'none';
                }
                
                // Mise à jour du compteur après la recherche
                updateUserCount();
            }
            
            // Événements de recherche
            searchInput.addEventListener('input', performSearch);
            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
            
            // Réinitialiser la recherche
            resetSearch.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            });
            
            // Fonction pour mettre à jour le compteur d'utilisateurs visibles
            function updateUserCount() {
                const visibleUsers = document.querySelectorAll('.tab-voyageurs tbody tr[style=""]').length || 
                                     document.querySelectorAll('.tab-voyageurs tbody tr:not([style*="display: none"])').length;
                document.getElementById('voyage-count').textContent = visibleUsers + ' voyageurs';
            }
            
            // Tri initial
            sortTable('recent');
        });
    </script>
</body>

</html>