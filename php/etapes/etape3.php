<?php
require_once('../session.php');
check_auth('../../connexion.php');

// Récupération des données des étapes précédentes
if (!isset($_POST['date_debut']) || !isset($_POST['date_fin']) || !isset($_POST['nb_personne'])) {
    header('Location: ../../destination.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$nb_personne = (int) $_POST['nb_personne'];

// Vérification de la validité de l'ID
if ($id === null) {
    header('Location: ../../destination.php');
    exit;
}

// Récupération des infos voyageurs depuis l'étape 2
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

// Si aucun voyageur n'est enregistré, redirection
if (count($voyageurs) == 0) {
    header('Location: ../../destination.php');
    exit;
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
            <?php echo date('d/m/Y', strtotime($date_fin)); ?></p>
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
                                            <?php echo number_format($option['prix'], 2, ',', ' '); ?> €</h4>
                                    </div>
                                    <div class="voyageurs-checkboxes">
                                        <p>Qui souhaite participer ?</p>
                                        <?php foreach ($voyageurs as $index => $voyageur): ?>
                                            <div class="checkbox-container">
                                                <input type="checkbox"
                                                    name="options[<?php echo $etape_index; ?>][<?php echo $option_index; ?>][voyageurs][]"
                                                    value="<?php echo $index; ?>"
                                                    id="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>">
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
            <a href="javascript:history.back()" class="back-button">Retour</a>
            <button type="submit" class="continue-button">Continuer vers le récapitulatif</button>
        </div>
    </form>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>