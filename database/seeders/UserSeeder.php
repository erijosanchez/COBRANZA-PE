<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@cobranza.pe'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'dni' => '12345678',
                'phone' => '999888777',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@cobranza.pe'],
            [
                'name' => 'María López',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'dni' => '87654321',
                'phone' => '999777666',
                'is_active' => true,
            ]
        );
        $supervisor->assignRole('supervisor');

        // Gestores
        $gestores = [
            ['name' => 'Carlos Ramírez', 'email' => 'carlos@cobranza.pe', 'dni' => '11223344', 'phone' => '999111222'],
            ['name' => 'Ana Torres', 'email' => 'ana@cobranza.pe', 'dni' => '55667788', 'phone' => '999333444'],
            ['name' => 'Pedro Sánchez', 'email' => 'pedro@cobranza.pe', 'dni' => '99001122', 'phone' => '999555666'],
        ];

        foreach ($gestores as $gestor) {
            $user = User::firstOrCreate(
                ['email' => $gestor['email']],
                [
                    'name' => $gestor['name'],
                    'password' => Hash::make('password'),
                    'company_id' => $company->id,
                    'dni' => $gestor['dni'],
                    'phone' => $gestor['phone'],
                    'is_active' => true,
                ]
            );
            $user->assignRole('gestor');
        }
    }
}
