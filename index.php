<!DOCTYPE php>
<php lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/accueil.css">
    <link rel="stylesheet" href="css/intro.css">
    <link rel="shortcut icon" href="img/svg/spiderman-pin.svg" type="image/x-icon">

    <title>Marvel Travel • Accueil</title>

</head>

<body>
    <div class="default"></div>

    <div class="intro">
        <div class="logo-container-intro">
            <div class="logo-gauche-intro">
                <span class="logo-intro mar-intro">MAR</span>
                <span class="logo-intro tra-intro">TRA</span>
            </div>
            <span class="logo-intro vel-intro">VEL</span>
        </div>
    </div>

    <header class="nav">
        <a href="index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>

        <div class="menu">
            <ul>
                <a href="index.php" class="active menu-li"><li>Accueil</li></a>
                <a href="php/destination.php" class="menu-li"><li>Destinations</li></a>
                <a href="php/contact.php" class="menu-li"><li>Contact</li></a>
                <a href="php/connexion.php" class="nav-button"><li>Se connecter</li></a>
            </ul>
        </div>
    </header>

    <section class="landing">

        <div class="landing-container">
            <div class="hero">
                    <img src="img/pierres/rouge.png" alt="pierre rouge" class="pierre rouge">
                    <img src="img/pierres/bleu.png" alt="pierre bleu" class="pierre bleu">
                    <img src="img/pierres/vert.png" alt="pierre verte" class="pierre vert">
                    <img src="img/pierres/violet.png" alt="pierre violette" class="pierre violet">
                    <img src="img/pierres/orange.png" alt="pierre orange" class="pierre orange">
                <div class="hero-text">
                    <h1>Explorer l'infini, un monde a la fois</h1>
                </div>
                <img src="img/flou.png" alt="flou" class="flou">
            </div>


            <div class="more-container">
                <div class="more-text">Voir plus</div>
                <img class="more-svg" alt="fleche bas" src="img/svg/fleche_bas.svg">
            </div>
        </div>

    </section>

    <section class="partenaires">
        <div class="partenaires-container">
            <div class="sous-titre-2">
                <span>Nos partenaires</span>
            </div>

            <div class="companie-logo-container">
                <img src="img/companies/nasa.png" alt="nasa" class="companie-logo">
                <img src="img/companies/nova.png" alt="nova" class="companie-logo">
                <img src="img/companies/oscorp.png" alt="oscorp" class="companie-logo">
                <img src="img/companies/pym.png" alt="pym" class="companie-logo">
                <img src="img/companies/ravager.png" alt="ravager" class="companie-logo">
                <img src="img/companies/saber.png" alt="saber" class="companie-logo">
                <img src="img/companies/shield.png" alt="shield" class="companie-logo">
                <img src="img/companies/stark.png" alt="stark" class="companie-logo">
                <img src="img/companies/xmen.png" alt="xmen" class="companie-logo">
                <img src="img/companies/TVA.png" alt="tva" class="companie-logo">
            </div>
        </div>
    </section>


    <section class="pourquoi-nous">
        <div class="pourquoi-nous-container">
            <div class="pourquoi-nous-text">
                <div class="titre">
                    <span>Pourquoi voyager avec nous ?</span>
                </div>
                <div class="sous-titre">
                    <span>Des destinations légendaires. <br>
                        Une immersion totale dans le Multivers.
                    </span>
                </div>
            </div>
        </div>

    </section>

    <section class="destinations">

        <div class="destinations-container">
            <div class="destinations-text">
                <div class="titre">
                    <span style="color: #F2DBAF;">Nos destinations</span>
                </div>
                <div class="sous-titre">
                    <span style="color: #FFFAE7;">De New York à Asgard... <br>
                        ...jusqu’aux confins de la Galaxie.</span>
                </div>
            </div>

            <div class="destinations-exemples">
                <div class="card">
                    <img src="img/destinations/NY.png" alt="new york">
                    <span class="ville">New York</span>
                    <span class="film">AVENGERS</span>
                </div>

                <div class="card">
                    <img src="img/destinations/WAK.png" alt="wakanda">
                    <span class="ville">Wakanda</span>
                    <span class="film">BLACK PANTER</span>
                </div>

                <div class="card">
                    <img src="img/destinations/ASG.png" alt="asgard">
                    <span class="ville">Asgard</span>
                    <span class="film">THOR</span>
                </div>

                <div class="card">
                    <img src="img/destinations/KNOW.png" alt="knowhere">
                    <span class="ville">Knowhere</span>
                    <span class="film">GARDIENS DE LA GALAXIE</span>
                </div>
            </div>

            <span class="sous-titre-2">Et bien d'autres ...</span>
        </div>

    </section>

    <section class="comment">
        <div class="comment-all">
            <div class="comment-text">
                <div class="titre">
                    <span>Comment ça marche ?</span>
                </div>

                <div class="etapes">
                    <div>
                        <span class="chiffre">1</span>
                        <img src="img/svg/line.svg" alt="trait">
                        <span>Choisissez votre monde</span>
                    </div>
                    <div>
                        <span class="chiffre">2</span>
                        <img src="img/svg/line.svg" alt="trait">
                        <span>Activez votre passeport multiversel</span>
                    </div>
                    <div>
                        <span class="chiffre">3</span>
                        <img src="img/svg/line.svg" alt="trait">
                        <span>Et voilà !</span>
                    </div>
                </div>
            </div>

            <a href="php/destination.php" class="decouvrir-button">Découvrir les mondes !</a>
        </div>

    </section>


    <footer>
        <div class="footer-content">
            <div class="footer-top">
                <div class="footer-logo">
                    <img src="img/svg/logo.svg" alt="logo marvel travel" width="200px">
                    <span>Aucun Groot n’a été blessé lors du développement.</span>
                </div>

                <div class="footer-right-top">
                    <div class="footer-right-top-content">
                        <span>Notre agence</span>
                        <ul>
                            <li><a href="php/contact.php">Contact</a></li>
                            <li><a href="php/administrateur.php">Administrateur</a></li>
                            <li><a href="php/profil.php">Profil</a></li>
                        </ul>
                    </div>
                    <div class="footer-right-top-content">
                        <span>Nos réseaux</span>
                        <div class="reseaux">
                            <div class="github">
                                <ul>
                                    <li><a class="ibra" href="https://github.com/IBBC78" target="_blank"><img src="img/svg/github-mark.svg"
                                                alt="logo github"></a></li>
                                    <li><a class="paul" href="https://github.com/paulmarmelat" target="_blank"><img src="img/svg/github-mark.svg"
                                                alt="logo github"></a></li>
                                    <li><a class="charly" href="https://github.com/charlyppr" target="_blank"><img src="img/svg/github-mark.svg"
                                                alt="logo github"></a></li>
                                </ul>
                            </div>
                            
                            <div class="linkedin">
                                <ul>
                                    <li><a class="ibra" href="https://www.linkedin.com/in/ibrahimabaldecisse/" target="_blank"><img src="img/svg/linkedin.svg"
                                                alt="logo linkedin"></a></li>
                                    <li><a class="paul" href="https://www.linkedin.com/in/paul-marmelat-1387342a6/" target="_blank"><img src="img/svg/linkedin.svg"
                                                alt="logo linkedin"></a></li>
                                    <li><a class="charly" href="https://www.linkedin.com/in/charly-pupier-ba231a339/" target="_blank"><img src="img/svg/linkedin.svg"
                                                alt="logo linkedin"></a></li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>

                </div>

            </div>

            <div class="footer-bottom">
                <div class="footer-left-bottom">
                    <span class="copyright">Fait avec amour… et un peu de Vibranium • © 2025 Marvel Travel</span>
                </div>

                <div class="footer-right-bottom">
                    <span>Moyens de paiements acceptés</span>

                    <div class="paiements">
                        <img src="img/cards/mastercard.svg" alt="mastercard">
                        <img src="img/cards/wakanda.svg" alt="credit wakanda">
                        <img src="img/cards/visa.svg" alt="visa">
                        <img src="img/cards/asgard.svg" alt="lingots d'or asgard">
                        <img src="img/cards/paypal.svg" alt="paypal">
                        <img src="img/cards/amex.svg" alt="amex">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- <script src="js/intro.js"></script> -->
    <script src="js/intro.js"></script>
    <script src="js/custom-cursor.js"></script>
    <script src="js/nav.js"></script>
    <script src="js/accueil.js"></script>
    <script src="js/defilement-logo.js"></script>
    <script src="js/scroll-to.js"></script>



</body>

</php>