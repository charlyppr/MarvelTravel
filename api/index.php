<?php
// API Entry Point pour Vercel
// Redirige vers le fichier d'accueil principal

// Définir les chemins pour compatibilité
define('PROJECT_ROOT', dirname(__DIR__));

// Charger les fichiers PHP depuis le répertoire principal
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Si c'est une requête API
if (strpos($request_uri, '/api/') === 0) {
    // Traiter comme une API
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
} else {
    // Rediriger vers le répertoire public
    include PROJECT_ROOT . '/index.php';
}
?>
