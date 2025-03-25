<?php
// Méthode compatible avec tous les systèmes d'exploitation
$script_name = $_SERVER['SCRIPT_NAME'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];

// Déterminer quel est le chemin du dossier du projet par rapport à la racine du serveur web
$project_root = str_replace('\\', '/', dirname(dirname(__FILE__))); // Chemin absolu du projet (avec slash UNIX)
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Racine du serveur (avec slash UNIX)

// Obtenir le chemin relatif depuis la racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

// Construire l'URL de base
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path . '/';

// Debug - à supprimer en production
// echo "Debug - base_url: " . $base_url . "<br>";
// echo "Debug - document_root (normalisé): " . $document_root . "<br>";
// echo "Debug - project_root (normalisé): " . $project_root . "<br>";
// echo "Debug - relative_path: " . $relative_path . "<br>";
?>