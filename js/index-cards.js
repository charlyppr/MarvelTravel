document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".card-stack .destination-card");
  if (!cards.length) return;

  let currentIndex = 0;
  let rotationTimer = null;
  const stateClasses = ["active", "next", "previous", "back", "leaving"];
  const container = document.querySelector(".card-stack");

  function updateCardsState() {
    const total = cards.length;
    cards.forEach((card, index) => {
      stateClasses.forEach((state) => card.classList.remove(state));

      if (index === currentIndex) card.classList.add("active");
      else if (index === (currentIndex + 1) % total) card.classList.add("next");
      else if (index === (currentIndex + 2) % total)
        card.classList.add("previous");
      else if (index === (currentIndex + 3) % total) card.classList.add("back");
      else if (index === (currentIndex - 1 + total) % total)
        card.classList.add("leaving");
    });
  }

  function rotateCards(pause = false) {
    if (pause && rotationTimer) {
      clearInterval(rotationTimer);
      rotationTimer = null;
    } else if (!pause && !rotationTimer) {
      rotationTimer = setInterval(() => {
        currentIndex = (currentIndex + 1) % cards.length;
        updateCardsState();
      }, 3000);
    }
  }

  if (container) {
    container.addEventListener("mouseenter", () => rotateCards(true));
    container.addEventListener("mouseleave", () => rotateCards(false));
  }

  updateCardsState();
  rotateCards();
});
