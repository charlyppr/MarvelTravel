document.addEventListener("DOMContentLoaded", function () {
  // Gestion des suggestions de destination
  const destinationInput = document.getElementById("destination-search");
  const suggestionsContainer = document.getElementById(
    "destination-suggestions"
  );
  const dateDebutInput = document.getElementById("date-debut-visible");
  const calendarDropdown = document.getElementById("calendar-dropdown");

  // Variables pour suivre l'état de sélection des dates
  let startDateSelected = false;
  let endDateSelected = false;

  // Fonction pour faire défiler la page vers un élément spécifique
  function scrollToElement(element, offset = 200) {
    if (element) {
      const rect = element.getBoundingClientRect();
      const scrollTop =
        window.pageYOffset || document.documentElement.scrollTop;
      const targetPosition = scrollTop + rect.top - offset;

      window.scrollTo({
        top: targetPosition,
        behavior: "smooth",
      });
    }
  }

  if (destinationInput && suggestionsContainer) {
    // Afficher les suggestions au clic sur l'input
    destinationInput.addEventListener("click", function (e) {
      suggestionsContainer.style.display = "block";
      // Faire défiler jusqu'aux suggestions
      setTimeout(() => scrollToElement(suggestionsContainer), 100);
      e.stopPropagation(); // Empêcher la propagation pour éviter la fermeture immédiate
    });

    // Afficher les suggestions quand l'input reçoit le focus
    destinationInput.addEventListener("focus", function () {
      suggestionsContainer.style.display = "block";
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
      const searchTerm = this.value.toLowerCase();

      // Si l'entrée est vide, afficher toutes les suggestions
      if (searchTerm === "") {
        document.querySelectorAll(".suggestion-item").forEach((item) => {
          item.style.display = "flex";
        });
        suggestionsContainer.style.display = "block";
        return;
      }

      // Filtrer les suggestions
      let hasMatches = false;
      document.querySelectorAll(".suggestion-item").forEach((item) => {
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
