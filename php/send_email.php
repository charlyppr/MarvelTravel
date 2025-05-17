<?php
// Inclure le fichier de configuration
require_once 'config.php';

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

function send_welcome_email($to_email, $to_name, $passport_id) {
    $subject = "Bienvenue dans le Multivers Marvel Travel !";
    
    // Création du contenu HTML de l'email
    $htmlContent = "
    <html>
    <head>
        <title>Bienvenue chez Marvel Travel</title>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f7f7f7;'>
        <div style='max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <!-- Header -->
            <div style='text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eeeeee;'>
                <img src='http://{$_SERVER['HTTP_HOST']}/MarvelTravel/img/svg/logo.svg' alt='Marvel Travel Logo' style='height: 60px; margin-bottom: 15px;'>
            </div>
            
            <!-- Content -->
            <div style='color: #444444; line-height: 1.6; font-size: 16px;'>
                <h2 style='color: #222222; margin-top: 0;'>Votre aventure commence maintenant</h2>
                
                <p style='margin-bottom: 20px;'>Bonjour <strong>{$to_name}</strong>,</p>
                
                <p style='margin-bottom: 20px;'>Nous sommes ravis de vous accueillir parmi les voyageurs du Multivers Marvel ! Votre passeport multiversel <strong>{$passport_id}</strong> a été activé avec succès.</p>
                
                <div style='background-color: #f9f9f9; border-left: 4px solid #e23636; padding: 15px; margin: 25px 0;'>
                    <h3 style='margin-top: 0; color: #e23636;'>Cadeau de bienvenue</h3>
                    <p style='margin-bottom: 10px;'>Utilisez le code promo <strong style='background-color: #e23636; color: white; padding: 5px 10px; border-radius: 4px;'>MARVEL10</strong> pour bénéficier de 10% de réduction sur votre première réservation !</p>
                    <p style='font-size: 14px; margin-bottom: 0;'>Valable pendant 30 jours à compter d'aujourd'hui.</p>
                </div>
                
                <h3 style='color: #222222; margin-top: 30px;'>Que pouvez-vous faire maintenant ?</h3>
                
                <ul style='padding-left: 20px;'>
                    <li style='margin-bottom: 10px;'>Explorer nos <strong>destinations</strong> à travers le Multivers Marvel</li>
                    <li style='margin-bottom: 10px;'>Découvrir les <strong>héros</strong> qui vous accompagneront dans vos voyages</li>
                    <li style='margin-bottom: 10px;'>Réserver votre <strong>première aventure</strong> en utilisant votre code promo</li>
                    <li style='margin-bottom: 10px;'>Compléter votre <strong>profil</strong> pour des recommandations personnalisées</li>
                </ul>
                
                <div style='margin: 30px 0; padding: 20px; border: 1px solid #eee; border-radius: 6px;'>
                    <h4 style='margin-top: 0; color: #333;'>Les règles du voyageur multiversel</h4>
                    <ol style='padding-left: 20px; margin-bottom: 0;'>
                        <li style='margin-bottom: 8px;'>Respectez les lois et coutumes de chaque univers visité</li>
                        <li style='margin-bottom: 8px;'>Ne modifiez pas la chronologie des événements</li>
                        <li style='margin-bottom: 8px;'>N'interférez pas avec les missions des super-héros</li>
                        <li style='margin-bottom: 8px;'>Gardez votre passeport multiversel avec vous à tout moment</li>
                        <li style='margin-bottom: 0;'>Amusez-vous et profitez de l'immensité du Multivers Marvel !</li>
                    </ol>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://{$_SERVER['HTTP_HOST']}/MarvelTravel/index.php' style='display: inline-block; padding: 12px 24px; background-color: #e23636; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);'>Commencer l'aventure</a>
                </div>
            </div>
            
            <!-- Footer -->
            <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee; text-align: center; color: #777777; font-size: 14px;'>
                <p>&copy; 2025 Marvel Travel. Tous droits réservés.</p>
                <div style='margin-top: 15px;'>
                    <img src='http://{$_SERVER['HTTP_HOST']}/MarvelTravel/img/svg/spiderman-pin.svg' alt='Spiderman pin' style='height: 40px; margin-bottom: 15px;'>
                </div>
                <p style='margin-top: 15px;'>Pour toute question, contactez notre support client. <a href='mailto:contact@marveltravel.shop'>contact@marveltravel.shop</a></p>
            </div>
        </div>
    </body>
    </html>";
    
    // Envoi de l'email
    return send_password_reset_email($to_email, $to_name, $subject, $htmlContent);
} 
