<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::query()->firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Admin KeranjangKu', 'password' => 'password'],
        );

        foreach ([
            ['name' => 'Buku Catatan', 'sku' => 'BK-001', 'quantity' => 28, 'price' => 18000, 'description' => 'Buku catatan ukuran A5 untuk mencatat ide harian.'],
            ['name' => 'Pulpen Gel', 'sku' => 'ATK-014', 'quantity' => 42, 'price' => 7500, 'description' => 'Pulpen tinta hitam 0,5 mm.'],
            ['name' => 'Map Dokumen', 'sku' => 'ATK-021', 'quantity' => 0, 'price' => 5000, 'description' => 'Map plastik untuk dokumen ukuran A4.'],
            ['name' => 'Mouse Wireless', 'sku' => 'EL-008', 'quantity' => 4, 'price' => 89500, 'description' => 'Mouse nirkabel dengan koneksi USB.'],
            ['name' => 'Botol Minum', 'sku' => 'RT-005', 'quantity' => 16, 'price' => 45000, 'description' => 'Botol minum stainless steel 600 ml.'],
        ] as $product) {
            Product::query()->firstOrCreate(
                ['user_id' => $demoUser->id, 'sku' => $product['sku']],
                $product,
            );
        }
    }
}
