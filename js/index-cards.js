// Animation automatique des cartes Marvel dans la section hero-image

document.addEventListener('DOMContentLoaded', function () {
    // Sélectionne uniquement les cartes de destinations, pas la carte Marvel principale
    const cards = document.querySelectorAll('.card-stack .destination-card');
    if (!cards.length) return;

    let current = 0;
    
    function showCards() {
        cards.forEach((card, i) => {
            // Supprime toutes les classes d'état
            card.classList.remove('active', 'next', 'previous', 'back', 'leaving');
            
            const total = cards.length;
            
            // Calcul des positions relatives par rapport à la carte active
            // avec arithmétique modulaire pour créer un effet de carousel circulaire
            if (i === current) {
                card.classList.add('active');
            } 
            else if (i === (current + 1) % total) {
                card.classList.add('next');
            } 
            else if (i === (current + 2) % total) {
                card.classList.add('previous');
            }
            else if (i === (current + 3) % total) {
                card.classList.add('back');
            }
            else if (i === (current - 1 + total) % total) {
                // La carte qui vient de quitter la position active va "en dessous"
                card.classList.add('leaving');
            }
        });
    }
    
    // Affiche les cartes initiales
    showCards();
    
    // Fonction pour passer à la carte suivante avec une transition fluide
    function nextCard() {
        current = (current + 1) % cards.length;
        showCards();
    }

    let intervalId = setInterval(nextCard, 3000);
}); 