import './bootstrap';
// Custom JavaScript for Network Monitor

$(document).ready(function() {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
    });

    // Close sidebar on mobile when clicking outside
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#sidebar').length &&
                !$(e.target).closest('#sidebarCollapse').length) {
                $('#sidebar').removeClass('active');
                $('#content').removeClass('active');
            }
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();

    // Auto-refresh dashboard data
    if (window.location.pathname === '/dashboard') {
        setInterval(refreshDashboard, 30000); // Refresh every 30 seconds
    }

    // Load unread alerts count
    loadUnreadAlerts();
    setInterval(loadUnreadAlerts, 30000);

    // Add fade-in animation to cards
    $('.card').addClass('fade-in');

    // Confirm before leaving form
    $('form').on('change', ':input', function() {
        $(this).closest('form').addClass('dirty');
    });

    window.addEventListener('beforeunload', function(e) {
        if ($('form.dirty').length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Remove dirty class on form submission
    $('form').on('submit', function() {
        $(this).removeClass('dirty');
    });
});

// Dashboard refresh function
function refreshDashboard() {
    $.get('/dashboard/data', function(data) {
        // Update stats
        updateDashboardStats(data);
    });
}

// Update dashboard statistics
function updateDashboardStats(data) {
    // Update device counts
    if (data.deviceStats) {
        $('#totalDevices').text(data.deviceStats.total);
        $('#onlineDevices').text(data.deviceStats.online);
        $('#offlineDevices').text(data.deviceStats.offline);
    }
}

// Load unread alerts count
function loadUnreadAlerts() {
    $.get('/alerts/count/unread', function(data) {
        const count = data.count;
        const badge = $('#unread-alerts');

        if (count > 0) {
            badge.text(count).show();
            if (count > 10) {
                badge.addClass('pulse');
            }
        } else {
            badge.hide().removeClass('pulse');
        }
    });
}

// Show loading spinner
function showSpinner() {
    const spinner = `
        <div class="spinner-overlay">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-2">Processing...</h5>
            </div>
        </div>
    `;
    $('body').append(spinner);
}

// Hide loading spinner
function hideSpinner() {
    $('.spinner-overlay').remove();
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = `
        <div class="toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    $('body').append(toast);
    const toastEl = $('.toast').last();
    const bsToast = new bootstrap.Toast(toastEl[0], { delay: 3000 });
    bsToast.show();

    toastEl.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Copied to clipboard!', 'success');
    }).catch(function() {
        showToast('Failed to copy', 'danger');
    });
}

// Confirm action
function confirmAction(message, callback) {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Format bytes
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        $('input[name="search"]').first().focus();
    }

    // Ctrl + N for new device
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = '/devices/create';
    }

    // Escape to close modals
    if (e.key === 'Escape') {
        $('.modal').modal('hide');
    }
});
