document.addEventListener("DOMContentLoaded", () => {
  // Sélecteurs essentiels
  const notifications = document.querySelectorAll(".notification");
  const resetButton = document.querySelector(".reset-button");
  const themeOptions = document.querySelectorAll('input[name="theme"]');
  const themeSelectors = document.querySelectorAll(
    ".theme-selector .theme-option"
  );
  const highContrastToggle = document.getElementById("highContrast");
  const fontSizeOptions = document.querySelectorAll('input[name="fontSize"]');
  const fontSizeSelectors = document.querySelectorAll(
    ".font-size-selector .theme-option"
  );
  const dyslexicFontToggle = document.getElementById("dyslexicFont");
  const reduceMotionToggle = document.getElementById("reduceMotion");
  const downloadButton = document.querySelector(".download-button");

  // Gestion des notifications
  if (notifications.length) {
    notifications.forEach((notification) => {
      if (!notification.querySelector(".close-notification")) {
        const closeButton = document.createElement("button");
        closeButton.className = "close-notification";
        closeButton.innerHTML = "&times;";
        notification.appendChild(closeButton);
      }

      notification
        .querySelector(".close-notification")
        .addEventListener("click", () => {
          notification.style.opacity = "0";
          setTimeout(() => notification.remove(), 300);
        });

      setTimeout(() => {
        notification.style.opacity = "0";
        setTimeout(() => notification.remove(), 300);
      }, 5000);
    });
  }

  // Bouton de réinitialisation
  if (resetButton) {
    resetButton.addEventListener("click", () => {
      if (
        confirm("Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?")
      ) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "";

        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "reset_settings";
        input.value = "1";
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
      }
    });
  }

  // Options de thème
  if (themeOptions.length) {
    // Sélection initiale
    const currentTheme =
      document.cookie.replace(
        /(?:(?:^|.*;\s*)theme\s*\=\s*([^;]*).*$)|^.*$/,
        "$1"
      ) || "dark";
    themeSelectors.forEach((el) => {
      el.classList.remove("selected");
      const radioInput = el.querySelector('input[type="radio"]');
      if (radioInput && radioInput.value === currentTheme) {
        el.classList.add("selected");
      }
    });

    themeOptions.forEach((option) => {
      option.addEventListener("change", () => {
        applyTheme(option.value);

        themeSelectors.forEach((el) => el.classList.remove("selected"));
        option.closest(".theme-option").classList.add("selected");
      });
    });
  }

  // Application d'un thème
  function applyTheme(theme) {
    document.body.classList.add("theme-transition");
    document.body.classList.remove("light-theme", "dark-theme", "auto-theme");

    let effectiveTheme = theme;
    if (theme === "auto") {
      effectiveTheme = window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
    }

    document.body.classList.add(`${effectiveTheme}-theme`);

    // Enregistrement du thème
    const expires = new Date();
    expires.setTime(expires.getTime() + 365 * 24 * 60 * 60 * 1000);
    document.cookie = `theme=${theme};path=/;expires=${expires.toUTCString()}`;

    setTimeout(() => document.body.classList.remove("theme-transition"), 600);

    if (typeof updateUserThemeInDB === "function") {
      updateUserThemeInDB(theme);
    }
  }

  // Contraste élevé
  if (highContrastToggle) {
    highContrastToggle.addEventListener("change", () => {
      document.body.classList.toggle(
        "high-contrast",
        highContrastToggle.checked
      );
    });
  }

  // Taille de police
  if (fontSizeOptions.length) {
    fontSizeOptions.forEach((option) => {
      option.addEventListener("change", () => {
        document.body.classList.remove(
          "font-size-normal",
          "font-size-large",
          "font-size-larger"
        );
        document.body.classList.add(`font-size-${option.value}`);

        fontSizeSelectors.forEach((el) => el.classList.remove("selected"));
        option.closest(".theme-option").classList.add("selected");
      });
    });
  }

  // Police pour dyslexiques
  if (dyslexicFontToggle) {
    dyslexicFontToggle.addEventListener("change", () => {
      document.body.classList.toggle(
        "dyslexic-font",
        dyslexicFontToggle.checked
      );
    });
  }

  // Réduction des animations
  if (reduceMotionToggle) {
    reduceMotionToggle.addEventListener("change", () => {
      document.body.classList.toggle(
        "reduce-motion",
        reduceMotionToggle.checked
      );
    });
  }

  // Exportation des données
  if (downloadButton) {
    downloadButton.addEventListener("click", (e) => {
      e.preventDefault();

      const form = document.createElement("form");
      form.method = "POST";
      form.action = "";

      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "export_data";
      input.value = "1";
      form.appendChild(input);

      document.body.appendChild(form);
      form.submit();
    });
  }
});
