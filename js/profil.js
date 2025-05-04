document.addEventListener('DOMContentLoaded', function() {
    // Script pour réactiver le bouton de sauvegarde
    const submitProfileBtn = document.getElementById('submit-profile-btn');
    
    if (submitProfileBtn) {
        // Supprimer les attributs de désactivation ajoutés par form-validation.js
        setTimeout(() => {
            submitProfileBtn.classList.remove('disabled-button');
            submitProfileBtn.disabled = false;
        }, 100);
    }

    // Gestion des notifications
    document.querySelectorAll('.close-notification').forEach(button => {
        button.addEventListener('click', () => {
            const notification = button.closest('.notification');
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    });

    setTimeout(() => {
        document.querySelectorAll('.notification').forEach(notification => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    }, 5000);

    const validated = new Set();
    const editingFields = new Set();
    const submitBtn = document.getElementById('submit-profile-btn');
    const cancelAllBtn = document.getElementById('cancel-all-btn');

    function toggleEditingClass(field, isEditing) {
        const fieldValue = document.querySelector(`.field-value:has(#${field})`);
        if (fieldValue) {
            if (isEditing) {
                fieldValue.classList.add('editing');
            } else {
                fieldValue.classList.remove('editing');
            }
        }
    }

    function updateActionButtons() {
        if (validated.size > 0) {
            // Afficher les boutons avec animation
            submitBtn.style.display = 'inline-flex';
            cancelAllBtn.style.display = 'inline-flex';
            
            // S'assurer que le bouton n'est pas désactivé
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled-button');

            // Délai court pour permettre au navigateur de traiter le changement de display
            setTimeout(() => {
                submitBtn.classList.add('visible');
                cancelAllBtn.classList.add('visible');
            }, 10);
        } else {
            // Cacher les boutons avec animation
            submitBtn.classList.remove('visible');
            cancelAllBtn.classList.remove('visible');

            // Attendre que l'animation soit terminée avant de les cacher complètement
            setTimeout(() => {
                submitBtn.style.display = 'none';
                cancelAllBtn.style.display = 'none';
            }, 300); // Durée de la transition
        }
    }

    // Fonction pour vérifier la validité d'un champ
    function validateField(field) {
        const input = document.getElementById(field);
        const validateBtn = document.querySelector(`.field-validate[data-field="${field}"]`);
        
        if (!input || !validateBtn) return false;

        let isValid = true;
        
        if (field === 'email') {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            isValid = emailRegex.test(input.value.trim());
        } else if (field === 'password') {
            // Si le champ est vide ou ne respecte pas les critères
            if (input.value === '') {
                isValid = false;
            } else {
                const criteriaCount = [
                    input.value.length >= 8,
                    /[a-z]/.test(input.value),
                    /[A-Z]/.test(input.value),
                    /[0-9]/.test(input.value),
                    /[^A-Za-z0-9]/.test(input.value)
                ].filter(Boolean).length;
                isValid = criteriaCount === 5;
            }
        } else {
            // Pour les autres champs, vérifier simplement qu'ils ne sont pas vides
            isValid = input.value.trim() !== '';
        }
        
        // Mettre à jour l'état du bouton de validation
        if (isValid) {
            validateBtn.classList.remove('disabled-button');
            validateBtn.disabled = false;
        } else {
            validateBtn.classList.add('disabled-button');
            validateBtn.disabled = true;
        }
        
        return isValid;
    }

    // Fonction pour réinitialiser tous les champs modifiés
    function resetAllFields() {
        // Réinitialiser les champs validés
        validated.forEach(field => {
            const input = document.getElementById(field);
            input.value = input.dataset.originalValue;
            input.disabled = true;
            toggleEditingClass(field, false);
            
            // Supprimer les avertissements pour ce champ
            if (typeof removeWarning === 'function') {
                removeWarning(input);
            }
            
            // Retirer les classes de validation
            input.parentElement.classList.remove('input-valid', 'input-invalid');
        });

        // Réinitialiser les champs en cours d'édition
        editingFields.forEach(field => {
            const input = document.getElementById(field);
            const editBtn = document.querySelector(`.field-edit[data-field="${field}"]`);
            const validateBtn = document.querySelector(`.field-validate[data-field="${field}"]`);
            const cancelBtn = document.querySelector(`.field-cancel[data-field="${field}"]`);

            input.value = input.dataset.originalValue;
            input.disabled = true;
            toggleEditingClass(field, false);

            if (editBtn) editBtn.style.display = 'inline-flex';
            if (validateBtn) validateBtn.style.display = 'none';
            if (cancelBtn) cancelBtn.style.display = 'none';
            
            // Supprimer les avertissements pour ce champ
            if (typeof removeWarning === 'function') {
                removeWarning(input);
            }
            
            // Retirer les classes de validation
            input.parentElement.classList.remove('input-valid', 'input-invalid');
        });

        validated.clear();
        editingFields.clear();
        updateActionButtons();
    }

    // Gestionnaire pour le bouton d'annulation globale
    cancelAllBtn.addEventListener('click', resetAllFields);

    document.querySelectorAll('.field-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = btn.dataset.field;
            const input = document.getElementById(field);
            const validate = document.querySelector(`.field-validate[data-field="${field}"]`);
            const cancel = document.querySelector(`.field-cancel[data-field="${field}"]`);

            // Stocker la valeur originale si c'est la première fois qu'on édite
            if (!editingFields.has(field)) {
                // Conserver la valeur originale avant toute modification
                input.setAttribute('data-original-value', input.value);
                editingFields.add(field);
            }

            input.disabled = false;
            input.focus();
            toggleEditingClass(field, true);
            btn.style.display = 'none';
            validate.style.display = 'inline-flex';
            cancel.style.display = 'inline-flex';
            
            // Afficher le bouton de toggle pour le mot de passe
            if (field === 'password') {
                document.getElementById('password-toggle').style.display = 'flex';
            }
            
            // Vérifier initialement la validité et mettre à jour l'état du bouton
            validateField(field);
            
            // Si c'est un champ email ou mot de passe, déclencher la validation
            if (field === 'email' || field === 'password') {
                const event = new Event('input');
                input.dispatchEvent(event);
            }
        });
    });

    // Ajouter la fonctionnalité de toggle du mot de passe
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');

    if (passwordToggle && passwordInput && toggleIcon) {
        passwordToggle.addEventListener('click', function() {
            // Changer le type de l'input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Changer l'icône
            toggleIcon.src = type === 'password' ? '../img/svg/eye.svg' : '../img/svg/eye-slash.svg';
            toggleIcon.alt = type === 'password' ? 'Afficher' : 'Masquer';
        });
    }

    // Ajouter des écouteurs d'événements pour tous les champs de saisie
    document.querySelectorAll('.profile-input').forEach(input => {
        input.addEventListener('input', function() {
            const field = this.id;
            validateField(field);
        });
        
        input.addEventListener('keyup', function() {
            const field = this.id;
            validateField(field);
        });
    });

    // Masquer le bouton toggle lors de l'annulation
    document.querySelectorAll('.field-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const field = this.dataset.field;
            if (field === 'password') {
                document.getElementById('password-toggle').style.display = 'none';
                // Réinitialiser le type et l'icône
                passwordInput.setAttribute('type', 'password');
                toggleIcon.src = '../img/svg/eye.svg';
                toggleIcon.alt = 'Afficher';
            }
        });
    });

    // Délégation d'événements pour le clic sur les boutons "validate" et "cancel"
    document.addEventListener('click', (event) => {
        const validateBtn = event.target.closest('.field-validate');
        const cancelBtn = event.target.closest('.field-cancel');

        if (validateBtn && !validateBtn.disabled) {
            const field = validateBtn.dataset.field;
            const input = document.getElementById(field);
            
            // Vérifier une dernière fois si le champ est valide
            if (!validateField(field)) {
                return;
            }
            
            const originalValue = input.getAttribute('data-original-value');
            const hasChanged = originalValue !== input.value;
            input.disabled = true;
            toggleEditingClass(field, false);
            validateBtn.style.display = 'none';
            document.querySelector(`.field-cancel[data-field="${field}"]`).style.display = 'none';
            document.querySelector(`.field-edit[data-field="${field}"]`).style.display = 'inline-flex';
            
            // Cacher le bouton toggle pour le mot de passe
            if (field === 'password') {
                document.getElementById('password-toggle').style.display = 'none';
                // Réinitialiser à type password
                input.setAttribute('type', 'password');
            }
            
            if (hasChanged) validated.add(field);
            else validated.delete(field);
            updateActionButtons();
        }

        if (cancelBtn) {
            const field = cancelBtn.dataset.field;
            const input = document.getElementById(field);
            input.value = input.dataset.originalValue;
            input.disabled = true;
            toggleEditingClass(field, false);
            cancelBtn.style.display = 'none';
            document.querySelector(`.field-validate[data-field="${field}"]`).style.display = 'none';
            document.querySelector(`.field-edit[data-field="${field}"]`).style.display = 'inline-flex';
            editingFields.delete(field);
            if (editingFields.size === 0 && validated.size === 0) {
                cancelAllBtn.style.display = 'none';
            }
            
            // Supprimer les indications d'erreur et les avertissements
            if (field === 'email' || field === 'password') {
                // Retirer les classes de validation
                input.parentElement.classList.remove('input-valid', 'input-invalid');
                
                // Utiliser la fonction removeWarning de form-validation.js pour supprimer les messages
                if (typeof removeWarning === 'function') {
                    removeWarning(input);
                } else {
                    // Fallback: suppression manuelle des éléments
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
                        const warnings = parentField.querySelectorAll('.input-warning');
                        warnings.forEach(warning => warning.remove());
                    }
                }
            }
        }
    });

    document.querySelectorAll('.profile-input').forEach(input => {
        input.addEventListener('focus', () => {
            const wrapper = input.closest('.input-wrapper');
            if (wrapper) wrapper.classList.add('focused');
        });

        input.addEventListener('blur', () => {
            const wrapper = input.closest('.input-wrapper');
            if (wrapper) wrapper.classList.remove('focused');
        });
    });

    const profileForm = document.getElementById('profileForm');

    profileForm.addEventListener('submit', (event) => {
        // Vérifier toutes les validations avant soumission
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        let isValid = true;
        
        // Si email est en édition, vérifier sa validité
        if (editingFields.has('email')) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                isValid = false;
                event.preventDefault();
            }
        }
        
        // Si mot de passe est en édition, vérifier sa validité
        if (editingFields.has('password') && passwordInput.value !== '') {
            const criteriaCount = [
                passwordInput.value.length >= 8,
                /[a-z]/.test(passwordInput.value),
                /[A-Z]/.test(passwordInput.value),
                /[0-9]/.test(passwordInput.value),
                /[^A-Za-z0-9]/.test(passwordInput.value)
            ].filter(Boolean).length;
            
            if (criteriaCount < 5) {
                isValid = false;
                event.preventDefault();
            }
        }
        
        if (isValid) {
            // Activer tous les champs pour l'envoi du formulaire
            document.querySelectorAll('.profile-input').forEach(input => {
                input.disabled = false;
            });
        }
    });
    
    // Ajout d'un gestionnaire de clic sur le bouton de soumission
    submitBtn.addEventListener('click', (event) => {
        // Activer tous les champs avant la soumission
        document.querySelectorAll('.profile-input').forEach(input => {
            input.disabled = false;
        });
        
        // Forcer la soumission du formulaire
        profileForm.submit();
    });
}); 