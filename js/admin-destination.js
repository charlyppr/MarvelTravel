document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner tous les champs requis et le bouton de soumission
  const requiredFields = document.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  const submitButton = document.querySelector(".next-button");

  // Gestion de la galerie d'images
  const galleryInput = document.getElementById("gallery_images");
  const galleryPreview = document.getElementById("gallery-preview");

  if (galleryInput && galleryPreview) {
    galleryInput.addEventListener("change", function () {
      // Limiter à 5 images maximum
      if (this.files.length > 5) {
        alert(
          "Vous ne pouvez sélectionner que 5 images maximum pour le carrousel."
        );
        this.value = "";
        return;
      }

      // Vider la prévisualisation
      galleryPreview.innerHTML = "";

      // Créer les prévisualisations pour chaque image
      Array.from(this.files).forEach((file, index) => {
        if (!file.type.match("image.*")) return;

        const reader = new FileReader();

        reader.onload = function (e) {
          const galleryItem = document.createElement("div");
          galleryItem.className = "gallery-item";
          galleryItem.dataset.index = index;

          const img = document.createElement("img");
          img.src = e.target.result;
          img.alt = file.name;

          const removeBtn = document.createElement("div");
          removeBtn.className = "gallery-item-remove";
          removeBtn.innerHTML = "&times;";
          removeBtn.addEventListener("click", function (e) {
            e.preventDefault();
            galleryItem.remove();

            // Comme nous ne pouvons pas modifier les FileList directement,
            // nous allons réinitialiser l'input si toutes les images sont supprimées
            if (galleryPreview.querySelectorAll(".gallery-item").length === 0) {
              galleryInput.value = "";
            }
          });

          galleryItem.appendChild(img);
          galleryItem.appendChild(removeBtn);
          galleryPreview.appendChild(galleryItem);
        };

        reader.readAsDataURL(file);
      });

      // Mettre à jour le label
      const fileLabel = galleryInput.nextElementSibling;
      if (fileLabel && fileLabel.classList.contains("file-label")) {
        fileLabel.textContent = `${this.files.length} image(s) sélectionnée(s)`;
        fileLabel.classList.add("file-selected");
      }
    });
  }

  // Validation spécifique pour le champ de durée
  const dureeInput = document.getElementById("duree_jours");
  if (dureeInput) {
    dureeInput.addEventListener("input", function () {
      const value = parseInt(this.value);
      if (value < 2) {
        this.value = 2;
      }
    });
  }

  // Ajout de la capitalisation pour le champ titre
  const titreInput = document.querySelector('input[name="titre"]');
  if (titreInput) {
    titreInput.addEventListener("blur", function () {
      // Capitaliser la première lettre de chaque mot
      this.value = this.value
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
    });
  }

  // Ajout des tags pour les champs avec virgules
  const tagFields = [
    "categories",
    "highlights",
    "langues",
    "inclus",
    "non_inclus",
    "films",
    "personnages",
  ];

  tagFields.forEach((fieldName) => {
    const input = document.querySelector(`input[name="${fieldName}"]`);
    if (!input) return;

    // Créer un conteneur pour les tags
    const container = document.createElement("div");
    container.className = "tags-container";
    input.parentNode.insertBefore(container, input.nextSibling);

    // Masquer le champ input original
    input.style.display = "none";

    // Créer un nouvel input visible pour la saisie des tags
    const visibleInput = document.createElement("input");
    visibleInput.type = "text";
    visibleInput.className = "tag-input";
    visibleInput.placeholder = input.placeholder;
    input.parentNode.insertBefore(visibleInput, input.nextSibling);

    // Fonction pour mettre à jour l'input original avec les valeurs des tags
    function updateOriginalInput() {
      const tags = Array.from(container.querySelectorAll(".tag")).map(
        (tag) => tag.dataset.value
      );
      input.value = tags.join(",");
      // Déclencher l'événement input pour la validation
      const event = new Event("input", { bubbles: true });
      input.dispatchEvent(event);
    }

    // Fonction pour ajouter un tag
    function addTag(value) {
      if (!value.trim()) return;

      // Capitaliser la première lettre de chaque mot
      const formattedValue = value
        .trim()
        .split(" ")
        .map(
          (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
        .join(" ");

      // Créer l'élément tag
      const tag = document.createElement("span");
      tag.className = "tag";
      tag.textContent = formattedValue;
      tag.dataset.value = formattedValue;

      // Ajouter bouton de suppression
      const removeBtn = document.createElement("span");
      removeBtn.className = "tag-remove";
      removeBtn.innerHTML = "&times;";
      removeBtn.addEventListener("click", function () {
        container.removeChild(tag);
        updateOriginalInput();
        visibleInput.focus();
      });

      tag.appendChild(removeBtn);
      container.appendChild(tag);

      // Mettre à jour l'input original
      updateOriginalInput();

      // Réinitialiser le champ visible
      visibleInput.value = "";
    }

    // Ajouter les tags initiaux s'il y en a
    if (input.value) {
      input.value.split(",").forEach((tag) => {
        if (tag.trim()) addTag(tag.trim());
      });
    }

    // Gérer l'ajout de nouveaux tags
    visibleInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === ",") {
        e.preventDefault();
        addTag(this.value);
      } else if (e.key === "Backspace" && this.value === "") {
        // Si l'input est vide et qu'on appuie sur Backspace, supprimer le dernier tag
        const tags = container.querySelectorAll(".tag");
        if (tags.length > 0) {
          const lastTag = tags[tags.length - 1];
          container.removeChild(lastTag);
          updateOriginalInput();
        }
      }
    });

    // Ajouter également un tag lorsqu'on quitte le champ
    visibleInput.addEventListener("blur", function () {
      if (this.value.trim()) {
        addTag(this.value);
      }
    });

    // Capitaliser pendant la saisie
    visibleInput.addEventListener("input", function () {
      if (this.value.includes(",")) {
        // Si l'utilisateur a tapé une virgule, ajouter le tag
        const value = this.value.replace(",", "");
        if (value.trim()) {
          addTag(value);
        }
      }
    });
  });

  // Fonction pour vérifier si tous les champs sont valides
  function validateForm() {
    let isValid = true;
    let validFieldsCount = 0;

    requiredFields.forEach((field) => {
      // Vérification spécifique pour chaque type de champ
      let fieldValid = true;

      if (field.value.trim() === "") {
        fieldValid = false;
      } else if (
        field.type === "number" &&
        (isNaN(field.value) || parseFloat(field.value) <= 0)
      ) {
        fieldValid = false;
      } else if (field.type === "file" && field.files.length === 0) {
        fieldValid = false;
      }

      if (!fieldValid) {
        isValid = false;
      } else {
        validFieldsCount++;
      }

      // Ajouter une classe visuelle pour indiquer l'état
      if (
        field.value.trim() !== "" &&
        (field.type !== "file" || field.files.length > 0)
      ) {
        field.classList.add("input-valid");
        field.classList.remove("input-invalid");
      } else {
        field.classList.add("input-invalid");
        field.classList.remove("input-valid");
      }
    });

    return isValid;
  }

  // Appliquer la classe appropriée au bouton en fonction de la validation
  function updateSubmitButton() {
    if (validateForm()) {
      submitButton.classList.remove("disabled-button");
      submitButton.disabled = false;
    } else {
      submitButton.classList.add("disabled-button");
      submitButton.disabled = true;
    }
  }

  // Mettre à jour immédiatement au chargement
  updateSubmitButton();

  // Écouteurs d'événements pour tous les champs
  requiredFields.forEach((field) => {
    // Pour les inputs normaux, déclencher sur input
    field.addEventListener("input", updateSubmitButton);

    // Pour les selects, déclencher sur change
    if (field.tagName === "SELECT") {
      field.addEventListener("change", updateSubmitButton);
    }

    // Pour les fichiers, déclencher sur change
    if (field.type === "file") {
      field.addEventListener("change", updateSubmitButton);
    }

    // Pour les textareas, déclencher sur input
    if (field.tagName === "TEXTAREA") {
      field.addEventListener("input", updateSubmitButton);
    }

    // Pour gérer le focus et le blur
    field.addEventListener("focus", function () {
      field.classList.add("focus-within");
    });

    field.addEventListener("blur", function () {
      field.classList.remove("focus-within");

      // Valider à la perte du focus
      if (field.value.trim() === "") {
        field.classList.add("input-invalid");
        field.classList.remove("input-valid");
      } else if (
        field.type === "number" &&
        (isNaN(field.value) || parseFloat(field.value) <= 0)
      ) {
        field.classList.add("input-invalid");
        field.classList.remove("input-valid");
      } else if (field.type === "file" && field.files.length === 0) {
        field.classList.add("input-invalid");
        field.classList.remove("input-valid");
      } else {
        field.classList.add("input-valid");
        field.classList.remove("input-invalid");
      }
    });
  });

  // Styliser le champ d'upload de fichier
  const fileInput = document.getElementById("image");
  const fileLabel = document.querySelector(".file-label");

  if (fileInput && fileLabel) {
    fileInput.addEventListener("change", function () {
      if (fileInput.files.length > 0) {
        fileLabel.textContent = fileInput.files[0].name;
        fileLabel.classList.add("file-selected");
      } else {
        fileLabel.textContent = "Image principale";
        fileLabel.classList.remove("file-selected");
      }
      updateSubmitButton();
    });
  }
});
