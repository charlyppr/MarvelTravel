<?php
// Méthode compatible avec tous les systèmes d'exploitation
$script_name = $_SERVER['SCRIPT_NAME'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];

// Déterminer quel est le chemin du dossier du projet par rapport à la racine du serveur web
$project_root = str_replace('\\', '/', dirname(dirname(__FILE__))); // Chemin absolu du projet (avec slash UNIX)
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Racine du serveur (avec slash UNIX)

// Obtenir le chemin relatif depuis la racine du serveur
$relative_path = '';
if (strpos($project_root, $document_root) === 0) {
    $relative_path = substr($project_root, strlen($document_root));
}

// Construire l'URL de base
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . "://" . $host . $relative_path . '/';

?>

<footer>
    <div class="footer-content">
        <div class="footer-top">
            <div class="footer-logo">
                <img src="<?php echo $base_url; ?>img/svg/logo.svg" alt="logo marvel travel" width="200px"
                    class="no-invert">
                <span>Aucun Groot n'a été blessé lors du développement.</span>
            </div>

            <div class="footer-right-top">
                <div class="footer-right-top-content">
                    <span>Navigation</span>
                    <ul>
                        <li><a href="<?php echo $base_url; ?>index.php">Accueil</a></li>
                        <li><a href="<?php echo $base_url; ?>php/destination.php">Destinations</a></li>
                        <li><a href="<?php echo $base_url; ?>php/contact.php">Contact</a></li>
                        <li><a href="<?php echo $base_url; ?>php/contact.php#faq">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-right-top-content">
                    <span>Informations légales</span>
                    <ul>
                        <li><a href="<?php echo $base_url; ?>php/mentions-legales.php">Mentions légales</a></li>
                        <li><a href="<?php echo $base_url; ?>php/confidentialite.php">Confidentialité</a></li>
                        <li><a href="<?php echo $base_url; ?>php/cgv.php">CGV</a></li>
                    </ul>
                </div>

                <div class="footer-right-top-content">
                    <span>Notre équipe</span>
                    <div class="team-links">
                        <div class="team-member">
                            <span>Ibrahima</span>
                            <div class="social-links">
                                <a href="https://github.com/IBBC78" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="Github Ibrahim" class="no-invert">
                                </a>
                                <a href="https://www.linkedin.com/in/ibrahimabaldecisse/" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/linkedin.svg" alt="LinkedIn Ibrahim" class="no-invert">
                                </a>
                            </div>
                        </div>
                        <div class="team-member">
                            <span>Paul</span>
                            <div class="social-links">
                                <a href="https://github.com/paulmarmelat" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="Github Paul" class="no-invert">
                                </a>
                                <a href="https://www.linkedin.com/in/paul-marmelat-1387342a6/" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/linkedin.svg" alt="LinkedIn Paul" class="no-invert">
                                </a>
                            </div>
                        </div>
                        <div class="team-member">
                            <span>Charly</span>
                            <div class="social-links">
                                <a href="https://github.com/charlyppr" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="Github Charly" class="no-invert">
                                </a>
                                <a href="https://www.linkedin.com/in/charly-pupier-ba231a339/" target="_blank">
                                    <img src="<?php echo $base_url; ?>img/svg/linkedin.svg" alt="LinkedIn Charly" class="no-invert">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-left-bottom">
                <span class="copyright">Fait avec amour… et un peu de Vibranium • © 2025 Marvel Travel</span>
                <div class="support-info">
                    <p>Besoin d'aide ? <a href="mailto:support@marveltravel.com">support@marveltravel.com</a></p>
                </div>
            </div>

            <div class="footer-right-bottom">
                <span>Moyens de paiements acceptés</span>
                <div class="paiements">
                    <img src="<?php echo $base_url; ?>img/cards/mastercard.svg" alt="mastercard" class="no-invert">
                    <img src="<?php echo $base_url; ?>img/cards/wakanda.svg" alt="credit wakanda" class="no-invert">
                    <img src="<?php echo $base_url; ?>img/cards/visa.svg" alt="visa" class="no-invert">
                    <img src="<?php echo $base_url; ?>img/cards/asgard.svg" alt="lingots d'or asgard" class="no-invert">
                    <img src="<?php echo $base_url; ?>img/cards/paypal.svg" alt="paypal" class="no-invert">
                    <img src="<?php echo $base_url; ?>img/cards/amex.svg" alt="amex" class="no-invert">
                </div>
            </div>
        </div>
    </div>
</footer>