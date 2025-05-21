document.addEventListener("DOMContentLoaded", () => {
  const requiredFields = document.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );
  const submitButton = document.querySelector(".next-button");
  const buttonImage = submitButton?.querySelector("img");

  // Gestion de la galerie d'images
  const galleryInput = document.getElementById("gallery_images");
  const galleryPreview = document.getElementById("gallery-preview");

  if (galleryInput && galleryPreview) {
    galleryInput.addEventListener("change", function () {
      if (this.files.length > 5) {
        alert(
          "Vous ne pouvez sélectionner que 5 images maximum pour le carrousel."
        );
        this.value = "";
        return;
      }

      galleryPreview.innerHTML = "";

      Array.from(this.files).forEach((file, index) => {
        if (!file.type.match("image.*")) return;

        const reader = new FileReader();
        reader.onload = (e) => {
          const galleryItem = document.createElement("div");
          galleryItem.className = "gallery-item";
          galleryItem.dataset.index = index;

          const img = document.createElement("img");
          img.src = e.target.result;
          img.alt = file.name;

          const removeBtn = document.createElement("div");
          removeBtn.className = "gallery-item-remove";
          removeBtn.innerHTML = "&times;";
          removeBtn.addEventListener("click", (e) => {
            e.preventDefault();
            galleryItem.remove();
            if (galleryPreview.querySelectorAll(".gallery-item").length === 0)
              galleryInput.value = "";
          });

          galleryItem.append(img, removeBtn);
          galleryPreview.appendChild(galleryItem);
        };
        reader.readAsDataURL(file);
      });

      updateFileLabel(
        galleryInput,
        `${this.files.length} image(s) sélectionnée(s)`
      );
    });
  }

  // Validation et capitalisation des champs
  document
    .getElementById("duree_jours")
    ?.addEventListener("input", function () {
      if (parseInt(this.value) < 2) this.value = 2;
    });

  document
    .querySelector('input[name="titre"]')
    ?.addEventListener("blur", function () {
      this.value = this.value
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
    });

  // Gestion des tags
  [
    "categories",
    "highlights",
    "langues",
    "inclus",
    "non_inclus",
    "films",
    "personnages",
  ].forEach((fieldName) => {
    const input = document.querySelector(`input[name="${fieldName}"]`);
    if (!input) return;

    const container = document.createElement("div");
    container.className = "tags-container";

    const visibleInput = document.createElement("input");
    visibleInput.type = "text";
    visibleInput.className = "tag-input";
    visibleInput.placeholder = input.placeholder;

    input.parentNode.insertBefore(container, input.nextSibling);
    input.parentNode.insertBefore(visibleInput, input.nextSibling);
    input.style.display = "none";

    const updateOriginalInput = () => {
      input.value = Array.from(container.querySelectorAll(".tag"))
        .map((tag) => tag.dataset.value)
        .join(",");
      input.dispatchEvent(new Event("input", { bubbles: true }));
    };

    const addTag = (value) => {
      if (!value.trim()) return;

      const formattedValue = value
        .trim()
        .split(" ")
        .map(
          (word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        )
        .join(" ");

      const tag = document.createElement("span");
      tag.className = "tag";
      tag.textContent = formattedValue;
      tag.dataset.value = formattedValue;

      const removeBtn = document.createElement("span");
      removeBtn.className = "tag-remove";
      removeBtn.innerHTML = "&times;";
      removeBtn.addEventListener("click", () => {
        container.removeChild(tag);
        updateOriginalInput();
        visibleInput.focus();
      });

      tag.appendChild(removeBtn);
      container.appendChild(tag);
      updateOriginalInput();
      visibleInput.value = "";
    };

    if (input.value)
      input.value.split(",").forEach((tag) => tag.trim() && addTag(tag));

    visibleInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === ",") {
        e.preventDefault();
        addTag(e.target.value);
      } else if (e.key === "Backspace" && e.target.value === "") {
        const tags = container.querySelectorAll(".tag");
        if (tags.length > 0) {
          container.removeChild(tags[tags.length - 1]);
          updateOriginalInput();
        }
      }
    });

    visibleInput.addEventListener(
      "blur",
      (e) => e.target.value.trim() && addTag(e.target.value)
    );
    visibleInput.addEventListener("input", (e) => {
      if (e.target.value.includes(",")) {
        const value = e.target.value.replace(",", "");
        value.trim() && addTag(value);
      }
    });
  });

  // Validation du formulaire
  const validateField = (field) => {
    let valid = field.value.trim() !== "";
    if (field.type === "number")
      valid = valid && !isNaN(field.value) && parseFloat(field.value) > 0;
    if (field.type === "file") valid = valid && field.files.length > 0;

    field.classList.toggle("input-valid", valid);
    field.classList.toggle("input-invalid", !valid);
    return valid;
  };

  const validateForm = () => {
    let valid = true;
    requiredFields.forEach((field) => (valid = validateField(field) && valid));

    if (submitButton) {
      submitButton.classList.toggle("disabled-button", !valid);
      submitButton.disabled = !valid;
    }

    return valid;
  };

  // Initialiser la validation
  validateForm();

  // Événements de validation pour tous les champs requis
  requiredFields.forEach((field) => {
    const eventType =
      field.tagName === "SELECT" || field.type === "file" ? "change" : "input";
    field.addEventListener(eventType, validateForm);

    field.addEventListener("focus", () => field.classList.add("focus-within"));
    field.addEventListener("blur", () => {
      field.classList.remove("focus-within");
      validateField(field);
    });
  });

  // Style du champ d'upload
  const fileInput = document.getElementById("image");

  fileInput?.addEventListener("change", function () {
    updateFileLabel(
      this,
      this.files.length > 0 ? this.files[0].name : "Image principale"
    );
    validateForm();
  });

  // Fonction utilitaire pour les labels de fichiers
  function updateFileLabel(input, text) {
    const fileLabel = input.nextElementSibling;
    if (fileLabel?.classList.contains("file-label")) {
      fileLabel.textContent = text;
      fileLabel.classList.add("file-selected");
    }
  }
});
