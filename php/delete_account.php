<?php
require_once "session.php";

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

$email = $_SESSION['email'];

// 1. Supprimer l'utilisateur du fichier users.json
$users_file = "../json/users.json";
$users_data = [];

if (file_exists($users_file)) {
    $users_data = json_decode(file_get_contents($users_file), true);

    // Créer un nouveau tableau sans l'utilisateur actuel
    $new_users_data = [];
    foreach ($users_data as $user) {
        if (!isset($user['email']) || $user['email'] !== $email) {
            $new_users_data[] = $user;
        }
    }

    // Enregistrer le nouveau tableau
    file_put_contents($users_file, json_encode($new_users_data, JSON_PRETTY_PRINT));
}

// 2. Supprimer les commandes de l'utilisateur du fichier commandes.json
$commandes_file = "../json/commandes.json";
$commandes_data = [];

if (file_exists($commandes_file)) {
    $commandes_data = json_decode(file_get_contents($commandes_file), true);

    // Créer un nouveau tableau sans les commandes de l'utilisateur
    $new_commandes_data = [];
    foreach ($commandes_data as $commande) {
        if ($commande['acheteur'] !== $email) {
            $new_commandes_data[] = $commande;
        }
    }

    // Enregistrer le nouveau tableau
    file_put_contents($commandes_file, json_encode($new_commandes_data, JSON_PRETTY_PRINT));
}

// 3. Supprimer les messages de l'utilisateur du fichier messages.json
$messages_file = "../json/messages.json";
$messages_data = [];

if (file_exists($messages_file)) {
    $messages_data = json_decode(file_get_contents($messages_file), true);

    // Créer un nouveau tableau sans les messages de l'utilisateur
    $new_messages_data = [];
    foreach ($messages_data as $message) {
        if ($message['email'] !== $email) {
            $new_messages_data[] = $message;
        }
    }

    // Enregistrer le nouveau tableau
    file_put_contents($messages_file, json_encode($new_messages_data, JSON_PRETTY_PRINT));
}

// 4. Détruire la session et rediriger vers la page d'accueil
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte supprimé - Marvel Travel</title>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    <style>
        .container {
            max-width: 600px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #e23636;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .return-button {
            display: inline-block;
            background: linear-gradient(to right, #e23636, #518cca);
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .return-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <div class="default"></div>

    <div class="logo">
        <a href="../index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>
    </div>

    <div class="container">
        <h1>Votre compte a été supprimé</h1>
        <p>Toutes vos informations personnelles, réservations et messages ont été définitivement supprimés de notre base
            de données.</p>
        <p>Nous sommes désolés de vous voir partir. Vous êtes toujours le bienvenu si vous souhaitez revenir explorer le
            multivers Marvel avec nous.</p>
        <a href="../index.php" class="return-button">Retour à l'accueil</a>
    </div>

    <script src="../js/custom-cursor.js"></script>
    <script>
        // Rediriger vers la page d'accueil après 10 secondes
        setTimeout(function () {
            window.location.href = '../index.php';
        }, 10000);
    </script>
</body>

</html>