document.addEventListener("DOMContentLoaded", () => {
  const requiredFields = document.querySelectorAll(
    "input[required], select[required]"
  );
  const submitButton = document.querySelector(".next-button");
  const parentSelectors = ".email, .mdp, .civilite, .nationalite, .date-input";

  const validateForm = () => {
    let isValid = true;

    requiredFields.forEach((field) => {
      const value = field.value.trim();
      const isEmpty = value === "";

      const fieldValid = !isEmpty;
      const parent = field.closest(parentSelectors);

      field.classList.toggle("input-valid", !isEmpty);
      field.classList.toggle("input-invalid", isEmpty);

      if (parent) {
        parent.classList.toggle("input-valid", fieldValid);
        parent.classList.toggle("input-invalid", !fieldValid);
      }

      isValid = isValid && fieldValid;
    });

    submitButton.classList.toggle("disabled-button", !isValid);
    submitButton.disabled = !isValid;

    return isValid;
  };

  // Initialisation
  validateForm();
});
