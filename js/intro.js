const mar = document.querySelector('.mar-intro');
const tra = document.querySelector('.tra-intro');
const vel = document.querySelector('.vel-intro');
const logo_gauche = document.querySelectorAll('.logo-gauche-intro span');
const logo_container = document.querySelector('.logo-container-intro');
const intro = document.querySelector('.intro');
const nav = document.querySelector('.nav');

window.addEventListener('DOMContentLoaded', async () => {
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
    const cursor = document.querySelector('.default');
    const [navigationEntry] = performance.getEntriesByType('navigation');

    if (navigationEntry && navigationEntry.type === 'reload') {
        intro.style.display = 'block';
        document.body.style.overflow = 'hidden';
        cursor.classList.add('hidden'); 

        await delay(500);
        mar.style.transform = 'translateX(0)';

        await delay(300);
        vel.style.transform = 'translateX(0)';

        await delay(500);
        tra.style.transform = 'translateX(0)';

        await delay(1500);
        logo_gauche.forEach(element => {
            element.style.fontSize = '1.6rem';
        });
        vel.style.fontSize = '4rem';
        logo_container.style.gap = '3px';

        await delay(300);
        logo_container.style.top = '0%';
        logo_container.style.left = '0%';
        logo_container.style.transform = 'translate(0, 0)';
        logo_container.style.padding = '20px';
        nav.style.transform = 'translateY(0)';

        await delay(100);
        intro.style.top = '-100vh';
        
        await delay(400);
        logo_container.style.opacity = '0';
        logo_container.style.position = 'relative';

        cursor.classList.remove('hidden');   

        document.body.style.overflow = 'auto';
    } else {
        intro.style.display = 'none';
    }
});