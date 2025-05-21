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
            notificationFadeOut: 500,
            minLoadingDuration: 800
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
        },
        api: {
            updateUserStatus: '../php/update_user_status.php'
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
        },

        // API
        api: {
            updateUserStatus: async (email, updateData) => {
                try {                    
                    const response = await fetch(config.api.updateUserStatus, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ email, ...updateData })
                    });
                                        
                    if (!response.ok) {
                        console.error(`Server error: ${response.status} ${response.statusText}`);
                        return { 
                            success: false, 
                            message: `Erreur serveur: ${response.status} ${response.statusText}` 
                        };
                    }
                    
                    const text = await response.text();
                    
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        return { 
                            success: false, 
                            message: 'Réponse du serveur invalide' 
                        };
                    }
                    
                    return data;
                } catch (error) {
                    return { success: false, message: 'Erreur de connexion' };
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
        
        // Configuration du basculement de statut
        setupToggleStatus() {
            const self = this; // Référence à adminManager
            
            utils.dom.getAllElements(config.selectors.toggleStatus).forEach(element => {
                element.addEventListener('click', async function () {
                    const currentState = this.getAttribute('data-status') === config.status.blocked.value;
                    this.classList.add(config.classes.updating);
                    
                    // Obtenir l'email de l'utilisateur depuis la ligne parente
                    const userRow = this.closest('tr');
                    const userEmail = userRow.getAttribute('data-email');
                    const newStatus = currentState ? config.status.active.value : config.status.blocked.value;
                    
                    // Temps de début pour maintenir un temps minimal de chargement
                    const startTime = Date.now();
                    
                    // Appel à l'API
                    const response = await utils.api.updateUserStatus(userEmail, { status: newStatus });
                    
                    // Assurer un délai minimum pour l'affichage du chargement
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = Math.max(0, config.timing.minLoadingDuration - elapsedTime);
                    
                    setTimeout(() => {
                        if (response.success) {
                            // Mettre à jour visuellement l'élément
                            const newState = currentState ? config.status.active : config.status.blocked;
                            
                            // Mettre à jour les classes
                            this.classList.remove(currentState ? config.status.blocked.class : config.status.active.class);
                            this.classList.add(currentState ? config.status.active.class : config.status.blocked.class);
                            
                            // Mettre à jour l'attribut de données
                            this.setAttribute('data-status', newStatus);
                            
                            // Mettre à jour le contenu HTML
                            this.innerHTML = (currentState ? newState.html : config.status.blocked.html);
                            
                            // Si on bloque un utilisateur, mettre à jour le bouton VIP
                            if (newStatus === config.status.blocked.value) {
                                const vipToggle = userRow.querySelector(config.selectors.toggleVip);
                                if (vipToggle && vipToggle.getAttribute('data-vip') === config.vip.yes.value) {
                                    // Mettre à jour le toggle VIP en non-VIP
                                    vipToggle.classList.remove(config.vip.yes.class);
                                    vipToggle.classList.add(config.vip.no.class);
                                    vipToggle.setAttribute('data-vip', config.vip.no.value);
                                    vipToggle.innerHTML = config.vip.no.html;
                                }
                                
                                // Désactiver le bouton VIP
                                self.updateVipToggleState(userRow, true);
                            } else {
                                // Réactiver le bouton VIP
                                self.updateVipToggleState(userRow, false);
                            }
                            
                            // Afficher notification
                            utils.notification.show(response.message, 'success');
                        } else {
                            utils.notification.show(response.message || 'Une erreur est survenue', 'error');
                        }
                        
                        this.classList.remove(config.classes.updating);
                    }, remainingTime);
                });
            });
        },
        
        // Mettre à jour l'état du toggle VIP en fonction du statut bloqué/actif
        updateVipToggleState(userRow, isBlocked) {
            const vipToggle = userRow.querySelector(config.selectors.toggleVip);
            if (vipToggle) {
                if (isBlocked) {
                    vipToggle.classList.add('disabled');
                    vipToggle.title = "Impossible d'attribuer le statut VIP à un utilisateur bloqué";
                } else {
                    vipToggle.classList.remove('disabled');
                    vipToggle.title = "";
                }
            }
        },
        
        // Configuration du basculement VIP
        setupToggleVip() {
            const self = this; // Référence à adminManager
            
            utils.dom.getAllElements(config.selectors.toggleVip).forEach(element => {
                // Initialiser l'état du toggle VIP en fonction du statut initial
                const userRow = element.closest('tr');
                const statusToggle = userRow.querySelector(config.selectors.toggleStatus);
                const isBlocked = statusToggle && statusToggle.getAttribute('data-status') === config.status.blocked.value;
                self.updateVipToggleState(userRow, isBlocked);
                
                element.addEventListener('click', async function (e) {
                    // Vérifier si l'utilisateur est bloqué
                    const userRow = this.closest('tr');
                    const statusToggle = userRow.querySelector(config.selectors.toggleStatus);
                    const isBlocked = statusToggle && statusToggle.getAttribute('data-status') === config.status.blocked.value;
                    
                    if (isBlocked) {
                        utils.notification.show("Impossible d'attribuer le statut VIP à un utilisateur bloqué", 'error');
                        return;
                    }
                    
                    const currentVipState = this.getAttribute('data-vip') === config.vip.yes.value;
                    this.classList.add(config.classes.updating);
                    
                    // Obtenir l'email de l'utilisateur depuis la ligne parente
                    const userEmail = userRow.getAttribute('data-email');
                    const newVipStatus = currentVipState ? false : true;
                    
                    // Temps de début pour maintenir un temps minimal de chargement
                    const startTime = Date.now();
                    
                    // Appel à l'API
                    const response = await utils.api.updateUserStatus(userEmail, { vip: newVipStatus });
                    
                    // Assurer un délai minimum pour l'affichage du chargement
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = Math.max(0, config.timing.minLoadingDuration - elapsedTime);
                    
                    setTimeout(() => {
                        if (response.success) {
                            // Mettre à jour visuellement l'élément
                            const newState = currentVipState ? config.vip.no : config.vip.yes;
                            
                            // Mettre à jour les classes
                            this.classList.remove(currentVipState ? config.vip.yes.class : config.vip.no.class);
                            this.classList.add(currentVipState ? config.vip.no.class : config.vip.yes.class);
                            
                            // Mettre à jour l'attribut de données
                            this.setAttribute('data-vip', currentVipState ? config.vip.no.value : config.vip.yes.value);
                            
                            // Mettre à jour le contenu HTML
                            this.innerHTML = (currentVipState ? config.vip.no.html : config.vip.yes.html);
                            
                            // Afficher notification
                            utils.notification.show(response.message, 'success');
                        } else {
                            utils.notification.show(response.message || 'Une erreur est survenue', 'error');
                        }
                        
                        this.classList.remove(config.classes.updating);
                    }, remainingTime);
                });
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