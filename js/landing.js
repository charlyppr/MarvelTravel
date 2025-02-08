document.addEventListener('mousemove', function(event) {
    const pierres = document.querySelectorAll('.pierre');
    
    pierres.forEach(function(pierre) {
        // Calculer la position de la souris
        const mouseX = event.clientX;
        const mouseY = event.clientY;

        // Obtenir la position actuelle de la pierre
        const pierreRect = pierre.getBoundingClientRect();
        const pierreX = pierreRect.left + pierreRect.width / 2;
        const pierreY = pierreRect.top + pierreRect.height / 2;

        // Calculer la distance entre la souris et la pierre
        const distanceX = mouseX - pierreX;
        const distanceY = mouseY - pierreY;

        // Calculer la distance totale entre la souris et la pierre
        const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);

        // Utiliser une fonction exponentielle pour augmenter l'effet du mouvement
        const moveFactor = Math.min(distance / 20, 120);

        // Calculer les décalages, amplifiés par la distance
        const deltaX = distanceX / (10 + moveFactor);
        const deltaY = distanceY / (10 + moveFactor);

        pierre.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
    });
});
  



const text = document.querySelector('.hero-text h1');
let lastShadow = '';

// Lorsque la souris entre dans la section nav et landing
document.querySelector('.nav').addEventListener('mouseenter', function() {
    document.addEventListener('mousemove', applyShadow);
});

document.querySelector('.landing').addEventListener('mouseenter', function() {
    document.addEventListener('mousemove', applyShadow);
});

// Lorsque la souris quitte la section landing
document.querySelector('.landing').addEventListener('mouseleave', function() {
    // Ne pas réinitialiser l'ombre, mais la garder telle qu'elle était
    document.removeEventListener('mousemove', applyShadow);
    // Garder l'ombre actuelle
    text.style.textShadow = lastShadow;
});

// Appliquer l'ombre en fonction de la position de la souris
function applyShadow(e) {
    const rect = text.getBoundingClientRect();

    // Position de la souris par rapport à la page
    const mouseX = e.clientX;
    const mouseY = e.clientY;

    // Calculer les valeurs de mouvement de l'ombre
    const shadowX = (mouseX / rect.width) * 20;
    const shadowY = (mouseY / rect.height) * 20;

    // Calculer l'ombre en fonction de la position de la souris
    const newShadow = `${-shadowX / 10}px  ${-shadowY / 10}px 8px var(--couleur-secondaire),
                       ${-shadowX - 20}px ${-shadowY + 0}px 60px rgba(238, 102, 102, 0.4),
                       ${-shadowX - 0}px ${-shadowY + 20}px 80px rgba(141, 254, 2, 0.4)`;

    // Appliquer l'ombre et conserver son état
    text.style.textShadow = newShadow;

    // Conserver l'ombre actuelle pour la réutiliser après le leave
    lastShadow = newShadow;
}
