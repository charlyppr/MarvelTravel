const moreContainer = document.querySelector('.more-container');
const confianceSection = document.querySelector('.confiance');

moreContainer.addEventListener('click', function() {
    confianceSection.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
});
