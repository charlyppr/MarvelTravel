/**
 * Marvel Travel - Reservation System JavaScript
 * Handles all functionalities for the 4 reservation steps
 */

document.addEventListener('DOMContentLoaded', function() {
    // Determine which step page we're on
    const pathname = window.location.pathname;
    const isEtape1 = pathname.includes('etape1.php');
    const isEtape2 = pathname.includes('etape2.php');
    const isEtape3 = pathname.includes('etape3.php');
    const isEtape4 = pathname.includes('etape4.php');

    // Common functionalities across steps
    initializeCommonFunctions();

    // Step-specific functionalities
    if (isEtape1) {
        initializeEtape1();
    } else if (isEtape2) {
        initializeEtape2();
    } else if (isEtape3) {
        initializeEtape3();
    } else if (isEtape4) {
        initializeEtape4();
    }
});

/**
 * Common functionalities used across multiple steps
 */
function initializeCommonFunctions() {
    // Navigation toggles, common UI elements, etc.
}

/**
 * Etape 1 - Dates and number of travelers
 */
function initializeEtape1() {
    // Price update based on number of people
    const nbPersonneInput = document.getElementById('nb_personne');
    if (nbPersonneInput) {
        nbPersonneInput.addEventListener('input', function() {
            updatePriceDisplay();
        });
        // Initialize price display on page load
        updatePriceDisplay();
    }

    // Form validation
    const reservationForm = document.getElementById('reservationForm');
    const submitButton = reservationForm.querySelector('button[type="submit"]');
    
    // Disable submit button by default if dates are not set
    validateEtape1Form();
    
    // Check date fields on input
    const dateDebutInput = document.getElementById('date-debut');
    const dateFinInput = document.getElementById('date-fin');
    
    if (dateDebutInput) {
        dateDebutInput.addEventListener('input', validateEtape1Form);
    }
    
    if (dateFinInput) {
        dateFinInput.addEventListener('input', validateEtape1Form);
    }
    
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            if (!validateEtape1Form()) {
                e.preventDefault();
                alert('Veuillez sélectionner des dates d\'arrivée et de départ.');
            }
        });
    }

    // Initialize date fields
    initializeDateFields();
}

/**
 * Validates the dates form and enables/disables submit button
 */
function validateEtape1Form() {
    const dateDebutInput = document.getElementById('date-debut');
    const dateFinInput = document.getElementById('date-fin');
    const submitButton = document.querySelector('#reservationForm button[type="submit"]');
    
    if (!dateDebutInput || !dateFinInput || !submitButton) {
        return false;
    }
    
    const isValid = dateDebutInput.value && dateFinInput.value;
    
    if (submitButton) {
        submitButton.disabled = !isValid;
    }
    
    return isValid;
}

/**
 * Updates price display based on number of people
 */
function updatePriceDisplay() {
    const nbPersonnes = parseInt(document.getElementById('nb_personne').value);
    const prixBaseElement = document.querySelector('.price-row:first-child span:last-child');
    const prixBase = parseFloat(prixBaseElement.textContent.replace(/[^\d,]/g, '').replace(',', '.'));
    const prixTotal = nbPersonnes * prixBase;

    document.getElementById('nb_personnes_display').textContent = nbPersonnes;
    document.getElementById('prix_total').textContent = formatPrice(prixTotal) + ' €';
}

/**
 * Format price with French number formatting
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price).replace('€', '').trim();
}

/**
 * Initialize date input fields and setup visibility
 */
function initializeDateFields() {
    const dateDebutInput = document.getElementById('date-debut');
    const dateFinInput = document.getElementById('date-fin');
    const dateDebutVisible = document.getElementById('date-debut-visible');
    const dateFinVisible = document.getElementById('date-fin-visible');

    // Initialize visible fields with formatted dates if values exist
    if (dateDebutInput && dateDebutInput.value) {
        const date = new Date(dateDebutInput.value);
        const options = { day: 'numeric', month: 'short' };
        dateDebutVisible.value = date.toLocaleDateString('fr-FR', options);
    }

    if (dateFinInput && dateFinInput.value) {
        const date = new Date(dateFinInput.value);
        const options = { day: 'numeric', month: 'short' };
        dateFinVisible.value = date.toLocaleDateString('fr-FR', options);
    }
}

/**
 * Etape 2 - Travelers information
 */
