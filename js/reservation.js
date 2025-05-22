document.addEventListener("DOMContentLoaded", () => {
  // Détecter la page actuelle
  const pathname = window.location.pathname;

  // Fonctions utilitaires
  const formatPrice = (price) => {
    return new Intl.NumberFormat("fr-FR", {
      style: "currency",
      currency: "EUR",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
      .format(price)
      .replace("€", "")
      .trim();
  };

  const extractPrice = (priceString) => {
    return parseFloat(priceString.replace(/[^\d,]/g, "").replace(",", "."));
  };

  // ÉTAPE 1 - Dates et nombre de voyageurs
  if (pathname.includes("etape1.php")) {
    const nbPersonneInput = document.querySelector("#nb_personne");
    const dateDebutInput = document.querySelector("#date-debut");
    const dateFinInput = document.querySelector("#date-fin");
    const dateDebutVisible = document.querySelector("#date-debut-visible");
    const dateFinVisible = document.querySelector("#date-fin-visible");
    const form = document.querySelector("#reservationForm");
    const submitButton = document.querySelector(
      '#reservationForm button[type="submit"]'
    );

    // Mise à jour de l'affichage du prix
    const updatePriceDisplay = () => {
      const prixBaseElement = document.querySelector(
        ".price-row:first-child span:last-child"
      );
      const nbPersonnesDisplay = document.querySelector(
        "#nb_personnes_display"
      );
      const prixTotalElement = document.querySelector("#prix_total");

      if (
        !prixBaseElement ||
        !nbPersonnesDisplay ||
        !prixTotalElement ||
        !nbPersonneInput
      )
        return;

      const nbPersonnes = parseInt(nbPersonneInput.value);
      const prixBase = extractPrice(prixBaseElement.textContent);
      const prixTotal = nbPersonnes * prixBase;

      nbPersonnesDisplay.textContent = nbPersonnes;
      prixTotalElement.textContent = formatPrice(prixTotal) + " €";
    };

    // Validation du formulaire
    const validateForm = () => {
      if (!dateDebutInput || !dateFinInput || !submitButton) return false;

      const isValid = dateDebutInput.value && dateFinInput.value;
      submitButton.disabled = !isValid;
      return isValid;
    };

    // Initialisation des champs de date
    if (dateDebutInput && dateFinInput && dateDebutVisible && dateFinVisible) {
      if (dateDebutInput.value) {
        const date = new Date(dateDebutInput.value);
        dateDebutVisible.value = date.toLocaleDateString("fr-FR", {
          day: "numeric",
          month: "short",
        });
      }

      if (dateFinInput.value) {
        const date = new Date(dateFinInput.value);
        dateFinVisible.value = date.toLocaleDateString("fr-FR", {
          day: "numeric",
          month: "short",
        });
      }

      dateDebutInput.addEventListener("input", validateForm);
      dateFinInput.addEventListener("input", validateForm);
    }

    // Configuration du champ nombre de personnes
    if (nbPersonneInput) {
      nbPersonneInput.addEventListener("input", updatePriceDisplay);
      updatePriceDisplay();
    }

    // Configuration du formulaire
    if (form) {
      validateForm();
      form.addEventListener("submit", (e) => {
        if (!validateForm()) {
          e.preventDefault();
          alert("Veuillez sélectionner des dates d'arrivée et de départ.");
        }
      });
    }

    window.validateEtape1Form = validateForm;
  }

  // ÉTAPE 2 - Informations des voyageurs
  else if (pathname.includes("etape2.php")) {
    const form = document.querySelector("#travelersForm");
    const submitButton = document.getElementById("submit-button");
    const autoFillButton = document.querySelector("#autofill-button");
    const passportInputs = document.querySelectorAll('input[id^="passport_"]');

    // Formatage du numéro de passeport
    const formatPassport = (input) => {
      let value = input.value.replace(/[^\d]/g, "").slice(0, 10);
      let formattedValue = "";

      for (let i = 0; i < value.length; i++) {
        if (i === 3 || i === 6 || i === 9) formattedValue += " ";
        formattedValue += value[i];
      }

      input.value = formattedValue;
    };

    // Validation d'un champ de passeport
    const validatePassport = (input) => {
      const errorElement = document.getElementById(input.id + "_error");
      if (!errorElement) return;

      const formField = input.closest(".form-field");
      const digitsOnly = input.value.replace(/[^\d]/g, "");

      if (digitsOnly.length === 0) {
        errorElement.style.opacity = "0";
        formField.classList.remove("invalid");
      } else if (digitsOnly.length !== 10) {
        errorElement.style.opacity = "1";
        formField.classList.add("invalid");
      } else {
        errorElement.style.opacity = "0";
        formField.classList.remove("invalid");
      }
    };

    // Validation du formulaire
    const validateForm = () => {
      if (!form || !submitButton) return;

      let isValid = true;

      const requiredInputs = form.querySelectorAll(
        "input[required], select[required]"
      );
      requiredInputs.forEach((input) => {
        if (!input.value.trim()) {
          isValid = false;
        }

        if (input.id.startsWith("passport_")) {
          const digitsOnly = input.value.replace(/[^\d]/g, "");
          if (digitsOnly.length !== 10) {
            isValid = false;
          }
        }
      });

      if (form.querySelectorAll(".form-field.invalid").length > 0) {
        isValid = false;
      }

      submitButton.disabled = !isValid;
    };

    // Auto-remplissage des informations du voyageur principal
    if (autoFillButton) {
      autoFillButton.addEventListener("click", () => {
        const userData = {
          lastName: autoFillButton.getAttribute("data-lastname"),
          firstName: autoFillButton.getAttribute("data-firstname"),
          civilite: autoFillButton.getAttribute("data-civilite"),
          dateNaissance: autoFillButton.getAttribute("data-birthdate"),
          nationalite: autoFillButton.getAttribute("data-nationality"),
          passport: autoFillButton.getAttribute("data-passport"),
        };

        const fields = {
          nom: document.getElementById("nom_1"),
          prenom: document.getElementById("prenom_1"),
          civilite: document.getElementById("civilite_1"),
          dateNaissance: document.getElementById("date_naissance_1"),
          nationalite: document.getElementById("nationalite_1"),
          passport: document.getElementById("passport_1"),
        };

        if (fields.nom) fields.nom.value = userData.lastName;
        if (fields.prenom) fields.prenom.value = userData.firstName;
        if (fields.civilite && userData.civilite)
          fields.civilite.value = userData.civilite;
        if (fields.dateNaissance && userData.dateNaissance)
          fields.dateNaissance.value = userData.dateNaissance;
        if (fields.nationalite && userData.nationalite)
          fields.nationalite.value = userData.nationalite;

        if (fields.passport && userData.passport) {
          fields.passport.value = userData.passport;
          formatPassport(fields.passport);
          validatePassport(fields.passport);
        }

        validateForm();
      });
    }

    // Configuration des champs de passeport
    if (passportInputs.length) {
      passportInputs.forEach((input) => {
        if (input.value) formatPassport(input);

        input.addEventListener("input", () => {
          formatPassport(input);
          validatePassport(input);
          validateForm();
        });

        input.addEventListener("keydown", (e) => {
          if (
            e.key === "Backspace" ||
            e.key === "Delete" ||
            e.key === "ArrowLeft" ||
            e.key === "ArrowRight" ||
            e.key === "Tab" ||
            (e.key >= "0" && e.key <= "9")
          ) {
            return true;
          }

          e.preventDefault();
          return false;
        });
      });
    }

    // Configuration de la validation du formulaire
    if (form) {
      form.addEventListener("input", (e) => {
        if (
          e.target.hasAttribute("required") ||
          e.target.id.startsWith("passport_")
        ) {
          validateForm();
        }
      });

      form.addEventListener("change", (e) => {
        if (e.target.hasAttribute("required")) validateForm();
      });

      validateForm();
    }
  }

  // ÉTAPE 3 - Sélection des options
  else if (pathname.includes("etape3.php")) {
    const optionsForm = document.querySelector("#optionsForm");

    if (optionsForm) {
      const priceElements = {
        options: document.querySelector(".options-row span:last-child"),
        total: document.querySelector(".price-total span:last-child"),
        base: document.querySelector(".price-row:first-child span:last-child"),
      };

      if (!priceElements.base) return;

      const prixBase = extractPrice(priceElements.base.textContent);

      // Calcul du prix initial des options
      const calculateOptionsPrice = () => {
        if (!priceElements.options || !priceElements.total) return;

        const allCheckboxes = document.querySelectorAll(
          ".participant-checkbox:checked"
        );
        let prixOptions = 0;

        allCheckboxes.forEach((box) => {
          const optionItem = box.closest(".option-item");
          if (!optionItem) return;

          const priceText =
            optionItem.querySelector(".option-price")?.textContent;
          if (!priceText) return;

          const price = extractPrice(priceText);
          prixOptions += price;
        });

        priceElements.options.textContent = formatPrice(prixOptions) + " €";
        priceElements.total.textContent =
          formatPrice(prixBase + prixOptions) + " €";

        // Animation
        priceElements.options.classList.add("price-updated");
        priceElements.total.classList.add("price-updated");

        setTimeout(() => {
          priceElements.options.classList.remove("price-updated");
          priceElements.total.classList.remove("price-updated");
        }, 700);
      };

      // Calcul du prix initial
      calculateOptionsPrice();

      // Gestion des toggles
      optionsForm.addEventListener("click", (e) => {
        const toggle = e.target.closest(".participant-toggle");

        if (toggle) {
          const checkbox = toggle.querySelector('input[type="checkbox"]');
          if (!checkbox) return;

          if (e.target.tagName.toLowerCase() !== "input") {
            checkbox.checked = !checkbox.checked;
            e.preventDefault();
          }

          toggle.classList.toggle("selected", checkbox.checked);
          calculateOptionsPrice();
        }
      });
    }
  }

  window.updatePriceDisplay =
    typeof updatePriceDisplay !== "undefined" ? updatePriceDisplay : null;
});
