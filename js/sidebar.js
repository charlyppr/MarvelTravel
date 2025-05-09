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
    
    // Mobile sidebar toggle functionality
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    // Function to toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }
    
    // Toggle sidebar when mobile toggle button is clicked
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when clicking on the overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when window is resized to desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991 && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Close sidebar when clicking on sidebar links on mobile
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    if (window.innerWidth <= 991) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            });
        });
    }
}); 