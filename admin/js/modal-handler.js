// Universal Modal Handler for Admin Pages
// Handles opening, closing, and form submission for modals

// Add/remove body class to prevent scrolling when modal is open
function toggleBodyScroll(shouldLock) {
    if (shouldLock) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
}

function openAddModal(modalId, formId, titleElement, submitBtnElement, actionField) {
    const titles = {
        'Article': 'Add Article',
        'Word': 'Add Word',
        'Lesson': 'Add Lesson',
        'Proverb': 'Add Proverb',
        'Grammar': 'Add Grammar Rule',
        'Translation': 'Add Translation',
        'Media': 'Add Media',
        'User': 'Add User'
    };
    
    const titleText = Object.keys(titles).find(key => titleElement.includes(key));
    document.getElementById(titleElement).textContent = titles[titleText] || 'Add Item';
    document.getElementById(submitBtnElement).textContent = document.getElementById(titleElement).textContent;
    document.getElementById(actionField).value = 'add';
    document.getElementById(formId).reset();
    
    // Clear all hidden ID fields
    const idFields = document.getElementById(formId).querySelectorAll('input[type="hidden"][name="id"]');
    idFields.forEach(field => field.value = '');
    
    document.getElementById(modalId).classList.add('active');
    toggleBodyScroll(true);
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    toggleBodyScroll(false);
}

function openDeleteModal(deleteModalId, deleteIdField, deleteMsgElement, id, name) {
    document.getElementById(deleteIdField).value = id;
    document.getElementById(deleteMsgElement).innerHTML = `Are you sure you want to delete "<strong>${name}</strong>"? This action cannot be undone.`;
    document.getElementById(deleteModalId).classList.add('active');
    toggleBodyScroll(true);
}

function closeDeleteModal(deleteModalId) {
    document.getElementById(deleteModalId).classList.remove('active');
    toggleBodyScroll(false);
}

// Setup modal event listeners
function setupModalListeners(modalId, deleteModalId) {
    // Close modal on overlay click
    const mainModal = document.getElementById(modalId);
    if (mainModal) {
        mainModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(modalId);
            }
        });
    }
    
    const delModal = document.getElementById(deleteModalId);
    if (delModal) {
        delModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal(deleteModalId);
            }
        });
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal(modalId);
            closeDeleteModal(deleteModalId);
        }
    });
}
