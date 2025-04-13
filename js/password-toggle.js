document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les boutons de toggle de mot de passe
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');
    
    // Pour chaque bouton de toggle
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Trouve le champ de mot de passe associé au bouton
            const passwordField = this.closest('.password-field-container').querySelector('input');
            
            // Alterne entre les types "password" et "text"
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                // Change l'icône pour indiquer que le mot de passe est visible
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.type = 'password';
                // Change l'icône pour indiquer que le mot de passe est caché
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
});
