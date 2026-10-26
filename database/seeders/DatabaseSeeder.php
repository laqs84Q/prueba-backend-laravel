<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tasas de ejemplo para esta prueba. No son cotizaciones en tiempo real.
        $usd = Currency::updateOrCreate(['name' => 'USD'], [
            'symbol' => '$', 'exchange_rate' => 1.000000,
        ]);
        $crc = Currency::updateOrCreate(['name' => 'CRC'], [
            'symbol' => '₡', 'exchange_rate' => 500.000000,
        ]);
        $eur = Currency::updateOrCreate(['name' => 'EUR'], [
            'symbol' => '€', 'exchange_rate' => 0.900000,
        ]);

        $product = Product::firstOrCreate(['name' => 'Teclado mecánico'], [
            'description' => 'Teclado mecánico con conexión USB',
            'price' => 75.50,
            'currency_id' => $usd->id,
            'tax_cost' => 9.82,
            'manufacturing_cost' => 30.00,
        ]);

        ProductPrice::updateOrCreate(
            ['product_id' => $product->id, 'currency_id' => $crc->id],
            ['price' => 37750.00]
        );
        ProductPrice::updateOrCreate(
            ['product_id' => $product->id, 'currency_id' => $eur->id],
            ['price' => 67.95]
        );
    }
}
