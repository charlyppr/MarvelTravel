document.addEventListener("DOMContentLoaded", function () {
  window.navBarElement = document.querySelector(".nav");
  let lastScrollTop = 0;
  let isInitialLoad = true;

  // Ensure nav is visible on page load
  window.navBarElement.style.transform = "translateY(0)";

  window.addEventListener("scroll", () => {
    if (window.innerWidth > 768) {
      let scrollTop = document.documentElement.scrollTop;
      
      // Skip the first scroll event after page load
      if (isInitialLoad) {
        lastScrollTop = scrollTop;
        isInitialLoad = false;
        return;
      }
      
      if (scrollTop > lastScrollTop) {
        window.navBarElement.style.transform = "translateY(-100%)";
      } else {
        window.navBarElement.style.transform = "translateY(0)";
      }
      
      // Ensure we update the last scroll position correctly
      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }
  });

  // Menu hover effects
  if (window.innerWidth > 768) {
    const menuItems = document.querySelectorAll(".menu-li");

    const handleHover = (hoveredElement, isEntering) => {
      menuItems.forEach((item) => {
        if (item !== hoveredElement) {
          item.classList.toggle("hovered", isEntering);
        }
      });
    };

    menuItems.forEach((item) => {
      item.addEventListener("mouseover", () => handleHover(item, true));
      item.addEventListener("mouseout", () => handleHover(item, false));
    });
  }

  // Profile dropdown functionality
  const profileContainer = document.querySelector('.profile-dropdown-container');
  
  if (profileContainer) {
    profileContainer.addEventListener('mouseenter', function () {
      document.querySelector('.profile-dropdown').style.display = 'flex';
    });

    profileContainer.addEventListener('mouseleave', function () {
      document.querySelector('.profile-dropdown').style.display = 'none';
    });
  }

  // Theme toggle functionality - Maintenant accessible à tous les utilisateurs
  function handleThemeToggle() {
    // Use the getCookie function from theme-loader.js if available
    function getThemeCookie(name) {
      if (typeof getCookie === 'function') {
        return getCookie(name);
      } else {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
      }
    }
    
    // Recherche tous les groupes d'icônes de thème sur la page (navbar et dropdown si connecté)
    const allThemeIcons = document.querySelectorAll('.theme-icons');
    
    if (allThemeIcons.length > 0) {
      const currentThemeValue = getThemeCookie('theme') || 'dark';
      
      // Mettre à jour l'état visuel de tous les sélecteurs de thème
      updateThemeIcons(currentThemeValue);

      function changeTheme(newTheme) {
        // Use the functions from theme-loader.js if available
        if (typeof toggleTheme === 'function') {
          toggleTheme(newTheme);
        } else {
          // Fallback if theme-loader.js is not loaded
          document.cookie = `theme=${newTheme};path=/;max-age=31536000`;
          document.body.classList.remove('light-theme', 'dark-theme');
          document.body.classList.add(newTheme + '-theme');
        }

        // Update UI for theme icons
        updateThemeIcons(newTheme);

        // Add transition effect
        document.body.classList.add('theme-transition');
        setTimeout(() => {
          document.body.classList.remove('theme-transition');
        }, 1000);
      }

      // Function to update all theme icons based on current theme
      function updateThemeIcons(theme) {
        const allSunIcons = document.querySelectorAll('#sunIcon');
        const allMoonIcons = document.querySelectorAll('#moonIcon');
        
        if (theme === 'light') {
          allSunIcons.forEach(icon => icon.classList.add('active'));
          allMoonIcons.forEach(icon => icon.classList.remove('active'));
        } else {
          allMoonIcons.forEach(icon => icon.classList.add('active'));
          allSunIcons.forEach(icon => icon.classList.remove('active'));
        }
      }

      // Ajouter des écouteurs d'événements à tous les boutons de thème
      const allSunIcons = document.querySelectorAll('#sunIcon'); 
      const allMoonIcons = document.querySelectorAll('#moonIcon');
      
      allSunIcons.forEach(icon => {
        icon.addEventListener('click', function() {
          changeTheme('light');
        });
      });
      
      allMoonIcons.forEach(icon => {
        icon.addEventListener('click', function() {
          changeTheme('dark');
        });
      });
    }
  }
  
  // Appeler la fonction de gestion du thème
  handleThemeToggle();

  // Simple theme toggle button functionality
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function (e) {
      e.preventDefault();

      // Determine current theme
      const isLightMode =
        document.body.classList.contains("light-theme") ||
        document.cookie.includes("theme=light");

      // Toggle to opposite theme
      const newTheme = isLightMode ? "dark" : "light";
      
      // Use toggleTheme from theme-loader if available
      if (typeof toggleTheme === 'function') {
        toggleTheme(newTheme);
      } else {
        // Fallback
        document.cookie = `theme=${newTheme};path=/;max-age=31536000`;
        document.body.classList.remove("light-theme", "dark-theme");
        document.body.classList.add(`${newTheme}-theme`);
      }

      // Animation
      document.body.classList.add("theme-transition");
      setTimeout(() => {
        document.body.classList.remove("theme-transition");
      }, 1000);
    });
  }

  // Logout modal functionality
  const navLogoutModal = document.getElementById('nav-logout-modal');
  const navLogoutBtn = document.getElementById('nav-logout-button');
  const closeNavModalBtn = document.querySelector('#nav-logout-modal .close-modal');
  const cancelNavLogoutBtn = document.getElementById('cancel-nav-logout');

  if (navLogoutBtn) {
    navLogoutBtn.addEventListener('click', function (e) {
      e.preventDefault();
      navLogoutModal.style.display = 'flex';
    });
  }

  if (closeNavModalBtn) {
    closeNavModalBtn.addEventListener('click', function () {
      navLogoutModal.style.display = 'none';
    });
  }

  if (cancelNavLogoutBtn) {
    cancelNavLogoutBtn.addEventListener('click', function () {
      navLogoutModal.style.display = 'none';
    });
  }

  // Close modal when clicking outside
  window.addEventListener('click', function (event) {
    if (event.target === navLogoutModal) {
      navLogoutModal.style.display = 'none';
    }
  });
});
