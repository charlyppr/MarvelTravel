<?php
    session_start();

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
    function check_auth($path) {
        echo "vous devez vous connecter";
        if (!isset($_SESSION['user'])) {
            header("Location: $path");
            exit();
        }
    }

    function check_none_auth($path) {
        if (isset($_SESSION['user'])) {
            header("Location: $path");
            exit();
        }
    }

    function check_admin_auth($path){
        echo "vous devez vous connecter en tant qu'admin";
        if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
            header("Location: $path");
            exit();
        }
    }

    function log_out(){
        session_unset(); // Supprime toutes les variables de session
        session_destroy(); // Détruit la session
        header("Location: index.php"); // Redirection vers la page de connexion
        exit();
    }

    if(isset($_GET['logout'])){
        log_out();
    }

?>