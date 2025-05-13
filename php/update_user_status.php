<?php
require('session.php');
check_admin_auth('connexion.php');

// Préparer la réponse JSON
$response = [
    'success' => false,
    'message' => ''
];

// Vérifier si les données ont été soumises via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données en JSON
    $input_data = json_decode(file_get_contents('php://input'), true);
    
    // Valider les données reçues
    if (!isset($input_data['email']) || (!isset($input_data['status']) && !isset($input_data['vip']))) {
        $response['message'] = "Données incomplètes.";
    } else {
        $email = $input_data['email'];
        $status = $input_data['status'] ?? null;
        $vip = isset($input_data['vip']) ? (bool)$input_data['vip'] : null;
        
        // Chemin vers le fichier JSON des utilisateurs
        $users_file = '../json/users.json';
        
        if (file_exists($users_file)) {
            // Lire le fichier
            $json_users = file_get_contents($users_file);
            $users = json_decode($json_users, true) ?: [];
            $updated = false;
            
            // Rechercher l'utilisateur
            foreach ($users as &$user) {
                if ($user['email'] === $email) {
                    // Vérifier si on essaie de mettre en VIP un utilisateur bloqué
                    if ($vip === true && ($user['blocked'] || ($status === 'blocked'))) {
                        $response['message'] = "Impossible d'attribuer le statut VIP à un utilisateur bloqué.";
                        echo json_encode($response);
                        exit;
                    }
                    
                    // Mise à jour du statut si spécifié
                    if ($status !== null) {
                        $user['blocked'] = ($status === 'blocked');
                        
                        // Si on bloque un utilisateur, on lui retire automatiquement le statut VIP
                        if ($status === 'blocked' && $user['vip']) {
                            $user['vip'] = false;
                        }
                    }
                    
                    // Mise à jour du statut VIP si spécifié
                    if ($vip !== null) {
                        $user['vip'] = $vip;
                    }
                    
                    $updated = true;
                    break;
                }
            }
            
            // Sauvegarder les modifications
            if ($updated) {
                file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
                
                $response['success'] = true;
                
                // Définir le message approprié
                if ($status !== null) {
                    if ($status === 'blocked') {
                        $response['message'] = "Utilisateur bloqué avec succès" . 
                                              ($user['vip'] ? " et statut VIP retiré" : "");
                    } else {
                        $response['message'] = "Utilisateur débloqué avec succès";
                    }
                } else if ($vip !== null) {
                    $response['message'] = $vip 
                        ? "Statut VIP ajouté avec succès" 
                        : "Statut VIP retiré avec succès";
                }
            } else {
                $response['message'] = "Utilisateur non trouvé.";
            }
        } else {
            $response['message'] = "Erreur système: fichier utilisateurs introuvable.";
        }
    }
} else {
    $response['message'] = "Méthode non autorisée.";
}

// Envoyer la réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 