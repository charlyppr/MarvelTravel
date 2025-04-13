document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');
    
    toggleButtons.forEach(button => {
        // Au chargement de la page, l'œil est barré car le mot de passe est masqué par défaut
        button.classList.add('hide-password');
        
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            
            // Bascule entre les types de champ
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('hide-password'); // Œil normal quand le mot de passe est visible
            } else {
                input.type = 'password';
                this.classList.add('hide-password'); // Œil barré quand le mot de passe est masqué
            }
        });
    });
});
