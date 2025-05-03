document.addEventListener("DOMContentLoaded", function () {
  // Gestion des suggestions de destination
  const destinationInput = document.getElementById("destination-search");
  const suggestionsContainer = document.getElementById(
    "destination-suggestions"
  );
  const dateDebutInput = document.getElementById("date-debut-visible");
  const calendarDropdown = document.getElementById("calendar-dropdown");
  const sortSelect = document.getElementById("sort");

  // Variables pour suivre l'état de sélection des dates
  let startDateSelected = false;
  let endDateSelected = false;

  // Fonction pour faire défiler la page vers un élément spécifique
  function scrollToElement(element, offset = 200) {
    if (element) {
      const rect = element.getBoundingClientRect();
      const scrollTop = document.documentElement.scrollTop;
      const targetPosition = scrollTop + rect.top - offset;

      window.scrollTo({
        top: targetPosition,
        behavior: "smooth",
      });
    }
  }

  // Fonction pour extraire le prix d'une carte
  const getPrice = (card) => {
    const priceElement = card.querySelector(".price-value");
    if (priceElement) {
      const priceText = priceElement.textContent.trim();
      return parseFloat(priceText.replace(/[^\d,]/g, "").replace(",", "."));
    }
    return 0;
  };

  // Fonction pour extraire le titre d'une carte
  const getTitle = (card) => {
    const titleElement = card.querySelector(".card-title");
    return titleElement ? titleElement.textContent.trim().toLowerCase() : "";
  };

  // Fonction pour extraire la note d'une carte
  const getRating = (card) => {
    const ratingElement = card.querySelector(
      ".card-rating span, .rating-count span"
    );
    if (ratingElement) {
      const ratingText = ratingElement.textContent.trim();
      return parseFloat(ratingText);
    }
    return 0;
  };

  // Fonction pour trier les cartes
  function sortCards(sortValue) {
    // Sélectionner uniquement les cartes dans la section "toutes les destinations"
    const container = document.querySelector(
      "#toutes-destinations .all-destination-cards"
    );
    if (!container) return;

    const cards = Array.from(container.querySelectorAll(".destination-card"));
    if (cards.length === 0) return;

    // Trier les cartes selon la valeur sélectionnée
    cards.sort((a, b) => {
      switch (sortValue) {
        case "price-asc":
          return getPrice(a) - getPrice(b);
        case "price-desc":
          return getPrice(b) - getPrice(a);
        case "name-asc":
          return getTitle(a).localeCompare(getTitle(b));
        case "popular":
          return getRating(b) - getRating(a);
        default:
          return 0;
      }
    });

    // Réorganiser les cartes dans le DOM
    cards.forEach((card) => {
      container.appendChild(card);
    });
  }

  // Initialiser le tri par popularité au chargement
  sortCards("popular");

  // Mettre à jour le select pour refléter le tri initial
  if (sortSelect) {
    sortSelect.value = "popular";
    sortSelect.addEventListener("change", function () {
      sortCards(this.value);
    });
  }

  if (destinationInput && suggestionsContainer) {
    // Fonction pour limiter l'affichage des suggestions
    function limitSuggestions(isSearching = false) {
      // Sélectionner toutes les suggestions de destination
      const destinationItems = document.querySelectorAll('.suggestion-item:not(.category-suggestion)');
      // Sélectionner les items de catégorie
      const categoryItems = document.querySelectorAll('.suggestion-item.category-suggestion');
      
      // Si on n'est pas en train de rechercher, limiter à 5 destinations
      if (!isSearching) {
        destinationItems.forEach((item, index) => {
          item.style.display = index < 5 ? 'flex' : 'none';
        });
      } else {
        // En mode recherche, montrer toutes les destinations correspondantes
        destinationItems.forEach(item => {
          item.style.display = 'flex';
        });
      }
      
      // Toujours limiter à 3 catégories
      categoryItems.forEach((item, index) => {
        item.style.display = index < 3 ? 'flex' : 'none';
      });
    }

    // Afficher les suggestions au clic sur l'input
    destinationInput.addEventListener("click", function (e) {
      suggestionsContainer.style.display = "block";
      // Limiter les suggestions initiales
      limitSuggestions(false);
      // Faire défiler jusqu'aux suggestions
      setTimeout(() => scrollToElement(suggestionsContainer), 100);
      e.stopPropagation(); // Empêcher la propagation pour éviter la fermeture immédiate
    });

    // Afficher les suggestions quand l'input reçoit le focus
    destinationInput.addEventListener("focus", function () {
      suggestionsContainer.style.display = "block";
      // Limiter les suggestions initiales
      limitSuggestions(false);
      // Faire défiler jusqu'aux suggestions
      setTimeout(() => scrollToElement(suggestionsContainer), 100);
    });

    // Fonction pour ouvrir le calendrier après sélection d'une destination
    function selectDestinationAndOpenCalendar(destinationName) {
      destinationInput.value = destinationName;
      suggestionsContainer.style.display = "none";

      // Focus sur le champ de date et ouvrir le calendrier
      if (dateDebutInput) {
        setTimeout(() => {
          dateDebutInput.focus();
          // Ouvrir le calendrier en ajoutant la classe active
          if (calendarDropdown) {
            calendarDropdown.classList.add("active");
            // Faire défiler jusqu'au calendrier
            setTimeout(() => scrollToElement(calendarDropdown), 100);
            // Réinitialiser l'état de sélection des dates
            startDateSelected = false;
            endDateSelected = false;
          }
        }, 100);
      }
    }

    // Gérer la sélection d'une suggestion
    document.querySelectorAll(".suggestion-item").forEach((item) => {
      item.addEventListener("click", function () {
        const destinationName =
          this.querySelector("h4").textContent.split(",")[0]; // Prendre seulement le nom de la ville
        selectDestinationAndOpenCalendar(destinationName);
      });
    });

    // Gérer la touche Entrée dans le champ de recherche
    destinationInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault(); // Empêcher la soumission du formulaire

        // Vérifier si une suggestion est visible et correspondante
        let matchingSuggestion = null;
        const searchTerm = this.value.toLowerCase();

        document.querySelectorAll(".suggestion-item").forEach((item) => {
          const itemName = item.querySelector("h4").textContent.toLowerCase();
          if (
            itemName.includes(searchTerm) &&
            item.style.display !== "none" &&
            !matchingSuggestion
          ) {
            matchingSuggestion = item;
          }
        });

        if (matchingSuggestion) {
          const destinationName = matchingSuggestion
            .querySelector("h4")
            .textContent.split(",")[0];
          selectDestinationAndOpenCalendar(destinationName);
        } else if (this.value.trim() !== "") {
          // Si aucune suggestion ne correspond mais que le champ n'est pas vide
          selectDestinationAndOpenCalendar(this.value);
        }
      }
    });

    // Fermer les suggestions quand on clique ailleurs
    document.addEventListener("click", function (e) {
      if (
        !destinationInput.contains(e.target) &&
        !suggestionsContainer.contains(e.target)
      ) {
        suggestionsContainer.style.display = "none";
      }
    });

    // Gérer la recherche en temps réel
    destinationInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase().trim();

      // Si l'entrée est vide, afficher les suggestions limitées
      if (searchTerm === "") {
        document.querySelectorAll(".suggestion-item").forEach((item) => {
          item.style.display = "flex";
        });
        suggestionsContainer.style.display = "block";
        limitSuggestions(false);
        return;
      }

      // Filtrer les suggestions
      let hasMatches = false;
      
      // Séparons les destinations et les catégories pour une meilleure gestion
      const destinationItems = document.querySelectorAll('.suggestion-item:not(.category-suggestion)');
      const categoryItems = document.querySelectorAll('.suggestion-item.category-suggestion');
      
      // Filtrer les destinations
      destinationItems.forEach((item) => {
        const itemName = item.querySelector("h4").textContent.toLowerCase();
        const itemDesc = item.querySelector("p").textContent.toLowerCase();
        
        if (itemName.includes(searchTerm) || itemDesc.includes(searchTerm)) {
          item.style.display = "flex";
          hasMatches = true;
        } else {
          item.style.display = "none";
        }
      });
      
      // Filtrer les catégories
      categoryItems.forEach((item) => {
        const itemName = item.querySelector("h4").textContent.toLowerCase();
        
        if (itemName.includes(searchTerm)) {
          item.style.display = "flex";
          hasMatches = true;
        } else {
          item.style.display = "none";
        }
      });

      // Afficher ou masquer le conteneur de suggestions
      suggestionsContainer.style.display = hasMatches ? "block" : "none";
      
      // En mode recherche avec des résultats, ne pas limiter les résultats correspondants
      // (pas d'appel à limitSuggestions car nous voulons tous les résultats correspondants)
    });
  }

  // Gérer l'ouverture du calendrier quand on clique sur un champ de date
  if (dateDebutInput && calendarDropdown) {
    dateDebutInput.addEventListener("click", function () {
      calendarDropdown.classList.add("active");
      // Faire défiler jusqu'au calendrier
      setTimeout(() => scrollToElement(calendarDropdown), 100);
    });

    const dateFin = document.getElementById("date-fin-visible");
    if (dateFin) {
      dateFin.addEventListener("click", function () {
        calendarDropdown.classList.add("active");
        // Faire défiler jusqu'au calendrier
        setTimeout(() => scrollToElement(calendarDropdown), 100);
      });
    }
  }

  // Gérer la sélection des dates dans le calendrier
  if (calendarDropdown) {
    // Sélectionner tous les jours du calendrier
    const calendarDays = calendarDropdown.querySelectorAll(
      ".calendar-day:not(.empty):not(.past-day)"
    );

    calendarDays.forEach((day) => {
      day.addEventListener("click", function () {
        if (!startDateSelected) {
          // Première date sélectionnée
          startDateSelected = true;
          // Marquer visuellement cette date comme date de début
          this.classList.add("start-date");
        } else if (!endDateSelected) {
          // Deuxième date sélectionnée
          endDateSelected = true;
          // Marquer visuellement cette date comme date de fin
          this.classList.add("end-date");

          // Fermer le calendrier après une courte pause pour que l'utilisateur voie sa sélection
          setTimeout(() => {
            calendarDropdown.classList.remove("active");
            // Réinitialiser pour la prochaine utilisation
            startDateSelected = false;
            endDateSelected = false;
          }, 300);
        }
      });
    });
  }

  // Ajouter un gestionnaire pour le bouton d'application des dates si présent
  const applyDatesBtn = document.querySelector(".apply-dates");
  if (applyDatesBtn) {
    applyDatesBtn.addEventListener("click", function () {
      calendarDropdown.classList.remove("active");
      // Réinitialiser l'état de sélection
      startDateSelected = false;
      endDateSelected = false;
    });
  }

  // Empêcher la fermeture du calendrier lors des clics à l'intérieur
  if (calendarDropdown) {
    calendarDropdown.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }
});

