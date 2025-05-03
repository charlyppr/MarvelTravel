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
