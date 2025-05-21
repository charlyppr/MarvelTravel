// Gestion des cookies
if (typeof window.getCookie !== "function") {
  const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    return parts.length === 2 ? parts.pop().split(";").shift() : null;
  };
  window.getCookie = getCookie;
}

if (typeof window.setCookie !== "function") {
  const setCookie = (name, value, days = 365) => {
    const d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
  };
  window.setCookie = setCookie;
}

// Variable globale pour le thème actuel
window.currentTheme = "dark";

// Application du thème
function applyTheme(theme) {
  window.currentTheme = theme;
  document.body.classList.remove("light-theme", "dark-theme", "auto-theme");

  let appliedTheme = theme;
  if (theme === "auto") {
    appliedTheme = window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
    document.body.classList.add("auto-theme");
  }

  document.body.classList.add(`${appliedTheme}-theme`);
}

// Bascule de thème
function toggleTheme(newTheme) {
  document.body.classList.add("theme-transition");
  window.setCookie("theme", newTheme);
  applyTheme(newTheme);

  setTimeout(() => document.body.classList.remove("theme-transition"), 500);
  updateUserThemeInDB(newTheme);
}

// Mise à jour du thème dans la BDD
function updateUserThemeInDB(theme) {
  if (!document.querySelector(".profile-dropdown-container")) return;

  const xhr = new XMLHttpRequest();
  let path;

  const pathSegments = window.location.pathname.split("/");
  const projectIndex = pathSegments.findIndex(
    (segment) => segment === "MarvelTravel"
  );

  if (projectIndex !== -1) {
    path = `${pathSegments
      .slice(0, projectIndex + 1)
      .join("/")}/php/update-theme.php`;
  } else {
    path = window.location.pathname.includes("/php/etapes/")
      ? "../update-theme.php"
      : window.location.pathname.includes("/php/")
      ? "update-theme.php"
      : "php/update-theme.php";
  }

  xhr.open("POST", path, true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = () => xhr.readyState === 4;
  xhr.send("theme=" + theme);
}

// Listener pour les changements de préférences système
window.colorSchemeQuery = window.matchMedia("(prefers-color-scheme: dark)");
window.colorSchemeQuery.addEventListener(
  "change",
  () => window.currentTheme === "auto" && applyTheme("auto")
);

// Application des préférences au chargement
document.addEventListener("DOMContentLoaded", () => {
  const theme = window.getCookie("theme") || "dark";
  applyTheme(theme);

  const highContrast = window.getCookie("highContrast") === "true";
  const fontSize = window.getCookie("fontSize") || "normal";
  const dyslexicFont = window.getCookie("dyslexicFont") === "true";
  const reduceMotion = window.getCookie("reduceMotion") === "true";

  document.body.classList.toggle("high-contrast", highContrast);
  document.body.classList.add(`font-size-${fontSize}`);
  document.body.classList.toggle("dyslexic-font", dyslexicFont);
  document.body.classList.toggle("reduce-motion", reduceMotion);
});

// Expose toggleTheme globally
window.toggleTheme = toggleTheme;
