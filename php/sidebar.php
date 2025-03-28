<?php
// Déterminer la page active
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');

// Fonction pour vérifier si une classe est active
function isActive($page, $current_page)
{
    return $page === $current_page ? ' active-2' : '';
}
function isActiveText($page, $current_page)
{
    return $page === $current_page ? ' active-text' : '';
}

// Vérifier si l'utilisateur est sur la page administrateur
$is_admin_page = ($current_page === 'administrateur');
$is_admin_user = ($_SESSION['role'] === 'admin');
?>

<div class="sidebar">
    <div class="sidebar-content">
        <div class="sidebar-top">
            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <span class="trait"></span>

            <div class="categories">
                <?php if ($is_admin_page): ?>
                    <!-- Menu pour la page administrateur -->
                    <div class="categories-content<?php echo isActive('administrateur', $current_page); ?>">
                        <div class="categorie-img">
                            <img src="../img/svg/dashboard.svg" alt="crayon et rectangle">
                        </div>
                        <span
                            class="categorie-text<?php echo isActiveText('administrateur', $current_page); ?>">Dashboard</span>
                    </div>
                <?php else: ?>
                    <!-- Menu pour les pages utilisateur -->
                    <a href="profil.php" class="categories-content<?php echo isActive('profil', $current_page); ?>">
                        <div class="categorie-img">
                            <svg width="16" height="18" viewBox="0 0 163 175" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.9736 174.814H144.868C156.106 174.814 162.842 169.366 162.842 160.373C162.842 135.709 131.137 101.903 81.4209 101.903C31.6678 101.903 0 135.709 0 160.373C0 169.366 6.73547 174.814 17.9736 174.814ZM81.458 84.9999C102.929 84.9999 120.494 66.0819 120.494 42.0149C120.494 18.6194 102.743 0 81.458 0C60.1725 0 42.4594 18.8433 42.4594 42.1269C42.4594 66.0819 60.0237 84.9999 81.458 84.9999Z"
                                    fill="<?php echo ($current_page === 'profil') ? '#0D0D0D' : '#FFFAE7'; ?>" />
                            </svg>
                        </div>
                        <span class="categorie-text<?php echo isActiveText('profil', $current_page); ?>">Profil</span>
                    </a>
                <?php endif; ?>

                <div class="categories-content">
                    <div class="categorie-img">
                        <img src="../img/svg/notif.svg" alt="cloche">
                    </div>
                    <span class="categorie-text">Notifications</span>
                </div>

                <?php if (!$is_admin_page): ?>
                    <a href='mes-voyages.php'
                        class="categories-content<?php echo isActive('mes-voyages', $current_page); ?>">
                        <div class="categorie-img">
                            <img src="../img/svg/globe.svg" alt="globe" <?php echo ($current_page === 'mes-voyages') ? 'class="active-img"' : ''; ?>>
                        </div>
                        <span class="categorie-text<?php echo isActiveText('mes-voyages', $current_page); ?>">Mes
                            voyages</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar-bottom-deco">
            <div class="sidebar-bottom">
                <img class="photo-admin" src="../img/svg/spiderman-pin.svg" alt="spiderman-pin">

                <div class="info-admin">
                    <span class="nom-admin">
                        <?php echo $_SESSION['first_name'] ?>
                        <?php echo $_SESSION['last_name'] ?>
                    </span>
                    <span class="mail-admin"><?php echo $_SESSION['email'] ?></span>
                </div>
            </div>

            <div class="deconnexion">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M7.91341 15.4917C12.284 15.4917 15.8298 12.1336 15.8298 7.99722C15.8298 3.85804 12.284 0.5 7.91341 0.5C3.54283 0.5 0 3.85804 0 7.99722C0 12.1336 3.54283 15.4917 7.91341 15.4917ZM7.91341 13.8098C4.52026 13.8098 1.77582 11.2107 1.77582 7.99722C1.77582 4.78373 4.52026 2.1818 7.91341 2.1818C11.3065 2.1818 14.051 4.78373 14.051 7.99722C14.051 11.2107 11.3065 13.8098 7.91341 13.8098Z"
                        fill="#FFFAE7" />
                    <path
                        d="M5.10112 8.73108H10.7191C11.1829 8.73108 11.5176 8.44474 11.5176 8.01389C11.5176 7.57748 11.1947 7.28558 10.7191 7.28558H5.10112C4.62855 7.28558 4.30273 7.57748 4.30273 8.01389C4.30273 8.44474 4.63735 8.73108 5.10112 8.73108Z"
                        fill="#FFFAE7" />
                </svg>
                <a href="javascript:void(0)" id="logout-button">Se déconnecter</a>
            </div>

            <?php
            if ($_SESSION['role'] == 'admin' && $current_page !== 'administrateur') {
                echo "<div class='deconnexion'>
                        <a href='administrateur.php'>Administrateur</a>
                      </div>";
            }
            ?>

            <?php if (!$is_admin_page): ?>
                <?php if (!$is_admin_user): // Afficher le bouton de suppression uniquement pour les non-admins ?>
                    <div class="deconnexion delete-account">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.4 0.75H9.6C10.59 0.75 11.25 1.41 11.25 2.4V3.79H4.75V2.4C4.75 1.41 5.41 0.75 6.4 0.75ZM3.01 3.79V2.4C3.01 0.48 4.48 0 6.4 0H9.6C11.52 0 12.99 0.98 12.99 2.4V3.79H14.65C15.17 3.79 15.59 4.21 15.59 4.73C15.59 5.25 15.17 5.67 14.65 5.67H13.87L13.34 13.14C13.26 14.85 12.99 16 10.53 16H5.46C3 16 2.73 14.85 2.65 13.14L2.13 5.67H1.34C0.82 5.67 0.4 5.25 0.4 4.73C0.4 4.21 0.82 3.79 1.34 3.79H3.01ZM3.89 5.67L4.4 13.04C4.45 14.06 4.6 14.33 5.46 14.33H10.53C11.39 14.33 11.54 14.06 11.59 13.04L12.11 5.67H3.89ZM6.4 10.96C5.94 10.96 5.57 10.59 5.57 10.13V8.25C5.57 7.79 5.94 7.42 6.4 7.42C6.86 7.42 7.24 7.79 7.24 8.25V10.13C7.24 10.59 6.86 10.96 6.4 10.96ZM9.6 10.96C9.14 10.96 8.76 10.59 8.76 10.13V8.25C8.76 7.79 9.14 7.42 9.6 7.42C10.06 7.42 10.43 7.79 10.43 8.25V10.13C10.43 10.59 10.06 10.96 9.6 10.96Z"
                                fill="#E23636" />
                        </svg>
                        <a href="javascript:void(0)" id="delete-account-button">Supprimer mon compte</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>