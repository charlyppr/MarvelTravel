document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        rotationInterval: 3000, // Intervalle de rotation en ms
        cardSelectors: {
            container: '.card-stack',
            cards: '.card-stack .destination-card'
        },
        states: ['active', 'next', 'previous', 'back', 'leaving']
    };
    
    // Gestionnaire du carousel de cartes
    const cardCarousel = {
        cards: [],
        currentIndex: 0,
        intervalId: null,
        container: null,
        
        // Initialisation
        init() {
            this.cards = document.querySelectorAll(config.cardSelectors.cards);
            this.container = document.querySelector(config.cardSelectors.container);
            
            if (!this.cards.length) return false;
            
            this.setupEventListeners();
            this.updateCardsState();
            this.startAutoRotation();
            
            return true;
        },
        
        // Mise en place des écouteurs d'événements
        setupEventListeners() {
            // Pause de la rotation au survol du conteneur
            if (this.container) {
                this.container.addEventListener('mouseenter', () => this.pauseRotation());
                this.container.addEventListener('mouseleave', () => this.resumeRotation());
            }
        },
        
        // Mise à jour de l'état visuel des cartes
        updateCardsState() {
            const total = this.cards.length;
            
            this.cards.forEach((card, i) => {
                // Supprime toutes les classes d'état
                config.states.forEach(state => card.classList.remove(state));
                
                // Calcul des positions relatives avec arithmétique modulaire
                switch (true) {
                    case i === this.currentIndex:
                        card.classList.add('active');
                        break;
                    case i === (this.currentIndex + 1) % total:
                        card.classList.add('next');
                        break;
                    case i === (this.currentIndex + 2) % total:
                        card.classList.add('previous');
                        break;
                    case i === (this.currentIndex + 3) % total:
                        card.classList.add('back');
                        break;
                    case i === (this.currentIndex - 1 + total) % total:
                        card.classList.add('leaving');
                        break;
                }
            });
        },
        
        // Avance à la carte suivante
        nextCard() {
            this.currentIndex = (this.currentIndex + 1) % this.cards.length;
            this.updateCardsState();
        },
        
        // Démarre la rotation automatique
        startAutoRotation() {
            if (!this.intervalId) {
                this.intervalId = setInterval(() => this.nextCard(), config.rotationInterval);
            }
        },
        
        // Pause la rotation (utile au survol)
        pauseRotation() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },
        
        // Reprend la rotation
        resumeRotation() {
            this.startAutoRotation();
        }
    };
    
    // Initialisation du carousel
    cardCarousel.init();
}); 