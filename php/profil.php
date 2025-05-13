<?php
require('session.php');
check_auth('connexion.php');

// Récupérer les données des commandes
$json_file_path = '../json/commandes.json';
$commandes_utilisateur = [];
$total_commandes = 0;
$montant_total = 0;
$destinations_visitees = [];

try {
    if (file_exists($json_file_path)) {
        $json_file = file_get_contents($json_file_path);
        $data = json_decode($json_file, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur lors du décodage JSON: " . json_last_error_msg());
        }

        if (is_array($data)) {
            foreach ($data as $cmd) {
                if ($cmd['acheteur'] == $_SESSION['email']) {
                    // Vérifier que les champs requis existent
                    if (
                        !isset($cmd['transaction']) || !isset($cmd['voyage']) ||
                        !isset($cmd['date_debut']) || !isset($cmd['date_fin']) ||
                        !isset($cmd['montant']) || !isset($cmd['status'])
                    ) {
                        continue;
                    }

                    $commandes_utilisateur[] = $cmd;
                    $total_commandes++;
                    $montant_total += $cmd['montant'];
                    if (!in_array($cmd['voyage'], $destinations_visitees)) {
                        $destinations_visitees[] = $cmd['voyage'];
                    }
                }
            }
        }
    }

} catch (Exception $e) {
    $commandes_utilisateur = [];
    $total_commandes = 0;
    $montant_total = 0;
    $destinations_visitees = [];
}

// Après la récupération des commandes et avant l'affichage HTML, ajouter le tri :
if (count($commandes_utilisateur) > 0) {
    // Trier les commandes par date de création (du plus récent au plus ancien)
    usort($commandes_utilisateur, function ($a, $b) {
        // Si la commande a un champ 'created_at', utilisez-le
        if (isset($a['date_achat']) && isset($b['date_achat'])) {
            return strtotime($b['date_achat']) - strtotime($a['date_achat']);
        }
    });
}

