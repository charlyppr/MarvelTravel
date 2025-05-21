document.addEventListener("DOMContentLoaded", () => {
  // Sélecteurs et expressions régulières essentiels
  const EMAIL_REGEX = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  // Sélectionner tous les formulaires
  document.querySelectorAll("form").forEach((form) => {
    const emailInput = form.querySelector('input[type="email"]');
    const passwordInput = form.querySelector('input[type="password"]');
    const submitButton = form.querySelector(
      'button[type="submit"], .next-button'
    );

    // Désactiver le bouton par défaut
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.classList.add("disabled-button");
    }

    // Validation d'email
    if (emailInput) {
      ["input", "blur"].forEach((event) =>
        emailInput.addEventListener(event, () => {
          const isValid = validateEmail(emailInput);
          updateFormValidity(form, emailInput, isValid);
        })
      );
    }

    // Validation de mot de passe
    if (passwordInput) {
      ["input", "blur"].forEach((event) =>
        passwordInput.addEventListener(event, () => {
          const isValid = validatePassword(passwordInput);
          updateFormValidity(form, passwordInput, isValid);
        })
      );
    }

    // Gestion de soumission du formulaire
    form.addEventListener("submit", (event) => {
      let isFormValid = true;

      if (emailInput && !validateEmail(emailInput)) isFormValid = false;
      if (passwordInput && !validatePassword(passwordInput))
        isFormValid = false;

      if (!isFormValid) {
        event.preventDefault();
        const firstError = form.querySelector(
          '.input-warning[style*="display: block"]'
        );
        if (firstError)
          firstError.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    });
  });

  // Fonction de validation d'email
  function validateEmail(input) {
    const value = input.value.trim();
    const container = getContainer(input);

    if (!value) {
      updateFieldStatus(input, "");
      return false;
    }

    if (!EMAIL_REGEX.test(value)) {
      updateFieldStatus(input, "Adresse email invalide");
      return false;
    }

    updateFieldStatus(input, "");
    container.classList.remove("input-invalid");
    container.classList.add("input-valid");
    return true;
  }

  // Fonction de validation de mot de passe
  function validatePassword(input) {
    const value = input.value;
    const container = getContainer(input);

    if (!value) {
      updateFieldStatus(input, "");
      hideStrengthMeter(input);
      return false;
    }

    const criteria = {
      length: value.length >= 8,
      lowercase: /[a-z]/.test(value),
      uppercase: /[A-Z]/.test(value),
      number: /[0-9]/.test(value),
      special: /[^A-Za-z0-9]/.test(value),
    };

    const passedCriteria = Object.values(criteria).filter(Boolean).length;
    updateStrengthMeter(input, passedCriteria);

    if (passedCriteria < 5) {
      let message = "Le mot de passe doit contenir:";
      if (!criteria.length) message += "<br>• Au moins 8 caractères";
      if (!criteria.lowercase) message += "<br>• Au moins 1 minuscule";
      if (!criteria.uppercase) message += "<br>• Au moins 1 majuscule";
      if (!criteria.number) message += "<br>• Au moins 1 chiffre";
      if (!criteria.special) message += "<br>• Au moins 1 caractère spécial";

      updateFieldStatus(input, message);
      return false;
    }

    updateFieldStatus(input, "");
    container.classList.remove("input-invalid");
    container.classList.add("input-valid");
    return true;
  }

  // Fonctions utilitaires
  function getContainer(input) {
    const selectors = [
      ".mdp",
      ".email",
      ".field-value",
      ".profile-field",
      ".form-row",
      ".civilite-container",
    ];
    for (const selector of selectors) {
      const container = input.closest(selector);
      if (container) return container;
    }
    return input.parentElement;
  }

  function updateFieldStatus(input, errorMessage) {
    const container = getContainer(input);
    let errorContainer = container.nextElementSibling;

    while (
      errorContainer &&
      !errorContainer.classList.contains("input-warning")
    ) {
      errorContainer = errorContainer.nextElementSibling;
    }

    if (errorMessage) {
      if (!errorContainer) {
        errorContainer = document.createElement("div");
        errorContainer.className = "input-warning";
        container.after(errorContainer);
      }
      errorContainer.innerHTML = errorMessage;
      errorContainer.style.display = "block";
      container.classList.remove("input-valid");
      container.classList.add("input-invalid");
    } else if (errorContainer) {
      errorContainer.style.display = "none";
    }
  }

  function updateFormValidity(form, input, isValid) {
    const submitButton = form.querySelector(
      'button[type="submit"], .next-button'
    );
    if (!submitButton) return;

    const emailInput = form.querySelector('input[type="email"]');
    const passwordInput = form.querySelector('input[type="password"]');

    let formValid = true;
    if (emailInput && (emailInput.required || emailInput.value))
      formValid = formValid && validateEmail(emailInput);
    if (passwordInput && (passwordInput.required || passwordInput.value))
      formValid = formValid && validatePassword(passwordInput);

    submitButton.disabled = !formValid;
    submitButton.classList.toggle("disabled-button", !formValid);
  }

  function updateStrengthMeter(input, strength) {
    const container = getContainer(input);
    let meter = container.nextElementSibling;

    while (meter && !meter.classList.contains("password-strength-meter")) {
      meter = meter.nextElementSibling;
    }

    if (!meter) {
      meter = document.createElement("div");
      meter.className = "password-strength-meter";
      const indicator = document.createElement("div");
      indicator.className = "strength-indicator";
      meter.appendChild(indicator);
      container.after(meter);
    }

    meter.className = "password-strength-meter";
    if (strength <= 1) {
      meter.classList.add("strength-weak");
    } else if (strength === 2) {
      meter.classList.add("strength-fair");
    } else if (strength <= 4) {
      meter.classList.add("strength-good");
    } else {
      meter.classList.add("strength-strong");
    }

    meter.style.display = "block";
  }

  function hideStrengthMeter(input) {
    const container = getContainer(input);
    let meter = container.nextElementSibling;

    while (meter && !meter.classList.contains("password-strength-meter")) {
      meter = meter.nextElementSibling;
    }

    if (meter) meter.style.display = "none";
  }
});
