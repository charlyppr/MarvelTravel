<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    
    // Ne pas supprimer le cookie du thème, car cela pourrait donner une expérience utilisateur incohérente
    // Le thème sera automatiquement mis à jour lors de la prochaine connexion
    
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

// Fonction pour nettoyer les données de réservation
function clear_reservation_data()
{
    if (isset($_SESSION['reservation'])) {
        unset($_SESSION['reservation']);
    }
    if (isset($_SESSION['current_voyage_id'])) {
        unset($_SESSION['current_voyage_id']);
    }
}

// Fonction pour effacer les données d'une étape spécifique
function clear_form_data($step)
{
    if (isset($_SESSION['reservation_data']) && isset($_SESSION['reservation_data'][$step])) {
        unset($_SESSION['reservation_data'][$step]);
    }
}

function remove_query_param($param)
{
    $params = $_GET;
    unset($params[$param]);
    return '?' . http_build_query($params);
}

// Fonction pour charger le thème de l'utilisateur depuis users.json
function load_user_theme()
{
    if (isset($_SESSION['user']) && isset($_SESSION['email'])) {
        $userEmail = $_SESSION['email'];
        $json_file = dirname(__FILE__) . '/../json/users.json';
        
        if (file_exists($json_file)) {
            $users = json_decode(file_get_contents($json_file), true);
            if ($users) {
                foreach ($users as $user) {
                    if ($user['email'] === $userEmail && isset($user['theme'])) {
                        // Définir le cookie avec le thème de l'utilisateur
                        setcookie('theme', $user['theme'], time() + (30 * 24 * 60 * 60), '/');
                        return $user['theme'];
                    }
                }
            }
        }
    }
    
    // Par défaut, retourner le thème du cookie ou 'dark' si non défini
    return isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
}

?>