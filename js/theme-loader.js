document.addEventListener("DOMContentLoaded", function () {
  // Fonction pour obtenir la valeur d'un cookie
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  }

  // Fonction pour appliquer le thème
  function applyTheme(theme) {
    // Supprimer toutes les classes de thème
    document.body.classList.remove("light-theme", "dark-theme", "auto-theme");

    // Si le thème est 'auto', déterminer le thème en fonction des préférences du système
    if (theme === "auto") {
      const prefersDarkMode = window.matchMedia(
        "(prefers-color-scheme: dark)"
      ).matches;
      theme = prefersDarkMode ? "dark" : "light";
    }

    // Appliquer la classe du thème au body
    document.body.classList.add(`${theme}-theme`);
  }

  // Récupérer le thème depuis le cookie
  const theme = getCookie("theme") || "dark";

  // Appliquer le thème
  applyTheme(theme);

  // Écouter les changements de préférences du système si le thème est 'auto'
  if (theme === "auto") {
    window
      .matchMedia("(prefers-color-scheme: dark)")
      .addEventListener("change", (e) => {
        applyTheme("auto");
      });
  }

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
