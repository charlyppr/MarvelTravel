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
                    $email_changed = false;
                    $old_email = '';
                    
                    if ($email !== $_SESSION['email']) {
                        $old_email = $_SESSION['email'];
                        $email_changed = true;
                        
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
                    
                    // Si l'email a été modifié, mettre à jour les autres fichiers JSON
                    if ($email_changed) {
                        // 1. Mettre à jour commandes.json
                        update_email_in_commandes($old_email, $email);
                        
                        // 2. Mettre à jour panier.json
                        update_email_in_panier($old_email, $email);
                        
                        // 3. Mettre à jour messages.json
                        update_email_in_messages($old_email, $email);
                    }
                    
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

/**
 * Met à jour l'email dans le fichier commandes.json
 * 
 * @param string $old_email Ancien email
 * @param string $new_email Nouvel email
 * @return void
 */
function update_email_in_commandes($old_email, $new_email) {
    $commandes_file = '../json/commandes.json';
    
    if (file_exists($commandes_file)) {
        $json_commandes = file_get_contents($commandes_file);
        $commandes = json_decode($json_commandes, true) ?: [];
        $updated = false;
        
        foreach ($commandes as &$commande) {
            if ($commande['acheteur'] === $old_email) {
                $commande['acheteur'] = $new_email;
                $updated = true;
            }
        }
        
        if ($updated) {
            file_put_contents($commandes_file, json_encode($commandes, JSON_PRETTY_PRINT));
        }
    }
}

/**
 * Met à jour l'email dans le fichier panier.json
 * 
 * @param string $old_email Ancien email
 * @param string $new_email Nouvel email
 * @return void
 */
function update_email_in_panier($old_email, $new_email) {
    $panier_file = '../json/panier.json';
    
    if (file_exists($panier_file)) {
        $json_panier = file_get_contents($panier_file);
        $panier = json_decode($json_panier, true) ?: [];
        
        // Vérifier si l'ancien email existe dans le panier
        if (isset($panier[$old_email])) {
            // Copier les données de l'ancien email vers le nouveau
            $panier[$new_email] = $panier[$old_email];
            
            // Supprimer l'ancien email
            unset($panier[$old_email]);
            
            // Sauvegarder les modifications
            file_put_contents($panier_file, json_encode($panier, JSON_PRETTY_PRINT));
        }
    }
}

/**
 * Met à jour l'email dans le fichier messages.json
 * 
 * @param string $old_email Ancien email
 * @param string $new_email Nouvel email
 * @return void
 */
function update_email_in_messages($old_email, $new_email) {
    $messages_file = '../json/messages.json';
    
    if (file_exists($messages_file)) {
        $json_messages = file_get_contents($messages_file);
        $messages = json_decode($json_messages, true) ?: [];
        $updated = false;
        
        foreach ($messages as &$message) {
            if ($message['email'] === $old_email) {
                $message['email'] = $new_email;
                $updated = true;
            }
        }
        
        if ($updated) {
            file_put_contents($messages_file, json_encode($messages, JSON_PRETTY_PRINT));
        }
    }
}

// Envoyer la réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>