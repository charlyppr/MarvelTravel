<?php
require('session.php');
$_SESSION['current_url'] = current_url();

// Variables pour le message de confirmation
$message_status = '';
$message_text = '';

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $objet = htmlspecialchars(trim($_POST['objet']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Création du nouveau message
    $nouveau_message = [
        'nom' => $nom,
        'email' => $email,
        'objet' => $objet,
        'message' => $message,
        'date' => date('Y-m-d H:i:s')
    ];

    // Lecture du fichier messages.json
    $messages = [];
    if (file_exists('../json/messages.json')) {
        $messages_json = file_get_contents('../json/messages.json');
        $messages = json_decode($messages_json, true) ?: [];
    }

    // Ajout du nouveau message
    $messages[] = $nouveau_message;

    // Sauvegarde dans le fichier
    if (file_put_contents('../json/messages.json', json_encode($messages, JSON_PRETTY_PRINT))) {
        $message_status = 'success';
        $message_text = 'Votre message a bien été envoyé !';
    } else {
        $message_status = 'error';
        $message_text = 'Une erreur est survenue lors de l\'envoi du message.';
    }
}

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contactez Marvel Travel pour toutes vos questions sur nos voyages">
    <title>Marvel Travel • Contact</title>

    <script src="../js/theme-loader.js"></script>
    <link rel="stylesheet" href="../css/theme.css" id="theme">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body class="<?php echo $theme; ?>-theme">

    <?php include 'nav.php'; ?>

    <main>
        <section class="contact-us">
            <div class="container">
                <div class="contact-container">
                    <div class="left">
                        <div class="contact-info">
                            <h1 class="contact-title"><span class="text-gradient">Contactez-nous</span></h1>
                            <p class="contact-description">Envoyez-nous un mail ou complétez le formulaire pour que nous
                                puissions vous aider dans votre prochaine aventure héroïque.</p>
                            <a href="mailto:contact@marveltravel.com" class="contact-email">contact@marveltravel.com</a>
                        </div>

                        <div class="support-section">
                            <div class="support-info-card">
                                <h3 class="support-title">Support client</h3>
                                <p class="support-description">Un problème avec ta réservation ? Un portail dimensionnel
                                    qui s'est refermé trop tôt ? On est là pour toi !</p>
                            </div>

                            <div class="support-info-card">
                                <h3 class="support-title">Feedback et Suggestions</h3>
                                <p class="support-description">Tu as une idée de voyage intergalactique ou tu penses
                                    qu'on devrait ajouter une escale à Wakanda ?</p>
                            </div>

                            <div class="support-info-card">
                                <h3 class="support-title">Partenariat</h3>
                                <p class="support-description">Tu veux t'associer avec nous pour créer des expériences
                                    héroïques ? On est prêts à unir nos forces, faisons équipe !</p>
                            </div>
                        </div>
                    </div>

                    <div class="right">
                        <img class="spiderman-pin no-invert" src="../img/svg/spiderman-pin.svg" alt="Épingle Spiderman">

                        <div class="header">
                            <h2 class="form-title">Un message pour nous ?</h2>
                            <p class="form-description">Un problème, une idée, vous pouvez tout nous dire</p>
                        </div>

                        <form action="contact.php" method="post" class="contact-form">
                            <?php if ($message_status): ?>
                                <div class="alert alert-<?php echo $message_status; ?>">
                                    <?php echo $message_text; ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            if (!isset($_SESSION['user'])) {
                                echo '<div class="form-row">
                                    <div class="form-group">
                                        <label for="nom" class="visually-hidden">Prénom</label>
                                        <input type="text" name="nom" id="nom" placeholder="Prénom" required autocomplete="nom">
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="visually-hidden">Email</label>
                                        <input type="email" name="email" id="email" placeholder="Email" required autocomplete="email">
                                    </div>
                                </div>';
                            } else {
                                echo '<div class="form-row">
                                    <div class="form-group">
                                        <label for="nom" class="visually-hidden">Prénom</label>
                                        <input type="text" name="nom" id="nom" value="' . $_SESSION['first_name'] . '" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="visually-hidden">Email</label>
                                        <input type="email" name="email" id="email" value="' . $_SESSION['email'] . '" readonly>
                                    </div>
                                </div>';
                            } ?>

                            <div class="form-group">
                                <label for="objet" class="visually-hidden">Objet</label>
                                <input type="text" name="objet" id="objet" placeholder="Objet" required
                                    autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="message" class="visually-hidden">Message</label>
                                <textarea name="message" id="message" placeholder="Message" required></textarea>
                            </div>

                            <div class="button-div">
                                <button type="submit" class="button-form">
                                    <span>Envoyer</span>
                                    <img src="../img/svg/avion.svg" alt="Envoyer">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="location-faq">
            <div class="container">
                <div class="location">
                    <div class="location-map-container">
                        <img class="location-map" src="../img/map.png" alt="Carte du Sanctum Sanctorum">
                    </div>

                    <div class="location-info">
                        <span class="location-sous-titre">Où nous trouver ?</span>
                        <h2 class="location-titre">Nous rejoindre de près ou de loin</h2>

                        <div class="location-details">
                            <h3 class="location-titre">Notre QG</h3>
                            <address class="location-description">
                                Marvel Travel • Sanctum Sanctorum <br>
                                New York, NY 10012<br>
                                177A Bleeker Street <br>
                                États-Unis
                            </address>
                            <a href="https://www.google.com/maps/search/?api=1&query=177A+Bleeker+Street,+New+York,+NY+10012"
                                target="_blank" class="redir-text">
                                Voir sur Google Maps
                                <img src="../img/svg/arrow-right.svg" alt="Flèche" class="no-invert">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="faq" id="faq">
                    <div class="faq-info">
                        <h2 class="faq-titre">FAQ</h2>
                        <p class="location-description">Des questions ? On y répond !</p>
                    </div>

                    <div class="questions-list">
                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Comment fonctionnent les voyages avec Marvel Travel ?</span>
                                <img src="../img/svg/chevron.svg" alt="Déplier" class="faq-icon">
                            </div>
                            <div class="faq-answer">
                                <p>Nos voyages sont conçus pour vous faire découvrir l'univers
                                    Marvel comme jamais. Nous organisons des circuits thématiques à travers des lieux
                                    emblématiques qui ont inspiré ou servi de décor aux aventures de vos héros préférés.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Y a-t-il des réductions ou des offres spéciales chez Marvel Travel ?</span>
                                <img src="../img/svg/chevron.svg" alt="Déplier" class="faq-icon">
                            </div>
                            <div class="faq-answer">
                                <p>Nous proposons régulièrement des offres spéciales et des
                                    réductions pour nos voyageurs fidèles. Inscrivez-vous à notre newsletter pour être
                                    informé de nos promotions exclusives et offres saisonnières.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>En quoi consiste Infinity Green ?</span>
                                <img src="../img/svg/chevron.svg" alt="Déplier" class="faq-icon">
                            </div>
                            <div class="faq-answer">
                                <p>Infinity Green est notre programme de tourisme responsable. Pour
                                    chaque voyage réservé, nous compensons l'empreinte carbone et contribuons à des
                                    projets
                                    environnementaux locaux, car même les super-héros doivent prendre soin de leur
                                    planète !</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question">
                                <span>Les voyages avec Marvel Travel sont-ils sécurisés ?</span>
                                <img src="../img/svg/chevron.svg" alt="Déplier" class="faq-icon">
                            </div>
                            <div class="faq-answer">
                                <p>Absolument ! La sécurité est notre priorité. Tous nos itinéraires
                                    sont soigneusement planifiés, et nos guides sont formés aux premiers secours. Nous
                                    assurons également une assistance 24/7 pendant toute la durée de votre voyage.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <img class="shield no-invert" src="../img/svg/shield.svg" alt="Shield logo">

    <?php include "footer.php"; ?>

    <script src="../js/faq.js"></script>
</body>

</html>