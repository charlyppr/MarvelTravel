document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les champs requis et le bouton de soumission
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    const submitButton = document.querySelector('.next-button');
    
    // Fonction pour vérifier si tous les champs sont valides
    function validateForm() {
        let isValid = true;
        let validFieldsCount = 0;
        const totalFields = requiredFields.length;
        
        requiredFields.forEach(field => {
            // Vérification spécifique pour chaque type de champ
            let fieldValid = true;
            
            if (field.value.trim() === '') {
                fieldValid = false;
            } else if (field.type === 'email' && !isValidEmail(field.value)) {
                fieldValid = false;
            } else if (field.name === 'password' && field.value.length < 6) {
                fieldValid = false;
            }
            
            if (!fieldValid) {
                isValid = false;
            } else {
                validFieldsCount++;
            }
            
            // Ajouter une classe visuelle pour indiquer l'état
            if (field.value.trim() !== '') {
                field.classList.add('input-valid');
                field.classList.remove('input-invalid');
                
                // Si le parent est .email ou .mdp ou autres conteneurs
                let parent = field.closest('.email, .mdp, .civilite, .nationalite, .date-input');
                if (parent) {
                    parent.classList.add('input-valid');
                    parent.classList.remove('input-invalid');
                }
            } else {
                field.classList.add('input-invalid');
                field.classList.remove('input-valid');
                
                // Si le parent est .email ou .mdp ou autres conteneurs
                let parent = field.closest('.email, .mdp, .civilite, .nationalite, .date-input');
                if (parent) {
                    parent.classList.add('input-invalid');
                    parent.classList.remove('input-valid');
                }
            }
        });
        
        return isValid;
    }
    
    // Vérification d'email valide
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Appliquer la classe appropriée au bouton en fonction de la validation
    function updateSubmitButton() {
        if (validateForm()) {
            submitButton.classList.remove('disabled-button');
            submitButton.disabled = false;
        } else {
            submitButton.classList.add('disabled-button');
            submitButton.disabled = true;
        }
    }
    
    // Mettre à jour immédiatement au chargement
    updateSubmitButton();
    
    // Écouteurs d'événements pour tous les champs
    requiredFields.forEach(field => {
        // Pour les inputs normaux, déclencher sur input
        field.addEventListener('input', updateSubmitButton);
        
        // Pour les selects, déclencher sur change
        if (field.tagName === 'SELECT') {
            field.addEventListener('change', updateSubmitButton);
        }
        
        // Pour gérer le focus et le blur
        field.addEventListener('focus', function() {
            let parent = field.closest('.email, .mdp, .civilite, .nationalite, .date-input');
            if (parent) {
                parent.classList.add('focus-within');
            }
        });
        
        field.addEventListener('blur', function() {
            let parent = field.closest('.email, .mdp, .civilite, .nationalite, .date-input');
            if (parent) {
                parent.classList.remove('focus-within');
                
                // Valider à la perte du focus
                if (field.value.trim() === '') {
                    parent.classList.add('input-invalid');
                    parent.classList.remove('input-valid');
                } else if (field.type === 'email' && !isValidEmail(field.value)) {
                    parent.classList.add('input-invalid');
                    parent.classList.remove('input-valid');
                } else {
                    parent.classList.add('input-valid');
                    parent.classList.remove('input-invalid');
                }
            }
        });
    });
    
    // Animation de l'effet de survol du bouton
    submitButton.addEventListener('mouseover', function() {
        if (!submitButton.disabled) {
            const buttonImage = submitButton.querySelector('img');
            if (buttonImage) {
                buttonImage.style.transform = 'translateX(5px)';
            }
        }
    });
    
    submitButton.addEventListener('mouseout', function() {
        const buttonImage = submitButton.querySelector('img');
        if (buttonImage) {
            buttonImage.style.transform = 'translateX(0)';
        }
    });
}); 