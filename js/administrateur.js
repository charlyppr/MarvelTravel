document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "../php/update_user_status.php";
  const tableBody = document.querySelector(".tab-voyageurs tbody");
  const voyageCount = document.getElementById("voyage-count");
  const searchInput = document.getElementById("search");
  const searchNoResults = document.getElementById("search-no-results");
  const searchTerm = document.getElementById("search-term");
  const sortSelect = document.getElementById("sort-select");
  const searchButton =
    document.querySelector("#search-button") || searchInput?.nextElementSibling;
  const resetSearchBtn = document.getElementById("reset-search");

  // Afficher une notification
  const showNotification = (message, type = "success") => {
    document.querySelector(".notification")?.remove();
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => (notification.style.opacity = "1"), 10);
    setTimeout(() => {
      notification.style.opacity = "0";
      setTimeout(() => notification.remove(), 500);
    }, 3000);
  };

  // Mettre à jour le statut d'un utilisateur
  const updateUserStatus = async (email, updateData) => {
    try {
      const response = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, ...updateData }),
      });

      if (!response.ok)
        return { success: false, message: `Erreur: ${response.status}` };

      try {
        return JSON.parse(await response.text());
      } catch (e) {
        return { success: false, message: "Réponse invalide" };
      }
    } catch (error) {
      return {
        success: false,
        message: `Erreur de connexion: ${error.message}`,
      };
    }
  };

  // Mettre à jour le nombre d'utilisateurs affichés
  const updateUserCount = () =>
    (voyageCount.textContent = `${
      tableBody.querySelectorAll('tr:not([style*="display: none"])').length
    } voyageurs`);

  // Trier le tableau
  const sortTable = (sortBy) => {
    const sorters = {
      recent: (a, b) =>
        new Date(b.querySelector(".date").dataset.date) -
        new Date(a.querySelector(".date").dataset.date),
      oldest: (a, b) =>
        new Date(a.querySelector(".date").dataset.date) -
        new Date(b.querySelector(".date").dataset.date),
      "name-asc": (a, b) =>
        a
          .querySelector(".nom")
          .textContent.localeCompare(b.querySelector(".nom").textContent),
      "name-desc": (a, b) =>
        b
          .querySelector(".nom")
          .textContent.localeCompare(a.querySelector(".nom").textContent),
    };

    if (!sorters[sortBy]) return;

    const rows = Array.from(tableBody.querySelectorAll("tr")).sort(
      sorters[sortBy]
    );
    tableBody.innerHTML = "";
    rows.forEach((row) => tableBody.appendChild(row));
    updateUserCount();
  };

  // Rechercher des utilisateurs
  const performSearch = () => {
    const query = searchInput.value.toLowerCase().trim();
    let hasResults = false;

    tableBody.querySelectorAll("tr").forEach((row) => {
      const name = row.querySelector(".nom").textContent.toLowerCase();
      const email = row.dataset.email.toLowerCase();
      const match = query.includes("@")
        ? email.includes(query)
        : name.includes(query);

      row.style.display = match || !query ? "" : "none";
      hasResults = hasResults || match || !query;
    });

    searchNoResults.style.display = !hasResults && query ? "flex" : "none";
    if (!hasResults && query) searchTerm.textContent = query;
    updateUserCount();
  };

  // Mettre à jour l'état du toggle VIP
  const updateVipToggleState = (row, isBlocked) => {
    const vipToggle = row.querySelector(".toggle-vip");
    if (vipToggle) {
      vipToggle.classList.toggle("disabled", isBlocked);
      vipToggle.title = isBlocked
        ? "Impossible d'attribuer le statut VIP à un utilisateur bloqué"
        : "";
    }
  };

  // Gestionnaires d'événements pour les toggles de statut
  document.querySelectorAll(".toggle-status").forEach((el) => {
    el.addEventListener("click", async function () {
      const isCurrentlyBlocked = this.dataset.status === "blocked";
      this.classList.add("updating");
      const row = this.closest("tr");
      const email = row.dataset.email;
      const newStatus = isCurrentlyBlocked ? "active" : "blocked";

      const startTime = Date.now();
      const response = await updateUserStatus(email, { status: newStatus });

      setTimeout(() => {
        if (response.success) {
          this.classList.replace(
            isCurrentlyBlocked ? "status-pending" : "status-ok",
            isCurrentlyBlocked ? "status-ok" : "status-pending"
          );
          this.dataset.status = newStatus;

          if (isCurrentlyBlocked) {
            this.innerHTML =
              'Actif<img src="../img/svg/check.svg" alt="check"><span class="tooltip">Cliquez pour bloquer</span>';
            showNotification("Utilisateur débloqué avec succès");
          } else {
            this.innerHTML =
              'Bloqué<img src="../img/svg/block.svg" alt="block"><span class="tooltip">Cliquez pour débloquer</span>';

            const vipToggle = row.querySelector(".toggle-vip");
            if (vipToggle?.dataset.vip === "1") {
              vipToggle.classList.replace("vip-badge", "novip-badge");
              vipToggle.dataset.vip = "0";
              vipToggle.innerHTML =
                'Non<img src="../img/svg/no.svg" alt="croix"><span class="tooltip">Cliquez pour ajouter le VIP</span>';
            }
            showNotification("Utilisateur bloqué avec succès");
          }
          updateVipToggleState(row, newStatus === "blocked");
        } else {
          showNotification(
            response.message || "Une erreur est survenue",
            "error"
          );
        }
        this.classList.remove("updating");
      }, Math.max(0, 800 - (Date.now() - startTime)));
    });
  });

  // Gestionnaires d'événements pour les toggles VIP
  document.querySelectorAll(".toggle-vip").forEach((el) => {
    const row = el.closest("tr");
    updateVipToggleState(
      row,
      row.querySelector(".toggle-status")?.dataset.status === "blocked"
    );

    el.addEventListener("click", async function () {
      const row = this.closest("tr");
      const isBlocked =
        row.querySelector(".toggle-status")?.dataset.status === "blocked";

      if (isBlocked) {
        return showNotification(
          "Impossible d'attribuer le statut VIP à un utilisateur bloqué",
          "error"
        );
      }

      const isCurrentlyVip = this.dataset.vip === "1";
      this.classList.add("updating");
      const email = row.dataset.email;

      const startTime = Date.now();
      const response = await updateUserStatus(email, { vip: !isCurrentlyVip });

      setTimeout(() => {
        if (response.success) {
          if (isCurrentlyVip) {
            this.classList.replace("vip-badge", "novip-badge");
            this.dataset.vip = "0";
            this.innerHTML =
              'Non<img src="../img/svg/no.svg" alt="croix"><span class="tooltip">Cliquez pour ajouter le VIP</span>';
            showNotification("Statut VIP retiré avec succès");
          } else {
            this.classList.replace("novip-badge", "vip-badge");
            this.dataset.vip = "1";
            this.innerHTML =
              'VIP<img src="../img/svg/etoile.svg" alt="etoile"><span class="tooltip">Cliquez pour retirer le VIP</span>';
            showNotification("Statut VIP ajouté avec succès");
          }
        } else {
          showNotification(
            response.message || "Une erreur est survenue",
            "error"
          );
        }
        this.classList.remove("updating");
      }, Math.max(0, 800 - (Date.now() - startTime)));
    });
  });

  // Configuration des événements de recherche et tri
  searchInput.addEventListener("input", performSearch);
  searchButton?.addEventListener("click", performSearch);
  searchInput.addEventListener(
    "keypress",
    (e) => e.key === "Enter" && performSearch()
  );
  resetSearchBtn?.addEventListener("click", () => {
    searchInput.value = "";
    performSearch();
    searchInput.focus();
  });
  sortSelect.addEventListener("change", (e) => sortTable(e.target.value));

  // Initialisation
  updateUserCount();
  sortTable("recent");
});
