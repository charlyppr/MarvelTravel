document.addEventListener('DOMContentLoaded', function () {
    // Gestion de la case à cocher
    document.getElementById('rules-accept').addEventListener('change', function () {
        const continueBtn = document.getElementById('continue-btn');
        if (this.checked) {
            continueBtn.style.opacity = '1';
            continueBtn.style.pointerEvents = 'auto';
        } else {
            continueBtn.style.opacity = '0.5';
            continueBtn.style.pointerEvents = 'none';
        }
    });

    // Gestion du modal CGV
    const modal = document.getElementById('cgv-modal');
    const showCGVBtn = document.getElementById('show-cgv');
    const closeModal = document.querySelector('.close-modal');

    // Ouvrir le modal
    showCGVBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Empêcher le défilement de la page
    });

    // Fermer le modal avec le bouton X
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Réactiver le défilement
    });

    // Fermer le modal en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Empêcher la fermeture en cliquant sur le contenu
    document.querySelector('.modal-content').addEventListener('click', function(event) {
        event.stopPropagation();
    });
}); 