<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupérer l'ID du voyage en premier
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Vérifier si l'utilisateur consulte une nouvelle destination
// et effacer les données si c'est le cas
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    // ID différent, on efface les données de réservation
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données de l'étape 1
if (isset($_POST['date_debut']) && isset($_POST['date_fin']) && isset($_POST['nb_personne'])) {
    // Si on reçoit des données POST, on les stocke en session
    $form_data = [
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'nb_personne' => $_POST['nb_personne']
    ];
    store_form_data('etape1', $form_data);
} else {
    // Sinon, on tente de récupérer les données de la session
    $form_data = get_form_data('etape1');
    if (!$form_data) {
        header('Location: ../../destination.php');
        exit;
    }
}

$date_debut = $form_data['date_debut'];
$date_fin = $form_data['date_fin'];
$nb_personne = (int) $form_data['nb_personne'];

if ($nb_personne < 1) {
    header('Location: ../../destination.php');
    exit;
}

$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupérer les données des voyageurs (si elles existent)
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
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations voyageurs - <?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/reservation.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
    <style>
        .auto-fill-button {
            background-color: #f2dbaf;
            color: #0d0d0d;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .auto-fill-button:hover {
            background-color: #e6c993;
            transform: translateY(-2px);
        }

        .auto-fill-button img {
            width: 16px;
            height: 16px;
        }
    </style>
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <h1 class="titre">Étape 2: Informations des voyageurs</h1>

    <div class="information">
        <p>Destination: <?php echo htmlspecialchars($voyage['titre']); ?></p>
        <p>Dates: Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au
            <?php echo date('d/m/Y', strtotime($date_fin)); ?>
        </p>
        <p>Nombre de voyageurs: <?php echo $nb_personne; ?></p>
    </div>

    <form action="etape3.php?id=<?php echo $id; ?>" method="post" class="travelers-form">
        <!-- Données cachées de l'étape précédente -->
        <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>">
        <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
        <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">

        <div class="travelers-container">
            <?php for ($i = 1; $i <= $nb_personne; $i++): ?>
                <?php
                // Récupérer les données du voyageur si elles existent
                $civilite = '';
                $nom = '';
                $prenom = '';
                $date_naissance = '';
                $nationalite = '';
                $passport = '';

                if (isset($voyageursData[$i - 1])) {
                    $civilite = $voyageursData[$i - 1]['civilite'];
                    $nom = $voyageursData[$i - 1]['nom'];
                    $prenom = $voyageursData[$i - 1]['prenom'];
                    $date_naissance = $voyageursData[$i - 1]['date_naissance'];
                    $nationalite = $voyageursData[$i - 1]['nationalite'];
                    $passport = $voyageursData[$i - 1]['passport'];
                }
                ?>
                <fieldset class="traveler-info">
                    <legend>Voyageur <?php echo $i; ?></legend>

                    <?php if ($i === 1): ?>
                        <button type="button" id="autofill-button" class="auto-fill-button">
                            <img src="../../img/svg/profile.svg" alt="profil">
                            Utiliser mes informations
                        </button>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="civilite_<?php echo $i; ?>">Civilité :</label>
                        <select name="civilite_<?php echo $i; ?>" id="civilite_<?php echo $i; ?>" required>
                            <option value="">Choisir</option>
                            <option value="M" <?php echo ($civilite == 'M') ? 'selected' : ''; ?>>Monsieur</option>
                            <option value="Mme" <?php echo ($civilite == 'Mme') ? 'selected' : ''; ?>>Madame</option>
                            <option value="Autre" <?php echo ($civilite == 'Autre') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom_<?php echo $i; ?>">Nom :</label>
                            <input type="text" name="nom_<?php echo $i; ?>" id="nom_<?php echo $i; ?>"
                                value="<?php echo htmlspecialchars($nom); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenom_<?php echo $i; ?>">Prénom :</label>
                            <input type="text" name="prenom_<?php echo $i; ?>" id="prenom_<?php echo $i; ?>"
                                value="<?php echo htmlspecialchars($prenom); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date_naissance_<?php echo $i; ?>">Date de naissance :</label>
                        <div class="input-with-icon">
                            <img src="../../img/svg/calendar.svg" alt="calendrier" />
                            <input type="date" name="date_naissance_<?php echo $i; ?>" id="date_naissance_<?php echo $i; ?>"
                                value="<?php echo htmlspecialchars($date_naissance); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nationalite_<?php echo $i; ?>">Nationalité :</label>
                        <input type="text" name="nationalite_<?php echo $i; ?>" id="nationalite_<?php echo $i; ?>"
                            value="<?php echo htmlspecialchars($nationalite); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="passport_<?php echo $i; ?>">Numéro de passeport :</label>
                        <input type="text" name="passport_<?php echo $i; ?>" id="passport_<?php echo $i; ?>"
                            value="<?php echo htmlspecialchars($passport); ?>" required>
                    </div>
                </fieldset>
            <?php endfor; ?>
        </div>

        <div class="nav-buttons">
            <a href="etape1.php?id=<?php echo $id; ?>" class="back-button">Retour</a>
            <button type="submit" class="continue-button">Continuer</button>
        </div>
    </form>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const autoFillButton = document.getElementById('autofill-button');

            if (autoFillButton) {
                autoFillButton.addEventListener('click', function () {
                    // Remplir les champs du premier voyageur avec les données de l'utilisateur
                    document.getElementById('nom_1').value = '<?php echo addslashes($user_last_name); ?>';
                    document.getElementById('prenom_1').value = '<?php echo addslashes($user_first_name); ?>';

                    // Civilité
                    const civilite = '<?php echo addslashes($user_civilite); ?>';
                    if (civilite) {
                        document.getElementById('civilite_1').value = civilite;
                    }

                    // Date de naissance
                    const dateNaissance = '<?php echo addslashes($user_date_naissance); ?>';
                    if (dateNaissance) {
                        document.getElementById('date_naissance_1').value = dateNaissance;
                    }

                    // Nationalité
                    const nationalite = '<?php echo addslashes($user_nationalite); ?>';
                    if (nationalite) {
                        document.getElementById('nationalite_1').value = nationalite;
                    }

                    // Numéro de passeport
                    const passportId = '<?php echo addslashes($user_passport_id); ?>';
                    if (passportId) {
                        document.getElementById('passport_1').value = passportId;
                    }

                    // Focus sur le premier champ vide ou sur le passeport si tout est rempli
                    if (!dateNaissance) {
                        document.getElementById('date_naissance_1').focus();
                    } else if (!nationalite) {
                        document.getElementById('nationalite_1').focus();
                    } else if (!passportId) {
                        document.getElementById('passport_1').focus();
                    }
                });
            }
        });
    </script>
</body>

</html>