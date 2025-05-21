document.addEventListener("DOMContentLoaded", function () {
  // Sélectionner toutes les questions FAQ
  const faqQuestions = document.querySelectorAll(".faq-question");

  // Ajouter un écouteur d'événement à chaque question
  faqQuestions.forEach((question) => {
    question.addEventListener("click", () => {
      // Récupérer l'élément parent (faq-item)
      const faqItem = question.parentElement;

      // Vérifier si la question est déjà active
      const isActive = faqItem.classList.contains("active");

      // Basculer l'état actif de la question cliquée
      if (isActive) {
        faqItem.classList.remove("active");
      } else {
        faqItem.classList.add("active");
      }
    });
  });
});
