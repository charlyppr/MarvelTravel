<?php
require_once 'session.php';

$_SESSION['ban'] = true;

if (isset($_SESSION['user']) && isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];
    $json_file = dirname(__FILE__) . '/../json/users.json';
    
    if (file_exists($json_file)) {
        $users = json_decode(file_get_contents($json_file), true);
        if ($users) {
            $userStillBanned = false;
            
            foreach ($users as $user) {
                if ($user['email'] === $userEmail && $user['blocked']) {
                    $userStillBanned = true;
                    break;
                }
            }
            
            // Si l'utilisateur n'est plus banni, le rediriger vers la page d'accueil
            if (!$userStillBanned) {
                header("Location: ../index.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <title>bannissement • Marvel Travel</title>

    <script src="../js/theme-loader.js"></script>

    <link rel="stylesheet" href="../css/theme.css" id="theme">

    <link rel="stylesheet" href="../css/confirmation.css">
    <link rel="stylesheet" href="../css/legal-pages.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>

    <div class="confirmation-container">
        <div class="confirmation-header error-header">
            <div class="error-icon">
                <img src="../img/svg/alert-circle.svg" alt="Erreur">
            </div>
            <h1>Vous êtes banni. Votre compte ne respecte pas notre politique.</h1>
            <p class="confirmation-subtitle"></p>
        </div>

        <div class="confirmation-content">
            <div class="confirmation-card error-card">
                <div class="card-header">
                    <img src="../img/svg/info.svg" alt="Information" class="card-icon">
                    <h2>Que faire ?</h2>
                </div>
                <div class="card-content">
                    <ul class="error-reasons">
                        <li>Contactez notre service client <a class="link" href="mailto:contact@marveltravel.shop">contact@marveltravel.shop</a></li>
                        <li>Vous pouvez consulter notre <a class="link" href="#" id="show-confidentialite">politique de confidentialité</a></li>
                        <li>Vous pouvez consulter nos <a class="link" href="#" id="show-cgv">conditions générales d'utilisation</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confidentialité -->
    <div id="confidentialite-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="titre">Politique de Confidentialité</h1>
                <span class="close-modal" data-modal="confidentialite-modal">&times;</span>
            </div>
            <div class="modal-body">
                <?php include 'confidentialite-light.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal des CGV -->
    <div id="cgv-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="titre">Conditions Générales de Vente</h1>
                <span class="close-modal" data-modal="cgv-modal">&times;</span>
            </div>
            <div class="modal-body">
                <?php include 'cgv-light.php'; ?>
            </div>
        </div>
    </div>

    <style>
        /* Styles pour les modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            animation: modalAppear 0.3s ease;
        }

        .modal-content {
            background-color: var(--color-card-bg);
            margin: 5% auto;
            padding: var(--spacing-lg);
            border: 1px solid var(--color-accent-faded);
            width: 80%;
            max-width: 1000px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-large);
            position: relative;
            max-height: 85vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--color-accent-faded);
            margin-bottom: var(--spacing-md);
        }

        .modal-header .titre {
            margin-bottom: 0;
        }

        .close-modal {
            color: var(--color-accent);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--color-text-light);
        }

        .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 80px);
            padding-right: var(--spacing-md);
        }

        .modal-body .container {
            padding: 0;
            box-shadow: none;
            border: none;
            background: transparent;
        }

        .modal-body .legal-content {
            margin-top: 0;
            padding: 0;
            min-height: auto;
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gestion des modals
            const showConfidentialiteBtn = document.getElementById('show-confidentialite');
            const showCGVBtn = document.getElementById('show-cgv');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            const modals = document.querySelectorAll('.modal');

            // Ouvrir le modal de confidentialité
            showConfidentialiteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('confidentialite-modal').style.display = 'block';
                document.body.style.overflow = 'hidden'; // Empêcher le défilement de la page
            });

            // Ouvrir le modal CGV
            showCGVBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('cgv-modal').style.display = 'block';
                document.body.style.overflow = 'hidden'; // Empêcher le défilement de la page
            });

            // Fermer les modals avec le bouton X
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-modal');
                    document.getElementById(modalId).style.display = 'none';
                    document.body.style.overflow = 'auto'; // Réactiver le défilement
                });
            });

            // Fermer les modals en cliquant en dehors
            window.addEventListener('click', function(event) {
                modals.forEach(modal => {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            });

            // Empêcher la fermeture en cliquant sur le contenu
            document.querySelectorAll('.modal-content').forEach(content => {
                content.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });
        });
    </script>

</body>

</html>