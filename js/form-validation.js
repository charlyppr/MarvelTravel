document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner les éléments principaux
    const forms = document.querySelectorAll('form');
    const emailInputs = document.querySelectorAll('input[type="email"]');
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    const submitButtons = document.querySelectorAll('button[type="submit"], .next-button');
    
    // Regex pour validation d'email
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    // Désactiver les boutons de soumission par défaut
    submitButtons.forEach(button => {
        button.disabled = true;
        button.classList.add('disabled-button');
    });
    
    // --- GESTION DES ERREURS ---
    
    // Créer ou mettre à jour un message d'erreur
    function showError(input, message) {
        // Trouver ou créer un conteneur d'erreur
        let errorContainer = findErrorContainer(input);
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'input-warning';
            
            // Déterminer où placer le message d'erreur
            const container = getInputContainer(input);
            if (container) {
                container.after(errorContainer);
            }
        }
        
        // Mettre à jour le message et afficher l'erreur
        errorContainer.innerHTML = message;
        errorContainer.style.display = 'block';
        
        // Marquer le champ comme invalide
        const parentElement = input.parentElement;
        parentElement.classList.remove('input-valid');
        parentElement.classList.add('input-invalid');
        
        return errorContainer;
    }
    
    // Cacher un message d'erreur
    function hideError(input) {
        const errorContainer = findErrorContainer(input);
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }
    
    // Trouver un conteneur d'erreur existant
    function findErrorContainer(input) {
        const container = getInputContainer(input);
        if (!container) return null;
        
        // Vérifier si un conteneur d'erreur existe déjà après ce conteneur
        let sibling = container.nextElementSibling;
        if (sibling && sibling.classList.contains('input-warning')) {
            return sibling;
        }
        
        return null;
    }
    
    // Obtenir le conteneur du champ
    function getInputContainer(input) {
        return input.closest('.mdp') || 
               input.closest('.email') || 
               input.closest('.field-value') || 
               input.closest('.profile-field') || 
               input.closest('.form-row') ||
               input.closest('.civilite-container') ||
               input.parentElement;
    }
    
    // --- GESTION DE L'INDICATEUR DE FORCE ---
    
    // Obtenir ou créer un indicateur de force
    function getStrengthMeter(passwordInput) {
        const container = getInputContainer(passwordInput);
        if (!container) return null;
        
        // Vérifier si un indicateur existe déjà
        let sibling = container.nextElementSibling;
        while (sibling) {
            if (sibling.classList.contains('password-strength-meter')) {
                return sibling;
            }
            
            // Si on trouve un message d'erreur, vérifier après celui-ci
            if (sibling.classList.contains('input-warning')) {
                sibling = sibling.nextElementSibling;
                continue;
            }
            
            // Sinon, sortir de la boucle
            break;
        }
        
        // Créer un nouvel indicateur
        const meter = document.createElement('div');
        meter.className = 'password-strength-meter';
        
        const indicator = document.createElement('div');
        indicator.className = 'strength-indicator';
        meter.appendChild(indicator);
        
        // Insérer après le conteneur (ou après le message d'erreur si présent)
        if (sibling && sibling.classList.contains('input-warning')) {
            sibling.after(meter);
        } else {
            container.after(meter);
        }
        
        return meter;
    }
    
    // Mettre à jour l'indicateur de force
    function updateStrengthMeter(passwordInput, strength) {
        const meter = getStrengthMeter(passwordInput);
        if (!meter) return;
        
        // Réinitialiser les classes
        meter.className = 'password-strength-meter';
        
        // Ajouter la classe appropriée
        if (strength <= 1) {
            meter.classList.add('strength-weak');
        } else if (strength === 2) {
            meter.classList.add('strength-fair');
        } else if (strength <= 4) {
            meter.classList.add('strength-good');
        } else {
            meter.classList.add('strength-strong');
        }
        
        // Assurer que l'indicateur est visible
        meter.style.display = 'block';
    }
    
    // Cacher l'indicateur de force
    function hideStrengthMeter(passwordInput) {
        const meter = getStrengthMeter(passwordInput);
        if (meter) {
            meter.style.display = 'none';
        }
    }
    
    // --- FONCTIONS DE VALIDATION ---
    
    // Valider un champ email
    function validateEmail(input) {
        const value = input.value.trim();
        
        if (value === '') {
            hideError(input);
            input.parentElement.classList.remove('input-valid', 'input-invalid');
            return false;
        }
        
        if (!emailRegex.test(value)) {
            showError(input, 'Adresse email invalide');
            return false;
        }
        
        hideError(input);
        input.parentElement.classList.remove('input-invalid');
        input.parentElement.classList.add('input-valid');
        return true;
    }
    
    // Valider un champ mot de passe
    function validatePassword(input) {
        const value = input.value;
        
        if (value === '') {
            hideError(input);
            hideStrengthMeter(input);
            input.parentElement.classList.remove('input-valid', 'input-invalid');
            return false;
        }
        
        // Vérifier les critères
        const criteria = {
            length: value.length >= 8,
            lowercase: /[a-z]/.test(value),
            uppercase: /[A-Z]/.test(value),
            number: /[0-9]/.test(value),
            special: /[^A-Za-z0-9]/.test(value)
        };
        
        const passedCriteria = Object.values(criteria).filter(Boolean).length;
        
        // Mettre à jour l'indicateur de force
        updateStrengthMeter(input, passedCriteria);
        
        // Vérifier si tous les critères sont satisfaits
        if (passedCriteria < 5) {
            let message = 'Le mot de passe doit contenir:';
            if (!criteria.length) message += '<br>• Au moins 8 caractères';
            if (!criteria.lowercase) message += '<br>• Au moins 1 minuscule';
            if (!criteria.uppercase) message += '<br>• Au moins 1 majuscule';
            if (!criteria.number) message += '<br>• Au moins 1 chiffre';
            if (!criteria.special) message += '<br>• Au moins 1 caractère spécial';
            
            showError(input, message);
            return false;
        }
        
        hideError(input);
        input.parentElement.classList.remove('input-invalid');
        input.parentElement.classList.add('input-valid');
        return true;
    }
    
    // Mettre à jour l'état du bouton de soumission
    function updateSubmitButton(form) {
        const emailInput = form.querySelector('input[type="email"]');
        const passwordInput = form.querySelector('input[type="password"]');
        const submitButton = form.querySelector('button[type="submit"], .next-button');
        
        if (!submitButton) return;
        
        let isValid = true;
        
        if (emailInput) {
            isValid = isValid && validateEmail(emailInput);
        }
        
        if (passwordInput) {
            isValid = isValid && validatePassword(passwordInput);
        }
        
        submitButton.disabled = !isValid;
        submitButton.classList.toggle('disabled-button', !isValid);
    }
    
    // --- GESTIONNAIRES D'ÉVÉNEMENTS ---
    
    // Événements pour les champs email
    emailInputs.forEach(input => {
        ['input', 'blur'].forEach(event => {
            input.addEventListener(event, () => {
                validateEmail(input);
                updateSubmitButton(input.form);
            });
        });
    });
    
    // Événements pour les champs mot de passe
    passwordInputs.forEach(input => {
        // Créer l'indicateur de force
        getStrengthMeter(input);
        
        ['input', 'blur'].forEach(event => {
            input.addEventListener(event, () => {
                validatePassword(input);
                updateSubmitButton(input.form);
            });
        });
    });
    
    // Validation initiale et soumission du formulaire
    forms.forEach(form => {
        updateSubmitButton(form);
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput) {
                isValid = validateEmail(emailInput) && isValid;
            }
            
            const passwordInput = this.querySelector('input[type="password"]');
            if (passwordInput) {
                isValid = validatePassword(passwordInput) && isValid;
            }
            
            if (!isValid) {
                event.preventDefault();
                
                // Faire défiler jusqu'à la première erreur
                const firstError = document.querySelector('.input-warning[style*="display: block"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
}); 