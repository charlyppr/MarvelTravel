document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("cgv-modal");
  const rulesAccept = document.getElementById("rules-accept");
  const continueBtn = document.getElementById("continue-btn");

  // Gestion du bouton "Continuer"
  rulesAccept?.addEventListener("change", () => {
    const isChecked = rulesAccept.checked;
    continueBtn.style.opacity = isChecked ? "1" : "0.5";
    continueBtn.style.pointerEvents = isChecked ? "auto" : "none";
  });

  // Gestion du modal CGV
  const toggleModal = (show) => {
    if (!modal) return;
    modal.style.display = show ? "block" : "none";
    document.body.style.overflow = show ? "hidden" : "auto";
  };

  document.getElementById("show-cgv")?.addEventListener("click", (e) => {
    e.preventDefault();
    toggleModal(true);
  });

  document
    .querySelector(".close-modal")
    ?.addEventListener("click", () => toggleModal(false));

  window.addEventListener(
    "click",
    (e) => e.target === modal && toggleModal(false)
  );

  document
    .querySelector(".modal-content")
    ?.addEventListener("click", (e) => e.stopPropagation());
});
