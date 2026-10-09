// ===== WINE SLIDER DATA =====
const wineData = [
  { image: 'images/jamun1.jpeg',      name: 'Jamun Wine',        type: 'Reserve Collection', orchardImage: 'images/jamun5.png' },
  { image: 'images/strawberry1.png',  name: 'Strawberry Wine',   type: 'Premium Selection',  orchardImage: 'images/strawberry3.png' },
  { image: 'images/pome1.png',        name: 'Pomegranate Wine',  type: 'Signature Blend',    orchardImage: 'images/pome (2).png' },
  { image: 'images/mahua2.png',       name: 'Mahua Flower Wine', type: 'Limited Edition',    orchardImage: 'images/mahua2.png' },
];

let currentWineIndex = 0;

function updateWineDisplay(animate) {
  const wine = wineData[currentWineIndex];
  const bottle = document.querySelector('.wine-bottle');
  const name   = document.querySelector('.wine-name');
  const type   = document.querySelector('.wine-type');
  const lifestyleImg = document.querySelector('.lifestyle-image img');

  if (!bottle || !name) return;

  if (animate) {
    bottle.style.opacity = '0';
    bottle.style.transform = 'translateY(12px)';
    if (lifestyleImg) {
      lifestyleImg.style.opacity = '0';
      lifestyleImg.style.transform = 'scale(0.98)';
    }
    setTimeout(() => {
      bottle.src = wine.image;
      name.textContent = wine.name;
      if (type) type.textContent = wine.type;
      if (lifestyleImg && wine.orchardImage) {
        lifestyleImg.src = wine.orchardImage;
      }
      bottle.style.opacity = '1';
      bottle.style.transform = 'translateY(0)';
      if (lifestyleImg) {
        lifestyleImg.style.opacity = '1';
        lifestyleImg.style.transform = 'scale(1)';
      }
    }, 280);
  } else {
    bottle.src = wine.image;
    name.textContent = wine.name;
    if (type) type.textContent = wine.type;
    if (lifestyleImg && wine.orchardImage) {
      lifestyleImg.src = wine.orchardImage;
    }
  }

  // Update dots
  document.querySelectorAll('.dot').forEach((dot, i) => {
    dot.classList.toggle('active', i === currentWineIndex);
  });
}

function nextWine() {
  currentWineIndex = (currentWineIndex + 1) % wineData.length;
  updateWineDisplay(true);
}

function prevWine() {
  currentWineIndex = (currentWineIndex - 1 + wineData.length) % wineData.length;
  updateWineDisplay(true);
}

// ===== VISIT SECTION =====
function initVisitSection() {
  const menuItems = document.querySelectorAll('.visit-menu-item');
  const visitImage = document.querySelector('.visit-image');
  const visitDescription = document.querySelector('.visit-description');

  const visitData = {
    private: {
      image: 'images/edited/allbottels.jpg',
      description: "Experience the essence of our terroir through intimate tastings where each glass tells the story of our Orchard's heritage."
    },
    room: {
      image: 'images/rectanglesize5.png',
      description: 'Join us in our elegant tasting room where tradition meets sophistication in every carefully curated wine experience.'
    },
    food: {
      image: 'images/edited/Mahuawine (3).png',
      description: 'Discover perfect pairings where our culinary artistry complements the complexity and character of our finest wines.'
    }
  };

  function updateVisitContent(type) {
    const data = visitData[type];
    if (visitImage && visitDescription && data) {
      visitImage.src = data.image;
      visitDescription.textContent = data.description;
    }
  }

  menuItems.forEach(item => {
    const visitType = item.getAttribute('data-visit');

    item.addEventListener('click', () => {
      menuItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
      updateVisitContent(visitType);
    });

    item.addEventListener('mouseenter', () => updateVisitContent(visitType));

    item.addEventListener('mouseleave', () => {
      const active = document.querySelector('.visit-menu-item.active');
      if (active) updateVisitContent(active.getAttribute('data-visit'));
    });
  });
}

// ===== INSTAGRAM SCROLL ANIMATION =====
function initInstagramAnimation() {
  const items = document.querySelectorAll('.instagram-item');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 120);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });

  items.forEach(item => observer.observe(item));
}

