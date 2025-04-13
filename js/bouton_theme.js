document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner le bouton de thème
    const themeToggle = document.querySelector('.theme-toggle');
    const lightIcon = document.querySelector('.light-icon');
    const darkIcon = document.querySelector('.dark-icon');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Pour l'instant, on change juste visuellement les icônes
            lightIcon.classList.toggle('active');
            darkIcon.classList.toggle('active');
        });
    }
});
