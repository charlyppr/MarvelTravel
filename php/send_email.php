<?php
// Inclure le fichier de configuration
require_once __DIR__ . '/config.php';

function send_password_reset_email($to_email, $to_name, $subject, $html_content) {
    // Configuration de Brevo API
    $api_key = BREVO_API_KEY;
    $api_url = "https://api.brevo.com/v3/smtp/email";
    
    // Préparation des données
    $data = [
        "sender" => [
            "name" => SENDER_NAME,
            "email" => SENDER_EMAIL
        ],
        "to" => [
            [
                "email" => $to_email,
                "name" => $to_name
            ]
        ],
        "subject" => $subject,
        "htmlContent" => $html_content
    ];
    
    // Conversion en JSON
    $payload = json_encode($data);
    
    // Configuration de cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "accept: application/json",
        "api-key: " . $api_key
    ]);
    
    // Exécution de la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Vérification du succès (codes 200 ou 201)
    return ($http_code == 200 || $http_code == 201);
} 
