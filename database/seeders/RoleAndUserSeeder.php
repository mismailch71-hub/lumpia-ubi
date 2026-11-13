<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Inisialisasi Role (Admin, Kurir, Customer)
        $roleAdmin    = Role::firstOrCreate(['name' => 'admin']);
        $roleKurir    = Role::firstOrCreate(['name' => 'kurir']);
        $roleCustomer = Role::firstOrCreate(['name' => 'customer']);

        // 2. Akun ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@lumpiaubi.com'],
            [
                'name'     => 'Admin Lumpia Ubi',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole($roleAdmin);

        // 3. Akun 3 KURIR dengan Password Nama + Angka (123)
        $kurirs = [
            [
                'name'     => 'Kurir Yoga (Kurir 1)',
                'email'    => 'yoga@lumpiaubi.com',
                'password' => Hash::make('yoga123'),
            ],
            [
                'name'     => 'Kurir Rafi (Kurir 2)',
                'email'    => 'rafi@lumpiaubi.com',
                'password' => Hash::make('rafi123'),
            ],
            [
                'name'     => 'Kurir Mail (Kurir 3)',
                'email'    => 'mail@lumpiaubi.com',
                'password' => Hash::make('mail123'),
            ],
        ];

        foreach ($kurirs as $data) {
            $kurirUser = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => $data['password'],
                ]
            );
            $kurirUser->assignRole($roleKurir);
        }

        // 4. Akun CUSTOMER SAMPLE
        $customer = User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name'     => 'Budi Pembeli',
                'password' => Hash::make('password123'),
            ]
        );
        $customer->assignRole($roleCustomer);
    }
}