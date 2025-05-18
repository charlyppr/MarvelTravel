document.addEventListener("DOMContentLoaded", function () {
  // Constantes
  const MONTH_NAMES = [
    "Janvier",
    "Février",
    "Mars",
    "Avril",
    "Mai",
    "Juin",
    "Juillet",
    "Août",
    "Septembre",
    "Octobre",
    "Novembre",
    "Décembre",
  ];

  // Éléments DOM
  const dateDebutVisible = document.querySelector("#date-debut-visible");
  const dateFinVisible = document.querySelector("#date-fin-visible");
  const dateDebutInput = document.querySelector("#date-debut");
  const dateFinInput = document.querySelector("#date-fin");
  const calendarDropdown = document.querySelector("#calendar-dropdown");
  const prevMonthBtn = document.querySelector(".prev-month");
  const nextMonthBtn = document.querySelector(".next-month");
  const monthContainers = document.querySelectorAll(".month-container");
  const resetDatesBtn = document.querySelector("#reset-dates");
  const applyDatesBtn = document.querySelector("#apply-dates");

  // Variables d'état
  let currentDate = new Date(),
    selectedStartDate = dateDebutInput.value
      ? new Date(dateDebutInput.value)
      : null,
    selectedEndDate = dateFinInput.value ? new Date(dateFinInput.value) : null,
    editMode = null;

  // Démarrage
  updateInputDisplay();
  initCalendars();

  // Événements
  dateDebutVisible.addEventListener("click", () => {
    editMode = "start";
    toggleCalendar();
  });
  dateFinVisible.addEventListener("click", () => {
    editMode = !selectedStartDate ? "start" : "end";
    if (editMode === "end") setTimeout(applyEndDatePreview, 100);
    toggleCalendar();
  });

  prevMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendars();
  });
  nextMonthBtn.addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendars();
  });

  resetDatesBtn?.addEventListener("click", (e) => resetDates(e));
  applyDatesBtn?.addEventListener("click", submitForm);

  document.addEventListener("click", (e) => {
    if (
      !calendarDropdown.contains(e.target) &&
      e.target !== dateDebutVisible &&
      e.target !== dateFinVisible &&
      calendarDropdown.classList.contains("active")
    ) {
      calendarDropdown.classList.remove("active");
    }
  });

  document.addEventListener("focusin", (e) => {
    if (
      e.target.tagName === "INPUT" &&
      e.target !== dateDebutVisible &&
      e.target !== dateFinVisible &&
      calendarDropdown.classList.contains("active")
    ) {
      calendarDropdown.classList.remove("active");
    }
  });

  // Fonctions
  function toggleCalendar() {
    calendarDropdown.classList.toggle("active");
    if (calendarDropdown.classList.contains("active")) {
      currentDate = new Date();
      initCalendars();
    }
  }

  function initCalendars() {
    const firstMonth = new Date(currentDate),
      secondMonth = new Date(currentDate);
    secondMonth.setMonth(secondMonth.getMonth() + 1);
    renderMonth(monthContainers[0], firstMonth);
    renderMonth(monthContainers[1], secondMonth);
  }

  function renderCalendars() {
    const firstMonth = new Date(currentDate),
      secondMonth = new Date(firstMonth);
    secondMonth.setMonth(secondMonth.getMonth() + 1);
    renderMonth(monthContainers[0], firstMonth);
    renderMonth(monthContainers[1], secondMonth);
  }

  function renderMonth(container, date) {
    container.querySelector(".month-name").textContent = `${
      MONTH_NAMES[date.getMonth()]
    } ${date.getFullYear()}`;

    const grid = container.querySelector(".calendar-grid");
    grid.querySelectorAll(".calendar-day").forEach((el) => el.remove());

    // Calcul des jours
    const lastDay = new Date(
      date.getFullYear(),
      date.getMonth() + 1,
      0
    ).getDate();
    let firstDayOfWeek = new Date(
      date.getFullYear(),
      date.getMonth(),
      1
    ).getDay();
    firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Jours vides avant le début du mois
    for (let i = 0; i < firstDayOfWeek; i++)
      appendDayElement(grid, "", ["calendar-day", "empty"]);

    // Jours du mois
    for (let day = 1; day <= lastDay; day++) {
      const currentDayDate = new Date(date.getFullYear(), date.getMonth(), day);
      const isPastDay = currentDayDate < today;
      const isBeforeStartDateInEndMode =
        editMode === "end" &&
        selectedStartDate &&
        currentDayDate < selectedStartDate;
      const dayElement = appendDayElement(grid, day, ["calendar-day"]);

      if (isPastDay || isBeforeStartDateInEndMode) {
        dayElement.classList.add("past-day");
        dayElement.style.opacity = "0.5";
        dayElement.style.cursor = "not-allowed";
      } else {
        dayElement.addEventListener("click", () =>
          handleDayClick(currentDayDate)
        );

        if ((selectedStartDate && !selectedEndDate) || editMode === "end") {
          dayElement.addEventListener("mouseover", () => {
            if (currentDayDate > selectedStartDate)
              highlightRange(selectedStartDate, currentDayDate);
          });

          dayElement.addEventListener("mouseout", () => {
            clearHighlights();
            renderCalendars();
          });
        }
      }

      isSameDate(currentDayDate, today) && dayElement.classList.add("today");
      isDateInRange(currentDayDate) && dayElement.classList.add("in-range");
      isSameDate(currentDayDate, selectedStartDate) &&
        dayElement.classList.add("start-date");
      isSameDate(currentDayDate, selectedEndDate) &&
        dayElement.classList.add("end-date");
    }
  }

  function handleDayClick(date) {
    if (editMode === "end" && selectedStartDate) {
      if (date < selectedStartDate) {
        selectedStartDate = date;
        selectedEndDate = null;
        updateSelectionMessage("Sélectionnez la date de fin");
        renderCalendars();
        setTimeout(applyEndDatePreview, 100);
        return;
      }
      if (isSameDate(date, selectedStartDate)) return;
      selectedEndDate = date;
      applyDates();
      calendarDropdown.classList.remove("active");
      editMode = null;
    } else if (
      !selectedStartDate ||
      (selectedStartDate && selectedEndDate) ||
      date < selectedStartDate
    ) {
      if (selectedStartDate && selectedEndDate) resetDates();
      selectedStartDate = date;
      selectedEndDate = null;
      updateSelectionMessage("Sélectionnez la date de fin");
      applyDates();
    } else {
      if (isSameDate(date, selectedStartDate)) return;
      selectedEndDate = date;
      updateSelectionMessage("");
      applyDates();
      calendarDropdown.classList.remove("active");
      editMode = null;
    }
    renderCalendars();
  }

  function applyDates() {
    if (selectedStartDate) {
      dateDebutInput.value = formatForInput(selectedStartDate);
      dateFinInput.value = selectedEndDate
        ? formatForInput(selectedEndDate)
        : "";
      updateInputDisplay();
    } else {
      dateDebutInput.value =
        dateFinInput.value =
        dateDebutVisible.value =
        dateFinVisible.value =
          "";
    }
    validateForm();
  }

  function resetDates(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }

    selectedStartDate = selectedEndDate = null;
    editMode = "start";
    dateDebutInput.value =
      dateFinInput.value =
      dateDebutVisible.value =
      dateFinVisible.value =
        "";

    resetDatesBtn && (resetDatesBtn.style.display = "none");

    if (e) {
      initCalendars();
      updateSelectionMessage("Sélectionnez la date d'arrivée");
    }

    validateForm();
  }

  function updateInputDisplay() {
    dateDebutVisible.value = formatForDisplay(selectedStartDate);
    dateFinVisible.value = formatForDisplay(selectedEndDate);
    resetDatesBtn &&
      (resetDatesBtn.style.display = selectedStartDate ? "block" : "none");
  }

  function submitForm() {
    if (selectedStartDate) {
      const form = dateDebutInput.closest("form");
      if (form) {
        if (!form.action.includes("#")) form.action += "#toutes-destinations";
        form.submit();
      }
    }
  }

  function highlightRange(startDate, endDate) {
    const allDays = document.querySelectorAll(`.calendar-day:not(.empty)`);

    allDays.forEach((dayElement) => {
      dayElement.classList.remove("preview-in-range", "preview-end-date");
      if (dayElement.classList.contains("empty")) return;

      const dayNumber = parseInt(dayElement.textContent);
      if (isNaN(dayNumber)) return;

      const monthContainer = dayElement.closest(".month-container");
      const monthName = monthContainer.querySelector(".month-name").textContent;
      const [monthStr, yearStr] = monthName.split(" ");
      const monthIndex = MONTH_NAMES.indexOf(monthStr);
      const year = parseInt(yearStr);
      if (monthIndex === -1 || isNaN(year)) return;

      const currentDate = new Date(year, monthIndex, dayNumber);
      if (currentDate > startDate && currentDate < endDate)
        dayElement.classList.add("preview-in-range");
      if (isSameDate(currentDate, endDate))
        dayElement.classList.add("preview-end-date");
    });
  }

  function clearHighlights() {
    document
      .querySelectorAll(`.preview-in-range, .preview-end-date`)
      .forEach((el) =>
        el.classList.remove("preview-in-range", "preview-end-date")
      );
  }

  function updateSelectionMessage(message) {
    let messageEl = document.querySelector(".calendar-selection-message");

    if (!messageEl && message) {
      messageEl = document.createElement("div");
      messageEl.classList.add("calendar-selection-message");
      document
        .querySelector(".calendar-footer")
        .insertAdjacentElement("afterend", messageEl);
    }

    if (messageEl) {
      messageEl.textContent = message;
      messageEl.style.display = message ? "block" : "none";
    }
  }

  function applyEndDatePreview() {
    if (editMode !== "end" || !selectedStartDate) return;
  }

  function validateForm() {
    if (
      window.reservationManager &&
      typeof window.reservationManager.validateEtape1Form === "function"
    )
      window.reservationManager.validateEtape1Form();
    else if (typeof validateEtape1Form === "function") validateEtape1Form();
  }

  // Utilitaires
  function appendDayElement(grid, text, classes = []) {
    const element = document.createElement("div");
    if (classes.length) element.classList.add(...classes);
    if (text) element.textContent = text;
    grid.appendChild(element);
    return element;
  }

  function isDateInRange(date) {
    return (
      selectedStartDate &&
      selectedEndDate &&
      date >= selectedStartDate &&
      date <= selectedEndDate
    );
  }

  function isSameDate(date1, date2) {
    return (
      date1 &&
      date2 &&
      date1.getFullYear() === date2.getFullYear() &&
      date1.getMonth() === date2.getMonth() &&
      date1.getDate() === date2.getDate()
    );
  }

  function formatForInput(date) {
    if (!date) return "";
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  function formatForDisplay(date) {
    return date
      ? date.toLocaleDateString("fr-FR", { day: "numeric", month: "short" })
      : "";
  }
});
