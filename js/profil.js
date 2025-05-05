document.addEventListener('DOMContentLoaded', function() {
    // Configuration centralisée
    const config = {
        selectors: {
            submitBtn: '#submit-profile-btn',
            cancelAllBtn: '#cancel-all-btn',
            profileForm: '#profileForm',
            passwordToggle: '#password-toggle',
            toggleIcon: '#toggle-icon',
            inputs: '.profile-input',
            editBtns: '.field-edit',
            validateBtns: '.field-validate',
            cancelBtns: '.field-cancel',
            notifications: '.notification',
            closeNotification: '.close-notification'
        },
        classes: {
            disabled: 'disabled-button',
            visible: 'visible',
            editing: 'editing',
            focused: 'focused',
            inputValid: 'input-valid',
            inputInvalid: 'input-invalid'
        },
        validation: {
            email: {
                regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
            },
            password: {
                minLength: 8,
                patterns: [
                    /[a-z]/, // minuscule
                    /[A-Z]/, // majuscule
                    /[0-9]/, // chiffre
                    /[^A-Za-z0-9]/ // caractère spécial
                ]
            }
        },
        transitions: {
            notification: 300,
            buttons: 300
        }
    };
    
    // Utilitaires
    const utils = {
        getElement: (selector) => document.querySelector(selector),
        getElements: (selector) => document.querySelectorAll(selector),
        
        // Cherche un élément avec un attribut data-field spécifique
        getFieldControl: (type, field) => {
            return document.querySelector(`${config.selectors[type]}[data-field="${field}"]`);
        },
        
        delay: (ms) => new Promise(resolve => setTimeout(resolve, ms))
    };
    
    // Gestionnaire de profil
    const profileManager = {
        // État
        state: {
            validated: new Set(),
            editingFields: new Set()
        },
        
        // Éléments DOM principaux
        elements: {},
        
        // Initialisation
        init() {
            this.cacheElements();
            this.setupSubmitButton();
            this.setupNotifications();
            this.setupFieldControls();
            this.setupFormSubmission();
            this.setupPasswordToggle();
        },
        
        // Mise en cache des éléments DOM fréquemment utilisés
        cacheElements() {
            this.elements = {
                submitBtn: utils.getElement(config.selectors.submitBtn),
                cancelAllBtn: utils.getElement(config.selectors.cancelAllBtn),
                profileForm: utils.getElement(config.selectors.profileForm),
                passwordToggle: utils.getElement(config.selectors.passwordToggle),
                toggleIcon: utils.getElement(config.selectors.toggleIcon),
                passwordInput: document.getElementById('password')
            };
        },
        
        // Configuration du bouton de soumission
        setupSubmitButton() {
            const { submitBtn } = this.elements;
            if (!submitBtn) return;
            
            // Réactiver le bouton qui pourrait être désactivé par form-validation.js
            setTimeout(() => {
                submitBtn.classList.remove(config.classes.disabled);
                submitBtn.disabled = false;
            }, 100);
            
            submitBtn.addEventListener('click', () => {
                // Activer tous les champs avant la soumission
                utils.getElements(config.selectors.inputs).forEach(input => {
                    input.disabled = false;
                });
                
                this.elements.profileForm.submit();
            });
        },
        
        // Configuration des notifications
        setupNotifications() {
            // Gestionnaire pour fermer les notifications manuellement
            utils.getElements(config.selectors.closeNotification).forEach(button => {
                button.addEventListener('click', () => {
                    const notification = button.closest(config.selectors.notifications);
                    this.fadeOutAndRemove(notification);
                });
            });
            
            // Fermeture automatique des notifications après 5 secondes
            setTimeout(() => {
                utils.getElements(config.selectors.notifications).forEach(notification => {
                    this.fadeOutAndRemove(notification);
                });
            }, 5000);
        },
        
        // Effet de fondu et suppression d'un élément
        fadeOutAndRemove(element) {
            if (!element) return;
            
            element.style.opacity = '0';
            setTimeout(() => element.remove(), config.transitions.notification);
        },
        
        // Configuration des contrôles de champ (édition, validation, annulation)
        setupFieldControls() {
            this.setupEditButtons();
            this.setupValidationEvents();
            this.setupInputFocusEffects();
            
            // Utiliser la délégation d'événements pour gérer validate et cancel
            document.addEventListener('click', (event) => {
                this.handleValidateButtonClick(event);
                this.handleCancelButtonClick(event);
            });
            
            // Bouton d'annulation globale
            if (this.elements.cancelAllBtn) {
                this.elements.cancelAllBtn.addEventListener('click', () => this.resetAllFields());
            }
        },
        
        // Configuration des boutons d'édition
        setupEditButtons() {
            utils.getElements(config.selectors.editBtns).forEach(btn => {
                btn.addEventListener('click', () => {
                    const field = btn.dataset.field;
                    const input = document.getElementById(field);
                    const validateBtn = utils.getFieldControl('validateBtns', field);
                    const cancelBtn = utils.getFieldControl('cancelBtns', field);
                    
                    if (!input || !validateBtn || !cancelBtn) return;
                    
                    // Stocker la valeur originale
                    if (!this.state.editingFields.has(field)) {
                        input.setAttribute('data-original-value', input.value);
                        this.state.editingFields.add(field);
                    }
                    
                    // Activer l'édition
                    input.disabled = false;
                    input.focus();
                    this.toggleEditingClass(field, true);
                    
                    // Mettre à jour l'UI
                    btn.style.display = 'none';
                    validateBtn.style.display = 'inline-flex';
                    cancelBtn.style.display = 'inline-flex';
                    
                    // Gestion spéciale pour le mot de passe
                    if (field === 'password' && this.elements.passwordToggle) {
                        this.elements.passwordToggle.style.display = 'flex';
                    }
                    
                    // Validation initiale
                    this.validateField(field);
                    
                    // Déclencher la validation pour email et password
                    if (field === 'email' || field === 'password') {
                        input.dispatchEvent(new Event('input'));
                    }
                });
            });
        },
        
        // Configuration des événements de validation des champs
        setupValidationEvents() {
            utils.getElements(config.selectors.inputs).forEach(input => {
                input.addEventListener('input', () => this.validateField(input.id));
                input.addEventListener('keyup', () => this.validateField(input.id));
            });
        },
        
        // Configuration des effets de focus
        setupInputFocusEffects() {
            utils.getElements(config.selectors.inputs).forEach(input => {
                input.addEventListener('focus', () => {
                    const wrapper = input.closest('.input-wrapper');
                    if (wrapper) wrapper.classList.add(config.classes.focused);
                });
                
                input.addEventListener('blur', () => {
                    const wrapper = input.closest('.input-wrapper');
                    if (wrapper) wrapper.classList.remove(config.classes.focused);
                });
            });
        },
        
        // Configuration du toggle de mot de passe
        setupPasswordToggle() {
            const { passwordToggle, passwordInput, toggleIcon } = this.elements;
            
            if (!passwordToggle || !passwordInput || !toggleIcon) return;
            
            passwordToggle.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                toggleIcon.src = type === 'password' ? '../img/svg/eye.svg' : '../img/svg/eye-slash.svg';
                toggleIcon.alt = type === 'password' ? 'Afficher' : 'Masquer';
            });
        },
        
        // Configuration de la soumission du formulaire
        setupFormSubmission() {
            const { profileForm } = this.elements;
            
            if (!profileForm) return;
            
            profileForm.addEventListener('submit', (event) => {
                if (!this.validateAllEditingFields()) {
                    event.preventDefault();
                    return;
                }
                
                // Activer tous les champs pour l'envoi
                utils.getElements(config.selectors.inputs).forEach(input => {
                    input.disabled = false;
                });
            });
        },
        
        // Gestion du clic sur un bouton de validation
        handleValidateButtonClick(event) {
            const validateBtn = event.target.closest(config.selectors.validateBtns);
            
            if (!validateBtn || validateBtn.disabled) return;
            
            const field = validateBtn.dataset.field;
            const input = document.getElementById(field);
            
            if (!input) return;
            
            // Vérifier une dernière fois si le champ est valide
            if (!this.validateField(field)) return;
            
            const originalValue = input.getAttribute('data-original-value');
            const hasChanged = originalValue !== input.value;
            
            // Désactiver l'édition
            input.disabled = true;
            this.toggleEditingClass(field, false);
            
            // Mettre à jour l'UI
            validateBtn.style.display = 'none';
            utils.getFieldControl('cancelBtns', field).style.display = 'none';
            utils.getFieldControl('editBtns', field).style.display = 'inline-flex';
            
            // Gestion spéciale pour le mot de passe
            if (field === 'password' && this.elements.passwordToggle) {
                this.elements.passwordToggle.style.display = 'none';
                input.setAttribute('type', 'password');
            }
            
            // Mettre à jour l'état
            if (hasChanged) this.state.validated.add(field);
            else this.state.validated.delete(field);
            
            this.updateActionButtons();
        },
        
        // Gestion du clic sur un bouton d'annulation
        handleCancelButtonClick(event) {
            const cancelBtn = event.target.closest(config.selectors.cancelBtns);
            
            if (!cancelBtn) return;
            
            const field = cancelBtn.dataset.field;
            const input = document.getElementById(field);
            
            if (!input) return;
            
            // Réinitialiser la valeur et désactiver l'édition
            input.value = input.dataset.originalValue;
            input.disabled = true;
            this.toggleEditingClass(field, false);
            
            // Mettre à jour l'UI
            cancelBtn.style.display = 'none';
            utils.getFieldControl('validateBtns', field).style.display = 'none';
            utils.getFieldControl('editBtns', field).style.display = 'inline-flex';
            
            // Mettre à jour l'état
            this.state.editingFields.delete(field);
            
            // Mise à jour des boutons d'action
            if (this.state.editingFields.size === 0 && this.state.validated.size === 0) {
                this.elements.cancelAllBtn.style.display = 'none';
            }
            
            // Gestion spéciale pour les champs email et password
            if (field === 'email' || field === 'password') {
                this.cleanupValidationUI(input);
            }
            
            // Gestion spéciale pour le mot de passe
            if (field === 'password' && this.elements.passwordToggle) {
                this.elements.passwordToggle.style.display = 'none';
                input.setAttribute('type', 'password');
                if (this.elements.toggleIcon) {
                    this.elements.toggleIcon.src = '../img/svg/eye.svg';
                    this.elements.toggleIcon.alt = 'Afficher';
                }
            }
        },
        
        // Validation d'un champ spécifique
        validateField(field) {
            const input = document.getElementById(field);
            const validateBtn = utils.getFieldControl('validateBtns', field);
            
            if (!input || !validateBtn) return false;
            
            let isValid = true;
            
            if (field === 'email') {
                isValid = config.validation.email.regex.test(input.value.trim());
            } else if (field === 'password') {
                if (input.value === '') {
                    isValid = false;
                } else {
                    const criteriaCount = [
                        input.value.length >= config.validation.password.minLength,
                        ...config.validation.password.patterns.map(pattern => pattern.test(input.value))
                    ].filter(Boolean).length;
                    
                    isValid = criteriaCount === 5; // 1 pour la longueur + 4 patterns
                }
            } else {
                isValid = input.value.trim() !== '';
            }
            
            // Mettre à jour l'état du bouton de validation
            validateBtn.classList.toggle(config.classes.disabled, !isValid);
            validateBtn.disabled = !isValid;
            
            return isValid;
        },
        
        // Validation de tous les champs en cours d'édition
        validateAllEditingFields() {
            let isValid = true;
            
            // Si email est en édition, vérifier sa validité
            if (this.state.editingFields.has('email')) {
                const emailInput = document.getElementById('email');
                if (emailInput && !config.validation.email.regex.test(emailInput.value.trim())) {
                    isValid = false;
                }
            }
            
            // Si mot de passe est en édition et non vide, vérifier sa validité
            if (this.state.editingFields.has('password')) {
                const passwordInput = document.getElementById('password');
                if (passwordInput && passwordInput.value !== '') {
                    const criteriaCount = [
                        passwordInput.value.length >= config.validation.password.minLength,
                        ...config.validation.password.patterns.map(pattern => pattern.test(passwordInput.value))
                    ].filter(Boolean).length;
                    
                    if (criteriaCount < 5) isValid = false;
                }
            }
            
            return isValid;
        },
        
        // Nettoyage de l'UI de validation pour un champ
        cleanupValidationUI(input) {
            // Retirer les classes de validation
            input.parentElement.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
            
            // Utiliser la fonction externe removeWarning si disponible
            if (typeof removeWarning === 'function') {
                removeWarning(input);
                return;
            }
            
            // Fallback: suppression manuelle des messages d'erreur
            let nextElem = input.parentElement.nextElementSibling;
            while (nextElem) {
                if (nextElem.classList.contains('input-warning') || 
                    nextElem.classList.contains('password-strength-meter')) {
                    nextElem.remove();
                    nextElem = input.parentElement.nextElementSibling;
                } else {
                    nextElem = nextElem.nextElementSibling;
                }
            }
            
            // Supprimer également les messages dans le conteneur parent
            const parentField = input.closest('.profile-field');
            if (parentField) {
                parentField.querySelectorAll('.input-warning').forEach(warning => warning.remove());
            }
        },
        
        // Mise à jour de la classe d'édition pour un champ
        toggleEditingClass(field, isEditing) {
            const fieldValue = document.querySelector(`.field-value:has(#${field})`);
            if (fieldValue) {
                fieldValue.classList.toggle(config.classes.editing, isEditing);
            }
        },
        
        // Mise à jour de l'affichage des boutons d'action
        updateActionButtons() {
            const { submitBtn, cancelAllBtn } = this.elements;
            
            if (!submitBtn || !cancelAllBtn) return;
            
            if (this.state.validated.size > 0) {
                // Afficher les boutons avec animation
                submitBtn.style.display = 'inline-flex';
                cancelAllBtn.style.display = 'inline-flex';
                
                // S'assurer que le bouton n'est pas désactivé
                submitBtn.disabled = false;
                submitBtn.classList.remove(config.classes.disabled);
                
                // Animation
                setTimeout(() => {
                    submitBtn.classList.add(config.classes.visible);
                    cancelAllBtn.classList.add(config.classes.visible);
                }, 10);
            } else {
                // Cacher les boutons avec animation
                submitBtn.classList.remove(config.classes.visible);
                cancelAllBtn.classList.remove(config.classes.visible);
                
                // Attendre la fin de l'animation
                setTimeout(() => {
                    submitBtn.style.display = 'none';
                    cancelAllBtn.style.display = 'none';
                }, config.transitions.buttons);
            }
        },
        
        // Réinitialisation de tous les champs
        resetAllFields() {
            // Réinitialiser les champs validés
            this.state.validated.forEach(field => {
                const input = document.getElementById(field);
                if (!input) return;
                
                input.value = input.dataset.originalValue;
                input.disabled = true;
                this.toggleEditingClass(field, false);
                
                if (typeof removeWarning === 'function') {
                    removeWarning(input);
                }
                
                input.parentElement.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
            });
            
            // Réinitialiser les champs en cours d'édition
            this.state.editingFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input) return;
                
                input.value = input.dataset.originalValue;
                input.disabled = true;
                this.toggleEditingClass(field, false);
                
                const editBtn = utils.getFieldControl('editBtns', field);
                const validateBtn = utils.getFieldControl('validateBtns', field);
                const cancelBtn = utils.getFieldControl('cancelBtns', field);
                
                if (editBtn) editBtn.style.display = 'inline-flex';
                if (validateBtn) validateBtn.style.display = 'none';
                if (cancelBtn) cancelBtn.style.display = 'none';
                
                if (typeof removeWarning === 'function') {
                    removeWarning(input);
                }
                
                input.parentElement.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
                
                if (field === 'password' && this.elements.passwordToggle) {
                    this.elements.passwordToggle.style.display = 'none';
                }
            });
            
            // Réinitialiser l'état
            this.state.validated.clear();
            this.state.editingFields.clear();
            
            // Mettre à jour l'UI
            this.updateActionButtons();
        }
    };
    
    // Initialisation du gestionnaire de profil
    profileManager.init();
}); 