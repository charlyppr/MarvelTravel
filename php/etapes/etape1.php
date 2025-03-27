<?php
require_once('../session.php');
check_auth('../connexion.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Vérifier si l'utilisateur consulte une nouvelle destination
// et effacer les données si c'est le cas
$current_voyage_id = isset($_SESSION['current_voyage_id']) ? $_SESSION['current_voyage_id'] : null;
if ($current_voyage_id !== $id) {
    clear_reservation_data();
    $_SESSION['current_voyage_id'] = $id;
}

// Récupération des données sauvegardées en session
$form_data = get_form_data('etape1');
$date_debut_value = $form_data ? $form_data['date_debut'] : '';
$date_fin_value = $form_data ? $form_data['date_fin'] : '';
$nb_personne_value = $form_data ? $form_data['nb_personne'] : 1;

$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Convertir la durée recommandée en nombre de jours pour la validation
$duree_recommandee = 7; // Valeur par défaut
if (isset($voyage['dates']['duree'])) {
    $duree_str = $voyage['dates']['duree'];
    $duree_parts = explode(' ', $duree_str);
    if (count($duree_parts) >= 2 && is_numeric($duree_parts[0])) {
        $duree_recommandee = (int) $duree_parts[0];
    }
}

// Date minimum = aujourd'hui
$date_min = date('Y-m-d');

// Calculer un prix total en fonction du nombre de personnes
$prix_base = $voyage['prix'];
$prix_total = $prix_base * $nb_personne_value;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Choix des dates - <?php echo htmlspecialchars($voyage['titre']); ?></title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape1.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <div class="container">
        <div class="container1">
            <div class="title">Choix des dates</div>
            <div class="container2">
                <b class="product-name"><?php echo htmlspecialchars($voyage['titre']); ?></b>
                <div class="description"><?php echo htmlspecialchars($voyage['resume']); ?></div>
            </div>
            <div class="container3">
                <img class="check-1-icon" alt="" src="../../img/svg/check.svg">
                <div class="recommended-duration">
                    Durée recommandée : <?php echo htmlspecialchars($voyage['dates']['duree']); ?>
                </div>
            </div>
            <b class="total-price-text">Total : <?php echo number_format($prix_total, 2, ',', ' '); ?>€</b>
        </div>

        <form action="etape2.php?id=<?php echo $id; ?>" method="post" id="reservationForm">
            <div class="container4">
                <img class="atlantis-1-icon" alt="" src="../<?php echo htmlspecialchars($voyage['image']); ?>"
                    alt="<?php echo htmlspecialchars($voyage['titre']); ?>">

                <div class="container5">
                    <div class="container6">
                        <div class="start-date-label">Date de début :</div>
                        <div class="container7">
                            <img class="calendar-2-icon" alt="" src="../../img/svg/calendar.svg">
                            <input type="date" name="date_debut" id="date_debut" class="start-date"
                                min="<?php echo $date_min; ?>" value="<?php echo $date_debut_value; ?>" required>
                        </div>
                    </div>

                    <div class="container-child"></div>

                    <div class="container8">
                        <div class="start-date-label">Date de fin :</div>
                        <div class="container9">
                            <img class="calendar-2-icon" alt="" src="../../img/svg/calendar.svg">
                            <input type="date" name="date_fin" id="date_fin" class="end-date"
                                min="<?php echo $date_min; ?>" value="<?php echo $date_fin_value; ?>" required>
                        </div>
                    </div>

                    <div class="container-child"></div>

                    <div class="container8">
                        <div class="start-date-label">Nombre de voyageurs :</div>
                        <div class="container9">
                            <img class="calendar-2-icon" alt="" src="../../img/svg/person.svg">
                            <input type="number" name="nb_personne" id="nb_personne" class="end-date" min="1" max="10"
                                value="<?php echo $nb_personne_value; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="total-price">
                    <div class="navigation-buttons">
                        <a href="../destination.php" class="back-button">
                            <div class="back-button-text">Retour</div>
                        </a>
                        <button type="submit" class="continue-button">
                            <div class="back-button-text">Continuer vers les options du voyage</div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>