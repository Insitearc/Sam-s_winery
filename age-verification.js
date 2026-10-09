// Age Verification Script
(function() {
  'use strict';
  
  // Get modal and buttons
  const modal = document.getElementById('age-verification-modal');
  if (!modal) return;

  const yesBtn = document.getElementById('age-yes');
  const noBtn = document.getElementById('age-no');

  function unlockScroll() {
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
  }

  function lockScroll() {
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
  }
  
  // Check if user has already verified their age (check localStorage and sessionStorage)
  let isVerified = false;
  try {
    isVerified = (localStorage.getItem('ageVerified') === 'true') || (sessionStorage.getItem('ageVerified') === 'true');
  } catch (e) {
    isVerified = false;
  }
  
  // If already verified, hide modal immediately and ensure scroll is unlocked
  if (isVerified) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
    unlockScroll();
  } else {
    // Prevent scrolling when modal is open
    lockScroll();
  }
  
  // Handle "Yes" button click
  if (yesBtn) {
    yesBtn.addEventListener('click', function() {
      // Store verification in persistent and session storage
      try {
        localStorage.setItem('ageVerified', 'true');
        sessionStorage.setItem('ageVerified', 'true');
      } catch (e) {}
      
      // Hide modal with smooth transition
      modal.classList.add('hidden');
      
      // Re-enable scrolling immediately
      unlockScroll();

      // Remove from layout after fade transition so it cannot block wheel/touch events
      setTimeout(() => {
        modal.style.display = 'none';
        unlockScroll();
      }, 600);
    });
  }
  
  // Handle "No" button click
  if (noBtn) {
    noBtn.addEventListener('click', function() {
      // Show a message
      const modalText = document.querySelector('.age-modal-text');
      if (modalText) {
        modalText.innerHTML = `
          <h2 style="margin-bottom: 20px;">Sorry!</h2>
          <p style="font-size: 1.1rem; margin-bottom: 30px; line-height: 1.6;">
            You must be of legal drinking age to access this website.
          </p>
          <p style="font-size: 0.9rem; opacity: 0.8;">
            This page will close automatically.
          </p>
        `;
      }
      
      // Close the window/tab after 3 seconds
      setTimeout(() => {
        window.close();
        if (!window.closed) {
          window.location.href = 'about:blank';
        }
      }, 3000);
    });
  }
  
  // Prevent escape key from closing modal if still active
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && !modal.classList.contains('hidden') && modal.style.display !== 'none') {
      event.preventDefault();
    }
  });
  
  // Prevent clicking outside modal to close it
  modal.addEventListener('click', function(event) {
    if (event.target === modal) {
      event.stopPropagation();
    }
  });
  
})();