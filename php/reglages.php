<?php
require('session.php');
check_auth('connexion.php');

// Initialisation des variables
$message = '';
$theme = 'dark'; // Thème par défaut
$highContrast = false;
$fontSize = 'normal';
$dyslexicFont = false;
$reduceMotion = false;

// Réinitialisation des paramètres si demandé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_settings'])) {
    // Définir les cookies avec les valeurs par défaut
    setcookie('theme', 'dark', time() + (30 * 24 * 60 * 60), '/');
    setcookie('highContrast', 'false', time() + (30 * 24 * 60 * 60), '/');
    setcookie('fontSize', 'normal', time() + (30 * 24 * 60 * 60), '/');
    setcookie('dyslexicFont', 'false', time() + (30 * 24 * 60 * 60), '/');
    setcookie('reduceMotion', 'false', time() + (30 * 24 * 60 * 60), '/');

    // Message de succès
    $message = 'Tous les paramètres ont été réinitialisés avec succès !';

    // Mettre à jour les variables pour l'affichage immédiat
    $theme = 'dark';
    $highContrast = false;
    $fontSize = 'normal';
    $dyslexicFont = false;
    $reduceMotion = false;
} else {
    // Récupération des préférences depuis les cookies
    if (isset($_COOKIE['theme'])) {
        $theme = $_COOKIE['theme'];
    }

    if (isset($_COOKIE['highContrast'])) {
        $highContrast = $_COOKIE['highContrast'] === 'true';
    }

    if (isset($_COOKIE['fontSize'])) {
        $fontSize = $_COOKIE['fontSize'];
    }

    if (isset($_COOKIE['dyslexicFont'])) {
        $dyslexicFont = $_COOKIE['dyslexicFont'] === 'true';
    }

    if (isset($_COOKIE['reduceMotion'])) {
        $reduceMotion = $_COOKIE['reduceMotion'] === 'true';
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Récupération des valeurs du formulaire
    $theme = $_POST['theme'] ?? 'dark';
    $highContrast = isset($_POST['highContrast']);
    $fontSize = $_POST['fontSize'] ?? 'normal';
    $dyslexicFont = isset($_POST['dyslexicFont']);
    $reduceMotion = isset($_POST['reduceMotion']);

    // Définition des cookies
    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), '/');
    setcookie('highContrast', $highContrast ? 'true' : 'false', time() + (30 * 24 * 60 * 60), '/');
    setcookie('fontSize', $fontSize, time() + (30 * 24 * 60 * 60), '/');
    setcookie('dyslexicFont', $dyslexicFont ? 'true' : 'false', time() + (30 * 24 * 60 * 60), '/');
    setcookie('reduceMotion', $reduceMotion ? 'true' : 'false', time() + (30 * 24 * 60 * 60), '/');

    // Message de succès
    $message = 'Vos préférences ont été enregistrées avec succès !';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Réglages</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/svg-theme-adapter.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/reglages.css">

    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <div class="settings-header">
                        <div class="settings-greeting">
                            <span class="titre">Réglages</span>
                            <p class="subtitle">Personnalisez votre expérience Marvel Travel</p>
                        </div>
                        <a href="../index.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="flèche">
                        </a>
                    </div>

                    <?php if ($message): ?>
                        <div class="notification <?= strpos($message, 'succès') !== false ? 'success' : 'error' ?>">
                            <img src="../img/svg/<?= strpos($message, 'succès') !== false ? 'check-circle.svg' : 'alert-circle.svg' ?>"
                                alt="Notification">
                            <span><?= $message ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="settings-form">
                        <div class="settings-grid">
                            <!-- Apparence -->
                            <div class="card settings-card">
                                <div class="header-text">
                                    <img src="../img/svg/eye.svg" alt="Apparence" class="header-icon">
                                    <span class="titre-card">Apparence</span>
                                </div>

                                <div class="card-content">
                                    <div class="setting-group">
                                        <div class="setting-label">Thème</div>
                                        <div class="setting-options theme-selector">
                                            <label class="theme-option <?= $theme === 'light' ? 'selected' : '' ?>">
                                                <input type="radio" name="theme" value="light" <?= $theme === 'light' ? 'checked' : '' ?>>
                                                <div class="option-icon light-icon">
                                                    <img src="../img/svg/sun.svg" alt="Mode clair" class="theme-icon">
                                                </div>
                                                <span>Clair</span>
                                            </label>

                                            <label class="theme-option <?= $theme === 'dark' ? 'selected' : '' ?>">
                                                <input type="radio" name="theme" value="dark" <?= $theme === 'dark' ? 'checked' : '' ?>>
                                                <div class="option-icon dark-icon">
                                                    <img src="../img/svg/moon.svg" alt="Mode sombre" class="theme-icon">
                                                </div>
                                                <span>Sombre</span>
                                            </label>

                                            <label class="theme-option <?= $theme === 'auto' ? 'selected' : '' ?>">
                                                <input type="radio" name="theme" value="auto" <?= $theme === 'auto' ? 'checked' : '' ?>>
                                                <div class="option-icon auto-icon">
                                                    <img src="../img/svg/settings.svg" alt="Mode automatique"
                                                        class="theme-icon">
                                                </div>
                                                <span>Auto</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Contraste élevé</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="highContrast" name="highContrast"
                                                class="toggle-input" <?= $highContrast ? 'checked' : '' ?>>
                                            <label for="highContrast" class="toggle-label"></label>
                                        </div>
                                        <div class="setting-description">
                                            Augmente le contraste pour une meilleure lisibilité
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessibilité -->
                            <div class="card settings-card">
                                <div class="header-text">
                                    <img src="../img/svg/users.svg" alt="Accessibilité" class="header-icon">
                                    <span class="titre-card">Accessibilité</span>
                                </div>

                                <div class="card-content">
                                    <div class="setting-group">
                                        <div class="setting-label">Taille du texte</div>
                                        <div class="font-size-selector">
                                            <label class="theme-option <?= $fontSize === 'normal' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="normal"
                                                    <?= $fontSize === 'normal' ? 'checked' : '' ?>>
                                                <span>A</span>
                                            </label>

                                            <label class="theme-option <?= $fontSize === 'large' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="large" <?= $fontSize === 'large' ? 'checked' : '' ?>>
                                                <span>A+</span>
                                            </label>

                                            <label class="theme-option <?= $fontSize === 'larger' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="larger"
                                                    <?= $fontSize === 'larger' ? 'checked' : '' ?>>
                                                <span>A++</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Police dyslexique</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="dyslexicFont" name="dyslexicFont"
                                                class="toggle-input" <?= $dyslexicFont ? 'checked' : '' ?>>
                                            <label for="dyslexicFont" class="toggle-label"></label>
                                        </div>
                                        <div class="setting-description">
                                            Utilise une police spéciale pour les personnes dyslexiques
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Réduire les animations</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="reduceMotion" name="reduceMotion"
                                                class="toggle-input" <?= $reduceMotion ? 'checked' : '' ?>>
                                            <label for="reduceMotion" class="toggle-label"></label>
                                        </div>
                                        <div class="setting-description">
                                            Réduit ou désactive les animations pour limiter la distraction
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Préférences avancées -->
                            <div class="card settings-card full-width">
                                <div class="header-text">
                                    <img src="../img/svg/settings.svg" alt="Préférences avancées" class="header-icon">
                                    <span class="titre-card">Préférences avancées</span>
                                </div>

                                <div class="card-content">
                                    <div class="setting-group">
                                        <div class="setting-label">Télécharger mes données</div>
                                        <button type="button" class="action-button download-button">
                                            <img src="../img/svg/download.svg" alt="Télécharger">
                                            <span>Exporter mes données</span>
                                        </button>
                                        <div class="setting-description">
                                            Téléchargez toutes vos données personnelles au format JSON
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Réinitialiser les préférences</div>
                                        <button type="button" class="action-button reset-button">
                                            <img src="../img/svg/refresh.svg" alt="Réinitialiser">
                                            <span>Rétablir les paramètres par défaut</span>
                                        </button>
                                        <div class="setting-description">
                                            Restaurer tous les paramètres à leur valeur par défaut
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="save_settings" class="save-button">
                                <img src="../img/svg/check.svg" alt="Enregistrer" class="no-invert">
                                <span>Enregistrer les modifications</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fonction simplifiée pour définir un cookie
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = name + '=' + value + ';path=/;expires=' + expires.toUTCString();
        }

        // Gestion du bouton de réinitialisation
        const resetButton = document.querySelector('.reset-button');
        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?')) {
                    // Créer un formulaire caché pour soumettre l'action de réinitialisation
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'reset_settings';
                    input.value = '1';

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Gestion des options de thème en temps réel (uniquement pour la prévisualisation)
        const themeOptions = document.querySelectorAll('input[name="theme"]');
        themeOptions.forEach(option => {
            option.addEventListener('change', function () {
                let theme = this.value;

                // Nettoyer les classes existantes
                document.body.classList.remove('light-theme', 'dark-theme', 'auto-theme');

                // Si auto, déterminer en fonction des préférences système
                if (theme === 'auto') {
                    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    theme = prefersDarkMode ? 'dark' : 'light';
                }

                // Appliquer la nouvelle classe
                document.body.classList.add(`${theme}-theme`);

                // Mettre à jour la classe 'selected' UNIQUEMENT sur les options de thème
                document.querySelectorAll('.theme-selector .theme-option').forEach(optionEl => {
                    optionEl.classList.remove('selected');
                });
                this.closest('.theme-option').classList.add('selected');
            });
        });

        // Gestion des autres options d'accessibilité pour la prévisualisation
        const highContrastToggle = document.getElementById('highContrast');
        if (highContrastToggle) {
            highContrastToggle.addEventListener('change', function () {
                document.body.classList.toggle('high-contrast', this.checked);
            });
        }

        const fontSizeOptions = document.querySelectorAll('input[name="fontSize"]');
        fontSizeOptions.forEach(option => {
            option.addEventListener('change', function () {
                // Mise à jour des classes de taille de police sur le body
                document.body.classList.remove('font-size-normal', 'font-size-large', 'font-size-larger');
                document.body.classList.add(`font-size-${this.value}`);

                // Mise à jour visuelle de la sélection active
                document.querySelectorAll('.font-size-selector .theme-option').forEach(optionEl => {
                    optionEl.classList.remove('selected');
                });
                this.closest('.theme-option').classList.add('selected');
            });
        });

        const dyslexicFontToggle = document.getElementById('dyslexicFont');
        if (dyslexicFontToggle) {
            dyslexicFontToggle.addEventListener('change', function () {
                document.body.classList.toggle('dyslexic-font', this.checked);
            });
        }

        const reduceMotionToggle = document.getElementById('reduceMotion');
        if (reduceMotionToggle) {
            reduceMotionToggle.addEventListener('change', function () {
                document.body.classList.toggle('reduce-motion', this.checked);
            });
        }
    });
</script>

</html>