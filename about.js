// Subtle scroll animations
document.addEventListener('DOMContentLoaded', function () {

  // Founders data
  const foundersData = [
    {
      image: 'images/img.jpeg',
      name: 'Sam Agri Group',
      role: 'Parent Company',
      bio: 'A prominent integrated agricultural group since 1996, pioneering exports of premium Indian fresh produce to global markets and establishing trust over 25 years.',
      bottomName: 'Mr. K. N. Rao',
      bottomRole: 'Technical Director',
      bottomBio: 'K.N. Rao is the visionary who inspired Sam\'s journey into winemaking by recognizing the potential of transforming premium pomegranate harvests into world-class value-added products. With over 35 years of expertise in agriculture, food processing, research, and product innovation, he laid the technical and strategic foundation for Sam\'s Wine. His passion for quality, innovation, and sustainable value creation continues to shape our commitment to crafting distinctive fruit wines from the finest produce.'
    },
    {
      image: 'images/strawberry vertical (2).png',
      name: 'Viraj Deore',
      role: 'Head of Wine Business',
      bio: 'Viraj Deore heads the Wine Business at Sam\'s Wine, leading the brand from concept to market. He has been instrumental in establishing the winery, developing award-worthy fruit wines, implementing world-class production systems, securing regulatory approvals, and expanding the brand\'s presence through tourism, retail, and strategic partnerships. With international winemaking experience and a passion for innovation, he is committed to redefining India\'s fruit wine industry through quality, authenticity, and sustainable growth.',
      bottomName: 'Sarah Johnson',
      bottomRole: 'Cellar Master',
      bottomBio: 'With 15 years of experience in wine production, Sarah oversees every aspect of our fermentation and aging processes. Her meticulous attention to detail ensures each vintage meets our exacting standards.'
    },
    {
      image: 'images/pome3.png',
      name: 'Elena Martinez',
      role: 'Head Winemaker',
      bio: 'With a master\'s degree in viticulture and 20 years of international experience, Elena brings innovation and precision to every vintage. Her expertise in terroir expression has earned numerous awards for our wines.',
      bottomName: 'Michael Torres',
      bottomRole: 'Operations Manager',
      bottomBio: 'Leading our day-to-day operations with precision and care, Michael ensures that every aspect of our winery runs smoothly. His dedication to excellence is reflected in every bottle we produce.'
    }
  ];

  let currentFounderIndex = 0;

  // Founder navigation
  const founderNavBtn = document.querySelector('.founder-nav-btn');
  const founderImage = document.querySelector('.founder-image');
  const founderOverlayName = document.querySelector('.founder-overlay-name');
  const founderOverlayRole = document.querySelector('.founder-overlay-role');
  const founderBottomName = document.querySelector('.founder-bottom-name');
  const founderBottomRole = document.querySelector('.founder-bottom-role');
  const founderBottomBio = document.querySelector('.founder-bottom-bio');

  function updateFounder(index) {
    const founder = foundersData[index];
    if (founderImage && founderOverlayName && founderOverlayRole && founderBottomName && founderBottomRole && founderBottomBio) {
      founderImage.style.opacity = '0';

      setTimeout(() => {
        founderImage.src = founder.image;
        founderOverlayName.textContent = founder.name;
        founderOverlayRole.textContent = founder.role;
        founderBottomName.textContent = founder.bottomName;
        founderBottomRole.textContent = founder.bottomRole;
        founderBottomBio.textContent = founder.bottomBio;
        founderImage.style.opacity = '1';
      }, 300);
    }
  }

  if (founderNavBtn) {
    founderNavBtn.addEventListener('click', () => {
      currentFounderIndex = (currentFounderIndex + 1) % foundersData.length;
      updateFounder(currentFounderIndex);
    });
  }

  // Scroll reveal animation
  const scrollElements = document.querySelectorAll('.text-content, .craft-text, .family-card, .philosophy-item');

  const elementInView = (el, dividend = 1) => {
    const elementTop = el.getBoundingClientRect().top;
    return (
      elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend
    );
  };

  const displayScrollElement = (element) => {
    element.classList.add('scroll-element', 'visible');
  };

  const hideScrollElement = (element) => {
    element.classList.remove('visible');
  };

  const handleScrollAnimation = () => {
    scrollElements.forEach((el) => {
      if (elementInView(el, 1.25)) {
        displayScrollElement(el);
      }
    });
  };

  window.addEventListener('scroll', () => {
    handleScrollAnimation();
  });

  // Initial check
  handleScrollAnimation();

  // Subtle parallax for hero, Orchards, and founders sections
  let ticking = false;

  function updateParallax() {
    const scrolled = window.pageYOffset;
    const heroImage = document.querySelector('.hero-image img');
    const OrchardsImage = document.querySelector('.Orchards-image img');
    const founderImageCard = document.querySelector('.founder-image-card');
    const founderStoryPanel = document.querySelector('.founder-story-panel');

    if (heroImage) {
      const speed = 0.5;
      const yPos = -(scrolled * speed);
      heroImage.style.transform = `translateY(${yPos}px)`;
    }

    if (OrchardsImage) {
      const speed = 0.5;
      const yPos = -(scrolled * speed);
      OrchardsImage.style.transform = `translateY(${yPos}px)`;
    }

    // Founders scroll motion
    if (founderImageCard && founderStoryPanel) {
      const foundersSection = document.querySelector('.founders');
      if (foundersSection) {
        const rect = foundersSection.getBoundingClientRect();
        const windowHeight = window.innerHeight;

        if (rect.top < windowHeight && rect.bottom > 0) {
          const scrollProgress = Math.max(0, Math.min(1, (windowHeight - rect.top) / (windowHeight + rect.height)));

          const imageMove = scrollProgress * -20;
          const panelMove = scrollProgress * 15;

          founderImageCard.style.transform = `translateY(${imageMove}px)`;
          founderStoryPanel.style.transform = `translateY(${panelMove}px)`;
        }
      }
    }

    ticking = false;
  }

  function requestTick() {
    if (!ticking) {
      requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }

  window.addEventListener('scroll', requestTick);

  // ==========================================
  // DREAMING BIG / WINE SLIDER
  // ==========================================
  const dreamingSlidesData = [
    {
      img: "images/edited/1 edited.png",
      caption: "Signature handcrafted Indian fruit wines, celebrating authentic regional produce.",
      alt: "Sam's Wine signature fruit wine collection"
    },
    {
      img: "images/sam's winery/jamun2 edited.png",
      caption: "Wild Jamun wine — deep, fruit-forward notes that linger on the palate.",
      alt: "Jamun wine bottle and tasting presentation"
    },
    {
      img: "images/edited/Mahuawine (1).jpg",
      caption: "Ancient Mahua flower wine — a rare indigenous heritage brought to life.",
      alt: "Mahua flower wine bottle"
    },
    {
      img: "images/edited/pomo(4) edited.jpg",
      caption: "Pure pomegranate fruit wine, rich in natural character and complexity.",
      alt: "Pomegranate wine bottle"
    },
    {
      img: "images/edited/Sam's Product Creative-39.jpg",
      caption: "Curated fruit and flower wines crafted with passion and precision.",
      alt: "Sam's Wine creative product showcase"
    },
    {
      img: "images/edited/strawberrywine (4).jpg",
      caption: "Hand-picked Mahabaleshwar strawberries, naturally fermented to perfection.",
      alt: "Strawberry wine bottle"
    },
    {
      img: "images/edited/strawberrybottle1 edited.jpeg",
      caption: "Vibrant, aromatic, and refreshing — an exquisite berry wine experience.",
      alt: "Strawberry fruit wine bottle"
    }
  ];

  const dreamingTrack = document.getElementById('dreamingCarouselTrack');
  const dreamingPrevBtn = document.getElementById('dreamingPrevBtn');
  const dreamingNextBtn = document.getElementById('dreamingNextBtn');
  const dreamingCaption = document.getElementById('dreamingActiveCaption');

  if (dreamingTrack && dreamingSlidesData.length > 0) {
    const N = dreamingSlidesData.length;
    // Render 3 sets of slides for seamless circular infinite navigation
    const totalSets = 3;
    const allSlides = [];

    for (let s = 0; s < totalSets; s++) {
      dreamingSlidesData.forEach((item, dataIndex) => {
        const slideEl = document.createElement('div');
        slideEl.className = 'dreaming-slide';
        slideEl.dataset.index = dataIndex;

        const img = document.createElement('img');
        img.src = item.img;
        img.alt = item.alt || item.caption;
        img.loading = 'lazy';
        slideEl.appendChild(img);

        dreamingTrack.appendChild(slideEl);
        allSlides.push(slideEl);
      });
    }

    // Start with middle set, first slide (index N)
    let currentSlideIdx = N;
    let isTransitioning = false;

    function centerSlide(index, animate = true) {
      if (!allSlides[index]) return;

      allSlides.forEach((slide) => slide.classList.remove('active'));
      allSlides[index].classList.add('active');

      const containerWidth = dreamingTrack.parentElement.offsetWidth;
      const slideLeft = allSlides[index].offsetLeft;
      const slideWidth = allSlides[index].offsetWidth;
      const offset = containerWidth / 2 - (slideLeft + slideWidth / 2);

      if (!animate) {
        dreamingTrack.style.transition = 'none';
      } else {
        dreamingTrack.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1)';
      }

      dreamingTrack.style.transform = `translateX(${offset}px)`;

      // Update caption
      const realIndex = index % N;
      if (dreamingCaption) {
        dreamingCaption.style.opacity = '0';
        setTimeout(() => {
          dreamingCaption.textContent = dreamingSlidesData[realIndex].caption;
          dreamingCaption.style.opacity = '1';
        }, 160);
      }
    }

    function goToNext() {
      if (isTransitioning) return;
      isTransitioning = true;
      currentSlideIdx++;
      centerSlide(currentSlideIdx, true);
    }

    function goToPrev() {
      if (isTransitioning) return;
      isTransitioning = true;
      currentSlideIdx--;
      centerSlide(currentSlideIdx, true);
    }

    dreamingTrack.addEventListener('transitionend', () => {
      isTransitioning = false;
      if (currentSlideIdx >= N * 2) {
        currentSlideIdx -= N;
        centerSlide(currentSlideIdx, false);
      } else if (currentSlideIdx < N) {
        currentSlideIdx += N;
        centerSlide(currentSlideIdx, false);
      }
    });

    if (dreamingNextBtn) {
      dreamingNextBtn.addEventListener('click', () => {
        goToNext();
        resetAutoSlide();
      });
    }

    if (dreamingPrevBtn) {
      dreamingPrevBtn.addEventListener('click', () => {
        goToPrev();
        resetAutoSlide();
      });
    }

    // Allow clicking any visible slide to jump to it
    allSlides.forEach((slide, idx) => {
      slide.addEventListener('click', () => {
        if (hasDragged) return;
        currentSlideIdx = idx;
        centerSlide(currentSlideIdx, true);
        resetAutoSlide();
      });
    });

    // Touch & mouse drag support
    let startX = 0;
    let currentDragX = 0;
    let isDragging = false;
    let dragStartOffset = 0;
    let hasDragged = false;

    function getTrackCurrentX() {
      const match = dreamingTrack.style.transform.match(/translateX\(([-\d.]+)px\)/);
      return match ? parseFloat(match[1]) : 0;
    }

    const wrapper = dreamingTrack.parentElement;

    wrapper.addEventListener('pointerdown', (e) => {
      if (e.target.closest('.dreaming-nav-btn')) return;
      stopAutoSlide();
      isDragging = true;
      hasDragged = false;
      startX = e.clientX;
      currentDragX = e.clientX;
      dragStartOffset = getTrackCurrentX();
      dreamingTrack.classList.add('is-dragging');
      wrapper.setPointerCapture(e.pointerId);
    });

    wrapper.addEventListener('pointermove', (e) => {
      if (!isDragging) return;
      currentDragX = e.clientX;
      const deltaX = currentDragX - startX;
      if (Math.abs(deltaX) > 6) {
        hasDragged = true;
      }
      dreamingTrack.style.transform = `translateX(${dragStartOffset + deltaX}px)`;
    });

    wrapper.addEventListener('pointerup', (e) => {
      if (!isDragging) return;
      isDragging = false;
      dreamingTrack.classList.remove('is-dragging');
      const deltaX = currentDragX - startX;
      if (deltaX < -50) {
        goToNext();
      } else if (deltaX > 50) {
        goToPrev();
      } else {
        centerSlide(currentSlideIdx, true);
      }
      resetAutoSlide();
    });

    wrapper.addEventListener('pointercancel', () => {
      if (!isDragging) return;
      isDragging = false;
      dreamingTrack.classList.remove('is-dragging');
      centerSlide(currentSlideIdx, true);
      resetAutoSlide();
    });

    // Keyboard arrow navigation
    window.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') {
        goToNext();
        resetAutoSlide();
      } else if (e.key === 'ArrowLeft') {
        goToPrev();
        resetAutoSlide();
      }
    });

    // ==========================================
    // AUTO-SLIDE ROTATION (5 Seconds)
    // ==========================================
    const AUTO_SLIDE_INTERVAL = 5000; // 5 seconds
    let autoSlideTimer = null;
    let isCarouselHovered = false;

    function startAutoSlide() {
      stopAutoSlide();
      autoSlideTimer = setInterval(() => {
        if (!isCarouselHovered && !isDragging && !isTransitioning) {
          goToNext();
        }
      }, AUTO_SLIDE_INTERVAL);
    }

    function stopAutoSlide() {
      if (autoSlideTimer) {
        clearInterval(autoSlideTimer);
        autoSlideTimer = null;
      }
    }

    function resetAutoSlide() {
      stopAutoSlide();
      startAutoSlide();
    }

    // Pause on hover
    wrapper.addEventListener('mouseenter', () => {
      isCarouselHovered = true;
      stopAutoSlide();
    });

    wrapper.addEventListener('mouseleave', () => {
      isCarouselHovered = false;
      startAutoSlide();
    });

    // Pause when user switches tabs or minimizes window
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoSlide();
      } else if (!isCarouselHovered) {
        startAutoSlide();
      }
    });

    // Start auto-slide when carousel enters viewport
    if ('IntersectionObserver' in window) {
      const carouselObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            startAutoSlide();
          } else {
            stopAutoSlide();
          }
        });
      }, { threshold: 0.2 });

      carouselObserver.observe(wrapper);
    } else {
      startAutoSlide();
    }

    // Handle image load & window resize to ensure exact centering
    window.addEventListener('resize', () => {
      centerSlide(currentSlideIdx, false);
    });

    // Initial positioning once images layout
    setTimeout(() => {
      centerSlide(currentSlideIdx, false);
    }, 50);

    const firstImgs = dreamingTrack.querySelectorAll('img');
    let loadedCount = 0;
    firstImgs.forEach((img) => {
      if (img.complete) {
        loadedCount++;
      } else {
        img.addEventListener('load', () => {
          centerSlide(currentSlideIdx, false);
        });
      }
    });
    if (loadedCount === firstImgs.length) {
      centerSlide(currentSlideIdx, false);
    }
  }
});