// Vérifier s'il y a des messages de succès ou d'erreur à afficher
$success_message = '';
$error_message = '';

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Récupérer les messages de l'utilisateur
$messages = [];
$messages_file = '../json/messages.json';
if (file_exists($messages_file)) {
    $messages_json = file_get_contents($messages_file);
    $all_messages = json_decode($messages_json, true) ?: [];

    // Filtrer les messages pour ne garder que ceux de l'utilisateur connecté
    $messages = array_filter($all_messages, function ($msg) {
        return $msg['email'] === $_SESSION['email'];
    });

    // Trier les messages par date (du plus récent au plus ancien)
    usort($messages, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Profil</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/profil.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    
    <script src="../js/form-validation.js"></script>
    <script src="../js/profil.js" defer></script>
</head>

<body class="<?php echo $theme; ?>-theme">

    <?php if (!empty($success_message)): ?>
        <div class="notification success">
            <img src="../img/svg/check-circle.svg" alt="Succès">
            <p><?= $success_message ?></p>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="notification error">
            <img src="../img/svg/alert-circle.svg" alt="Erreur">
            <p><?= $error_message ?></p>
            <button class="close-notification">&times;</button>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <?php include 'sidebar.php'; ?>

        <div class="content-container-div">
            <div class="content-container">
                <div class="content">
                    <div class="profile-header">
                        <div class="profile-greeting">
                            <span class="titre">Bonjour, <?php echo $_SESSION['first_name']; ?></span>
                            <p class="subtitle">Bienvenue sur votre espace personnel</p>
                        </div>
                        <a href="destination.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="flèche">
                        </a>
                    </div>

                    <div class="dashboard-grid">
                        <div class="card profile-card">
                            <div class="header-text">
                                <img src="../img/svg/users.svg" alt="Profil" class="header-icon">
                                <span class="titre-card">Mon profil</span>
                            </div>

                            <div class="card-content">
                                <form id="profileForm" method="post" action="javascript:void(0);">
                                    <div class="profile-header-info">
                                        <div class="profile-avatar">
                                            <img src="../img/svg/spiderman-pin.svg" alt="photo de profil"
                                                class="no-invert">
                                            <div class="profile-status online"></div>
                                        </div>
                                        <div class="profile-name">
                                            <h2><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></h2>
                                            <span class="profile-email"><?= $_SESSION['email'] ?></span>
                                        </div>
                                    </div>

                                    <div class="separator"></div>

                                    <div class="profile-details">
                                        <div class="profile-field">
                                            <div class="field-label">Prénom</div>
                                            <div class="field-value">
                                                <div class="input-wrapper">
                                                    <input type="text" name="first_name" id="first_name"
                                                        class="profile-input" value="<?= $_SESSION['first_name'] ?>"
                                                        data-original-value="<?= $_SESSION['first_name'] ?>" disabled autocomplete="given-name">
                                                    <span class="input-highlight"></span>
                                                </div>
                                                <div class="field-actions">
                                                    <button type="button" class="field-edit" data-field="first_name">
                                                        <img src="../img/svg/edit.svg" alt="Modifier">
                                                    </button>
                                                    <button type="button" class="field-validate" data-field="first_name"
                                                        style="display:none">
                                                        <img src="../img/svg/check.svg" alt="Valider">
                                                    </button>
                                                    <button type="button" class="field-cancel" data-field="first_name"
                                                        style="display:none">
                                                        <img src="../img/svg/x.svg" alt="Annuler">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="profile-field">
                                            <div class="field-label">Nom</div>
                                            <div class="field-value">
                                                <div class="input-wrapper">
                                                    <input type="text" name="last_name" id="last_name"
                                                        class="profile-input" value="<?= $_SESSION['last_name'] ?>"
                                                        data-original-value="<?= $_SESSION['last_name'] ?>" disabled autocomplete="family-name">
                                                    <span class="input-highlight"></span>
                                                </div>
                                                <div class="field-actions">
                                                    <button type="button" class="field-edit" data-field="last_name">
                                                        <img src="../img/svg/edit.svg" alt="Modifier">
                                                    </button>
                                                    <button type="button" class="field-validate" data-field="last_name"
                                                        style="display:none">
                                                        <img src="../img/svg/check.svg" alt="Valider">
                                                    </button>
                                                    <button type="button" class="field-cancel" data-field="last_name"
                                                        style="display:none">
                                                        <img src="../img/svg/x.svg" alt="Annuler">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="profile-field">
                                            <div class="field-label">Email</div>
                                            <div class="field-value">
                                                <div class="input-wrapper">
                                                    <input type="email" name="email" id="email" class="profile-input"
                                                        value="<?= $_SESSION['email'] ?>"
                                                        data-original-value="<?= $_SESSION['email'] ?>" disabled autocomplete="email">
                                                    <span class="input-highlight"></span>
                                                </div>
                                                <div class="field-actions">
                                                    <button type="button" class="field-edit" data-field="email">
                                                        <img src="../img/svg/edit.svg" alt="Modifier">
                                                    </button>
                                                    <button type="button" class="field-validate" data-field="email"
                                                        style="display:none">
                                                        <img src="../img/svg/check.svg" alt="Valider">
                                                    </button>
                                                    <button type="button" class="field-cancel" data-field="email"
                                                        style="display:none">
                                                        <img src="../img/svg/x.svg" alt="Annuler">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="profile-field">
                                            <div class="field-label">Mot de passe</div>
                                            <div class="field-value password-field-container">
                                                <div class="input-wrapper">
                                                    <input type="password" name="password" id="password"
                                                        class="profile-input" placeholder="••••••••"
                                                        data-original-value="" disabled>
                                                    <span class="input-highlight"></span>
                                                </div>
                                                <div class="field-actions">
                                                    <button type="button" class="field-edit" data-field="password">
                                                        <img src="../img/svg/edit.svg" alt="Modifier">
                                                    </button>
                                                    <button type="button" class="password-toggle" id="password-toggle" style="display:none">
                                                        <img src="../img/svg/eye.svg" alt="Afficher" id="toggle-icon">
                                                    </button>
                                                    <button type="button" class="field-validate" data-field="password"
                                                        style="display:none">
                                                        <img src="../img/svg/check.svg" alt="Valider">
                                                    </button>
                                                    <button type="button" class="field-cancel" data-field="password"
                                                        style="display:none">
                                                        <img src="../img/svg/x.svg" alt="Annuler">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="profile-action-container">
                                        <button type="submit" id="submit-profile-btn" class="profile-submit-button"
                                            style="display:none">
                                            <img src="../img/svg/save.svg" alt="Soumettre" class="button-icon">
                                            <span>Sauvegarder les modifications</span>
                                        </button>
                                        <button type="button" id="cancel-all-btn" class="profile-cancel-button"
                                            style="display:none">
                                            <img src="../img/svg/x.svg" alt="Annuler" class="button-icon">
                                            <span>Annuler tout</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card stats-card">
                            <div class="header-text">
                                <img src="../img/svg/chart.svg" alt="Statistiques" class="header-icon">
                                <span class="titre-card">Statistiques</span>
                            </div>

                            <div class="card-content stats-grid">
                                <div class="stat-item">
                                    <div class="stat-value"><?= $total_commandes ?></div>
                                    <div class="stat-label">Voyages réservés</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?= count($destinations_visitees) ?></div>
                                    <div class="stat-label">Destinations</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?= number_format($montant_total, 0, ',', ' ') ?> €</div>
                                    <div class="stat-label">Dépensés</div>
                                </div>
                            </div>
                        </div>

                        <div class="card voyages-card">
                            <div class="header-text">
                                <img src="../img/svg/globe.svg" alt="Voyages" class="header-icon">
                                <span class="titre-card">Derniers voyages</span>
                            </div>

                            <div class="card-content">
                                <div class="voyages-list">
                                    <?php if (count($commandes_utilisateur) > 0): ?>
                                        <?php
                                        $count = 0;
                                        foreach ($commandes_utilisateur as $commande):
                                            if ($count >= 3)
                                                break; // Limitation à 3 voyages
                                            $count++;

                                            // Formatage des dates
                                            $date_debut = date('d/m/Y', strtotime($commande['date_debut']));
                                            $date_fin = date('d/m/Y', strtotime($commande['date_fin']));

                                            // Déterminer le statut
                                            $statut_class = ($commande['status'] == 'accepted') ? 'status-success' : 'status-pending';
                                            $statut_text = ($commande['status'] == 'accepted') ? 'Confirmé' : 'En attente';
                                            ?>
                                            <a href="commande.php?transaction=<?= $commande['transaction'] ?>"
                                                class="voyage-item">
                                                <div class="voyage-info">
                                                    <div class="voyage-destination"><?= $commande['voyage'] ?></div>
                                                    <div class="voyage-dates"><?= $date_debut ?> au <?= $date_fin ?></div>
                                                </div>
                                                <div class="voyage-meta">
                                                    <div class="voyage-status <?= $statut_class ?>"><?= $statut_text ?></div>
                                                    <div class="voyage-price">
                                                        <?= number_format($commande['montant'], 0, ',', ' ') ?> €
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>

                                        <?php if ($total_commandes > 3): ?>
                                            <a href="mes-voyages.php" class="voir-plus-btn">
                                                <span>Voir tous mes voyages</span>
                                                <img src="../img/svg/fleche-droite.svg" alt="Voir plus" class="no-invert">
                                            </a>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <div class="empty-state">
                                            <img src="../img/svg/empty-voyages.svg" alt="Aucun voyage">
                                            <p>Vous n'avez pas encore de voyages</p>
                                            <a href="destination.php" class="action-button">Découvrir des destinations</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card messages-card">
                            <div class="header-text">
                                <img src="../img/svg/message.svg" alt="Messages" class="header-icon">
                                <span class="titre-card">Messages</span>
                            </div>

                            <div class="card-content">
                                <?php if (!empty($messages)): ?>
                                    <div class="messages-list">
                                        <?php
                                        $count = 0;
                                        foreach ($messages as $msg):
                                            if ($count >= 3)
                                                break;
                                            $count++;
                                            ?>
                                            <div class="message-item">
                                                <div class="message-icon">
                                                    <img src="../img/svg/mail.svg" alt="Message">
                                                </div>
                                                <div class="message-details">
                                                    <div class="message-header">
                                                        <span
                                                            class="message-subject"><?= htmlspecialchars($msg['objet']) ?></span>
                                                        <span
                                                            class="message-date"><?= date('d/m/Y', strtotime($msg['date'])) ?></span>
                                                    </div>
                                                    <span
                                                        class="message-preview"><?= htmlspecialchars(substr($msg['message'], 0, 100)) ?>...</span>
                                                    <div class="message-meta">
                                                        <span
                                                            class="message-time"><?= date('H:i', strtotime($msg['date'])) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <img src="../img/svg/filter-empty.svg" alt="Aucun message">
                                        <p>Vous n'avez pas de messages</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>