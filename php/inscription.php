<?php
require_once "session.php";

// Récupérer les données sauvegardées si elles existent
$login_civilite_value = $_SESSION['inscription']['civilite'] ?? '';
$login_firstname_value = $_SESSION['inscription']['first_name'] ?? '';
$login_lastname_value = $_SESSION['inscription']['last_name'] ?? '';
$login_mail_value = $_SESSION['inscription']['email'] ?? '';
// Ne pas récupérer le mot de passe pour des raisons de sécurité

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_civilite = trim($_POST['login_civilite'] ?? '');
    $login_firstname = ucfirst(strtolower(trim($_POST['login_firstname'] ?? '')));
    $login_lastname = ucfirst(strtolower(trim($_POST['login_lastname'] ?? '')));
    $login_mail = trim($_POST['login_mail'] ?? '');
    $login_pass = trim($_POST['login_pass'] ?? '');

    // Validation des données
    $inscri = 0;

    if ($login_civilite && $login_firstname && $login_lastname && $login_mail && $login_pass) {
        if (!filter_var($login_mail, FILTER_VALIDATE_EMAIL)) {
            $inscri = 3; // Email invalide
        } else {
            // Lire le fichier JSON existant
            $json_file = "../json/users.json";
            $users = [];
            if (file_exists($json_file)) {
                $json_data = file_get_contents($json_file);
                $users = json_decode($json_data, true) ?? [];
            }

            // Vérifier si l'email est déjà utilisé
            foreach ($users as $user) {
                if (isset($user['email']) && $user['email'] === $login_mail) {
                    $inscri = 2; // Email déjà utilisé
                    break;
                }
            }

            // Si tout est valide, stocker les informations en session et passer à l'étape 2
            if ($inscri == 0) {
                $_SESSION['inscription'] = [
                    'civilite' => $login_civilite,
                    'first_name' => $login_firstname,
                    'last_name' => $login_lastname,
                    'email' => $login_mail,
                    'password' => password_hash($login_pass, PASSWORD_BCRYPT),
                    'temp_pass' => $login_pass
                ];

                // Redirection vers l'étape 2
                header("Location: inscription-etape2.php");
                exit();
            }
        }
    } else {
        $inscri = 1; // Champs manquants
    }

    // Dans tous les cas d'erreur, sauvegarder les données saisies
    $_SESSION['inscription'] = [
        'civilite' => $login_civilite,
        'first_name' => $login_firstname,
        'last_name' => $login_lastname,
        'email' => $login_mail
    ];

    // Stocker le code d'erreur
    $_SESSION['inscri'] = $inscri;
    header("Location: inscription.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Inscription</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/shield.svg" alt="shield pin" class="shield-pin">
            <img src="../img/svg/captain.svg" alt="captain pin" class="captain-pin">

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
                <span class="titre-2">S'inscrire avec l'email</span>
                <span class="sous-titre-3">Première visite ? Obtenez votre Passeport Multiversel !</span>
            </div>

            <form class="form" action="inscription.php" method="post">

                <div class="civilite-container">
                    <div class="civilite">
                        <select name="login_civilite" id="login_civilite" required>
                            <option value="" disabled <?php echo empty($login_civilite_value) ? 'selected' : ''; ?>>
                                Civilité</option>
                            <option value="M" <?php echo $login_civilite_value === 'M' ? 'selected' : ''; ?>>Monsieur
                            </option>
                            <option value="Mme" <?php echo $login_civilite_value === 'Mme' ? 'selected' : ''; ?>>Madame
                            </option>
                            <option value="Autre" <?php echo $login_civilite_value === 'Autre' ? 'selected' : ''; ?>>Autre
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <input type="text" name="login_firstname" id="prenom" placeholder="Prénom" required
                        autocomplete="name" value="<?php echo htmlspecialchars($login_firstname_value); ?>">
                    <input type="text" name="login_lastname" id="nom" placeholder="Nom" required
                        autocomplete="family-name" value="<?php echo htmlspecialchars($login_lastname_value); ?>">
                </div>

                <div class="email">
                    <img src="../img/svg/email.svg" alt="Email Icon">
                    <input type="email" id="email" name="login_mail" placeholder="Email" required autocomplete="email"
                        value="<?php echo htmlspecialchars($login_mail_value); ?>">
                </div>

                <div class="mdp">
                    <img src="../img/svg/lock.svg" alt="Lock Icon">
                    <input type="password" id="mdp" name="login_pass" placeholder="Mot de passe" required>
                </div>

                <button class="next-button" type="submit">Suivant<img src="../img/svg/fleche-droite.svg"
                        alt="fleche"></button>

                <div class="other-text">
                    <a href='connexion.php'>Déjà membre chez nous ?&nbsp<span>Se connecter</span></a>
                    <?php
                    if (isset($_SESSION['inscri'])) {
                        switch ($_SESSION['inscri']) {
                            case 1:
                                echo "<p class='sous-titre-3'>Veuillez remplir tous les champs</p>";
                                break;
                            case 2:
                                echo "<p class='sous-titre-3'>Cette adresse email est déjà utilisée</p>";
                                break;
                            case 3:
                                echo "<p class='sous-titre-3'>Adresse email invalide</p>";
                                break;
                        }
                        unset($_SESSION['inscri']); // Supprime la variable après affichage
                    }
                    ?>
                </div>
            </form>

        </div>
    </div>

</body>

</html>