<?php
// Déterminer la page active
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');

// Vérifier si l'utilisateur est sur la page administrateur
$is_admin_page = (strpos($current_page, 'admin') !== false);
$is_admin_user = ($_SESSION['role'] === 'admin');

// Fonctions d'aide pour les classes CSS
function isActive($page, $current_page)
{
    return $page === $current_page ? 'active' : '';
}
?>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<button class="mobile-toggle" id="mobile-toggle" aria-label="Menu">
    <span></span>
</button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-section sidebar-nav">
            <h3 class="sidebar-heading">Navigation</h3>

            <nav class="nav-menu">
                <?php if ($is_admin_page): ?>
                    <a href="administrateur.php" class="nav-link <?= isActive('administrateur', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/dashboard.svg" alt="Dashboard" class="icon-img">
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>

                    <!-- <a href="admin-statistiques.php" class="nav-link <?= isActive('admin-statistiques', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/chart-bar.svg" alt="Statistiques" class="icon-img">
                            <div class="icon-highlight"></div>
                        </div>
                        <span class="nav-text">Statistiques</span>
                    </a> -->
                <?php else: ?>
                    <a href="profil.php" class="nav-link <?= isActive('profil', $current_page) ?>">
                        <div class="nav-icon">
                            <img src="../img/svg/person.svg" alt="Personne" class="icon-img">
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

        <?php if ($is_admin_page): ?>
            <div class="sidebar-section sidebar-quick-links">
                <h3 class="sidebar-heading">Raccourcis admin</h3>

                <div class="nav-menu">
                    <a href="admin-nouvel-utilisateur.php" class="quick-link <?= isActive('admin-nouvel-utilisateur', $current_page) ?>">
                        <div class="quick-link-icon">
                            <img src="../img/svg/user-plus.svg" alt="Ajouter utilisateur">
                        </div>
                        <span class="quick-link-text">Nouvel utilisateur</span>
                    </a>

                    <a href="admin-nouvelle-destination.php" class="quick-link <?= isActive('admin-nouvelle-destination', $current_page) ?>">
                        <div class="quick-link-icon">
                            <img src="../img/svg/map-pin.svg" alt="Nouvelle destination">
                        </div>
                        <span class="quick-link-text">Nouvelle destination</span>
                    </a>
                </div>
            </div>
        <?php elseif (!$is_admin_page): ?>
            <div class="sidebar-section sidebar-quick-links">
                <h3 class="sidebar-heading">Raccourcis</h3>

                <div class="nav-menu">
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
                        <img src="../img/svg/spiderman-pin.svg" alt="Photo de profil" class="no-invert">
                    </div>
                    <div class="user-status online"></div>
                </div>

                <div class="user-info">
                    <div class="user-name"><?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?></div>
                    <div class="user-email"><?= $_SESSION['email'] ?></div>
                </div>
            </div>

            <div class="user-actions">
                <?php if ($is_admin_page): ?>
                    <a href="profil.php" class="user-action admin-link">
                        <img src="../img/svg/person.svg" alt="Profil">
                        <span>Retour au profil</span>
                    </a>
                <?php elseif ($_SESSION['role'] == 'admin' && $current_page !== 'administrateur'): ?>
                    <a href="administrateur.php" class="user-action admin-link">
                        <img src="../img/svg/settings.svg" alt="Admin">
                        <span>Administration</span>
                    </a>
                <?php endif; ?>

                <button class="user-action logout-action" id="logout-button">
                    <img src="../img/svg/log-out.svg" alt="Déconnexion">
                    <span>Se déconnecter</span>
                </button>

                <?php if (!$is_admin_page && !$is_admin_user): ?>
                    <button class="user-action delete-action" id="delete-account-button">
                        <img src="../img/svg/trash.svg" alt="Supprimer" class="no-invert">
                        <span>Supprimer mon compte</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-brand">
            <img src="../img/svg/spiderman-pin.svg" alt="Marvel Travel" class="brand-icon no-invert">
            <span class="brand-text">© Marvel Travel 2025</span>
        </div>
    </div>
</div>

<div id="logout-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmation de déconnexion</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
        </div>
        <div class="modal-footer">
            <button id="cancel-logout" class="btn-secondary">Annuler</button>
            <a href="logout.php" class="btn-primary">Se déconnecter</a>
        </div>
    </div>
</div>

<div id="delete-account-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmation de suppression</h3>
            <span class="close-modal-delete">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer définitivement votre compte ?</p>
            <p class="warning-text">Cette action est irréversible et toutes vos données seront perdues.</p>
        </div>
        <div class="modal-footer">
            <button id="cancel-delete" class="btn-secondary">Annuler</button>
            <a href="delete-account.php" class="btn-danger">Supprimer mon compte</a>
        </div>
    </div>
</div>

<script src="../js/sidebar.js" defer></script>