function initializeEtape2() {
    // Autofill button functionality
    const autoFillButton = document.getElementById('autofill-button');
    if (autoFillButton) {
        autoFillButton.addEventListener('click', function() {
            autofillPrimaryTraveler();
        });
    }

    // Format passport fields
    const passportInputs = document.querySelectorAll('input[id^="passport_"]');
    passportInputs.forEach(input => {
        // Format existing values
        if (input.value) {
            formatPassport(input);
        }

        // Add event listeners for input formatting
        input.addEventListener('input', function() {
            formatPassport(this);
            validatePassport(this);
            validateForm();
        });

        input.addEventListener('keydown', function(e) {
            // Allow only valid keys: numbers, navigation, etc.
            if (
                e.key === 'Backspace' ||
                e.key === 'Delete' ||
                e.key === 'ArrowLeft' ||
                e.key === 'ArrowRight' ||
                e.key === 'Tab' ||
                (e.key >= '0' && e.key <= '9')
            ) {
                return true;
            }

            // Block all other characters
            e.preventDefault();
            return false;
        });
    });

    // Add validation for all required fields
    const requiredInputs = document.querySelectorAll('input[required], select[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateForm();
        });
        input.addEventListener('change', function() {
            validateForm();
        });
    });

    // Initial form validation
    validateForm();
}

/**
 * Validate form and update submit button state
 */
function validateForm() {
    const form = document.getElementById('travelersForm');
    const submitButton = document.getElementById('submit-button');
    let isValid = true;

    // Check all required fields
    const requiredInputs = form.querySelectorAll('input[required], select[required]');
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
        }
        
        // Special validation for passport numbers
        if (input.id.startsWith('passport_')) {
            const digitsOnly = input.value.replace(/[^\d]/g, '');
            if (digitsOnly.length !== 10 && digitsOnly.length !== 0) {
                isValid = false;
            }
            
            // If there's no value, we don't show red border but the field is still required
            if (digitsOnly.length === 0) {
                isValid = false;
            }
        }
    });

    // Check if any form-field has invalid class
    const invalidFields = form.querySelectorAll('.form-field.invalid');
    if (invalidFields.length > 0) {
        isValid = false;
    }

    // Update submit button state
    if (submitButton) {
        submitButton.disabled = !isValid;
    }
}

/**
 * Validate passport field
 */
function validatePassport(input) {
    const errorElement = document.getElementById(input.id + '_error');
    if (!errorElement) return;
    
    // Get the form-field container
    const formField = input.closest('.form-field');
    
    const digitsOnly = input.value.replace(/[^\d]/g, '');
    
    if (digitsOnly.length === 0) {
        // Input is empty, remove any error message
        errorElement.style.opacity = '0';
        formField.classList.remove('invalid');
    } else if (digitsOnly.length !== 10) {
        // Input has content but not exactly 10 digits
        errorElement.style.opacity = '1';
        formField.classList.add('invalid');
    } else {
        // Input has exactly 10 digits
        errorElement.style.opacity = '0';
        formField.classList.remove('invalid');
    }
}

/**
 * Autofill form with primary traveler information
 */
function autofillPrimaryTraveler() {
    // These values should be set as data attributes on the button or a hidden input
    // For now, we'll retrieve them from the page in runtime context
    const userData = {
        lastName: document.querySelector('#autofill-button').getAttribute('data-lastname'),
        firstName: document.querySelector('#autofill-button').getAttribute('data-firstname'),
        civilite: document.querySelector('#autofill-button').getAttribute('data-civilite'),
        dateNaissance: document.querySelector('#autofill-button').getAttribute('data-birthdate'),
        nationalite: document.querySelector('#autofill-button').getAttribute('data-nationality'),
        passport: document.querySelector('#autofill-button').getAttribute('data-passport')
    };

    // Fill the fields
    if (document.getElementById('nom_1')) document.getElementById('nom_1').value = userData.lastName;
    if (document.getElementById('prenom_1')) document.getElementById('prenom_1').value = userData.firstName;
    
    if (userData.civilite && document.getElementById('civilite_1')) {
        document.getElementById('civilite_1').value = userData.civilite;
    }
    
    if (userData.dateNaissance && document.getElementById('date_naissance_1')) {
        document.getElementById('date_naissance_1').value = userData.dateNaissance;
    }
    
    if (userData.nationalite && document.getElementById('nationalite_1')) {
        document.getElementById('nationalite_1').value = userData.nationalite;
    }
    
    if (userData.passport && document.getElementById('passport_1')) {
        document.getElementById('passport_1').value = userData.passport;
        formatPassport(document.getElementById('passport_1'));
        validatePassport(document.getElementById('passport_1'));
    }
    
    // Trigger validation after autofill
    validateForm();
}

