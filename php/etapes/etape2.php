<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupérer l'ID du voyage en premier
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    header('Location: ../destination.php');
    exit;
}


$panierJson = file_get_contents('../../json/panier.json');
if ($panierJson !== false) {
    $panier = json_decode($panierJson, true);

    // Chercher le voyage correspondant dans le panier
    if (isset($panier['items'])) {
        foreach ($panier['items'] as $index => $item) {
            if ($item['voyage_id'] === $id) {
                // Mettre à jour l'étape
                $panier['items'][$index]['etape_atteinte'] = 2;

                // Sauvegarder le panier mis à jour
                file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
                break;
            }
        }
    }
}


// Vérifier si l'utilisateur consulte une nouvelle destination et effacer les données si c'est le cas
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    // ID différent, on efface les données de réservation
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données de l'étape 1
$form_data = get_form_data('etape1');
if (!$form_data) {
    header('Location: ../destination.php');
    exit;
}


$date_debut = $form_data['date_debut'];
$date_fin = $form_data['date_fin'];
$nb_personne = (int) $form_data['nb_personne'];

if ($nb_personne < 1) {
    header('Location: ../destination.php');
    exit;
}

$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupérer les données des voyageurs
$voyageursData = [];
if ($form_data2 = get_form_data('etape2')) {
    $voyageursData = $form_data2['voyageurs'];
}

// Récupérer les informations de l'utilisateur connecté
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
$user_civilite = isset($_SESSION['civilite']) ? $_SESSION['civilite'] : '';
$user_date_naissance = isset($_SESSION['date_naissance']) ? $_SESSION['date_naissance'] : '';
$user_nationalite = isset($_SESSION['nationalite']) ? $_SESSION['nationalite'] : '';
$user_passport_id = isset($_SESSION['passport_id']) ? $_SESSION['passport_id'] : '';

// Calculer les dates en format lisible
$date_debut_obj = new DateTime($date_debut);
$date_fin_obj = new DateTime($date_fin);
$date_debut_format = $date_debut_obj->format('d/m/Y');
$date_fin_format = $date_fin_obj->format('d/m/Y');

// Calculer la durée du séjour
$interval = $date_debut_obj->diff($date_fin_obj);
$duree_sejour = $interval->days;

// Calculer un prix total en fonction du nombre de personnes
$prix_base = $voyage['prix'];
$prix_total = $prix_base * $nb_personne;