// ===== STORIES PARALLAX =====
function initStoriesAnimation() {
  const storyLeft  = document.querySelector('.story-left');
  const storyRight = document.querySelector('.story-right');
  let ticking = false;

  function update() {
    if (!storyLeft || !storyRight) return;
    const rect = storyLeft.getBoundingClientRect();
    const wh   = window.innerHeight;
    if (rect.top < wh && rect.bottom > 0) {
      const p = Math.max(0, Math.min(1, (wh - rect.top) / (wh + rect.height)));
      storyLeft.style.transform  = `translateY(${p * -15}px)`;
      storyRight.style.transform = `translateY(${p * 15}px)`;
    }
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(update); ticking = true; }
  }, { passive: true });
  update();
}

// ===== FAMILY PARALLAX =====
function initFamilyAnimation() {
  const familySection = document.querySelector('.family');
  const mainImage     = document.querySelector('.main-image');
  const contentCard   = document.querySelector('.family-content-card');
  const overlayImage  = document.querySelector('.overlay-image');
  let ticking = false;

  function update() {
    if (!familySection || !mainImage || !contentCard || !overlayImage) return;
    const rect = familySection.getBoundingClientRect();
    const wh   = window.innerHeight;
    if (rect.top < wh && rect.bottom > 0) {
      const p = Math.max(0, Math.min(1, (wh - rect.top) / (wh + rect.height)));
      mainImage.style.transform    = `translateY(${p * -25}px)`;
      contentCard.style.transform  = `translateY(${p * 15}px)`;
      overlayImage.style.transform = `translateY(${p * -12}px)`;
    }
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(update); ticking = true; }
  }, { passive: true });
  update();
}

// ===== INIT ON DOM READY =====
document.addEventListener('DOMContentLoaded', () => {
  // Wine slider
  const nextBtn = document.querySelector('.next-btn');
  const prevBtn = document.querySelector('.prev-btn');
  const dots    = document.querySelectorAll('.dot');

  if (nextBtn) nextBtn.addEventListener('click', nextWine);
  if (prevBtn) prevBtn.addEventListener('click', prevWine);
  dots.forEach((dot, i) => dot.addEventListener('click', () => {
    currentWineIndex = i;
    updateWineDisplay(true);
  }));

  updateWineDisplay(false);

  // Sections
  initInstagramAnimation();
  initStoriesAnimation();
  initFamilyAnimation();
  initVisitSection();
  initShowcaseCarousel();
  initSmartNavbar();
});

// ===== SMART NAVBAR SCROLL (HIDE ON SCROLL DOWN, SHOW ON SCROLL UP) =====
function initSmartNavbar() {
  let lastY = window.scrollY || window.pageYOffset || 0;
  let ticking = false;

  function onScroll() {
    const nav = document.querySelector('.navbar');
    if (!nav) {
      ticking = false;
      return;
    }
    const currentY = Math.max(0, window.scrollY || window.pageYOffset || 0);

    if (nav.classList.contains('menu-active')) {
      nav.classList.remove('nav-hidden');
      lastY = currentY;
      ticking = false;
      return;
    }

    if (currentY <= 80) {
      nav.classList.remove('nav-hidden');
    } else if (currentY > lastY && currentY > 100) {
      nav.classList.add('nav-hidden');
    } else if (currentY < lastY) {
      nav.classList.remove('nav-hidden');
    }

    lastY = currentY;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(onScroll);
      ticking = true;
    }
  }, { passive: true });
}

