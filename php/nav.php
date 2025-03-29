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

// Déterminer le chemin de base pour les URLs
$project_root = str_replace('\\', '/', dirname(dirname(__FILE__))); // Chemin absolu du projet
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path;

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
                class="menu-li <?php echo $current_page === 'destination.php' ? 'active-nav-nav' : ''; ?>">
                <li>Destinations</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/contact.php"
                class="menu-li <?php echo $current_page === 'contact.php' ? 'active-nav-nav' : ''; ?>">
                <li>Contact</li>
            </a>
            <?php
            if (isset($_SESSION['user'])) {
                // Utilisation du préfixe pour les chemins relatifs
                echo "<a href='{$base_url}/php/profil.php' class='menu-li'>
                      <img src='{$prefix_path}img/svg/spiderman-pin.svg' alt='Profil' style='width: 40px; height: 40px;'></a>";
            } else {
                echo "<a href='{$base_url}/php/connexion.php' class='nav-button'>
                      <li>Se connecter</li></a>";
            }
            ?>
        </ul>
    </div>
</header>