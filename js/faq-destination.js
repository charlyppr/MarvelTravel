document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les éléments ayant la classe "question"
    var questions = document.querySelectorAll('.question');

    // Pour chaque question, ajoute un écouteur d'événement sur le clic
    questions.forEach(function(question) {
        question.addEventListener('click', function() {
            // On suppose que l'élément suivant est la réponse
            var answer = this.nextElementSibling;
            // Si la réponse est visible, on la masque, sinon on l'affiche
            if (answer.style.display === 'block') {
                answer.style.display = 'none';
            } else {
                answer.style.display = 'block';
            }
        });
    });
});
