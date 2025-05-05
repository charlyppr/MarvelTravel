document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        selectors: {
            tabButtons: '.tab-button',
            tabContents: '.tab-content',
            tabContainer: '.tab-buttons',
            carousel: '.carousel-container',
            carouselTrack: '.carousel-track',
            carouselSlide: '.carousel-slide',
            dotsContainer: '.carousel-dots',
            nextButton: '.next-button',
            prevButton: '.prev-button',
            dot: '.dot'
        },
        classes: {
            active: 'active'
        },
        timing: {
            autoPlayInterval: 5000
        }
    };

    // Gestionnaire du détail de voyage
    const voyageDetailManager = {
        // Propriétés
        currentIndex: 0,
        intervalId: null,
        elements: {},

        // Initialisation
        init() {
            this.cacheElements();
            this.setupTabs();
            this.setupCarousel();
        },

        // Mise en cache des éléments DOM fréquemment utilisés
        cacheElements() {
            this.elements = {
                tabContainer: document.querySelector(config.selectors.tabContainer) || document.body,
                tabButtons: document.querySelectorAll(config.selectors.tabButtons),
                tabContents: document.querySelectorAll(config.selectors.tabContents),
                carousel: document.querySelector(config.selectors.carousel)
            };

            // Éléments du carousel (seulement si le carousel existe)
            if (this.elements.carousel) {
                this.elements.track = this.elements.carousel.querySelector(config.selectors.carouselTrack);
                this.elements.slides = Array.from(this.elements.carousel.querySelectorAll(config.selectors.carouselSlide));
                this.elements.dotsContainer = this.elements.carousel.querySelector(config.selectors.dotsContainer) || this.elements.carousel;
                this.elements.nextButton = this.elements.carousel.querySelector(config.selectors.nextButton);
                this.elements.prevButton = this.elements.carousel.querySelector(config.selectors.prevButton);
                this.elements.dots = Array.from(this.elements.carousel.querySelectorAll(config.selectors.dot));
            }
        },

        // Configuration des onglets
        setupTabs() {
            const { tabContainer, tabButtons, tabContents } = this.elements;
            
            tabContainer.addEventListener('click', (e) => {
                const button = e.target.closest(config.selectors.tabButtons);
                if (!button) return;
                
                // Gestion des classes actives
                tabButtons.forEach(btn => btn.classList.remove(config.classes.active));
                tabContents.forEach(content => content.classList.remove(config.classes.active));
                
                button.classList.add(config.classes.active);
                
                // Affichage du contenu correspondant
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-content').classList.add(config.classes.active);
            });
        },

        // Configuration du carousel
        setupCarousel() {
            if (!this.elements.carousel) return;
            
            const { carousel, nextButton, prevButton, dotsContainer } = this.elements;
            
            // Navigation par boutons
            nextButton.addEventListener('click', () => this.moveToSlide(this.getNextIndex()));
            prevButton.addEventListener('click', () => this.moveToSlide(this.getPrevIndex()));
            
            // Navigation par points
            dotsContainer.addEventListener('click', (e) => {
                const dot = e.target.closest(config.selectors.dot);
                if (!dot) return;
                
                const index = this.elements.dots.indexOf(dot);
                if (index !== -1) {
                    this.moveToSlide(index);
                }
            });
            
            // Contrôle de l'autoplay
            this.startAutoPlay();
            carousel.addEventListener('mouseenter', () => this.stopAutoPlay());
            carousel.addEventListener('mouseleave', () => this.startAutoPlay());
            
            // Initialisation du premier slide
            this.moveToSlide(0);
        },

        // Déplacement vers un slide spécifique
        moveToSlide(index) {
            const { track, dots } = this.elements;
            
            track.style.transform = `translateX(-${index * 100}%)`;
            
            // Mise à jour des points indicateurs
            dots.forEach((dot, i) => {
                dot.classList.toggle(config.classes.active, i === index);
            });
            
            this.currentIndex = index;
        },

        // Calcul de l'index suivant
        getNextIndex() {
            return (this.currentIndex + 1) % this.elements.slides.length;
        },

        // Calcul de l'index précédent
        getPrevIndex() {
            return (this.currentIndex - 1 + this.elements.slides.length) % this.elements.slides.length;
        },

        // Démarrage de l'autoplay
        startAutoPlay() {
            this.stopAutoPlay(); // Arrêt préventif
            this.intervalId = setInterval(() => {
                this.moveToSlide(this.getNextIndex());
            }, config.timing.autoPlayInterval);
        },

        // Arrêt de l'autoplay
        stopAutoPlay() {
            clearInterval(this.intervalId);
        }
    };
    
    // Initialisation du gestionnaire
    voyageDetailManager.init();
}); 