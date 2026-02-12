<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Debtor;
use Illuminate\Database\Seeder;

class DebtorSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        $debtors = [
            [
                'document_type' => 'DNI',
                'document_number' => '45678901',
                'full_name' => 'Juan Pérez García',
                'email' => 'juan.perez@gmail.com',
                'phone' => '951234567',
                'address' => 'Jr. Lima 456, Cercado de Lima',
                'district' => 'Cercado de Lima',
                'province' => 'Lima',
                'department' => 'Lima',
            ],
            [
                'document_type' => 'DNI',
                'document_number' => '56789012',
                'full_name' => 'Rosa Martínez Díaz',
                'email' => 'rosa.martinez@gmail.com',
                'phone' => '962345678',
                'address' => 'Av. Arequipa 789, Miraflores',
                'district' => 'Miraflores',
                'province' => 'Lima',
                'department' => 'Lima',
            ],
            [
                'document_type' => 'DNI',
                'document_number' => '67890123',
                'full_name' => 'Luis Rodríguez Vega',
                'email' => 'luis.rodriguez@gmail.com',
                'phone' => '973456789',
                'address' => 'Calle Los Olivos 123, Los Olivos',
                'district' => 'Los Olivos',
                'province' => 'Lima',
                'department' => 'Lima',
            ],
            [
                'document_type' => 'RUC',
                'document_number' => '10234567890',
                'full_name' => 'Comercial El Sol E.I.R.L.',
                'email' => 'comercial.elsol@gmail.com',
                'phone' => '984567890',
                'address' => 'Av. Colonial 2345, Callao',
                'district' => 'Callao',
                'province' => 'Callao',
                'department' => 'Callao',
            ],
            [
                'document_type' => 'DNI',
                'document_number' => '78901234',
                'full_name' => 'Carmen Flores Huamán',
                'email' => 'carmen.flores@gmail.com',
                'phone' => '995678901',
                'address' => 'Jr. Huancavelica 567, Breña',
                'district' => 'Breña',
                'province' => 'Lima',
                'department' => 'Lima',
            ],
        ];

        foreach ($debtors as $debtor) {
            Debtor::firstOrCreate(
                ['company_id' => $company->id, 'document_number' => $debtor['document_number']],
                array_merge($debtor, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
