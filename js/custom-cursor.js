const cursor = document.querySelector('.default');

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

window.addEventListener('load', () => {
    cursor.style.opacity = '0';
});

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




const styleSheet = document.createElement('style');
document.head.appendChild(styleSheet);

styleSheet.sheet.insertRule(`
    .default::before {
        content: "";
        font-size: 0em;
        transition: font-size 0.3s ease-out;
    }
`, styleSheet.sheet.cssRules.length);

document.querySelectorAll('.github-ibra').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        // Lorsque le curseur entre sur un lien avec la classe 'github-ibra'
        document.querySelector('.default').classList.add('link-hover');
        
        // Modifie le contenu et la taille de la police pour le lien 'ibra'
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "Ibrahima";
                font-size: 1em;
            }
        `, styleSheet.sheet.cssRules.length);
    });

    link.addEventListener('mouseleave', function () {
        // Lorsque le curseur quitte le lien avec la classe 'github-ibra'
        document.querySelector('.default').classList.remove('link-hover');
        
        // Restaure la taille de la police à 0em lorsque le curseur quitte le lien
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "";
                font-size: 0em;
            }
        `, styleSheet.sheet.cssRules.length);
    });
});

document.querySelectorAll('.github-paul').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        // Lorsque le curseur entre sur un lien avec la classe 'github-paul'
        document.querySelector('.default').classList.add('link-hover');
        
        // Modifie le contenu et la taille de la police pour le lien 'paul'
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "Paul";
                font-size: 1em;
            }
        `, styleSheet.sheet.cssRules.length);
    });

    link.addEventListener('mouseleave', function () {
        // Lorsque le curseur quitte le lien avec la classe 'github-paul'
        document.querySelector('.default').classList.remove('link-hover');
        
        // Restaure la taille de la police à 0em lorsque le curseur quitte le lien
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "";
                font-size: 0em;
            }
        `, styleSheet.sheet.cssRules.length);
    });
});

document.querySelectorAll('.github-charly').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        // Lorsque le curseur entre sur un lien avec la classe 'github-paul'
        document.querySelector('.default').classList.add('link-hover');
        
        // Modifie le contenu et la taille de la police pour le lien 'paul'
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "Charly";
                font-size: 1em;
            }
        `, styleSheet.sheet.cssRules.length);
    });

    link.addEventListener('mouseleave', function () {
        // Lorsque le curseur quitte le lien avec la classe 'github-paul'
        document.querySelector('.default').classList.remove('link-hover');
        
        // Restaure la taille de la police à 0em lorsque le curseur quitte le lien
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "";
                font-size: 0em;
            }
        `, styleSheet.sheet.cssRules.length);
    });
});

