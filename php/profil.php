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
</head>

<body>
    <div class="default"></div>

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
                        <a href="../index.php" class="redir-text">
                            <span>Retour à l'accueil</span>
                            <img src="../img/svg/fleche-redir.svg" alt="flèche">
                        </a>
                    </div>

                    <div class="dashboard-grid">
                        <!-- Carte du profil -->
                        <div class="card profile-card">
                            <div class="header-text">
                                <img src="../img/svg/users.svg" alt="Profil" class="header-icon">
                                <span class="titre-card">Mon profil</span>
                            </div>

                            <div class="card-content">
                                <form id="profileForm" method="post" action="update-profile.php">
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
                                                        data-original-value="<?= $_SESSION['first_name'] ?>" disabled>
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
                                                        data-original-value="<?= $_SESSION['last_name'] ?>" disabled>
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
                                                        data-original-value="<?= $_SESSION['email'] ?>" disabled>
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

                        <!-- Statistiques -->
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

                        <!-- Derniers voyages -->
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

                        <!-- Messages -->
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
                                                break; // Limite à 3 messages
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

    <script>
        document.querySelectorAll('.close-notification').forEach(button => {
            button.addEventListener('click', () => {
                const notification = button.closest('.notification');
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
        });

        setTimeout(() => {
            document.querySelectorAll('.notification').forEach(notification => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });
        }, 5000);

        document.addEventListener('DOMContentLoaded', () => {
            const validated = new Set();
            const editingFields = new Set(); // Nouvel ensemble pour suivre les champs en cours d'édition
            const submitBtn = document.getElementById('submit-profile-btn');
            const cancelAllBtn = document.getElementById('cancel-all-btn');

            function toggleEditingClass(field, isEditing) {
                const fieldValue = document.querySelector(`.field-value:has(#${field})`);
                if (fieldValue) {
                    if (isEditing) {
                        fieldValue.classList.add('editing');
                    } else {
                        fieldValue.classList.remove('editing');
                    }
                }
            }

            function updateActionButtons() {
                if (validated.size > 0) {
                    // Afficher les boutons avec animation
                    submitBtn.style.display = 'inline-flex';
                    cancelAllBtn.style.display = 'inline-flex';

                    // Délai court pour permettre au navigateur de traiter le changement de display
                    setTimeout(() => {
                        submitBtn.classList.add('visible');
                        cancelAllBtn.classList.add('visible');
                    }, 10);
                } else {
                    // Cacher les boutons avec animation
                    submitBtn.classList.remove('visible');
                    cancelAllBtn.classList.remove('visible');

                    // Attendre que l'animation soit terminée avant de les cacher complètement
                    setTimeout(() => {
                        submitBtn.style.display = 'none';
                        cancelAllBtn.style.display = 'none';
                    }, 300); // Durée de la transition
                }
            }

            // Fonction pour réinitialiser tous les champs modifiés
            function resetAllFields() {
                // Réinitialiser les champs validés
                validated.forEach(field => {
                    const input = document.getElementById(field);
                    input.value = input.dataset.originalValue;
                    input.disabled = true;
                    toggleEditingClass(field, false);
                });

                // Réinitialiser les champs en cours d'édition
                editingFields.forEach(field => {
                    const input = document.getElementById(field);
                    const editBtn = document.querySelector(`.field-edit[data-field="${field}"]`);
                    const validateBtn = document.querySelector(`.field-validate[data-field="${field}"]`);
                    const cancelBtn = document.querySelector(`.field-cancel[data-field="${field}"]`);

                    input.value = input.dataset.originalValue;
                    input.disabled = true;
                    toggleEditingClass(field, false);

                    if (editBtn) editBtn.style.display = 'inline-flex';
                    if (validateBtn) validateBtn.style.display = 'none';
                    if (cancelBtn) cancelBtn.style.display = 'none';
                });

                validated.clear();
                editingFields.clear();
                updateActionButtons();
            }

            // Gestionnaire pour le bouton d'annulation globale
            cancelAllBtn.addEventListener('click', resetAllFields);

            document.querySelectorAll('.field-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const field = btn.dataset.field;
                    const input = document.getElementById(field);
                    const validate = document.querySelector(`.field-validate[data-field="${field}"]`);
                    const cancel = document.querySelector(`.field-cancel[data-field="${field}"]`);

                    // Stocker la valeur originale si c'est la première fois qu'on édite
                    if (!editingFields.has(field)) {
                        // Conserver la valeur originale avant toute modification
                        input.setAttribute('data-original-value', input.value);
                        editingFields.add(field);
                    }

                    input.disabled = false;
                    input.focus();
                    toggleEditingClass(field, true);
                    btn.style.display = 'none';
                    validate.style.display = 'inline-flex';
                    cancel.style.display = 'inline-flex';

                    // Ne pas afficher le bouton d'annulation global avant validation
                });
            });

            // Délégation d'événements pour le clic sur les boutons "validate" et "cancel"
            document.addEventListener('click', (event) => {
                const validateBtn = event.target.closest('.field-validate');
                const cancelBtn = event.target.closest('.field-cancel');

                if (validateBtn) {
                    const field = validateBtn.dataset.field;
                    const input = document.getElementById(field);
                    if (input.value.trim() === '') {
                        alert('Ce champ ne peut pas être vide');
                        return;
                    }
                    const originalValue = input.getAttribute('data-original-value');
                    const hasChanged = originalValue !== input.value;
                    input.disabled = true;
                    toggleEditingClass(field, false);
                    validateBtn.style.display = 'none';
                    document.querySelector(`.field-cancel[data-field="${field}"]`).style.display = 'none';
                    document.querySelector(`.field-edit[data-field="${field}"]`).style.display = 'inline-flex';
                    if (hasChanged) validated.add(field);
                    else validated.delete(field);
                    updateActionButtons();
                }

                if (cancelBtn) {
                    const field = cancelBtn.dataset.field;
                    const input = document.getElementById(field);
                    input.value = input.dataset.originalValue;
                    input.disabled = true;
                    toggleEditingClass(field, false);
                    cancelBtn.style.display = 'none';
                    document.querySelector(`.field-validate[data-field="${field}"]`).style.display = 'none';
                    document.querySelector(`.field-edit[data-field="${field}"]`).style.display = 'inline-flex';
                    editingFields.delete(field);
                    if (editingFields.size === 0 && validated.size === 0) {
                        cancelAllBtn.style.display = 'none';
                    }
                }
            });

            document.querySelectorAll('.profile-input').forEach(input => {
                input.addEventListener('focus', () => {
                    const wrapper = input.closest('.input-wrapper');
                    if (wrapper) wrapper.classList.add('focused');
                });

                input.addEventListener('blur', () => {
                    const wrapper = input.closest('.input-wrapper');
                    if (wrapper) wrapper.classList.remove('focused');
                });
            });

            const profileForm = document.getElementById('profileForm');

            profileForm.addEventListener('submit', (event) => {
                document.querySelectorAll('.profile-input').forEach(input => {
                    input.disabled = false;
                });
            });
        });
    </script>

</body>

</html>