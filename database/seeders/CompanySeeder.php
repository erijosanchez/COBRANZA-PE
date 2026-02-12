<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['ruc' => '20123456789'],
            [
                'business_name' => 'Financiera Demo S.A.C.',
                'trade_name' => 'FinDemo',
                'address' => 'Av. Javier Prado 1234, San Isidro, Lima',
                'phone' => '01-234-5678',
                'email' => 'admin@findemo.pe',
                'is_active' => true,
            ]
        );
    }
}
