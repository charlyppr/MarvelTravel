document.addEventListener('DOMContentLoaded', function () {
    // Afficher le nombre d'utilisateurs
    const userCount = document.querySelectorAll('tbody tr').length;
    document.getElementById('voyage-count').textContent = userCount + ' voyageurs';

    // Gestion des clics sur les status (bloqué/actif)
    document.querySelectorAll('.toggle-status').forEach(statusElement => {
        statusElement.addEventListener('click', function () {
            // Vérifier l'état actuel
            const isBlocked = this.getAttribute('data-status') === 'blocked';

            // Simuler un délai de mise à jour
            this.classList.add('updating');

            setTimeout(() => {
                // Basculer l'état
                if (isBlocked) {
                    // Changer en Actif
                    this.classList.remove('status-pending');
                    this.classList.add('status-ok');
                    this.setAttribute('data-status', 'active');
                    this.innerHTML = 'Actif<img src="../img/svg/check.svg" alt="check"><span class="tooltip">Cliquez pour bloquer</span>';
                } else {
                    // Changer en Bloqué
                    this.classList.remove('status-ok');
                    this.classList.add('status-pending');
                    this.setAttribute('data-status', 'blocked');
                    this.innerHTML = 'Bloqué<img src="../img/svg/block.svg" alt="block"><span class="tooltip">Cliquez pour débloquer</span>';
                }

                this.classList.remove('updating');

                // Afficher une notification
                showNotification(isBlocked ? 'Utilisateur débloqué avec succès' : 'Utilisateur bloqué avec succès', 'success');
            }, 1000);
        });
    });

    // Gestion des clics sur les badges VIP
    document.querySelectorAll('.toggle-vip').forEach(vipElement => {
        vipElement.addEventListener('click', function () {
            // Vérifier l'état actuel
            const isVip = this.getAttribute('data-vip') === '1';

            // Simuler un délai de mise à jour
            this.classList.add('updating');

            setTimeout(() => {
                // Basculer l'état
                if (isVip) {
                    // Changer en non-VIP
                    this.classList.remove('vip-badge');
                    this.classList.add('novip-badge');
                    this.setAttribute('data-vip', '0');
                    this.innerHTML = 'Non<img src="../img/svg/no.svg" alt="croix"><span class="tooltip">Cliquez pour ajouter le VIP</span>';
                } else {
                    // Changer en VIP
                    this.classList.remove('novip-badge');
                    this.classList.add('vip-badge');
                    this.setAttribute('data-vip', '1');
                    this.innerHTML = 'VIP<img src="../img/svg/etoile.svg" alt="etoile"><span class="tooltip">Cliquez pour retirer le VIP</span>';
                }

                this.classList.remove('updating');

                // Afficher une notification
                showNotification(isVip ? 'Statut VIP retiré avec succès' : 'Statut VIP ajouté avec succès', 'success');
            }, 1000);
        });
    });

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'success') {
        // Supprimer une notification existante si elle est déjà présente
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Afficher avec fade-in
        setTimeout(() => notification.style.opacity = '1', 10);

        // Cacher après 3 secondes
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    // Fonctionnalité de tri
    const sortSelect = document.getElementById('sort-select');
    sortSelect.addEventListener('change', function() {
        sortTable(this.value);
    });

    function sortTable(sortBy) {
        const tableBody = document.querySelector('.tab-voyageurs tbody');
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            switch(sortBy) {
                case 'recent':
                    // Tri par date d'inscription (plus récent d'abord)
                    const dateA = new Date(a.querySelector('.date').getAttribute('data-date'));
                    const dateB = new Date(b.querySelector('.date').getAttribute('data-date'));
                    return dateB - dateA;
                case 'oldest':
                    // Tri par date d'inscription (plus ancien d'abord)
                    const dateC = new Date(a.querySelector('.date').getAttribute('data-date'));
                    const dateD = new Date(b.querySelector('.date').getAttribute('data-date'));
                    return dateC - dateD;
                case 'name-asc':
                    // Tri par nom (A-Z)
                    return a.querySelector('.nom').textContent.localeCompare(b.querySelector('.nom').textContent);
                case 'name-desc':
                    // Tri par nom (Z-A)
                    return b.querySelector('.nom').textContent.localeCompare(a.querySelector('.nom').textContent);
                default:
                    return 0;
            }
        });
        
        // Vider et remplir le tableau avec les lignes triées
        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));
        
        // Mise à jour du compteur après le tri
        updateUserCount();
    }
    
    // Fonctionnalité de recherche
    const searchInput = document.getElementById('search');
    const searchButton = searchInput.nextElementSibling;
    const searchNoResults = document.getElementById('search-no-results');
    const searchTerm = document.getElementById('search-term');
    const resetSearch = document.getElementById('reset-search');
    
    // Fonction de recherche
    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.tab-voyageurs tbody tr');
        let hasResults = false;
        
        rows.forEach(row => {
            const name = row.querySelector('.nom').textContent.toLowerCase();
            const email = row.getAttribute('data-email').toLowerCase();
            
            // Si la requête contient @ ou ressemble à un email, chercher dans l'email aussi
            // Sinon chercher uniquement dans le nom
            const matchFound = query.includes('@') 
                ? email.includes(query) 
                : name.includes(query);
            
            if (matchFound || query === '') {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Afficher le message "aucun résultat" si nécessaire
        if (!hasResults && query !== '') {
            searchNoResults.style.display = 'flex';
            searchTerm.textContent = query;
        } else {
            searchNoResults.style.display = 'none';
        }
        
        // Mise à jour du compteur après la recherche
        updateUserCount();
    }
    
    // Événements de recherche
    searchInput.addEventListener('input', performSearch);
    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    // Réinitialiser la recherche
    resetSearch.addEventListener('click', function() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    });
    
    // Fonction pour mettre à jour le compteur d'utilisateurs visibles
    function updateUserCount() {
        const visibleUsers = document.querySelectorAll('.tab-voyageurs tbody tr[style=""]').length || 
                             document.querySelectorAll('.tab-voyageurs tbody tr:not([style*="display: none"])').length;
        document.getElementById('voyage-count').textContent = visibleUsers + ' voyageurs';
    }
    
    // Tri initial
    sortTable('recent');
}); 