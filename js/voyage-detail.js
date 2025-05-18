document.addEventListener("DOMContentLoaded", () => {
  // Éléments du DOM
  const tabButtons = document.querySelectorAll(".tab-button");
  const tabContents = document.querySelectorAll(".tab-content");
  const tabContainer = document.querySelector(".tab-buttons") || document.body;
  const carousel = document.querySelector(".carousel-container");

  // Configuration des onglets
  if (tabButtons.length && tabContents.length) {
    tabContainer.addEventListener("click", (e) => {
      const button = e.target.closest(".tab-button");
      if (!button) return;

      tabButtons.forEach((btn) => btn.classList.remove("active"));
      tabContents.forEach((content) => content.classList.remove("active"));

      button.classList.add("active");

      const tabId = button.getAttribute("data-tab");
      document.getElementById(tabId + "-content").classList.add("active");
    });
  }

  // Configuration du carousel
  if (carousel) {
    let currentIndex = 0;
    let intervalId = null;
    const autoPlayInterval = 5000;

    const track = carousel.querySelector(".carousel-track");
    const slides = Array.from(carousel.querySelectorAll(".carousel-slide"));
    const dotsContainer = carousel.querySelector(".carousel-dots") || carousel;
    const nextButton = carousel.querySelector(".next-button");
    const prevButton = carousel.querySelector(".prev-button");
    const dots = Array.from(carousel.querySelectorAll(".dot"));

    if (!track || !slides.length) return;

    const moveToSlide = (index) => {
      track.style.transform = `translateX(-${index * 100}%)`;

      dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
      });

      currentIndex = index;
    };

    const getNextIndex = () => (currentIndex + 1) % slides.length;
    const getPrevIndex = () =>
      (currentIndex - 1 + slides.length) % slides.length;

    const stopAutoPlay = () => clearInterval(intervalId);

    const startAutoPlay = () => {
      stopAutoPlay();
      intervalId = setInterval(
        () => moveToSlide(getNextIndex()),
        autoPlayInterval
      );
    };

    // Navigation par boutons
    if (nextButton) {
      nextButton.addEventListener("click", () => moveToSlide(getNextIndex()));
    }

    if (prevButton) {
      prevButton.addEventListener("click", () => moveToSlide(getPrevIndex()));
    }

    // Navigation par points
    dotsContainer.addEventListener("click", (e) => {
      const dot = e.target.closest(".dot");
      if (!dot) return;

      const index = dots.indexOf(dot);
      if (index !== -1) moveToSlide(index);
    });

    // Contrôle de l'autoplay
    startAutoPlay();
    carousel.addEventListener("mouseenter", stopAutoPlay);
    carousel.addEventListener("mouseleave", startAutoPlay);

    // Initialisation du premier slide
    moveToSlide(0);
  }
});
