document.addEventListener('DOMContentLoaded', function () {
    // Éléments du modal de déconnexion
    const logoutModal = document.getElementById('logout-modal');
    const logoutBtn = document.getElementById('logout-button');
    const closeLogoutBtn = document.querySelector('.close-modal');
    const cancelLogoutBtn = document.getElementById('cancel-logout');

    // Éléments du modal de suppression de compte
    const deleteModal = document.getElementById('delete-account-modal');
    const deleteBtn = document.getElementById('delete-account-button');
    const closeDeleteBtn = document.querySelector('.close-modal-delete');
    const cancelDeleteBtn = document.getElementById('cancel-delete');

    // Ouvrir le modal quand on clique sur le bouton de déconnexion
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            logoutModal.style.display = 'flex';
        });
    }

    // Fermer le modal de déconnexion
    if (closeLogoutBtn) {
        closeLogoutBtn.addEventListener('click', function () {
            logoutModal.style.display = 'none';
        });
    }

    // Fermer le modal sur le bouton Annuler
    if (cancelLogoutBtn) {
        cancelLogoutBtn.addEventListener('click', function () {
            logoutModal.style.display = 'none';
        });
    }

    // Ouvrir le modal quand on clique sur le bouton de suppression de compte
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            deleteModal.style.display = 'flex';
        });
    }

    // Fermer le modal de suppression
    if (closeDeleteBtn) {
        closeDeleteBtn.addEventListener('click', function () {
            deleteModal.style.display = 'none';
        });
    }

    // Fermer le modal sur le bouton Annuler
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function () {
            deleteModal.style.display = 'none';
        });
    }

    // Fermer le modal si on clique à l'extérieur
    window.addEventListener('click', function (event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
        }
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
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