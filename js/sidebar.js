document.addEventListener('DOMContentLoaded', function () {
    // Éléments du modal
    const modal = document.getElementById('logout-modal');
    const logoutBtn = document.getElementById('logout-button');
    const closeBtn = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancel-logout');

    // Ouvrir le modal quand on clique sur le bouton de déconnexion
    logoutBtn.addEventListener('click', function () {
        modal.style.display = 'flex';
    });

    // Fermer le modal sur le bouton de fermeture
    closeBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Fermer le modal sur le bouton Annuler
    cancelBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Fermer le modal si on clique à l'extérieur
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}); 