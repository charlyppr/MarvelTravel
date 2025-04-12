<?php
require_once "session.php";
check_none_auth($_SESSION['current_url'] ?? "../index.php");

// Récupérer l'email saisi précédemment (s'il existe)
$login_mail_value = $_SESSION['login_mail'] ?? '';

$message = ""; // Initialisation du message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_mail = trim($_POST['login_mail'] ?? '');
    $login_pass = trim($_POST['login_pass'] ?? '');

    // Mémoriser l'email saisi pour le réafficher en cas d'erreur
    $_SESSION['login_mail'] = $login_mail;

    $json_file = "../json/users.json";
    $connexion = 1; // 1 = erreur par défaut

    if (!empty($login_mail) && !empty($login_pass)) {
        if (file_exists($json_file)) {
            // Lire le fichier JSON et le convertir en tableau PHP
            $users = json_decode(file_get_contents($json_file), true) ?? [];
            foreach ($users as $user) {
                if ($user['email'] === $login_mail && password_verify($login_pass, $user['password'])) {
                    session_start();
                    $_SESSION['user'] = $login_mail;
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $login_mail;
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    // Ajouter ces lignes pour stocker toutes les données utilisateur
                    $_SESSION['civilite'] = $user['civilite'] ?? '';
                    $_SESSION['date_naissance'] = $user['date_naissance'] ?? '';
                    $_SESSION['nationalite'] = $user['nationalite'] ?? '';
                    $_SESSION['passport_id'] = $user['passport_id'] ?? '';

                    // En cas de connexion réussie, supprimer l'email mémorisé
                    unset($_SESSION['login_mail']);

                    // Mise à jour de la date de dernière connexion
                    $user['last_login'] = date("Y-m-d H:i:s");

                    // Enregistrer la mise à jour dans le fichier JSON
                    foreach ($users as $key => $u) {
                        if ($u['email'] === $login_mail) {
                            $users[$key]['last_login'] = $user['last_login'];
                            break;
                        }
                    }
                    file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT));

                    // Redirection
                    if (isset($_SESSION['current_url'])) {
                        header('Location: ' . $_SESSION['current_url']);
                    } else {
                        header('Location: ../index.php');
                    }
                    exit();
                }
            }
        }
    }

    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['connexion'] = $connexion;
    $_SESSION['role'] = $user['role'];
    header("Location: connexion.php"); // Redirection pour éviter le re-submit du formulaire
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Connexion</title>

    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>
    <div class="card">
        <div class="card-content">
            <img src="../img/svg/spiderman-pin.svg" alt="spiderman pin" class="spiderman-pin">
            <img src="../img/svg/hulk-pin.svg" alt="hulk-pin" class="hulk-pin">
            <a href="javascript:history.back()" class="retour"><img src="../img/svg/fleche-gauche.svg"
                    alt="fleche retour"></a>

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Se connecter avec l'email</span>
                <span class="sous-titre-3">Déjà explorateur ? Voyage avec nous dans le Multivers</span>
            </div>

            <form class="form" action="connexion.php" method="post">
                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="login_mail" placeholder="Email" required autocomplete="email"
                        value="<?php echo htmlspecialchars($login_mail_value); ?>">
                </div>

                <div class="mdp">
                    <img src="../img/svg/lock.svg" alt="Lock Icon">
                    <input type="password" id="password" name="login_pass" placeholder="Mot de passe" required>
                </div>

                <a href="#" class="redir-text">Mot de passe oublié dans la galaxie ?</a>

                <button class="next-button" type="submit">Entrer dans le Multivers<img src="../img/svg/sparkle.svg"
                        alt="etoile"></button>

                <div class="other-text">
                    <a href="inscription.php">Pas de passeport Multiversel ?&nbsp;<span>Créer un compte</span></a>
                </div>
                <div class="other-text">
                    <?php
                    // Affichage du message si une connexion a été tentée
                    if (isset($_SESSION['connexion'])) {
                        if ($_SESSION['connexion'] == 0) {
                            echo "<a href='../index.php'>Connexion réussi !&nbsp<span>Retour à l'acceuil</span></a>";
                        } elseif ($_SESSION['connexion'] == 1) {
                            echo "<p>Identifiant ou mot de passe incorrect</p>";
                        }
                        unset($_SESSION['connexion']); // Suppression de la variable après affichage
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
</body>

</html>