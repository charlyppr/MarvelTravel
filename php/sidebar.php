<?php
// Déterminer la page active
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');

// Vérifier si l'utilisateur est sur la page administrateur
$is_admin_page = ($current_page === 'administrateur');
$is_admin_user = ($_SESSION['role'] === 'admin');

// Fonctions d'aide pour les classes CSS
function isActive($page, $current_page)
{
    return $page === $current_page ? 'active' : '';
}
?>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>
        <button class="mobile-toggle" id="mobile-toggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-section sidebar-nav">
            <h3 class="sidebar-heading">Navigation</h3>

            <nav class="nav-menu">
                <?php if ($is_admin_page): ?>
                    <!-- Menu pour la page administrateur -->
                    <a href="administrateur.php" class="nav-link <?= isActive('administrateur', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/dashboard.svg" alt="Dashboard" class="icon-img">
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                <?php else: ?>
                    <!-- Menu pour les pages utilisateur -->
                    <a href="profil.php" class="nav-link <?= isActive('profil', $current_page) ?>">
                        <div class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 163 175" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="icon-img">
                                <path
                                    d="M17.9736 174.814H144.868C156.106 174.814 162.842 169.366 162.842 160.373C162.842 135.709 131.137 101.903 81.4209 101.903C31.6678 101.903 0 135.709 0 160.373C0 169.366 6.73547 174.814 17.9736 174.814ZM81.458 84.9999C102.929 84.9999 120.494 66.0819 120.494 42.0149C120.494 18.6194 102.743 0 81.458 0C60.1725 0 42.4594 18.8433 42.4594 42.1269C42.4594 66.0819 60.0237 84.9999 81.458 84.9999Z"
                                    fill="currentColor" />
                            </svg>
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Profil</span>

                    </a>

                    <a href="mes-voyages.php" class="nav-link <?= isActive('mes-voyages', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/globe.svg" alt="Mes voyages" class="icon-img">
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Mes voyages</span>

                    </a>

                    <a href="reglages.php" class="nav-link <?= isActive('reglages', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/settings.svg" alt="Réglages" class="icon-img">
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Réglages</span>
                    </a>

                <?php endif; ?>
            </nav>
        </div>

        <?php if (!$is_admin_page): ?>
            <div class="sidebar-section sidebar-quick-links">
                <h3 class="sidebar-heading">Raccourcis</h3>

                <div class="quick-links">
                    <a href="../php/destination.php" class="quick-link">
                        <div class="quick-link-icon">
                            <img src="../img/svg/plane.svg" alt="Réserver">
                        </div>
                        <span class="quick-link-text">Réserver un voyage</span>
                    </a>

                    <a href="../php/contact.php" class="quick-link">
                        <div class="quick-link-icon">
                            <img src="../img/svg/help-circle.svg" alt="Support">
                        </div>
                        <span class="quick-link-text">Support & Aide</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="sidebar-section sidebar-user">
            <div class="user-profile">
                <div class="avatar-container">
                    <div class="user-avatar">
                        <img src="../img/svg/spiderman-pin.svg" alt="Photo de profil">
                    </div>
                    <div class="user-status online"></div>
                </div>

                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?></div>
                    <div class="user-email"><?= $_SESSION['email'] ?></div>
                </div>
            </div>

            <div class="user-actions">
                <?php if ($_SESSION['role'] == 'admin' && $current_page !== 'administrateur'): ?>
                    <a href="administrateur.php" class="user-action admin-link">
                        <img src="../img/svg/settings.svg" alt="Admin">
                        <span>Administration</span>
                    </a>
                <?php endif; ?>

                <button class="user-action logout-action" id="logout-button">
                    <img src="../img/svg/log-out.svg" alt="Déconnexion">
                    <span><a href='logout.php'>Se déconnecter</a></span>
                </button>

                <?php if (!$is_admin_page && !$is_admin_user): ?>
                    <button class="user-action delete-action" id="delete-account-button">
                        <img src="../img/svg/trash.svg" alt="Supprimer">
                        <span>Supprimer mon compte</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-brand">
            <img src="../img/svg/spiderman-pin.svg" alt="Marvel Travel" class="brand-icon">
            <span class="brand-text">© Marvel Travel 2025</span>
        </div>
    </div>
</div>