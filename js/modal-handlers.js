document.addEventListener("DOMContentLoaded", function () {
  // Handlers pour la modale de déconnexion
  const logoutButton = document.getElementById("logout-button");
  const logoutModal = document.getElementById("logout-modal");
  const cancelLogout = document.getElementById("cancel-logout");
  const confirmLogout = document.getElementById("confirm-logout");

  if (logoutButton && logoutModal && cancelLogout && confirmLogout) {
    // Afficher la modale lors du clic sur le bouton de déconnexion
    logoutButton.addEventListener("click", function () {
      logoutModal.classList.add("show");
      document.body.style.overflow = "hidden"; // Empêcher le défilement
    });

    // Cacher la modale lors du clic sur Annuler
    cancelLogout.addEventListener("click", function () {
      logoutModal.classList.remove("show");
      document.body.style.overflow = "auto"; // Réactiver le défilement
    });

    // Rediriger vers la page de déconnexion lors de la confirmation
    confirmLogout.addEventListener("click", function () {
      window.location.href = "../index.php?logout=true";
    });

    // Fermer la modale si on clique en dehors
    window.addEventListener("click", function (event) {
      if (event.target === logoutModal) {
        logoutModal.classList.remove("show");
        document.body.style.overflow = "auto";
      }
    });
  }

  // Handlers pour la modale de suppression de compte
  const deleteButton = document.getElementById("delete-account-button");
  const deleteModal = document.getElementById("delete-account-modal");
  const cancelDelete = document.getElementById("cancel-delete");
  const confirmDelete = document.getElementById("confirm-delete");

  if (deleteButton && deleteModal && cancelDelete && confirmDelete) {
    // Afficher la modale lors du clic sur le bouton de suppression
    deleteButton.addEventListener("click", function () {
      deleteModal.classList.add("show");
      document.body.style.overflow = "hidden"; // Empêcher le défilement
    });

    // Cacher la modale lors du clic sur Annuler
    cancelDelete.addEventListener("click", function () {
      deleteModal.classList.remove("show");
      document.body.style.overflow = "auto"; // Réactiver le défilement
    });

    // Rediriger vers la page de suppression de compte lors de la confirmation
    confirmDelete.addEventListener("click", function () {
      window.location.href = "delete_account.php";
    });

    // Fermer la modale si on clique en dehors
    window.addEventListener("click", function (event) {
      if (event.target === deleteModal) {
        deleteModal.classList.remove("show");
        document.body.style.overflow = "auto";
      }
    });
  }
});
