<?php

$json_file = '../json/messages.json';
$message = $_POST['message'] ?? '';
$objet = $_POST['objet'] ?? '';
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';

$messages = json_decode(file_get_contents($json_file), true) ?? [];    $messages[] = [
    'nom' => $nom,
    'objet' => $objet,
    'email' => $email,
    'message' => $message,
    'date' => date('Y-m-d H:i:s')
];
file_put_contents($json_file, json_encode($messages, JSON_PRETTY_PRINT));

header("Location: ../php/retour_message.php?message=true");
?>