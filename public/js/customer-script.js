/**
 * Script Modul Customer - Lumpia Ubi Store
 */

let currentPriceMedium = 0;
let currentPriceLarge = 0;
const SHIPPING_FEE = 5000; // Biaya Ongkir Flat Rp 5.000

function openOrderModal(id, name, desc, priceMedium, priceLarge, imageSrc) {
    currentPriceMedium = Number(priceMedium);
    currentPriceLarge = Number(priceLarge);

    // Set Data Produk di Modal
    document.getElementById('modalProductId').value = id;
    document.getElementById('modalProductName').innerText = name;
    document.getElementById('modalProductDesc').innerText = desc || 'Lumpia Ubi renyah dengan isian manis/gurih.';
    document.getElementById('modalProductImg').src = imageSrc;

    // Set Opsi Varian
    const variantSelect = document.getElementById('modalVariant');
    variantSelect.innerHTML = `
        <option value="medium">Medium (Rp ${currentPriceMedium.toLocaleString('id-ID')})</option>
        <option value="large">Large (Rp ${currentPriceLarge.toLocaleString('id-ID')})</option>
    `;

    // Reset Kuantitas ke 1
    document.getElementById('modalQuantity').value = 1;

    // Reset Metode Pembayaran ke COD & sembunyikan kotak QRIS
    const paymentSelect = document.getElementById('modalPaymentMethod');
    if (paymentSelect) {
        paymentSelect.value = 'cod';
        handlePaymentMethodChange();
    }

    // Hitung Ulang Ringkasan Harga
    calculateTotal();

    // Tampilkan Modal
    document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

function calculateTotal() {
    const variant = document.getElementById('modalVariant').value;
    const qty = parseInt(document.getElementById('modalQuantity').value) || 1;

    const unitPrice = (variant === 'large') ? currentPriceLarge : currentPriceMedium;
    const subtotal = unitPrice * qty;
    const grandTotal = subtotal + SHIPPING_FEE;

    // Update Text di UI Modal
    document.getElementById('textSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('textShipping').innerText = 'Rp ' + SHIPPING_FEE.toLocaleString('id-ID');
    document.getElementById('textGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
}

/**
 * Menampilkan Kotak Scan QRIS & Upload Bukti sesuai Pilihan Metode Pembayaran
 */
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
        if (proofInput) proofInput.required = true; // Wajib diisi saat pilih QRIS
    } else {
        if (qrisBox) qrisBox.style.display = 'none';
        if (proofBox) proofBox.style.display = 'none';
        if (proofInput) {
            proofInput.required = false; // Tidak wajib saat pilih COD
            proofInput.value = '';       // Reset file jika batal pilih QRIS
        }
    }
}

/**
 * Toggle Posisi Navigasi (Sidebar Kiri <-> Navigasi Bawah)
 */
document.addEventListener('DOMContentLoaded', function () {
    const savedLayout = localStorage.getItem('customer_nav_layout');
    if (savedLayout === 'bottom') {
        document.body.classList.add('layout-bottom');
        updateToggleButtonText(true);
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
        btn.innerHTML = isBottom 
            ? '⬅️ Pindah Menu ke Kiri' 
            : '⬇️ Pindah Menu ke Bawah';
    }
}