const navBar = document.querySelector('.nav');

let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    if (window.innerWidth > 768) {
        let scrollTop = document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop) {
            navBar.style.transform = 'translateY(-100%)';
        } else {
            navBar.style.transform = 'translateY(0)';
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    if (window.innerWidth > 768) {
        const menuItems = document.querySelectorAll('.menu-li');
        const cartIcon = document.querySelector('.cart-icon');

        const handleHover = (hoveredElement, isEntering) => {
            menuItems.forEach(item => {
                if (item !== hoveredElement) {
                    item.classList.toggle('hovered', isEntering);
                }
            });
            if (hoveredElement !== cartIcon) {
                cartIcon.classList.toggle('hovered', isEntering);
            }
        };

        menuItems.forEach(item => {
            item.addEventListener('mouseover', () => handleHover(item, true));
            item.addEventListener('mouseout', () => handleHover(item, false));
        });

        cartIcon.addEventListener('mouseover', () => handleHover(cartIcon, true));
        cartIcon.addEventListener('mouseout', () => handleHover(cartIcon, false));
    }
});