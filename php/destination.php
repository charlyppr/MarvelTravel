<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Destinations</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/destination.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">

</head>

<body>
    <div class="default"></div>

    <header class="nav">
        <a href="../index.php" class="logo-container">
            <div class="logo-gauche">
                <span class="logo mar">MAR</span>
                <span class="logo tra">TRA</span>
            </div>
            <span class="logo vel">VEL</span>
        </a>

        <div class="menu">
            <ul>
                <a href="../index.php" class="menu-li">
                    <li>Accueil</li>
                </a>
                <a href="destination.php" class="active menu-li">
                    <li>Destinations</li>
                </a>
                <a href="contact.php" class="menu-li">
                    <li>Contact</li>
                </a>
                <a href="connexion.php" class="nav-button">
                    <li>Se connecter</li>
                </a>
            </ul>
        </div>
    </header>

    <section class="destination-container">

        <div class="destination-landing">
            <div class="destination-titre">
                <h1>Vers quels destinations vous envolerez-vous</h1>
                <p>Asgard, Knowhere, New York, Wakanda, plus d’une vingtaine de destinations ...</p>
            </div>

            <form class="search-container">
                <div class="filtres-container">
                    <div class="filtre">
                        <img src="../img/svg/globe.svg" alt="globe">
                        <input type="search" name="destination" placeholder="Destination">
                    </div>

                    <img src="../img/svg/line-haut.svg" alt="separateur">

                    <div class="filtre">
                        
                        <img src="../img/svg/calendar.svg" alt="calendrier">
                        <input type="date" name="date" id="date">
                    </div>

                    <img src="../img/svg/line-haut.svg" alt="separateur">


                    <div class="filtre">
                        <img src="../img/svg/budget.svg" alt="budget euro">
                        <input type="number" name="budget" id="budget" placeholder="Budget">
                    </div>

                    <img src="../img/svg/line-haut.svg" alt="separateur">


                    <div class="filtre">
                        <img src="../img/svg/double-person.svg" alt="deux personne">
                        <input type="number" name="voyageurs" id="voyageurs" placeholder="Voyageurs">
                    </div>
                </div>

                <button class="search-button" type="submit">Rechercher</button>
            </form>
        </div>

    </section>

    <section class="card-info">
        <div class="card">
            <h1>100+</h1>
            <p>Clients</p>
        </div>

        <div class="card">
            <div class="best">
                <img src="../img/svg/ble.svg" alt="blé">
                <h1>N°1</h1>
                <img src="../img/svg/ble.svg" alt="blé" class="flip">
            </div>
            <p>Agence de voyage multiversel</p>
        </div>

        <div class="card">
            <h1>20+</h1>
            <p>Destinations</p>
        </div>
    </section>

    <section class="best-seller">

        <div class="top-section">
            <p class="sous-titre-2">Nos best-seller</p>
            <h1 class="titre">Les destinations les plus vendu</h1>
        </div>

        <div class="best-seller-cards">
            <div class="card-best">
                <img src="../img/destinations/newyork.jpg" alt="new york">
                <div class="card-text">
                    <p>New York</p>
                    <p>399,95€</p>
                </div>
            </div>

            <div class="card-best">
                <img src="../img/destinations/wakanda.jpg" alt="wakanda">
                <div class="card-text">
                    <p>Wakanda</p>
                    <p>5099,95€</p>
                </div>
            </div>

            <div class="card-best">
                <img src="../img/destinations/hala.jpg" alt="hala">
                <div class="card-text">
                    <p>Hala</p>
                    <p>1199,95€</p>
                </div>

            </div>

            <div class="card-best">
                <img src="../img/destinations/wandar.png" alt="wandar">
                <div class="card-text">
                    <p>Wandar</p>
                    <p>999,95€</p>
                </div>
            </div>
        </div>

        <div class="discover-more-container">
            <a href="" class="discover-more">Voir toutes nos destinations</a>
        </div>

    </section>

    <section class="avis-section">
        <div class="top-section">
            <p class="sous-titre-2">Ils ont adorés</p>
        </div>

        <div class="avis-container">
            <div class="avis">
                <div class="user-info">
                    <span class="nom">Sophie Lefebvre</span>
                    <span class="destination">New York</span>
                </div>

                <div class="avis-text-container">
                    <p>Voir la tour des Avengers en vrai, c’est juste dingue ! J’ai fait le circuit guidé 'Batailles des
                        Avengers', et on sent vraiment l’histoire de la ville. Petit conseil : évitez Times Square si
                        Hulk est de mauvaise humeur</p>
                    <div class="stars">
                        <img src="../img/svg/star.svg" alt="étoile">
                        <img src="../img/svg/star.svg" alt="étoile">
                        <img src="../img/svg/star.svg" alt="étoile">
                        <img src="../img/svg/star.svg" alt="étoile">
                        <img src="../img/svg/star.svg" alt="étoile">
                    </div>
                </div>
            </div>

            <div class="fleches">
                <img src="../img/svg/fleche-droite.svg" alt="fleche-gauche" class="flip" style="opacity: 0.5;">
                <img src="../img/svg/fleche-droite.svg" alt="fleche-droite">
            </div>
        </div>
    </section>


    <footer>
        <div class="footer-content">
            <div class="footer-top">
                <div class="footer-logo">
                    <img src="../img/svg/logo.svg" alt="logo marvel travel" width="200px">
                    <span>Aucun Groot n’a été blessé lors du développement.</span>
                </div>

                <div class="footer-right-top">
                    <div class="footer-right-top-content">
                        <span>Notre agence</span>
                        <ul>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="administrateur.php">Administrateur</a></li>
                            <li><a href="profil.php">Profil</a></li>
                        </ul>
                    </div>
                    <div class="footer-right-top-content">
                        <span>Nos réseaux</span>
                        <div class="reseaux">
                            <div class="github">
                                <ul>
                                    <li><a class="ibra" href="https://github.com/IBBC78" target="_blank"><img
                                                src="../img/svg/github-mark.svg" alt="logo github"></a></li>
                                    <li><a class="paul" href="https://github.com/paulmarmelat" target="_blank"><img
                                                src="../img/svg/github-mark.svg" alt="logo github"></a></li>
                                    <li><a class="charly" href="https://github.com/charlyppr" target="_blank"><img
                                                src="../img/svg/github-mark.svg" alt="logo github"></a></li>
                                </ul>
                            </div>

                            <div class="linkedin">
                                <ul>
                                    <li><a class="ibra" href="https://www.linkedin.com/in/ibrahimabaldecisse/"
                                            target="_blank"><img src="../img/svg/linkedin.svg" alt="logo linkedin"></a>
                                    </li>
                                    <li><a class="paul" href="https://www.linkedin.com/in/paul-marmelat-1387342a6/"
                                            target="_blank"><img src="../img/svg/linkedin.svg" alt="logo linkedin"></a>
                                    </li>
                                    <li><a class="charly" href="https://www.linkedin.com/in/charly-pupier-ba231a339/"
                                            target="_blank"><img src="../img/svg/linkedin.svg" alt="logo linkedin"></a>
                                    </li>
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
                        <img src="../img/cards/mastercard.svg" alt="mastercard">
                        <img src="../img/cards/wakanda.svg" alt="credit wakanda">
                        <img src="../img/cards/visa.svg" alt="visa">
                        <img src="../img/cards/asgard.svg" alt="lingots d'or asgard">
                        <img src="../img/cards/paypal.svg" alt="paypal">
                        <img src="../img/cards/amex.svg" alt="amex">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>

</body>

</html>