// Effet de déplacement sur les onglets de catégorie
document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".search-tab");
  const indicator = document.querySelector(".tab-indicator");
  const tabsContainer = document.querySelector(".search-tabs");

  // Fonction pour positionner l'indicateur
  function positionIndicator(tab) {
    if (!tab || !indicator || !tabsContainer) return;

    const rect = tab.getBoundingClientRect();
    const tabsRect = tabsContainer.getBoundingClientRect();

    indicator.style.width = rect.width + "px";
    indicator.style.left = rect.left - tabsRect.left + "px";
  }

  // Positionner l'indicateur sous l'onglet actif au chargement
  const activeTab = document.querySelector(".search-tab.active");
  if (activeTab && indicator) {
    positionIndicator(activeTab);
    indicator.style.opacity = "1";
  }

  // Ajouter les événements de survol sur chaque onglet
  tabs.forEach((tab) => {
    tab.addEventListener("mouseenter", function () {
      positionIndicator(this);
      indicator.style.opacity = "1";
    });
  });

  // Quand on quitte la zone des onglets, remettre l'indicateur sous l'onglet actif
  if (tabsContainer) {
    tabsContainer.addEventListener("mouseleave", function () {
      if (activeTab) {
        positionIndicator(activeTab);
      } else {
        indicator.style.opacity = "0";
      }
    });
  }
});

