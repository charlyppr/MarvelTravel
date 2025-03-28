<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir une durée d'expiration pour la session (ex: 30 minutes)
$inactive = 1800; // 1800 secondes = 30 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    header("Location: connexion.php"); // Redirection vers la page de connexion
    exit();
}
$_SESSION['last_activity'] = time(); // Mettre à jour le timestamp de la session

// Empêcher l'accès si l'utilisateur n'est pas connecté (à mettre dans les pages protégées)
function check_auth($path)
{
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
        $_SESSION['current_url'] = current_url();
        header("Location: $path");
        exit();
    }
}

function check_none_auth($path)
{
    if (isset($_SESSION['user'])) {
        header("Location: $path");
        exit();
    }
}

function current_url()
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function check_admin_auth($path)
{
    if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
        header("Location: $path");
        exit();
    }
}

function log_out()
{
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    header("Location: ../index.php"); // Redirection vers la page de connexion
    exit();
}

// Ajouter ces fonctions

// Fonction pour stocker les données d'étape
function store_form_data($step, $data)
{
    if (!isset($_SESSION['reservation_data'])) {
        $_SESSION['reservation_data'] = [];
    }
    $_SESSION['reservation_data'][$step] = $data;
}

// Fonction pour récupérer les données d'étape
function get_form_data($step)
{
    if (isset($_SESSION['reservation_data']) && isset($_SESSION['reservation_data'][$step])) {
        return $_SESSION['reservation_data'][$step];
    }
    return null;
}

// Fonction pour effacer toutes les données de réservation en session
function clear_reservation_data()
{
    if (isset($_SESSION['reservation_data'])) {
        unset($_SESSION['reservation_data']);
    }
}

// Fonction pour effacer les données d'une étape spécifique
function clear_form_data($step)
{
    if (isset($_SESSION['reservation_data']) && isset($_SESSION['reservation_data'][$step])) {
        unset($_SESSION['reservation_data'][$step]);
    }
}

?>