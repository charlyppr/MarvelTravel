<?php
require_once('../session.php');
check_auth('../../connexion.php');

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

    <form action="etape2.php?id=<?php echo $id; ?>" method="post" class="date-selection-form" id="reservationForm">
        <div class="form-group">
            <label for="date_debut">Date de début :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/calendar.svg" alt="calendrier" />
                <input type="date" name="date_debut" id="date_debut" min="<?php echo $date_min; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="date_fin">Date de fin :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/calendar.svg" alt="calendrier" />
                <input type="date" name="date_fin" id="date_fin" min="<?php echo $date_min; ?>" required>
            </div>
            <p class="date-info" id="duree-info"></p>
        </div>

        <div class="form-group">
            <label for="nb_personne">Nombre de personnes :</label>
            <div class="input-with-icon">
                <img src="../../img/svg/people.svg" alt="personnes" />
                <input type="number" name="nb_personne" id="nb_personne" min="1" max="10"
                    value="<?php echo $nb_personne_value; ?>" required>
            </div>
        </div>

        <div class="button-container">
            <button type="submit" class="continue-button" onclick="storeFormData()">Continuer vers les détails du
                voyage</button>
        </div>
    </form>

    <a href="../destination.php" class="back-link">⬅ Retour aux destinations</a>

    <?php include '../footer.php'; ?>

    <script src="../../js/nav.js"></script>
    <script src="../../js/custom-cursor.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateDebutInput = document.getElementById('date_debut');
            const dateFinInput = document.getElementById('date_fin');
            const dureeInfo = document.getElementById('duree-info');
            const dureeRecommandee = <?php echo $duree_recommandee; ?>;
            const form = document.getElementById('reservationForm');

            // Vérifier s'il y a des valeurs sauvegardées en session
            const dateSavedDebut = "<?php echo $date_debut_value; ?>";
            const dateSavedFin = "<?php echo $date_fin_value; ?>";

            // Si aucune valeur sauvegardée, définir aujourd'hui par défaut
            if (dateSavedDebut) {
                dateDebutInput.value = dateSavedDebut;
            } else {
                const aujourdhui = new Date().toISOString().split('T')[0];
                dateDebutInput.value = aujourdhui;
            }

            dateFinInput.disabled = false;

            // Définir la date de fin
            if (dateSavedFin) {
                dateFinInput.value = dateSavedFin;
            } else {
                const dateDebut = new Date(dateDebutInput.value);
                const dateFinRecommandee = new Date(dateDebut);
                dateFinRecommandee.setDate(dateFinRecommandee.getDate() + dureeRecommandee);
                dateFinInput.value = dateFinRecommandee.toISOString().split('T')[0];
            }

            // S'assurer que la date minimale est correcte
            const dateDebut = new Date(dateDebutInput.value);
            const dateFinMin = new Date(dateDebut);
            dateFinMin.setDate(dateFinMin.getDate() + 1);
            dateFinInput.min = dateFinMin.toISOString().split('T')[0];

            // Calculer la durée initiale
            calculerDuree();

            dateDebutInput.addEventListener('change', function () {
                // Date de début sélectionnée
                const dateDebut = new Date(this.value);

                // Ajouter un jour à la date de début pour la date de fin minimale
                const dateFinMin = new Date(dateDebut);
                dateFinMin.setDate(dateFinMin.getDate() + 1);

                // Calculer la date recommandée (date de début + durée recommandée)
                const dateFinRecommandee = new Date(dateDebut);
                dateFinRecommandee.setDate(dateFinRecommandee.getDate() + dureeRecommandee);

                // Formater les dates pour l'attribut min
                const dateFinMinFormatted = dateFinMin.toISOString().split('T')[0];
                const dateFinRecommandeeFormatted = dateFinRecommandee.toISOString().split('T')[0];

                // Activer et mettre à jour la date min de fin
                dateFinInput.disabled = false;
                dateFinInput.min = dateFinMinFormatted;

                // Définir la date de fin à la date recommandée par défaut
                dateFinInput.value = dateFinRecommandeeFormatted;

                // Calculer et afficher la durée
                calculerDuree();
            });

            // Calculer la durée quand la date de fin change
            dateFinInput.addEventListener('change', function () {
                calculerDuree();
            });

            // Fonction pour calculer la durée du séjour
            function calculerDuree() {
                if (dateDebutInput.value && dateFinInput.value) {
                    const dateDebut = new Date(dateDebutInput.value);
                    const dateFin = new Date(dateFinInput.value);

                    // S'assurer que la date de fin est postérieure à la date de début
                    if (dateFin <= dateDebut) {
                        // Définir la date de fin au lendemain de la date de début
                        const newDateFin = new Date(dateDebut);
                        newDateFin.setDate(newDateFin.getDate() + 1);
                        dateFinInput.value = newDateFin.toISOString().split('T')[0];
                        dateFin.setDate(dateDebut.getDate() + 1); // Mettre à jour la variable dateFin aussi
                    }

                    // Différence en millisecondes
                    const differenceMs = dateFin - dateDebut;

                            // Convertir en jours
                        const differenceJours = Math.round(differenceMs / (1000 * 60 * 60 * 24));
   
                         dureeInfo.textContent = `Durée du séjour: ${differenceJours} jour(s)`;

                    // Avertissement si différent de la durée recommandée
                    if (differenceJours !== dureeRecommandee) {
                        dureeInfo.innerHTML += `<br><span class="warning">(La durée recommandée est de ${dureeRecommandee} jours)</span>`;
                    }
                } else {
                    dureeInfo.textContent = '';
                }
            }

            // Valider le formulaire avant soumission
            form.addEventListener('submit', function (e) {
                if (dateDebutInput.value && dateFinInput.value) {
                    const dateDebut = new Date(dateDebutInput.value);
                    const dateFin = new Date(dateFinInput.value);

                    if (dateFin <= dateDebut) {
                        e.preventDefault();
                        alert('La date de fin doit être postérieure à la date de début.');
                    }
                }
            });

            // Nouvelle fonction pour stocker les données côté client
            function storeFormData() {
                // Les données sont envoyées via le formulaire, donc pas besoin 
                // d'action supplémentaire ici pour l'étape 1
            }
        });
    </script>
</body>

</html>