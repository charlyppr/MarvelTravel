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

// Hauteur d'une section (100vh)
const sectionHeight = window.innerHeight;

function updateSections() {
    // Position du scroll par rapport au début du wrapper
    const scrollTop = window.pageYOffset - wrapper.offsetTop;
    
    sections.forEach((section, index) => {
        // Calculer la progression du scroll pour cette section
        const progress = Math.max(0, (scrollTop - (index * sectionHeight)) / sectionHeight);
        
        // Appliquer une transformation qui "écrase" la section
        // Plus on scrolle, plus la section se réduit en hauteur
        if (progress <= 1) {
            const scale = 1 - (progress * 0.01); // Réduire légèrement la taille
            const y = progress * progress * 50; // Déplacement vers le haut non linéaire
            section.style.transform = `scale(${scale}) translateY(-${y}px)`;
            section.style.opacity = 1;
        }
    });
}

// Écouter l'événement de scroll avec requestAnimationFrame pour de meilleures performances
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

// Initialiser les positions
updateSections();
