document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const eyeIcon = this.querySelector('.eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.src = '../img/svg/eye.svg';
                eyeIcon.alt = 'Masquer le mot de passe';
                this.title = 'Masquer le mot de passe';
            } else {
                input.type = 'password';
                eyeIcon.src = '../img/svg/eye-slash.svg';
                eyeIcon.alt = 'Afficher le mot de passe';
                this.title = 'Afficher le mot de passe';
            }
        });
    });
});