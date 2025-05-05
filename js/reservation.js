document.addEventListener('DOMContentLoaded', function() {
    // Configuration centralisée
    const config = {
        selectors: {
            // Étape 1
            nbPersonne: '#nb_personne',
            reservationForm: '#reservationForm',
            dateDebut: '#date-debut',
            dateFin: '#date-fin',
            dateDebutVisible: '#date-debut-visible',
            dateFinVisible: '#date-fin-visible',
            nbPersonnesDisplay: '#nb_personnes_display',
            prixTotal: '#prix_total',
            submitButton: '#reservationForm button[type="submit"]',
            prixBaseElement: '.price-row:first-child span:last-child',
            
            // Étape 2
            travelersForm: '#travelersForm',
            autofillButton: '#autofill-button',
            passportInputs: 'input[id^="passport_"]',
            requiredInputs: 'input[required], select[required]',
            
            // Étape 3
            optionsForm: '#optionsForm',
            participantToggles: '.participant-toggle',
            participantCheckbox: '.participant-checkbox',
            optionsPrice: '.options-row span:last-child',
            totalPrice: '.price-total span:last-child',
            
        },
        classes: {
            selected: 'selected',
            invalid: 'invalid',
            priceUpdated: 'price-updated'
        },
        timing: {
            priceAnimationDuration: 700
        }
    };

    // Utilitaires
    const utils = {
        // Formater un prix en format français
        formatPrice: (price) => {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price).replace('€', '').trim();
        },
        
        // Extraire un nombre d'une chaîne de prix
        extractPrice: (priceString) => {
            return parseFloat(priceString.replace(/[^\d,]/g, '').replace(',', '.'));
        }
    };

    // Gestionnaire de réservation
    const reservationManager = {
        // Initialisation
        init() {
            // Déterminer sur quelle page nous sommes
            const pathname = window.location.pathname;
            const isEtape1 = pathname.includes('etape1.php');
            const isEtape2 = pathname.includes('etape2.php');
            const isEtape3 = pathname.includes('etape3.php');

            // Initialiser les fonctionnalités spécifiques à chaque étape
            if (isEtape1) {
                this.initializeEtape1();
            } else if (isEtape2) {
                this.initializeEtape2();
            } else if (isEtape3) {
                this.initializeEtape3();
            }
        },

        // ÉTAPE 1 - Dates et nombre de voyageurs
        initializeEtape1() {
            this.setupNbPersonneInput();
            this.setupDateFields();
            this.setupReservationFormValidation();
        },

        // Configuration du champ nombre de personnes
        setupNbPersonneInput() {
            const nbPersonneInput = document.querySelector(config.selectors.nbPersonne);
            if (!nbPersonneInput) return;

            nbPersonneInput.addEventListener('input', () => this.updatePriceDisplay());
            this.updatePriceDisplay();
        },

        // Mise à jour de l'affichage du prix
        updatePriceDisplay() {
            const nbPersonneInput = document.querySelector(config.selectors.nbPersonne);
            const prixBaseElement = document.querySelector(config.selectors.prixBaseElement);
            const nbPersonnesDisplay = document.querySelector(config.selectors.nbPersonnesDisplay);
            const prixTotalElement = document.querySelector(config.selectors.prixTotal);
            
            if (!nbPersonneInput || !prixBaseElement || !nbPersonnesDisplay || !prixTotalElement) return;
            
            const nbPersonnes = parseInt(nbPersonneInput.value);
            const prixBase = utils.extractPrice(prixBaseElement.textContent);
            const prixTotal = nbPersonnes * prixBase;

            nbPersonnesDisplay.textContent = nbPersonnes;
            prixTotalElement.textContent = utils.formatPrice(prixTotal) + ' €';
        },

        // Configuration des champs de date
        setupDateFields() {
            const dateDebutInput = document.querySelector(config.selectors.dateDebut);
            const dateFinInput = document.querySelector(config.selectors.dateFin);
            const dateDebutVisible = document.querySelector(config.selectors.dateDebutVisible);
            const dateFinVisible = document.querySelector(config.selectors.dateFinVisible);

            if (!dateDebutInput || !dateFinInput || !dateDebutVisible || !dateFinVisible) return;

            // Initialiser les champs visibles avec des dates formatées si les valeurs existent
            if (dateDebutInput.value) {
                const date = new Date(dateDebutInput.value);
                const options = { day: 'numeric', month: 'short' };
                dateDebutVisible.value = date.toLocaleDateString('fr-FR', options);
            }

            if (dateFinInput.value) {
                const date = new Date(dateFinInput.value);
                const options = { day: 'numeric', month: 'short' };
                dateFinVisible.value = date.toLocaleDateString('fr-FR', options);
            }

            // Ajouter des écouteurs pour la validation
            dateDebutInput.addEventListener('input', () => this.validateEtape1Form());
            dateFinInput.addEventListener('input', () => this.validateEtape1Form());
            
            // Déclencher la validation au démarrage
            this.validateEtape1Form();
        },

        // Configuration de la validation du formulaire
        setupReservationFormValidation() {
            const form = document.querySelector(config.selectors.reservationForm);
            
            // Validation initiale
            this.validateEtape1Form();
            
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (!this.validateEtape1Form()) {
                        e.preventDefault();
                        alert('Veuillez sélectionner des dates d\'arrivée et de départ.');
                    }
                });
            }
        },

        // Validation du formulaire étape 1
        validateEtape1Form() {
            const dateDebutInput = document.querySelector(config.selectors.dateDebut);
            const dateFinInput = document.querySelector(config.selectors.dateFin);
            const submitButton = document.querySelector(config.selectors.submitButton);
            
            if (!dateDebutInput || !dateFinInput || !submitButton) {
                return false;
            }
            
            const isValid = dateDebutInput.value && dateFinInput.value;
            
            submitButton.disabled = !isValid;
            return isValid;
        },

        // ÉTAPE 2 - Informations des voyageurs
        initializeEtape2() {
            this.setupAutofillButton();
            this.setupPassportFields();
            this.setupEtape2FormValidation();
        },

        // Configuration du bouton d'auto-remplissage
        setupAutofillButton() {
            const autoFillButton = document.querySelector(config.selectors.autofillButton);
            if (!autoFillButton) return;
            
            autoFillButton.addEventListener('click', () => this.autofillPrimaryTraveler());
        },

        // Auto-remplissage des informations du voyageur principal
        autofillPrimaryTraveler() {
            const autoFillButton = document.querySelector(config.selectors.autofillButton);
            if (!autoFillButton) return;
            
            // Récupérer les données de l'utilisateur
            const userData = {
                lastName: autoFillButton.getAttribute('data-lastname'),
                firstName: autoFillButton.getAttribute('data-firstname'),
                civilite: autoFillButton.getAttribute('data-civilite'),
                dateNaissance: autoFillButton.getAttribute('data-birthdate'),
                nationalite: autoFillButton.getAttribute('data-nationality'),
                passport: autoFillButton.getAttribute('data-passport')
            };

            // Remplir les champs
            const fields = {
                nom: document.getElementById('nom_1'),
                prenom: document.getElementById('prenom_1'),
                civilite: document.getElementById('civilite_1'),
                dateNaissance: document.getElementById('date_naissance_1'),
                nationalite: document.getElementById('nationalite_1'),
                passport: document.getElementById('passport_1')
            };

            if (fields.nom) fields.nom.value = userData.lastName;
            if (fields.prenom) fields.prenom.value = userData.firstName;
            if (userData.civilite && fields.civilite) fields.civilite.value = userData.civilite;
            if (userData.dateNaissance && fields.dateNaissance) fields.dateNaissance.value = userData.dateNaissance;
            if (userData.nationalite && fields.nationalite) fields.nationalite.value = userData.nationalite;
            
            if (userData.passport && fields.passport) {
                fields.passport.value = userData.passport;
                this.formatPassport(fields.passport);
                this.validatePassport(fields.passport);
            }
            
            // Déclencher la validation après auto-remplissage
            this.validateEtape2Form();
        },

        // Configuration des champs de passeport
        setupPassportFields() {
            const passportInputs = document.querySelectorAll(config.selectors.passportInputs);
            passportInputs.forEach(input => {
                // Formater les valeurs existantes
                if (input.value) {
                    this.formatPassport(input);
                }

                // Ajouter des écouteurs pour le formatage à la saisie
                input.addEventListener('input', () => {
                    this.formatPassport(input);
                    this.validatePassport(input);
                    this.validateEtape2Form();
                });

                input.addEventListener('keydown', (e) => {
                    // Autoriser uniquement les touches valides : chiffres, navigation, etc.
                    if (
                        e.key === 'Backspace' ||
                        e.key === 'Delete' ||
                        e.key === 'ArrowLeft' ||
                        e.key === 'ArrowRight' ||
                        e.key === 'Tab' ||
                        (e.key >= '0' && e.key <= '9')
                    ) {
                        return true;
                    }

                    // Bloquer tous les autres caractères
                    e.preventDefault();
                    return false;
                });
            });
        },

        // Formatage du numéro de passeport avec des espaces (XXX XXX XXX X)
        formatPassport(input) {
            // Supprimer tous les caractères non numériques
            let value = input.value.replace(/[^\d]/g, '');
            
            // Limiter à 10 chiffres
            value = value.slice(0, 10);
            
            // Formater avec des espaces (XXX XXX XXX X)
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i === 3 || i === 6 || i === 9) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            // Mettre à jour la valeur de l'input
            input.value = formattedValue;
        },

        // Validation d'un champ de passeport
        validatePassport(input) {
            const errorElement = document.getElementById(input.id + '_error');
            if (!errorElement) return;
            
            // Récupérer le conteneur form-field
            const formField = input.closest('.form-field');
            
            const digitsOnly = input.value.replace(/[^\d]/g, '');
            
            if (digitsOnly.length === 0) {
                // L'input est vide, supprimer tout message d'erreur
                errorElement.style.opacity = '0';
                formField.classList.remove(config.classes.invalid);
            } else if (digitsOnly.length !== 10) {
                // L'input a du contenu mais pas exactement 10 chiffres
                errorElement.style.opacity = '1';
                formField.classList.add(config.classes.invalid);
            } else {
                // L'input a exactement 10 chiffres
                errorElement.style.opacity = '0';
                formField.classList.remove(config.classes.invalid);
            }
        },

        // Configuration de la validation du formulaire étape 2
        setupEtape2FormValidation() {
            const form = document.querySelector(config.selectors.travelersForm);
            if (!form) return;
            
            // Utiliser la délégation d'événements pour la validation
            form.addEventListener('input', (e) => {
                if (e.target.hasAttribute('required') || e.target.id.startsWith('passport_')) {
                    this.validateEtape2Form();
                }
            });
            
            form.addEventListener('change', (e) => {
                if (e.target.hasAttribute('required')) {
                    this.validateEtape2Form();
                }
            });

            // Validation initiale
            this.validateEtape2Form();
        },

        // Validation du formulaire étape 2
        validateEtape2Form() {
            const form = document.querySelector(config.selectors.travelersForm);
            const submitButton = document.getElementById('submit-button');
            if (!form || !submitButton) return;
            
            let isValid = true;

            // Vérifier tous les champs obligatoires
            const requiredInputs = form.querySelectorAll(config.selectors.requiredInputs);
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                }
                
                // Validation spéciale pour les numéros de passeport
                if (input.id.startsWith('passport_')) {
                    const digitsOnly = input.value.replace(/[^\d]/g, '');
                    if (digitsOnly.length !== 10 && digitsOnly.length !== 0) {
                        isValid = false;
                    }
                    
                    // S'il n'y a pas de valeur, nous n'affichons pas de bordure rouge 
                    // mais le champ est toujours requis
                    if (digitsOnly.length === 0) {
                        isValid = false;
                    }
                }
            });

            // Vérifier si un champ form-field a la classe invalid
            const invalidFields = form.querySelectorAll('.form-field.' + config.classes.invalid);
            if (invalidFields.length > 0) {
                isValid = false;
            }

            // Mettre à jour l'état du bouton de soumission
            submitButton.disabled = !isValid;
        },

        // ÉTAPE 3 - Sélection des options
        initializeEtape3() {
            this.setupOptionSelectors();
        },

        // Configuration des sélecteurs d'options
        setupOptionSelectors() {
            const optionsForm = document.querySelector(config.selectors.optionsForm);
            if (!optionsForm) return;
            
            // Extraire les éléments d'affichage de prix
            const priceElements = {
                options: document.querySelector(config.selectors.optionsPrice),
                total: document.querySelector(config.selectors.totalPrice),
                base: document.querySelector(config.selectors.prixBaseElement)
            };
            
            if (!priceElements.base) return;
            
            // Extraire le prix de base une seule fois
            const prixBase = utils.extractPrice(priceElements.base.textContent);
            
            // Initialiser le prix des options à partir des sélections existantes
            this.calculateInitialOptionsPrice(priceElements, prixBase);
            
            // Utiliser la délégation d'événements pour les toggles de participants
            optionsForm.addEventListener('click', (e) => {
                const toggle = e.target.closest(config.selectors.participantToggles);
                if (toggle) {
                    this.handleParticipantToggle(e, toggle);
                    this.updateOptionsPrice(priceElements, prixBase);
                }
            });
        },

        // Calcul du prix initial des options sélectionnées
        calculateInitialOptionsPrice(priceElements, prixBase) {
            if (!priceElements.options || !priceElements.total) return;
            
            const allCheckboxes = document.querySelectorAll(config.selectors.participantCheckbox + ':checked');
            let prixOptions = 0;
            
            allCheckboxes.forEach(box => {
                const optionItem = box.closest('.option-item');
                if (!optionItem) return;
                
                const priceText = optionItem.querySelector('.option-price')?.textContent;
                if (!priceText) return;
                
                const price = utils.extractPrice(priceText);
                prixOptions += price;
            });
            
            // Mettre à jour l'affichage
            priceElements.options.textContent = utils.formatPrice(prixOptions) + ' €';
            priceElements.total.textContent = utils.formatPrice(prixBase + prixOptions) + ' €';
        },

        // Gestion du clic sur un toggle de participant
        handleParticipantToggle(e, toggleElement) {
            const checkbox = toggleElement.querySelector('input[type="checkbox"]');
            if (!checkbox) return;
            
            if (e.target.tagName.toLowerCase() !== 'input') {
                checkbox.checked = !checkbox.checked;
                e.preventDefault();
            }
            
            // Mettre à jour le style du toggle
            toggleElement.classList.toggle(config.classes.selected, checkbox.checked);
        },

        // Mise à jour du prix des options
        updateOptionsPrice(priceElements, prixBase) {
            if (!priceElements.options || !priceElements.total) return;
            
            const allCheckboxes = document.querySelectorAll(config.selectors.participantCheckbox);
            let prixOptions = 0;
            
            allCheckboxes.forEach(box => {
                if (box.checked) {
                    // Extraire le prix depuis le DOM
                    const optionItem = box.closest('.option-item');
                    if (!optionItem) return;
                    
                    const priceText = optionItem.querySelector('.option-price')?.textContent;
                    if (!priceText) return;
                    
                    const price = utils.extractPrice(priceText);
                    prixOptions += price;
                }
            });
            
            // Mettre à jour les affichages de prix avec animation
            priceElements.options.textContent = utils.formatPrice(prixOptions) + ' €';
            priceElements.options.classList.add(config.classes.priceUpdated);
            
            priceElements.total.textContent = utils.formatPrice(prixBase + prixOptions) + ' €';
            priceElements.total.classList.add(config.classes.priceUpdated);
            
            // Supprimer la classe d'animation après la transition
            setTimeout(() => {
                priceElements.options.classList.remove(config.classes.priceUpdated);
                priceElements.total.classList.remove(config.classes.priceUpdated);
            }, config.timing.priceAnimationDuration);
        },

    };

    // Initialisation du gestionnaire de réservation
    reservationManager.init();
    
    // Pour qu'il soit accessible par calendar.js
    window.reservationManager = reservationManager;
});