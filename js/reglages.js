document.addEventListener('DOMContentLoaded', function () {
    // Configuration centralisée
    const config = {
        selectors: {
            notifications: '.notification',
            closeNotification: '.close-notification',
            resetButton: '.reset-button',
            themeOptions: 'input[name="theme"]',
            themeSelector: '.theme-selector .theme-option',
            highContrastToggle: '#highContrast',
            fontSizeOptions: 'input[name="fontSize"]',
            fontSizeSelector: '.font-size-selector .theme-option',
            dyslexicFontToggle: '#dyslexicFont',
            reduceMotionToggle: '#reduceMotion',
            downloadButton: '.download-button'
        },
        classes: {
            themeTransition: 'theme-transition',
            selected: 'selected',
            highContrast: 'high-contrast',
            dyslexicFont: 'dyslexic-font',
            reduceMotion: 'reduce-motion'
        },
        timing: {
            notificationAutoClose: 5000,
            notificationFadeOut: 300,
            themeTransitionDuration: 600
        }
    };

    // Utilitaires
    const utils = {
        // Définir un cookie
        setCookie: (name, value, days) => {
            const expires = new Date();
            expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
            document.cookie = `${name}=${value};path=/;expires=${expires.toUTCString()}`;
        },
        
        // Créer et soumettre un formulaire
        submitForm: (action, params) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action || '';
            
            Object.entries(params).forEach(([key, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    };

    // Gestionnaire des réglages
    const settingsManager = {
        // Initialisation
        init() {
            this.setupNotifications();
            this.setupResetButton();
            this.setupThemeOptions();
            this.setupAccessibilityOptions();
            this.setupDataExport();
        },
        
        // Gestion des notifications
        setupNotifications() {
            const notifications = document.querySelectorAll(config.selectors.notifications);
            if (notifications.length === 0) return;
            
            notifications.forEach(notification => {
                // Ajouter un bouton de fermeture si nécessaire
                if (!notification.querySelector(config.selectors.closeNotification)) {
                    const closeButton = document.createElement('button');
                    closeButton.className = 'close-notification';
                    closeButton.innerHTML = '&times;';
                    notification.appendChild(closeButton);
                }
                
                // Configurer la fermeture au clic
                notification.querySelector(config.selectors.closeNotification)
                    .addEventListener('click', () => this.fadeOutNotification(notification));
                
                // Fermeture automatique
                setTimeout(() => this.fadeOutNotification(notification), config.timing.notificationAutoClose);
            });
        },
        
        // Effet de disparition pour les notifications
        fadeOutNotification(notification) {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), config.timing.notificationFadeOut);
        },
        
        // Bouton de réinitialisation
        setupResetButton() {
            const resetButton = document.querySelector(config.selectors.resetButton);
            if (!resetButton) return;
            
            resetButton.addEventListener('click', () => {
                if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?')) {
                    utils.submitForm('', { reset_settings: '1' });
                }
            });
        },
        
        // Options de thème
        setupThemeOptions() {
            const themeOptions = document.querySelectorAll(config.selectors.themeOptions);
            if (themeOptions.length === 0) return;
            
            // S'assurer que la sélection visuelle corresponde au thème actuel au chargement
            const currentTheme = document.cookie.replace(/(?:(?:^|.*;\s*)theme\s*\=\s*([^;]*).*$)|^.*$/, "$1") || "dark";
            document.querySelectorAll(config.selectors.themeSelector).forEach(el => {
                el.classList.remove(config.classes.selected);
                const radioInput = el.querySelector('input[type="radio"]');
                if (radioInput && radioInput.value === currentTheme) {
                    el.classList.add(config.classes.selected);
                }
            });
            
            themeOptions.forEach(option => {
                option.addEventListener('change', () => {
                    this.applyTheme(option.value);
                    
                    // Mise à jour visuelle de la sélection
                    document.querySelectorAll(config.selectors.themeSelector).forEach(el => {
                        el.classList.remove(config.classes.selected);
                    });
                    option.closest('.theme-option').classList.add(config.classes.selected);
                });
            });
        },
        
        // Appliquer un thème
        applyTheme(theme) {
            // Ajouter la transition
            document.body.classList.add(config.classes.themeTransition);
            
            // Supprimer les classes de thème existantes
            document.body.classList.remove('light-theme', 'dark-theme', 'auto-theme');
            
            // Déterminer le thème à appliquer
            let effectiveTheme = theme;
            if (theme === 'auto') {
                const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                effectiveTheme = prefersDarkMode ? 'dark' : 'light';
            }
            
            // Appliquer le thème
            document.body.classList.add(`${effectiveTheme}-theme`);
            
            // Supprimer la classe de transition après délai
            setTimeout(() => {
                document.body.classList.remove(config.classes.themeTransition);
            }, config.timing.themeTransitionDuration);
            
            // Si la fonction updateUserThemeInDB existe (définie dans theme-loader.js),
            // l'utiliser pour mettre à jour le thème dans la base de données
            if (typeof updateUserThemeInDB === 'function') {
                updateUserThemeInDB(theme);
            }
        },
        
        // Options d'accessibilité
        setupAccessibilityOptions() {
            this.setupHighContrastToggle();
            this.setupFontSizeOptions();
            this.setupDyslexicFontToggle();
            this.setupReduceMotionToggle();
        },
        
        // Option de contraste élevé
        setupHighContrastToggle() {
            const highContrastToggle = document.getElementById('highContrast');
            if (!highContrastToggle) return;
            
            highContrastToggle.addEventListener('change', () => {
                document.body.classList.toggle(config.classes.highContrast, highContrastToggle.checked);
            });
        },
        
        // Options de taille de police
        setupFontSizeOptions() {
            const fontSizeOptions = document.querySelectorAll(config.selectors.fontSizeOptions);
            if (fontSizeOptions.length === 0) return;
            
            fontSizeOptions.forEach(option => {
                option.addEventListener('change', () => {
                    // Mettre à jour les classes de taille
                    document.body.classList.remove('font-size-normal', 'font-size-large', 'font-size-larger');
                    document.body.classList.add(`font-size-${option.value}`);
                    
                    // Mise à jour visuelle
                    document.querySelectorAll(config.selectors.fontSizeSelector).forEach(el => {
                        el.classList.remove(config.classes.selected);
                    });
                    option.closest('.theme-option').classList.add(config.classes.selected);
                });
            });
        },
        
        // Option de police pour dyslexiques
        setupDyslexicFontToggle() {
            const dyslexicFontToggle = document.getElementById('dyslexicFont');
            if (!dyslexicFontToggle) return;
            
            dyslexicFontToggle.addEventListener('change', () => {
                document.body.classList.toggle(config.classes.dyslexicFont, dyslexicFontToggle.checked);
            });
        },
        
        // Option de réduction des animations
        setupReduceMotionToggle() {
            const reduceMotionToggle = document.getElementById('reduceMotion');
            if (!reduceMotionToggle) return;
            
            reduceMotionToggle.addEventListener('change', () => {
                document.body.classList.toggle(config.classes.reduceMotion, reduceMotionToggle.checked);
            });
        },
        
        // Exportation des données
        setupDataExport() {
            const downloadButton = document.querySelector(config.selectors.downloadButton);
            if (!downloadButton) return;
            
            downloadButton.addEventListener('click', (e) => {
                e.preventDefault();
                utils.submitForm('', { export_data: '1' });
            });
        }
    };
    
    // Initialisation du gestionnaire de réglages
    settingsManager.init();
}); 