/**
 * Script Modul Customer - Lumpia Ubi Store
 */

let currentPriceMedium = 0;
let currentPriceLarge = 0;
let cart = [];
let calculatedDistanceKm = 0;

// Koordinat Kampus Pusat Universitas Mataram (Jl. Majapahit No. 62, Gomong, Selaparang)
const UNRAM_LAT = -8.58819;
const UNRAM_LNG = 116.09288;
const UNRAM_FREE_RADIUS_KM = 0.8; // Area Unram = Rp 0, di luar itu Rp 1.000 / km

function formatRupiah(num) {
    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

function updateCartBadge() {
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.getElementById('cartCountBadge');
    if (badge) badge.textContent = totalQty;
}

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id && i.size === product.size);
    if (existing) {
        existing.qty = Math.min(25, existing.qty + product.qty);
    } else {
        cart.push({ ...product, qty: Math.min(25, product.qty) });
    }
    updateCartBadge();
    renderCartItems();
    calculateTotal();
}

function quickAddToCart(id, name, priceMedium, priceLarge, imageSrc) {
    addToCart({
        id: String(id),
        name: name,
        size: 'medium',
        sizeLabel: 'Medium',
        price: Number(priceMedium),
        qty: 1,
        img: imageSrc
    });
    alert(name + ' (Medium) ditambahkan ke keranjang.');
}

function openCartModal() {
    renderCartItems();
    const paymentSelect = document.getElementById('modalPaymentMethod');
    if (paymentSelect) {
        paymentSelect.value = 'cod';
        handlePaymentMethodChange();
    }
    calculateTotal();
    const modal = document.getElementById('orderModal');
    if (modal) modal.style.display = 'flex';
}

function openCartModalWithItem(id, name, priceMedium, priceLarge, imageSrc) {
    addToCart({
        id: String(id),
        name: name,
        size: 'medium',
        sizeLabel: 'Medium',
        price: Number(priceMedium),
        qty: 1,
        img: imageSrc
    });
    openCartModal();
}

// Kompatibilitas pemanggilan modal lama
function openOrderModal(id, name, desc, priceMedium, priceLarge, imageSrc) {
    currentPriceMedium = Number(priceMedium);
    currentPriceLarge = Number(priceLarge);
    openCartModalWithItem(id, name, priceMedium, priceLarge, imageSrc);
}

function closeOrderModal() {
    const modal = document.getElementById('orderModal');
    if (modal) modal.style.display = 'none';
}

function addPickerItemToCart() {
    const selectEl = document.getElementById('pickerProduct');
    const sizeEl = document.getElementById('pickerSize');
    const qtyEl = document.getElementById('pickerQty');
    if (!selectEl || !sizeEl || !qtyEl) return;

    const opt = selectEl.options[selectEl.selectedIndex];
    const size = sizeEl.value;
    const price = size === 'large' ? Number(opt.dataset.large) : Number(opt.dataset.medium);
    const qty = Math.max(1, Math.min(25, Number(qtyEl.value || 1)));
    const defaultImg = window.LUMPIA_DEFAULT_IMG || '/images/lumpia-default.jpg';

    addToCart({
        id: String(opt.value),
        name: opt.dataset.name,
        size: size,
        sizeLabel: size === 'large' ? 'Large' : 'Medium',
        price: price,
        qty: qty,
        img: defaultImg
    });
}

function changeCartQty(index, delta) {
    if (!cart[index]) return;
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    } else if (cart[index].qty > 25) {
        cart[index].qty = 25;
    }
    updateCartBadge();
    renderCartItems();
    calculateTotal();
}

function removeCartItem(index) {
    cart.splice(index, 1);
    updateCartBadge();
    renderCartItems();
    calculateTotal();
}

function renderCartItems() {
    const container = document.getElementById('cartItemsContainer');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<div style="padding: 12px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 13px;">Keranjang masih kosong. Tambahkan varian Coklat, Keju, atau Mix di atas.</div>';
        return;
    }

    container.innerHTML = cart.map((item, idx) => `
        <div class="cart-item-row">
            <div>
                <strong>${item.name}</strong> <span style="font-size: 12px; color: #6b21a8;">(${item.sizeLabel})</span><br>
                <small style="color: #64748b;">${formatRupiah(item.price)} / item</small>
            </div>
            <div class="qty-controls">
                <button type="button" class="qty-btn" onclick="changeCartQty(${idx}, -1)">-</button>
                <span style="min-width: 22px; text-align: center; font-weight: 700;">${item.qty}</span>
                <button type="button" class="qty-btn" onclick="changeCartQty(${idx}, 1)">+</button>
                <button type="button" class="qty-btn" onclick="removeCartItem(${idx})" style="color: #dc2626;">x</button>
            </div>
        </div>
    `).join('');
}

