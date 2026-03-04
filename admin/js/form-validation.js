/**
 * Custom Form Validation System
 * Provides real-time validation with custom styling
 */

class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.errors = {};
        this.init();
    }

    init() {
        // Add novalidate to prevent browser default validation
        this.form.setAttribute('novalidate', '');
        
        // Add real-time validation on input
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    this.validateField(input);
                }
            });
        });

        // Validate on submit
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.focusFirstError();
            }
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const name = field.name;
        let isValid = true;
        let errorMessage = '';

        // Remove previous error
        this.clearFieldError(field);

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = this.getFieldLabel(field) + ' is required';
        }

        // Email validation
        if (isValid && field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }

        // URL validation
        if (isValid && field.type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                isValid = false;
                errorMessage = 'Please enter a valid URL';
            }
        }

        // Number validation
        if (isValid && field.type === 'number' && value) {
            const num = parseFloat(value);
            if (isNaN(num)) {
                isValid = false;
                errorMessage = 'Please enter a valid number';
            }
            if (field.hasAttribute('min') && num < parseFloat(field.min)) {
                isValid = false;
                errorMessage = `Value must be at least ${field.min}`;
            }
            if (field.hasAttribute('max') && num > parseFloat(field.max)) {
                isValid = false;
                errorMessage = `Value must be at most ${field.max}`;
            }
        }

        // Min length validation
        if (isValid && field.hasAttribute('minlength') && value) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (value.length < minLength) {
                isValid = false;
                errorMessage = `Must be at least ${minLength} characters`;
            }
        }

        // Max length validation
        if (isValid && field.hasAttribute('maxlength') && value) {
            const maxLength = parseInt(field.getAttribute('maxlength'));
            if (value.length > maxLength) {
                isValid = false;
                errorMessage = `Must be at most ${maxLength} characters`;
            }
        }

        // Pattern validation
        if (isValid && field.hasAttribute('pattern') && value) {
            const pattern = new RegExp(field.getAttribute('pattern'));
            if (!pattern.test(value)) {
                isValid = false;
                errorMessage = field.getAttribute('data-pattern-message') || 'Invalid format';
            }
        }

        // Custom validation
        if (isValid && field.hasAttribute('data-validate')) {
            const validationType = field.getAttribute('data-validate');
            const customValidation = this.customValidations[validationType];
            if (customValidation) {
                const result = customValidation(value, field);
                if (!result.valid) {
                    isValid = false;
                    errorMessage = result.message;
                }
            }
        }

        if (!isValid) {
            this.showFieldError(field, errorMessage);
            this.errors[name] = errorMessage;
        } else {
            delete this.errors[name];
        }

        return isValid;
    }

    validateForm() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');

        // Create or update error message
        let errorDiv = field.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            field.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;

        // Add shake animation
        field.style.animation = 'shake 0.3s';
        setTimeout(() => {
            field.style.animation = '';
        }, 300);
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const errorDiv = field.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    focusFirstError() {
        const firstInvalid = this.form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    getFieldLabel(field) {
        const label = this.form.querySelector(`label[for="${field.id}"]`);
        if (label) {
            return label.textContent.replace('*', '').trim();
        }
        return field.name.charAt(0).toUpperCase() + field.name.slice(1);
    }

    // Custom validation rules
    customValidations = {
        username: (value) => {
            const regex = /^[a-zA-Z0-9_]{3,20}$/;
            return {
                valid: regex.test(value),
                message: 'Username must be 3-20 characters (letters, numbers, underscore only)'
            };
        },
        password: (value) => {
            if (value.length < 6) {
                return {
                    valid: false,
                    message: 'Password must be at least 6 characters'
                };
            }
            return { valid: true };
        },
        phone: (value) => {
            const regex = /^[\d\s\-\+\(\)]+$/;
            return {
                valid: regex.test(value),
                message: 'Please enter a valid phone number'
            };
        },
        noSpecialChars: (value) => {
            const regex = /^[a-zA-Z0-9\s]+$/;
            return {
                valid: regex.test(value),
                message: 'Special characters are not allowed'
            };
        }
    };

    // Add custom validation rule
    addCustomValidation(name, validationFn) {
        this.customValidations[name] = validationFn;
    }
}

// Auto-initialize forms with data-validate attribute
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        new FormValidator(form);
    });
});

// Export for manual initialization
window.FormValidator = FormValidator;
