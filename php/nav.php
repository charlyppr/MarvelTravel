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
function getAssetPath($file) {
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

    <div class="menu">
        <ul>
            <a href="<?php echo $base_url; ?>/index.php"
                class="menu-li <?php echo $current_page === 'index.php' ? 'active-nav' : ''; ?>">
                <li>Accueil</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/destination.php"
                class="menu-li <?php echo $current_page === 'destination.php' ? 'active-nav' : ''; ?>">
                <li>Destinations</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/contact.php"
                class="menu-li <?php echo $current_page === 'contact.php' ? 'active-nav' : ''; ?>">
                <li>Contact</li>
            </a>
            <div class="nav-cart">
                <a href="<?php echo $base_url; ?>/php/panier.php" class="cart-icon">
                    <img src="<?php echo getAssetPath('svg/cart.svg'); ?>" alt="Panier">
                    <?php
                    // N'afficher le compteur que si l'utilisateur est connecté
                    if (isset($_SESSION['user'])) {
                        $panier_path = $project_root . '/json/panier.json';
                        if (file_exists($panier_path)) {
                            $panierJson = file_get_contents($panier_path);
                            if ($panierJson !== false) {
                                $panier = json_decode($panierJson, true);
                                if (isset($panier['items']) && count($panier['items']) > 0) {
                                    echo '<span class="cart-count">' . count($panier['items']) . '</span>';
                                }
                            }
                        }
                    }
                    ?>
                </a>
            </div>
            <?php
            if (isset($_SESSION['user'])) {
                // Utilisation du préfixe pour les chemins relatifs
                echo "<a href='{$base_url}/php/profil.php' class='menu-li'>
                      <img src='" . getAssetPath('svg/spiderman-pin.svg') . "' alt='Profil' style='width: 40px; height: 40px;'></a>";
            } else {
                echo "<a href='{$base_url}/php/connexion.php' class='nav-button'>
                      <li>Se connecter</li></a>";
            }
            ?>
        </ul>
    </div>
</header>