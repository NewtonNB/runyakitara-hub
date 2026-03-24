/**
 * Custom Form Validation for Modal Forms
 */

class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.form.setAttribute('novalidate', '');
        this._attachListeners();
        this._attachSubmit();
    }

    _attachListeners() {
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => this._validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) this._validateField(field);
            });
            field.addEventListener('change', () => this._validateField(field));
        });
    }

    _attachSubmit() {
        this.form.addEventListener('submit', e => {
            if (!this._validateAll()) {
                e.preventDefault();
                const first = this.form.querySelector('.is-invalid');
                if (first) { first.focus(); first.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            }
        });
    }

    _validateField(field) {
        // Skip hidden fields and action/id inputs
        if (field.type === 'hidden' || field.type === 'file') return true;

        const value = field.value.trim();
        let error = '';

        if (field.hasAttribute('required') && !value) {
            error = `${this._label(field)} is required`;
        } else if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            error = 'Enter a valid email address';
        } else if (field.hasAttribute('minlength') && value && value.length < +field.getAttribute('minlength')) {
            error = `Must be at least ${field.getAttribute('minlength')} characters`;
        } else if (field.hasAttribute('maxlength') && value && value.length > +field.getAttribute('maxlength')) {
            error = `Must be at most ${field.getAttribute('maxlength')} characters`;
        } else if (field.tagName === 'SELECT' && field.hasAttribute('required') && !value) {
            error = `${this._label(field)} is required`;
        }

        if (error) {
            this._showError(field, error);
            return false;
        } else {
            this._clearError(field);
            return true;
        }
    }

    _validateAll() {
        let valid = true;
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            if (!this._validateField(field)) valid = false;
        });
        return valid;
    }

    _showError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        let err = field.parentElement.querySelector('.error-message');
        if (!err) {
            err = document.createElement('div');
            err.className = 'error-message';
            field.parentElement.appendChild(err);
        }
        err.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i><span>${message}</span>`;
    }

    _clearError(field) {
        field.classList.remove('is-invalid');
        if (field.value.trim()) field.classList.add('is-valid');
        const err = field.parentElement.querySelector('.error-message');
        if (err) err.remove();
    }

    reset() {
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
            const err = field.parentElement?.querySelector('.error-message');
            if (err) err.remove();
        });
    }

    _label(field) {
        const lbl = this.form.querySelector(`label[for="${field.id}"]`);
        if (lbl) return lbl.textContent.replace('*', '').trim();
        return field.name ? field.name.charAt(0).toUpperCase() + field.name.slice(1) : 'This field';
    }
}

// Registry so modal open functions can call validator.reset()
window._validators = {};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        if (form.id) window._validators[form.id] = new FormValidator(form);
        else new FormValidator(form);
    });
});

window.FormValidator = FormValidator;

/**
 * Call this when opening a modal to reset its form's validation state.
 * Usage: resetFormValidation('lessonForm');
 */
window.resetFormValidation = function(formId) {
    const v = window._validators[formId];
    if (v) { v.reset(); return; }
    // Fallback: just strip classes/errors manually
    const form = document.getElementById(formId);
    if (!form) return;
    form.querySelectorAll('input, textarea, select').forEach(f => {
        f.classList.remove('is-invalid', 'is-valid');
        const err = f.parentElement?.querySelector('.error-message');
        if (err) err.remove();
    });
};