// Calculer quelques éléments supplémentaires pour le résumé
$prix_par_jour = $duree_sejour > 0 ? ($prix_base * $nb_personne / $duree_sejour) : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des voyageurs
    $voyageurs = [];
    for ($i = 1; $i <= $nb_personne; $i++) {
        if (isset($_POST['nom_' . $i]) && isset($_POST['prenom_' . $i])) {
            $voyageurs[] = [
                'civilite' => $_POST['civilite_' . $i],
                'nom' => $_POST['nom_' . $i],
                'prenom' => $_POST['prenom_' . $i],
                'date_naissance' => $_POST['date_naissance_' . $i],
                'nationalite' => $_POST['nationalite_' . $i],
                'passport' => $_POST['passport_' . $i]
            ];
        }
    }

    // Stocker les données pour l'étape suivante
    $form_data = [
        'voyageurs' => $voyageurs
    ];
    store_form_data('etape2', $form_data);

    // Mettre à jour le panier
    $panierJson = file_get_contents('../../json/panier.json');
    $panier = json_decode($panierJson, true);

    // Déterminer l'index de l'item dans le panier
    $item_index = null;
    if (isset($panier['items'])) {
        foreach ($panier['items'] as $index => $item) {
            if ($item['voyage_id'] === $id) {
                $item_index = $index;
                break;
            }
        }
    }

    // Mettre à jour le panier si on a trouvé l'item
    if ($item_index !== null) {
        // Mettre à jour les informations des voyageurs
        $panier['items'][$item_index]['voyageurs'] = $voyageurs;

        // Sauvegarder le panier mis à jour
        $success = file_put_contents('../../json/panier.json', json_encode($panier, JSON_PRETTY_PRINT));
        if ($success === false) {
            error_log("Impossible d'écrire dans le fichier panier.json");
        }
    }

    // Rediriger vers l'étape 3
    header('Location: etape3.php?id=' . $id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Informations voyageurs - <?php echo htmlspecialchars($voyage['titre']); ?> • Marvel Travel</title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape2.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <div class="reservation-container">
        <!-- En-tête avec fil d'Ariane et progression -->
        <div class="booking-header">
            <div class="breadcrumb">
                <a href="../destination.php" class="breadcrumb-link">
                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                    <span>Destinations</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="etape1.php?id=<?php echo $id; ?>" class="breadcrumb-link">
                    <span>Étape 1: Dates</span>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Étape 2: Voyageurs</span>
            </div>

            <div class="booking-progress">
                <div class="progress-step completed">
                    <div class="step-indicator">
                        <img src="../../img/svg/check.svg" alt="Completed">
                    </div>
                    <div class="step-label">Dates</div>
                </div>
                <div class="progress-line active4"></div>
                <div class="progress-step active4">
                    <div class="step-indicator">2</div>
                    <div class="step-label">Voyageurs</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-indicator">3</div>
                    <div class="step-label">Options</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-indicator">4</div>
                    <div class="step-label">Paiement</div>
                </div>
            </div>
        </div>

        <div class="booking-content">
            <div class="travelers-summary">
                <div class="destination-info">
                    <h1 class="destination-title"><?php echo htmlspecialchars($voyage['titre']); ?></h1>

                    <!-- Image de la destination comme dans l'étape 1 -->
                    <div class="destination-image">
                        <img src="../<?php echo htmlspecialchars($voyage['image']); ?>"
                            alt="<?php echo htmlspecialchars($voyage['titre']); ?>" class="destination-photo">
                    </div>

                    <div class="booking-details">
                        <div class="booking-detail">
                            <img src="../../img/svg/calendar.svg" alt="Dates" class="detail-icon">
                            <div>
                                <div class="detail-label">Dates du voyage</div>
                                <div class="detail-value">
                                    <?php echo $date_debut_format . ' - ' . $date_fin_format; ?>
                                    <span class="detail-tag"><?php echo $duree_sejour; ?> jours</span>
                                </div>
                            </div>
                        </div>
                        <div class="booking-detail">
                            <img src="../../img/svg/users.svg" alt="Voyageurs" class="detail-icon">
                            <div>
                                <div class="detail-label">Nombre de voyageurs</div>
                                <div class="detail-value"><?php echo $nb_personne; ?>
                                    personne<?php echo $nb_personne > 1 ? 's' : ''; ?>
                                </div>
                            </div>
                        </div>
                        <div class="booking-detail">
                            <img src="../../img/svg/tag.svg" alt="Prix" class="detail-icon">
                            <div>
                                <div class="detail-label">Prix total estimé</div>
                                <div class="detail-value"><?php echo number_format($prix_total, 2, ',', ' '); ?> €</div>
                            </div>
                        </div>

                        <div class="instructions-panel">
                            <h2 class="instructions-title">Informations importantes</h2>
                            <p class="instructions-text">
                                Pour chaque voyageur, veuillez saisir les informations telles qu'elles apparaissent sur
                                le
                                passeport multiversel. Ces informations sont nécessaires pour la validation de votre
                                voyage
                                inter-dimensions et garantir votre sécurité à travers le multivers Marvel.
                            </p>
                            <div class="instructions-note">
                                <img src="../../img/svg/alert-circle.svg" alt="Note" class="note-icon">
                                <p>Les informations doivent correspondre exactement à celles de vos documents
                                    d'identité.
                                    Toute erreur peut entraîner des complications lors du passage entre les dimensions.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="travelers-form-container">
                <form action="etape2.php?id=<?php echo $id; ?>" method="post" id="travelersForm" class="travelers-form">
                    <?php if (isset($_GET['from_cart']) && isset($_GET['cart_index'])): ?>
                        <input type="hidden" name="from_cart" value="1">
                        <input type="hidden" name="cart_index" value="<?php echo $_GET['cart_index']; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>">
                    <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
                    <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">

                    <?php for ($i = 1; $i <= $nb_personne; $i++): ?>
                        <?php
                        // Initialiser les variables avec des valeurs par défaut
                        $civilite = '';
                        $nom = '';
                        $prenom = '';
                        $date_naissance = '';
                        $nationalite = '';
                        $passport = '';
                        
                        // Si des données existent pour ce voyageur, les utiliser
                        if (isset($voyageursData[$i - 1])) {
                            $civilite = $voyageursData[$i - 1]['civilite'];
                            $nom = $voyageursData[$i - 1]['nom'];
                            $prenom = $voyageursData[$i - 1]['prenom'];
                            $date_naissance = $voyageursData[$i - 1]['date_naissance'];
                            $nationalite = $voyageursData[$i - 1]['nationalite'];
                            $passport = $voyageursData[$i - 1]['passport'];
                        }
                        ?>

                        <div class="traveler-card card">
                            <div class="card-header">
                                <div class="traveler-number">
                                    <img src="../../img/svg/users.svg" alt="Voyageur" class="traveler-icon">
                                    <h3>Voyageur <?php echo $i; ?><?php echo ($i === 1) ? ' (Principal)' : ''; ?></h3>
                                </div>
                                <?php if ($i === 1): ?>
                                    <button type="button" id="autofill-button" class="autofill-button">
                                        <img src="../../img/svg/copy.svg" alt="Copier" class="autofill-icon">
                                        <span>Utiliser mes informations</span>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="card-content">
                                <div class="form-row">
                                    <div class="form-group form-group-small">
                                        <label for="civilite_<?php echo $i; ?>">Civilité</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/users.svg" alt="Civilité" class="field-icon">
                                            <select name="civilite_<?php echo $i; ?>" id="civilite_<?php echo $i; ?>"
                                                required>
                                                <option value="">Choisir...</option>
                                                <option value="M" <?php echo ($civilite == 'M') ? 'selected' : ''; ?>>
                                                    Monsieur</option>
                                                <option value="Mme" <?php echo ($civilite == 'Mme') ? 'selected' : ''; ?>>
                                                    Madame</option>
                                                <option value="Autre" <?php echo ($civilite == 'Autre') ? 'selected' : ''; ?>>
                                                    Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nom_<?php echo $i; ?>">Nom</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/file-text.svg" alt="Nom" class="field-icon">
                                            <input type="text" name="nom_<?php echo $i; ?>" id="nom_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars($nom); ?>"
                                                placeholder="Votre nom de famille" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="prenom_<?php echo $i; ?>">Prénom</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/file-text.svg" alt="Prénom" class="field-icon">
                                            <input type="text" name="prenom_<?php echo $i; ?>" id="prenom_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars($prenom); ?>" placeholder="Votre prénom"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date_naissance_<?php echo $i; ?>">Date de naissance</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/calendar.svg" alt="Date" class="field-icon">
                                            <input type="date" name="date_naissance_<?php echo $i; ?>"
                                                id="date_naissance_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars($date_naissance); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="field-help">Format : JJ/MM/AAAA</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="nationalite_<?php echo $i; ?>">Nationalité</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/globe.svg" alt="Nationalité" class="field-icon">
                                            <input type="text" name="nationalite_<?php echo $i; ?>"
                                                id="nationalite_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars($nationalite); ?>"
                                                placeholder="Ex: Française, Wakandaise..." required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group form-group-wide">
                                        <label for="passport_<?php echo $i; ?>">N° de passeport multiversel</label>
                                        <div class="form-field">
                                            <img src="../../img/svg/credit-card.svg" alt="Passeport" class="field-icon">
                                            <input type="text" name="passport_<?php echo $i; ?>"
                                                id="passport_<?php echo $i; ?>"
                                                value="<?php echo htmlspecialchars($passport); ?>"
                                                placeholder="XXX XXX XXX X" maxlength="10" required>
                                        </div>
                                        <div class="field-help">Format : XXX XXX XXX X (lettres)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="price-summary card">
                        <div class="card-header">
                            <img src="../../img/svg/credit-card.svg" alt="Paiement" class="card-icon">
                            <h3 class="card-title">Récapitulatif de votre réservation</h3>
                        </div>

                        <div class="card-content">
                            <div class="price-rows">
                                <div class="price-row">
                                    <span>Prix par personne</span>
                                    <span><?php echo number_format($voyage['prix'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="price-row">
                                    <span>Nombre de voyageurs</span>
                                    <span><?php echo $nb_personne; ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Durée du séjour</span>
                                    <span><?php echo $duree_sejour; ?> jours</span>
                                </div>
                                <div class="price-row">
                                    <span>Prix par jour</span>
                                    <span><?php echo number_format($prix_par_jour, 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="price-total">
                                    <span>Total</span>
                                    <span><?php echo number_format($prix_total, 2, ',', ' '); ?> €</span>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="etape1.php?id=<?php echo $id; ?><?php echo isset($_GET['from_cart']) && isset($_GET['cart_index']) ? '&from_cart=1&cart_index=' . $_GET['cart_index'] : ''; ?>"
                                    class="secondary-button">
                                    <img src="../../img/svg/arrow-left.svg" alt="Retour">
                                    Modifier les dates
                                </a>
                                <button type="submit" class="primary-button">
                                    Continuer vers les options
                                    <img src="../../img/svg/arrow-right.svg" alt="Continuer">
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fonction d'autofill
            const autoFillButton = document.getElementById('autofill-button');
            if (autoFillButton) {
                autoFillButton.addEventListener('click', function () {
                    // Récupération des valeurs utilisateur
                    const userData = {
                        lastName: '<?php echo addslashes($user_last_name); ?>',
                        firstName: '<?php echo addslashes($user_first_name); ?>',
                        civilite: '<?php echo addslashes($user_civilite); ?>',
                        dateNaissance: '<?php echo addslashes($user_date_naissance); ?>',
                        nationalite: '<?php echo addslashes($user_nationalite); ?>',
                        passport: '<?php echo addslashes($user_passport_id); ?>'
                    };

                    // Remplissage des champs du premier voyageur
                    document.getElementById('nom_1').value = userData.lastName;
                    document.getElementById('prenom_1').value = userData.firstName;

                    if (userData.civilite) {
                        document.getElementById('civilite_1').value = userData.civilite;
                    }

                    if (userData.dateNaissance) {
                        document.getElementById('date_naissance_1').value = userData.dateNaissance;
                    }

                    if (userData.nationalite) {
                        document.getElementById('nationalite_1').value = userData.nationalite;
                    }

                    if (userData.passport) {
                        document.getElementById('passport_1').value = userData.passport;
                    }
                });
            }
        });
    </script>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>