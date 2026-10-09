let currentSlide = 0;
const slides = document.querySelectorAll('.slide');

function changeSlide(direction) {
  slides[currentSlide].classList.remove('active');
  currentSlide = (currentSlide + direction + slides.length) % slides.length;
  slides[currentSlide].classList.add('active');
}

let currentAdventureSlide = 0;
const adventureSlides = document.querySelectorAll('.adventure-slide');
const contentSlides = document.querySelectorAll('.content-slide');

function changeAdventureSlide() {
  adventureSlides[currentAdventureSlide].classList.remove('active');
  contentSlides[currentAdventureSlide].classList.remove('active');
  
  currentAdventureSlide = (currentAdventureSlide + 1) % adventureSlides.length;
  
  adventureSlides[currentAdventureSlide].classList.add('active');
  contentSlides[currentAdventureSlide].classList.add('active');
}

// Navigation Menu JavaScript
(function() {
    const navbar = document.querySelector('.navbar');
    const menuToggle = document.querySelector('.menu-toggle');
    const menuOverlay = document.querySelector('.menu-overlay');
    const menuLinks = document.querySelectorAll('.nav-menu a');

    // Toggle Menu
    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        navbar.classList.toggle('menu-active'); 
    });

    // Close menu on link click
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            menuOverlay.classList.remove('active');
            navbar.classList.remove('menu-active');
        });
    });
    
    menuOverlay.addEventListener('click', (e) => {
        if (e.target === menuOverlay || e.target.closest('.menu-left')) {
            menuToggle.classList.remove('active');
            menuOverlay.classList.remove('active');
            navbar.classList.remove('menu-active');
        }
    });
})();
