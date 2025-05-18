document.addEventListener("DOMContentLoaded", () => {
  // Sélecteurs essentiels
  const sortSelect = document.querySelector("#sort-select");
  const voyagesContainer = document.querySelector(".voyages-list");
  const tableContainer = document.querySelector(".table-container");
  const searchInput = document.querySelector("#search");
  const resetSearch = document.querySelector("#reset-search");
  const searchNoResults = document.querySelector("#search-no-results");
  const voyageCount = document.querySelector("#voyage-count");
  const searchButton = searchInput?.nextElementSibling;

  // Détection de l'environnement
  const isAdminPage = document.querySelector(".tab-voyageurs") !== null;
  const isCardView = voyagesContainer && voyagesContainer.children.length > 0;
  const isTableView =
    tableContainer &&
    tableContainer.querySelector("tbody")?.children.length > 0;

  // Fonctions principales
  function getElements() {
    if (isCardView)
      return Array.from(voyagesContainer.querySelectorAll(".voyage-card"));
    if (isTableView)
      return Array.from(
        tableContainer.querySelector("tbody").querySelectorAll("tr")
      );
    return [];
  }

  function updateCounter(matchCount = null) {
    if (!voyageCount) return;

    const count = matchCount === null ? getElements().length : matchCount;
    const suffix = isAdminPage
      ? `voyageur${count > 1 ? "s" : ""}${
          matchCount !== null ? " trouvé" + (count > 1 ? "s" : "") : ""
        }`
      : `${matchCount !== null ? "résultat" : "au total"}${
          count > 1 ? "s" : ""
        }`;

    voyageCount.textContent = `${count} ${suffix}`;
  }

  function sortVoyages(sortType) {
    if (!isCardView && !isTableView) return;

    const elements = getElements();
    const container = isCardView
      ? voyagesContainer
      : tableContainer.querySelector("tbody");

    const extractPrice = (el) =>
      parseFloat(
        el
          .querySelector(".price")
          .textContent.replace(/[^\d,]/g, "")
          .replace(",", ".")
      );
    const parseDate = (str) => {
      const parts = str.split("/");
      return parts.length === 3
        ? new Date(parts[2], parts[1] - 1, parts[0])
        : null;
    };

    const sortFunctions = {
      recent: (a, b) =>
        (b.dataset.dateAchat || "").localeCompare(a.dataset.dateAchat || ""),
      "price-asc": (a, b) => extractPrice(a) - extractPrice(b),
      "price-desc": (a, b) => extractPrice(b) - extractPrice(a),
      "date-asc": (a, b) => {
        const dateA = parseDate(
          a.querySelector(".dates").textContent.trim().split(" au ")[0]
        );
        const dateB = parseDate(
          b.querySelector(".dates").textContent.trim().split(" au ")[0]
        );
        return dateA && dateB ? dateA - dateB : 0;
      },
      "date-desc": (a, b) => {
        const dateA = parseDate(
          a.querySelector(".dates").textContent.trim().split(" au ")[0]
        );
        const dateB = parseDate(
          b.querySelector(".dates").textContent.trim().split(" au ")[0]
        );
        return dateA && dateB ? dateB - dateA : 0;
      },
    };

    if (sortFunctions[sortType]) {
      elements
        .sort(sortFunctions[sortType])
        .forEach((el) => container.appendChild(el));
    }
  }

  function performSearch() {
    if (!searchInput) return;

    const term = searchInput.value.trim().toLowerCase();
    const elements = getElements();
    let matchCount = 0;

    // Analyse du type de recherche
    const isPriceQuery = /^\d+[\d,. ]*€?$/.test(term);
    let searchPrice = null,
      searchPriceString = null;

    if (isPriceQuery) {
      if (term.includes(",") || term.includes(".")) {
        searchPrice = parseFloat(
          term.replace(/[^\d,\.]/g, "").replace(",", ".")
        );
      } else {
        searchPriceString = term.replace(/[^\d]/g, "");
      }
    }

    const isDateQuery = (query) => /[\d\/\-]/.test(query);

    elements.forEach((element) => {
      const destination = element
        .querySelector(".destination")
        .textContent.toLowerCase();
      const dates = element.querySelector(".dates").textContent.toLowerCase();
      const priceText = element.querySelector(".price").textContent;
      const priceValue = parseFloat(
        priceText.replace(/[^\d,]/g, "").replace(",", ".")
      );
      const priceDigits = priceText.replace(/[^\d]/g, "");

      let match =
        term === "" ||
        (isPriceQuery &&
          ((searchPrice !== null &&
            !isNaN(searchPrice) &&
            Math.abs(priceValue - searchPrice) < 0.01) ||
            (searchPriceString && priceDigits.includes(searchPriceString)))) ||
        (isDateQuery(term) && dates.includes(term)) ||
        (!isPriceQuery && !isDateQuery(term) && destination.includes(term));

      element.style.display = match ? "" : "none";
      if (match) matchCount++;
    });

    // Mise à jour de l'interface après recherche
    if (matchCount === 0 && term !== "") {
      if (searchNoResults) {
        searchNoResults.style.display = "flex";
        if (searchNoResults.querySelector(".search-term"))
          searchNoResults.querySelector(".search-term").textContent = term;
      }

      if (isCardView && voyagesContainer)
        voyagesContainer.style.display = "none";
      if (isTableView && tableContainer) tableContainer.style.display = "none";
    } else {
      if (searchNoResults) searchNoResults.style.display = "none";
      if (isCardView && voyagesContainer) voyagesContainer.style.display = "";
      if (isTableView && tableContainer) tableContainer.style.display = "";
    }

    updateCounter(term !== "" ? matchCount : null);
  }

  // Écouteurs d'événements
  sortSelect?.addEventListener("change", (e) => sortVoyages(e.target.value));

  if (searchInput) {
    searchInput.addEventListener("input", performSearch);
    searchInput.addEventListener(
      "keypress",
      (e) => e.key === "Enter" && (e.preventDefault(), performSearch())
    );
    searchButton?.addEventListener("click", performSearch);
    resetSearch?.addEventListener("click", () => {
      searchInput.value = "";
      performSearch();
      searchInput.focus();
    });
  }

  // Initialisation
  updateCounter();
  if (sortSelect) sortVoyages(sortSelect.value);
});
