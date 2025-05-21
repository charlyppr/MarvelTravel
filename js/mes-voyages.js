// Mettre à jour le compteur de voyages
// document.getElementById('voyage-count').textContent = "<?php echo $total_voyages; ?> au total";

document.addEventListener('DOMContentLoaded', function() {
    // Configuration centralisée
    const config = {
        selectors: {
            sortSelect: '#sort-select',
            voyagesContainer: '.voyages-list',
            tableContainer: '.table-container',
            searchInput: '#search',
            resetSearch: '#reset-search',
            searchNoResults: '#search-no-results',
            searchTerm: '#search-term',
            voyageCount: '#voyage-count'
        }
    };
    
    // Utilitaires
    const utils = {
        // Extraction du prix depuis un texte (ex: "159,99 €" -> 159.99)
        extractPrice: (priceText) => {
            return parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
        },
        
        // Conversion d'une date au format DD/MM/YYYY en objet Date
        parseDate: (dateStr) => {
            const parts = dateStr.split('/');
            return parts.length === 3 ? new Date(parts[2], parts[1] - 1, parts[0]) : null;
        },
        
        // Vérifie si une chaîne contient des caractères de date (chiffres, /, -)
        isDateQuery: (query) => /[\d\/\-]/.test(query)
    };
    
    // Gestionnaire principal
    const voyagesManager = {
        // Éléments DOM
        elements: {},
        
        // État
        state: {
            isAdminPage: false,
            isCardView: false,
            isTableView: false
        },
        
        // Initialisation
        init() {
            this.cacheElements();
            this.detectPageType();
            this.updateCounter();
            this.setupEventListeners();
            
            // Tri initial par défaut
            if (this.elements.sortSelect) {
                this.sortVoyages(this.elements.sortSelect.value);
            }
        },
        
        // Mise en cache des éléments DOM
        cacheElements() {
            this.elements = Object.entries(config.selectors).reduce((acc, [key, selector]) => {
                acc[key] = document.querySelector(selector);
                return acc;
            }, {});
            
            // Éléments spéciaux
            if (this.elements.searchInput) {
                this.elements.searchButton = this.elements.searchInput.nextElementSibling;
            }
        },
        
        // Détecte le type de page (admin ou utilisateur) et le type d'affichage (cartes ou tableau)
        detectPageType() {
            this.state.isAdminPage = document.querySelector('.tab-voyageurs') !== null;
            this.state.isCardView = this.elements.voyagesContainer && this.elements.voyagesContainer.children.length > 0;
            this.state.isTableView = this.elements.tableContainer && 
                                     this.elements.tableContainer.querySelector('tbody') && 
                                     this.elements.tableContainer.querySelector('tbody').children.length > 0;
        },
        
        // Configuration des écouteurs d'événements
        setupEventListeners() {
            // Tri
            if (this.elements.sortSelect) {
                this.elements.sortSelect.addEventListener('change', e => this.sortVoyages(e.target.value));
            }
            
            // Recherche
            if (this.elements.searchInput) {
                this.elements.searchInput.addEventListener('input', () => this.performSearch());
                this.elements.searchInput.addEventListener('keypress', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.performSearch();
                    }
                });
                
                if (this.elements.searchButton) {
                    this.elements.searchButton.addEventListener('click', () => this.performSearch());
                }
                
                if (this.elements.resetSearch) {
                    this.elements.resetSearch.addEventListener('click', () => {
                        this.elements.searchInput.value = '';
                        this.performSearch();
                    });
                }
            }
        },
        
        // Obtient les éléments à trier/filtrer (cartes ou lignes de tableau)
        getElements() {
            if (this.state.isCardView) {
                return Array.from(this.elements.voyagesContainer.querySelectorAll('.voyage-card'));
            } else if (this.state.isTableView) {
                return Array.from(this.elements.tableContainer.querySelector('tbody').querySelectorAll('tr'));
            }
            return [];
        },
        
        // Mise à jour du compteur de voyages
        updateCounter(matchCount = null) {
            if (!this.elements.voyageCount) return;
            
            const elements = this.getElements();
            
            if (matchCount === null) {
                // Affichage du total
                if (this.state.isAdminPage) {
                    this.elements.voyageCount.textContent = `${elements.length} voyageurs`;
                } else {
                    this.elements.voyageCount.textContent = `${elements.length} au total`;
                }
            } else {
                // Affichage des résultats de recherche
                if (this.state.isAdminPage) {
                    this.elements.voyageCount.textContent = `${matchCount} voyageur${matchCount > 1 ? 's' : ''} trouvé${matchCount > 1 ? 's' : ''}`;
                } else {
                    this.elements.voyageCount.textContent = `${matchCount} résultat${matchCount > 1 ? 's' : ''}`;
                }
            }
        },
        
        // Trie les voyages selon le critère spécifié
        sortVoyages(sortType) {
            if (!this.state.isCardView && !this.state.isTableView) return;
            
            const sortStrategies = {
                'recent': (a, b) => {
                    const dateA = a.dataset.dateAchat || '';
                    const dateB = b.dataset.dateAchat || '';
                    return dateB.localeCompare(dateA);
                },
                'price-asc': (a, b) => {
                    const priceA = utils.extractPrice(a.querySelector('.price').textContent);
                    const priceB = utils.extractPrice(b.querySelector('.price').textContent);
                    return priceA - priceB;
                },
                'price-desc': (a, b) => {
                    const priceA = utils.extractPrice(a.querySelector('.price').textContent);
                    const priceB = utils.extractPrice(b.querySelector('.price').textContent);
                    return priceB - priceA;
                },
                'date-asc': (a, b) => this.compareDates(a, b, true),
                'date-desc': (a, b) => this.compareDates(a, b, false)
            };
            
            const elements = this.getElements();
            
            // Trier les éléments si une stratégie valide est fournie
            if (sortStrategies[sortType]) {
                elements.sort(sortStrategies[sortType]);
                
                // Réorganiser les éléments selon le tri
                if (this.state.isCardView) {
                    elements.forEach(el => this.elements.voyagesContainer.appendChild(el));
                } else if (this.state.isTableView) {
                    const tbody = this.elements.tableContainer.querySelector('tbody');
                    elements.forEach(el => tbody.appendChild(el));
                }
            }
        },
        
        // Compare les dates de deux éléments pour le tri
        compareDates(a, b, ascending) {
            const dateStrA = a.querySelector('.dates').textContent.trim().split(' au ')[0];
            const dateStrB = b.querySelector('.dates').textContent.trim().split(' au ')[0];
            
            const dateA = utils.parseDate(dateStrA);
            const dateB = utils.parseDate(dateStrB);
            
            if (dateA && dateB) {
                return ascending ? dateA - dateB : dateB - dateA;
            }
            return 0;
        },
        
        // Effectue une recherche dans les voyages
        performSearch() {
            if (!this.elements.searchInput) return;
            
            const searchTerm = this.elements.searchInput.value.trim().toLowerCase();
            const elements = this.getElements();
            let matchCount = 0;
            
            // Détection si la recherche est un prix
            const isPriceQuery = /^\d+[\d,. ]*€?$/.test(searchTerm);
            let searchPrice = null;
            let searchPriceString = null;
            if (isPriceQuery) {
                // Si l'utilisateur tape un nombre avec virgule ou point, on cherche la correspondance exacte
                if (searchTerm.includes(',') || searchTerm.includes('.')) {
                    searchPrice = parseFloat(searchTerm.replace(/[^\d,\.]/g, '').replace(',', '.'));
                } else {
                    // Sinon, on cherche tous les prix qui CONTIENNENT ce nombre
                    searchPriceString = searchTerm.replace(/[^\d]/g, '');
                }
            }
            
            // Parcourir tous les éléments et les filtrer
            elements.forEach(element => {
                const destination = element.querySelector('.destination').textContent.toLowerCase();
                const dates = element.querySelector('.dates').textContent.toLowerCase();
                const priceText = element.querySelector('.price').textContent;
                const priceValue = utils.extractPrice(priceText);
                const priceDigits = priceText.replace(/[^\d]/g, '');
                
                let match = false;
                
                if (searchTerm === '') {
                    match = true;
                } else if (isPriceQuery) {
                    if (searchPrice !== null && !isNaN(searchPrice)) {
                        // Recherche exacte (tolérance 1 centime)
                        match = Math.abs(priceValue - searchPrice) < 0.01;
                    } else if (searchPriceString) {
                        // Recherche partielle : le prix affiché contient la séquence de chiffres
                        match = priceDigits.includes(searchPriceString);
                    }
                } else if (utils.isDateQuery(searchTerm)) {
                    match = dates.includes(searchTerm);
                } else {
                    match = destination.includes(searchTerm);
                }
                
                // Afficher ou masquer l'élément
                element.style.display = match ? '' : 'none';
                if (match) matchCount++;
            });
            
            // Gestion de l'affichage "aucun résultat"
            this.updateSearchResults(searchTerm, matchCount);
            
            // Mise à jour du compteur
            this.updateCounter(searchTerm !== '' ? matchCount : null);
        },
        
        // Met à jour l'affichage des résultats de recherche
        updateSearchResults(searchTerm, matchCount) {
            if (!this.elements.searchNoResults) return;
            
            if (matchCount === 0 && searchTerm !== '') {
                this.elements.searchNoResults.style.display = 'flex';
                
                if (this.elements.searchTerm) {
                    this.elements.searchTerm.textContent = searchTerm;
                }
                
                // Masquer les conteneurs principaux
                if (this.state.isCardView && this.elements.voyagesContainer) {
                    this.elements.voyagesContainer.style.display = 'none';
                }
                if (this.state.isTableView && this.elements.tableContainer) {
                    this.elements.tableContainer.style.display = 'none';
                }
            } else {
                this.elements.searchNoResults.style.display = 'none';
                
                // Afficher les conteneurs principaux
                if (this.state.isCardView && this.elements.voyagesContainer) {
                    this.elements.voyagesContainer.style.display = '';
                }
                if (this.state.isTableView && this.elements.tableContainer) {
                    this.elements.tableContainer.style.display = '';
                }
            }
        }
    };
    
    // Initialisation du gestionnaire de voyages
    voyagesManager.init();
}); 