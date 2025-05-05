document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        selectors: {
            // Champs de date visibles et cachés
            dateDebutVisible: '#date-debut-visible',
            dateFinVisible: '#date-fin-visible',
            dateDebutInput: '#date-debut',
            dateFinInput: '#date-fin',
            
            // Conteneur du calendrier
            calendarDropdown: '#calendar-dropdown',
            monthContainers: '.month-container',
            
            // Navigation du calendrier
            prevMonthBtn: '.prev-month',
            nextMonthBtn: '.next-month',
            
            // Boutons d'action
            resetDatesBtn: '#reset-dates',
            applyDatesBtn: '#apply-dates',
            
            // Sélecteurs du calendrier
            calendarDay: '.calendar-day',
            calendarGrid: '.calendar-grid',
            monthName: '.month-name',
            calendarSelectionMessage: '.calendar-selection-message',
            calendarFooter: '.calendar-footer'
        },
        classes: {
            active: 'active',
            calendarDay: 'calendar-day',
            empty: 'empty',
            pastDay: 'past-day',
            today: 'today',
            inRange: 'in-range',
            startDate: 'start-date',
            endDate: 'end-date',
            previewInRange: 'preview-in-range',
            previewEndDate: 'preview-end-date'
        },
        constants: {
            MONTH_NAMES: [
                "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
            ]
        },
        timing: {
            previewDelay: 100
        }
    };

    // Utilitaires
    const utils = {
        // Utilitaires de date
        date: {
            isInRange: (date, startDate, endDate) => {
                if (!startDate || !endDate) return false;
                return date >= startDate && date <= endDate;
            },
            
            isSameDate: (date1, date2) => {
                if (!date1 || !date2) return false;
                return date1.getFullYear() === date2.getFullYear() &&
                       date1.getMonth() === date2.getMonth() &&
                       date1.getDate() === date2.getDate();
            },
            
            formatForInput: (date) => {
                if (!date) return "";
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, "0");
                const day = String(date.getDate()).padStart(2, "0");
                return `${year}-${month}-${day}`;
            },
            
            formatForDisplay: (date) => {
                if (!date) return "";
                const formatOptions = { day: "numeric", month: "short" };
                return date.toLocaleDateString("fr-FR", formatOptions);
            }
        },
        
        // Utilitaires DOM
        dom: {
            getElement: (selector) => {
                return document.querySelector(selector);
            },
            
            getAllElements: (selector) => {
                return document.querySelectorAll(selector);
            },
            
            createElement: (tagName, classes = [], text = "") => {
                const element = document.createElement(tagName);
                if (classes.length) element.classList.add(...classes);
                if (text) element.textContent = text;
                return element;
            },
            
            appendDayElement: (grid, text, classes = []) => {
                const element = utils.dom.createElement("div", classes, text);
                grid.appendChild(element);
                return element;
            }
        }
    };

    // Gestionnaire de calendrier
    const calendarManager = {
        // État du calendrier
        state: {
            currentDate: new Date(),
            selectedStartDate: null,
            selectedEndDate: null,
            editMode: null
        },
        
        // Initialisation
        init() {
            this.loadInitialDates();
            this.setupElements();
            this.setupEventListeners();
            this.updateInputDisplay();
            this.initCalendars();
        },
        
        // Chargement des dates initiales
        loadInitialDates() {
            const dateDebutInput = utils.dom.getElement(config.selectors.dateDebutInput);
            const dateFinInput = utils.dom.getElement(config.selectors.dateFinInput);
            
            this.state.selectedStartDate = dateDebutInput.value ? new Date(dateDebutInput.value) : null;
            this.state.selectedEndDate = dateFinInput.value ? new Date(dateFinInput.value) : null;
        },
        
        // Mise en cache des éléments DOM fréquemment utilisés
        setupElements() {
            this.elements = {
                dateDebutVisible: utils.dom.getElement(config.selectors.dateDebutVisible),
                dateFinVisible: utils.dom.getElement(config.selectors.dateFinVisible),
                dateDebutInput: utils.dom.getElement(config.selectors.dateDebutInput),
                dateFinInput: utils.dom.getElement(config.selectors.dateFinInput),
                calendarDropdown: utils.dom.getElement(config.selectors.calendarDropdown),
                prevMonthBtn: utils.dom.getElement(config.selectors.prevMonthBtn),
                nextMonthBtn: utils.dom.getElement(config.selectors.nextMonthBtn),
                monthContainers: utils.dom.getAllElements(config.selectors.monthContainers),
                resetDatesBtn: utils.dom.getElement(config.selectors.resetDatesBtn),
                applyDatesBtn: utils.dom.getElement(config.selectors.applyDatesBtn)
            };
        },
        
        // Configuration des écouteurs d'événements
        setupEventListeners() {
            // Date de début
            this.elements.dateDebutVisible.addEventListener("click", () => {
                this.state.editMode = "start";
                this.toggleCalendar();
            });
            
            // Date de fin
            this.elements.dateFinVisible.addEventListener("click", () => {
                if (!this.state.selectedStartDate) {
                    this.state.editMode = "start";
                    this.toggleCalendar();
                    return;
                }
                
                this.state.editMode = "end";
                this.toggleCalendar();
                
                // Appliquer prévisualisation
                if (this.state.selectedStartDate) {
                    setTimeout(() => this.applyEndDatePreview(), config.timing.previewDelay);
                }
            });
            
            // Navigation dans le calendrier
            this.elements.prevMonthBtn.addEventListener("click", () => this.showPreviousMonths());
            this.elements.nextMonthBtn.addEventListener("click", () => this.showNextMonths());
            
            // Boutons de réinitialisation et d'application
            if (this.elements.resetDatesBtn) {
                this.elements.resetDatesBtn.addEventListener("click", (e) => this.resetDates(e));
            }
            
            if (this.elements.applyDatesBtn) {
                this.elements.applyDatesBtn.addEventListener("click", () => this.submitForm());
            }
            
            // Fermeture du dropdown en cliquant à l'extérieur
            document.addEventListener("click", (e) => {
                const dropdown = this.elements.calendarDropdown;
                if (!dropdown.contains(e.target) && 
                    e.target !== this.elements.dateDebutVisible && 
                    e.target !== this.elements.dateFinVisible && 
                    dropdown.classList.contains(config.classes.active)) {
                    dropdown.classList.remove(config.classes.active);
                }
            });
            
            // Fermeture du dropdown en cliquant sur d'autres inputs
            document.addEventListener("focusin", (e) => {
                if (e.target.tagName === "INPUT" && 
                    e.target !== this.elements.dateDebutVisible && 
                    e.target !== this.elements.dateFinVisible && 
                    this.elements.calendarDropdown.classList.contains(config.classes.active)) {
                    this.elements.calendarDropdown.classList.remove(config.classes.active);
                }
            });
        },
        
        // Affiche/masque le calendrier
        toggleCalendar() {
            const { calendarDropdown } = this.elements;
            calendarDropdown.classList.toggle(config.classes.active);
            
            if (calendarDropdown.classList.contains(config.classes.active)) {
                this.state.currentDate = new Date();
                this.initCalendars();
            }
        },
        
        // Initialisation des calendriers
        initCalendars() {
            const firstMonth = new Date(this.state.currentDate);
            const secondMonth = new Date(this.state.currentDate);
            secondMonth.setMonth(secondMonth.getMonth() + 1);
            
            this.renderMonth(this.elements.monthContainers[0], firstMonth);
            this.renderMonth(this.elements.monthContainers[1], secondMonth);
        },
        
        // Rendu d'un mois du calendrier
        renderMonth(container, date) {
            // Mise à jour du nom du mois
            container.querySelector(config.selectors.monthName).textContent = 
                `${config.constants.MONTH_NAMES[date.getMonth()]} ${date.getFullYear()}`;
            
            // Récupérer la grille et supprimer les jours existants
            const grid = container.querySelector(config.selectors.calendarGrid);
            grid.querySelectorAll(config.selectors.calendarDay).forEach(el => el.remove());
            
            // Calcul des jours du mois
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            let firstDayOfWeek = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
            firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
            
            // Date actuelle pour la mise en évidence "aujourd'hui"
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Ajouter les cellules vides pour les jours avant le début du mois
            for (let i = 0; i < firstDayOfWeek; i++) {
                utils.dom.appendDayElement(grid, "", [config.classes.calendarDay, config.classes.empty]);
            }
            
            // Ajouter les jours du mois
            for (let day = 1; day <= lastDay; day++) {
                const currentDayDate = new Date(date.getFullYear(), date.getMonth(), day);
                const isPastDay = currentDayDate < today;
                const isBeforeStartDateInEndMode = this.state.editMode === "end" && 
                                               this.state.selectedStartDate && 
                                               currentDayDate < this.state.selectedStartDate;
                
                // Créer l'élément du jour
                const dayElement = utils.dom.appendDayElement(grid, day, [config.classes.calendarDay]);
                
                // Appliquer les classes et états appropriés
                if (isPastDay || isBeforeStartDateInEndMode) {
                    dayElement.classList.add(config.classes.pastDay);
                    dayElement.style.opacity = "0.5";
                    dayElement.style.cursor = "not-allowed";
                } else {
                    dayElement.addEventListener("click", () => this.handleDayClick(currentDayDate));
                    
                    // Gestion de l'aperçu au survol
                    if ((this.state.selectedStartDate && !this.state.selectedEndDate) || 
                        this.state.editMode === "end") {
                        dayElement.addEventListener("mouseover", () => {
                            if (currentDayDate > this.state.selectedStartDate) {
                                this.highlightRange(this.state.selectedStartDate, currentDayDate);
                            }
                        });
                        
                        dayElement.addEventListener("mouseout", () => {
                            this.clearHighlights();
                            this.renderCalendars();
                        });
                    }
                }
                
                // Appliquer les classes spéciales
                if (utils.date.isSameDate(currentDayDate, today)) {
                    dayElement.classList.add(config.classes.today);
                }
                if (this.isDateInRange(currentDayDate)) {
                    dayElement.classList.add(config.classes.inRange);
                }
                if (utils.date.isSameDate(currentDayDate, this.state.selectedStartDate)) {
                    dayElement.classList.add(config.classes.startDate);
                }
                if (utils.date.isSameDate(currentDayDate, this.state.selectedEndDate)) {
                    dayElement.classList.add(config.classes.endDate);
                }
            }
        },
        
        // Gestion du clic sur un jour
        handleDayClick(date) {
            if (this.state.editMode === "end" && this.state.selectedStartDate) {
                // En mode édition de date de fin
                if (date < this.state.selectedStartDate) {
                    this.state.selectedStartDate = date;
                    this.state.selectedEndDate = null;
                    this.updateSelectionMessage("Sélectionnez la date de fin");
                    this.renderCalendars();
                    setTimeout(() => this.applyEndDatePreview(), config.timing.previewDelay);
                    return;
                }
                
                // Ignorer si même date que le début
                if (utils.date.isSameDate(date, this.state.selectedStartDate)) return;
                
                this.state.selectedEndDate = date;
                this.applyDates();
                this.elements.calendarDropdown.classList.remove(config.classes.active);
                this.state.editMode = null;
            } else if (!this.state.selectedStartDate || 
                      (this.state.selectedStartDate && this.state.selectedEndDate) || 
                      date < this.state.selectedStartDate) {
                // Nouvelle sélection ou réinitialisation d'une sélection existante
                if (this.state.selectedStartDate && this.state.selectedEndDate) this.resetDates();
                
                this.state.selectedStartDate = date;
                this.state.selectedEndDate = null;
                this.updateSelectionMessage("Sélectionnez la date de fin");
                this.applyDates(); // Mettre à jour la date de début
            } else {
                // Ignorer si même date que le début
                if (utils.date.isSameDate(date, this.state.selectedStartDate)) return;
                
                // Compléter la sélection
                this.state.selectedEndDate = date;
                this.updateSelectionMessage("");
                this.applyDates();
                this.elements.calendarDropdown.classList.remove(config.classes.active);
                this.state.editMode = null;
            }
            
            this.renderCalendars();
        },
        
        // Rendu des deux mois du calendrier
        renderCalendars() {
            const firstMonth = new Date(this.state.currentDate);
            const secondMonth = new Date(this.state.currentDate);
            secondMonth.setMonth(secondMonth.getMonth() + 1);
            
            this.renderMonth(this.elements.monthContainers[0], firstMonth);
            this.renderMonth(this.elements.monthContainers[1], secondMonth);
        },
        
        // Navigation vers les mois précédents
        showPreviousMonths() {
            this.state.currentDate.setMonth(this.state.currentDate.getMonth() - 1);
            this.renderCalendars();
        },
        
        // Navigation vers les mois suivants
        showNextMonths() {
            this.state.currentDate.setMonth(this.state.currentDate.getMonth() + 1);
            this.renderCalendars();
        },
        
        // Vérification si une date est dans la plage sélectionnée
        isDateInRange(date) {
            return utils.date.isInRange(date, this.state.selectedStartDate, this.state.selectedEndDate);
        },
        
        // Application des dates sélectionnées
        applyDates() {
            if (this.state.selectedStartDate) {
                // Mettre à jour les champs cachés
                this.elements.dateDebutInput.value = utils.date.formatForInput(this.state.selectedStartDate);
                this.elements.dateFinInput.value = this.state.selectedEndDate ? 
                                              utils.date.formatForInput(this.state.selectedEndDate) : "";
                
                // Mettre à jour l'affichage
                this.updateInputDisplay();
                
                // Validation du formulaire
                this.validateForm();
            } else {
                // Réinitialiser tous les champs
                this.elements.dateDebutInput.value = "";
                this.elements.dateFinInput.value = "";
                this.elements.dateDebutVisible.value = "";
                this.elements.dateFinVisible.value = "";
                
                // Validation du formulaire
                this.validateForm();
            }
        },
        
        // Mise à jour de l'affichage des champs de date
        updateInputDisplay() {
            // Mise à jour de la date de début
            this.elements.dateDebutVisible.value = utils.date.formatForDisplay(this.state.selectedStartDate);
                
            // Mise à jour de la date de fin
            this.elements.dateFinVisible.value = utils.date.formatForDisplay(this.state.selectedEndDate);
            
            // Gestion du bouton de réinitialisation
            if (this.elements.resetDatesBtn) {
                this.elements.resetDatesBtn.style.display = this.state.selectedStartDate ? "block" : "none";
            }
        },
        
        // Réinitialisation des dates
        resetDates(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Réinitialisation des variables d'état
            this.state.selectedStartDate = null;
            this.state.selectedEndDate = null;
            this.state.editMode = "start";
            
            // Réinitialisation des champs
            this.elements.dateDebutInput.value = "";
            this.elements.dateFinInput.value = "";
            this.elements.dateDebutVisible.value = "";
            this.elements.dateFinVisible.value = "";
            
            // Masquer le bouton de réinitialisation
            if (this.elements.resetDatesBtn) {
                this.elements.resetDatesBtn.style.display = "none";
            }
            
            // Ne pas fermer le calendrier lors de la réinitialisation
            if (e) {
                this.initCalendars();
                this.updateSelectionMessage("Sélectionnez la date d'arrivée");
            }
            
            // Validation du formulaire
            this.validateForm();
        },
        
        // Soumission du formulaire
        submitForm() {
            if (this.state.selectedStartDate) {
                const form = this.elements.dateDebutInput.closest("form");
                if (form) {
                    // S'assurer que le formulaire inclut l'ancre
                    if (!form.action.includes("#")) {
                        form.action = form.action + "#toutes-destinations";
                    }
                    form.submit();
                }
            }
        },
        
        // Mise en évidence de la plage de dates
        highlightRange(startDate, endDate) {
            const allDays = document.querySelectorAll(`${config.selectors.calendarDay}:not(.${config.classes.empty})`);
            
            allDays.forEach(dayElement => {
                // Supprimer les classes d'aperçu existantes
                dayElement.classList.remove(config.classes.previewInRange, config.classes.previewEndDate);
                
                // Ignorer les jours vides
                if (dayElement.classList.contains(config.classes.empty)) return;
                
                // Analyser le jour
                const dayNumber = parseInt(dayElement.textContent);
                if (isNaN(dayNumber)) return;
                
                // Déterminer le mois et l'année du jour
                const monthContainer = dayElement.closest(config.selectors.monthContainers);
                const monthName = monthContainer.querySelector(config.selectors.monthName).textContent;
                const [monthStr, yearStr] = monthName.split(" ");
                
                const monthIndex = config.constants.MONTH_NAMES.indexOf(monthStr);
                const year = parseInt(yearStr);
                
                if (monthIndex === -1 || isNaN(year)) return;
                
                // Créer l'objet date pour ce jour
                const currentDate = new Date(year, monthIndex, dayNumber);
                
                // Appliquer les classes d'aperçu si nécessaire
                if (currentDate > startDate && currentDate < endDate) {
                    dayElement.classList.add(config.classes.previewInRange);
                }
                
                if (utils.date.isSameDate(currentDate, endDate)) {
                    dayElement.classList.add(config.classes.previewEndDate);
                }
            });
        },
        
        // Supprimer les surlignages
        clearHighlights() {
            document.querySelectorAll(`.${config.classes.previewInRange}, .${config.classes.previewEndDate}`)
                .forEach(el => el.classList.remove(config.classes.previewInRange, config.classes.previewEndDate));
        },
        
        // Mise à jour du message de sélection
        updateSelectionMessage(message) {
            let messageEl = document.querySelector(config.selectors.calendarSelectionMessage);
            
            // Créer l'élément de message s'il n'existe pas
            if (!messageEl && message) {
                messageEl = utils.dom.createElement("div", ["calendar-selection-message"]);
                const calendarHeader = document.querySelector(config.selectors.calendarFooter);
                calendarHeader.insertAdjacentElement("afterend", messageEl);
            }
            
            // Mettre à jour ou masquer le message
            if (messageEl) {
                messageEl.textContent = message;
                messageEl.style.display = message ? "block" : "none";
            }
        },
        
        // Aperçu de la date de fin
        applyEndDatePreview() {
            // Cette fonction était vide dans l'original, maintenue pour compatibilité
            if (this.state.editMode !== "end" || !this.state.selectedStartDate) return;
        },
        
        // Validation du formulaire
        validateForm() {
            // Validation du formulaire en utilisant le gestionnaire exposé globalement
            if (window.reservationManager && typeof window.reservationManager.validateEtape1Form === 'function') {
                window.reservationManager.validateEtape1Form();
            } else if (typeof validateEtape1Form === 'function') {
                validateEtape1Form();
            }
        }
    };
    
    // Initialisation du gestionnaire de calendrier
    calendarManager.init();
});
