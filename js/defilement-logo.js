const logos = document.querySelectorAll('.companie-logo');
const logoContainer = document.querySelector('.companie-logo-container');

let scrollAmount = 0;
let scrollSpeed = 0.7;

function scrollLogos() {
    scrollAmount += scrollSpeed;

    if (scrollAmount >= logoContainer.scrollWidth / 2) {
        scrollAmount = 0;
    }

    logoContainer.scrollLeft = scrollAmount;
    requestAnimationFrame(scrollLogos);
}

window.addEventListener('load', () => {
    const cloneLogos = () => {
        logos.forEach(logo => {
            const clone = logo.cloneNode(true);
            logoContainer.appendChild(clone);
        });
    };

    cloneLogos();
    cloneLogos();
    scrollLogos();
});

logoContainer.addEventListener('mouseover', () => {
    scrollSpeed = 0.3;
});

logoContainer.addEventListener('mouseout', () => {
    scrollSpeed = 0.7;
});
