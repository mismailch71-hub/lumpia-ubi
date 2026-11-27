<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    /**
     * Tampilkan Katalog Produk untuk Customer
     */
    public function catalog()
    {
        $products = DB::table('products')->get();
        $user = Auth::user();

        return view('customer.catalog', compact('products', 'user'));
    }

    /**
     * Tampilkan Riwayat & Status Pesanan Saya
     */
    public function index()
    {
        $userId = Auth::id();

        $orders = DB::table('orders')
            ->leftJoin('users as couriers', 'orders.courier_id', '=', 'couriers.id')
            ->select(
                'orders.*',
                'couriers.name as courier_name',
                'couriers.no_hp as courier_phone'
            )
            ->where('orders.user_id', $userId)
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $orderIds = $orders->pluck('id')->toArray();
        $orderItems = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('order_items.order_id', 'order_items.quantity', 'order_items.price', 'products.name as product_name')
            ->whereIn('order_items.order_id', $orderIds)
            ->get()
            ->groupBy('order_id');

        foreach ($orders as $order) {
            $order->items = $orderItems->get($order->id, collect());
        }

        return view('customer.orders', compact('orders'));
    }

    /**
     * Proses Pembuatan Pesanan Baru dari Keranjang Katalog
     */
    public function storeOrder(Request $request)
    {
        $request->validate([
            'cart_items'       => 'required|string',
            'whatsapp_phone'   => 'required|string|max:30',
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod,qris',
            'payment_proof'    => 'required_if:payment_method,qris|nullable|file|mimes:jpeg,png,jpg,webp,jfif|max:4096',
            'notes'            => 'nullable|string|max:255',
            'shipping_cost'    => 'nullable|numeric|min:0',
            'earned_points'    => 'nullable|integer|min:0',
        ], [
            'cart_items.required'         => 'Keranjang masih kosong. Silakan tambahkan produk terlebih dahulu.',
            'whatsapp_phone.required'     => 'Nomor HP WhatsApp wajib diisi untuk verifikasi pembayaran.',
            'payment_proof.required_if' => 'Anda wajib mengunggah bukti pembayaran (screenshot) untuk metode QRIS.',
        ]);

        $items = json_decode($request->input('cart_items'), true);
        if (!is_array($items) || count($items) === 0) {
            return back()->withErrors([
                'cart_items' => 'Keranjang masih kosong. Silakan tambahkan minimal 1 varian produk.'
            ])->withInput();
        }

        $subtotal = 0;
        $totalQty = 0;
        $variantSummary = [];

        foreach ($items as $item) {
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (int) ($item['price'] ?? 0));
            $subtotal += $price * $qty;
            $totalQty += $qty;
            $sizeLabel = strtoupper($item['sizeLabel'] ?? ($item['size'] ?? 'MEDIUM'));
            $variantSummary[] = ($item['name'] ?? 'Produk') . " ({$sizeLabel} x{$qty})";
        }

        $shippingFee = max(0, (int) $request->input('shipping_cost', 0));
        $totalPrice  = $subtotal + $shippingFee;

        // Validasi anti-duplikat & simpan file bukti pembayaran QRIS
        $proofPath = null;
        $proofHash = null;

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $proofHash = md5_file($file->getRealPath());

            $isDuplicate = DB::table('orders')->where('proof_hash', $proofHash)->exists();
            if ($isDuplicate) {
                return back()->withErrors([
                    'payment_proof' => 'Bukti pembayaran ini sudah pernah digunakan sebelumnya! Harap unggah screenshot pembayaran terbaru.'
                ])->withInput();
            }

            $proofPath = $file->store('payment_proofs', 'public');
        }

        $notesCombined = '[' . implode(', ', $variantSummary) . '] ' . ($request->notes ?? '');
        $initialStatus = ($request->payment_method === 'qris') ? 'menunggu_verifikasi' : 'diproses';

        DB::beginTransaction();
        try {
            $orderId = DB::table('orders')->insertGetId([
                'user_id'          => Auth::id(),
                'total_price'      => $totalPrice,
                'status'           => $initialStatus,
                'payment_method'   => $request->payment_method,
                'payment_proof'    => $proofPath,
                'proof_hash'       => $proofHash,
                'shipping_address' => $request->shipping_address,
                'notes'            => trim($notesCombined),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($items as $item) {
                $productId = (int) ($item['id'] ?? 0);
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $unitPrice = max(0, (int) ($item['price'] ?? 0));

                $product = DB::table('products')->where('id', $productId)->lockForUpdate()->first();
                if ($product) {
                    DB::table('order_items')->insert([
                        'order_id'   => $orderId,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $unitPrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $currentStock = (int) ($product->stock ?? 25);
                    $newStock = max(0, $currentStock - $qty);
                    DB::table('products')->where('id', $product->id)->update([
                        'stock'      => $newStock,
                        'updated_at' => now(),
                    ]);
                }
            }

            $earnedPoints = max(0, (int) $request->input('earned_points', $totalQty));
            $user = Auth::user();
            if ($user && isset($user->points)) {
                DB::table('users')->where('id', $user->id)->update([
                    'points'     => (int) ($user->points ?? 0) + $earnedPoints,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors([
                'order' => 'Terjadi kesalahan saat menyimpan pesanan: ' . $e->getMessage()
            ])->withInput();
        }

        $successMessage = ($request->payment_method === 'qris')
            ? 'Pesanan berhasil dibuat! Stok diperbarui dan pembayaran QRIS sedang diverifikasi oleh Admin.'
            : 'Pesanan COD berhasil dibuat! Stok diperbarui dan pesanan sedang diproses.';

        return redirect()->route('customer.orders.index')->with('success', $successMessage);
    }
}