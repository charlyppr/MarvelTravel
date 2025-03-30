<?php
require_once "session.php";

// Vérifier si l'utilisateur a bien passé l'étape 1
if (!isset($_SESSION['inscription'])) {
    header("Location: inscription.php");
    exit();
}

// Récupérer les données sauvegardées si elles existent
$date_naissance_value = $_SESSION['inscription']['date_naissance'] ?? '';
$nationalite_value = $_SESSION['inscription']['nationalite'] ?? '';

// Traitement du formulaire de l'étape 2
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_naissance = $_POST['date_naissance'] ?? '';
    $nationalite = trim($_POST['nationalite'] ?? '');

    if ($date_naissance && $nationalite) {
        // Récupérer les données de l'étape 1
        $user_data = $_SESSION['inscription'];

        // S'assurer que le mot de passe existe, sinon utiliser celui stocké dans user_data_complete
        if (!isset($user_data['password']) && isset($_SESSION['user_data_complete']['password'])) {
            $user_data['password'] = $_SESSION['user_data_complete']['password'];
        }

        // Vérifier si le mot de passe existe, sinon rediriger vers l'étape 1
        if (!isset($user_data['password'])) {
            $_SESSION['inscri'] = 4; // Code d'erreur pour mot de passe manquant
            header("Location: inscription.php");
            exit();
        }

        // Ajouter les nouvelles données
        $user_data['date_naissance'] = $date_naissance;
        $user_data['nationalite'] = $nationalite;

        // Mettre à jour la session avec les nouvelles données
        $_SESSION['inscription'] = $user_data;

        // Générer un ID unique de passeport (10 chiffres)
        $passport_id = generateUniquePassportID();

        // Compléter les données utilisateur SANS LES ENREGISTRER
        $role = 'user';
        $user_data['role'] = $role;
        $user_data['passport_id'] = $passport_id;
        $user_data['date_inscription'] = date("Y-m-d H:i:s");
        $user_data['blocked'] = false;
        $user_data['vip'] = false;
        $user_data['last_login'] = "null";

        // Supprimer le mot de passe temporaire s'il existe
        if (isset($user_data['temp_pass'])) {
            unset($user_data['temp_pass']);
        }

        // NE PAS ENREGISTRER DANS LE FICHIER JSON À CETTE ÉTAPE
        // On stocke juste les données complètes en session pour l'enregistrement ultérieur
        $_SESSION['user_data_complete'] = $user_data;

        // Stocker les informations de passeport pour l'affichage
        $_SESSION['passport_info'] = [
            'name' => $user_data['first_name'] . ' ' . $user_data['last_name'],
            'passport_id' => $passport_id,
            'date_naissance' => $date_naissance,
            'nationalite' => $nationalite,
            'date_emission' => date("Y-m-d")
        ];

        // Stocker les informations pour la connexion automatique
        $_SESSION['auto_login'] = [
            'email' => $user_data['email'],
            'role' => $user_data['role'],
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'civilite' => $user_data['civilite'] ?? '',
            'date_naissance' => $user_data['date_naissance'],
            'nationalite' => $user_data['nationalite'],
            'passport_id' => $user_data['passport_id']
        ];

        // Nettoyer les données d'inscription (on garde juste le nécessaire)
        $_SESSION['inscription'] = [
            'civilite' => $user_data['civilite'],
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'email' => $user_data['email']
        ];

        // Redirection vers la page de passeport
        header("Location: passeport.php");
        exit();
    } else {
        // Si certains champs sont manquants, on sauvegarde quand même ce qui a été rempli
        if ($date_naissance) {
            $_SESSION['inscription']['date_naissance'] = $date_naissance;
        }
        if ($nationalite) {
            $_SESSION['inscription']['nationalite'] = $nationalite;
        }
    }
}

