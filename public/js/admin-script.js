/**
 * Global Admin Modal & Action Controller
 * Lumpia Ubi Dashboard
 */

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.add('active');
    modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('active');
    modal.style.display = 'none';
}

function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    });
}

// Menutup modal saat klik area luar/overlay
window.addEventListener('click', function (event) {
    if (event.target && event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
        event.target.style.display = 'none';
    }
});

// Menutup modal saat menekan tombol Escape
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-confirm') || 'Lanjutkan aksi ini?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const promoForm = document.getElementById('adminPromoForm');
    if (promoForm && promoForm.getAttribute('action') === '#') {
        promoForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const titleInput = document.getElementById('adminPromoTitle');
            const statusBox = document.getElementById('adminPromoPreviewStatus');
            if (statusBox && titleInput) {
                const safeTitle = titleInput.value.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;');
                statusBox.textContent = `Perubahan promo "${safeTitle}" siap disimpan (hubungkan ke route admin.promos.store untuk persistensi DB).`;
            }
        });
    }
});