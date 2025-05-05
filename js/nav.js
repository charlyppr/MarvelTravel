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
      cancelLogoutBtn: "#cancel-nav-logout"
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

  // Gestionnaire de la barre de navigation
  const navManager = {
    navBarElement: null,
    lastScrollTop: 0,
    isInitialLoad: true,

    init() {
      this.navBarElement = document.querySelector(config.navSelectors.navbar);
      if (!this.navBarElement) return;
      
      window.navBarElement = this.navBarElement; // Pour compatibilité
      
      // Assurer que la nav est visible au chargement
      this.navBarElement.style.transform = "translateY(0)";
      
      this.setupEventListeners();
    },

    setupEventListeners() {
      // Gestion du scroll pour cacher/montrer la navbar
      window.addEventListener("scroll", () => this.handleScroll());
      
      // Montrer la navbar lors du survol en haut de l'écran
      document.addEventListener("mousemove", (e) => this.handleMouseMove(e));
    },

    handleScroll() {
      if (window.innerWidth <= config.breakpoints.mobile) return;
      
      const scrollTop = document.documentElement.scrollTop;
      this.navBarElement.style.transition = `transform ${config.transitionTimes.navHide} ease`;
      
      // Ignorer le premier événement de défilement
      if (this.isInitialLoad) {
        this.lastScrollTop = scrollTop;
        this.isInitialLoad = false;
        return;
      }
      
      // Cacher la navbar en défilant vers le bas, la montrer en défilant vers le haut
      if (scrollTop > this.lastScrollTop) {
        this.navBarElement.style.transform = "translateY(-100%)";
      } else {
        this.navBarElement.style.transform = "translateY(0)";
      }
      
      // Mettre à jour la position de défilement
      this.lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    },

    handleMouseMove(e) {
      if (window.innerWidth <= config.breakpoints.mobile || !this.navBarElement) return;
      
      if (e.clientY <= 70) {
        this.navBarElement.style.transition = `transform ${config.transitionTimes.navShow} ease`;
        this.navBarElement.style.transform = "translateY(0)";
      }
    }
  };

  // Gestionnaire des effets du menu
  const menuEffectsManager = {
    menuItems: null,

    init() {
      if (window.innerWidth <= config.breakpoints.mobile) return;
      
      this.menuItems = document.querySelectorAll(config.navSelectors.menuItems);
      if (this.menuItems.length === 0) return;
      
      this.setupEventListeners();
    },

    setupEventListeners() {
      this.menuItems.forEach(item => {
        item.addEventListener("mouseover", () => this.handleHover(item, true));
        item.addEventListener("mouseout", () => this.handleHover(item, false));
      });
    },

    handleHover(hoveredElement, isEntering) {
      this.menuItems.forEach(item => {
        if (item !== hoveredElement) {
          item.classList.toggle("hovered", isEntering);
        }
      });
    }
  };

  // Gestionnaire du menu déroulant du profil
  const profileDropdownManager = {
    container: null,
    dropdown: null,

    init() {
      this.container = document.querySelector(config.navSelectors.profileDropdown);
      if (!this.container) return;
      
      this.dropdown = document.querySelector(config.navSelectors.profileDropdownMenu);
      if (!this.dropdown) return;
      
      this.setupEventListeners();
    },

    setupEventListeners() {
      this.container.addEventListener("mouseenter", () => {
        this.dropdown.style.display = "flex";
      });

      this.container.addEventListener("mouseleave", () => {
        this.dropdown.style.display = "none";
      });
    }
  };

  // Gestionnaire de thème
  const themeManager = {
    themeIcons: null,
    sunIcons: null,
    moonIcons: null,
    themeToggleBtn: null,

    init() {
      this.themeIcons = document.querySelectorAll(config.navSelectors.themeIcons);
      if (this.themeIcons.length === 0) return;
      
      this.getCookieHelper = typeof getCookie === "function" ? getCookie : this.fallbackGetCookie;
      this.toggleThemeHelper = typeof toggleTheme === "function" ? toggleTheme : this.fallbackToggleTheme;
      
      this.setCurrentTheme();
      this.setupThemeIcons();
      this.setupSimpleToggle();
    },

    setCurrentTheme() {
      const currentTheme = this.getCookieHelper("theme") || "dark";
      this.updateThemeIcons(currentTheme);
    },

    setupThemeIcons() {
      this.sunIcons = document.querySelectorAll(config.navSelectors.sunIcon);
      this.moonIcons = document.querySelectorAll(config.navSelectors.moonIcon);
      
      this.sunIcons.forEach(icon => {
        icon.addEventListener("click", () => this.changeTheme("light"));
      });
      
      this.moonIcons.forEach(icon => {
        icon.addEventListener("click", () => this.changeTheme("dark"));
      });
    },

    setupSimpleToggle() {
      this.themeToggleBtn = document.getElementById("themeToggleBtn");
      if (!this.themeToggleBtn) return;
      
      this.themeToggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        
        const isLightMode = document.body.classList.contains("light-theme") || 
                           document.cookie.includes("theme=light");
        
        this.changeTheme(isLightMode ? "dark" : "light");
      });
    },

    changeTheme(newTheme) {
      this.toggleThemeHelper(newTheme);
      this.updateThemeIcons(newTheme);
      this.addTransitionEffect();
    },

    updateThemeIcons(theme) {
      if (!this.sunIcons || !this.moonIcons) return;
      
      if (theme === "light") {
        this.sunIcons.forEach(icon => icon.classList.add("active"));
        this.moonIcons.forEach(icon => icon.classList.remove("active"));
      } else {
        this.moonIcons.forEach(icon => icon.classList.add("active"));
        this.sunIcons.forEach(icon => icon.classList.remove("active"));
      }
    },

    addTransitionEffect() {
      document.body.classList.add("theme-transition");
      setTimeout(() => {
        document.body.classList.remove("theme-transition");
      }, config.transitionTimes.themeChange);
    },

    fallbackGetCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
      return null;
    },

    fallbackToggleTheme(theme) {
      document.cookie = `theme=${theme};path=/;max-age=31536000`;
      document.body.classList.remove("light-theme", "dark-theme");
      document.body.classList.add(`${theme}-theme`);
    }
  };

  // Gestionnaire du modal de déconnexion
  const logoutModalManager = {
    modal: null,
    logoutBtn: null,
    closeBtn: null,
    cancelBtn: null,

    init() {
      this.modal = document.getElementById("nav-logout-modal");
      this.logoutBtn = document.getElementById("nav-logout-button");
      
      if (!this.modal || !this.logoutBtn) return;
      
      this.closeBtn = document.querySelector(config.navSelectors.closeModalBtn);
      this.cancelBtn = document.getElementById("cancel-nav-logout");
      
      this.setupEventListeners();
    },

    setupEventListeners() {
      this.logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.openModal();
      });
      
      if (this.closeBtn) {
        this.closeBtn.addEventListener("click", () => this.closeModal());
      }
      
      if (this.cancelBtn) {
        this.cancelBtn.addEventListener("click", () => this.closeModal());
      }
      
      window.addEventListener("click", (event) => {
        if (event.target === this.modal) {
          this.closeModal();
        }
      });
    },

    openModal() {
      this.modal.style.display = "flex";
    },

    closeModal() {
      this.modal.style.display = "none";
    }
  };

  // Initialisation des gestionnaires
  navManager.init();
  menuEffectsManager.init();
  profileDropdownManager.init();
  themeManager.init();
  logoutModalManager.init();
});
