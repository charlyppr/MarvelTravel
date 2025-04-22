document.addEventListener("DOMContentLoaded", function () {
  // Éléments DOM
  const dateDebutVisible = document.getElementById("date-debut-visible");
  const dateFinVisible = document.getElementById("date-fin-visible");
  const dateDebutInput = document.getElementById("date-debut");
  const dateFinInput = document.getElementById("date-fin");
  const calendarDropdown = document.getElementById("calendar-dropdown");
  const prevMonthBtn = document.querySelector(".prev-month");
  const nextMonthBtn = document.querySelector(".next-month");
  const monthContainers = document.querySelectorAll(".month-container");
  const resetDatesBtn = document.getElementById("reset-dates");
  const applyDatesBtn = document.getElementById("apply-dates");

  // Variables d'état
  let currentDate = new Date();
  let selectedStartDate = null;
  let selectedEndDate = null;
  let editMode = null;

  if (dateDebutInput.value) {
    selectedStartDate = new Date(dateDebutInput.value);
  }
  if (dateFinInput.value) {
    selectedEndDate = new Date(dateFinInput.value);
  }

  // Mettre à jour l'affichage des dates
  updateInputDisplay();
  
 

  // Écouteurs d'événements
  dateDebutVisible.addEventListener("click", function() {
    editMode = 'start';
    toggleCalendar();
  });
  
  dateFinVisible.addEventListener("click", function() {
    // Si aucune date de début n'est sélectionnée, agir comme si on cliquait sur le champ de date de début
    if (!selectedStartDate) {
      editMode = 'start';
      toggleCalendar();
      return;
    }
    
    editMode = 'end';
    toggleCalendar();
    
    // Appliquer prévisualisation pour tous les jours après la date de début
    if (selectedStartDate) {
      setTimeout(() => {
        applyEndDatePreview();
      }, 100);
    }
  });
  
  prevMonthBtn.addEventListener("click", showPreviousMonths);
  nextMonthBtn.addEventListener("click", showNextMonths);

  // Écouteur pour le bouton de réinitialisation
  if (resetDatesBtn) {
    resetDatesBtn.addEventListener("click", resetDates);
  }

  // Écouteur pour le bouton d'application
  if (applyDatesBtn) {
    applyDatesBtn.addEventListener("click", submitForm);
  }

  // Fonction pour soumettre le formulaire
  function submitForm() {
    // Soumettre seulement si au moins une date de début est sélectionnée
    if (selectedStartDate) {
      const form = dateDebutInput.closest("form");
      if (form) {
        // S'assurer que le formulaire inclut l'ancre dans l'action
        if (!form.action.includes("#")) {
          form.action = form.action + "#toutes-destinations";
        }
        form.submit();
      }
    }

    initCalendars();
  }

  // Fonction pour réinitialiser les dates
  function resetDates(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Effacer les dates sélectionnées
    selectedStartDate = null;
    selectedEndDate = null;
    editMode = 'start'; // Mode de sélection de date d'arrivée

    // Effacer les valeurs des champs
    dateDebutInput.value = "";
    dateFinInput.value = "";
    dateDebutVisible.value = "";
    dateFinVisible.value = "";

    // Masquer le bouton de réinitialisation
    if (resetDatesBtn) {
      resetDatesBtn.style.display = "none";
    }

    // Réinitialiser les calendriers mais garder le dropdown ouvert
    if (e) {
      // Ne pas fermer le calendrier lors de la réinitialisation
      initCalendars();
      
      // Mettre à jour le message d'instruction
      updateSelectionMessage("Sélectionnez la date d'arrivée");
    }
  }

  // Fermer le dropdown en cliquant à l'extérieur
  document.addEventListener("click", function (e) {
    if (
      !calendarDropdown.contains(e.target) &&
      e.target !== dateDebutVisible &&
      e.target !== dateFinVisible &&
      calendarDropdown.classList.contains("active")
    ) {
      calendarDropdown.classList.remove("active");
    }
  });

  // Fermer le dropdown en cliquant sur n'importe quel input
  document.addEventListener("focusin", function (e) {
    if (
      e.target.tagName === "INPUT" &&
      e.target !== dateDebutVisible &&
      e.target !== dateFinVisible &&
      calendarDropdown.classList.contains("active")
    ) {
      calendarDropdown.classList.remove("active");
    }
  });

  // Initialiser les calendriers
  initCalendars();

  // Fonctions
  function toggleCalendar() {
    calendarDropdown.classList.toggle("active");
    if (calendarDropdown.classList.contains("active")) {
      // Réinitialiser au mois courant lors de l'ouverture
      currentDate = new Date();
      initCalendars();
    }
  }

  function initCalendars() {
    // Premier mois = mois courant
    const firstMonth = new Date(currentDate);

    // Second mois = mois suivant
    const secondMonth = new Date(currentDate);
    secondMonth.setMonth(secondMonth.getMonth() + 1);

    // Afficher les deux calendriers
    renderMonth(monthContainers[0], firstMonth);
    renderMonth(monthContainers[1], secondMonth);
  }

  function renderMonth(container, date) {
    // Mettre à jour le nom du mois
    const monthNames = [
      "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
      "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
    ];
    container.querySelector(".month-name").textContent = 
      `${monthNames[date.getMonth()]} ${date.getFullYear()}`;

    // Récupérer la grille pour afficher les jours
    const grid = container.querySelector(".calendar-grid");

    // Supprimer les éléments de jour existants
    const dayElements = grid.querySelectorAll(".calendar-day");
    dayElements.forEach((el) => el.remove());

    // Obtenir le nombre de jours dans le mois
    const lastDay = new Date(
      date.getFullYear(),
      date.getMonth() + 1,
      0
    ).getDate();

    // Obtenir le jour de la semaine du premier jour (0 = Dimanche, ..., 6 = Samedi)
    let firstDayOfWeek = new Date(
      date.getFullYear(),
      date.getMonth(),
      1
    ).getDay();
    // Convertir en index basé sur lundi (0 = Lundi, ..., 6 = Dimanche)
    firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

    // Date actuelle pour la mise en évidence "aujourd'hui"
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Ajouter des cellules vides pour les jours avant le premier jour du mois
    for (let i = 0; i < firstDayOfWeek; i++) {
      const emptyDay = document.createElement("div");
      emptyDay.classList.add("calendar-day", "empty");
      grid.appendChild(emptyDay);
    }

    // Ajouter les jours du mois
    for (let day = 1; day <= lastDay; day++) {
      const dayElement = document.createElement("div");
      dayElement.classList.add("calendar-day");
      dayElement.textContent = day;

      // Créer un objet date pour ce jour
      const currentDayDate = new Date(date.getFullYear(), date.getMonth(), day);

      // Vérifier si ce jour est dans le passé
      const isPastDay = currentDayDate < today;
      
      // Vérifier si cette date est avant la date de début en mode édition de fin
      const isBeforeStartDateInEndMode = editMode === 'end' && selectedStartDate && currentDayDate < selectedStartDate;
      
      if (isPastDay || isBeforeStartDateInEndMode) {
        dayElement.classList.add("past-day");
        // Rendre non-cliquable
        dayElement.style.opacity = "0.5";
        dayElement.style.cursor = "not-allowed";
      } else {
        // Ajouter l'événement de clic uniquement pour les jours non passés
        dayElement.addEventListener("click", () =>
          handleDayClick(currentDayDate)
        );

        // Ajouter des événements de survol pour l'aperçu de la plage sélectionnée
        if ((selectedStartDate && !selectedEndDate) || editMode === 'end') {
          dayElement.addEventListener("mouseover", () => {
            // Afficher l'aperçu uniquement si la date survolée est après la date de début
            if (currentDayDate > selectedStartDate) {
              highlightRange(selectedStartDate, currentDayDate);
            }
          });

          dayElement.addEventListener("mouseout", () => {
            // Supprimer les surlignages d'aperçu
            clearHighlights();
            renderCalendars();
          });
        }
      }

      // Mettre en évidence aujourd'hui
      if (
        currentDayDate.getFullYear() === today.getFullYear() &&
        currentDayDate.getMonth() === today.getMonth() &&
        currentDayDate.getDate() === today.getDate()
      ) {
        dayElement.classList.add("today");
      }

      // Vérifier si ce jour est sélectionné
      if (isDateInRange(currentDayDate)) {
        dayElement.classList.add("in-range");
      }

      if (isSameDate(currentDayDate, selectedStartDate)) {
        dayElement.classList.add("start-date");
      }

      if (isSameDate(currentDayDate, selectedEndDate)) {
        dayElement.classList.add("end-date");
      }

      grid.appendChild(dayElement);
    }
  }

  function handleDayClick(date) {
    if (editMode === 'end' && selectedStartDate) {
      // Mode édition de date de fin
      if (date < selectedStartDate) {
        // Si on clique sur une date avant la date d'arrivée, modifier la date d'arrivée
        selectedStartDate = date;
        selectedEndDate = null;
        
        // Mettre à jour l'affichage
        updateSelectionMessage("Sélectionnez la date de fin");
        
        // Réappliquer les aperçus
        renderCalendars();
        setTimeout(() => {
          applyEndDatePreview();
        }, 100);
        
        return;
      }
      
      // Empêcher de sélectionner la même date pour l'arrivée et le départ
      if (isSameDate(date, selectedStartDate)) {
        return; // Ignorer la sélection si c'est la même date
      }
      
      // Mettre à jour la date de fin
      selectedEndDate = date;
      
      // Mettre à jour les champs
      applyDates();
      
      // Fermer le calendrier
      calendarDropdown.classList.remove("active");
      editMode = null;
    } else if (
      !selectedStartDate ||
      (selectedStartDate && selectedEndDate) ||
      date < selectedStartDate
    ) {
      // Si on commence une nouvelle sélection ou sélection existante complète
      if (selectedStartDate && selectedEndDate) {
        resetDates();
      }

      // Nouvelle sélection
      selectedStartDate = date;
      selectedEndDate = null;

      // Mettre à jour le message d'instruction
      updateSelectionMessage("Sélectionnez la date de fin");
    } else {
      // Empêcher de sélectionner la même date pour l'arrivée et le départ
      if (isSameDate(date, selectedStartDate)) {
        return; // Ignorer la sélection si c'est la même date
      }
      
      // Compléter la sélection
      selectedEndDate = date;

      // Mettre à jour le message d'instruction
      updateSelectionMessage("");

      // Mettre à jour les champs cachés
      applyDates();

      // Fermer le calendrier
      calendarDropdown.classList.remove("active");
      editMode = null;
    }

    renderCalendars();
  }

  function renderCalendars() {
    // Réafficher les deux calendriers
    const firstMonth = new Date(currentDate);
    const secondMonth = new Date(currentDate);
    secondMonth.setMonth(secondMonth.getMonth() + 1);

    renderMonth(monthContainers[0], firstMonth);
    renderMonth(monthContainers[1], secondMonth);
  }

  function showPreviousMonths() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendars();
  }

  function showNextMonths() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendars();
  }

  function isDateInRange(date) {
    if (!selectedStartDate || !selectedEndDate) return false;
    return date >= selectedStartDate && date <= selectedEndDate;
  }

  function isSameDate(date1, date2) {
    if (!date1 || !date2) return false;
    return (
      date1.getFullYear() === date2.getFullYear() &&
      date1.getMonth() === date2.getMonth() &&
      date1.getDate() === date2.getDate()
    );
  }

  function applyDates() {
    if (selectedStartDate) {
      // Mettre à jour les champs cachés au format ISO YYYY-MM-DD
      let finalStartDate = new Date(selectedStartDate);
      let finalEndDate = selectedEndDate ? new Date(selectedEndDate) : null;

      dateDebutInput.value = formatDateForInput(finalStartDate);

      if (finalEndDate) {
        dateFinInput.value = formatDateForInput(finalEndDate);
      } else {
        dateFinInput.value = "";
      }

      // Mettre à jour l'affichage des champs
      updateInputDisplay();
    } else {
      dateDebutInput.value = "";
      dateFinInput.value = "";
      dateDebutVisible.value = "";
      dateFinVisible.value = "";
    }
  }

  function formatDateForInput(date) {
    if (!date) return "";
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  function updateInputDisplay() {
    const formatOptions = { day: "numeric", month: "short" };

    if (selectedStartDate) {
      const startStr = selectedStartDate.toLocaleDateString(
        "fr-FR",
        formatOptions
      );
      dateDebutVisible.value = startStr;

      // Afficher le bouton de réinitialisation si nous avons une date de début
      if (resetDatesBtn) {
        resetDatesBtn.style.display = "block";
      }
    } else {
      dateDebutVisible.value = "";
    }

    if (selectedEndDate) {
      const endStr = selectedEndDate.toLocaleDateString("fr-FR", formatOptions);
      dateFinVisible.value = endStr;
    } else {
      dateFinVisible.value = "";
    }

    // Masquer le bouton si aucune date
    if (!selectedStartDate && !selectedEndDate && resetDatesBtn) {
      resetDatesBtn.style.display = "none";
    }
  }

  // Fonction pour surligner temporairement une plage pour l'aperçu au survol
  function highlightRange(startDate, endDate) {
    const allDays = document.querySelectorAll(".calendar-day:not(.empty)");

    allDays.forEach((dayElement) => {
      // Supprimer d'abord toutes les classes d'aperçu
      dayElement.classList.remove("preview-in-range", "preview-end-date");

      // Ignorer les jours vides
      if (dayElement.classList.contains("empty")) return;

      // Obtenir le numéro du jour
      const dayNumber = parseInt(dayElement.textContent);
      if (isNaN(dayNumber)) return;

      // Trouver le conteneur de mois auquel appartient ce jour
      const monthContainer = dayElement.closest(".month-container");
      const monthName = monthContainer.querySelector(".month-name").textContent;
      const [monthStr, yearStr] = monthName.split(" ");

      const monthNames = [
        "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
        "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
      ];
      const monthIndex = monthNames.indexOf(monthStr);
      const year = parseInt(yearStr);

      if (monthIndex === -1 || isNaN(year)) return;

      // Créer un objet date pour ce jour
      const currentDate = new Date(year, monthIndex, dayNumber);

      // Si cette date est dans la plage d'aperçu, ajouter des classes
      if (currentDate > startDate && currentDate < endDate) {
        dayElement.classList.add("preview-in-range");
      }

      if (isSameDate(currentDate, endDate)) {
        dayElement.classList.add("preview-end-date");
      }
    });
  }

  // Fonction pour effacer les surlignages temporaires
  function clearHighlights() {
    const previewElements = document.querySelectorAll(
      ".preview-in-range, .preview-end-date"
    );
    previewElements.forEach((el) => {
      el.classList.remove("preview-in-range", "preview-end-date");
    });
  }

  // Fonction pour mettre à jour le message de sélection
  function updateSelectionMessage(message) {
    // Vérifier si l'élément de message existe, le créer si non
    let messageEl = document.querySelector(".calendar-selection-message");
    if (!messageEl && message) {
      messageEl = document.createElement("div");
      messageEl.className = "calendar-selection-message";
      const calendarHeader = document.querySelector(".calendar-footer");
      calendarHeader.insertAdjacentElement("afterend", messageEl);
    }

    // Mettre à jour ou supprimer le message
    if (messageEl) {
      if (message) {
        messageEl.textContent = message;
        messageEl.style.display = "block";
      } else {
        messageEl.style.display = "none";
      }
    }
  }

  // Fonction pour prévisualiser les dates pour le mode d'édition de date de fin
  function applyEndDatePreview() {
    if (editMode !== 'end' || !selectedStartDate) return;
    
    const allDays = document.querySelectorAll(".calendar-day:not(.empty):not(.past-day)");
    
    allDays.forEach((dayElement) => {
      // Ignorer les jours vides
      if (dayElement.classList.contains("empty")) return;

      // Obtenir le numéro du jour
      const dayNumber = parseInt(dayElement.textContent);
      if (isNaN(dayNumber)) return;

      // Trouver le conteneur de mois auquel appartient ce jour
      const monthContainer = dayElement.closest(".month-container");
      const monthName = monthContainer.querySelector(".month-name").textContent;
      const [monthStr, yearStr] = monthName.split(" ");

      const monthNames = [
        "Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
        "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
      ];
      const monthIndex = monthNames.indexOf(monthStr);
      const year = parseInt(yearStr);

      if (monthIndex === -1 || isNaN(year)) return;

      // Créer un objet date pour ce jour
      const currentDate = new Date(year, monthIndex, dayNumber);
      
      // Ajouter les événements d'aperçu pour tous les jours après la date de début
      if (currentDate > selectedStartDate) {
        dayElement.addEventListener("mouseover", () => {
          highlightRange(selectedStartDate, currentDate);
        });

        dayElement.addEventListener("mouseout", () => {
          clearHighlights();
          renderCalendars();
        });

        // Si c'est le jour juste après la date de début, simuler un survol
        if (currentDate.getTime() === new Date(selectedStartDate.getTime() + 24*60*60*1000).getTime()) {
          // Simuler un hover sur cette date pour montrer un aperçu initial
          setTimeout(() => {
            highlightRange(selectedStartDate, currentDate);
          }, 50);
        }
      }
    });
  }
});
