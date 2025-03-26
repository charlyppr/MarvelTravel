<?php
require_once "session.php";

// Vérifier si les informations du passeport sont disponibles
if (!isset($_SESSION['passport_info'])) {
    header("Location: connexion.php");
    exit();
}

$passport_info = $_SESSION['passport_info'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • Votre Passeport Multiversel</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/connexion-inscription.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
    <style>
        .passport-container {
            background: linear-gradient(135deg, #e23636, #518cca);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin: 20px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .passport-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .passport-logo {
            font-weight: bold;
            font-size: 18px;
        }

        .passport-title {
            text-align: center;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .passport-data {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }

        .data-label {
            font-size: 14px;
            opacity: 0.8;
        }

        .data-value {
            font-weight: bold;
            font-size: 16px;
        }

        .passport-footer {
            text-align: center;
            font-size: 12px;
            opacity: 0.7;
        }

        .passport-id {
            font-size: 22px;
            font-family: monospace;
            letter-spacing: 2px;
            text-align: center;
            margin: 15px 0;
            background: rgba(0, 0, 0, 0.2);
            padding: 10px;
            border-radius: 5px;
        }

        .watermark {
            position: absolute;
            opacity: 0.05;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
        }

        .continue-button {
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 5px;
            padding: 12px 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 16px;
            text-decoration: none;
            margin-top: 20px;
            width: 100%;
        }

        .continue-button:hover {
            background: #e0e0e0;
        }
    </style>
</head>

<body>
    <div class="default"></div>

    <div class="card">
        <div class="card-content">
            <img src="../img/svg/shield.svg" alt="shield pin" class="shield-pin">
            <img src="../img/svg/captain.svg" alt="captain pin" class="captain-pin">

            <a href="../index.php" class="logo-container">
                <div class="logo-gauche">
                    <span class="logo mar">MAR</span>
                    <span class="logo tra">TRA</span>
                </div>
                <span class="logo vel">VEL</span>
            </a>

            <div class="card-header">
                <span class="titre-2">Félicitations!</span>
                <span class="sous-titre-3">Votre Passeport Multiversel est prêt</span>
            </div>

            <div class="passport-container">
                <img src="../img/svg/shield.svg" alt="Marvel Logo" class="watermark">
                <div class="passport-header">
                    <div class="passport-logo">MARVEL TRAVEL</div>
                    <div><?php echo date('d/m/Y'); ?></div>
                </div>

                <div class="passport-title">PASSEPORT MULTIVERSEL</div>

                <div class="passport-id">
                    <?php echo chunk_split($passport_info['passport_id'], 2, ' '); ?>
                </div>

                <div class="passport-data">
                    <div class="data-row">
                        <span class="data-label">NOM:</span>
                        <span class="data-value"><?php echo htmlspecialchars($passport_info['name']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">DATE DE NAISSANCE:</span>
                        <span
                            class="data-value"><?php echo date('d/m/Y', strtotime($passport_info['date_naissance'])); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">NATIONALITÉ:</span>
                        <span class="data-value"><?php echo htmlspecialchars($passport_info['nationalite']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">DATE D'ÉMISSION:</span>
                        <span
                            class="data-value"><?php echo date('d/m/Y', strtotime($passport_info['date_emission'])); ?></span>
                    </div>
                </div>

                <div class="passport-footer">
                    Ce passeport vous permet de voyager à travers tout le multivers Marvel.
                </div>
            </div>

            <a href="connexion.php" class="continue-button">
                Se connecter
                <img src="../img/svg/fleche-droite.svg" alt="fleche">
            </a>
        </div>
    </div>

    <script src="../js/custom-cursor.js"></script>
</body>

</html>