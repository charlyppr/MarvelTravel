<?php
if (!isset($page_title)) {
    $page_title = "Page en cours de maintenance";
}

if (!isset($maintenance_message)) {
    $maintenance_message = "Cette fonctionnalité sera bientôt disponible";
}

if (!isset($completion_date)) {
    $completion_date = "prochainement";
}
?>

<link rel="stylesheet" href="../css/maintenance.css">

<div class="maintenance-container">
    <div class="maintenance-content">
        <div class="maintenance-icon">
            <img src="../img/svg/settings.svg" alt="Maintenance" class="icon-img">
        </div>
        <h1 class="maintenance-title"><?= $page_title ?></h1>
        <p class="maintenance-message"><?= $maintenance_message ?></p>
        <p class="maintenance-date">Disponible : <?= $completion_date ?></p>
        <a href="javascript:history.back()" class="btn-primary maintenance-btn">Retour</a>
    </div>
</div> 