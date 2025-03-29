<?php
// Initialiser la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'ID du voyage depuis l'URL
$voyage_id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Charger les données des voyages
$json_file = '../json/voyages.json';
if (!file_exists($json_file)) {
    header('Location: destination.php');
    exit;
}

$voyages = json_decode(file_get_contents($json_file), true);

// Vérifier si le voyage existe
if ($voyage_id < 0 || $voyage_id >= count($voyages)) {
    header('Location: destination.php');
    exit;
}

// Récupérer les détails du voyage
$voyage = $voyages[$voyage_id];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Travel • <?php echo htmlspecialchars($voyage['titre']); ?></title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/destination.css">
    <link rel="stylesheet" href="../css/voyage-detail.css">
    <link rel="shortcut icon" href="../img/svg/spiderman-pin.svg" type="image/x-icon">
</head>

<body>
    <div class="default"></div>

    <?php include 'nav.php'; ?>

    <div class="voyage-detail-container">
        <!-- En-tête du voyage avec image de fond -->
        <div class="voyage-header" style="background-image: url('<?php echo htmlspecialchars($voyage['image']); ?>')">
            <div class="voyage-header-overlay">
                <div class="voyage-header-content">
                    <h1><?php echo htmlspecialchars($voyage['titre']); ?></h1>
                    <div class="voyage-meta">
                        <div class="meta-item">
                            <img src="../img/svg/map-pin.svg" alt="Lieu">
                            <span><?php echo htmlspecialchars($voyage['lieu'] ?? 'Destination Marvel'); ?></span>
                        </div>
                        <div class="meta-item">
                            <img src="../img/svg/calendar.svg" alt="Dates">
                            <span><?php echo isset($voyage['dates']) ? 
                                htmlspecialchars($voyage['dates']['debut']) . ' - ' . 
                                htmlspecialchars($voyage['dates']['fin']) : 'Dates flexibles'; ?></span>
                        </div>
                        <div class="meta-item">
                            <img src="../img/svg/price-tag.svg" alt="Prix">
                            <span><?php echo number_format($voyage['prix'], 2, ',', ' ') . '€'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal du voyage -->
        <div class="voyage-content">
            <div class="voyage-description">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($voyage['description'] ?? 'Aucune description disponible.')); ?></p>
            </div>

            <?php if (isset($voyage['etapes']) && !empty($voyage['etapes'])): ?>
            <div class="voyage-etapes">
                <h2>Étapes du voyage</h2>
                <div class="etapes-timeline">
                    <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                    <div class="etape-item">
                        <div class="etape-numero"><?php echo $index + 1; ?></div>
                        <div class="etape-content">
                            <h3><?php echo htmlspecialchars($etape['nom']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($etape['description'] ?? '')); ?></p>
                            
                            <?php if (isset($etape['options']) && !empty($etape['options'])): ?>
                            <div class="etape-options">
                                <h4>Options disponibles</h4>
                                <ul>
                                    <?php foreach ($etape['options'] as $option): ?>
                                    <li>
                                        <span class="option-nom"><?php echo htmlspecialchars($option['nom']); ?></span>
                                        <span class="option-prix"><?php echo number_format($option['prix'], 2, ',', ' ') . '€'; ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Informations complémentaires -->
            <div class="voyage-infos">
                <h2>Informations complémentaires</h2>
                <div class="infos-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <img src="../img/svg/users.svg" alt="Capacité">
                        </div>
                        <div class="info-content">
                            <h4>Capacité</h4>
                            <p><?php echo htmlspecialchars($voyage['capacite'] ?? 'Illimitée'); ?> personnes</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <img src="../img/svg/clock.svg" alt="Durée">
                        </div>
                        <div class="info-content">
                            <h4>Durée</h4>
                            <p><?php 
                                if (isset($voyage['dates'])) {
                                    $debut = new DateTime($voyage['dates']['debut']);
                                    $fin = new DateTime($voyage['dates']['fin']);
                                    $duree = $debut->diff($fin)->days;
                                    echo $duree . ' jours';
                                } else {
                                    echo 'Variable';
                                }
                            ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <img src="../img/svg/star.svg" alt="Difficulté">
                        </div>
                        <div class="info-content">
                            <h4>Difficulté</h4>
                            <p><?php echo htmlspecialchars($voyage['difficulte'] ?? 'Modérée'); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <img src="../img/svg/globe.svg" alt="Langue">
                        </div>
                        <div class="info-content">
                            <h4>Langue</h4>
                            <p><?php echo htmlspecialchars($voyage['langue'] ?? 'Français, Anglais'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton de réservation -->
            <div class="voyage-actions">
                <a href="etapes/etape1.php?id=<?php echo $voyage_id; ?>" class="btn-reserver">
                    Réserver ce voyage
                    <img src="../img/svg/arrow-right.svg" alt="Réserver">
                </a>
                <a href="destination.php" class="btn-retour">
                    <img src="../img/svg/arrow-left.svg" alt="Retour">
                    Retour aux destinations
                </a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="../js/nav.js"></script>
    <script src="../js/custom-cursor.js"></script>
</body>

</html> 