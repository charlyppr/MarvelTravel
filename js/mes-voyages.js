// Mettre à jour le compteur de voyages
document.getElementById('voyage-count').textContent = "<?php echo $total_voyages; ?> au total";


document.addEventListener('DOMContentLoaded', function() {
    // Déterminer la page active
    const isAdminPage = document.querySelector('.tab-voyageurs') !== null;
    
    const sortSelect = document.getElementById('sort-select');
    const voyagesContainer = document.querySelector('.voyages-list');
    const tableContainer = isAdminPage ? 
                           document.querySelector('.table-container') : 
                           document.querySelector('.table-container');
    const searchInput = document.getElementById('search');
    const searchButton = searchInput ? searchInput.nextElementSibling : null;
    const resetSearchButton = document.getElementById('reset-search');
    const searchNoResults = document.getElementById('search-no-results');
    const searchTermDisplay = document.getElementById('search-term');
    
    // Mettre à jour le compteur
    const voyageCountElement = document.getElementById('voyage-count');
    if (voyageCountElement) {
        if (isAdminPage) {
            const totalUsers = document.querySelectorAll('tbody tr').length;
            voyageCountElement.textContent = `${totalUsers} voyageurs`;
        } else {
            const totalVoyages = document.querySelectorAll('.voyage-card, .tab-voyages tbody tr').length;
            voyageCountElement.textContent = `${totalVoyages} au total`;
        }
    }
    
    // Fonction pour extraire le prix d'un élément (pour la page de voyages)
    function getPrice(element) {
        const priceElement = element.querySelector('.price');
        if (priceElement) {
            const priceText = priceElement.textContent.trim();
            return parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
        }
        return 0;
    }

    // Fonction pour extraire la date d'un élément
    function getDate(element) {
        const dateElement = element.querySelector('.dates, .date');
        if (dateElement) {
            const dateText = dateElement.textContent.trim();
            if (isAdminPage) {
                // Format pour la page d'administrateur: "YYYY-MM-DD" ou texte
                return dateText;
            } else {
                // Format pour la page de voyages: "DD/MM/YYYY"
                const dateMatch = dateText.match(/(\d{2}\/\d{2}\/\d{4})/);
                if (dateMatch) {
                    const [day, month, year] = dateMatch[1].split('/');
                    return new Date(year, month - 1, day);
                }
            }
        }
        return isAdminPage ? '' : new Date(0);
    }

    // Fonction pour extraire le nom d'un élément (pour la page d'administrateur)
    function getName(element) {
        const nameElement = element.querySelector('.nom');
        return nameElement ? nameElement.textContent.trim().toLowerCase() : '';
    }

    // Fonction pour extraire la date d'achat/inscription d'un élément
    function getPurchaseDate(element) {
        if (isAdminPage) {
            const dateElement = element.querySelector('.date');
            if (dateElement) {
                const dateText = dateElement.textContent.trim();
                // Format YYYY-MM-DD
                return dateText;
            }
            return '';
        } else {
            const recentBadge = element.querySelector('.badge-new');
            return recentBadge ? new Date() : new Date(0);
        }
    }

    // Fonction pour trier les éléments
    function sortElements(sortValue) {
        const container = isAdminPage ? tableContainer : (voyagesContainer || tableContainer);
        if (!container) return;

        const elements = isAdminPage ? 
                        Array.from(container.querySelectorAll('tbody tr')) : 
                        Array.from(container.children);
        if (elements.length === 0) return;

        elements.sort((a, b) => {
            switch (sortValue) {
                case 'price-asc':
                    return getPrice(a) - getPrice(b);
                case 'price-desc':
                    return getPrice(b) - getPrice(a);
                case 'date-asc':
                    if (isAdminPage) {
                        return getDate(a).localeCompare(getDate(b));
                    } else {
                        return getDate(a) - getDate(b);
                    }
                case 'date-desc':
                    if (isAdminPage) {
                        return getDate(b).localeCompare(getDate(a));
                    } else {
                        return getDate(b) - getDate(a);
                    }
                case 'name-asc':
                    return getName(a).localeCompare(getName(b));
                case 'name-desc':
                    return getName(b).localeCompare(getName(a));
                case 'recent':
                default:
                    if (isAdminPage) {
                        return getPurchaseDate(b).localeCompare(getPurchaseDate(a));
                    } else {
                        return getPurchaseDate(b) - getPurchaseDate(a);
                    }
            }
        });

        // Réorganiser les éléments dans le DOM
        const parent = isAdminPage ? container.querySelector('tbody') : container;
        elements.forEach(element => {
            parent.appendChild(element);
        });
    }

    // Fonction pour rechercher des éléments
    function searchElements(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();
        
        // Mise à jour de l'affichage du terme recherché
        if (searchTermDisplay) {
            searchTermDisplay.textContent = searchTerm;
        }
        
        if (!searchTerm) {
            // Si la recherche est vide, afficher tous les éléments
            showAllElements();
            return;
        }

        const container = isAdminPage ? tableContainer : (voyagesContainer || tableContainer);
        if (!container) return;

        const elements = isAdminPage ? 
                        Array.from(container.querySelectorAll('tbody tr')) : 
                        Array.from(container.querySelectorAll('.voyage-card, tbody tr'));
        let visibleCount = 0;

        elements.forEach(element => {
            let isMatch = false;
            
            if (isAdminPage) {
                // Recherche pour la page d'administrateur
                const nameElement = element.querySelector('.nom');
                const name = nameElement ? nameElement.textContent.toLowerCase() : '';
                const emailAttr = element.getAttribute('data-email');
                const email = emailAttr ? emailAttr.toLowerCase() : '';
                
                isMatch = name.includes(searchTerm) || email.includes(searchTerm);
            } else {
                // Recherche pour la page de voyages
                const destinationElement = element.querySelector('.destination');
                const destination = destinationElement ? destinationElement.textContent.toLowerCase() : '';
                
                const datesElement = element.querySelector('.dates');
                const dates = datesElement ? datesElement.textContent.toLowerCase() : '';
                
                const travelersElement = element.querySelector('.travelers');
                const travelers = travelersElement ? travelersElement.textContent.toLowerCase() : '';
                
                isMatch = destination.includes(searchTerm) || 
                          dates.includes(searchTerm) || 
                          travelers.includes(searchTerm);
            }
            
            // Afficher ou masquer l'élément
            if (isMatch) {
                element.style.display = '';
                visibleCount++;
            } else {
                element.style.display = 'none';
            }
        });
        
        // Afficher un message si aucun résultat
        if (visibleCount === 0 && searchNoResults) {
            container.style.display = 'none';
            searchNoResults.style.display = 'flex';
        } else {
            if (container) container.style.display = '';
            if (searchNoResults) searchNoResults.style.display = 'none';
        }
        
        // Mettre à jour le compteur
        if (voyageCountElement) {
            if (isAdminPage) {
                voyageCountElement.textContent = `${visibleCount} voyageurs`;
            } else {
                voyageCountElement.textContent = `${visibleCount} sur ${elements.length} au total`;
            }
        }
    }
    
    // Fonction pour réinitialiser la recherche
    function showAllElements() {
        const container = isAdminPage ? tableContainer : (voyagesContainer || tableContainer);
        if (!container) return;
        
        // Afficher tous les éléments
        const selector = isAdminPage ? 'tbody tr' : '.voyage-card, tbody tr';
        const elements = container.querySelectorAll(selector);
        elements.forEach(element => {
            element.style.display = '';
        });
        
        // Masquer le message "aucun résultat"
        if (searchNoResults) searchNoResults.style.display = 'none';
        
        // Afficher à nouveau le conteneur principal
        if (container) container.style.display = '';
        
        // Réinitialiser le champ de recherche
        if (searchInput) searchInput.value = '';
        
        // Mettre à jour le compteur
        if (voyageCountElement) {
            if (isAdminPage) {
                voyageCountElement.textContent = `${elements.length} voyageurs`;
            } else {
                voyageCountElement.textContent = `${elements.length} au total`;
            }
        }
    }

    // Initialiser le tri au chargement de la page
    if (sortSelect) {
        sortElements(sortSelect.value || 'recent');
    }

    // Gérer le changement de tri
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortElements(this.value);
        });
    }
    
    // Gérer la recherche
    if (searchInput) {
        // Rechercher en temps réel à chaque saisie
        searchInput.addEventListener('input', function() {
            searchElements(this.value);
        });
        
        // Rechercher lors du clic sur le bouton
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                searchElements(searchInput.value);
            });
        }
        
        // Gérer la touche Entrée dans le champ de recherche
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchElements(this.value);
            }
        });
    }
    
    // Gérer le bouton de réinitialisation de la recherche
    if (resetSearchButton) {
        resetSearchButton.addEventListener('click', showAllElements);
    }
}); 