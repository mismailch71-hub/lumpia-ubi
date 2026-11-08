<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil customer yang ada, atau buat otomatis jika belum ada akun customer (tanpa bikin data sampah)
        $customer = User::firstOrCreate(
            ['email' => 'customer@lumpiaubi.com'],
            [
                'name' => 'Budi Pembeli',
                'password' => Hash::make('password123'),
            ]
        );

        // Pastikan role customer terpasang dari Spatie jika user baru terbuat
        if (!$customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }

        // 2. Ambil kurir internal secara dinamis
        $kurirYoga = User::where('email', 'yoga@lumpiaubi.com')->first() ?? User::role('kurir')->first();

        // 3. Insert Pesanan Simulasi Dinamis
        // Pesanan 1: Status Pending (Menunggu Assign Kurir dari Admin)
        DB::table('orders')->insert([
            'user_id'          => $customer->id,
            'courier_id'       => null,
            'status'           => 'pending',
            'total_price'      => 25000,
            'shipping_address' => 'Jl. Mawar No. 12, Mataram',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Pesanan 2: Status Dalam Pengiriman (Untuk menguji badge "Sedang Mengantar" pada kurir)
        if ($kurirYoga) {
            DB::table('orders')->insert([
                'user_id'          => $customer->id,
                'courier_id'       => $kurirYoga->id,
                'status'           => 'dalam_pengiriman',
                'total_price'      => 35000,
                'shipping_address' => 'Jl. Melati No. 45, Mataram',
                'created_at'       => now()->subMinutes(30),
                'updated_at'       => now(),
            ]);
        }
    }
}