// Fonction pour générer un ID de passeport unique à 10 chiffres
function generateUniquePassportID()
{
    $json_file = "../json/users.json";
    $users = [];
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $users = json_decode($json_data, true) ?? [];
    }

    // Récupérer tous les ID de passeport existants
    $existing_ids = [];
    foreach ($users as $user) {
        if (isset($user['passport_id'])) {
            $existing_ids[] = $user['passport_id'];
        }
    }

    // Générer un nouvel ID unique
    do {
        $passport_id = sprintf('%010d', mt_rand(1000000000, 9999999999));
    } while (in_array($passport_id, $existing_ids));

    return $passport_id;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Finaliser l'inscription</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/shield.svg" alt="shield pin" class="shield-pin">
            <img src="../img/svg/captain.svg" alt="captain pin" class="captain-pin">

            <a href="inscription.php" class="retour" id="back-button"><img src="../img/svg/fleche-gauche.svg"
                    alt="fleche retour"></a>

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Finalisez votre inscription</span>
                <span class="sous-titre-3">Plus qu'une étape pour obtenir votre Passeport Multiversel!</span>
            </div>

            <form class="form" action="inscription-etape2.php" method="post">
                <div class="form-container">
                    <div class="date-input-container">
                        <label for="date_naissance">Date de naissance</label>
                        <div class="date-input">
                            <img src="../img/svg/calendar.svg" alt="Date Icon">
                            <input type="date" id="date_naissance" name="date_naissance" required
                                max="<?php echo date('Y-m-d'); ?>" placeholder="Date de naissance"
                                value="<?php echo htmlspecialchars($date_naissance_value); ?>">
                        </div>

                    </div>

                    <div class="nationalite">
                        <img src="../img/svg/globe.svg" alt="Globe Icon">
                        <select name="nationalite" id="nationalite" required>
                            <option value="" disabled selected>Nationalité</option>
                            <option value="Wakandaise" <?php echo $nationalite_value === 'Wakandaise' ? 'selected' : ''; ?>>Wakandaise</option>
                            <option value="Asgardienne" <?php echo $nationalite_value === 'Asgardienne' ? 'selected' : ''; ?>>Asgardienne</option>
                            <option value="Xandarienne" <?php echo $nationalite_value === 'Xandarienne' ? 'selected' : ''; ?>>Xandarienne</option>
                            <option value="Kree" <?php echo $nationalite_value === 'Kree' ? 'selected' : ''; ?>>Kree</option>
                            <option value="Skrull" <?php echo $nationalite_value === 'Skrull' ? 'selected' : ''; ?>>Skrull</option>
                            <option value="Française" <?php echo $nationalite_value === 'Française' ? 'selected' : ''; ?>>Française</option>
                            <option value="Américaine" <?php echo $nationalite_value === 'Américaine' ? 'selected' : ''; ?>>Américaine</option>
                            <option value="Britannique" <?php echo $nationalite_value === 'Britannique' ? 'selected' : ''; ?>>Britannique</option>
                            <option value="Allemande" <?php echo $nationalite_value === 'Allemande' ? 'selected' : ''; ?>>Allemande</option>
                            <option value="Espagnole" <?php echo $nationalite_value === 'Espagnole' ? 'selected' : ''; ?>>Espagnole</option>
                            <option value="Italienne" <?php echo $nationalite_value === 'Italienne' ? 'selected' : ''; ?>>Italienne</option>
                            <option value="Canadienne" <?php echo $nationalite_value === 'Canadienne' ? 'selected' : ''; ?>>Canadienne</option>
                            <option value="Japonaise" <?php echo $nationalite_value === 'Japonaise' ? 'selected' : ''; ?>>Japonaise</option>
                            <option value="Chinoise" <?php echo $nationalite_value === 'Chinoise' ? 'selected' : ''; ?>>Chinoise</option>
                            <option value="Russe" <?php echo $nationalite_value === 'Russe' ? 'selected' : ''; ?>>Russe</option>
                            <option value="Australienne" <?php echo $nationalite_value === 'Australienne' ? 'selected' : ''; ?>>Australienne</option>
                            <option value="Brésilienne" <?php echo $nationalite_value === 'Brésilienne' ? 'selected' : ''; ?>>Brésilienne</option>
                            <option value="Indienne" <?php echo $nationalite_value === 'Indienne' ? 'selected' : ''; ?>>Indienne</option>
                            <option value="Mexicaine" <?php echo $nationalite_value === 'Mexicaine' ? 'selected' : ''; ?>>Mexicaine</option>
                            <option value="Sud-Africaine" <?php echo $nationalite_value === 'Sud-Africaine' ? 'selected' : ''; ?>>Sud-Africaine</option>
                        </select>
                    </div>
                </div>


                <div class="generate-button-container">
                    <button class="generate-button" type="submit">
                        Générer mon passeport multiversel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>
</body>

</html>