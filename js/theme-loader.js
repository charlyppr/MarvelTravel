// Fonction pour obtenir la valeur d'un cookie
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}

// Fonction pour définir un cookie
function setCookie(name, value, days = 365) {
  const d = new Date();
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
  const expires = `expires=${d.toUTCString()}`;
  document.cookie = `${name}=${value};${expires};path=/`;
}

// Variable globale pour stocker le thème actuel
window.currentTheme = "dark";


// Fonction pour appliquer le thème - rendue globale
function applyTheme(theme) {
  // Sauvegarder le thème choisi par l'utilisateur
  window.currentTheme = theme;

  // Supprimer toutes les classes de thème
  document.body.classList.remove("light-theme", "dark-theme", "auto-theme");

  // Si le thème est 'auto', déterminer le thème en fonction des préférences du système
  let appliedTheme = theme;
  if (theme === "auto") {
    const prefersDarkMode = window.matchMedia(
      "(prefers-color-scheme: dark)"
    ).matches;
    appliedTheme = prefersDarkMode ? "dark" : "light";
    // Ajouter également la classe auto-theme pour indiquer que c'est le mode auto qui est actif
    document.body.classList.add("auto-theme");
  }

  // Appliquer la classe du thème au body
  document.body.classList.add(`${appliedTheme}-theme`);
}

// Fonction pour basculer le thème
function toggleTheme(newTheme) {
  // Ajouter la classe de transition pour une animation fluide
  document.body.classList.add("theme-transition");
  
  // Sauvegarder le thème dans un cookie
  setCookie("theme", newTheme);

  // Appliquer le thème immédiatement
  applyTheme(newTheme);
  
  // Supprimer la classe de transition après la fin de l'animation
  setTimeout(() => {
    document.body.classList.remove("theme-transition");
  }, 500); // Durée légèrement supérieure à la transition CSS (0.5s)
  
  // Mettre à jour le thème dans la base de données utilisateur
  updateUserThemeInDB(newTheme);
}

// Fonction pour mettre à jour le thème dans la base de données
function updateUserThemeInDB(theme) {
  // Vérifier si l'utilisateur est connecté (on peut détecter cela en cherchant des éléments spécifiques dans la page)
  if (document.querySelector('.profile-dropdown-container')) {
    // L'utilisateur est probablement connecté, essayons de mettre à jour son thème
    const xhr = new XMLHttpRequest();
    
    // Déterminer le chemin relatif en fonction de l'URL actuelle
    let path;
    
    // Récupérer le chemin de base du projet
    const pathSegments = window.location.pathname.split('/');
    const projectIndex = pathSegments.findIndex(segment => segment === 'MarvelTravel');
    
    if (projectIndex !== -1) {
      // Construire le chemin de base
      const basePath = pathSegments.slice(0, projectIndex + 1).join('/');
      path = `${basePath}/php/update-theme.php`;
    } else {
      // Fallback: essayer de déterminer le chemin relatif en fonction de la structure actuelle
      if (window.location.pathname.includes('/php/etapes/')) {
        path = '../update-theme.php';
      } else if (window.location.pathname.includes('/php/')) {
        path = 'update-theme.php';
      } else {
        path = 'php/update-theme.php';
      }
    }
    
    xhr.open('POST', path, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        console.log('Theme update response:', xhr.responseText);
      }
    };
    xhr.send('theme=' + theme);
  }
}

// Listener pour les changements de préférences système
window.colorSchemeQuery = window.matchMedia("(prefers-color-scheme: dark)");
window.colorSchemeQuery.addEventListener("change", (e) => {
  // Ne mettre à jour que si le mode auto est activé
  if (window.currentTheme === "auto") {
    applyTheme("auto");
  }
});

// Au chargement du document, appliquer le thème et les préférences d'accessibilité
document.addEventListener("DOMContentLoaded", function () {
  // Récupérer le thème depuis le cookie
  const theme = getCookie("theme") || "dark";

  // Appliquer le thème
  applyTheme(theme);

  // Récupérer et appliquer les autres préférences d'accessibilité
  const highContrast = getCookie("highContrast") === "true";
  const fontSize = getCookie("fontSize") || "normal";
  const dyslexicFont = getCookie("dyslexicFont") === "true";
  const reduceMotion = getCookie("reduceMotion") === "true";

  // Appliquer les classes d'accessibilité
  document.body.classList.toggle("high-contrast", highContrast);
  document.body.classList.add(`font-size-${fontSize}`);
  document.body.classList.toggle("dyslexic-font", dyslexicFont);
  document.body.classList.toggle("reduce-motion", reduceMotion);
});
