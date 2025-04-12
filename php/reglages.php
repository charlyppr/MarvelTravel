<?php
require('session.php');
check_auth('connexion.php');

// Récupérer les préférences utilisateur ou utiliser des valeurs par défaut
$user_prefs = [];
$prefs_file = '../json/preferences.json';

if (file_exists($prefs_file)) {
    $prefs_json = file_get_contents($prefs_file);
    $all_prefs = json_decode($prefs_json, true) ?: [];
    
    // Récupérer les préférences de l'utilisateur connecté
    foreach ($all_prefs as $pref) {
        if (isset($pref['email']) && $pref['email'] === $_SESSION['email']) {
            $user_prefs = $pref;
            break;
        }
    }
}

// Valeurs par défaut si aucune préférence n'existe
$theme = $user_prefs['theme'] ?? 'auto';
$fontSize = $user_prefs['fontSize'] ?? 'normal';
$highContrast = $user_prefs['highContrast'] ?? false;
$reduceMotion = $user_prefs['reduceMotion'] ?? false;
$dyslexicFont = $user_prefs['dyslexicFont'] ?? false;

// Traiter le formulaire si soumis
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $new_prefs = [
        'email' => $_SESSION['email'],
        'theme' => $_POST['theme'] ?? 'auto',
        'fontSize' => $_POST['fontSize'] ?? 'normal',
        'highContrast' => isset($_POST['highContrast']),
        'reduceMotion' => isset($_POST['reduceMotion']),
        'dyslexicFont' => isset($_POST['dyslexicFont']),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Mettre à jour ou ajouter les préférences dans le fichier
    $updated = false;
    if (file_exists($prefs_file)) {
        $all_prefs = json_decode(file_get_contents($prefs_file), true) ?: [];
        
        foreach ($all_prefs as $key => $pref) {
            if (isset($pref['email']) && $pref['email'] === $_SESSION['email']) {
                $all_prefs[$key] = $new_prefs;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            $all_prefs[] = $new_prefs;
        }
    } else {
        $all_prefs = [$new_prefs];
    }
    
    // Enregistrer les préférences
    if (file_put_contents($prefs_file, json_encode($all_prefs, JSON_PRETTY_PRINT))) {
        $message = 'Vos préférences ont été enregistrées avec succès.';
        
        // Mettre à jour les variables pour l'affichage
        $theme = $new_prefs['theme'];
        $fontSize = $new_prefs['fontSize'];
        $highContrast = $new_prefs['highContrast'];
        $reduceMotion = $new_prefs['reduceMotion'];
        $dyslexicFont = $new_prefs['dyslexicFont'];
    } else {
        $message = 'Une erreur est survenue lors de l\'enregistrement de vos préférences.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Réglages</title>

    <link rel="stylesheet" href="../css/root.css">
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/reglages.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?= $theme === 'dark' ? 'dark-theme' : ($theme === 'light' ? 'light-theme' : '') ?> 
            <?= $fontSize === 'large' ? 'large-text' : ($fontSize === 'larger' ? 'larger-text' : '') ?>
            <?= $highContrast ? 'high-contrast' : '' ?>
            <?= $reduceMotion ? 'reduce-motion' : '' ?>
            <?= $dyslexicFont ? 'dyslexic-font' : '' ?>">
    <div class="default"></div>

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
                            <img src="../img/svg/<?= strpos($message, 'succès') !== false ? 'check-circle.svg' : 'alert-circle.svg' ?>" alt="Notification">
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
                                                    <img src="../img/svg/sun.svg" alt="Mode clair">
                                                </div>
                                                <span>Clair</span>
                                            </label>
                                            
                                            <label class="theme-option <?= $theme === 'dark' ? 'selected' : '' ?>">
                                                <input type="radio" name="theme" value="dark" <?= $theme === 'dark' ? 'checked' : '' ?>>
                                                <div class="option-icon dark-icon">
                                                    <img src="../img/svg/moon.svg" alt="Mode sombre">
                                                </div>
                                                <span>Sombre</span>
                                            </label>
                                            
                                            <label class="theme-option <?= $theme === 'auto' ? 'selected' : '' ?>">
                                                <input type="radio" name="theme" value="auto" <?= $theme === 'auto' ? 'checked' : '' ?>>
                                                <div class="option-icon auto-icon">
                                                    <img src="../img/svg/settings.svg" alt="Mode automatique">
                                                </div>
                                                <span>Auto</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Contraste élevé</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="highContrast" name="highContrast" class="toggle-input" <?= $highContrast ? 'checked' : '' ?>>
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
                                            <label class="size-option <?= $fontSize === 'normal' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="normal" <?= $fontSize === 'normal' ? 'checked' : '' ?>>
                                                <span>A</span>
                                            </label>
                                            
                                            <label class="size-option <?= $fontSize === 'large' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="large" <?= $fontSize === 'large' ? 'checked' : '' ?>>
                                                <span>A+</span>
                                            </label>
                                            
                                            <label class="size-option <?= $fontSize === 'larger' ? 'selected' : '' ?>">
                                                <input type="radio" name="fontSize" value="larger" <?= $fontSize === 'larger' ? 'checked' : '' ?>>
                                                <span>A++</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Police dyslexique</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="dyslexicFont" name="dyslexicFont" class="toggle-input" <?= $dyslexicFont ? 'checked' : '' ?>>
                                            <label for="dyslexicFont" class="toggle-label"></label>
                                        </div>
                                        <div class="setting-description">
                                            Utilise une police spéciale pour les personnes dyslexiques
                                        </div>
                                    </div>

                                    <div class="setting-group">
                                        <div class="setting-label">Réduire les animations</div>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="reduceMotion" name="reduceMotion" class="toggle-input" <?= $reduceMotion ? 'checked' : '' ?>>
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
                                <img src="../img/svg/check.svg" alt="Enregistrer">
                                <span>Enregistrer les modifications</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/reglages.js"></script>
</body>

</html>