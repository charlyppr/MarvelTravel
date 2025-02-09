const navBar = document.querySelector('.nav');

let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    let scrollTop = document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop) {
        navBar.style.transform = 'translateY(-100%)';
    } else {
        navBar.style.transform = 'translateY(0)';
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});