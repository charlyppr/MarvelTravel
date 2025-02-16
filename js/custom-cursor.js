if (window.innerWidth > 768) {
    const cursor = document.querySelector('.default');

    let cursorX = 0, cursorY = 0;
    let inactivityTimeout;
    let targetX = window.innerWidth / 2, targetY = window.innerHeight / 2;
    let velocityX = 0, velocityY = 0;
    const inertia = 0.1; 
    const bounceFactor = 0.68; 

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
            white-space: nowrap;
            transition: font-size 0.3s ease-out;
        }
    `, styleSheet.sheet.cssRules.length);

    function updateCursorContent(content) {
        styleSheet.sheet.deleteRule(0);
        styleSheet.sheet.insertRule(`
            .default::before {
                content: "${content}";
                font-size: ${content ? '1em' : '0em'};
                white-space: nowrap;
                transition: font-size 0.3s ease-out;
            }
        `, 0);
    }

    styleSheet.sheet.insertRule(`
        .link-hover {
            width: 0;
            height: 0;
            transition: width 0.3s ease-out, height 0.3s ease-out;
        }
    `, styleSheet.sheet.cssRules.length);

    function updateCursorSize(content) {
        const width = content ? `${content.length}ch` : '0';
        const height = content ? `2.5em` : '0';
        styleSheet.sheet.deleteRule(1);
        styleSheet.sheet.insertRule(`
            .link-hover {
                width: ${width};
                height: ${height};
                transition: width 0.3s ease-out, height 0.3s ease-out;
            }
        `, 1);
    }

    document.querySelectorAll('.ibra').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            document.querySelector('.default').classList.add('link-hover');
            document.querySelector('.ibra').style.cursor = 'none';
            updateCursorSize("Ibrahima");
            updateCursorContent("Ibrahima");
        });

        link.addEventListener('mouseleave', function () {
            document.querySelector('.default').classList.remove('link-hover');
            document.querySelector('.ibra').style.cursor = 'auto';
            updateCursorSize("");
            updateCursorContent("");
        });
    });

    document.querySelectorAll('.paul').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            document.querySelector('.default').classList.add('link-hover');
            document.querySelector('.paul').style.cursor = 'none';
            updateCursorSize("Paul");
            updateCursorContent("Paul");
        });

        link.addEventListener('mouseleave', function () {
            document.querySelector('.default').classList.remove('link-hover');
            document.querySelector('.paul').style.cursor = 'auto';
            updateCursorSize("");
            updateCursorContent("");
        });
    });

    document.querySelectorAll('.charly').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            document.querySelector('.default').classList.add('link-hover');
            document.querySelector('.charly').style.cursor = 'none';
            updateCursorSize("Charly");
            updateCursorContent("Charly");
        });

        link.addEventListener('mouseleave', function () {
            document.querySelector('.default').classList.remove('link-hover');
            document.querySelector('.charly').style.cursor = 'auto';
            updateCursorSize("");
            updateCursorContent("");
        });
    });

    document.querySelectorAll('.logo-container').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            document.querySelector('.default').classList.add('link-hover');
            document.querySelector('.logo-container').style.cursor = 'none';
            updateCursorSize("Accueil");
            updateCursorContent("Accueil");
        });

        link.addEventListener('mouseleave', function () {
            document.querySelector('.default').classList.remove('link-hover');
            document.querySelector('.logo-container').style.cursor = 'auto';
            updateCursorSize("");
            updateCursorContent("");
        });
    });
}