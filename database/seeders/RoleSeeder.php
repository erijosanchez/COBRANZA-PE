<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos
        $permissions = [
            // Deudores
            'debtors.index',
            'debtors.create',
            'debtors.edit',
            'debtors.delete',
            'debtors.show',
            // Deudas
            'debts.index',
            'debts.create',
            'debts.edit',
            'debts.delete',
            'debts.show',
            // Pagos
            'payments.index',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.show',
            // Gestiones de cobranza
            'collections.index',
            'collections.create',
            'collections.edit',
            'collections.delete',
            // Asignaciones
            'assignments.index',
            'assignments.create',
            'assignments.edit',
            'assignments.delete',
            // Reportes
            'reports.dashboard',
            'reports.debts',
            'reports.payments',
            'reports.collections',
            'reports.export',
            // Notificaciones
            'notifications.index',
            'notifications.send',
            'notifications.templates',
            // Configuración
            'settings.company',
            'settings.payment_methods',
            'settings.users',
            'settings.roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'debtors.index',
            'debtors.create',
            'debtors.edit',
            'debtors.show',
            'debts.index',
            'debts.create',
            'debts.edit',
            'debts.show',
            'payments.index',
            'payments.create',
            'payments.edit',
            'payments.show',
            'collections.index',
            'collections.create',
            'collections.edit',
            'assignments.index',
            'assignments.create',
            'assignments.edit',
            'reports.dashboard',
            'reports.debts',
            'reports.payments',
            'reports.collections',
            'reports.export',
            'notifications.index',
            'notifications.send',
            'notifications.templates',
        ]);

        $gestor = Role::firstOrCreate(['name' => 'gestor']);
        $gestor->givePermissionTo([
            'debtors.index',
            'debtors.show',
            'debts.index',
            'debts.show',
            'payments.index',
            'payments.create',
            'payments.show',
            'collections.index',
            'collections.create',
            'reports.dashboard',
            'notifications.index',
            'notifications.send',
        ]);
    }
}
