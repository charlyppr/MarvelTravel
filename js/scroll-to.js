const moreContainer = document.querySelector('.more-container');
const confianceSection = document.querySelector('.partenaires');

moreContainer.addEventListener('click', function() {
    confianceSection.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
});



const cards = document.querySelectorAll('.card');

cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
        let rect = card.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top; 
        
        let centerX = rect.width / 2;
        let centerY = rect.height / 2;
        
        let rotateX = (y - centerY) / 20;
        let rotateY = (centerX - x) / 20;

        let shadowX = (centerX - x) / 25;
        let shadowY = (centerY - y) / 25;
    
        
        card.style.transition = `transform 0.2s linear`;
        card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        card.style.boxShadow = `${-shadowX}px ${-shadowY}px 20px #f2dbaf30`;

    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = `rotateX(0deg) rotateY(0deg)`;
        card.style.transition = `transform 0.2s linear, box-shadow 1s ease-in-out`;
        setTimeout(() => {
            card.style.boxShadow = `0px 0px 20px #f2dbaf30`;
        }, 100);
    });
});