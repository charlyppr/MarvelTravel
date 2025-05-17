<?php
session_start();
require_once 'session.php';
$theme = load_user_theme();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Politique de Confidentialité</title>
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/legal-pages.css">
</head>
<body class="<?php echo $theme; ?>-theme">
    <?php include 'nav.php'; ?>

<main class="legal-content">
    <h1 class="titre">Politique de Confidentialité</h1>

    <div class="container">
        
        <section class="legal-section">
            <h2 class="sous-titre">Introduction</h2>
            <p>Chez Marvel Travel, nous accordons une grande importance à la protection de vos données personnelles. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations lorsque vous utilisez notre site web et nos services.</p>
            <p>En utilisant notre site, vous acceptez les pratiques décrites dans cette politique de confidentialité.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Informations collectées</h2>
            <p>Nous collectons différents types d'informations lorsque vous utilisez nos services :</p>
            <ul>
                <li><strong>Informations personnelles</strong> : nom, prénom, adresse email, numéro de téléphone, adresse postale, date de naissance.</li>
                <li><strong>Informations de paiement</strong> : numéro de carte bancaire, date d'expiration, cryptogramme visuel (nous ne stockons pas ces informations qui sont traitées par nos prestataires de paiement sécurisés).</li>
                <li><strong>Informations de navigation</strong> : cookies, durée de visite, préférences.</li>
                <li><strong>Informations de réservation</strong> : destinations, dates de voyage, nombre de voyageurs, préférences spéciales.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Utilisation des données</h2>
            <p>Nous utilisons vos informations pour les finalités suivantes :</p>
            <ul>
                <li>Fournir, exploiter et maintenir notre site web et nos services.</li>
                <li>Traiter et gérer vos réservations et vos achats.</li>
                <li>Vous envoyer des confirmations, des mises à jour et des alertes concernant vos voyages.</li>
                <li>Personnaliser votre expérience utilisateur et vous proposer des offres adaptées à vos intérêts.</li>
                <li>Améliorer notre site web, nos produits et services.</li>
                <li>Communiquer avec vous concernant nos offres, promotions et nouveautés (si vous avez consenti à recevoir ces communications).</li>
                <li>Détecter, prévenir et résoudre les problèmes techniques ou de sécurité.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Partage des données</h2>
            <p>Nous pouvons partager vos informations avec :</p>
            <ul>
                <li><strong>Nos partenaires</strong> : hôtels, compagnies aériennes, prestataires d'activités touristiques, uniquement dans la mesure nécessaire pour réaliser votre réservation.</li>
                <li><strong>Nos prestataires de services</strong> : sociétés de paiement, services informatiques, services d'analyse, services marketing, qui nous aident à fournir nos services.</li>
                <li><strong>Les autorités compétentes</strong> : si nous sommes tenus de le faire par la loi ou en réponse à des demandes légales valides.</li>
            </ul>
            <p>Nous ne vendons pas vos données personnelles à des tiers.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Cookies et technologies similaires</h2>
            <p>Nous utilisons des cookies et des technologies similaires pour améliorer votre expérience, analyser le trafic et personnaliser le contenu. Vous pouvez contrôler l'utilisation des cookies via les paramètres de votre navigateur.</p>
            <p>Nous utilisons les types de cookies suivants :</p>
            <ul>
                <li><strong>Cookies essentiels</strong> : nécessaires au fonctionnement du site.</li>
                <li><strong>Cookies de fonctionnalité</strong> : pour reconnaître vos préférences.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Sécurité des données</h2>
            <p>Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos informations personnelles contre tout accès non autorisé, altération, divulgation ou destruction.</p>
            <p>Cependant, aucune méthode de transmission sur Internet ou de stockage électronique n'est totalement sécurisée, et nous ne pouvons garantir la sécurité absolue de vos données.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Conservation des données</h2>
            <p>Nous conservons vos données personnelles aussi longtemps que nécessaire pour les finalités décrites dans cette politique de confidentialité, sauf si une période de conservation plus longue est requise ou permise par la loi.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Vos droits</h2>
            <p>Conformément aux lois applicables en matière de protection des données, vous disposez des droits suivants :</p>
            <ul>
                <li>Droit d'accès à vos données personnelles.</li>
                <li>Droit de rectification des données inexactes.</li>
                <li>Droit à l'effacement de vos données dans certaines circonstances.</li>
                <li>Droit à la limitation du traitement dans certaines circonstances.</li>
                <li>Droit à la portabilité des données.</li>
                <li>Droit d'opposition au traitement pour des raisons légitimes.</li>
                <li>Droit de retirer votre consentement à tout moment lorsque le traitement est basé sur le consentement.</li>
            </ul>
            <p>Pour exercer ces droits, veuillez nous contacter à l'adresse email suivante : <a href="mailto:contact@marveltravel.shop">contact@marveltravel.shop</a></p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Modifications de notre politique de confidentialité</h2>
            <p>Nous pouvons mettre à jour cette politique de confidentialité de temps à autre. Nous vous informerons de tout changement significatif en publiant la nouvelle politique sur cette page et, si nécessaire, par email.</p>
            <p>Nous vous encourageons à consulter régulièrement cette politique pour rester informé de la façon dont nous protégeons vos informations.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Contact</h2>
            <p>Pour toute question concernant cette politique de confidentialité, vous pouvez nous contacter à l'adresse suivante : <a href="mailto:contact@marveltravel.shop">contact@marveltravel.shop</a></p>
        </section>
    </div>
</main>

<?php
include 'footer.php';
?> 
</body>
</html>