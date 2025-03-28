document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebar-overlay");
  const mobileToggle = document.getElementById("mobile-toggle");
  const logoutButton = document.getElementById("logout-button");
  const deleteAccountButton = document.getElementById("delete-account-button");

  // Fonction pour activer/désactiver la barre latérale sur mobile
  function toggleSidebar() {
    sidebar.classList.toggle("active");
    sidebarOverlay.classList.toggle("visible");
    mobileToggle.classList.toggle("active");
    document.body.classList.toggle("sidebar-open");
  }

  // Event listeners pour le bouton mobile et le clic sur l'overlay
  if (mobileToggle) {
    mobileToggle.addEventListener("click", toggleSidebar);
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", toggleSidebar);
  }

  // Fermer la sidebar si on clique sur un lien (mobile)
  document.querySelectorAll(".sidebar .nav-link").forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth < 992 && sidebar.classList.contains("active")) {
        toggleSidebar();
      }
    });
  });

  // Fonctionnalité de déconnexion
  if (logoutButton) {
    logoutButton.addEventListener("click", function () {
      // Confirmation avant déconnexion
      if (window.confirm("Êtes-vous sûr de vouloir vous déconnecter ?")) {
        window.location.href = "logout.php";
      }
    });
  }

  // Fonctionnalité de suppression de compte
  if (deleteAccountButton) {
    deleteAccountButton.addEventListener("click", function () {
      // Afficher la modal de confirmation (ou rediriger vers une page de confirmation)
      const deleteAccountModal = document.getElementById(
        "delete-account-modal"
      );
      if (deleteAccountModal) {
        deleteAccountModal.classList.add("active");

        // Fermer la sidebar sur mobile
        if (window.innerWidth < 992 && sidebar.classList.contains("active")) {
          toggleSidebar();
        }
      } else {
        // Fallback si la modal n'existe pas
        if (
          window.confirm(
            "Attention : Êtes-vous vraiment sûr de vouloir supprimer définitivement votre compte ?"
          )
        ) {
          window.location.href = "delete_account.php";
        }
      }
    });
  }

  // Ajuster la sidebar en fonction de la taille de l'écran au chargement
  function adjustSidebar() {
    if (window.innerWidth < 992) {
      sidebar.classList.remove("active");
      sidebarOverlay.classList.remove("visible");
    }
  }

  // Ajuster au chargement
  adjustSidebar();

  // Ajuster lors du redimensionnement
  window.addEventListener("resize", adjustSidebar);
});
