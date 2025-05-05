document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        selectors: {
            // Champs de recherche
            destinationInput: '#destination-search',
            suggestionsContainer: '#destination-suggestions',
            dateDebutInput: '#date-debut-visible',
            dateFinInput: '#date-fin-visible',
            calendarDropdown: '#calendar-dropdown',
            applyDatesBtn: '.apply-dates',
            
            // Tri et cartes
            sortSelect: '#sort',
            cardsContainer: '#toutes-destinations .all-destination-cards',
            
            // Onglets
            tabs: '.search-tab',
            tabsContainer: '.search-tabs',
            tabIndicator: '.tab-indicator',
            activeTab: '.search-tab.active',
            
            // Champs de recherche et focus
            searchInputs: '.search-field input',
            fieldsContainer: '.search-fields-container',
            focusIndicator: '.field-focus-indicator',
            
            // Sélecteurs d'éléments
            destinationItems: '.suggestion-item:not(.category-suggestion)',
            categoryItems: '.suggestion-item.category-suggestion',
            calendarDays: '.calendar-day:not(.empty):not(.past-day)'
        },
        classes: {
            active: 'active',
            startDate: 'start-date',
            endDate: 'end-date'
        },
        timing: {
            scrollDelay: 100,
            calendarClose: 300
        },
        limits: {
            maxDestinationSuggestions: 5,
            maxCategorySuggestions: 3 
        }
    };

    // Utilitaires
    const utils = {
        scrollToElement: (element, offset = 200) => {
            if (element) {
                const rect = element.getBoundingClientRect();
                const targetPosition = document.documentElement.scrollTop + rect.top - offset;
                window.scrollTo({ top: targetPosition, behavior: "smooth" });
            }
        },
        
        card: {
            getPrice: (card) => {
                const priceElement = card.querySelector(".price-value");
                if (!priceElement) return 0;
                return parseFloat(priceElement.textContent.trim().replace(/[^\d,]/g, "").replace(",", "."));
            },
            
            getTitle: (card) => {
                const titleElement = card.querySelector(".card-title");
                return titleElement ? titleElement.textContent.trim().toLowerCase() : "";
            },
            
            getRating: (card) => {
                const ratingElement = card.querySelector(".card-rating span, .rating-count span");
                if (!ratingElement) return 0;
                return parseFloat(ratingElement.textContent.trim());
            }
        },
        
        getElement: (selector) => {
            return document.querySelector(selector);
        },
        
        getAllElements: (selector) => {
            return document.querySelectorAll(selector);
        }
    };

    // Gestionnaire des destinations
    const destinationManager = {
        // Variables d'état
        state: {
            startDateSelected: false,
            endDateSelected: false,
            lastActiveDateField: null
        },
        
        // Initialisation
        init() {
            this.setupCardSorting();
            this.setupDestinationSuggestions();
            this.setupCalendar();
            this.setupTabIndicator();
            this.setupFocusIndicator();
        },
        
        // Système de tri des cartes
        setupCardSorting() {
            const sortSelect = utils.getElement(config.selectors.sortSelect);
            const container = utils.getElement(config.selectors.cardsContainer);
            if (!sortSelect || !container) return;
            
            const sortStrategies = {
                'price-asc': (a, b) => utils.card.getPrice(a) - utils.card.getPrice(b),
                'price-desc': (a, b) => utils.card.getPrice(b) - utils.card.getPrice(a),
                'name-asc': (a, b) => utils.card.getTitle(a).localeCompare(utils.card.getTitle(b)),
                'popular': (a, b) => utils.card.getRating(b) - utils.card.getRating(a)
            };
            
            const sortCards = (sortValue) => {
                const cards = Array.from(container.querySelectorAll(".destination-card"));
                if (cards.length === 0) return;
                
                cards.sort(sortStrategies[sortValue] || (() => 0));
                cards.forEach(card => container.appendChild(card));
            };
            
            // Initialiser le tri par popularité
            sortCards("popular");
            sortSelect.value = "popular";
            
            // Configurer l'événement de tri
            sortSelect.addEventListener("change", e => sortCards(e.target.value));
        },
        
        // Système de suggestions de destination
        setupDestinationSuggestions() {
            const destinationInput = utils.getElement(config.selectors.destinationInput);
            const suggestionsContainer = utils.getElement(config.selectors.suggestionsContainer);
            
            if (!destinationInput || !suggestionsContainer) return;
            
            // Limite l'affichage des suggestions
            const limitSuggestions = (isSearching = false) => {
                const destinationItems = utils.getAllElements(config.selectors.destinationItems);
                const categoryItems = utils.getAllElements(config.selectors.categoryItems);
                
                if (!isSearching) {
                    destinationItems.forEach((item, index) => {
                        item.style.display = index < config.limits.maxDestinationSuggestions ? 'flex' : 'none';
                    });
                } else {
                    destinationItems.forEach(item => {
                        item.style.display = 'flex';
                    });
                }
                
                categoryItems.forEach((item, index) => {
                    item.style.display = index < config.limits.maxCategorySuggestions ? 'flex' : 'none';
                });
            };
            
            // Affiche les suggestions
            const showSuggestions = () => {
                suggestionsContainer.style.display = "block";
                limitSuggestions(false);
                setTimeout(() => utils.scrollToElement(suggestionsContainer), config.timing.scrollDelay);
            };
            
            // Sélectionne une destination et ouvre le calendrier
            const selectDestinationAndOpenCalendar = (destinationName) => {
                const dateDebutInput = utils.getElement(config.selectors.dateDebutInput);
                const calendarDropdown = utils.getElement(config.selectors.calendarDropdown);
                
                destinationInput.value = destinationName;
                suggestionsContainer.style.display = "none";
                
                if (dateDebutInput) {
                    setTimeout(() => {
                        dateDebutInput.focus();
                        
                        if (calendarDropdown) {
                            calendarDropdown.classList.add(config.classes.active);
                            setTimeout(() => utils.scrollToElement(calendarDropdown), config.timing.scrollDelay);
                            this.state.startDateSelected = false;
                            this.state.endDateSelected = false;
                        }
                    }, config.timing.scrollDelay);
                }
            };
            
            // Gestion de la touche Entrée
            const handleEnterKey = function(e) {
                if (e.key !== "Enter") return;
                e.preventDefault();
                
                const searchTerm = this.value.toLowerCase();
                if (searchTerm.trim() === "") return;
                
                let matchingSuggestion = null;
                utils.getAllElements(".suggestion-item").forEach(item => {
                    const itemName = item.querySelector("h4").textContent.toLowerCase();
                    if (itemName.includes(searchTerm) && item.style.display !== "none" && !matchingSuggestion) {
                        matchingSuggestion = item;
                    }
                });
                
                if (matchingSuggestion) {
                    const destinationName = matchingSuggestion.querySelector("h4").textContent.split(",")[0];
                    selectDestinationAndOpenCalendar(destinationName);
                } else {
                    selectDestinationAndOpenCalendar(this.value);
                }
            };
            
            // Filtrage des suggestions
            const filterSuggestions = function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                if (searchTerm === "") {
                    utils.getAllElements(".suggestion-item").forEach(item => {
                        item.style.display = "flex";
                    });
                    suggestionsContainer.style.display = "block";
                    limitSuggestions(false);
                    return;
                }
                
                let hasMatches = false;
                const destinationItems = utils.getAllElements(config.selectors.destinationItems);
                const categoryItems = utils.getAllElements(config.selectors.categoryItems);
                
                // Filtrer les destinations
                destinationItems.forEach(item => {
                    const itemName = item.querySelector("h4").textContent.toLowerCase();
                    const itemDesc = item.querySelector("p").textContent.toLowerCase();
                    
                    const isMatch = itemName.includes(searchTerm) || itemDesc.includes(searchTerm);
                    item.style.display = isMatch ? "flex" : "none";
                    hasMatches = hasMatches || isMatch;
                });
                
                // Filtrer les catégories
                categoryItems.forEach(item => {
                    const itemName = item.querySelector("h4").textContent.toLowerCase();
                    const isMatch = itemName.includes(searchTerm);
                    item.style.display = isMatch ? "flex" : "none";
                    hasMatches = hasMatches || isMatch;
                });
                
                suggestionsContainer.style.display = hasMatches ? "block" : "none";
            };
            
            // Configuration des écouteurs d'événements
            destinationInput.addEventListener("click", e => {
                showSuggestions();
                e.stopPropagation();
            });
            
            destinationInput.addEventListener("focus", showSuggestions);
            destinationInput.addEventListener("input", filterSuggestions);
            destinationInput.addEventListener("keypress", handleEnterKey);
            
            // Gestion de la sélection des suggestions
            utils.getAllElements(".suggestion-item").forEach(item => {
                item.addEventListener("click", function() {
                    const destinationName = this.querySelector("h4").textContent.split(",")[0];
                    selectDestinationAndOpenCalendar(destinationName);
                });
            });
            
            // Fermeture des suggestions en cliquant ailleurs
            document.addEventListener("click", e => {
                if (!destinationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.style.display = "none";
                }
            });
        },
        
        // Système de calendrier
        setupCalendar() {
            const dateDebutInput = utils.getElement(config.selectors.dateDebutInput);
            const dateFinInput = utils.getElement(config.selectors.dateFinInput);
            const calendarDropdown = utils.getElement(config.selectors.calendarDropdown);
            const applyDatesBtn = utils.getElement(config.selectors.applyDatesBtn);
            
            if (!dateDebutInput || !calendarDropdown) return;
            
            // Ouvre le calendrier
            const openCalendar = () => {
                calendarDropdown.classList.add(config.classes.active);
                setTimeout(() => utils.scrollToElement(calendarDropdown), config.timing.scrollDelay);
            };
            
            // Configuration des champs de date
            dateDebutInput.addEventListener("click", openCalendar);
            
            if (dateFinInput) {
                dateFinInput.addEventListener("click", openCalendar);
            }
            
            // Gestion de la sélection des dates
            const calendarDays = calendarDropdown.querySelectorAll(config.selectors.calendarDays);
            
            calendarDays.forEach(day => {
                day.addEventListener("click", () => {
                    if (!this.state.startDateSelected) {
                        this.state.startDateSelected = true;
                        day.classList.add(config.classes.startDate);
                    } else if (!this.state.endDateSelected) {
                        this.state.endDateSelected = true;
                        day.classList.add(config.classes.endDate);
                        
                        setTimeout(() => {
                            calendarDropdown.classList.remove(config.classes.active);
                            this.state.startDateSelected = false;
                            this.state.endDateSelected = false;
                        }, config.timing.calendarClose);
                    }
                });
            });
            
            // Empêcher la fermeture du calendrier lors des clics à l'intérieur
            calendarDropdown.addEventListener("click", e => e.stopPropagation());
            
            // Bouton d'application des dates
            if (applyDatesBtn) {
                applyDatesBtn.addEventListener("click", () => {
                    calendarDropdown.classList.remove(config.classes.active);
                    this.state.startDateSelected = false;
                    this.state.endDateSelected = false;
                });
            }
            
            // Observer les changements de classe du calendrier
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName === "class") {
                        const isActive = calendarDropdown.classList.contains(config.classes.active);
                        const searchInputs = utils.getAllElements(config.selectors.searchInputs);
                        const focusIndicator = utils.getElement(config.selectors.focusIndicator);
                        
                        if (!isActive && this.state.lastActiveDateField && 
                            !Array.from(searchInputs).includes(document.activeElement)) {
                            focusIndicator.style.opacity = "0";
                            this.state.lastActiveDateField = null;
                        }
                    }
                });
            });
            
            observer.observe(calendarDropdown, { attributes: true });
        },
        
        // Effet de déplacement sur les onglets
        setupTabIndicator() {
            const tabs = utils.getAllElements(config.selectors.tabs);
            const indicator = utils.getElement(config.selectors.tabIndicator);
            const tabsContainer = utils.getElement(config.selectors.tabsContainer);
            const activeTab = utils.getElement(config.selectors.activeTab);
            
            if (!indicator || !tabsContainer) return;
            
            const positionIndicator = (tab) => {
                if (!tab) return;
                
                const rect = tab.getBoundingClientRect();
                const tabsRect = tabsContainer.getBoundingClientRect();
                
                indicator.style.width = rect.width + "px";
                indicator.style.left = rect.left - tabsRect.left + "px";
            };
            
            // Positionner l'indicateur initial
            if (activeTab) {
                positionIndicator(activeTab);
                indicator.style.opacity = "1";
            }
            
            // Gestion du survol
            tabs.forEach(tab => {
                tab.addEventListener("mouseenter", function() {
                    positionIndicator(this);
                    indicator.style.opacity = "1";
                });
            });
            
            // Retour à l'onglet actif quand on quitte la zone
            tabsContainer.addEventListener("mouseleave", () => {
                if (activeTab) {
                    positionIndicator(activeTab);
                } else {
                    indicator.style.opacity = "0";
                }
            });
        },
        
        // Effet de déplacement sur les champs de recherche
        setupFocusIndicator() {
            const searchInputs = utils.getAllElements(config.selectors.searchInputs);
            const fieldsContainer = utils.getElement(config.selectors.fieldsContainer);
            const focusIndicator = utils.getElement(config.selectors.focusIndicator);
            
            if (!searchInputs.length || !focusIndicator || !fieldsContainer) return;
            
            const positionFocusIndicator = (field) => {
                if (!field) return;
                
                const fieldRect = field.closest(".search-field").getBoundingClientRect();
                const containerRect = fieldsContainer.getBoundingClientRect();
                
                focusIndicator.style.width = fieldRect.width + "px";
                focusIndicator.style.height = fieldRect.height + "px";
                focusIndicator.style.left = fieldRect.left - containerRect.left + "px";
                focusIndicator.style.top = fieldRect.top - containerRect.top + "px";
                focusIndicator.style.opacity = "1";
            };
            
            // Gestion du focus sur les champs
            searchInputs.forEach(input => {
                input.addEventListener("focus", function() {
                    positionFocusIndicator(this);
                    
                    if (this.id === "date-debut-visible" || this.id === "date-fin-visible") {
                        destinationManager.state.lastActiveDateField = this;
                    }
                });
            });
            
            // Gestion de la perte de focus
            document.addEventListener("focusout", () => {
                setTimeout(() => {
                    const calendarDropdown = utils.getElement(config.selectors.calendarDropdown);
                    const calendarIsOpen = calendarDropdown && 
                                         calendarDropdown.classList.contains(config.classes.active);
                    
                    if (calendarIsOpen && this.state.lastActiveDateField) {
                        positionFocusIndicator(this.state.lastActiveDateField);
                    } else if (!Array.from(searchInputs).includes(document.activeElement)) {
                        focusIndicator.style.opacity = "0";
                        this.state.lastActiveDateField = null;
                    }
                }, 10);
            });
        }
    };
    
    // Initialisation du gestionnaire de destinations
    destinationManager.init();
});
