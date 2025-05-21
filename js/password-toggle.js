document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".password-toggle-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const input = button.parentElement.querySelector("input");
      const eyeIcon = button.querySelector(".eye-icon");
      const isVisible = input.type === "password";

      input.type = isVisible ? "text" : "password";
      eyeIcon.src = `../img/svg/eye${isVisible ? "" : "-slash"}.svg`;
      eyeIcon.alt = `${isVisible ? "Masquer" : "Afficher"} le mot de passe`;
      button.title = eyeIcon.alt;
    });
  });
});
