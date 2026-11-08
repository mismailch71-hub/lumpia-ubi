<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Lumpia Ubi Coklat Lumer',
            'slug' => 'lumpia-ubi-coklat-lumer',
            'description' => 'Isian ubi manis lezat dengan lelehan coklat premium yang lumer di mulut.',
            'price_medium' => 10000,
            'price_large' => 15000,
            'is_available' => true,
        ]);

        Product::create([
            'name' => 'Lumpia Ubi Keju Lumer',
            'slug' => 'lumpia-ubi-keju-lumer',
            'description' => 'Isian ubi lembut dipadukan dengan keju gurih meledak di setiap gigitan.',
            'price_medium' => 10000,
            'price_large' => 15000,
            'is_available' => true,
        ]);

        Product::create([
            'name' => 'Lumpia Ubi Mix Coklat Keju',
            'slug' => 'lumpia-ubi-mix-coklat-keju',
            'description' => 'Kombinasi sempurna antara manisnya coklat dan gurihnya keju.',
            'price_medium' => 10000,
            'price_large' => 15000,
            'is_available' => true,
        ]);
    }
}