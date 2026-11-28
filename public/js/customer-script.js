/**
 * Script Modul Customer - Lumpia Ubi Store
 */

let currentPriceMedium = 0;
let currentPriceLarge = 0;
let cart = [];
let calculatedDistanceKm = 0;
let addressGeocodeTimer = null;

// Koordinat Kampus Pusat Universitas Mataram (Jl. Majapahit No. 62, Gomong, Selaparang)
const UNRAM_LAT = -8.58819;
const UNRAM_LNG = 116.09288;
const UNRAM_FREE_RADIUS_KM = 0.8; // Area Unram = Rp 0, di luar itu Rp 1.000 / km

// Perkiraan jarak fallback (km) dari Kampus Unram untuk area sekitar Mataram & Lombok Barat
const AREA_DISTANCE_HINTS = [
    { keyword: 'midang', km: 7.5 },
    { keyword: 'gunungsari', km: 8.5 },
    { keyword: 'gomong', km: 0.6 },
    { keyword: 'dasan agung', km: 1.5 },
    { keyword: 'sek arbela', km: 1.8 },
    { keyword: 'sekarbela', km: 1.8 },
    { keyword: 'ampenan', km: 4.5 },
    { keyword: 'cakranegara', km: 4.0 },
    { keyword: 'mataram', km: 2.5 },
    { keyword: 'senggigi', km: 14.0 },
    { keyword: 'narmada', km: 10.5 },
    { keyword: 'gerung', km: 22.0 },
];

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

function guessDistanceFromText(addressText) {
    const lower = (addressText || '').toLowerCase();
    if (!lower.trim()) return null;

    if (lower.includes('unram') || lower.includes('universitas mataram')) {
        return 0;
    }

    for (const item of AREA_DISTANCE_HINTS) {
        if (lower.includes(item.keyword)) {
            return item.km;
        }
    }

    return null;
}

