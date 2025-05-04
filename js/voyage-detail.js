document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Show corresponding content
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-content').classList.add('active');
        });
    });

    // Carousel functionality
    const carousel = document.querySelector('.carousel-container');
    if (carousel) {
        const track = carousel.querySelector('.carousel-track');
        const slides = Array.from(carousel.querySelectorAll('.carousel-slide'));
        const nextButton = carousel.querySelector('.next-button');
        const prevButton = carousel.querySelector('.prev-button');
        const dots = Array.from(carousel.querySelectorAll('.dot'));

        let currentIndex = 0;

        // Function to move to specific slide
        const moveToSlide = (index) => {
            // Update track position for sliding animation
            track.style.transform = `translateX(-${index * 100}%)`;

            // Update active dot
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            // Update current index
            currentIndex = index;
        };

        // Event listeners for buttons
        nextButton.addEventListener('click', () => {
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
        });

        prevButton.addEventListener('click', () => {
            const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
            moveToSlide(prevIndex);
        });

        // Event listeners for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                moveToSlide(index);
            });
        });

        // Auto-play functionality
        let intervalId;

        const startAutoPlay = () => {
            intervalId = setInterval(() => {
                const nextIndex = (currentIndex + 1) % slides.length;
                moveToSlide(nextIndex);
            }, 5000);
        };

        const stopAutoPlay = () => {
            clearInterval(intervalId);
        };

        // Start autoplay
        startAutoPlay();

        // Pause autoplay on hover
        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);

        // Initialize first slide
        moveToSlide(0);
    }
}); 