<?php
// Si la session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détermination de la page active
$current_page = basename($_SERVER['PHP_SELF']);

// Déterminer si on est dans un sous-dossier de php
$current_dir = dirname($_SERVER['PHP_SELF']);
$is_in_root = $current_dir == '/' . basename(dirname(dirname(__FILE__)));
$is_in_subdirectory = strpos($current_dir, '/' . basename(dirname(dirname(__FILE__))) . '/php/') === 0;

// Amélioration de la détection du chemin de base sans dépendre du nom du dossier
$script_filename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$project_root = str_replace('\\', '/', dirname(dirname(__FILE__))); // Chemin absolu du projet

// Obtenir le chemin relatif depuis la racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

// Construction de l'URL de base
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path;

// Fonction helper pour générer les chemins d'assets
function getAssetPath($file)
{
    global $project_root, $script_filename;

    // Obtenir le chemin du script courant par rapport à la racine du projet
    $current_script = str_replace('\\', '/', dirname($script_filename));
    $depth = substr_count(substr($current_script, strlen($project_root)), '/');

    // Construire le chemin relatif en fonction de la profondeur
    return str_repeat('../', $depth) . "img/" . $file;
}

// Chemins relatifs pour les ressources statiques et les liens
// Détermine la profondeur du sous-dossier pour ajuster le préfixe
$prefix_path = '';
if ($is_in_root) {
    $prefix_path = '';
} else if ($is_in_subdirectory) {
    // Compter combien de niveaux sous php/ nous sommes
    $path_parts = explode('/', trim($current_dir, '/'));
    $php_index = array_search('php', $path_parts);
    if ($php_index !== false) {
        $sub_levels = count($path_parts) - $php_index - 1;
        $prefix_path = str_repeat('../', $sub_levels + 1);
    } else {
        $prefix_path = '../';
    }
} else {
    $prefix_path = '../';
}
?>

<header class="nav">
    <a href="<?php echo $base_url; ?>/index.php" class="logo-container">
        <div class="logo-gauche">
            <span class="logo mar">MAR</span>
            <span class="logo tra">TRA</span>
        </div>
        <span class="logo vel">VEL</span>
    </a>

    <!-- Add hamburger button for mobile -->
    <div class="hamburger-button">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="menu">
        <ul>
            <a href="<?php echo $base_url; ?>/index.php"
                class="menu-li <?php echo $current_page === 'index.php' ? 'active-nav' : ''; ?>">
                <li>Accueil</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/destination.php?category=all"
                class="menu-li <?php echo $current_page === 'destination.php' ? 'active-nav' : ''; ?>">
                <li>Destinations</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/contact.php"
                class="menu-li <?php echo $current_page === 'contact.php' ? 'active-nav' : ''; ?>">
                <li>Contact</li>
            </a>

            <?php if (!isset($_SESSION['user'])) { ?>
                <div class="theme-toggle-nav">
                    <div class="theme-icons">
                        <div id="sunIcon" class="theme-icon-wrapper <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ? 'active' : ''; ?>">
                            <img src="<?php echo getAssetPath('svg/sun.svg'); ?>" alt="Mode clair" class="theme-icon">
                        </div>
                        <div id="moonIcon" class="theme-icon-wrapper <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ? '' : 'active'; ?>">
                            <img src="<?php echo getAssetPath('svg/moon.svg'); ?>" alt="Mode sombre" class="theme-icon">
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php
            if (isset($_SESSION['user'])) {
                ?>
                <div class="profile-dropdown-container">
                    <a href="<?php echo $base_url; ?>/php/profil.php" class="profile-icon-container">
                        <img src="<?php echo getAssetPath('svg/spiderman-pin.svg'); ?>" alt="Profil" class="profile-icon">
                        <?php
                        // Afficher le badge de notification du panier s'il y a des articles
                        $panier_path = $project_root . '/json/panier.json';
                        $has_items = false;
                        $items_count = 0;

                        if (file_exists($panier_path)) {
                            $panierJson = file_get_contents($panier_path);
                            if ($panierJson !== false) {
                                $panier = json_decode($panierJson, true);
                                $userEmail = $_SESSION['email'];
                                if (isset($panier[$userEmail]) && isset($panier[$userEmail]['items']) && count($panier[$userEmail]['items']) > 0) {
                                    $has_items = true;
                                    $items_count = count($panier[$userEmail]['items']);
                                    echo '<span class="cart-count">' . $items_count . '</span>';
                                }
                            }
                        }
                        ?>
                    </a>
                    <div class="profile-dropdown" style="display: none;">
                        <div class="dropdown-item theme-toggle-container">
                            <span id="themeText">Thème</span>
                            <div class="theme-icons">
                                <div id="sunIcon"
                                    class="theme-icon-wrapper <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ? 'active' : ''; ?>">
                                    <img src="<?php echo getAssetPath('svg/sun.svg'); ?>" alt="Mode clair"
                                        class="theme-icon">
                                </div>
                                <div id="moonIcon"
                                    class="theme-icon-wrapper <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light') ? '' : 'active'; ?>">
                                    <img src="<?php echo getAssetPath('svg/moon.svg'); ?>" alt="Mode sombre"
                                        class="theme-icon">
                                </div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="<?php echo $base_url; ?>/php/profil.php" class="dropdown-item">
                            <img src="<?php echo getAssetPath('svg/users.svg'); ?>" alt="Profil">
                            <span>Profil</span>
                        </a>
                        <a href="<?php echo $base_url; ?>/php/panier.php" class="dropdown-item">
                            <img src="<?php echo getAssetPath('svg/cart.svg'); ?>" alt="Panier">
                            <span>Panier<?php echo $has_items ? ' (' . $items_count . ')' : ''; ?></span>
                        </a>
                        <a href="<?php echo $base_url; ?>/php/reglages.php" class="dropdown-item">
                            <img src="<?php echo getAssetPath('svg/settings.svg'); ?>" alt="Réglages">
                            <span>Réglages</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <a href="#" id="nav-logout-button" class="dropdown-item logout">
                            <img src="<?php echo getAssetPath('svg/log-out.svg'); ?>" alt="Déconnexion">
                            <span>Déconnexion</span>
                        </a>
                    </div>
                </div>
                <?php
            } else {
                echo "<a href='{$base_url}/php/connexion.php' class='nav-button'>
                      <li>Se connecter</li></a>";
            }
            ?>
        </ul>
    </div>
</header>

<!-- Add overlay for mobile menu -->
<div class="menu-overlay"></div>

<div id="nav-logout-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmation de déconnexion</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
        </div>
        <div class="modal-footer">
            <button id="cancel-nav-logout" class="btn-secondary">Annuler</button>
            <a href="<?php echo $base_url; ?>/php/logout.php" class="btn-primary">Se déconnecter</a>
        </div>
    </div>
</div>

<script src="<?php echo $prefix_path; ?>js/theme-loader.js"></script>
<script src="<?php echo $prefix_path; ?>js/nav.js"></script>