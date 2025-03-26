<?php

// Vérifier si on est sur une page qui a besoin des modales
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$needs_modals = in_array($current_page, ['profil', 'commande']);
?>

<?php if ($needs_modals): ?>
    <!-- Modal de confirmation de déconnexion -->
    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <img src="../img/svg/warning.svg" alt="warning" class="modal-icon">
                <h2>Confirmation de déconnexion</h2>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
            </div>
            <div class="modal-footer">
                <button id="cancel-logout" class="btn-cancel">Annuler</button>
                <button id="confirm-logout" class="btn-confirm">Déconnexion</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression du compte -->
    <div id="delete-account-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <img src="../img/svg/warning.svg" alt="warning" class="modal-icon">
                <h2>Suppression du compte</h2>
            </div>
            <div class="modal-body">
                <p><strong>Attention :</strong> Cette action est irréversible !</p>
                <p>Toutes vos données personnelles, réservations et messages seront définitivement supprimés.</p>
                <p>Êtes-vous vraiment sûr de vouloir supprimer votre compte ?</p>
            </div>
            <div class="modal-footer">
                <button id="cancel-delete" class="btn-cancel">Annuler</button>
                <button id="confirm-delete" class="btn-confirm btn-danger">Supprimer</button>
            </div>
        </div>
    </div>
<?php endif; ?>