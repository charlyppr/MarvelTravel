document.addEventListener("DOMContentLoaded", function () {
  // Configuration centralisée
  const config = {
    navSelectors: {
      navbar: ".nav",
      menuItems: ".menu-li",
      profileDropdown: ".profile-dropdown-container",
      profileDropdownMenu: ".profile-dropdown",
      themeIcons: ".theme-icons",
      sunIcon: "#sunIcon",
      moonIcon: "#moonIcon",
      themeToggleBtn: "#themeToggleBtn",
      logoutModal: "#nav-logout-modal",
      logoutBtn: "#nav-logout-button",
      closeModalBtn: "#nav-logout-modal .close-modal",
      cancelLogoutBtn: "#cancel-nav-logout",
      hamburgerButton: ".hamburger-button",
      menu: ".menu",
      menuOverlay: ".menu-overlay",
      menuLinks: ".menu a"
    },
    transitionTimes: {
      navHide: "0.5s",
      navShow: "0.3s",
      themeChange: 1000
    },
    breakpoints: {
      mobile: 768
    }
  };

  // Sélection des éléments DOM essentiels (évite les multiples sélections)
  const elements = {
    navbar: document.querySelector(config.navSelectors.navbar),
    menuItems: document.querySelectorAll(config.navSelectors.menuItems),
    profileContainer: document.querySelector(config.navSelectors.profileDropdown),
    profileDropdown: document.querySelector(config.navSelectors.profileDropdownMenu),
    themeIcons: document.querySelectorAll(config.navSelectors.themeIcons),
    sunIcons: document.querySelectorAll(config.navSelectors.sunIcon),
    moonIcons: document.querySelectorAll(config.navSelectors.moonIcon),
    themeToggleBtn: document.getElementById("themeToggleBtn"),
    logoutModal: document.getElementById("nav-logout-modal"),
    logoutBtn: document.getElementById("nav-logout-button"),
    closeModalBtn: document.querySelector(config.navSelectors.closeModalBtn),
    cancelBtn: document.getElementById("cancel-nav-logout"),
    hamburgerButton: document.querySelector(config.navSelectors.hamburgerButton),
    menu: document.querySelector(config.navSelectors.menu),
    menuOverlay: document.querySelector(config.navSelectors.menuOverlay),
    menuLinks: document.querySelectorAll(config.navSelectors.menuLinks)
  };

  // État global
  const state = {
    lastScrollTop: 0,
    isInitialLoad: true,
    isMobileMenuOpen: false,
    scrollPosition: 0,
    isDropdownOpen: false,
    isMobile: window.innerWidth <= config.breakpoints.mobile
  };

  // Fonctions utilitaires
  const utils = {
    getScrollbarWidth() {
      return window.innerWidth - document.documentElement.clientWidth;
    },
    
    disableScroll() {
      state.scrollPosition = window.pageYOffset;
      document.documentElement.style.setProperty('--scrollbar-width', `${this.getScrollbarWidth()}px`);
      document.body.classList.add('no-scroll');
      document.body.style.top = `-${state.scrollPosition}px`;
    },
    
    enableScroll() {
      document.body.classList.remove('no-scroll');
      document.body.style.top = '';
      document.documentElement.style.removeProperty('--scrollbar-width');
      window.scrollTo(0, state.scrollPosition);
    },

    getCookie(name) {
      if (typeof getCookie === "function") return getCookie(name);
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
      return null;
    },

    toggleTheme(theme) {
      if (typeof toggleTheme === "function") {
        toggleTheme(theme);
      } else {
        document.cookie = `theme=${theme};path=/;max-age=31536000`;
        document.body.classList.remove("light-theme", "dark-theme");
        document.body.classList.add(`${theme}-theme`);
      }
    }
  };

  // Navigation
  const navManager = {
    init() {
      if (!elements.navbar) return;
      
      // Style initial
      elements.navbar.style.transform = "translateY(0)";
      elements.navbar.style.position = "fixed";
      elements.navbar.style.top = "0";
      elements.navbar.style.width = "100%";
      elements.navbar.style.zIndex = "1000";
      
      // Event listeners
      window.addEventListener("scroll", () => this.handleScroll());
      document.addEventListener("mousemove", (e) => this.handleMouseMove(e));
      
      // Observer pour détecter l'état du menu mobile
      if (elements.menu) {
        new MutationObserver(mutations => {
          mutations.forEach(mutation => {
            if (mutation.attributeName === 'class') {
              state.isMobileMenuOpen = elements.menu.classList.contains('open');
            }
          });
        }).observe(elements.menu, { attributes: true });
      }
    },

    handleScroll() {
      if (state.isMobileMenuOpen || !elements.navbar) return;
      
      const scrollTop = document.documentElement.scrollTop;
      elements.navbar.style.transition = `transform ${config.transitionTimes.navHide} ease`;
      
      if (state.isInitialLoad) {
        state.lastScrollTop = scrollTop;
        state.isInitialLoad = false;
        return;
      }
      
      elements.navbar.style.transform = (scrollTop > state.lastScrollTop && scrollTop > 50) 
        ? "translateY(-100%)" 
        : "translateY(0)";
      
      state.lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    },

    handleMouseMove(e) {
      if (state.isMobile || !elements.navbar || state.isMobileMenuOpen) return;
      
      if (e.clientY <= 70) {
        elements.navbar.style.transition = `transform ${config.transitionTimes.navShow} ease`;
        elements.navbar.style.transform = "translateY(0)";
      }
    },
    
    showNavbar() {
      if (elements.navbar) {
        elements.navbar.style.transform = "translateY(0)";
      }
    }
  };

  // Gestionnaire des menus
  const menuManager = {
    init() {
      // Effets du menu (survol)
      if (!state.isMobile && elements.menuItems.length > 0) {
        elements.menuItems.forEach(item => {
          item.addEventListener("mouseover", () => this.handleHover(item, true));
          item.addEventListener("mouseout", () => this.handleHover(item, false));
        });
      }
      
      // Menu mobile
      if (elements.hamburgerButton && elements.menu && elements.menuOverlay) {
        elements.hamburgerButton.addEventListener('click', () => this.toggleMobileMenu());
        elements.menuOverlay.addEventListener('click', () => this.closeMobileMenu());
        
        if (elements.menuLinks) {
          elements.menuLinks.forEach(link => {
            link.addEventListener('click', (e) => {
              if (!link.closest('.profile-dropdown-container') && 
                  !link.closest('.theme-toggle-container')) {
                this.closeMobileMenu();
              }
            });
          });
        }
        
        // Événements de touch pour mobile
        document.addEventListener('touchmove', (e) => {
          if (document.body.classList.contains('no-scroll')) {
            if (!e.target.closest('.menu') || 
                (e.target.closest('.menu') && !this.isMenuScrollable())) {
              e.preventDefault();
            }
          }
        }, { passive: false });
        
        window.addEventListener('resize', () => {
          state.isMobile = window.innerWidth <= config.breakpoints.mobile;
          if (!state.isMobile && elements.menu.classList.contains('open')) {
            this.closeMobileMenu();
          }
        });
      }
    },
    
    handleHover(hoveredElement, isEntering) {
      elements.menuItems.forEach(item => {
        if (item !== hoveredElement) {
          item.classList.toggle("hovered", isEntering);
        }
      });
    },
    
    isMenuScrollable() {
      return elements.menu.scrollHeight > elements.menu.clientHeight;
    },
    
    toggleMobileMenu() {
      const isOpening = !elements.menu.classList.contains('open');
      
      elements.hamburgerButton.classList.toggle('open');
      elements.menu.classList.toggle('open');
      elements.menuOverlay.classList.toggle('open');
      
      if (isOpening) {
        utils.disableScroll();
        navManager.showNavbar();
      } else {
        utils.enableScroll();
        state.lastScrollTop = window.pageYOffset;
      }
      
      // Menu déroulant du profil dans le menu mobile
      if (elements.profileDropdown && state.isMobile) {
        elements.profileDropdown.style.display = elements.menu.classList.contains('open') ? 
          'flex' : 
          (!elements.profileDropdown.closest('.profile-dropdown-container:hover') ? 'none' : 'flex');
      }
    },
    
    closeMobileMenu() {
      if (!elements.menu.classList.contains('open')) return;
      
      elements.hamburgerButton.classList.remove('open');
      elements.menu.classList.remove('open');
      elements.menuOverlay.classList.remove('open');
      
      utils.enableScroll();
      state.lastScrollTop = window.pageYOffset;
      
      if (elements.profileDropdown && state.isMobile && 
          !elements.profileDropdown.closest('.profile-dropdown-container:hover')) {
        elements.profileDropdown.style.display = 'none';
      }
    }
  };

  // Profile dropdown
  const profileManager = {
    init() {
      if (!elements.profileContainer || !elements.profileDropdown) return;
      
      // Événements de souris pour ordinateur de bureau
      elements.profileContainer.addEventListener("mouseenter", () => {
        if (!state.isMobile) {
          elements.profileDropdown.style.display = "flex";
        }
      });

      elements.profileContainer.addEventListener("mouseleave", () => {
        if (!state.isMobile) {
          elements.profileDropdown.style.display = "none";
        }
      });
      
      // Événements de clic/tap pour mobile
      elements.profileContainer.addEventListener("click", (e) => {
        if (state.isMobile && !state.isMobileMenuOpen) {
          if (e.target === elements.profileContainer || 
              !e.target.closest('a') || 
              e.target.closest('.profile-icon-container')) {
            e.preventDefault();
            this.toggleDropdown();
          }
        }
      });
      
      // Fermer le menu déroulant lorsque l'on clique en dehors
      document.addEventListener("click", (e) => {
        if (state.isMobile && state.isDropdownOpen && 
            !elements.profileContainer.contains(e.target) && 
            !state.isMobileMenuOpen) {
          elements.profileDropdown.style.display = "none";
          state.isDropdownOpen = false;
        }
      });
    },
    
    toggleDropdown() {
      state.isDropdownOpen = !state.isDropdownOpen;
      elements.profileDropdown.style.display = state.isDropdownOpen ? "flex" : "none";
    }
  };

  // Gestionnaire de thème
  const themeManager = {
    init() {
      if (elements.themeIcons.length === 0) return;
      
      // Initialiser le thème actuel
      const currentTheme = utils.getCookie("theme") || "dark";
      this.updateThemeIcons(currentTheme);
      
      // Configuration des événements de basculement du thème
      elements.sunIcons.forEach(icon => {
        icon.addEventListener("click", (e) => {
          e.stopPropagation();
          this.changeTheme("light");
        });
      });
      
      elements.moonIcons.forEach(icon => {
        icon.addEventListener("click", (e) => {
          e.stopPropagation();
          this.changeTheme("dark");
        });
      });
      
      // Gestion du bouton de basculement du thème
      if (elements.themeToggleBtn) {
        elements.themeToggleBtn.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          
          const isLightMode = document.body.classList.contains("light-theme") || 
                             document.cookie.includes("theme=light");
          
          this.changeTheme(isLightMode ? "dark" : "light");
        });
      }
    },

    changeTheme(newTheme) {
      utils.toggleTheme(newTheme);
      this.updateThemeIcons(newTheme);
      
      // Effet de transition
      document.body.classList.add("theme-transition");
      setTimeout(() => {
        document.body.classList.remove("theme-transition");
      }, config.transitionTimes.themeChange);
    },

    updateThemeIcons(theme) {
      if (!elements.sunIcons || !elements.moonIcons) return;
      
      const isLight = theme === "light";
      elements.sunIcons.forEach(icon => icon.classList.toggle("active", isLight));
      elements.moonIcons.forEach(icon => icon.classList.toggle("active", !isLight));
    }
  };

  // Modal de déconnexion
  const modalManager = {
    init() {
      if (!elements.logoutModal || !elements.logoutBtn) return;
      
      elements.logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        elements.logoutModal.style.display = "flex";
      });
      
      if (elements.closeModalBtn) {
        elements.closeModalBtn.addEventListener("click", () => {
          elements.logoutModal.style.display = "none";
        });
      }
      
      if (elements.cancelBtn) {
        elements.cancelBtn.addEventListener("click", () => {
          elements.logoutModal.style.display = "none";
        });
      }
      
      window.addEventListener("click", (event) => {
        if (event.target === elements.logoutModal) {
          elements.logoutModal.style.display = "none";
        }
      });
    }
  };

  // Initialiser les fonctionnalités
  navManager.init();
  menuManager.init();
  profileManager.init();
  themeManager.init();
  modalManager.init();
});
