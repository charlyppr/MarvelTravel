let mar = document.querySelector('.mar-intro');
let tra = document.querySelector('.tra-intro');
let vel = document.querySelector('.vel-intro');
let logo_gauche = document.querySelectorAll('.logo-gauche-intro span');
let logo_container = document.querySelector('.logo-container-intro');
let intro = document.querySelector('.intro');
let nav = document.querySelector('.nav');

window.addEventListener('DOMContentLoaded', async () => {
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    document.body.style.overflow = 'hidden';

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

    document.body.style.overflow = 'auto';
});

let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    let scrollTop = document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop) {
        nav.style.transform = 'translateY(-100%)';
    } else {
        nav.style.transform = 'translateY(0)';
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});

