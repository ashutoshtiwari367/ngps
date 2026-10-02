/**
 * School Management System - Client Side JavaScript
 */

// Toggle Mobile Sidebar Drawer
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (sidebar && backdrop) {
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    }
}

// Auto Calculate Remaining Fee Amount
function calculateRemainingFee() {
    const totalInput = document.getElementById('total_amount');
    const paidInput = document.getElementById('paid_amount');
    const remainingInput = document.getElementById('remaining_amount');
    const statusSelect = document.getElementById('payment_status');

    if (totalInput && paidInput && remainingInput) {
        const total = parseFloat(totalInput.value) || 0;
        const paid = parseFloat(paidInput.value) || 0;
        const remaining = Math.max(0, total - paid);
        
        remainingInput.value = remaining.toFixed(2);

        if (statusSelect) {
            if (paid >= total && total > 0) {
                statusSelect.value = 'Paid';
            } else if (paid > 0 && paid < total) {
                statusSelect.value = 'Partial';
            } else {
                statusSelect.value = 'Pending';
            }
        }
    }
}

// Preview Uploaded Image
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Document Ready Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide Flash Messages after 5 seconds
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            setTimeout(() => flashAlert.remove(), 300);
        }, 5000);
    }

    // Attach fee calculation listeners if present
    const totalInput = document.getElementById('total_amount');
    const paidInput = document.getElementById('paid_amount');
    
    if (totalInput) totalInput.addEventListener('input', calculateRemainingFee);
    if (paidInput) paidInput.addEventListener('input', calculateRemainingFee);
});
