const moreContainer = document.querySelector('.more-container');
const confianceSection = document.querySelector('.partenaires');

moreContainer.addEventListener('click', function() {
    confianceSection.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
});


const sections = document.querySelectorAll('.stack-section');
const wrapper = document.querySelector('.stack-wrapper');

const sectionHeight = window.innerHeight;

function updateSections() {
    const scrollTop = wrapper ? window.scrollY - wrapper.offsetTop : 0;
    
    sections.forEach((section, index) => {
        const progress = Math.max(0, (scrollTop - (index * sectionHeight)) / sectionHeight);

        if (progress <= 1) {
            const scale = 1 - (progress * 0.01);
            const y = progress * progress * 50;
            section.style.transform = `scale(${scale}) translateY(-${y}px)`;
            section.style.opacity = 1;
        }
    });
}

let ticking = false;
window.addEventListener('scroll', () => {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            updateSections();
            ticking = false;
        });
        ticking = true;
    }
});

updateSections();
