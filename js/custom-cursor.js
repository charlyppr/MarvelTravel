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

function updateCursorContent(content) {
    styleSheet.sheet.deleteRule(0);
    styleSheet.sheet.insertRule(`
        .default::before {
            content: "${content}";
            font-size: ${content ? '1em' : '0em'};
            transition: font-size 0.3s ease-out;
        }
    `, 0);
}

document.querySelectorAll('.github-ibra').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        document.querySelector('.default').classList.add('link-hover');
        document.querySelector('.github-ibra').style.cursor = 'none';
        updateCursorContent("Ibrahima");
    });

    link.addEventListener('mouseleave', function () {
        document.querySelector('.default').classList.remove('link-hover');
        document.querySelector('.github-ibra').style.cursor = 'auto';
        updateCursorContent("");
    });
});

document.querySelectorAll('.github-paul').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        document.querySelector('.default').classList.add('link-hover');
        document.querySelector('.github-paul').style.cursor = 'none';
        updateCursorContent("Paul");
    });

    link.addEventListener('mouseleave', function () {
        document.querySelector('.default').classList.remove('link-hover');
        document.querySelector('.github-paul').style.cursor = 'auto';
        updateCursorContent("");
    });
});

document.querySelectorAll('.github-charly').forEach(function (link) {
    link.addEventListener('mouseenter', function () {
        document.querySelector('.default').classList.add('link-hover');
        document.querySelector('.github-charly').style.cursor = 'none';
        updateCursorContent("Charly");
    });

    link.addEventListener('mouseleave', function () {
        document.querySelector('.default').classList.remove('link-hover');
        document.querySelector('.github-charly').style.cursor = 'auto';
        updateCursorContent("");
    });
});
