<?php
require_once "session.php";

// Vérifier si l'utilisateur a bien passé l'étape 1
if (!isset($_SESSION['inscription'])) {
    header("Location: inscription.php");
    exit();
}

// Traitement du formulaire de l'étape 2
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_naissance = $_POST['date_naissance'] ?? '';
    $nationalite = trim($_POST['nationalite'] ?? '');

    if ($date_naissance && $nationalite) {
        // Récupérer les données de l'étape 1
        $user_data = $_SESSION['inscription'];

        // Générer un ID unique de passeport (10 chiffres)
        $passport_id = generateUniquePassportID();

        // Compléter les données utilisateur
        $role = 'user';
        $user_data['role'] = $role;
        $user_data['date_naissance'] = $date_naissance;
        $user_data['nationalite'] = $nationalite;
        $user_data['passport_id'] = $passport_id;
        $user_data['date_inscription'] = date("Y-m-d H:i:s");
        $user_data['blocked'] = false;
        $user_data['vip'] = false;
        $user_data['last_login'] = "null";

        // Enregistrer dans le fichier JSON
        $json_file = "../json/users.json";
        $users = [];
        if (file_exists($json_file)) {
            $json_data = file_get_contents($json_file);
            $users = json_decode($json_data, true) ?? [];
        }

        $users[] = $user_data;
        file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));

        // Stocker les informations de passeport pour l'affichage
        $_SESSION['passport_info'] = [
            'name' => $user_data['first_name'] . ' ' . $user_data['last_name'],
            'passport_id' => $passport_id,
            'date_naissance' => $date_naissance,
            'nationalite' => $nationalite,
            'date_emission' => date("Y-m-d")
        ];

        // Nettoyer les données d'inscription
        unset($_SESSION['inscription']);

        // Redirection vers la page de passeport
        header("Location: passeport.php");
        exit();
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
    <style>
        .date-input {
            display: flex;
            align-items: center;
            background-color: #f8f8f8;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .date-input img {
            width: 25px;
            margin-right: 10px;
        }

        .date-input input {
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
            font-size: 16px;
        }

        .nationalite {
            display: flex;
            align-items: center;
            background-color: #f8f8f8;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .nationalite img {
            width: 25px;
            margin-right: 10px;
        }

        .nationalite input {
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
            font-size: 16px;
        }

        .generate-button {
            background: linear-gradient(to right, #e23636, #f78f3f);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 16px;
            margin-top: 10px;
        }

        .generate-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(226, 54, 54, 0.3);
        }
    </style>
</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/shield.svg" alt="shield pin" class="shield-pin">
            <img src="../img/svg/captain.svg" alt="captain pin" class="captain-pin">

            <a href="inscription.php" class="retour"><img src="../img/svg/fleche-gauche.svg" alt="fleche retour"></a>

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
                <div class="date-input">
                    <img src="../img/svg/calendar.svg" alt="Date Icon">
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>

                <div class="nationalite">
                    <img src="../img/svg/globe.svg" alt="Globe Icon">
                    <input type="text" id="nationalite" name="nationalite" placeholder="Nationalité" required>
                </div>

                <button class="generate-button" type="submit">
                    Générer mon passeport multiversel
                    <img src="../img/svg/shield.svg" alt="shield" style="width: 20px; height: 20px;">
                </button>
            </form>
        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>
</body>

</html>