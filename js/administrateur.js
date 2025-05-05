document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        selectors: {
            tableBody: '.tab-voyageurs tbody',
            rows: 'tr',
            voyageCount: '#voyage-count',
            toggleStatus: '.toggle-status',
            toggleVip: '.toggle-vip',
            searchInput: '#search',
            searchNoResults: '#search-no-results',
            searchTerm: '#search-term',
            sortSelect: '#sort-select',
            searchButton: '#search-button',
            resetSearch: '#reset-search'
        },
        classes: {
            notification: 'notification',
            updating: 'updating',
            statusOk: 'status-ok',
            statusPending: 'status-pending',
            vipBadge: 'vip-badge',
            novipBadge: 'novip-badge'
        },
        timing: {
            toggleDelay: 1000,
            notificationFadeIn: 10,
            notificationDuration: 3000,
            notificationFadeOut: 500
        },
        status: {
            blocked: {
                value: 'blocked',
                class: 'status-pending',
                html: 'Bloqué<img src="../img/svg/block.svg" alt="block"><span class="tooltip">Cliquez pour débloquer</span>',
                message: 'Utilisateur bloqué avec succès'
            },
            active: {
                value: 'active',
                class: 'status-ok',
                html: 'Actif<img src="../img/svg/check.svg" alt="check"><span class="tooltip">Cliquez pour bloquer</span>',
                message: 'Utilisateur débloqué avec succès'
            }
        },
        vip: {
            no: {
                value: '0',
                class: 'novip-badge',
                html: 'Non<img src="../img/svg/no.svg" alt="croix"><span class="tooltip">Cliquez pour ajouter le VIP</span>',
                message: 'Statut VIP retiré avec succès'
            },
            yes: {
                value: '1',
                class: 'vip-badge',
                html: 'VIP<img src="../img/svg/etoile.svg" alt="etoile"><span class="tooltip">Cliquez pour retirer le VIP</span>',
                message: 'Statut VIP ajouté avec succès'
            }
        }
    };

    // Utilitaires
    const utils = {
        // Sélecteurs d'éléments
        dom: {
            getElement: (selector) => {
                return document.querySelector(selector);
            },
            
            getAllElements: (selector, parent = document) => {
                return parent.querySelectorAll(selector);
            }
        },
        
        // Notifications
        notification: {
            show: (message, type = 'success') => {
                const existingNotification = utils.dom.getElement('.' + config.classes.notification);
                if (existingNotification) existingNotification.remove();
                
                const notification = document.createElement('div');
                notification.className = `${config.classes.notification} notification-${type}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => notification.style.opacity = '1', config.timing.notificationFadeIn);
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), config.timing.notificationFadeOut);
                }, config.timing.notificationDuration);
            }
        },
        
        // Stratégies de tri
        sort: {
            strategies: {
                'recent': (a, b) => {
                    return new Date(b.querySelector('.date').getAttribute('data-date')) - 
                           new Date(a.querySelector('.date').getAttribute('data-date'));
                },
                'oldest': (a, b) => {
                    return new Date(a.querySelector('.date').getAttribute('data-date')) - 
                           new Date(b.querySelector('.date').getAttribute('data-date'));
                },
                'name-asc': (a, b) => {
                    return a.querySelector('.nom').textContent.localeCompare(b.querySelector('.nom').textContent);
                },
                'name-desc': (a, b) => {
                    return b.querySelector('.nom').textContent.localeCompare(a.querySelector('.nom').textContent);
                }
            }
        }
    };

    // Gestionnaire de l'administration
    const adminManager = {
        // Éléments DOM fréquemment utilisés
        elements: {
            tableBody: null,
            voyageCount: null,
            searchInput: null,
            searchNoResults: null,
            searchTerm: null,
            sortSelect: null
        },
        
        // Initialisation
        init() {
            this.cacheElements();
            this.setupToggleStatus();
            this.setupToggleVip();
            this.setupSearch();
            this.setupSort();
            this.updateUserCount();
            this.sortTable('recent');
        },
        
        // Mise en cache des éléments DOM
        cacheElements() {
            this.elements = {
                tableBody: utils.dom.getElement(config.selectors.tableBody),
                voyageCount: utils.dom.getElement(config.selectors.voyageCount),
                searchInput: utils.dom.getElement(config.selectors.searchInput),
                searchNoResults: utils.dom.getElement(config.selectors.searchNoResults),
                searchTerm: utils.dom.getElement(config.selectors.searchTerm),
                sortSelect: utils.dom.getElement(config.selectors.sortSelect)
            };
        },
        
        // Fonction générique pour configurer les éléments basculables
        setupToggle(selector, options) {
            utils.dom.getAllElements(selector).forEach(element => {
                element.addEventListener('click', function () {
                    const currentState = this.getAttribute(options.dataAttr) === options.states[0].value;
                    this.classList.add(config.classes.updating);
                    
                    setTimeout(() => {
                        const newState = currentState ? options.states[1] : options.states[0];
                        
                        // Mettre à jour les classes
                        this.classList.remove(currentState ? options.states[0].class : options.states[1].class);
                        this.classList.add(currentState ? options.states[1].class : options.states[0].class);
                        
                        // Mettre à jour l'attribut de données
                        this.setAttribute(options.dataAttr, currentState ? newState.value : options.states[0].value);
                        
                        // Mettre à jour le contenu HTML
                        this.innerHTML = (currentState ? newState.html : options.states[0].html);
                        
                        this.classList.remove(config.classes.updating);
                        
                        // Afficher notification
                        utils.notification.show(currentState ? newState.message : options.states[0].message, 'success');
                    }, config.timing.toggleDelay);
                });
            });
        },
        
        // Configuration du basculement de statut
        setupToggleStatus() {
            this.setupToggle(config.selectors.toggleStatus, {
                dataAttr: 'data-status',
                states: [
                    config.status.blocked,
                    config.status.active
                ]
            });
        },
        
        // Configuration du basculement VIP
        setupToggleVip() {
            this.setupToggle(config.selectors.toggleVip, {
                dataAttr: 'data-vip',
                states: [
                    config.vip.no,
                    config.vip.yes
                ]
            });
        },
        
        // Configuration de la recherche
        setupSearch() {
            const { searchInput } = this.elements;
            
            // Écouter les événements de recherche
            searchInput.addEventListener('input', () => this.performSearch());
            
            const searchButton = utils.dom.getElement(config.selectors.searchButton) || 
                                searchInput.nextElementSibling;
            if (searchButton) {
                searchButton.addEventListener('click', () => this.performSearch());
            }
            
            searchInput.addEventListener('keypress', e => { 
                if (e.key === 'Enter') this.performSearch();
            });
            
            utils.dom.getElement(config.selectors.resetSearch).addEventListener('click', () => {
                searchInput.value = '';
                this.performSearch();
                searchInput.focus();
            });
        },
        
        // Exécution de la recherche
        performSearch() {
            const { searchInput, searchNoResults, searchTerm, tableBody } = this.elements;
            const query = searchInput.value.toLowerCase().trim();
            const rows = utils.dom.getAllElements(config.selectors.rows, tableBody);
            let hasResults = false;
            
            rows.forEach(row => {
                const name = row.querySelector('.nom').textContent.toLowerCase();
                const email = row.getAttribute('data-email').toLowerCase();
                const matchFound = query.includes('@') ? email.includes(query) : name.includes(query);
                
                row.style.display = (matchFound || query === '') ? '' : 'none';
                hasResults = hasResults || (matchFound || query === '');
            });
            
            searchNoResults.style.display = (!hasResults && query !== '') ? 'flex' : 'none';
            if (!hasResults && query !== '') {
                searchTerm.textContent = query;
            }
            
            this.updateUserCount();
        },
        
        // Configuration du tri
        setupSort() {
            this.elements.sortSelect.addEventListener('change', e => this.sortTable(e.target.value));
        },
        
        // Tri du tableau
        sortTable(sortBy) {
            const { tableBody } = this.elements;
            
            if (!utils.sort.strategies[sortBy]) return;
            
            const rowsArray = Array.from(utils.dom.getAllElements(config.selectors.rows, tableBody));
            rowsArray.sort(utils.sort.strategies[sortBy]);
            
            // Vider et remplir le tableau avec les lignes triées
            tableBody.innerHTML = '';
            rowsArray.forEach(row => tableBody.appendChild(row));
            
            this.updateUserCount();
        },
        
        // Mise à jour du compteur d'utilisateurs
        updateUserCount() {
            const { tableBody, voyageCount } = this.elements;
            const visibleUsers = utils.dom.getAllElements('tr:not([style*="display: none"])', tableBody).length;
            voyageCount.textContent = visibleUsers + ' voyageurs';
        }
    };
    
    // Initialisation du gestionnaire d'administration
    adminManager.init();
}); 