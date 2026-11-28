/**
 * Global Admin Modal & Action Controller
 * Lumpia Ubi Dashboard
 */

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.add('active');
    modal.classList.add('show');
    modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('active');
    modal.classList.remove('show');
    modal.style.display = 'none';
}

function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
        modal.classList.remove('active');
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
}

// Menutup modal saat klik area luar/overlay
window.addEventListener('click', function (event) {
    if (event.target && event.target.classList && event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
        event.target.classList.remove('show');
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

function initAdminLoginApprovalPolling() {
    const container = document.getElementById('adminApprovalWaiting');
    if (!container) return;

    const statusUrl = container.dataset.statusUrl;
    const msgEl = document.getElementById('statusMessage');
    if (!statusUrl || !msgEl) return;

    const timer = setInterval(async () => {
        try {
            const res = await fetch(statusUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) return;

            const data = await res.json();

            if (data.status === 'approved' && data.redirect_url) {
                clearInterval(timer);
                msgEl.textContent = 'Akses disetujui! Mengalihkan ke Dashboard Admin...';
                window.location.href = data.redirect_url;
            } else if (data.status === 'rejected') {
                clearInterval(timer);
                msgEl.innerHTML = '<strong class="approval-status-rejected">Permintaan login ditolak melalui email.</strong>';
            } else if (data.status === 'expired') {
                clearInterval(timer);
                msgEl.innerHTML = '<strong class="approval-status-expired">Permintaan login kedaluwarsa. Silakan login ulang.</strong>';
            }
        } catch (e) {
            // Abaikan error jaringan sementara saat polling
        }
    }, 3000);
}

document.addEventListener('DOMContentLoaded', initAdminLoginApprovalPolling);

function initAdminReportsFeatures() {
    const printBtn = document.getElementById('btnPrintReport');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    const searchInput = document.getElementById('reportQuickSearch');
    const rows = Array.from(document.querySelectorAll('#reportsTable tbody tr.report-data-row'));
    const countEl = document.getElementById('reportVisibleCount');

    if (!searchInput || !rows.length || !countEl) return;

    searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const haystack = row.getAttribute('data-search') || '';
            const match = !keyword || haystack.indexOf(keyword) !== -1;

            row.style.display = match ? '' : 'none';
            if (match) {
                visibleCount += 1;
            }
        });

        countEl.textContent = String(visibleCount);
    });
}

document.addEventListener('DOMContentLoaded', initAdminReportsFeatures);