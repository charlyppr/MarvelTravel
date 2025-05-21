document.addEventListener("DOMContentLoaded", () => {
  // Variables d'état minimales
  const validated = new Set();
  const editingFields = new Set();
  let submitting = false;
  let loadingStartTime = 0;

  // Éléments DOM principaux
  const profileForm = document.querySelector("#profileForm");
  const submitBtn = document.querySelector("#submit-profile-btn");
  const cancelAllBtn = document.querySelector("#cancel-all-btn");
  const passwordToggle = document.querySelector("#password-toggle");
  const passwordInput = document.getElementById("password");
  const toggleIcon = document.querySelector("#toggle-icon");
  const inputs = document.querySelectorAll(".profile-input");
  const messageItems = document.querySelectorAll(".message-item");
  const messageModal = document.querySelector("#messageModal");

  // Expressions régulières et critères de validation
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  const passwordPatterns = [
    /.{8,}/, // longueur minimale
    /[a-z]/, // minuscule
    /[A-Z]/, // majuscule
    /[0-9]/, // chiffre
    /[^A-Za-z0-9]/, // caractère spécial
  ];

  // Désactiver tous les champs au démarrage
  inputs.forEach((input) => {
    if (!editingFields.has(input.id)) input.disabled = true;
  });

  // Configuration bouton de soumission
  if (submitBtn) {
    setTimeout(() => {
      submitBtn.classList.remove("disabled-button");
      submitBtn.disabled = false;
    }, 100);

    submitBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (!submitting) submitForm();
    });
  }

  // Configuration notifications
  document.querySelectorAll(".close-notification").forEach((button) => {
    button.addEventListener("click", () => {
      const notification = button.closest(".notification");
      if (notification) fadeOut(notification);
    });
  });

  setTimeout(() => {
    document.querySelectorAll(".notification").forEach(fadeOut);
  }, 5000);

  // Configuration boutons d'édition
  document.querySelectorAll(".field-edit").forEach((btn) => {
    btn.addEventListener("click", () => {
      const field = btn.dataset.field;
      const input = document.getElementById(field);
      const validateBtn = document.querySelector(
        `.field-validate[data-field="${field}"]`
      );
      const cancelBtn = document.querySelector(
        `.field-cancel[data-field="${field}"]`
      );

      if (!input || !validateBtn || !cancelBtn) return;

      if (!editingFields.has(field)) {
        input.setAttribute("data-original-value", input.value);
        editingFields.add(field);
      }

      input.disabled = false;
      input.focus();
      toggleEditingClass(field, true);

      btn.style.display = "none";
      validateBtn.style.display = "inline-flex";
      cancelBtn.style.display = "inline-flex";

      if (field === "password" && passwordToggle) {
        passwordToggle.style.display = "flex";
      }

      validateField(field);
      updateActionButtons();
    });
  });

  // Validation des champs
  inputs.forEach((input) => {
    const debounce = (fn, delay) => {
      let timeout;
      return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), delay);
      };
    };

    const debouncedValidate = debounce(() => {
      validateField(input.id);
    }, 200);

    input.addEventListener("input", debouncedValidate);

    // Effet de focus
    input.addEventListener("focus", () => {
      const wrapper = input.closest(".input-wrapper");
      if (wrapper) wrapper.classList.add("focused");
    });

    input.addEventListener("blur", () => {
      const wrapper = input.closest(".input-wrapper");
      if (wrapper) wrapper.classList.remove("focused");
    });
  });

  // Toggle mot de passe
  if (passwordToggle && passwordInput && toggleIcon) {
    passwordToggle.addEventListener("click", () => {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      toggleIcon.src =
        type === "password" ? "../img/svg/eye.svg" : "../img/svg/eye-slash.svg";
      toggleIcon.alt = type === "password" ? "Afficher" : "Masquer";
    });
  }

  // Soumission du formulaire
  if (profileForm) {
    profileForm.addEventListener("submit", (e) => {
      e.preventDefault();
      e.stopPropagation();
      submitForm();
    });
  }

  // Configuration du modal de message
  if (messageItems.length && messageModal) {
    messageItems.forEach((item) => {
      item.addEventListener("click", () => openMessageModal(item));
    });

    document
      .querySelector(".close-message-modal")
      ?.addEventListener("click", (e) => {
        e.preventDefault();
        closeMessageModal();
      });

    messageModal.addEventListener("click", (e) => {
      if (e.target === messageModal) closeMessageModal();
    });

    document.addEventListener("keydown", (e) => {
      if (
        e.key === "Escape" &&
        messageModal.classList.contains("message-modal-active")
      ) {
        closeMessageModal();
      }
    });
  }

  // Validation et gestion des boutons d'action
  document.addEventListener("click", (e) => {
    // Bouton de validation
    const validateBtn = e.target.closest(".field-validate");
    if (validateBtn && !validateBtn.disabled) {
      const field = validateBtn.dataset.field;
      const input = document.getElementById(field);
      if (!input || !validateField(field)) return;

      const originalValue = input.getAttribute("data-original-value");
      const hasChanged = originalValue !== input.value;

      input.disabled = true;
      toggleEditingClass(field, false);

      validateBtn.style.display = "none";
      document.querySelector(
        `.field-cancel[data-field="${field}"]`
      ).style.display = "none";
      document.querySelector(
        `.field-edit[data-field="${field}"]`
      ).style.display = "inline-flex";

      if (field === "password" && passwordToggle) {
        passwordToggle.style.display = "none";
        passwordInput.setAttribute("type", "password");
        if (toggleIcon) toggleIcon.src = "../img/svg/eye.svg";
      }

      editingFields.delete(field);
      if (hasChanged) validated.add(field);
      else validated.delete(field);

      updateActionButtons();
    }

    // Bouton d'annulation
    const cancelBtn = e.target.closest(".field-cancel");
    if (cancelBtn) {
      const field = cancelBtn.dataset.field;
      const input = document.getElementById(field);
      if (!input) return;

      input.value = input.getAttribute("data-original-value") || "";
      input.disabled = true;
      toggleEditingClass(field, false);
      cleanupValidationUI(input);

      cancelBtn.style.display = "none";
      document.querySelector(
        `.field-validate[data-field="${field}"]`
      ).style.display = "none";
      document.querySelector(
        `.field-edit[data-field="${field}"]`
      ).style.display = "inline-flex";

      editingFields.delete(field);
      validated.delete(field);

      if (field === "password" && passwordToggle) {
        passwordToggle.style.display = "none";
        passwordInput.setAttribute("type", "password");
        if (toggleIcon) toggleIcon.src = "../img/svg/eye.svg";
      }

      setTimeout(updateActionButtons, 50);
    }
  });

  // Annulation de toutes les modifications
  if (cancelAllBtn) {
    cancelAllBtn.addEventListener("click", resetAllFields);
  }

  // Fonctions essentielles
  function validateField(field) {
    const input = document.getElementById(field);
    const validateBtn = document.querySelector(
      `.field-validate[data-field="${field}"]`
    );
    if (!input || !validateBtn) return false;

    let isValid = true;

    if (field === "email") {
      isValid = emailRegex.test(input.value.trim());
    } else if (field === "password") {
      if (input.value === "") {
        isValid = false;
      } else {
        isValid = passwordPatterns.every((pattern) =>
          pattern.test(input.value)
        );
      }
    } else {
      isValid = input.value.trim() !== "";
    }

    validateBtn.classList.toggle("disabled-button", !isValid);
    validateBtn.disabled = !isValid;

    return isValid;
  }

  function submitForm() {
    if (submitting) return;

    // Vérifier que tous les champs en édition sont valides
    let allValid = true;
    editingFields.forEach((field) => {
      if (!validateField(field)) allValid = false;
    });

    if (!allValid || !profileForm) return;

    submitting = true;

    // Activer tous les champs pour l'envoi
    inputs.forEach((input) => (input.disabled = false));

    const formData = new FormData(profileForm);
    showLoading();

    fetch("../php/update-profile.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        const pendingNotification = {
          type: data.success ? "success" : "error",
          message:
            data.message ||
            (data.success
              ? "Vos informations ont été mises à jour avec succès."
              : "Une erreur est survenue lors de la mise à jour."),
          success: data.success,
        };

        inputs.forEach((input) => {
          if (!editingFields.has(input.id)) input.disabled = true;
        });

        hideLoading();
        submitting = false;

        // Afficher la notification
        showNotification(pendingNotification.type, pendingNotification.message);

        // Traiter la réponse
        if (pendingNotification.success) {
          validated.forEach((field) => {
            const input = document.getElementById(field);
            if (input) input.setAttribute("data-original-value", input.value);
          });
          validated.clear();
          editingFields.clear();
          inputs.forEach((input) => {
            if (!editingFields.has(input.id)) input.disabled = true;
          });
          setTimeout(updateActionButtons, 50);
        } else {
          resetAllFields();
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        showNotification("error", "Une erreur de connexion est survenue.");
        inputs.forEach((input) => {
          if (!editingFields.has(input.id)) input.disabled = true;
        });
        hideLoading();
        submitting = false;
        resetAllFields();
      });
  }

  function fadeOut(element) {
    if (!element) return;
    element.style.opacity = "0";
    setTimeout(() => element.remove(), 300);
  }

  function toggleEditingClass(field, isEditing) {
    const fieldValue = document.querySelector(`.field-value:has(#${field})`);
    if (fieldValue) fieldValue.classList.toggle("editing", isEditing);
  }

  function cleanupValidationUI(input) {
    if (!input) return;

    const inputWrapper = input.parentElement;
    if (inputWrapper) {
      inputWrapper.classList.remove("input-valid", "input-invalid");
    }

    if (typeof removeWarning === "function") {
      removeWarning(input);
      return;
    }

    // Suppression manuelle des avertissements
    if (inputWrapper) {
      let nextElem = inputWrapper.nextElementSibling;
      while (nextElem) {
        if (
          nextElem.classList.contains("input-warning") ||
          nextElem.classList.contains("password-strength-meter")
        ) {
          nextElem.remove();
          nextElem = inputWrapper.nextElementSibling;
        } else {
          nextElem = nextElem.nextElementSibling;
        }
      }
    }

    const parentField = input.closest(".profile-field");
    if (parentField) {
      parentField
        .querySelectorAll(".input-warning")
        .forEach((warning) => warning.remove());
    }
  }

  function updateActionButtons() {
    if (!submitBtn || !cancelAllBtn) return;

    const hasValidatedFields = validated.size > 0;

    if (hasValidatedFields) {
      submitBtn.style.display = "inline-flex";
      cancelAllBtn.style.display = "inline-flex";

      setTimeout(() => {
        submitBtn.classList.add("visible");
        cancelAllBtn.classList.add("visible");
      }, 10);
    } else if (submitBtn.classList.contains("visible")) {
      submitBtn.classList.remove("visible");
      cancelAllBtn.classList.remove("visible");

      setTimeout(() => {
        if (!validated.size) {
          submitBtn.style.display = "none";
          cancelAllBtn.style.display = "none";
        }
      }, 300);
    }
  }

  function resetAllFields() {
    validated.forEach((field) => {
      const input = document.getElementById(field);
      if (!input) return;

      input.value = input.getAttribute("data-original-value") || "";
      input.disabled = true;
      toggleEditingClass(field, false);
      cleanupValidationUI(input);
    });

    editingFields.forEach((field) => {
      const input = document.getElementById(field);
      if (!input) return;

      input.value = input.getAttribute("data-original-value") || "";
      input.disabled = true;
      toggleEditingClass(field, false);

      document.querySelector(
        `.field-edit[data-field="${field}"]`
      ).style.display = "inline-flex";
      document.querySelector(
        `.field-validate[data-field="${field}"]`
      ).style.display = "none";
      document.querySelector(
        `.field-cancel[data-field="${field}"]`
      ).style.display = "none";

      cleanupValidationUI(input);

      if (field === "password" && passwordToggle) {
        passwordToggle.style.display = "none";
        passwordInput.setAttribute("type", "password");
        if (toggleIcon) toggleIcon.src = "../img/svg/eye.svg";
      }
    });

    validated.clear();
    editingFields.clear();

    setTimeout(updateActionButtons, 50);
  }

  function showNotification(type, message) {
    document.querySelectorAll(".notification").forEach(fadeOut);

    const notification = document.createElement("div");
    notification.className = `notification ${type}`;

    const icon = document.createElement("img");
    icon.src =
      type === "success"
        ? "../img/svg/check-circle.svg"
        : "../img/svg/alert-circle.svg";
    icon.alt = type === "success" ? "Succès" : "Erreur";

    const text = document.createElement("p");
    text.textContent = message;

    const closeBtn = document.createElement("button");
    closeBtn.className = "close-notification";
    closeBtn.innerHTML = "&times;";
    closeBtn.addEventListener("click", () => fadeOut(notification));

    notification.appendChild(icon);
    notification.appendChild(text);
    notification.appendChild(closeBtn);

    document.body.insertBefore(notification, document.body.firstChild);
    setTimeout(() => fadeOut(notification), 5000);
  }

  function showLoading() {
    if (!submitBtn) return;

    loadingStartTime = Date.now();
    submitBtn.classList.add("loading");
    submitBtn.disabled = true;

    const originalText = submitBtn.querySelector("span");
    if (originalText) {
      originalText.setAttribute("data-original-text", originalText.textContent);
      originalText.textContent = "Traitement en cours...";
    }
  }

  function hideLoading() {
    if (!submitBtn) return;

    const elapsedTime = Date.now() - loadingStartTime;
    const minLoadingTime = 800;

    if (elapsedTime < minLoadingTime) {
      setTimeout(() => {
        submitBtn.classList.remove("loading");
        submitBtn.disabled = false;

        const textElement = submitBtn.querySelector("span");
        if (textElement && textElement.hasAttribute("data-original-text")) {
          textElement.textContent =
            textElement.getAttribute("data-original-text");
          textElement.removeAttribute("data-original-text");
        }
      }, minLoadingTime - elapsedTime);
    } else {
      submitBtn.classList.remove("loading");
      submitBtn.disabled = false;

      const textElement = submitBtn.querySelector("span");
      if (textElement && textElement.hasAttribute("data-original-text")) {
        textElement.textContent =
          textElement.getAttribute("data-original-text");
        textElement.removeAttribute("data-original-text");
      }
    }
  }

  function openMessageModal(messageItem) {
    const messageData = JSON.parse(messageItem.dataset.message);
    const subjectElement = document.querySelector(".message-subject-modal");
    const dateElement = document.querySelector(".message-date-modal");
    const timeElement = document.querySelector(".message-time-modal");
    const contentElement = document.querySelector(".message-content-modal");

    if (
      !messageData ||
      !subjectElement ||
      !dateElement ||
      !timeElement ||
      !contentElement ||
      !messageModal
    )
      return;

    subjectElement.textContent = messageData.objet;

    const messageDate = new Date(messageData.date);
    dateElement.textContent = messageDate.toLocaleDateString("fr-FR", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
    timeElement.textContent = messageDate.toLocaleTimeString("fr-FR", {
      hour: "2-digit",
      minute: "2-digit",
    });

    contentElement.textContent = messageData.message;

    messageModal.style.display = "flex";
    document.body.style.overflow = "hidden";

    setTimeout(() => {
      messageModal.classList.add("message-modal-active");
    }, 10);
  }

  function closeMessageModal() {
    if (!messageModal) return;

    messageModal.classList.remove("message-modal-active");

    setTimeout(() => {
      if (!messageModal.classList.contains("message-modal-active")) {
        messageModal.style.display = "none";
        document.body.style.overflow = "";
      }
    }, 300);
  }
});
