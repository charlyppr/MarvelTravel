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
    
    // Fonction de tri pour les voyages
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortVoyages(this.value);
        });
    }
    
    function sortVoyages(sortType) {
        // Déterminer si on est en vue cartes ou en vue tableau
        const isCardView = voyagesContainer && voyagesContainer.children.length > 0;
        const isTableView = tableContainer && tableContainer.querySelector('tbody') && 
                        tableContainer.querySelector('tbody').children.length > 0;
        
        if (!isCardView && !isTableView) return;
        
        let elements = isCardView 
            ? Array.from(voyagesContainer.querySelectorAll('.voyage-card')) 
            : Array.from(tableContainer.querySelector('tbody').querySelectorAll('tr'));
            
        elements.sort(function(a, b) {
            switch (sortType) {
                case 'recent':
                    // Tri par date d'achat (plus récent en premier)
                    const dateA = a.dataset.dateAchat || '';
                    const dateB = b.dataset.dateAchat || '';
                    return dateB.localeCompare(dateA);
                
                case 'price-asc':
                case 'price-desc':
                    // Tri par prix
                    let priceA, priceB;
                    
                    if (isCardView) {
                        priceA = parseFloat(a.querySelector('.price').textContent.replace(/[^\d,]/g, '').replace(',', '.'));
                        priceB = parseFloat(b.querySelector('.price').textContent.replace(/[^\d,]/g, '').replace(',', '.'));
                    } else {
                        priceA = parseFloat(a.querySelector('.price').textContent.replace(/[^\d,]/g, '').replace(',', '.'));
                        priceB = parseFloat(b.querySelector('.price').textContent.replace(/[^\d,]/g, '').replace(',', '.'));
                    }
                    
                    return sortType === 'price-asc' ? priceA - priceB : priceB - priceA;
                
                case 'date-asc':
                case 'date-desc':
                    // Tri par date de début du voyage
                    let dateDebutA, dateDebutB;
                    
                    if (isCardView) {
                        dateDebutA = a.querySelector('.dates').textContent.trim().split(' au ')[0];
                        dateDebutB = b.querySelector('.dates').textContent.trim().split(' au ')[0];
                    } else {
                        dateDebutA = a.querySelector('.dates').textContent.trim().split(' au ')[0];
                        dateDebutB = b.querySelector('.dates').textContent.trim().split(' au ')[0];
                    }
                    
                    // Convertir les dates (format DD/MM/YYYY) en objets Date
                    const datePartsA = dateDebutA.split('/');
                    const datePartsB = dateDebutB.split('/');
                    
                    if (datePartsA.length === 3 && datePartsB.length === 3) {
                        const dateObjA = new Date(datePartsA[2], datePartsA[1] - 1, datePartsA[0]);
                        const dateObjB = new Date(datePartsB[2], datePartsB[1] - 1, datePartsB[0]);
                        
                        return sortType === 'date-asc' 
                            ? dateObjA.getTime() - dateObjB.getTime()
                            : dateObjB.getTime() - dateObjA.getTime();
                    }
                    
                    return 0;
                
                default:
                    return 0;
            }
        });
        
        // Réorganiser les éléments selon le tri
        if (isCardView) {
            elements.forEach(el => voyagesContainer.appendChild(el));
        } else if (isTableView) {
            const tbody = tableContainer.querySelector('tbody');
            elements.forEach(el => tbody.appendChild(el));
        }
    }
    
    // Fonction de recherche
    if (searchInput) {
        // Événement de saisie dans le champ de recherche
        searchInput.addEventListener('input', performSearch);
        
        // Événement clic sur le bouton de recherche
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                performSearch();
            });
        }
        
        // Événement clic sur le bouton de réinitialisation
        if (resetSearchButton) {
            resetSearchButton.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
            });
        }
        
        // Événement touche Entrée dans le champ de recherche
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
                e.preventDefault();
            }
        });
    }
    
    function performSearch() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        
        // Déterminer si on est en vue cartes ou en vue tableau
        const isCardView = voyagesContainer && voyagesContainer.children.length > 0;
        const isTableView = tableContainer && tableContainer.querySelector('tbody') && 
                        tableContainer.querySelector('tbody').children.length > 0;
        
        if (!isCardView && !isTableView) return;
        
        let elements = isCardView 
            ? Array.from(voyagesContainer.querySelectorAll('.voyage-card')) 
            : Array.from(tableContainer.querySelector('tbody').querySelectorAll('tr'));
        
        let matchCount = 0;
        
        // Parcourir tous les éléments et les filtrer
        elements.forEach(function(element) {
            let match = false;
            
            if (searchTerm === '') {
                // Si le terme de recherche est vide, afficher tous les éléments
                match = true;
            } else {
                // Rechercher dans le contenu textuel de l'élément
                const content = element.textContent.toLowerCase();
                
                // Vérifier si le terme de recherche est présent dans le contenu
                if (content.includes(searchTerm)) {
                    match = true;
                }
            }
            
            // Afficher ou masquer l'élément selon qu'il correspond ou non
            if (match) {
                element.style.display = '';
                matchCount++;
            } else {
                element.style.display = 'none';
            }
        });
        
        // Afficher ou masquer le message "aucun résultat"
        if (searchNoResults) {
            if (matchCount === 0 && searchTerm !== '') {
                searchNoResults.style.display = 'flex';
                if (searchTermDisplay) {
                    searchTermDisplay.textContent = searchTerm;
                }
                
                // Masquer les conteneurs principaux
                if (isCardView) voyagesContainer.style.display = 'none';
                if (isTableView) tableContainer.style.display = 'none';
            } else {
                searchNoResults.style.display = 'none';
                
                // Afficher les conteneurs principaux
                if (isCardView) voyagesContainer.style.display = '';
                if (isTableView) tableContainer.style.display = '';
            }
        }
        
        // Mettre à jour le compteur de voyages visibles
        if (voyageCountElement) {
            if (searchTerm === '') {
                // Si pas de recherche, afficher le total
                if (isAdminPage) {
                    const totalUsers = elements.length;
                    voyageCountElement.textContent = `${totalUsers} voyageurs`;
                } else {
                    voyageCountElement.textContent = `${elements.length} au total`;
                }
            } else {
                // Si recherche active, afficher le nombre de résultats
                if (isAdminPage) {
                    voyageCountElement.textContent = `${matchCount} voyageur${matchCount > 1 ? 's' : ''} trouvé${matchCount > 1 ? 's' : ''}`;
                } else {
                    voyageCountElement.textContent = `${matchCount} résultat${matchCount > 1 ? 's' : ''}`;
                }
            }
        }
    }
}); 