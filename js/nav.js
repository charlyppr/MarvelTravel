if (typeof window.navBarElement === 'undefined') {
  window.navBarElement = document.querySelector(".nav");
}

if (typeof window.lastScrollTop === 'undefined') {
  window.lastScrollTop = 0;
}

window.addEventListener("scroll", () => {
  if (window.innerWidth > 768) {
    let scrollTop = document.documentElement.scrollTop;
    if (scrollTop > window.lastScrollTop) {
      window.navBarElement.style.transform = "translateY(-100%)";
    } else {
      window.navBarElement.style.transform = "translateY(0)";
    }
    window.lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
  }
});

// Menu hover effect
document.addEventListener("DOMContentLoaded", function () {
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

  // Theme icons functionality
  const themeIcons = document.querySelector('.theme-icons');
  
  if (themeIcons) {
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
    
    const currentThemeValue = getThemeCookie('theme') || 'dark';
    
    if (currentThemeValue === 'light') {
      themeIcons.classList.add('light-active');
    } else {
      themeIcons.classList.add('dark-active');
    }

    // Handle theme icon clicks
    const sunIconWrapper = document.getElementById('sunIcon');
    const moonIconWrapper = document.getElementById('moonIcon');

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
      if (newTheme === 'light') {
        sunIconWrapper.classList.add('active');
        moonIconWrapper.classList.remove('active');
        themeIcons.classList.add('light-active');
        themeIcons.classList.remove('dark-active');
      } else {
        moonIconWrapper.classList.add('active');
        sunIconWrapper.classList.remove('active');
        themeIcons.classList.add('dark-active');
        themeIcons.classList.remove('light-active');
      }

      // Add transition effect
      document.body.classList.add('theme-transition');
      setTimeout(() => {
        document.body.classList.remove('theme-transition');
      }, 1000);
    }

    if (sunIconWrapper) {
      sunIconWrapper.addEventListener('click', function() {
        changeTheme('light');
      });
    }

    if (moonIconWrapper) {
      moonIconWrapper.addEventListener('click', function() {
        changeTheme('dark');
      });
    }
  }

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
