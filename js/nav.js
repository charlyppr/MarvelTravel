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

        menuItems.forEach(item => {
            item.addEventListener('mouseover', () => {
                menuItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.add('hovered');
                    }
                });
            });

            item.addEventListener('mouseout', () => {
                menuItems.forEach(otherItem => {
                    otherItem.classList.remove('hovered');
                });
            });
        });
    }
});