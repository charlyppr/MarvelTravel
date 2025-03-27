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

// Calculer un prix total (base + options)
$prix_base = $voyage['prix'] * $nb_personne;
$prix_options = 0;

// Calculer le prix des options déjà sélectionnées (s'il y en a)
if (!empty($optionsData)) {
    foreach ($optionsData as $etape_index => $etape_options) {
        foreach ($etape_options as $option_index => $option_data) {
            if (isset($option_data['voyageurs']) && is_array($option_data['voyageurs'])) {
                $nb_participants = count($option_data['voyageurs']);
                if ($nb_participants > 0 && isset($voyage['etapes'][$etape_index]['options'][$option_index]['prix'])) {
                    $option_prix = $voyage['etapes'][$etape_index]['options'][$option_index]['prix'];
                    $prix_options += $option_prix * $nb_participants;
                }
            }
        }
    }
}

$prix_total_avec_options = $prix_base + $prix_options;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>Options du voyage - <?php echo htmlspecialchars($voyage['titre']); ?></title>

    <link rel="stylesheet" href="../../css/base.css">
    <link rel="stylesheet" href="../../css/etape3.css">
    <link rel="shortcut icon" href="../../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include '../nav.php'; ?>

    <form action="etape4.php?id=<?php echo $id; ?>" method="post">
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

        <div class="main-container">
            <div class="header">
                <div class="title">Options du voyage</div>
                <div class="subtitle">Retrouvez toutes les options proposées par notre agence !</div>
            </div>

            <div class="spacer">
                <?php foreach ($voyage['etapes'] as $etape_index => $etape): ?>
                    <div class="section">
                        <div class="section-header">
                            <div class="section-title"><?php echo htmlspecialchars($etape['lieu']); ?></div>
                            <div class="section-duration">Durée : <?php echo htmlspecialchars($etape['duree']); ?></div>
                        </div>

                        <?php if (!empty($etape['options'])): ?>
                            <div class="section-options-label">Options disponibles :</div>
                            <div class="options-container">
                                <?php foreach ($etape['options'] as $option_index => $option): ?>
                                    <div class="option">
                                        <div class="option-title">
                                            <span class="visite-guide-"><?php echo htmlspecialchars($option['nom']); ?> - </span>
                                            <b><?php echo number_format($option['prix'], 2, ',', ' '); ?>€</b>
                                        </div>

                                        <div class="option-actions">
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
                                                <div class="option-action<?php echo $isChecked ? '' : '1'; ?>">
                                                    <div class="option-action-name">
                                                        <?php echo htmlspecialchars($voyageur['prenom']); ?>
                                                    </div>

                                                    <label
                                                        for="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>">
                                                        <input type="checkbox"
                                                            name="options[<?php echo $etape_index; ?>][<?php echo $option_index; ?>][voyageurs][]"
                                                            value="<?php echo $index; ?>"
                                                            id="opt_<?php echo $etape_index; ?>_<?php echo $option_index; ?>_voy_<?php echo $index; ?>"
                                                            <?php if ($isChecked)
                                                                echo 'checked'; ?> style="display: none;">

                                                        <img class="<?php echo $isChecked ? 'check-2-icon' : 'no-1-icon'; ?>" alt=""
                                                            src="../../img/svg/<?php echo $isChecked ? 'check.svg' : 'no.svg'; ?>">
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="section-options-label">Aucune option disponible pour cette étape.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-price">
                <b class="total-price-text">Total :
                    <?php echo number_format($prix_total_avec_options, 2, ',', ' '); ?>€</b>
                <div class="navigation-buttons">
                    <a href="etape2.php?id=<?php echo $id; ?>" class="back-button">
                        <div class="back-button-text">Retour</div>
                    </a>
                    <button type="submit" class="continue-button">
                        <div class="back-button-text">Récapitulatif de la commande</div>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sélectionner les divs d'option au lieu des labels
            const optionDivs = document.querySelectorAll('.option-action, .option-action1');

            optionDivs.forEach(div => {
                div.addEventListener('click', function (e) {
                    // Trouver la checkbox à l'intérieur du div
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    const img = this.querySelector('img');

                    // Ne rien faire si on a cliqué directement sur la checkbox ou l'image
                    // (pour éviter double événements)
                    if (e.target === checkbox || e.target === img) {
                        return;
                    }

                    // Inverser l'état de la case à cocher
                    checkbox.checked = !checkbox.checked;

                    // Changer l'image et la classe du div parent
                    if (checkbox.checked) {
                        img.src = '../../img/svg/check.svg';
                        img.className = 'check-2-icon';
                        this.className = 'option-action';
                    } else {
                        img.src = '../../img/svg/no.svg';
                        img.className = 'no-1-icon';
                        this.className = 'option-action1';
                    }
                });
            });

            // Empêcher les événements de propagation sur les labels pour éviter les doubles clics
            const checkboxLabels = document.querySelectorAll('.option-action label, .option-action1 label');
            checkboxLabels.forEach(label => {
                label.addEventListener('click', function (e) {
                    // Empêcher la propagation pour éviter que l'événement de clic
                    // ne soit capturé à la fois par le label et le div parent
                    e.stopPropagation();

                    const checkbox = this.querySelector('input[type="checkbox"]');
                    const img = this.querySelector('img');
                    const actionDiv = this.closest('.option-action, .option-action1');

                    // Inverser l'état de la case à cocher
                    checkbox.checked = !checkbox.checked;

                    // Changer l'image et la classe du div parent
                    if (checkbox.checked) {
                        img.src = '../../img/svg/check.svg';
                        img.className = 'check-2-icon';
                        actionDiv.className = 'option-action';
                    } else {
                        img.src = '../../img/svg/no.svg';
                        img.className = 'no-1-icon';
                        actionDiv.className = 'option-action1';
                    }

                    // Empêcher le comportement par défaut du label
                    e.preventDefault();
                });
            });
        });
    </script>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>
</body>

</html>