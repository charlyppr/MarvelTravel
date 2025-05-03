document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const forms = document.querySelectorAll('form');
    const emailInputs = document.querySelectorAll('input[type="email"]');
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    const submitButtons = document.querySelectorAll('button[type="submit"], .next-button');
    
    // Email validation regex
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    // Désactiver les boutons de soumission par défaut
    submitButtons.forEach(button => {
        button.disabled = true;
        button.classList.add('disabled-button');
    });
    
    // Create warning message element
    function createWarning(message, inputElement) {
        // Check if warning already exists for this input
        let existingWarning = inputElement.parentElement.nextElementSibling;
        
        // Si l'élément suivant est le compteur de force, prendre l'élément d'après
        if (existingWarning && existingWarning.classList.contains('password-strength-meter')) {
            existingWarning = existingWarning.nextElementSibling;
        }
        
        if (existingWarning && existingWarning.classList.contains('input-warning')) {
            existingWarning.innerHTML = message;
            return existingWarning;
        }
        
        const warningElement = document.createElement('div');
        warningElement.className = 'input-warning';
        warningElement.innerHTML = message;
        
        // Si c'est un mot de passe, insérer après le compteur de force
        const strengthMeter = inputElement.parentElement.nextElementSibling;
        if (inputElement.type === 'password' && strengthMeter && strengthMeter.classList.contains('password-strength-meter')) {
            strengthMeter.after(warningElement);
        } else {
            // Sinon, insérer après le parent de l'input
            inputElement.parentElement.after(warningElement);
        }
        
        return warningElement;
    }
    
    // Remove warning message
    function removeWarning(inputElement) {
        // Si c'est un mot de passe, vérifier après le compteur de force
        let existingWarning;
        const nextElement = inputElement.parentElement.nextElementSibling;
        
        if (inputElement.type === 'password' && nextElement && nextElement.classList.contains('password-strength-meter')) {
            existingWarning = nextElement.nextElementSibling;
        } else {
            existingWarning = nextElement;
        }
        
        if (existingWarning && existingWarning.classList.contains('input-warning')) {
            existingWarning.remove();
        }
    }
    
    // Validate email format
    function validateEmail(emailInput) {
        const email = emailInput.value.trim();
        
        if (email === '') {
            removeWarning(emailInput);
            emailInput.parentElement.classList.remove('input-valid', 'input-invalid');
            return false;
        }
        
        if (!emailRegex.test(email)) {
            createWarning('Adresse email invalide', emailInput);
            emailInput.parentElement.classList.remove('input-valid');
            emailInput.parentElement.classList.add('input-invalid');
            return false;
        } else {
            removeWarning(emailInput);
            emailInput.parentElement.classList.remove('input-invalid');
            emailInput.parentElement.classList.add('input-valid');
            return true;
        }
    }
    
    // Create password strength meter
    function createStrengthMeter(passwordInput) {
        // Check if meter already exists anywhere near the password input
        const parentElement = passwordInput.parentElement;
        let siblings = [];
        let nextSibling = parentElement.nextElementSibling;
        
        while (nextSibling) {
            siblings.push(nextSibling);
            nextSibling = nextSibling.nextElementSibling;
        }
        
        const existingMeter = siblings.find(el => el.classList.contains('password-strength-meter'));
        if (existingMeter) {
            return existingMeter;
        }
        
        const meterContainer = document.createElement('div');
        meterContainer.className = 'password-strength-meter';
        
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'strength-indicator';
        meterContainer.appendChild(strengthIndicator);
        
        // Insert after password input parent
        parentElement.after(meterContainer);
        return meterContainer;
    }
    
    // Password strength validation
    function validatePassword(passwordInput) {
        const password = passwordInput.value;
        
        // Create or get the strength meter
        const strengthMeter = createStrengthMeter(passwordInput);
        
        if (password === '') {
            removeWarning(passwordInput);
            passwordInput.parentElement.classList.remove('input-valid', 'input-invalid');
            strengthMeter.className = 'password-strength-meter';
            return false;
        }
        
        const criteria = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };
        
        // Count passed criteria for strength calculation
        const passedCriteria = Object.values(criteria).filter(Boolean).length;
        
        // Update strength meter class
        strengthMeter.className = 'password-strength-meter';
        if (passedCriteria === 0 || passedCriteria === 1) {
            strengthMeter.classList.add('strength-weak');
        } else if (passedCriteria === 2) {
            strengthMeter.classList.add('strength-fair');
        } else if (passedCriteria === 3 || passedCriteria === 4) {
            strengthMeter.classList.add('strength-good');
        } else if (passedCriteria === 5) {
            strengthMeter.classList.add('strength-strong');
        }
        
        // Generate appropriate warning message if needed
        if (passedCriteria < 5) {
            let warningMessage = 'Le mot de passe doit contenir:';
            let criteriaItems = [];
            
            if (!criteria.length) criteriaItems.push('Au moins 8 caractères');
            if (!criteria.lowercase) criteriaItems.push('Au moins 1 minuscule');
            if (!criteria.uppercase) criteriaItems.push('Au moins 1 majuscule');
            if (!criteria.number) criteriaItems.push('Au moins 1 chiffre');
            if (!criteria.special) criteriaItems.push('Au moins 1 caractère spécial');
            
            criteriaItems.forEach(item => {
                warningMessage += '<br>• ' + item;
            });
            
            const warningElement = createWarning(warningMessage, passwordInput);
            passwordInput.parentElement.classList.remove('input-valid');
            passwordInput.parentElement.classList.add('input-invalid');
            return false;
        } else {
            removeWarning(passwordInput);
            passwordInput.parentElement.classList.remove('input-invalid');
            passwordInput.parentElement.classList.add('input-valid');
            return true;
        }
    }
    
    // Fonction pour vérifier et mettre à jour l'état du bouton de soumission
    function updateSubmitButtonState(form) {
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
        
        // Si le formulaire est valide, activer le bouton
        submitButton.disabled = !isValid;
        if (isValid) {
            submitButton.classList.remove('disabled-button');
        } else {
            submitButton.classList.add('disabled-button');
        }
    }
    
    // Add event listeners to all email inputs
    emailInputs.forEach(emailInput => {
        emailInput.addEventListener('input', function() {
            validateEmail(this);
            updateSubmitButtonState(this.form);
        });
        
        emailInput.addEventListener('blur', function() {
            validateEmail(this);
            updateSubmitButtonState(this.form);
        });
    });
    
    // Add event listeners to all password inputs
    passwordInputs.forEach(passwordInput => {
        // Create strength meter on page load
        createStrengthMeter(passwordInput);
        
        passwordInput.addEventListener('input', function() {
            validatePassword(this);
            updateSubmitButtonState(this.form);
        });
        
        passwordInput.addEventListener('blur', function() {
            validatePassword(this);
            updateSubmitButtonState(this.form);
        });
    });
    
    // Vérifier initialement tous les formulaires
    forms.forEach(form => {
        updateSubmitButtonState(form);
        
        // Prevent form submission if validation fails
        form.addEventListener('submit', function(event) {
            const emailInput = this.querySelector('input[type="email"]');
            const passwordInput = this.querySelector('input[type="password"]');
            let isValid = true;
            
            if (emailInput) {
                const emailValid = validateEmail(emailInput);
                isValid = isValid && emailValid;
            }
            
            if (passwordInput) {
                const passwordValid = validatePassword(passwordInput);
                isValid = isValid && passwordValid;
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.input-warning');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
}); 