// ===== PRODUCT SHOWCASE CAROUSEL (OLIVER WINERY STYLE) =====
function initShowcaseCarousel() {
  const container = document.getElementById('showcase-container');
  const track = document.getElementById('showcase-track');
  const prevBtn = document.getElementById('showcase-prev');
  const nextBtn = document.getElementById('showcase-next');
  const titleEl = document.getElementById('showcase-title');
  const subtitleEl = document.getElementById('showcase-subtitle');
  const btnEl = document.getElementById('showcase-btn');

  if (!track || !container || !prevBtn || !nextBtn) return;

  const wineData = [
    {
      name: 'Pomegranate Wine',
      subtitle: 'Ruby Rich Reserve · 100% Residue-Free Pomegranates',
      link: 'pome.html'
    },
    {
      name: 'Jamun Wine',
      subtitle: 'Jamun Reserve Collection · Wild Nashik Berries',
      link: 'jamun.html'
    },
    {
      name: 'Mahua Flower Wine',
      subtitle: 'Amber Wild Mahua Flower Wine · Sacred Forest Blossoms',
      link: 'mahua.html'
    },
    {
      name: 'Strawberry Wine',
      subtitle: 'Scarlet Blush Strawberry Wine · Fresh Mahabaleshwar Berries',
      link: 'Strawberry.html'
    }
  ];

  const cards = Array.from(track.querySelectorAll('.showcase-card'));
  if (cards.length === 0) return;

  let currentIndex = 4; // Start with index 4 (Pomegranate, middle set)
  let isAnimating = false;

  function updateCards(animateTrack) {
    const cardWidth = cards[0].offsetWidth;
    const containerWidth = container.offsetWidth;
    const targetX = (containerWidth / 2) - (currentIndex * cardWidth + cardWidth / 2);

    if (animateTrack) {
      track.style.transition = 'transform 0.5s cubic-bezier(0.22, 1, 0.36, 1)';
    } else {
      track.style.transition = 'none';
    }
    track.style.transform = `translateX(${targetX}px)`;

    cards.forEach((card, idx) => {
      const dist = Math.abs(idx - currentIndex);
      card.classList.remove('state-active', 'state-adjacent', 'state-outer', 'state-far');
      if (dist === 0) {
        card.classList.add('state-active');
      } else if (dist === 1) {
        card.classList.add('state-adjacent');
      } else if (dist === 2) {
        card.classList.add('state-outer');
      } else {
        card.classList.add('state-far');
      }
    });

    const activeWine = wineData[currentIndex % wineData.length];
    if (titleEl && subtitleEl && btnEl && activeWine) {
      if (animateTrack) {
        titleEl.style.opacity = '0';
        titleEl.style.transform = 'translateY(6px)';
        subtitleEl.style.opacity = '0';
        subtitleEl.style.transform = 'translateY(6px)';

        setTimeout(() => {
          titleEl.textContent = activeWine.name;
          subtitleEl.textContent = activeWine.subtitle;
          btnEl.href = activeWine.link;

          titleEl.style.opacity = '1';
          titleEl.style.transform = 'translateY(0)';
          subtitleEl.style.opacity = '1';
          subtitleEl.style.transform = 'translateY(0)';
        }, 160);
      } else {
        titleEl.textContent = activeWine.name;
        subtitleEl.textContent = activeWine.subtitle;
        btnEl.href = activeWine.link;
        titleEl.style.opacity = '1';
        titleEl.style.transform = 'translateY(0)';
        subtitleEl.style.opacity = '1';
        subtitleEl.style.transform = 'translateY(0)';
      }
    }
  }

  function goTo(index) {
    if (isAnimating) return;
    isAnimating = true;
    currentIndex = index;
    updateCards(true);

    setTimeout(() => {
      // Seamless wrap-around for infinite carousel loop
      if (currentIndex >= 8) {
        currentIndex -= 4;
        updateCards(false);
      } else if (currentIndex < 4) {
        currentIndex += 4;
        updateCards(false);
      }
      isAnimating = false;
    }, 520);
  }

  prevBtn.addEventListener('click', (e) => {
    e.preventDefault();
    goTo(currentIndex - 1);
  });

  nextBtn.addEventListener('click', (e) => {
    e.preventDefault();
    goTo(currentIndex + 1);
  });

  cards.forEach((card, idx) => {
    card.addEventListener('click', () => {
      if (idx !== currentIndex) {
        goTo(idx);
      }
    });
  });

  // Touch / swipe support
  let touchStartX = 0;
  let touchEndX = 0;

  container.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  container.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diffX = touchStartX - touchEndX;
    if (Math.abs(diffX) > 40) {
      if (diffX > 0) goTo(currentIndex + 1);
      else goTo(currentIndex - 1);
    }
  }, { passive: true });

  // Keyboard navigation
  window.addEventListener('keydown', (e) => {
    const rect = container.getBoundingClientRect();
    const inView = rect.top < window.innerHeight && rect.bottom > 0;
    if (inView) {
      if (e.key === 'ArrowRight') goTo(currentIndex + 1);
      if (e.key === 'ArrowLeft') goTo(currentIndex - 1);
    }
  });

  window.addEventListener('resize', () => {
    updateCards(false);
  });

  // Initial layout calculation
  updateCards(false);
  window.addEventListener('load', () => updateCards(false));
}