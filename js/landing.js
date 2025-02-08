const pierres = document.querySelectorAll('.pierre');

document.addEventListener('mousemove', function(event) {
    mouseX = event.clientX;
    mouseY = event.clientY;

    const text = document.querySelector('.hero-text h1');
    const rect = text.getBoundingClientRect();
    const mouseTextX = mouseX - rect.left;
    const mouseTextY = mouseY - rect.top;


    let shadowX = (mouseTextX / rect.width) * 20;
    let shadowY = (mouseTextY / rect.height) * 20;


    const maxShadow = 30;
    shadowX = Math.min(Math.max(shadowX, -maxShadow), maxShadow);
    shadowY = Math.min(Math.max(shadowY, -maxShadow), maxShadow);


    text.style.textShadow = `${-shadowX / 8}px  ${-shadowY / 10}px 10px var(--couleur-secondaire),
                           ${-shadowX - 20}px ${-shadowY + 0}px 60px rgba(238, 102, 102, 0.4),
                           ${-shadowX - 0}px ${-shadowY + 20}px 80px rgba(141, 254, 2, 0.4)`;
});

let mouseX = 0;
let mouseY = 0;
let rotationAngle = 0;
let incrementing = true;

function rotatePierres() {
    pierres.forEach(function(pierre) {
        const pierreRect = pierre.getBoundingClientRect();
        const pierreX = pierreRect.left + pierreRect.width / 2;
        const pierreY = pierreRect.top + pierreRect.height / 2;

        const distanceX = mouseX - pierreX;
        const distanceY = mouseY - pierreY;

        const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);

        const moveFactor = (distance / 20);

        const deltaX = distanceX / (5 + moveFactor);
        const deltaY = distanceY / (5 + moveFactor);

        // Logique d'incrémentation ou de décrémentation de l'angle
        if (incrementing) {
            rotationAngle += 0.03; // Augmenter l'angle
            if (rotationAngle >= 1000) {
                incrementing = false; // Une fois qu'on atteint 3600, on commence à décrémenter
            }
        } else {
            rotationAngle -= 0.03; // Réduire l'angle
            if (rotationAngle <= 0) {
                incrementing = true; // Une fois qu'on atteint 0, on recommence à incrémenter
            }
        }

        // Appliquer la transformation de translation et de rotation
        pierre.style.transform = `translate(${deltaX}px, ${deltaY}px) rotate(${rotationAngle}deg)`;
    });

    // Demander au navigateur de rappeler cette fonction pour une animation fluide
    requestAnimationFrame(rotatePierres);
}

// Démarrer l'animation de rotation
rotatePierres();

