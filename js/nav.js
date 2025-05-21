document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".nav");
  const menu = document.querySelector(".menu");
  const hamburger = document.querySelector(".hamburger-button");
  const overlay = document.querySelector(".menu-overlay");
  const profileDropdown = document.querySelector(".profile-dropdown");
  const profileContainer = document.querySelector(
    ".profile-dropdown-container"
  );
  const menuItems = document.querySelectorAll(".menu-li");
  const logoutModal = document.getElementById("nav-logout-modal");
  const isMobileCheck = () => window.innerWidth <= 768;

  let lastScrollTop = 0;
  let scrollPosition = 0;
  let isMobile = isMobileCheck();
  let isProfileDropdownOpen = false;

  // Gestion de la barre de navigation
  if (navbar) {
    Object.assign(navbar.style, {
      position: "fixed",
      top: "0",
      width: "100%",
      zIndex: "1000",
    });

    window.addEventListener("scroll", () => {
      if (menu?.classList.contains("open")) return;

      const scrollTop = document.documentElement.scrollTop;
      navbar.style.transition = "transform 0.5s ease";
      navbar.style.transform =
        scrollTop > lastScrollTop && scrollTop > 50
          ? "translateY(-100%)"
          : "translateY(0)";
      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });

    document.addEventListener("mousemove", (e) => {
      if (!isMobile && e.clientY <= 70) {
        navbar.style.transition = "transform 0.3s ease";
        navbar.style.transform = "translateY(0)";
      }
    });

    // Effet hover sur les éléments du menu
    menuItems.forEach((item) => {
      if (!isMobile) {
        item.addEventListener("mouseover", () =>
          menuItems.forEach((i) => {
            if (i !== item) i.classList.add("hovered");
          })
        );
        item.addEventListener("mouseout", () =>
          menuItems.forEach((i) => i.classList.remove("hovered"))
        );
      }
    });
  }

  // Menu mobile
  if (hamburger && menu && overlay) {
    const toggleMenu = () => {
      const isOpening = !menu.classList.contains("open");

      hamburger.classList.toggle("open");
      menu.classList.toggle("open");
      overlay.classList.toggle("open");

      if (isOpening) {
        scrollPosition = window.pageYOffset;
        document.documentElement.style.setProperty(
          "--scrollbar-width",
          `${window.innerWidth - document.documentElement.clientWidth}px`
        );
        document.body.classList.add("no-scroll");
        document.body.style.top = `-${scrollPosition}px`;
        navbar.style.transform = "translateY(0)";
      } else {
        document.body.classList.remove("no-scroll");
        document.body.style.top = "";
        document.documentElement.style.removeProperty("--scrollbar-width");
        window.scrollTo(0, scrollPosition);
        lastScrollTop = window.pageYOffset;
      }

      if (profileDropdown && isMobile) {
        profileDropdown.style.display = menu.classList.contains("open")
          ? "flex"
          : "none";
      }
    };

    hamburger.addEventListener("click", toggleMenu);
    overlay.addEventListener(
      "click",
      () => menu.classList.contains("open") && toggleMenu()
    );

    document.querySelectorAll(".menu a").forEach((link) => {
      if (
        !link.closest(".profile-dropdown-container") &&
        !link.closest(".theme-toggle-container")
      ) {
        link.addEventListener(
          "click",
          () => menu.classList.contains("open") && toggleMenu()
        );
      }
    });

    document.addEventListener(
      "touchmove",
      (e) => {
        if (document.body.classList.contains("no-scroll")) {
          const menuTarget = e.target.closest(".menu");
          if (
            !menuTarget ||
            (menuTarget && menu.scrollHeight <= menu.clientHeight)
          ) {
            e.preventDefault();
          }
        }
      },
      { passive: false }
    );

    window.addEventListener("resize", () => {
      isMobile = isMobileCheck();
      if (!isMobile && menu.classList.contains("open")) toggleMenu();
    });
  }

  // Dropdown du profil
  if (profileContainer && profileDropdown) {
    profileContainer.addEventListener(
      "mouseenter",
      () => !isMobile && (profileDropdown.style.display = "flex")
    );

    profileContainer.addEventListener(
      "mouseleave",
      () => !isMobile && (profileDropdown.style.display = "none")
    );

    profileContainer.addEventListener("click", (e) => {
      if (isMobile && !menu?.classList.contains("open")) {
        if (
          e.target === profileContainer ||
          !e.target.closest("a") ||
          e.target.closest(".profile-icon-container")
        ) {
          e.preventDefault();
          isProfileDropdownOpen = !isProfileDropdownOpen;
          profileDropdown.style.display = isProfileDropdownOpen
            ? "flex"
            : "none";
        }
      }
    });

    document.addEventListener("click", (e) => {
      if (
        isMobile &&
        isProfileDropdownOpen &&
        !profileContainer.contains(e.target) &&
        !menu?.classList.contains("open")
      ) {
        profileDropdown.style.display = "none";
        isProfileDropdownOpen = false;
      }
    });
  }

  // Gestion du thème
  const themeToggleBtn = document.getElementById("themeToggleBtn");
  const sunIcons = document.querySelectorAll("#sunIcon");
  const moonIcons = document.querySelectorAll("#moonIcon");

  if (sunIcons.length || moonIcons.length) {
    const getCookie = (name) => {
      if (typeof window.getCookie === "function") return window.getCookie(name);
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      return parts.length === 2 ? parts.pop().split(";").shift() : null;
    };

    const updateThemeIcons = (theme) => {
      const isLight = theme === "light";
      sunIcons.forEach((icon) => icon.classList.toggle("active", isLight));
      moonIcons.forEach((icon) => icon.classList.toggle("active", !isLight));
    };

    const changeTheme = (theme) => {
      // Utiliser la fonction toggleTheme définie dans theme-loader.js
      if (typeof window.toggleTheme === "function") {
        window.toggleTheme(theme);
        updateThemeIcons(theme);
      } else {
        // Fallback au cas où toggleTheme n'est pas disponible
        document.cookie = `theme=${theme};path=/;max-age=31536000`;
        document.body.classList.remove("light-theme", "dark-theme");
        document.body.classList.add(`${theme}-theme`);
        updateThemeIcons(theme);

        document.body.classList.add("theme-transition");
        setTimeout(
          () => document.body.classList.remove("theme-transition"),
          1000
        );
      }
    };

    updateThemeIcons(getCookie("theme") || "dark");

    sunIcons.forEach((icon) =>
      icon.addEventListener("click", (e) => {
        e.stopPropagation();
        changeTheme("light");
      })
    );

    moonIcons.forEach((icon) =>
      icon.addEventListener("click", (e) => {
        e.stopPropagation();
        changeTheme("dark");
      })
    );

    themeToggleBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      changeTheme(
        document.body.classList.contains("light-theme") ? "dark" : "light"
      );
    });
  }

  // Modal de déconnexion
  if (logoutModal) {
    const hideModal = () => (logoutModal.style.display = "none");

    document
      .getElementById("nav-logout-button")
      ?.addEventListener("click", (e) => {
        e.preventDefault();
        logoutModal.style.display = "flex";
      });

    document
      .querySelector("#nav-logout-modal .close-modal")
      ?.addEventListener("click", hideModal);
    document
      .getElementById("cancel-nav-logout")
      ?.addEventListener("click", hideModal);
    window.addEventListener(
      "click",
      (e) => e.target === logoutModal && hideModal()
    );
  }
});
