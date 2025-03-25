<?php
require('session.php');
$_SESSION['current_url'] = current_url();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Contact</title>

    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <section class="contact-us">
        <div class="contact-container">
            <div class="left">
                <div class="contact-info">
                    <span class="contact-title">Contactez - nous</span>
                    <span class="contact-description">Envoyer-nous un mail, ou complétez le formulaire pour qu’on puisse
                        vous aider</span>
                    <a href="mailto:contact@marveltravel.com" class="contact-email">contact@marveltravel.com</a>
                </div>

                <div class="support-section">
                    <div class="support-info">
                        <span class="support-title">Support client </span>
                        <span class="support-description">Un problème avec ta réservation ? Un portail dimensionnel qui
                            s’est refermé trop tôt ? On est là pour toi ! Contacte-nous, et on réglera ça.</span>
                    </div>

                    <div class="support-info">
                        <span class="support-title">Feedback et Suggestions</span>
                        <span class="support-description">Tu as une idée de voyage intergalactique ou tu penses qu’on
                            devrait ajouter une escale à Wakanda ? On adore les bonnes idées !</span>
                    </div>

                    <div class="support-info">
                        <span class="support-title">Partenariat</span>
                        <span class="support-description">Tu veux t’associer avec nous pour créer des expériences
                            héroïques ? On est prêts à unir nos forces, faisons équipe, comme les Avengers, mais en
                            costard !</span>
                    </div>
                </div>
            </div>

            <div class="right">
                <img class="spiderman-pin" src="../img/svg/spiderman-pin.svg" alt="spiderman-pin">

                <div class="header">
                    <span class="form-title">Un message pour nous ?</span>
                    <span class="form-description">Un problème, une idée, vous pouvez tout nous dire</span>
                </div>

                <form action="message.php" method="post" class="contact-form">
                    <?php
                    if (!isset($_SESSION['user'])) {
                        echo '<div class="form-row">
                            <input type="text" name="prenom" id="prenom" placeholder="Prénom" required autocomplete="name">
                            <input type="text" name="nom" id="nom" placeholder="Nom" required autocomplete="family-name">
                        </div>

                        <input type="email" name="email" id="email" placeholder="Email" required autocomplete="email">';
                    }else{
                        echo '<div class="form-row"><input type="hidden" name="prenom" value="'.$_SESSION['first_name'].'">
                        <input type="hidden" name="nom" value="'.$_SESSION['last_name'].'">
                        <input type="hidden" name="email" value="'.$_SESSION['email'].'"></div>';
                    }?>
                    <textarea name="message" id="message" placeholder="Message" required></textarea>

                    <div class="button-div">
                        <button type="submit" class="button-form">Envoyer<img src="../img/svg/avion.svg"
                                alt="avion"></button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="location-faq">
        <div class="location">
            <img class="location-map" src="../img/map.png" alt="map">

            <div class="location-info">
                <span class="location-sous-titre">Où nous trouver ?</span>
                <span class="location-titre">Nous rejoindre de près ou de loin</span>

                <div class="location-details">
                    <span class="location-titre">Notre QG</span>
                    <span class="location-description">Marvel Travel • Sanctum Sanctorum <br>New York, NY 10012
                        <br>177A Bleeker Street <br>États-Unis</span>
                    <a href="https://www.google.com/maps/search/?api=1&query=177A+Bleeker+Street,+New+York,+NY+10012"
                        target="_blank">Voir sur Google Maps</a>
                </div>

            </div>
        </div>

        <div class="faq">
            <div class="faq-info">
                <span class="faq-titre">FAQ</span>
                <span class="location-description">Des questions ? On y repond !</span>
            </div>

            <div class="questions-list">
                <div class="question">
                    <span class="question-title">Comment fonctionnent les voyages avec Marvel Travel ?</span>
                    <img src="../img/svg/chevron.svg" alt="chevron">
                </div>

                <div class="question">
                    <span class="question-title">Y a-t-il des réductions ou des offres spéciales chez Marvel Travel
                        ?</span>
                    <img src="../img/svg/chevron.svg" alt="chevron">
                </div>

                <div class="question">
                    <span class="question-title">En quoi consiste Infinity Green ?</span>
                    <img src="../img/svg/chevron.svg" alt="chevron">
                </div>

                <div class="question">
                    <span class="question-title">Les voyages avec Marvel Travel sont-ils sécurisés ?</span>
                    <img src="../img/svg/chevron.svg" alt="chevron">
                </div>
            </div>
        </div>

    </section>

    <img class="shield" src="../img/svg/shield.svg" alt="shield">

    <?php include "footer.php"; ?>

    <script src="../js/custom-cursor.js"></script>
    <script src="../js/nav.js"></script>
</body>

</html>