<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Vérifier que l'utilisateur n'est pas admin
if ($_SESSION['role'] === 'admin') {
    $_SESSION['error'] = "Les administrateurs ne peuvent pas supprimer leur compte.";
    header('Location: profil.php');
    exit;
}

// Récupérer l'email de l'utilisateur
$userEmail = $_SESSION['email'];

// Supprimer les données utilisateurs de tous les fichiers
$success = true;

// 1. Suppression du compte dans users.json
$usersFile = '../json/users.json';
$users = json_decode(file_get_contents($usersFile), true);
$updatedUsers = array_filter($users, function($user) use ($userEmail) {
    return $user['email'] !== $userEmail;
});

// Si le nombre d'utilisateurs n'a pas changé, l'utilisateur n'a pas été trouvé
if (count($users) === count($updatedUsers)) {
    $_SESSION['error'] = "Utilisateur introuvable.";
    header('Location: profil.php');
    exit;
}

// 2. Suppression du panier dans panier.json
$panierFile = '../json/panier.json';
if (file_exists($panierFile)) {
    $paniers = json_decode(file_get_contents($panierFile), true);
    if (isset($paniers[$userEmail])) {
        unset($paniers[$userEmail]);
        $success = $success && file_put_contents($panierFile, json_encode($paniers, JSON_PRETTY_PRINT));
    }
}

// 3. Suppression des messages dans messages.json
$messagesFile = '../json/messages.json';
if (file_exists($messagesFile)) {
    $messages = json_decode(file_get_contents($messagesFile), true);
    $updatedMessages = array_filter($messages, function($message) use ($userEmail) {
        return $message['email'] !== $userEmail;
    });
    // Réindexer le tableau pour supprimer les clés numériques
    $updatedMessages = array_values($updatedMessages);
    $success = $success && file_put_contents($messagesFile, json_encode($updatedMessages, JSON_PRETTY_PRINT));
}

// 4. Suppression des commandes dans commandes.json
$commandesFile = '../json/commandes.json';
if (file_exists($commandesFile)) {
    $commandes = json_decode(file_get_contents($commandesFile), true);
    $updatedCommandes = array_filter($commandes, function($commande) use ($userEmail) {
        return $commande['acheteur'] !== $userEmail;
    });
    // Réindexer le tableau pour supprimer les clés numériques
    $updatedCommandes = array_values($updatedCommandes);
    $success = $success && file_put_contents($commandesFile, json_encode($updatedCommandes, JSON_PRETTY_PRINT));
}

// 5. Sauvegarder les données utilisateurs mises à jour
$updatedUsers = array_values($updatedUsers);
if ($success && file_put_contents($usersFile, json_encode($updatedUsers, JSON_PRETTY_PRINT))) {
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de connexion avec un message
    session_start();
    $_SESSION['success'] = "Votre compte a été supprimé avec succès.";
    header('Location: ../index.php');
    exit;
} else {
    $_SESSION['error'] = "Une erreur est survenue lors de la suppression du compte.";
    header('Location: ../index.php');
    exit;
} 