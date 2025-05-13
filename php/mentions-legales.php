<?php
session_start();
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Mentions Légales</title>

    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/legal-pages.css">
</head>

<body class="<?php echo $theme; ?>-theme">
    <?php include 'nav.php'; ?>
    <main class="legal-content">
    <h1 class="titre">Mentions Légales</h1>

    <div class="container">
        
        <section class="legal-section">
            <h2 class="sous-titre">Informations sur l'entreprise</h2>
            <p>Marvel Travel, société fictive à responsabilité limitée</p>
            <p>Capital social : 100 000 Unités Galactiques</p>
            <p>Siège social : Tour Stark, 175 Avenue Tony Stark, 75008 Paris, France</p>
            <p>SIRET : 123 456 789 00012</p>
            <p>RCS Paris B 123 456 789</p>
            <p>N° TVA Intracommunautaire : FR 12 123456789</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Directeur de la publication</h2>
            <p>Nick Fury, Directeur de Marvel Travel</p>
            <p>Adresse email : nick.fury@marveltravel.com</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Hébergement du site</h2>
            <p>Le site Marvel Travel est hébergé par Wakanda Web Services</p>
            <p>Siège social : Cité de Birnin Zana, Wakanda</p>
            <p>Téléphone : +123 4567 8910</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Propriété intellectuelle</h2>
            <p>L'ensemble des éléments constituant le site Marvel Travel (textes, graphismes, logiciels, photographies, images, vidéos, sons, plans, noms, logos, marques, créations et œuvres protégeables diverses, bases de données, etc.) ainsi que le site lui-même, relèvent des législations françaises et internationales sur le droit d'auteur et la propriété intellectuelle.</p>
            <p>Ces éléments sont la propriété exclusive de Marvel Travel. L'internaute reconnaît et accepte que l'utilisation du site Marvel Travel ne lui confère aucun droit de propriété sur tout ou partie de ces éléments.</p>
            <p>En conséquence, l'internaute s'interdit de reproduire, représenter, modifier, publier, adapter, exploiter, traduire, sur quelque support que ce soit, tout ou partie des éléments et du site sans autorisation préalable écrite de Marvel Travel.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Protection des données personnelles</h2>
            <p>Conformément à la Loi Informatique et Libertés du 6 janvier 1978 modifiée par la loi du 6 août 2004, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.</p>
            <p>Vous pouvez exercer ce droit en envoyant un email à l'adresse suivante : privacy@marveltravel.com ou par courrier postal à l'adresse du siège social indiquée ci-dessus.</p>
            <p>Pour plus d'informations concernant la façon dont nous traitons vos données personnelles, veuillez consulter notre <a href="confidentialite.php">Politique de Confidentialité</a>.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Cookies</h2>
            <p>Le site Marvel Travel utilise des cookies pour améliorer l'expérience utilisateur. Pour plus d'informations sur notre utilisation des cookies, veuillez consulter notre <a href="confidentialite.php">Politique de Confidentialité</a>.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Limitation de responsabilité</h2>
            <p>Marvel Travel s'efforce d'assurer au mieux de ses possibilités l'exactitude et la mise à jour des informations diffusées sur son site, dont elle se réserve le droit de corriger, à tout moment et sans préavis, le contenu.</p>
            <p>Toutefois, Marvel Travel ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à la disposition sur son site.</p>
            <p>En conséquence, Marvel Travel décline toute responsabilité :</p>
            <ul>
                <li>Pour toute imprécision, inexactitude ou omission portant sur des informations disponibles sur le site ;</li>
                <li>Pour tous dommages résultant d'une intrusion frauduleuse d'un tiers ayant entraîné une modification des informations mises à la disposition sur le site ;</li>
                <li>Et plus généralement, pour tous dommages, directs ou indirects, qu'elles qu'en soient les causes, origines, nature ou conséquences, provoqués en raison de l'accès de quiconque au site ou de l'impossibilité d'y accéder.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Loi applicable et juridiction compétente</h2>
            <p>Les présentes mentions légales sont régies par la loi française. En cas de litige, les tribunaux français seront seuls compétents.</p>
        </section>
        
        <section class="legal-section">
            <h2 class="sous-titre">Contact</h2>
            <p>Pour toute question concernant ces mentions légales, vous pouvez nous contacter à l'adresse suivante : <a href="mailto:support@marveltravel.com">support@marveltravel.com</a></p>
        </section>
    </div>
</main>

<?php
include 'footer.php';
?> 
</body>

</html>