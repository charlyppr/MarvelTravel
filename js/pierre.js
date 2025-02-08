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
  