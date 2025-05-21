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
            closeNotification: '.close-notification',
            // Message modal selectors
            messageItems: '.message-item',
            messageModal: '#messageModal',
            closeModal: '.close-message-modal',
            messageSubjectModal: '.message-subject-modal',
            messageDateModal: '.message-date-modal',
            messageTimeModal: '.message-time-modal',
            messageContentModal: '.message-content-modal'
        },
        classes: {
            disabled: 'disabled-button',
            visible: 'visible',
            editing: 'editing',
            focused: 'focused',
            inputValid: 'input-valid',
            inputInvalid: 'input-invalid',
            // Modal classes
            modalActive: 'message-modal-active'
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
        
        delay: (ms) => new Promise(resolve => setTimeout(resolve, ms)),
        
        // Fonction pour débouncer les fonctions (éviter les appels répétés)
        debounce: (fn, delay) => {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), delay);
            };
        }
    };
    
    // Gestionnaire de profil
    const profileManager = {
        // État
        state: {
            validated: new Set(),
            editingFields: new Set(),
            submitting: false
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
            this.setupMessageModal();
            
            // Initialiser tous les champs comme désactivés
            this.disableAllInputs();
        },
        
        // Mise en cache des éléments DOM fréquemment utilisés
        cacheElements() {
            this.elements = {
                submitBtn: utils.getElement(config.selectors.submitBtn),
                cancelAllBtn: utils.getElement(config.selectors.cancelAllBtn),
                profileForm: utils.getElement(config.selectors.profileForm),
                passwordToggle: utils.getElement(config.selectors.passwordToggle),
                toggleIcon: utils.getElement(config.selectors.toggleIcon),
                passwordInput: document.getElementById('password'),
                inputs: utils.getElements(config.selectors.inputs)
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
            
            submitBtn.addEventListener('click', (event) => {
                event.preventDefault();
                
                if (this.state.submitting) return;
                
                // Soumettre le formulaire
                this.submitForm();
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
                    
                    // Mettre à jour l'état des boutons d'action
                    this.updateActionButtons();
                });
            });
        },
        
        // Configuration des événements de validation des champs
        setupValidationEvents() {
            utils.getElements(config.selectors.inputs).forEach(input => {
                // Utiliser debounce pour éviter les validations trop fréquentes
                const debouncedValidate = utils.debounce(() => {
                    this.validateField(input.id);
                }, 200);
                
                input.addEventListener('input', debouncedValidate);
                input.addEventListener('keyup', debouncedValidate);
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
            
            // Attacher l'écouteur d'événement directement sur le formulaire
            profileForm.addEventListener('submit', (event) => {
                // Toujours empêcher la soumission par défaut
                event.preventDefault();
                event.stopPropagation();
                
                // Soumettre le formulaire via notre méthode
                this.submitForm();
            });
        },
        
        // Configuration du modal de message
        setupMessageModal() {
            const messageItems = utils.getElements(config.selectors.messageItems);
            const messageModal = utils.getElement(config.selectors.messageModal);
            const closeModal = utils.getElement(config.selectors.closeModal);
            
            if (!messageItems.length || !messageModal) return;
            
            // Ajouter le gestionnaire d'événements pour ouvrir le modal
            messageItems.forEach(item => {
                item.addEventListener('click', () => {
                    this.openMessageModal(item);
                });
            });
            
            // Ajouter le gestionnaire d'événements pour fermer le modal
            if (closeModal) {
                closeModal.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeMessageModal();
                });
            }
            
            // Fermer le modal en cliquant à l'extérieur
            messageModal.addEventListener('click', (e) => {
                if (e.target === messageModal) {
                    this.closeMessageModal();
                }
            });
            
            // Fermer le modal avec la touche Echap
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && messageModal.classList.contains(config.classes.modalActive)) {
                    this.closeMessageModal();
                }
            });
        },
        
        // Ouvrir le modal de message
        openMessageModal(messageItem) {
            const messageData = JSON.parse(messageItem.dataset.message);
            const subjectElement = utils.getElement(config.selectors.messageSubjectModal);
            const dateElement = utils.getElement(config.selectors.messageDateModal);
            const timeElement = utils.getElement(config.selectors.messageTimeModal);
            const contentElement = utils.getElement(config.selectors.messageContentModal);
            const messageModal = utils.getElement(config.selectors.messageModal);
            
            if (!messageData || !subjectElement || !dateElement || !timeElement || !contentElement || !messageModal) return;
            
            // Remplir le modal avec les données du message
            subjectElement.textContent = messageData.objet;
            
            const messageDate = new Date(messageData.date);
            dateElement.textContent = messageDate.toLocaleDateString('fr-FR', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            timeElement.textContent = messageDate.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            contentElement.textContent = messageData.message;
            
            // Afficher le modal
            messageModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Empêcher le défilement du body
            
            // Ajouter une courte temporisation pour l'animation
            setTimeout(() => {
                messageModal.classList.add(config.classes.modalActive);
            }, 10);
        },
        
        // Fermer le modal de message
        closeMessageModal() {
            const messageModal = utils.getElement(config.selectors.messageModal);
            
            if (!messageModal) return;
            
            // Déclencher l'animation de fermeture
            messageModal.classList.remove(config.classes.modalActive);
            
            // Attendre la fin de l'animation avant de cacher complètement le modal
            setTimeout(() => {
                if (!messageModal.classList.contains(config.classes.modalActive)) {
                    messageModal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }, 300); // Durée de la transition
        },
        
        // Soumettre le formulaire
        submitForm() {
            if (this.state.submitting) return;
            
            // Vérifier que tous les champs en édition sont valides
            if (!this.validateAllEditingFields()) {
                return;
            }
            
            const { profileForm } = this.elements;
            if (!profileForm) return;
            
            // Marquer comme en cours de soumission
            this.state.submitting = true;
            
            // Activer tous les champs pour l'envoi
            this.enableAllInputs();
            
            // Collecter les données du formulaire
            const formData = new FormData(profileForm);
            
            // Afficher un indicateur de chargement
            this.showLoading();
            
            // Envoyer la requête asynchrone
            fetch('../php/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Stocker les données et l'état de réussite
                this.pendingNotification = {
                    type: data.success ? 'success' : 'error',
                    message: data.message || (data.success ? 'Vos informations ont été mises à jour avec succès.' : 'Une erreur est survenue lors de la mise à jour.'),
                    data: data
                };
                
                // Terminer le processus
                this.finalizeSubmission();
            })
            .catch(error => {
                console.error('Erreur:', error);
                
                this.pendingNotification = {
                    type: 'error',
                    message: 'Une erreur de connexion est survenue.'
                };
                
                this.finalizeSubmission();
            });
        },
        
        // Finaliser la soumission du formulaire
        finalizeSubmission() {
            // Désactiver tous les champs
            this.disableAllInputs();
            
            // Masquer le chargement
            this.hideLoading();
            
            // Réinitialiser l'état de soumission
            this.state.submitting = false;
        },
        
        // Activer tous les champs de saisie
        enableAllInputs() {
            this.elements.inputs.forEach(input => {
                input.disabled = false;
            });
        },
        
        // Désactiver tous les champs de saisie sauf ceux en cours d'édition
        disableAllInputs() {
            this.elements.inputs.forEach(input => {
                // Ne pas désactiver les champs en cours d'édition
                if (!this.state.editingFields.has(input.id)) {
                    input.disabled = true;
                }
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
                if (this.elements.toggleIcon) {
                    this.elements.toggleIcon.src = '../img/svg/eye.svg';
                }
            }
            
            // Mettre à jour l'état
            this.state.editingFields.delete(field);
            if (hasChanged) this.state.validated.add(field);
            else this.state.validated.delete(field);
            
            // Mettre à jour les boutons d'action
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
            input.value = input.getAttribute('data-original-value') || '';
            input.disabled = true;
            this.toggleEditingClass(field, false);
            
            // Nettoyer l'UI de validation
            this.cleanupValidationUI(input);
            
            // Mettre à jour l'UI
            cancelBtn.style.display = 'none';
            utils.getFieldControl('validateBtns', field).style.display = 'none';
            utils.getFieldControl('editBtns', field).style.display = 'inline-flex';
            
            // Mettre à jour l'état
            this.state.editingFields.delete(field);
            this.state.validated.delete(field);
            
            // Gestion spéciale pour le mot de passe
            if (field === 'password' && this.elements.passwordToggle) {
                this.elements.passwordToggle.style.display = 'none';
                input.setAttribute('type', 'password');
                if (this.elements.toggleIcon) {
                    this.elements.toggleIcon.src = '../img/svg/eye.svg';
                }
            }
            
            // Mettre à jour les boutons d'action après un court délai pour éviter les problèmes de timing
            setTimeout(() => {
                this.updateActionButtons();
            }, 50);
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
            let allValid = true;
            
            // Vérifier tous les champs en édition
            this.state.editingFields.forEach(field => {
                if (!this.validateField(field)) {
                    allValid = false;
                }
            });
            
            return allValid;
        },
        
        // Nettoyage de l'UI de validation pour un champ
        cleanupValidationUI(input) {
            if (!input) return;
            
            // Retirer les classes de validation
            const inputWrapper = input.parentElement;
            if (inputWrapper) {
                inputWrapper.classList.remove(config.classes.inputValid, config.classes.inputInvalid);
            }
            
            // Utiliser la fonction externe removeWarning si disponible
            if (typeof removeWarning === 'function') {
                removeWarning(input);
                return;
            }
            
            // Fallback: suppression manuelle des messages d'erreur
            if (inputWrapper) {
                let nextElem = inputWrapper.nextElementSibling;
                while (nextElem) {
                    if (nextElem.classList.contains('input-warning') || 
                        nextElem.classList.contains('password-strength-meter')) {
                        nextElem.remove();
                        nextElem = inputWrapper.nextElementSibling;
                    } else {
                        nextElem = nextElem.nextElementSibling;
                    }
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
            
            const hasValidatedFields = this.state.validated.size > 0;
            
            // Plutôt que de jouer avec display et visibility, on utilise une classe
            if (hasValidatedFields) {
                // Si les boutons ne sont pas déjà visibles, les afficher
                if (!submitBtn.classList.contains(config.classes.visible)) {
                    // Définir display avant d'ajouter la classe visible
                    submitBtn.style.display = 'inline-flex';
                    cancelAllBtn.style.display = 'inline-flex';
                    
                    // Permettre au navigateur de traiter le changement de display
                    setTimeout(() => {
                        submitBtn.classList.add(config.classes.visible);
                        cancelAllBtn.classList.add(config.classes.visible);
                    }, 10);
                }
            } else {
                // Si les boutons sont visibles, les cacher
                if (submitBtn.classList.contains(config.classes.visible)) {
                    // Enlever d'abord la classe visible
                    submitBtn.classList.remove(config.classes.visible);
                    cancelAllBtn.classList.remove(config.classes.visible);
                    
                    // Attendre la fin de la transition avant de changer display
                    setTimeout(() => {
                        if (!this.state.validated.size) { // double vérification
                            submitBtn.style.display = 'none';
                            cancelAllBtn.style.display = 'none';
                        }
                    }, config.transitions.buttons);
                }
            }
        },
        
        // Réinitialisation de tous les champs
        resetAllFields() {
            // Réinitialiser les champs validés
            this.state.validated.forEach(field => {
                const input = document.getElementById(field);
                if (!input) return;
                
                input.value = input.getAttribute('data-original-value') || '';
                input.disabled = true;
                this.toggleEditingClass(field, false);
                this.cleanupValidationUI(input);
            });
            
            // Réinitialiser les champs en cours d'édition
            this.state.editingFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input) return;
                
                input.value = input.getAttribute('data-original-value') || '';
                input.disabled = true;
                this.toggleEditingClass(field, false);
                
                const editBtn = utils.getFieldControl('editBtns', field);
                const validateBtn = utils.getFieldControl('validateBtns', field);
                const cancelBtn = utils.getFieldControl('cancelBtns', field);
                
                if (editBtn) editBtn.style.display = 'inline-flex';
                if (validateBtn) validateBtn.style.display = 'none';
                if (cancelBtn) cancelBtn.style.display = 'none';
                
                this.cleanupValidationUI(input);
                
                if (field === 'password' && this.elements.passwordToggle) {
                    this.elements.passwordToggle.style.display = 'none';
                    input.setAttribute('type', 'password');
                    if (this.elements.toggleIcon) {
                        this.elements.toggleIcon.src = '../img/svg/eye.svg';
                    }
                }
            });
            
            // Réinitialiser l'état
            this.state.validated.clear();
            this.state.editingFields.clear();
            
            // Mettre à jour l'UI après un court délai
            setTimeout(() => {
                this.updateActionButtons();
            }, 50);
        },
        
        // Afficher une notification
        showNotification(type, message) {
            // Supprimer les notifications existantes
            utils.getElements(config.selectors.notifications).forEach(notification => {
                this.fadeOutAndRemove(notification);
            });
            
            // Créer une nouvelle notification
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            const icon = document.createElement('img');
            icon.src = type === 'success' ? '../img/svg/check-circle.svg' : '../img/svg/alert-circle.svg';
            icon.alt = type === 'success' ? 'Succès' : 'Erreur';
            
            const text = document.createElement('p');
            text.textContent = message;
            
            const closeBtn = document.createElement('button');
            closeBtn.className = 'close-notification';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', () => this.fadeOutAndRemove(notification));
            
            notification.appendChild(icon);
            notification.appendChild(text);
            notification.appendChild(closeBtn);
            
            // Ajouter la notification au document
            document.body.insertBefore(notification, document.body.firstChild);
            
            // Fermeture automatique après 5 secondes
            setTimeout(() => this.fadeOutAndRemove(notification), 5000);
        },
        
        // Afficher l'indicateur de chargement
        showLoading() {
            const { submitBtn } = this.elements;
            if (!submitBtn) return;
            
            // Définir un timestamp de début de chargement
            this.loadingStartTime = Date.now();
            
            // Appliquer la classe de chargement
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            const originalText = submitBtn.querySelector('span');
            if (originalText) {
                originalText.setAttribute('data-original-text', originalText.textContent);
                originalText.textContent = 'Traitement en cours...';
            }
        },
        
        // Masquer l'indicateur de chargement
        hideLoading() {
            const { submitBtn } = this.elements;
            if (!submitBtn) return;
            
            // Calculer le temps écoulé depuis le début du chargement
            const elapsedTime = Date.now() - (this.loadingStartTime || 0);
            const minLoadingTime = 800; // Temps minimum de chargement en ms
            
            // Si le temps écoulé est inférieur au minimum, attendre avant de cacher
            if (elapsedTime < minLoadingTime) {
                setTimeout(() => {
                    this.hideLoadingImmediately();
                    this.processPendingNotification();
                }, minLoadingTime - elapsedTime);
            } else {
                this.hideLoadingImmediately();
                this.processPendingNotification();
            }
        },
        
        // Masquer immédiatement l'indicateur de chargement
        hideLoadingImmediately() {
            const { submitBtn } = this.elements;
            if (!submitBtn) return;
            
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            
            const textElement = submitBtn.querySelector('span');
            if (textElement && textElement.hasAttribute('data-original-text')) {
                textElement.textContent = textElement.getAttribute('data-original-text');
                textElement.removeAttribute('data-original-text');
            }
        },
        
        // Traiter la notification en attente
        processPendingNotification() {
            if (!this.pendingNotification) return;
            
            const { type, message, data } = this.pendingNotification;
            
            // Afficher la notification
            this.showNotification(type, message);
            
            // Appliquer les changements appropriés en fonction du type
            if (type === 'success' && data && data.success) {
                // Mettre à jour les valeurs originales des champs modifiés
                this.state.validated.forEach(field => {
                    const input = document.getElementById(field);
                    if (input) {
                        input.setAttribute('data-original-value', input.value);
                    }
                });
                
                // Réinitialiser l'état
                this.state.validated.clear();
                this.state.editingFields.clear();
                
                // Désactiver tous les inputs
                this.disableAllInputs();
                
                // Mettre à jour l'UI après un court délai
                setTimeout(() => {
                    this.updateActionButtons();
                }, 50);
            } else if (type === 'error') {
                // Restaurer les valeurs originales
                this.resetAllFields();
            }
            
            // Nettoyer la notification en attente
            this.pendingNotification = null;
        }
    };
    
    // Initialisation du gestionnaire de profil
    profileManager.init();
}); 