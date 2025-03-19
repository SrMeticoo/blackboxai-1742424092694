// Preview handlers for logo and banner images
document.getElementById('logo-image')?.addEventListener('input', function() {
    const preview = document.getElementById('logo-preview');
    preview.style.backgroundImage = `url('${this.value}')`;
});

document.getElementById('banner-image')?.addEventListener('input', function() {
    const preview = document.getElementById('banner-preview');
    preview.style.backgroundImage = `url('${this.value}')`;
});

// Toggle auto-release minutes input based on checkbox
document.getElementById('auto-release-enabled')?.addEventListener('change', function() {
    const minutesInput = document.getElementById('auto-release-minutes');
    minutesInput.disabled = !this.checked;
});

// Phone number validation
document.getElementById('whatsapp-number')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Confirmation modal handling
const confirmModal = document.getElementById('confirm-modal');
const confirmAction = document.getElementById('confirm-action');
const confirmCancel = document.getElementById('confirm-cancel');
let pendingAction = null;

// Function to show confirmation modal
const showConfirmModal = (title, message, action) => {
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-message').textContent = message;
    pendingAction = action;
    confirmModal.classList.remove('hidden');
};

// Function to hide confirmation modal
const hideConfirmModal = () => {
    confirmModal.classList.add('hidden');
    pendingAction = null;
};

// Handle confirmation modal buttons
confirmAction?.addEventListener('click', async () => {
    if (pendingAction) {
        await pendingAction();
        pendingAction = null;
    }
    hideConfirmModal();
});

confirmCancel?.addEventListener('click', hideConfirmModal);

// Function to mark balls as paid
const confirmBlockBalls = (itemId, numbers) => {
    if (!numbers) return;
    
    const numberArray = numbers.split(',');
    showConfirmModal(
        'Confirmar Pago',
        `¿Está seguro que desea marcar las balotas ${numbers} como pagadas? Esta acción no se puede deshacer.`,
        async () => {
            try {
                const response = await fetch('../admin/actions/block-balls.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        numbers: numberArray
                    })
                });

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message);
                }

                // Reload page to show updated status
                window.location.reload();

            } catch (error) {
                alert('Error al marcar las balotas como pagadas: ' + error.message);
            }
        }
    );
};

// Form validation
document.getElementById('create-item-form')?.addEventListener('submit', function(e) {
    const totalBalls = parseInt(document.getElementById('total-balls').value);
    if (totalBalls <= 0 || totalBalls > 99999) {
        e.preventDefault();
        alert('El número de balotas debe estar entre 1 y 99999');
    }
});

// Show success/error messages
document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide success/error messages after 5 seconds
    const messages = document.querySelectorAll('.alert');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});

// Preview image URLs when pasted
const previewImageUrl = (input, previewElement) => {
    const url = input.value.trim();
    if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
        const img = new Image();
        img.onload = () => {
            previewElement.style.backgroundImage = `url('${url}')`;
            previewElement.classList.remove('bg-red-50');
            previewElement.classList.add('bg-green-50');
        };
        img.onerror = () => {
            previewElement.style.backgroundImage = '';
            previewElement.classList.remove('bg-green-50');
            previewElement.classList.add('bg-red-50');
        };
        img.src = url;
    } else {
        previewElement.style.backgroundImage = '';
        previewElement.classList.remove('bg-green-50', 'bg-red-50');
    }
};

// Add image preview functionality to all image URL inputs
document.querySelectorAll('input[type="url"]').forEach(input => {
    const previewId = input.id + '-preview';
    const preview = document.getElementById(previewId);
    if (preview) {
        input.addEventListener('input', () => previewImageUrl(input, preview));
        input.addEventListener('paste', () => setTimeout(() => previewImageUrl(input, preview), 100));
    }
});