function haversineKm(lat1, lon1, lat2, lon2) {
    const toRad = d => (d * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat / 2) ** 2 +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function detectCurrentLocation() {
    const statusText = document.getElementById('locationStatusText');
    const addressInput = document.getElementById('shippingAddressInput');
    if (!navigator.geolocation) {
        if (statusText) statusText.textContent = 'Browser tidak mendukung GPS lokasi saat ini. Silakan tulis alamat manual.';
        return;
    }

    if (statusText) statusText.textContent = 'Mendeteksi lokasi saat ini...';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const latInput = document.getElementById('latInput');
            const lngInput = document.getElementById('lngInput');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            const dist = haversineKm(UNRAM_LAT, UNRAM_LNG, lat, lng);
            calculatedDistanceKm = dist <= UNRAM_FREE_RADIUS_KM ? 0 : Number(dist.toFixed(1));

            if (addressInput && !addressInput.value.trim()) {
                addressInput.value = calculatedDistanceKm === 0
                    ? 'Area Kampus Universitas Mataram (Lokasi Saat Ini)'
                    : `Lokasi Saat Ini (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
            }

            if (statusText) {
                statusText.textContent = calculatedDistanceKm === 0
                    ? 'Terdeteksi di area Kampus Unram -> Ongkir Rp 0'
                    : `Terdeteksi ~${calculatedDistanceKm} km dari Kampus Unram -> Ongkir ${formatRupiah(Math.ceil(calculatedDistanceKm) * 1000)}`;
            }

            calculateTotal();
        },
        () => {
            if (statusText) statusText.textContent = 'Izin lokasi ditolak/tidak aktif. Anda tetap bisa mengisi alamat pengiriman secara manual.';
        }
    );
}

function calculateTotal() {
    const subtotalEl = document.getElementById('textSubtotal');
    const shippingEl = document.getElementById('textShipping');
    const grandTotalEl = document.getElementById('textGrandTotal');
    const distanceEl = document.getElementById('textDistance');
    const pointsEl = document.getElementById('textPointsEarned');

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const totalProducts = cart.reduce((sum, item) => sum + item.qty, 0);
    const shippingFee = calculatedDistanceKm <= 0 ? 0 : Math.ceil(calculatedDistanceKm) * 1000;
    const grandTotal = subtotal + shippingFee;

    if (subtotalEl) subtotalEl.innerText = formatRupiah(subtotal);
    if (distanceEl) distanceEl.innerText = calculatedDistanceKm.toFixed(1).replace('.', ',') + ' km';
    if (shippingEl) shippingEl.innerText = formatRupiah(shippingFee);
    if (pointsEl) pointsEl.innerText = `+${totalProducts} Poin`;
    if (grandTotalEl) grandTotalEl.innerText = formatRupiah(grandTotal);

    const shippingInput = document.getElementById('shippingCostInput');
    const distanceInput = document.getElementById('distanceKmInput');
    const pointsInput = document.getElementById('earnedPointsInput');
    if (shippingInput) shippingInput.value = shippingFee;
    if (distanceInput) distanceInput.value = calculatedDistanceKm;
    if (pointsInput) pointsInput.value = totalProducts;
}

function handlePaymentMethodChange() {
    const paymentSelect = document.getElementById('modalPaymentMethod');
    if (!paymentSelect) return;

    const method = paymentSelect.value;
    const qrisBox = document.getElementById('qrisPaymentBox');
    const proofBox = document.getElementById('paymentProofBox');
    const proofInput = document.getElementById('modalPaymentProof');

    if (method === 'qris') {
        if (qrisBox) qrisBox.style.display = 'block';
        if (proofBox) proofBox.style.display = 'block';
        if (proofInput) proofInput.required = true;
    } else {
        if (qrisBox) qrisBox.style.display = 'none';
        if (proofBox) proofBox.style.display = 'none';
        if (proofInput) {
            proofInput.required = false;
            proofInput.value = '';
        }
    }
}

function prepareCartSubmission() {
    if (cart.length === 0) {
        alert('Silakan tambahkan minimal 1 varian produk ke keranjang terlebih dahulu.');
        return false;
    }
    const cartInput = document.getElementById('cartItemsInput');
    if (cartInput) cartInput.value = JSON.stringify(cart);
    return true;
}

function sendCourierChat(orderId) {
    const input = document.getElementById('chatInput-' + orderId);
    const area = document.getElementById('chatMessages-' + orderId);
    if (!input || !area) return;

    const text = input.value.trim();
    if (!text) return;

    const safeText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    area.innerHTML += `<div class="chat-msg-item" style="background:#eef2ff;color:#1e293b;"><strong>Anda:</strong> ${safeText}</div>`;
    area.scrollTop = area.scrollHeight;
    input.value = '';
}

function submitRating(orderId) {
    const selectEl = document.getElementById('ratingSelect-' + orderId);
    const reviewEl = document.getElementById('reviewInput-' + orderId);
    const statusEl = document.getElementById('ratingStatus-' + orderId);
    if (!selectEl || !statusEl) return;

    const ratingVal = selectEl.value;
    const reviewText = reviewEl ? reviewEl.value.trim() : '';
    const safeReview = reviewText.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    statusEl.textContent = safeReview
        ? `Rating ${ratingVal}/5 — "${safeReview}" tercatat.`
        : `Rating ${ratingVal}/5 tercatat.`;
}

function openProfileModalIfNeeded(e) {
    const href = e.currentTarget.getAttribute('href');
    if (href === '#profil-section') {
        e.preventDefault();
        const modal = document.getElementById('profileModal');
        if (modal) modal.style.display = 'flex';
    }
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) modal.style.display = 'none';
}

/**
 * Toggle Posisi Navigasi (Sidebar Kiri <-> Navigasi Bawah)
 */
document.addEventListener('DOMContentLoaded', function () {
    const savedLayout = localStorage.getItem('customer_nav_layout');
    if (savedLayout === 'bottom') {
        document.body.classList.add('layout-bottom');
        updateToggleButtonText(true);
    } else {
        updateToggleButtonText(false);
    }
});

function toggleSidebarPosition() {
    const isBottom = document.body.classList.toggle('layout-bottom');
    localStorage.setItem('customer_nav_layout', isBottom ? 'bottom' : 'left');
    updateToggleButtonText(isBottom);
}

function updateToggleButtonText(isBottom) {
    const btn = document.getElementById('btnToggleLayout');
    if (btn) {
        btn.textContent = isBottom
            ? 'Pindah Menu ke Kiri'
            : 'Pindah Menu ke Bawah';
    }
}