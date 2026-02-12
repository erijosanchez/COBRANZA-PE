<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        $methods = [
            ['name' => 'Efectivo', 'code' => 'CASH'],
            ['name' => 'Yape', 'code' => 'YAPE'],
            ['name' => 'Plin', 'code' => 'PLIN'],
            ['name' => 'Transferencia Bancaria', 'code' => 'TRANSFER'],
            ['name' => 'Mercado Pago', 'code' => 'MP'],
            ['name' => 'Depósito Bancario', 'code' => 'DEPOSIT'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['company_id' => $company->id, 'code' => $method['code']],
                ['name' => $method['name'], 'is_active' => true]
            );
        }
    }
}