<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupération des données de l'étape 1
if (!isset($_POST['date_debut']) || !isset($_POST['date_fin']) || !isset($_POST['nb_personne'])) {
    header('Location: ../../destination.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$nb_personne = (int) $_POST['nb_personne'];

if ($id === null || $nb_personne < 1) {
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
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <h1 class="titre">Étape 2: Informations des voyageurs</h1>

    <div class="information">
        <p>Destination: <?php echo htmlspecialchars($voyage['titre']); ?></p>
        <p>Dates: Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au
            <?php echo date('d/m/Y', strtotime($date_fin)); ?></p>
        <p>Nombre de voyageurs: <?php echo $nb_personne; ?></p>
    </div>

    <form action="etape3.php?id=<?php echo $id; ?>" method="post" class="travelers-form">
        <!-- Données cachées de l'étape précédente -->
        <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>">
        <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
        <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">

        <div class="travelers-container">
            <?php for ($i = 1; $i <= $nb_personne; $i++): ?>
                <fieldset class="traveler-info">
                    <legend>Voyageur <?php echo $i; ?></legend>

                    <div class="form-group">
                        <label for="civilite_<?php echo $i; ?>">Civilité :</label>
                        <select name="civilite_<?php echo $i; ?>" id="civilite_<?php echo $i; ?>" required>
                            <option value="">Choisir</option>
                            <option value="M">Monsieur</option>
                            <option value="Mme">Madame</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom_<?php echo $i; ?>">Nom :</label>
                            <input type="text" name="nom_<?php echo $i; ?>" id="nom_<?php echo $i; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="prenom_<?php echo $i; ?>">Prénom :</label>
                            <input type="text" name="prenom_<?php echo $i; ?>" id="prenom_<?php echo $i; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date_naissance_<?php echo $i; ?>">Date de naissance :</label>
                        <div class="input-with-icon">
                            <img src="../../img/svg/calendar.svg" alt="calendrier" />
                            <input type="date" name="date_naissance_<?php echo $i; ?>" id="date_naissance_<?php echo $i; ?>"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nationalite_<?php echo $i; ?>">Nationalité :</label>
                        <input type="text" name="nationalite_<?php echo $i; ?>" id="nationalite_<?php echo $i; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="passport_<?php echo $i; ?>">Numéro de passeport :</label>
                        <input type="text" name="passport_<?php echo $i; ?>" id="passport_<?php echo $i; ?>" required>
                    </div>
                </fieldset>
            <?php endfor; ?>
        </div>

        <div class="nav-buttons">
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button type="submit" class="continue-button">Continuer</button>
        </div>
    </form>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>