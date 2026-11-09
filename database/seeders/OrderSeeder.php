<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@lumpiaubi.com'],
            ['name' => 'Budi Pembeli', 'password' => Hash::make('password123')]
        );

        if (!$customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }

        $kurirYoga = User::where('email', 'yoga@lumpiaubi.com')->first() ?? User::role('kurir')->first();
        $produk    = Product::first();

        // 1. Pesanan 1 (Pending)
        $orderId1 = DB::table('orders')->insertGetId([
            'user_id'          => $customer->id,
            'courier_id'       => null,
            'status'           => 'pending',
            'total_price'      => 25000,
            'shipping_address' => 'Jl. Mawar No. 12, Mataram',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        if ($produk) {
            DB::table('order_items')->insert([
                'order_id'   => $orderId1,
                'product_id' => $produk->id,
                'quantity'   => 2,
                'price'      => 12500,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Pesanan 2 (Dalam Pengiriman)
        if ($kurirYoga) {
            $orderId2 = DB::table('orders')->insertGetId([
                'user_id'          => $customer->id,
                'courier_id'       => $kurirYoga->id,
                'status'           => 'dalam_pengiriman',
                'total_price'      => 35000,
                'shipping_address' => 'Jl. Melati No. 45, Mataram',
                'created_at'       => now()->subMinutes(30),
                'updated_at'       => now(),
            ]);

            if ($produk) {
                DB::table('order_items')->insert([
                    'order_id'   => $orderId2,
                    'product_id' => $produk->id,
                    'quantity'   => 3,
                    'price'      => 11666,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}