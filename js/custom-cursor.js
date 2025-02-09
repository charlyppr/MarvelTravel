const cursor = document.getElementById('custom-cursor');

let cursorX = 0, cursorY = 0;
let inactivityTimeout;
let targetX = window.innerWidth / 2, targetY = window.innerHeight / 2;
let velocityX = 0, velocityY = 0;
const inertia = 0.1; // Valeur de l'inertie
const bounceFactor = 0.68; // Facteur de rebond

function updateCursor() {

    velocityX += (targetX - cursorX) * inertia;
    velocityY += (targetY - cursorY) * inertia;

    cursorX += velocityX;
    cursorY += velocityY;

    cursor.style.left = `${cursorX}px`;
    cursor.style.top = `${cursorY}px`;

    velocityX *= bounceFactor;
    velocityY *= bounceFactor;

    requestAnimationFrame(updateCursor);
}


document.addEventListener('mousemove', (e) => {

    targetX = e.clientX;
    targetY = e.clientY;

    setTimeout(() => {
        cursor.style.opacity = '1';
    }, 50);

    clearTimeout(inactivityTimeout);
    inactivityTimeout = setTimeout(() => {
        cursor.style.opacity = '0';
    }, 2000);
});

updateCursor();
