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

// echo "Debug - base_url: " . $base_url . "<br>";
// echo "Debug - document_root (normalisé): " . $document_root . "<br>";
// echo "Debug - project_root (normalisé): " . $project_root . "<br>";
// echo "Debug - relative_path: " . $relative_path . "<br>";
?>

<footer>
    <div class="footer-content">
        <div class="footer-top">
            <div class="footer-logo">
                <img src="<?php echo $base_url; ?>img/svg/logo.svg" alt="logo marvel travel" width="200px">
                <span>Aucun Groot n'a été blessé lors du développement.</span>
            </div>

            <div class="footer-right-top">
                <div class="footer-right-top-content">
                    <span>Notre agence</span>
                    <ul>
                        <li><a href="<?php echo $base_url; ?>php/contact.php">Contact</a></li>
                        <li><a href="<?php echo $base_url; ?>php/administrateur.php">Administrateur</a></li>
                        <li><a href="<?php echo $base_url; ?>php/profil.php">Profil</a></li>
                    </ul>
                </div>
                <div class="footer-right-top-content">
                    <span>Nos réseaux</span>
                    <div class="reseaux">
                        <div class="github">
                            <ul>
                                <li><a class="ibra" href="https://github.com/IBBC78" target="_blank"><img
                                            src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="logo github"></a>
                                </li>
                                <li><a class="paul" href="https://github.com/paulmarmelat" target="_blank"><img
                                            src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="logo github"></a>
                                </li>
                                <li><a class="charly" href="https://github.com/charlyppr" target="_blank"><img
                                            src="<?php echo $base_url; ?>img/svg/github-mark.svg" alt="logo github"></a>
                                </li>
                            </ul>
                        </div>

                        <div class="linkedin">
                            <ul>
                                <li><a class="ibra" href="https://www.linkedin.com/in/ibrahimabaldecisse/"
                                        target="_blank"><img src="<?php echo $base_url; ?>img/svg/linkedin.svg"
                                            alt="logo linkedin"></a>
                                </li>
                                <li><a class="paul" href="https://www.linkedin.com/in/paul-marmelat-1387342a6/"
                                        target="_blank"><img src="<?php echo $base_url; ?>img/svg/linkedin.svg"
                                            alt="logo linkedin"></a>
                                </li>
                                <li><a class="charly" href="https://www.linkedin.com/in/charly-pupier-ba231a339/"
                                        target="_blank"><img src="<?php echo $base_url; ?>img/svg/linkedin.svg"
                                            alt="logo linkedin"></a>
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
                    <img src="<?php echo $base_url; ?>img/cards/mastercard.svg" alt="mastercard">
                    <img src="<?php echo $base_url; ?>img/cards/wakanda.svg" alt="credit wakanda">
                    <img src="<?php echo $base_url; ?>img/cards/visa.svg" alt="visa">
                    <img src="<?php echo $base_url; ?>img/cards/asgard.svg" alt="lingots d'or asgard">
                    <img src="<?php echo $base_url; ?>img/cards/paypal.svg" alt="paypal">
                    <img src="<?php echo $base_url; ?>img/cards/amex.svg" alt="amex">
                </div>
            </div>
        </div>
    </div>
</footer>