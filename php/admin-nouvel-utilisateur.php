<?php
// Démarrer la session si ce n'est pas déjà fait
session_start();

// Inclure le fichier session.php pour accéder à la fonction load_user_theme
require_once 'session.php';

$page_title = "Création d'utilisateur";
$message = "";
$message_type = "";

// Vérifier si l'utilisateur est connecté et est administrateur
if ($_SESSION['role'] !== 'admin') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas admin
    header('Location: connexion.php');
    exit;
}

// Traiter le formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $civilite = trim($_POST['civilite'] ?? '');
    $first_name = ucfirst(strtolower(trim($_POST['first_name'] ?? '')));
    $last_name = ucfirst(strtolower(trim($_POST['last_name'] ?? '')));
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $nationalite = trim($_POST['nationalite'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $vip = isset($_POST['vip']) ? true : false;

    // Validation de base
    if ($civilite && $first_name && $last_name && $email && $password && $date_naissance && $nationalite) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "L'adresse email n'est pas valide";
            $message_type = "error";
        } else {
            // Lire le fichier JSON existant
            $json_file = "../json/users.json";
            $users = [];
            if (file_exists($json_file)) {
                $json_data = file_get_contents($json_file);
                $users = json_decode($json_data, true) ?? [];
            }

            // Vérifier si l'email est déjà utilisé
            $email_exists = false;
            foreach ($users as $user) {
                if (isset($user['email']) && $user['email'] === $email) {
                    $email_exists = true;
                    break;
                }
            }

            if ($email_exists) {
                $message = "Cette adresse email est déjà utilisée";
                $message_type = "error";
            } else {
                // Générer un ID unique de passeport (10 chiffres)
                $passport_id = mt_rand(1000000000, 9999999999);
                
                // Préparer le nouvel utilisateur
                $newUser = [
                    'civilite' => $civilite,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'date_naissance' => $date_naissance,
                    'nationalite' => $nationalite,
                    'role' => $role,
                    'passport_id' => $passport_id,
                    'date_inscription' => date("Y-m-d H:i:s"),
                    'blocked' => false,
                    'vip' => $vip,
                    'last_login' => "null"
                ];

                // Ajouter le nouvel utilisateur
                $users[] = $newUser;
                
                // Sauvegarder dans le fichier JSON
                if (file_put_contents($json_file, json_encode($users, JSON_PRETTY_PRINT))) {
                    $message = "L'utilisateur a été créé avec succès";
                    $message_type = "success";
                    
                    // Réinitialiser les valeurs du formulaire
                    $civilite = $first_name = $last_name = $email = $password = $date_naissance = $nationalite = "";
                    $role = "user";
                    $vip = false;
                } else {
                    $message = "Erreur lors de la création de l'utilisateur";
                    $message_type = "error";
                }
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires";
        $message_type = "error";
    }
}

// Récupérer le thème de la session
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Création d'utilisateur</title>
    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="stylesheet" href="../css/form-validation.css">
    <link rel="stylesheet" href="../css/admin-new.css">
</head>

<body class="<?php echo $theme; ?>-theme">
    <?php include 'sidebar.php'; ?>
        
    <div class="user-creation-container">
        <div class="card-content">
            <div class="card-header">
                <h2 class="titre-2">Créer un nouvel utilisateur</h2>
                <p class="sous-titre-3">Remplissez le formulaire pour ajouter un nouvel utilisateur au système</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form class="form" action="admin-nouvel-utilisateur.php" method="post">
                <div class="form-section">
                    <h3 class="form-section-title">Informations personnelles</h3>
                    
                    <div class="civilite-container">
                        <div class="civilite">
                            <select name="civilite" id="civilite" required>
                                <option value="" <?php echo empty($civilite) ? 'selected' : ''; ?>>Civilité</option>
                                <option value="M" <?php echo isset($civilite) && $civilite === 'M' ? 'selected' : ''; ?>>Monsieur</option>
                                <option value="Mme" <?php echo isset($civilite) && $civilite === 'Mme' ? 'selected' : ''; ?>>Madame</option>
                                <option value="Autre" <?php echo isset($civilite) && $civilite === 'Autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <input type="text" name="first_name" placeholder="Prénom" required 
                            value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                        <input type="text" name="last_name" placeholder="Nom" required
                            value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                    </div>

                    <div class="email">
                        <img src="../img/svg/email.svg" alt="Email Icon">
                        <input type="email" name="email" placeholder="Email" required
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" autocomplete="off">
                    </div>

                    <div class="mdp">
                        <img src="../img/svg/lock.svg" alt="Lock Icon">
                        <input type="password" name="password" placeholder="Mot de passe" required autocomplete="new-password">
                        <button type="button" class="password-toggle-btn" title="Afficher le mot de passe">
                            <img src="../img/svg/eye-slash.svg" alt="Afficher le mot de passe" class="eye-icon">
                        </button>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Informations supplémentaires</h3>
                    
                    <div class="date-input-container">
                        <label for="date_naissance">Date de naissance</label>
                        <div class="date-input">
                            <img src="../img/svg/calendar.svg" alt="Date Icon">
                            <input type="date" id="date_naissance" name="date_naissance" required
                                max="<?php echo date('Y-m-d'); ?>" 
                                value="<?php echo isset($date_naissance) ? htmlspecialchars($date_naissance) : ''; ?>">
                        </div>
                    </div>

                    <div class="nationalite-container">
                        <div class="nationalite">
                            <img src="../img/svg/globe.svg" alt="Globe Icon">
                            <select name="nationalite" id="nationalite" required>
                                <option value="" disabled <?php echo empty($nationalite) ? 'selected' : ''; ?>>Nationalité</option>
                                <option value="Wakandaise" <?php echo isset($nationalite) && $nationalite === 'Wakandaise' ? 'selected' : ''; ?>>Wakandaise</option>
                                <option value="Asgardienne" <?php echo isset($nationalite) && $nationalite === 'Asgardienne' ? 'selected' : ''; ?>>Asgardienne</option>
                                <option value="Xandarienne" <?php echo isset($nationalite) && $nationalite === 'Xandarienne' ? 'selected' : ''; ?>>Xandarienne</option>
                                <option value="Kree" <?php echo isset($nationalite) && $nationalite === 'Kree' ? 'selected' : ''; ?>>Kree</option>
                                <option value="Skrull" <?php echo isset($nationalite) && $nationalite === 'Skrull' ? 'selected' : ''; ?>>Skrull</option>
                                <option value="Française" <?php echo isset($nationalite) && $nationalite === 'Française' ? 'selected' : ''; ?>>Française</option>
                                <option value="Américaine" <?php echo isset($nationalite) && $nationalite === 'Américaine' ? 'selected' : ''; ?>>Américaine</option>
                                <option value="Britannique" <?php echo isset($nationalite) && $nationalite === 'Britannique' ? 'selected' : ''; ?>>Britannique</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Paramètres du compte</h3>
                    
                    <select name="role" class="role-select" required>
                        <option value="user" <?php echo (!isset($role) || $role === 'user') ? 'selected' : ''; ?>>Utilisateur standard</option>
                        <option value="admin" <?php echo isset($role) && $role === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    </select>

                    <div class="checkbox-container">
                        <input type="checkbox" id="vip" name="vip" <?php echo isset($vip) && $vip ? 'checked' : ''; ?>>
                        <label for="vip">Statut VIP</label>
                    </div>
                </div>

                <button class="next-button" type="submit">Créer l'utilisateur<img src="../img/svg/fleche-droite.svg" alt="fleche"></button>
            </form>
        </div>
    </div>

    <script src="../js/password-toggle.js"></script>
    <script src="../js/form-validation.js"></script>
    <script src="../js/admin-validation.js"></script>
</body>
</html>