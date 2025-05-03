<?php
// Maintenance page template
// This file should be included in pages that are under construction

// Set page title if not already set
if (!isset($page_title)) {
    $page_title = "Page en cours de maintenance";
}

// Set maintenance message if not already set
if (!isset($maintenance_message)) {
    $maintenance_message = "Cette fonctionnalité sera bientôt disponible";
}

// Set estimated completion date if not already set
if (!isset($completion_date)) {
    $completion_date = "prochainement";
}
?>

<!-- On s'assure que le CSS de maintenance est chargé -->
<link rel="stylesheet" href="../css/maintenance.css">

<div class="maintenance-container">
    <div class="maintenance-content">
        <div class="maintenance-icon">
            <img src="../img/svg/settings.svg" alt="Maintenance" class="icon-img">
        </div>
        <h1 class="maintenance-title"><?= $page_title ?></h1>
        <p class="maintenance-message"><?= $maintenance_message ?></p>
        <p class="maintenance-date">Disponible <?= $completion_date ?></p>
        <a href="../index.php" class="btn-primary maintenance-btn">Retour à l'accueil</a>
    </div>
</div> 