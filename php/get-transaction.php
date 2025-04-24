<?php
// Inclure le fichier de session pour gérer l'authentification
require_once('session.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Utilisateur non connecté']));
}

// Vérifier si l'ID de transaction est fourni
if (!isset($_GET['transaction']) || empty($_GET['transaction'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'ID de transaction manquant']));
}

$transaction_id = htmlspecialchars($_GET['transaction']);
$json_file_path = '../json/commandes.json';

// Vérifier si le fichier des commandes existe
if (!file_exists($json_file_path) || !is_readable($json_file_path)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Fichier de commandes introuvable']));
}

// Lire le contenu du fichier
$json_file = file_get_contents($json_file_path);
$commandes = json_decode($json_file, true);

if (!is_array($commandes)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit(json_encode(['error' => 'Format de commandes invalide']));
}

// Rechercher la transaction
$transaction = null;
foreach ($commandes as $commande) {
    if ($commande['transaction'] === $transaction_id) {
        // Vérifier que l'utilisateur est le propriétaire de cette commande ou qu'il est administrateur
        if (isset($_SESSION['email']) && ($_SESSION['email'] === $commande['acheteur'] || $_SESSION['role'] === 'admin')) {
            $transaction = $commande;
            break;
        } else {
            header('HTTP/1.1 403 Forbidden');
            exit(json_encode(['error' => 'Accès non autorisé à cette transaction']));
        }
    }
}

// Vérifier si la transaction a été trouvée
if ($transaction === null) {
    header('HTTP/1.1 404 Not Found');
    exit(json_encode(['error' => 'Transaction introuvable']));
}

// Si tout est ok, retourner les informations de la transaction
header('Content-Type: application/json');
echo json_encode($transaction);
exit; 