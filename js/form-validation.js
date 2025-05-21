document.addEventListener('DOMContentLoaded', function() {
    // Configuration centralisée
    const config = {
        selectors: {
            forms: 'form',
            emailInputs: 'input[type="email"]',
            passwordInputs: 'input[type="password"]',
            submitButtons: 'button[type="submit"], .next-button',
            inputContainers: [
                '.mdp', 
                '.email', 
                '.field-value', 
                '.profile-field', 
                '.form-row',
                '.civilite-container'
            ]
        },
        classes: {
            inputValid: 'input-valid',
            inputInvalid: 'input-invalid',
            disabledButton: 'disabled-button',
            inputWarning: 'input-warning',
            strengthMeter: 'password-strength-meter',
            strengthIndicator: 'strength-indicator',
            strengthWeak: 'strength-weak',
            strengthFair: 'strength-fair',
            strengthGood: 'strength-good',
            strengthStrong: 'strength-strong'
        },
        regex: {
            email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
        },
        passwordCriteria: {
            minLength: 8,
            requireLowercase: true,
            requireUppercase: true,
            requireNumber: true,
            requireSpecial: true
        }
    };

    // Gestionnaire de validation de formulaire
    const formValidator = {
        // Éléments DOM mis en cache
        elements: {},

        // Initialisation
        init() {
            this.cacheElements();
            this.disableSubmitButtons();
            this.setupEventListeners();
            this.initialValidation();
        },

        // Mise en cache des éléments DOM
        cacheElements() {
            this.elements = {
                forms: document.querySelectorAll(config.selectors.forms),
                emailInputs: document.querySelectorAll(config.selectors.emailInputs),
                passwordInputs: document.querySelectorAll(config.selectors.passwordInputs),
                submitButtons: document.querySelectorAll(config.selectors.submitButtons)
            };
        },

        // Désactiver les boutons de soumission par défaut
        disableSubmitButtons() {
            this.elements.submitButtons.forEach(button => {
                button.disabled = true;
                button.classList.add(config.classes.disabledButton);
            });
        },

        // Configuration des écouteurs d'événements
        setupEventListeners() {
            this.setupEmailValidation();
            this.setupPasswordValidation();
            this.setupFormSubmission();
        },

        // Validation initiale des formulaires
        initialValidation() {
            this.elements.forms.forEach(form => {
                this.updateSubmitButton(form);
            });
        },

        // -- GESTION DES CONTENEURS --

        // Obtenir le conteneur du champ
        getInputContainer(input) {
            for (const selector of config.selectors.inputContainers) {
                const container = input.closest(selector);
                if (container) return container;
            }
            return input.parentElement;
        },

        // -- GESTION DES ERREURS --

        // Afficher un message d'erreur
        showError(input, message) {
            let errorContainer = this.findErrorContainer(input);
            
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.className = config.classes.inputWarning;
                
                const container = this.getInputContainer(input);
                if (container) {
                    container.after(errorContainer);
                }
            }
            
            errorContainer.innerHTML = message;
            errorContainer.style.display = 'block';
            
            const parentElement = input.parentElement;
            parentElement.classList.remove(config.classes.inputValid);
            parentElement.classList.add(config.classes.inputInvalid);
            
            return errorContainer;
        },

        // Masquer un message d'erreur
        hideError(input) {
            const errorContainer = this.findErrorContainer(input);
            if (errorContainer) {
                errorContainer.style.display = 'none';
            }
        },

        // Trouver un conteneur d'erreur existant
        findErrorContainer(input) {
            const container = this.getInputContainer(input);
            if (!container) return null;
            
            let sibling = container.nextElementSibling;
            if (sibling && sibling.classList.contains(config.classes.inputWarning)) {
                return sibling;
            }
            
            return null;
        },

        // -- GESTION DE L'INDICATEUR DE FORCE --

        // Obtenir ou créer un indicateur de force
        getStrengthMeter(passwordInput) {
            const container = this.getInputContainer(passwordInput);
            if (!container) return null;
            
            let sibling = container.nextElementSibling;
            while (sibling) {
                if (sibling.classList.contains(config.classes.strengthMeter)) {
                    return sibling;
                }
                
                if (sibling.classList.contains(config.classes.inputWarning)) {
                    sibling = sibling.nextElementSibling;
                    continue;
                }
                
                break;
            }
            
            const meter = document.createElement('div');
            meter.className = config.classes.strengthMeter;
            meter.style.display = 'none';
            
            const indicator = document.createElement('div');
            indicator.className = config.classes.strengthIndicator;
            meter.appendChild(indicator);
            
            if (sibling && sibling.classList.contains(config.classes.inputWarning)) {
                sibling.after(meter);
            } else {
                container.after(meter);
            }
            
            return meter;
        },

        // Mettre à jour l'indicateur de force
        updateStrengthMeter(passwordInput, strength) {
            const meter = this.getStrengthMeter(passwordInput);
            if (!meter) return;
            
            meter.className = config.classes.strengthMeter;
            
            if (strength <= 1) {
                meter.classList.add(config.classes.strengthWeak);
            } else if (strength === 2) {
                meter.classList.add(config.classes.strengthFair);
            } else if (strength <= 4) {
                meter.classList.add(config.classes.strengthGood);
            } else {
                meter.classList.add(config.classes.strengthStrong);
            }
            
            meter.style.display = 'block';
        },

        // Masquer l'indicateur de force
        hideStrengthMeter(passwordInput) {
            const meter = this.getStrengthMeter(passwordInput);
            if (meter) {
                meter.style.display = 'none';
            }
        },

        // -- FONCTIONS DE VALIDATION --

        // Valider un champ email
        validateEmail(input) {
            const value = input.value.trim();
            
            if (value === '') {
                this.hideError(input);
                input.parentElement.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
                return false;
            }
            
            if (!config.regex.email.test(value)) {
                this.showError(input, 'Adresse email invalide');
                return false;
            }
            
            this.hideError(input);
            input.parentElement.classList.remove(config.classes.inputInvalid);
            input.parentElement.classList.add(config.classes.inputValid);
            return true;
        },

        // Valider un champ mot de passe
        validatePassword(input) {
            const value = input.value;
            
            if (value === '') {
                this.hideError(input);
                this.hideStrengthMeter(input);
                input.parentElement.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
                return false;
            }
            
            const criteria = {
                length: value.length >= config.passwordCriteria.minLength,
                lowercase: !config.passwordCriteria.requireLowercase || /[a-z]/.test(value),
                uppercase: !config.passwordCriteria.requireUppercase || /[A-Z]/.test(value),
                number: !config.passwordCriteria.requireNumber || /[0-9]/.test(value),
                special: !config.passwordCriteria.requireSpecial || /[^A-Za-z0-9]/.test(value)
            };
            
            const passedCriteria = Object.values(criteria).filter(Boolean).length;
            
            this.updateStrengthMeter(input, passedCriteria);
            
            if (passedCriteria < 5) {
                let message = 'Le mot de passe doit contenir:';
                if (!criteria.length) message += '<br>• Au moins 8 caractères';
                if (!criteria.lowercase) message += '<br>• Au moins 1 minuscule';
                if (!criteria.uppercase) message += '<br>• Au moins 1 majuscule';
                if (!criteria.number) message += '<br>• Au moins 1 chiffre';
                if (!criteria.special) message += '<br>• Au moins 1 caractère spécial';
                
                this.showError(input, message);
                return false;
            }
            
            this.hideError(input);
            input.parentElement.classList.remove(config.classes.inputInvalid);
            input.parentElement.classList.add(config.classes.inputValid);
            return true;
        },

        // Mettre à jour l'état du bouton de soumission
        updateSubmitButton(form) {
            const emailInput = form.querySelector(config.selectors.emailInputs);
            const passwordInput = form.querySelector(config.selectors.passwordInputs);
            const submitButton = form.querySelector(config.selectors.submitButtons);
            
            if (!submitButton) return;
            
            let isValid = true;
            
            if (emailInput) {
                isValid = isValid && this.validateEmail(emailInput);
            }
            
            if (passwordInput) {
                isValid = isValid && this.validatePassword(passwordInput);
            }
            
            submitButton.disabled = !isValid;
            submitButton.classList.toggle(config.classes.disabledButton, !isValid);
        },

        // -- CONFIGURATION DES ÉVÉNEMENTS --

        // Configuration de la validation des emails
        setupEmailValidation() {
            this.elements.emailInputs.forEach(input => {
                ['input', 'blur'].forEach(event => {
                    input.addEventListener(event, () => {
                        this.validateEmail(input);
                        this.updateSubmitButton(input.form);
                    });
                });
            });
        },

        // Configuration de la validation des mots de passe
        setupPasswordValidation() {
            this.elements.passwordInputs.forEach(input => {
                ['input', 'blur'].forEach(event => {
                    input.addEventListener(event, () => {
                        this.validatePassword(input);
                        this.updateSubmitButton(input.form);
                    });
                });
            });
        },

        // Configuration de la soumission des formulaires
        setupFormSubmission() {
            this.elements.forms.forEach(form => {
                form.addEventListener('submit', event => {
                    let isValid = true;
                    
                    const emailInput = form.querySelector(config.selectors.emailInputs);
                    if (emailInput) {
                        isValid = this.validateEmail(emailInput) && isValid;
                    }
                    
                    const passwordInput = form.querySelector(config.selectors.passwordInputs);
                    if (passwordInput) {
                        isValid = this.validatePassword(passwordInput) && isValid;
                    }
                    
                    if (!isValid) {
                        event.preventDefault();
                        
                        const firstError = document.querySelector(`.${config.classes.inputWarning}[style*="display: block"]`);
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            });
        }
    };
    
    // Initialisation du gestionnaire de validation
    formValidator.init();
}); 