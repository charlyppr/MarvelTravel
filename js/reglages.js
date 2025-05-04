document.addEventListener('DOMContentLoaded', function () {
    // Animation des notifications
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        notifications.forEach(notification => {
            // Ajouter un bouton de fermeture s'il n'existe pas déjà
            if (!notification.querySelector('.close-notification')) {
                const closeButton = document.createElement('button');
                closeButton.className = 'close-notification';
                closeButton.innerHTML = '&times;';
                notification.appendChild(closeButton);
            }

            // Gérer la fermeture au clic
            notification.querySelector('.close-notification').addEventListener('click', () => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });

            // Fermeture automatique après 5 secondes
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        });
    }

    // Fonction simplifiée pour définir un cookie
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + value + ';path=/;expires=' + expires.toUTCString();
    }

    // Gestion du bouton de réinitialisation
    const resetButton = document.querySelector('.reset-button');
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?')) {
                // Créer un formulaire caché pour soumettre l'action de réinitialisation
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'reset_settings';
                input.value = '1';

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Gestion des options de thème en temps réel (uniquement pour la prévisualisation)
    const themeOptions = document.querySelectorAll('input[name="theme"]');
    themeOptions.forEach(option => {
        option.addEventListener('change', function () {
            let theme = this.value;

            // Ajouter la classe de transition avant de changer le thème
            document.body.classList.add('theme-transition');

            // Nettoyer les classes existantes
            document.body.classList.remove('light-theme', 'dark-theme', 'auto-theme');

            // Si auto, déterminer en fonction des préférences système
            if (theme === 'auto') {
                const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = prefersDarkMode ? 'dark' : 'light';
            }

            // Appliquer la nouvelle classe
            document.body.classList.add(`${theme}-theme`);

            // Mettre à jour la classe 'selected' UNIQUEMENT sur les options de thème
            document.querySelectorAll('.theme-selector .theme-option').forEach(optionEl => {
                optionEl.classList.remove('selected');
            });
            this.closest('.theme-option').classList.add('selected');

            // Supprimer la classe de transition après un délai pour permettre la transition complète
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, 600); // Délai légèrement supérieur à la durée de transition (0.5s)
        });
    });

    // Gestion des autres options d'accessibilité pour la prévisualisation
    const highContrastToggle = document.getElementById('highContrast');
    if (highContrastToggle) {
        highContrastToggle.addEventListener('change', function () {
            document.body.classList.toggle('high-contrast', this.checked);
        });
    }

    const fontSizeOptions = document.querySelectorAll('input[name="fontSize"]');
    fontSizeOptions.forEach(option => {
        option.addEventListener('change', function () {
            // Mise à jour des classes de taille de police sur le body
            document.body.classList.remove('font-size-normal', 'font-size-large', 'font-size-larger');
            document.body.classList.add(`font-size-${this.value}`);

            // Mise à jour visuelle de la sélection active
            document.querySelectorAll('.font-size-selector .theme-option').forEach(optionEl => {
                optionEl.classList.remove('selected');
            });
            this.closest('.theme-option').classList.add('selected');
        });
    });

    const dyslexicFontToggle = document.getElementById('dyslexicFont');
    if (dyslexicFontToggle) {
        dyslexicFontToggle.addEventListener('change', function () {
            document.body.classList.toggle('dyslexic-font', this.checked);
        });
    }

    const reduceMotionToggle = document.getElementById('reduceMotion');
    if (reduceMotionToggle) {
        reduceMotionToggle.addEventListener('change', function () {
            document.body.classList.toggle('reduce-motion', this.checked);
        });
    }

    // Gestion du bouton d'exportation des données
    const downloadButton = document.querySelector('.download-button');
    if (downloadButton) {
        downloadButton.addEventListener('click', function (e) {
            // Empêcher le comportement par défaut
            e.preventDefault();

            // Créer un formulaire dédié pour l'exportation
            const exportForm = document.createElement('form');
            exportForm.method = 'POST';
            exportForm.action = '';

            const exportInput = document.createElement('input');
            exportInput.type = 'hidden';
            exportInput.name = 'export_data';
            exportInput.value = '1';

            exportForm.appendChild(exportInput);
            document.body.appendChild(exportForm);
            exportForm.submit();
        });
    }
}); 