/**
 * Format passport number with spaces (XXX XXX XXX X)
 */
function formatPassport(input) {
    // Remove all non-digits
    let value = input.value.replace(/[^\d]/g, '');
    
    // Limit to 10 digits
    value = value.slice(0, 10);
    
    // Format with spaces (XXX XXX XXX X)
    let formattedValue = '';
    for (let i = 0; i < value.length; i++) {
        if (i === 3 || i === 6 || i === 9) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    // Update input value
    input.value = formattedValue;
}

/**
 * Etape 3 - Options selection
 */
function initializeEtape3() {
    // Handle participant toggles for options
    const participantToggles = document.querySelectorAll('.participant-toggle');
    const prixOptionsElement = document.querySelector('.options-row span:last-child');
    const prixTotalElement = document.querySelector('.price-total span:last-child');
    
    // Extract the base price once
    const prixBaseText = document.querySelector('.price-row:first-child span:last-child').textContent;
    const prixBase = parseFloat(prixBaseText.replace(/[^\d,]/g, '').replace(',', '.'));
    
    let prixOptions = 0;
    
    // Initialize price options from existing selections
    calculateInitialOptionsPrice();
    
    // Handle clicks on participant toggles
    participantToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            handleParticipantToggle(e, this);
            updateOptionsPrice();
        });
    });

    /**
     * Calculate initial price of selected options
     */
    function calculateInitialOptionsPrice() {
        const allCheckboxes = document.querySelectorAll('.participant-checkbox:checked');
        
        allCheckboxes.forEach(box => {
            const optionItem = box.closest('.option-item');
            const priceText = optionItem.querySelector('.option-price').textContent;
            const price = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
            prixOptions += price;
        });
        
        // Update the display
        if (prixOptionsElement) {
            prixOptionsElement.textContent = formatPrice(prixOptions) + ' €';
        }
        
        if (prixTotalElement) {
            prixTotalElement.textContent = formatPrice(prixBase + prixOptions) + ' €';
        }
    }

    /**
     * Handle participant toggle click
     */
    function handleParticipantToggle(e, toggleElement) {
        const checkbox = toggleElement.querySelector('input[type="checkbox"]');
        if (e.target.tagName.toLowerCase() !== 'input') {
            checkbox.checked = !checkbox.checked;
            e.preventDefault();
        }
        
        // Update toggle styling
        if (checkbox.checked) {
            toggleElement.classList.add('selected');
        } else {
            toggleElement.classList.remove('selected');
        }
    }

    /**
     * Update options price based on selected participants
     */
    function updateOptionsPrice() {
        const allCheckboxes = document.querySelectorAll('.participant-checkbox');
        prixOptions = 0;
        
        allCheckboxes.forEach(box => {
            if (box.checked) {
                // Extract price from the DOM
                const optionItem = box.closest('.option-item');
                const priceText = optionItem.querySelector('.option-price').textContent;
                const price = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
                prixOptions += price;
            }
        });
        
        // Update price displays with animation
        updatePriceDisplays();
    }

    /**
     * Update price displays with animation
     */
    function updatePriceDisplays() {
        if (prixOptionsElement) {
            prixOptionsElement.textContent = formatPrice(prixOptions) + ' €';
            prixOptionsElement.classList.add('price-updated');
        }
        
        if (prixTotalElement) {
            prixTotalElement.textContent = formatPrice(prixBase + prixOptions) + ' €';
            prixTotalElement.classList.add('price-updated');
        }
        
        // Remove animation class after transition
        setTimeout(() => {
            if (prixOptionsElement) prixOptionsElement.classList.remove('price-updated');
            if (prixTotalElement) prixTotalElement.classList.remove('price-updated');
        }, 700);
    }
}

/**
 * Etape 4 - Payment
 */
function initializeEtape4() {
    // Handle promo code application and payment form validation
    const promoForm = document.querySelector('.promo-code-form');
    const promoInput = document.getElementById('promo-code');
    const promoMessage = document.getElementById('promo-message');
    
    if (promoForm) {
        // Promo code form submission - optional if already handled by page reloading
        promoForm.addEventListener('submit', function(e) {
            // You can add validation here if needed
        });
    }
    
    // Any additional payment page specific code
} 