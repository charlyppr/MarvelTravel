document.addEventListener("DOMContentLoaded", () => {
  // Éléments des modals
  const logoutModal = document.getElementById("logout-modal");
  const deleteModal = document.getElementById("delete-account-modal");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebar-overlay");

  // Fonction pour afficher/masquer un modal
  const toggleModal = (modal, show) => {
    if (modal) modal.style.display = show ? "flex" : "none";
  };

  // Gestion des modals
  document
    .getElementById("logout-button")
    ?.addEventListener("click", () => toggleModal(logoutModal, true));
  document
    .getElementById("delete-account-button")
    ?.addEventListener("click", () => toggleModal(deleteModal, true));

  document
    .querySelector(".close-modal")
    ?.addEventListener("click", () => toggleModal(logoutModal, false));
  document
    .querySelector(".close-modal-delete")
    ?.addEventListener("click", () => toggleModal(deleteModal, false));

  document
    .getElementById("cancel-logout")
    ?.addEventListener("click", () => toggleModal(logoutModal, false));
  document
    .getElementById("cancel-delete")
    ?.addEventListener("click", () => toggleModal(deleteModal, false));

  // Fermeture des modals en cliquant à l'extérieur
  window.addEventListener("click", (e) => {
    if (e.target === logoutModal) toggleModal(logoutModal, false);
    if (e.target === deleteModal) toggleModal(deleteModal, false);
  });

  // Gestion de la sidebar mobile
  const toggleSidebar = () => {
    sidebar?.classList.toggle("open");
    sidebarOverlay?.classList.toggle("active");
    document.body.classList.toggle("sidebar-open");
  };

  document
    .getElementById("mobile-toggle")
    ?.addEventListener("click", toggleSidebar);
  sidebarOverlay?.addEventListener("click", toggleSidebar);

  // Fermeture de la sidebar sur resize et sur click des liens (mobile)
  window.addEventListener("resize", () => {
    if (window.innerWidth > 991 && sidebar?.classList.contains("open")) {
      sidebar.classList.remove("open");
      sidebarOverlay?.classList.remove("active");
      document.body.classList.remove("sidebar-open");
    }
  });

  if (window.innerWidth <= 991) {
    document.querySelectorAll(".sidebar a").forEach((link) => {
      link.addEventListener("click", () => {
        sidebar?.classList.remove("open");
        sidebarOverlay?.classList.remove("active");
        document.body.classList.remove("sidebar-open");
      });
    });
  }
});
