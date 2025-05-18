document.addEventListener("DOMContentLoaded", () => {
  // Variables d'état
  let startDateSelected = false,
    endDateSelected = false,
    lastActiveDateField = null;

  // Fonction de défilement
  const scrollToElement = (element, offset = 200) => {
    if (!element) return;
    window.scrollTo({
      top:
        document.documentElement.scrollTop +
        element.getBoundingClientRect().top -
        offset,
      behavior: "smooth",
    });
  };

  // 1. Système de tri des cartes
  const sortSelect = document.querySelector("#sort"),
    cardsContainer = document.querySelector(
      "#toutes-destinations .all-destination-cards"
    );

  if (sortSelect && cardsContainer) {
    const getPrice = (card) =>
        parseFloat(
          (card.querySelector(".price-value")?.textContent || "0")
            .trim()
            .replace(/[^\d,]/g, "")
            .replace(",", ".")
        ),
      getTitle = (card) =>
        (card.querySelector(".card-title")?.textContent || "")
          .trim()
          .toLowerCase(),
      getRating = (card) =>
        parseFloat(
          (
            card.querySelector(".card-rating span, .rating-count span")
              ?.textContent || "0"
          ).trim()
        );

    const sortCards = (sortValue) => {
      const cards = Array.from(
        cardsContainer.querySelectorAll(".destination-card")
      );
      if (!cards.length) return;

      switch (sortValue) {
        case "price-asc":
          cards.sort((a, b) => getPrice(a) - getPrice(b));
          break;
        case "price-desc":
          cards.sort((a, b) => getPrice(b) - getPrice(a));
          break;
        case "name-asc":
          cards.sort((a, b) => getTitle(a).localeCompare(getTitle(b)));
          break;
        case "popular":
          cards.sort((a, b) => getRating(b) - getRating(a));
          break;
      }

      cards.forEach((card) => cardsContainer.appendChild(card));
    };

    sortCards("popular");
    sortSelect.value = "popular";
    sortSelect.addEventListener("change", (e) => sortCards(e.target.value));
  }

  // 2. Système de recherche et suggestions
  const searchInput = document.querySelector("#destination-search"),
    suggestionsContainer = document.querySelector("#destination-suggestions"),
    suggestionItems = document.querySelectorAll(".suggestion-item"),
    destinationItems = document.querySelectorAll(
      ".suggestion-item:not(.category-suggestion)"
    ),
    categoryItems = document.querySelectorAll(
      ".suggestion-item.category-suggestion"
    );

  if (searchInput && suggestionsContainer) {
    const showSuggestions = () => {
      suggestionsContainer.style.display = "block";

      // Limiter le nombre de suggestions
      destinationItems.forEach(
        (item, idx) => (item.style.display = idx < 5 ? "flex" : "none")
      );

      categoryItems.forEach(
        (item, idx) => (item.style.display = idx < 3 ? "flex" : "none")
      );

      setTimeout(() => scrollToElement(suggestionsContainer), 100);
    };

    const selectDestination = (name) => {
      searchInput.value = name;
      suggestionsContainer.style.display = "none";

      const dateInput = document.querySelector("#date-debut-visible"),
        calendarDropdown = document.querySelector("#calendar-dropdown");

      if (dateInput && calendarDropdown) {
        setTimeout(() => {
          dateInput.focus();
          calendarDropdown.classList.add("active");
          setTimeout(() => scrollToElement(calendarDropdown), 100);
          startDateSelected = endDateSelected = false;
        }, 100);
      }
    };

    searchInput.addEventListener("click", (e) => {
      showSuggestions();
      e.stopPropagation();
    });

    searchInput.addEventListener("focus", showSuggestions);

    searchInput.addEventListener("input", function () {
      const term = this.value.toLowerCase().trim();

      if (!term) {
        suggestionItems.forEach((item) => (item.style.display = "flex"));
        suggestionsContainer.style.display = "block";
        destinationItems.forEach(
          (item, idx) => (item.style.display = idx < 5 ? "flex" : "none")
        );
        categoryItems.forEach(
          (item, idx) => (item.style.display = idx < 3 ? "flex" : "none")
        );
        return;
      }

      let hasMatches = false;

      destinationItems.forEach((item) => {
        const isMatch =
          item.querySelector("h4").textContent.toLowerCase().includes(term) ||
          item.querySelector("p").textContent.toLowerCase().includes(term);
        item.style.display = isMatch ? "flex" : "none";
        if (isMatch) hasMatches = true;
      });

      categoryItems.forEach((item) => {
        const isMatch = item
          .querySelector("h4")
          .textContent.toLowerCase()
          .includes(term);
        item.style.display = isMatch ? "flex" : "none";
        if (isMatch) hasMatches = true;
      });

      suggestionsContainer.style.display = hasMatches ? "block" : "none";
    });

    searchInput.addEventListener("keypress", function (e) {
      if (e.key !== "Enter") return;
      e.preventDefault();

      const term = this.value.toLowerCase().trim();
      if (!term) return;

      const match = Array.from(suggestionItems).find(
        (item) =>
          item.querySelector("h4").textContent.toLowerCase().includes(term) &&
          item.style.display !== "none"
      );

      selectDestination(
        match ? match.querySelector("h4").textContent.split(",")[0] : this.value
      );
    });

    suggestionItems.forEach((item) => {
      item.addEventListener("click", function () {
        selectDestination(this.querySelector("h4").textContent.split(",")[0]);
      });
    });

    document.addEventListener("click", (e) => {
      if (
        !searchInput.contains(e.target) &&
        !suggestionsContainer.contains(e.target)
      ) {
        suggestionsContainer.style.display = "none";
      }
    });
  }

  // 3. Système de calendrier
  const dateDebutInput = document.querySelector("#date-debut-visible"),
    dateFinInput = document.querySelector("#date-fin-visible"),
    calendarDropdown = document.querySelector("#calendar-dropdown"),
    applyDatesBtn = document.querySelector(".apply-dates"),
    calendarDays = document.querySelectorAll(
      ".calendar-day:not(.empty):not(.past-day)"
    );

  if (dateDebutInput && calendarDropdown) {
    const openCalendar = () => {
      calendarDropdown.classList.add("active");
      setTimeout(() => scrollToElement(calendarDropdown), 100);
    };

    dateDebutInput.addEventListener("click", openCalendar);
    if (dateFinInput) dateFinInput.addEventListener("click", openCalendar);

    calendarDays.forEach((day) => {
      day.addEventListener("click", () => {
        if (!startDateSelected) {
          startDateSelected = true;
          day.classList.add("start-date");
        } else if (!endDateSelected) {
          endDateSelected = true;
          day.classList.add("end-date");
          setTimeout(() => {
            calendarDropdown.classList.remove("active");
            startDateSelected = endDateSelected = false;
          }, 300);
        }
      });
    });

    calendarDropdown.addEventListener("click", (e) => e.stopPropagation());

    if (applyDatesBtn) {
      applyDatesBtn.addEventListener("click", () => {
        calendarDropdown.classList.remove("active");
        startDateSelected = endDateSelected = false;
      });
    }

    const searchInputs = document.querySelectorAll(".search-field input"),
      focusIndicator = document.querySelector(".field-focus-indicator");

    if (focusIndicator) {
      new MutationObserver((mutations) => {
        mutations.forEach((m) => {
          if (
            m.attributeName === "class" &&
            !calendarDropdown.classList.contains("active") &&
            lastActiveDateField &&
            !Array.from(searchInputs).includes(document.activeElement)
          ) {
            focusIndicator.style.opacity = "0";
            lastActiveDateField = null;
          }
        });
      }).observe(calendarDropdown, { attributes: true });
    }
  }

  // 4. Indicateurs UI
  const tabIndicator = document.querySelector(".tab-indicator"),
    tabsContainer = document.querySelector(".search-tabs"),
    tabs = document.querySelectorAll(".search-tab"),
    activeTab = document.querySelector(".search-tab.active");

  if (tabIndicator && tabsContainer) {
    const positionIndicator = (tab) => {
      if (!tab) return;
      const rect = tab.getBoundingClientRect(),
        tabsRect = tabsContainer.getBoundingClientRect();
      tabIndicator.style.width = `${rect.width}px`;
      tabIndicator.style.left = `${rect.left - tabsRect.left}px`;
    };

    if (activeTab) {
      positionIndicator(activeTab);
      tabIndicator.style.opacity = "1";
    }

    tabs.forEach((tab) => {
      tab.addEventListener("mouseenter", function () {
        positionIndicator(this);
        tabIndicator.style.opacity = "1";
      });
    });

    tabsContainer.addEventListener("mouseleave", () => {
      if (activeTab) {
        positionIndicator(activeTab);
      } else {
        tabIndicator.style.opacity = "0";
      }
    });
  }

  // 5. Indicateur de champ actif
  const searchInputs = document.querySelectorAll(".search-field input"),
    focusIndicator = document.querySelector(".field-focus-indicator"),
    fieldsContainer = document.querySelector(".search-fields-container");

  if (searchInputs.length && focusIndicator && fieldsContainer) {
    const positionFocusIndicator = (field) => {
      if (!field) return;
      const fieldRect = field.closest(".search-field").getBoundingClientRect(),
        containerRect = fieldsContainer.getBoundingClientRect();

      focusIndicator.style.width = `${fieldRect.width}px`;
      focusIndicator.style.height = `${fieldRect.height}px`;
      focusIndicator.style.left = `${fieldRect.left - containerRect.left}px`;
      focusIndicator.style.top = `${fieldRect.top - containerRect.top}px`;
      focusIndicator.style.opacity = "1";
    };

    searchInputs.forEach((input) => {
      input.addEventListener("focus", function () {
        positionFocusIndicator(this);
        if (
          this.id === "date-debut-visible" ||
          this.id === "date-fin-visible"
        ) {
          lastActiveDateField = this;
        }
      });
    });

    document.addEventListener("focusout", () => {
      setTimeout(() => {
        const calendarOpen = calendarDropdown?.classList.contains("active");

        if (calendarOpen && lastActiveDateField) {
          positionFocusIndicator(lastActiveDateField);
        } else if (!Array.from(searchInputs).includes(document.activeElement)) {
          focusIndicator.style.opacity = "0";
          lastActiveDateField = null;
        }
      }, 10);
    });
  }
});