async function reverseGeocodeCoords(lat, lng) {
    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&accept-language=id`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });
        if (!response.ok) return null;

        const data = await response.json();
        return data && data.display_name ? data.display_name : null;
    } catch (err) {
        return null;
    }
}

async function calculateDistanceFromAddressText(addressText) {
    const query = (addressText || '').trim();
    const statusText = document.getElementById('locationStatusText');
    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');

    if (!query) {
        calculatedDistanceKm = 0;
        if (latInput) latInput.value = '';
        if (lngInput) lngInput.value = '';
        if (statusText) {
            statusText.textContent = 'Masukkan alamat tujuan atau gunakan lokasi saat ini untuk menghitung jarak dari Unram.';
        }
        calculateTotal();
        return;
    }

    if (statusText) {
        statusText.textContent = 'Menghitung jarak dari Kampus Unram berdasarkan alamat...';
    }

    try {
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=id&q=${encodeURIComponent(query)}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const results = await response.json();
            if (Array.isArray(results) && results.length > 0) {
                const targetLat = parseFloat(results[0].lat);
                const targetLng = parseFloat(results[0].lon);

                if (latInput) latInput.value = targetLat;
                if (lngInput) lngInput.value = targetLng;

                const rawDist = haversineKm(UNRAM_LAT, UNRAM_LNG, targetLat, targetLng);
                const roadFactorDist = rawDist <= UNRAM_FREE_RADIUS_KM ? 0 : Number((rawDist * 1.25).toFixed(1));
                calculatedDistanceKm = roadFactorDist;

                const fee = calculatedDistanceKm <= 0 ? 0 : Math.ceil(calculatedDistanceKm) * 1000;
                if (statusText) {
                    statusText.textContent = calculatedDistanceKm === 0
                        ? 'Terdeteksi di area Kampus Unram -> Ongkir Rp 0.'
                        : `Jarak ~${calculatedDistanceKm.toFixed(1).replace('.', ',')} km dari Kampus Unram -> Ongkir ${formatRupiah(fee)}.`;
                }

                calculateTotal();
                return;
            }
        }
    } catch (err) {
        // Lanjut ke fallback teks wilayah di bawah
    }

    const fallbackKm = guessDistanceFromText(query);
    if (fallbackKm !== null) {
        calculatedDistanceKm = fallbackKm <= UNRAM_FREE_RADIUS_KM ? 0 : fallbackKm;
        const fee = calculatedDistanceKm <= 0 ? 0 : Math.ceil(calculatedDistanceKm) * 1000;
        if (statusText) {
            statusText.textContent = calculatedDistanceKm === 0
                ? 'Terdeteksi di area Kampus Unram -> Ongkir Rp 0.'
                : `Perkiraan jarak ~${calculatedDistanceKm.toFixed(1).replace('.', ',')} km dari Kampus Unram -> Ongkir ${formatRupiah(fee)}.`;
        }
        calculateTotal();
    }
}

function handleAddressInputChange() {
    const addressInput = document.getElementById('shippingAddressInput');
    if (!addressInput) return;

    clearTimeout(addressGeocodeTimer);
    addressGeocodeTimer = setTimeout(() => {
        calculateDistanceFromAddressText(addressInput.value);
    }, 500);
}

function detectCurrentLocation() {
    const statusText = document.getElementById('locationStatusText');
    const addressInput = document.getElementById('shippingAddressInput');

    if (!navigator.geolocation) {
        if (statusText) {
            statusText.textContent = 'Browser tidak mendukung GPS lokasi saat ini. Silakan tulis alamat manual.';
        }
        return;
    }

    if (statusText) {
        statusText.textContent = 'Mendeteksi lokasi & mengambil nama alamat dari peta...';
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const latInput = document.getElementById('latInput');
            const lngInput = document.getElementById('lngInput');

            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            const dist = haversineKm(UNRAM_LAT, UNRAM_LNG, lat, lng);
            calculatedDistanceKm = dist <= UNRAM_FREE_RADIUS_KM ? 0 : Number((dist * 1.25).toFixed(1));

            const readableAddress = await reverseGeocodeCoords(lat, lng);

            if (addressInput) {
                if (calculatedDistanceKm === 0) {
                    addressInput.value = readableAddress
                        ? `${readableAddress} (Area Kampus Unram)`
                        : 'Area Kampus Universitas Mataram (Lokasi Saat Ini)';
                } else {
                    addressInput.value = readableAddress || `Lokasi Saat Ini (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
                }
            }

            if (statusText) {
                statusText.textContent = calculatedDistanceKm === 0
                    ? 'Terdeteksi di area Kampus Unram -> Ongkir Rp 0 & alamat terisi otomatis.'
                    : `Terdeteksi ~${calculatedDistanceKm.toFixed(1).replace('.', ',')} km dari Kampus Unram -> Ongkir ${formatRupiah(Math.ceil(calculatedDistanceKm) * 1000)} & alamat terisi otomatis.`;
            }

            calculateTotal();
        },
        () => {
            if (statusText) {
                statusText.textContent = 'Izin lokasi ditolak/tidak aktif. Anda tetap bisa mengisi alamat pengiriman secara manual.';
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
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

function getCsrfTokenCustomer() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
}

function appendCustomerBubble(container, senderName, messageText, timeText, isOutgoing, isSynced = true) {
    if (!container) return;

    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + (isSynced ? 'chat-bubble-synced ' : '') + (isOutgoing ? 'chat-bubble-outgoing' : 'chat-bubble-incoming');

    const label = document.createElement('div');
    label.className = 'chat-bubble-label';
    label.textContent = senderName;

    const body = document.createElement('div');
    body.textContent = messageText;

    const footer = document.createElement('div');
    footer.className = 'chat-bubble-footer';

    const timeSpan = document.createElement('span');
    timeSpan.textContent = timeText;

    footer.appendChild(timeSpan);
    bubble.appendChild(label);
    bubble.appendChild(body);
    bubble.appendChild(footer);

    container.appendChild(bubble);
    container.scrollTop = container.scrollHeight;
}

async function loadCustomerOrderChat(orderId) {
    const area = document.getElementById('chatMessages-' + orderId);
    if (!area || !orderId) return;

    try {
        const res = await fetch(`/orders/${encodeURIComponent(orderId)}/chat`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!res.ok) return;

        const data = await res.json();
        if (!Array.isArray(data.messages)) return;

        area.querySelectorAll('.chat-bubble-synced').forEach(el => el.remove());

        data.messages.forEach(function (msg) {
            const isOutgoing = msg.sender_role === 'customer';
            appendCustomerBubble(
                area,
                msg.sender_name || (isOutgoing ? 'Anda (Customer)' : 'Kurir'),
                msg.message || '',
                msg.time || '',
                isOutgoing,
                true
            );
        });
    } catch (e) {
        // Abaikan error jaringan sementara
    }
}

async function sendCourierChat(orderId) {
    const input = document.getElementById('chatInput-' + orderId);
    const area = document.getElementById('chatMessages-' + orderId);
    if (!input || !area) return;

    const text = input.value.trim();
    if (!text) return;

    const now = new Date();
    const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

    try {
        const res = await fetch(`/orders/${encodeURIComponent(orderId)}/chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfTokenCustomer()
            },
            body: JSON.stringify({ message: text })
        });
        if (res.ok) {
            input.value = '';
            await loadCustomerOrderChat(orderId);
            return;
        }
    } catch (e) {
        // Fallback jika gagal request
    }

    appendCustomerBubble(area, 'Anda (Customer)', text, timeStr, true, false);
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

document.addEventListener('DOMContentLoaded', function () {
    const bodyEl = document.body;
    if (bodyEl && bodyEl.dataset.defaultImg) {
        window.LUMPIA_DEFAULT_IMG = bodyEl.dataset.defaultImg;
    }

    const savedLayout = localStorage.getItem('customer_nav_layout');
    if (savedLayout === 'bottom') {
        document.body.classList.add('layout-bottom');
        updateToggleButtonText(true);
    } else {
        updateToggleButtonText(false);
    }

    const addressInput = document.getElementById('shippingAddressInput');
    if (addressInput) {
        addressInput.addEventListener('input', handleAddressInputChange);
        addressInput.addEventListener('change', function () {
            calculateDistanceFromAddressText(addressInput.value);
        });
        if (addressInput.value.trim()) {
            calculateDistanceFromAddressText(addressInput.value);
        }
    }

    document.querySelectorAll('.customer-chat-thread[data-order-id]').forEach(function (thread) {
        const orderId = thread.getAttribute('data-order-id');
        if (!orderId) return;
        loadCustomerOrderChat(orderId);
        setInterval(function () {
            loadCustomerOrderChat(orderId);
        }, 4000);
    });
});

window.toggleCustomerEditForm = function () {
    const viewCard = document.getElementById('profileViewCard');
    const editCard = document.getElementById('profileEditCard');
    const btnToggle = document.getElementById('btnToggleEdit');
    if (!viewCard || !editCard) return;

    const isHidden = editCard.style.display === 'none' || window.getComputedStyle(editCard).display === 'none';
    editCard.style.display = isHidden ? 'block' : 'none';
    viewCard.style.display = isHidden ? 'none' : 'block';
    if (btnToggle) {
        btnToggle.style.display = isHidden ? 'none' : 'inline-flex';
    }
};

document.addEventListener('DOMContentLoaded', function () {
    if (document.body && document.body.dataset.hasErrors === '1') {
        window.toggleCustomerEditForm();
    }
});