<?php
// Si la session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détermination de la page active
$current_page = basename($_SERVER['PHP_SELF']);
$is_in_root = dirname($_SERVER['PHP_SELF']) == '/' . basename(dirname(dirname(__FILE__)));

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
$prefix_path = $is_in_root ? '' : '../';
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
                class="menu-li <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <li>Accueil</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/destination.php"
                class="menu-li <?php echo $current_page === 'destination.php' ? 'active' : ''; ?>">
                <li>Destinations</li>
            </a>
            <a href="<?php echo $base_url; ?>/php/contact.php"
                class="menu-li <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>">
                <li>Contact</li>
            </a>
            <?php
            if (isset($_SESSION['user'])) {
                // Utilisation du préfixe pour les chemins relatifs
                echo "<a href='{$prefix_path}php/profil.php' class='menu-li'>
                      <img src='{$prefix_path}img/svg/spiderman-pin.svg' alt='Profil' class='profil-icon'></a>";
            } else {
                echo "<a href='{$prefix_path}php/connexion.php' class='nav-button'>
                      <li>Se connecter</li></a>";
            }
            ?>
        </ul>
    </div>
</header>