// Effet de déplacement sur les champs de recherche
document.addEventListener("DOMContentLoaded", function () {
  const inputs = document.querySelectorAll(".search-field input");
  const fieldsContainer = document.querySelector(".search-fields-container");
  const focusIndicator = document.querySelector(".field-focus-indicator");
  const calendarDropdown = document.getElementById("calendar-dropdown");

  // Variable pour garder une référence au dernier champ de date actif
  let lastActiveDateField = null;

  // Fonction pour positionner l'indicateur de focus
  function positionFocusIndicator(field) {
    if (!field || !focusIndicator || !fieldsContainer) return;

    const fieldRect = field.closest(".search-field").getBoundingClientRect();
    const containerRect = fieldsContainer.getBoundingClientRect();

    focusIndicator.style.width = fieldRect.width + "px";
    focusIndicator.style.height = fieldRect.height + "px";
    focusIndicator.style.left = fieldRect.left - containerRect.left + "px";
    focusIndicator.style.top = fieldRect.top - containerRect.top + "px";
    focusIndicator.style.opacity = "1";
  }

  // Ajouter les gestionnaires d'événements pour chaque input
  if (inputs && focusIndicator) {
    inputs.forEach((input) => {
      // Quand l'input reçoit le focus
      input.addEventListener("focus", function () {
        positionFocusIndicator(this);

        // Si c'est un champ de date, enregistrer la référence
        if (
          this.id === "date-debut-visible" ||
          this.id === "date-fin-visible"
        ) {
          lastActiveDateField = this;
        }
      });
    });

    // Quand un input perd le focus
    document.addEventListener("focusout", function (e) {
      if (inputs && Array.from(inputs).includes(e.target)) {
        // Vérifier si le calendrier est ouvert
        setTimeout(() => {
          const calendarIsOpen =
            calendarDropdown && calendarDropdown.classList.contains("active");

          // Ne pas cacher l'indicateur si le calendrier est ouvert
          if (calendarIsOpen && lastActiveDateField) {
            // Maintenir l'indicateur sur le dernier champ de date actif
            positionFocusIndicator(lastActiveDateField);
          }
          // Sinon vérifier si un autre input a le focus
          else if (!Array.from(inputs).includes(document.activeElement)) {
            focusIndicator.style.opacity = "0";
            lastActiveDateField = null;
          }
        }, 10);
      }
    });
  }

  // Écouter la fermeture du calendrier pour réinitialiser l'indicateur si nécessaire
  if (calendarDropdown) {
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.attributeName === "class") {
          const isActive = calendarDropdown.classList.contains("active");

          // Si le calendrier vient d'être fermé
          if (!isActive && lastActiveDateField) {
            // Vérifier si un input a le focus
            if (!Array.from(inputs).includes(document.activeElement)) {
              focusIndicator.style.opacity = "0";
              lastActiveDateField = null;
            }
          }
        }
      });
    });

    observer.observe(calendarDropdown, { attributes: true });
  }
});
