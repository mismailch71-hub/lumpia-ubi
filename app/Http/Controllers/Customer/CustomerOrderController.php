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
        $user = Auth::user(); // Ambil data customer untuk auto-fill alamat

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
     * Proses Pembuatan Pesanan Baru dari Katalog
     */
    public function storeOrder(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'variant'          => 'required|in:medium,large',
            'quantity'         => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'payment_method'   => 'required|in:cod,qris',
            'payment_proof'    => 'required_if:payment_method,qris|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes'            => 'nullable|string',
        ], [
            'payment_proof.required_if' => 'Anda wajib mengunggah bukti pembayaran (screenshot) untuk metode QRIS.',
        ]);

        $product = DB::table('products')->where('id', $request->product_id)->first();

        // Tentukan harga satuan berdasarkan varian ukuran & tambahkan ongkir Rp 5.000
        $unitPrice   = ($request->variant === 'large') ? $product->price_large : $product->price_medium;
        $shippingFee = 5000;
        $totalPrice  = ($unitPrice * $request->quantity) + $shippingFee;

        // Validasi anti-duplikat & simpan file bukti pembayaran QRIS
        $proofPath = null;
        $proofHash = null;

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');

            // 1. Hitung sidik jari unik (MD5 hash) dari file gambar
            $proofHash = md5_file($file->getRealPath());

            // 2. Tolak jika screenshot yang sama persis pernah dipakai di pesanan lain
            $isDuplicate = DB::table('orders')->where('proof_hash', $proofHash)->exists();
            if ($isDuplicate) {
                return back()->withErrors([
                    'payment_proof' => 'Bukti pembayaran ini sudah pernah digunakan sebelumnya! Harap unggah screenshot pembayaran terbaru.'
                ])->withInput();
            }

            // 3. Simpan ke storage/app/public/payment_proofs
            $proofPath = $file->store('payment_proofs', 'public');
        }

        // Gabungkan catatan dengan informasi varian ukuran
        $notesWithVariant = '[Varian: ' . strtoupper($request->variant) . '] ' . ($request->notes ?? '');

        // Tentukan status awal: QRIS perlu verifikasi admin, COD langsung diproses
        $initialStatus = ($request->payment_method === 'qris') ? 'menunggu_verifikasi' : 'diproses';

        // 1. Simpan data utama order
        $orderId = DB::table('orders')->insertGetId([
            'user_id'          => Auth::id(),
            'total_price'      => $totalPrice,
            'status'           => $initialStatus,
            'payment_method'   => $request->payment_method,
            'payment_proof'    => $proofPath,
            'proof_hash'       => $proofHash,
            'shipping_address' => $request->shipping_address,
            'notes'            => trim($notesWithVariant),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 2. Simpan rincian item order
        DB::table('order_items')->insert([
            'order_id'   => $orderId,
            'product_id' => $product->id,
            'quantity'   => $request->quantity,
            'price'      => $unitPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $successMessage = ($request->payment_method === 'qris')
            ? 'Pesanan berhasil dibuat! Pembayaran QRIS Anda sedang diverifikasi oleh Admin.'
            : 'Pesanan COD berhasil dibuat! Admin akan segera memproses dan menugaskan kurir.';

        return redirect()->route('customer.orders.index')->with('success', $successMessage);
    }
}