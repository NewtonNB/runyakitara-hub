/**
 * Common Dashboard JavaScript
 * Shared functionality across all admin pages
 */

// Mobile Toggle — sidebar slide in/out with overlay
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');

    if (mobileToggle && sidebar) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileToggle.addEventListener('click', function() {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        });

        overlay.addEventListener('click', closeSidebar);

        // Close on nav item click (mobile)
        sidebar.querySelectorAll('.nav-item').forEach(function(item) {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });
    }
});

// Notification Dropdown Toggle
const notificationBtn = document.getElementById('notificationBtn');
const notificationDropdown = document.getElementById('notificationDropdown');

if (notificationBtn && notificationDropdown) {
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('active');
    });
    
    // Close notification dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && e.target !== notificationBtn) {
            notificationDropdown.classList.remove('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Search functionality
const headerSearch = document.querySelector('.header-search input');
if (headerSearch) {
    headerSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                // Redirect to search page or filter current page
                console.log('Searching for:', searchTerm);
                // You can implement search functionality here
            }
        }
    });
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Confirm delete actions
document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});

// Add loading state to buttons on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.classList.contains('no-loading')) {
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            
            // Re-enable after 3 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 3000);
        }
    });
});

// Smooth scroll to top button
const scrollToTopBtn = document.createElement('button');
scrollToTopBtn.className = 'scroll-to-top';
scrollToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
scrollToTopBtn.setAttribute('aria-label', 'Back to top');
document.body.appendChild(scrollToTopBtn);

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('visible');
    } else {
        scrollToTopBtn.classList.remove('visible');
    }
});

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        headerSearch?.focus();
    }
    
    // Escape to close dropdowns
    if (e.key === 'Escape') {
        notificationDropdown?.classList.remove('active');
    }
});

// Add tooltips to icons
document.querySelectorAll('[title]').forEach(element => {
    element.style.cursor = 'help';
});

console.log('Dashboard common scripts loaded successfully');
