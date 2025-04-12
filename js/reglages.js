document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner les éléments du DOM
    const themeOptions = document.querySelectorAll('.theme-option');
    const sizeOptions = document.querySelectorAll('.size-option');
    const resetButton = document.querySelector('.reset-button');
    const downloadButton = document.querySelector('.download-button');
    const form = document.querySelector('.settings-form');
    const toggleInputs = document.querySelectorAll('.toggle-input');
    
    // Fonction pour définir un cookie
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
    
    // Fonction pour récupérer un cookie
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    // Fonction pour supprimer un cookie
    function eraseCookie(name) {
        document.cookie = name + '=; Max-Age=-99999999; path=/';
    }
    
    // Fonction pour charger le CSS du thème
    function loadThemeCSS(theme) {
        const head = document.head;
        const currentThemeLink = document.getElementById('theme-css');
        
        // Si un lien pour le thème existe déjà, on le supprime
        if (currentThemeLink) {
            head.removeChild(currentThemeLink);
        }
        
        // Si le thème est "light", on ajoute le CSS du thème clair
        if (theme === 'light') {
            const linkElement = document.createElement('link');
            linkElement.id = 'theme-css';
            linkElement.rel = 'stylesheet';
            linkElement.href = '../css/root-light.css';
            head.appendChild(linkElement);
        }
        // Pour "dark" ou "auto", on utilise le CSS par défaut (sombre)
        
        // Sauvegarder le choix dans un cookie (expire après 365 jours)
        setCookie('marvelTravel_theme', theme, 365);
    }
    
    // Ajouter la classe 'selected' aux options sélectionnées et gérer les changements
    themeOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        
        option.addEventListener('click', function() {
            // Désélectionner toutes les options
            themeOptions.forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Sélectionner l'option cliquée
            option.classList.add('selected');
            radio.checked = true;
            
            // Appliquer le thème
            loadThemeCSS(radio.value);
        });
    });
    
    sizeOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        
        option.addEventListener('click', function() {
            // Désélectionner toutes les options
            sizeOptions.forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Sélectionner l'option cliquée
            option.classList.add('selected');
            radio.checked = true;
            
            // Sauvegarder la taille du texte
            setCookie('marvelTravel_fontSize', radio.value, 365);
            
            // Appliquer la taille du texte
            applyFontSize(radio.value);
        });
    });
    
    // Gérer les toggles pour les fonctionnalités d'accessibilité
    toggleInputs.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const settingName = toggle.id;
            const isEnabled = toggle.checked;
            
            // Sauvegarder le paramètre
            setCookie('marvelTravel_' + settingName, isEnabled, 365);
            
            // Appliquer le paramètre
            applyAccessibilitySetting(settingName, isEnabled);
        });
    });
    
    // Réinitialiser les paramètres
    resetButton.addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir rétablir tous les paramètres par défaut?')) {
            resetAllSettings();
        }
    });
    
    // Télécharger les données
    downloadButton.addEventListener('click', function() {
        downloadUserData();
    });
    
    // Fonction pour appliquer la taille de police
    function applyFontSize(size) {
        document.body.classList.remove('large-text', 'larger-text');
        
        if (size === 'large') {
            document.body.classList.add('large-text');
        } else if (size === 'larger') {
            document.body.classList.add('larger-text');
        }
    }
    
    // Fonction pour appliquer un paramètre d'accessibilité
    function applyAccessibilitySetting(setting, isEnabled) {
        switch(setting) {
            case 'highContrast':
                document.body.classList.toggle('high-contrast', isEnabled);
                break;
            case 'dyslexicFont':
                document.body.classList.toggle('dyslexic-font', isEnabled);
                break;
            case 'reduceMotion':
                document.body.classList.toggle('reduce-motion', isEnabled);
                break;
        }
    }
    
    // Fonction pour réinitialiser tous les paramètres
    function resetAllSettings() {
        // Réinitialiser le thème
        document.querySelector('input[name="theme"][value="dark"]').checked = true;
        themeOptions.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.querySelector('input').value === 'dark') {
                opt.classList.add('selected');
            }
        });
        loadThemeCSS('dark');
        
        // Réinitialiser la taille du texte
        document.querySelector('input[name="fontSize"][value="normal"]').checked = true;
        sizeOptions.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.querySelector('input').value === 'normal') {
                opt.classList.add('selected');
            }
        });
        applyFontSize('normal');
        
        // Réinitialiser les toggles
        toggleInputs.forEach(toggle => {
            toggle.checked = false;
            applyAccessibilitySetting(toggle.id, false);
        });
        
        // Effacer les cookies
        eraseCookie('marvelTravel_theme');
        eraseCookie('marvelTravel_fontSize');
        eraseCookie('marvelTravel_highContrast');
        eraseCookie('marvelTravel_dyslexicFont');
        eraseCookie('marvelTravel_reduceMotion');
        
        // Supprimer la feuille de style du thème clair s'il existe
        const currentThemeLink = document.getElementById('theme-css');
        if (currentThemeLink) {
            document.head.removeChild(currentThemeLink);
        }
        
        // Afficher confirmation
        displayNotification('Les paramètres ont été réinitialisés.', 'success');
    }
    
    function downloadUserData() {
        // Collecter les données utilisateur
        const userData = {
            preferences: {
                theme: document.querySelector('input[name="theme"]:checked').value,
                fontSize: document.querySelector('input[name="fontSize"]:checked').value,
                highContrast: document.getElementById('highContrast').checked,
                dyslexicFont: document.getElementById('dyslexicFont').checked,
                reduceMotion: document.getElementById('reduceMotion').checked
            },
            userInfo: {
                timestamp: new Date().toISOString()
            }
        };
        
        // Convertir en JSON
        const jsonData = JSON.stringify(userData, null, 2);
        
        // Créer un blob et un lien de téléchargement
        const blob = new Blob([jsonData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'marveltravel_settings.json';
        document.body.appendChild(a);
        a.click();
        
        // Nettoyer
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
        
        displayNotification('Vos données ont été téléchargées avec succès.', 'success');
    }
    
    function displayNotification(message, type) {
        // Vérifier si une notification existe déjà
        let notification = document.querySelector('.notification');
        
        // Si non, créer une nouvelle
        if (!notification) {
            notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            const img = document.createElement('img');
            img.src = `../img/svg/${type === 'success' ? 'check-circle.svg' : 'alert-circle.svg'}`;
            img.alt = type === 'success' ? 'Succès' : 'Erreur';
            
            const span = document.createElement('span');
            span.textContent = message;
            
            notification.appendChild(img);
            notification.appendChild(span);
            
            // Insérer avant le formulaire
            const settingsHeader = document.querySelector('.settings-header');
            settingsHeader.insertAdjacentElement('afterend', notification);
            
            // Supprimer après 5 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
        // Si oui, mettre à jour
        else {
            const span = notification.querySelector('span');
            span.textContent = message;
            notification.className = `notification ${type}`;
            const img = notification.querySelector('img');
            img.src = `../img/svg/${type === 'success' ? 'check-circle.svg' : 'alert-circle.svg'}`;
        }
    }
    
    // Si la page a une notification de succès, on la fait disparaître après 5 secondes
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        setTimeout(() => {
            if (existingNotification.parentNode) {
                existingNotification.parentNode.removeChild(existingNotification);
            }
        }, 5000);
    }
    
    // Intercepter le formulaire de sauvegarde
    form.addEventListener('submit', function(e) {
        // On ne bloque pas l'envoi du formulaire, il sera traité côté serveur
        // Les cookies sont déjà mis à jour lors des changements d'options
    });
});