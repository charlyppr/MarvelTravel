document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la vue (grille/liste)
    const viewButtons = document.querySelectorAll('.view-button');
    const destinationsContainer = document.getElementById('destinations-container');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            viewButtons.forEach(btn => btn.classList.remove('active'));
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Changer la vue
            const viewType = this.getAttribute('data-view');
            if (viewType === 'list') {
                destinationsContainer.classList.add('list-view');
                destinationsContainer.classList.remove('destinations-grid');
            } else {
                destinationsContainer.classList.remove('list-view');
                destinationsContainer.classList.add('destinations-grid');
            }
            
            // Sauvegarder la préférence utilisateur
            localStorage.setItem('preferredView', viewType);
        });
    });
    
    // Restaurer la vue préférée de l'utilisateur
    const preferredView = localStorage.getItem('preferredView');
    if (preferredView && destinationsContainer) {
        if (preferredView === 'list') {
            destinationsContainer.classList.add('list-view');
            destinationsContainer.classList.remove('destinations-grid');
            document.querySelector('[data-view="list"]').classList.add('active');
            document.querySelector('[data-view="grid"]').classList.remove('active');
        }
    }
    
    // Gestion du curseur de prix
    const priceRange = document.getElementById('price_range');
    const priceValue = document.getElementById('price_value');
    
    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = this.value + ' €';
        });
    }
    
    // Animation au défilement pour les sections
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.featured-section, .categories-section, .testimonials-section, .cta-section, .newsletter-section').forEach(section => {
        section.classList.add('animate-ready');
        observer.observe(section);
    });
    
    // Gestion du formulaire newsletter avec feedback
    const newsletterForm = document.getElementById('newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Simuler l'envoi du formulaire
            submitButton.textContent = 'Envoi en cours...';
            submitButton.disabled = true;
            
            setTimeout(() => {
                // Simuler une réponse réussie
                showNotification('Merci ! Vous êtes maintenant inscrit à notre newsletter.', 'success');
                
                // Réinitialiser le formulaire
                newsletterForm.reset();
                submitButton.textContent = originalText;
                submitButton.disabled = false;
                
            }, 1500);
        });
    }
    
    // Fonction pour afficher une notification
    function showNotification(message, type = 'info') {
        // Vérifier si une notification existe déjà et la supprimer
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Créer la notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Ajouter la notification au DOM
        document.body.appendChild(notification);
        
        // Afficher la notification avec animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Ajouter un gestionnaire d'événements pour fermer la notification
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
        
        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    // Effet parallaxe sur les images de fond
    const heroSection = document.querySelector('.hero-section');
    
    if (heroSection) {
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            if (scrollPosition < window.innerHeight) {
                heroSection.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
            }
        });
    }
}); 