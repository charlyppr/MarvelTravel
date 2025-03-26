<?php
require_once('../session.php');
check_auth('../../connexion.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
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
    <title>Choix des dates - <?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/reservation.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <h1 class="titre">Étape 1: Choix des dates</h1>

    <div class="information">
        <p>Destination: <?php echo htmlspecialchars($voyage['titre']); ?></p>
        <p>Description: <?php echo htmlspecialchars($voyage['resume']); ?></p>
        <p>Durée recommandée: <?php echo htmlspecialchars($voyage['dates']['duree']); ?></p>
    </div>

    <form action="etape2.php?id=<?php echo $id; ?>" method="post" class="date-selection-form"
        class="date-selection-form">
        <div class="form-group">
            <label for="date_debut">Date de début :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/calendar.svg" alt="calendrier" />
                <input type="date" name="date_debut" id="date_debut" required>
            </div>
        </div>

        <div class="form-group">
            <label for="date_fin">Date de fin :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/calendar.svg" alt="calendrier" />
                <input type="date" name="date_fin" id="date_fin" required>
            </div>
        </div>

        <div class="form-group">
            <label for="nb_personne">Nombre de personnes :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/people.svg" alt="personnes" />
                <input type="number" name="nb_personne" id="nb_personne" min="1" max="10" value="1" required>
            </div>
        </div>

        <div class="button-container">
            <button type="submit" class="continue-button">Continuer vers les détails du voyage</button>
        </div>
    </form>

    <a href="../destination.php" class="back-link">⬅ Retour aux destinations</a>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>