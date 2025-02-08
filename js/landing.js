const pierres = document.querySelectorAll('.pierre');

document.addEventListener('mousemove', function(event) {
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

    const text = document.querySelector('.hero-text h1');
    const rect = text.getBoundingClientRect();
    const mouseX = event.clientX - rect.left;
    const mouseY = event.clientY - rect.top;

    // Calculer les valeurs de mouvement de l'ombre
    let shadowX = (mouseX / rect.width) * 20;
    let shadowY = (mouseY / rect.height) * 20;

    // Limiter les valeurs de l'ombre
    const maxShadow = 30;
    shadowX = Math.min(Math.max(shadowX, -maxShadow), maxShadow);
    shadowY = Math.min(Math.max(shadowY, -maxShadow), maxShadow);

    // Appliquer le mouvement au texte
    text.style.textShadow = `${-shadowX / 8}px  ${-shadowY / 10}px 10px var(--couleur-secondaire),
                           ${-shadowX - 20}px ${-shadowY + 0}px 60px rgba(238, 102, 102, 0.4),
                           ${-shadowX - 0}px ${-shadowY + 20}px 80px rgba(141, 254, 2, 0.4)`;
});

