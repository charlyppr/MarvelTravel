const navBar = document.querySelector(".nav");

let lastScrollTop = 0;

window.addEventListener("scroll", () => {
  if (window.innerWidth > 768) {
    let scrollTop = document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop) {
      navBar.style.transform = "translateY(-100%)";
    } else {
      navBar.style.transform = "translateY(0)";
    }
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
  }
});

document.addEventListener("DOMContentLoaded", function () {
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
});

// Theme toggle functionality with simple button
document.addEventListener("DOMContentLoaded", function () {
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  const themeText = document.getElementById("themeText");
  const themeIcon = document.getElementById("themeIcon");

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function (e) {
      e.preventDefault();

      // Determine current theme
      const isLightMode =
        document.body.classList.contains("light-theme") ||
        document.cookie.includes("theme=light");

      // Toggle to opposite theme
      const newTheme = isLightMode ? "dark" : "light";

      // Update cookie
      document.cookie = `theme=${newTheme};path=/;max-age=31536000`; // Cookie expires in 1 year

      // Update UI text and icon - affiche l'action possible, pas l'état actuel
      if (themeText) {
        themeText.textContent = isLightMode ? "Mode clair" : "Mode sombre";
      }

      if (themeIcon) {
        // En mode sombre, on montre le soleil (pour aller vers la lumière)
        // En mode clair, on montre la lune (pour aller vers l'obscurité)
        const newIcon = isLightMode ? "sun.svg" : "moon.svg";
        themeIcon.src = themeIcon.src.replace(/[^\/]+\.svg$/, newIcon);
      }

      // Update body class
      document.body.classList.remove("light-theme", "dark-theme");
      document.body.classList.add(`${newTheme}-theme`);

      // Animation
      document.body.classList.add("theme-transition");
      setTimeout(() => {
        document.body.classList.remove("theme-transition");
      }, 1000);
    });
  }
});
