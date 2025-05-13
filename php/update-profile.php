<?php
require('session.php');
check_auth('connexion.php');

// Préparer la réponse JSON
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Vérifier si les données du formulaire ont été soumises
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et valider les données
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation de base
    $errors = [];

    if (empty($first_name)) {
        $errors[] = "Le prénom ne peut pas être vide.";
    }

    if (empty($last_name)) {
        $errors[] = "Le nom ne peut pas être vide.";
    }

    if (empty($email)) {
        $errors[] = "L'email ne peut pas être vide.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }

    // Si aucune erreur, mettre à jour les informations
    if (empty($errors)) {
        // Chemin vers le fichier JSON des utilisateurs
        $users_file = '../json/users.json';
        $success = false;

        if (file_exists($users_file)) {
            // Lire le fichier
            $json_users = file_get_contents($users_file);
            $users = json_decode($json_users, true) ?: [];

            // Rechercher l'utilisateur et mettre à jour ses informations
            foreach ($users as &$user) {
                if ($user['email'] === $_SESSION['email']) {
                    // Mise à jour des données
                    $user['first_name'] = $first_name;
                    $user['last_name'] = $last_name;

                    // Si l'email a changé
                    if ($email !== $_SESSION['email']) {
                        // Vérifier que le nouvel email n'est pas déjà utilisé
                        $email_exists = false;
                        foreach ($users as $u) {
                            if ($u['email'] === $email && $u['email'] !== $_SESSION['email']) {
                                $email_exists = true;
                                break;
                            }
                        }

                        if ($email_exists) {
                            $response['message'] = "Cet email est déjà utilisé par un autre compte.";
                            echo json_encode($response);
                            exit;
                        }

                        $user['email'] = $email;
                    }

                    // Si le mot de passe a été modifié
                    if (!empty($password)) {
                        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }

                    $success = true;
                    break;
                }
            }

            // Sauvegarder les modifications
            if ($success) {
                file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

                // Mettre à jour la session
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;

                $response['success'] = true;
                $response['message'] = "Votre profil a été mis à jour avec succès!";
                $response['data'] = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email
                ];
            } else {
                $response['message'] = "Utilisateur non trouvé.";
            }
        } else {
            $response['message'] = "Erreur système: fichier utilisateurs introuvable.";
        }
    } else {
        // S'il y a des erreurs, les renvoyer
        $response['message'] = implode("<br>", $errors);
    }
} else {
    $response['message'] = "Méthode non autorisée.";
}

// Envoyer la réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>