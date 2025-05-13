<?php
session_start();

// Liste des fichiers JSON autorisés
$allowed_files = [
    'voyages',
    'panier',
    'commandes',
    'messages'
];

// Vérifier si l'utilisateur est connecté pour certains fichiers sensibles
$restricted_files = ['users', 'commandes', 'messages'];

// Le paramètre file doit être spécifié
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Fichier non spécifié']);
    exit;
}

$file = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['file']);

// Vérifier si le fichier est autorisé
if (!in_array($file, $allowed_files)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Vérifier les restrictions d'accès pour certains fichiers
if (in_array($file, $restricted_files) && (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true)) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Authentification requise']);
    exit;
}

// Vérifier si l'utilisateur est administrateur pour le fichier users
if ($file === 'users' && (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Accès administrateur requis']);
    exit;
}

// Chemin du fichier JSON
$json_path = "../json/{$file}.json";

// Vérifier si le fichier existe
if (!file_exists($json_path)) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Fichier non trouvé']);
    exit;
}

// Lire et retourner le contenu JSON
$json_content = file_get_contents($json_path);
header('Content-Type: application/json');
echo $json_content;
?> 