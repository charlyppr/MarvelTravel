<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupérer l'ID du voyage en premier
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Vérification de la validité de l'ID
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

// Récupération des données du voyage
$json_file = "../../json/voyages.json";
$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier que l'ID est valide
if (!isset($voyages[$id])) {
    header('Location: ../../destination.php');
    exit;
}

$voyage = $voyages[$id];

// Récupération des données des étapes précédentes
if (isset($_POST['date_debut']) && isset($_POST['date_fin']) && isset($_POST['nb_personne'])) {
    // Si on reçoit des données POST, on les stocke en session
    $form_data1 = [
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'nb_personne' => $_POST['nb_personne']
    ];
    store_form_data('etape1', $form_data1);

    // Récupération des infos voyageurs depuis l'étape 2
    $voyageurs = [];
    for ($i = 1; $i <= $form_data1['nb_personne']; $i++) {
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

    $form_data2 = [
        'voyageurs' => $voyageurs
    ];
    store_form_data('etape2', $form_data2);

    // Initialiser les variables ici également
    $date_debut = $form_data1['date_debut'];
    $date_fin = $form_data1['date_fin'];
    $nb_personne = (int) $form_data1['nb_personne'];

} else {
    // Sinon, on tente de récupérer les données de la session
    $form_data1 = get_form_data('etape1');
    $form_data2 = get_form_data('etape2');

    if (!$form_data1 || !$form_data2) {
        header('Location: ../../destination.php');
        exit;
    }

    $date_debut = $form_data1['date_debut'];
    $date_fin = $form_data1['date_fin'];
    $nb_personne = (int) $form_data1['nb_personne'];
    $voyageurs = $form_data2['voyageurs'];
}

// Récupérer les options précédemment sélectionnées (si elles existent)
$optionsData = [];
if ($form_data3 = get_form_data('etape3')) {
    $optionsData = $form_data3['options'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choix des options - <?php echo htmlspecialchars($voyage['titre']); ?></title>
    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/reservation.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <h1 class="titre">Étape 3: Options du voyage</h1>

    <div class="information">
        <p>Destination: <?php echo htmlspecialchars($voyage['titre']); ?></p>
        <p>Dates: Du <?php echo date('d/m/Y', strtotime($date_debut)); ?> au
            <?php echo date('d/m/Y', strtotime($date_fin)); ?>
        </p>
        <p>Nombre de voyageurs: <?php echo $nb_personne; ?></p>
    </div>

    <form action="etape4.php?id=<?php echo $id; ?>" method="post" class="options-form">
        <!-- Données cachées des étapes précédentes -->
        <input type="hidden" name="date_debut" value="<?php echo htmlspecialchars($date_debut); ?>">
        <input type="hidden" name="date_fin" value="<?php echo htmlspecialchars($date_fin); ?>">
        <input type="hidden" name="nb_personne" value="<?php echo $nb_personne; ?>">

        <?php foreach ($voyageurs as $index => $voyageur): ?>
            <input type="hidden" name="civilite_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['civilite']); ?>">
            <input type="hidden" name="nom_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['nom']); ?>">
            <input type="hidden" name="prenom_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['prenom']); ?>">
            <input type="hidden" name="date_naissance_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['date_naissance']); ?>">
            <input type="hidden" name="nationalite_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['nationalite']); ?>">
            <input type="hidden" name="passport_<?php echo $index + 1; ?>"
                value="<?php echo htmlspecialchars($voyageur['passport']); ?>">
        <?php endforeach; ?>

        <section class="etapes-container">
            <?php foreach ($voyage['etapes'] as $etape_index => $etape): ?>
                <div class="etape-box">
                    <h2><?php echo htmlspecialchars($etape['lieu']); ?></h2>
                    <p>Durée: <?php echo htmlspecialchars($etape['duree']); ?></p>
                    <p>Prix de base: <?php echo number_format($etape['prix'], 2, ',', ' '); ?> €</p>

                    <?php if (!empty($etape['options'])): ?>
                        <div class="options-list">
                            <h3>Options disponibles</h3>
                            <?php foreach ($etape['options'] as $option_index => $option): ?>
                                <div class="option-item">
                                    <div class="option-header">
                                        <h4><?php echo htmlspecialchars($option['nom']); ?> -
                                            <?php echo number_format($option['prix'], 2, ',', ' '); ?> €
                                        </h4>
                                    </div>
                                    <div class="voyageurs-checkboxes">
                                        <p>Qui souhaite participer ?</p>
                                        <?php foreach ($voyageurs as $index => $voyageur):
                                            // Vérifier si cette option était précédemment sélectionnée pour ce voyageur
                                            $isChecked = false;
                                            if (
                                                isset($optionsData[$etape_index][$option_index]['voyageurs']) &&
                                                in_array($index, $optionsData[$etape_index][$option_index]['voyageurs'])
                                            ) {
                                                $isChecked = true;
                                            }
                                            ?>
                                            <div class="checkbox-container">
                                                <input type="checkbox"
                                                    name="options[<?php echo $etape_index; ?>][<?php echo $option_index; ?>][voyageurs][]"
                                                    value="<?php echo $index; ?>"
                                                    id="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>"
                                                    <?php if ($isChecked)
                                                        echo 'checked'; ?>>
                                                <label
                                                    for="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>">
                                                    <?php echo htmlspecialchars($voyageur['prenom'] . ' ' . $voyageur['nom']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Aucune option disponible pour cette étape.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="nav-buttons">
            <a href="etape2.php?id=<?php echo $id; ?>" class="back-button">Retour</a>
            <button type="submit" class="continue-button">Continuer vers le récapitulatif</button>
        </div>
    </form>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>