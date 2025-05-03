<?php
require('php/session.php');
$_SESSION['current_url'] = $_SERVER['REQUEST_URI'];

// Récupérer le thème depuis le cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Charger le script de thème en premier -->
    <script src="js/theme-loader.js"></script>

    <!-- Charger le fichier de thème approprié -->
    <link rel="stylesheet" href="css/theme.css" id="theme">

    <link rel="stylesheet" href="css/index.css">
    <link rel="shortcut icon" href="img/svg/spiderman-pin.svg" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600&display=swap" rel="stylesheet">

    <title>Marvel Travel • Accueil</title>

</head>

<body class="<?php echo $theme; ?>-theme">

    <?php include 'php/nav.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Voyagez dans l'univers Marvel</h1>
            <p>Découvrez des destinations uniques inspirées par vos super-héros préférés et vivez des aventures extraordinaires.</p>
            <div class="hero-cta">
                <a href="php/destination.php" class="cta-button">Planifier mon voyage</a>
                <a href="#" class="secondary-link">Découvrir les destinations <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
            </div>
        </div>
        <div class="hero-image">
            <div class="card-stack">
                <!-- Carte Marvel principale fixe en dessous -->
                <div class="marvel-main-card">
                    <div class="marvel-logo"></div>
                </div>
                
                <!-- Cartes de destinations qui défilent au-dessus -->
                <div class="destination-card" data-title="Wakanda" data-img="img/destinations/wakanda.jpg">
                    <img src="img/destinations/wakanda.jpg" alt="Wakanda" />
                </div>
                <div class="destination-card" data-title="Asgard" data-img="img/destinations/asgard.jpg">
                    <img src="img/destinations/asgard.jpg" alt="Asgard" />
                </div>
                <div class="destination-card" data-title="Atlantis" data-img="img/destinations/atlantis.jpg">
                    <img src="img/destinations/atlantis.jpg" alt="Atlantis" />
                </div>
                <div class="destination-card" data-title="Titan" data-img="img/destinations/titan.jpg">
                    <img src="img/destinations/titan.jpg" alt="Titan" />
                </div>
                <div class="destination-card" data-title="Genosha" data-img="img/destinations/genosha.jpg">
                    <img src="img/destinations/genosha.jpg" alt="Genosha" />
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">15+</span>
                    <span class="stat-label">Destinations uniques</span>
                </div>
                <div class="stat">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">Voyageurs satisfaits</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Section confiance -->
    <section class="trust-section">
        <div class="container">
            <h2 class="trust-title">Ils nous font confiance</h2>
            <div class="companies-grid">
                <div class="company-logo"><img src="img/companies/stark.png" alt="Stark Industries"></div>
                <div class="company-logo"><img src="img/companies/oscorp.png" alt="Oscorp"></div>
                <div class="company-logo"><img src="img/companies/pym.png" alt="Pym Technologies"></div>
                <div class="company-logo"><img src="img/companies/nova.png" alt="Nova Corp"></div>
                <div class="company-logo"><img src="img/companies/ravager.png" alt="Ravagers"></div>
            </div>
        </div>
    </section>

    <!-- Section à propos -->
    <section class="about-section">
        <div class="about-card">
            <div class="about-content">
                <h2 class="about-title">Chez Marvel Travel, nous vous invitons à explorer un monde où le voyage n'est pas seulement vécu—il est ressenti.</h2>
                
                <p class="about-text">
                    Découvrez l'attrait d'une aventure extraordinaire, vivez l'instant magique où le rêve devient réalité. 
                    Ces moments uniques sont le fruit de notre passion pour l'univers Marvel et d'une expertise développée 
                    depuis des années. En combinant votre amour pour les super-héros avec notre expertise en voyages, 
                    nous créons des expériences inoubliables qui résonnent vraiment avec l'univers qui nous entoure.
                </p>
                
                <video class="signature-video" autoplay muted playsinline>
                    <source src="img/signature.webm" type="video/webm">
                    <img src="img/svg/signature.svg" alt="Signature" class="signature-img">
                </video>
                
                <div class="signature-footer">
                    <div class="signature-info">
                        <p class="signature-name">Paul, Ibrahima, Charly</p>
                        <p class="signature-title">Fondateurs</p>
                    </div>
                    <a href="#" class="story-button">Notre Histoire <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Destinations Populaires -->
    <section class="popular-destinations">
        <div class="container">
            <h2 class="section-title">Destinations Populaires</h2>
            <div class="destinations-grid">
                <div class="destination-item postcard">
                    <div class="postcard-image">
                        <img src="img/destinations/wakanda.jpg" alt="Wakanda">
                    </div>
                    <div class="postcard-content">
                        <h3 class="destination-name">Wakanda Forever</h3>
                        <p class="destination-description">
                            Découvrez la nation africaine cachée, avec son vibranium, ses tribus locales et sa Cité d'Or futuriste. 
                            Un voyage idéal pour les passionnés de culture et de technologie.
                        </p>
                        <a href="php/voyage-detail.php?id=1" class="postcard-link">Découvrir <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
                    </div>
                </div>
                
                <div class="destination-item postcard">
                    <div class="postcard-image">
                        <img src="img/destinations/asgard.jpg" alt="Asgard">
                    </div>
                    <div class="postcard-content">
                        <h3 class="destination-name">Asgard</h3>
                        <p class="destination-description">
                            Explorez le royaume doré d'Asgard, avec le Bifrost, le palais d'Odin et les célébrations asgardiennes. 
                            Idéal pour les passionnés de mythologie et d'aventure.
                        </p>
                        <a href="php/voyage-detail.php?id=4" class="postcard-link">Découvrir <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
                    </div>
                </div>
                
                <div class="destination-item postcard">
                    <div class="postcard-image">
                        <img src="img/destinations/new-york.jpg" alt="New York">
                    </div>
                    <div class="postcard-content">
                        <h3 class="destination-name">New York</h3>
                        <p class="destination-description">
                            Explorez New York avec Marvel. Vous verrez la Tour Stark, le Sanctum Sanctorum et les sites de Spider-Man. 
                            Une aventure urbaine dans les lieux de vos super-héros préférés.
                        </p>
                        <a href="php/voyage-detail.php?id=0" class="postcard-link">Découvrir <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
                    </div>
                </div>
            </div>
            <div class="destinations-cta">
                <a href="php/destination.php" class="view-all-button">Voir toutes les destinations <span class="arrow"><svg width="30" height="25" viewBox="0 0 30 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_505_3993)"><path d="M29.6545 12.0471C29.6545 11.6384 29.4829 11.2388 29.1704 10.9385L18.7505 0.510739C18.3848 0.145018 18.0171 0 17.636 0C16.79 0 16.1675 0.609126 16.1675 1.4265C16.1675 1.84496 16.3279 2.21165 16.6047 2.48411L20.708 6.63524L26.2373 11.7383L26.7114 10.8359L21.5515 10.562H1.50145C0.615717 10.562 0 11.1667 0 12.0471C0 12.9153 0.615717 13.5322 1.50145 13.5322H21.5515L26.7114 13.2461L26.2373 12.3559L20.708 17.4568L16.6047 21.5957C16.3379 21.8704 16.1675 22.2449 16.1675 22.6655C16.1675 23.4807 16.79 24.0898 17.636 24.0898C18.0171 24.0898 18.3747 23.9348 18.7004 23.6313L29.1704 13.1457C29.4829 12.8533 29.6545 12.4558 29.6545 12.0471Z" fill="white" fill-opacity="0.85"/></g><defs><clipPath id="clip0_505_3993"><rect width="30.0085" height="24.1328" fill="white"/></clipPath></defs></svg></span></a>
            </div>
        </div>
    </section>

    <!-- Section Nos Guides Experts -->
    <section class="guides-experts">
        <div class="container">
            <h2 class="section-title">Notre équipe</h2>
            
            <div class="guides-description">
                <p>Nous sommes une équipe unie d'experts du voyage soigneusement sélectionnés. Nous nous faisons confiance et nous nous fixons des normes élevées, ce qui garantit que chaque voyage que nous organisons est vraiment exceptionnel.</p>
            </div>
            
            <div class="guides-grid">
                <div class="guide-card">
                    <div class="guide-photo">
                        <img src="img/avatars/ibrahima.jpg" alt="Ibrahima">
                    </div>
                    <h3 class="guide-name">Ibrahima</h3>
                </div>
                
                <div class="guide-card">
                    <div class="guide-photo">
                        <img src="img/avatars/paul.jpg" alt="Paul">
                    </div>
                    <h3 class="guide-name">Paul</h3>
                </div>
                
                <div class="guide-card">
                    <div class="guide-photo">
                        <img src="img/avatars/charly.jpg" alt="Charly">
                    </div>
                    <h3 class="guide-name">Charly</h3>
                </div>               
            </div>
        </div>
    </section>

    <?php include 'php/footer.php'; ?>

    <script src="js/nav.js"></script>
    <script src="js/index-cards.js"></script>

</body>

</html>