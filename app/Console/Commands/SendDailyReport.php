<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\CollectionAction;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyReport extends Command
{
    protected $signature = 'reports:daily';
    protected $description = 'Genera y muestra el reporte diario de cobranza';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("=== REPORTE DIARIO DE COBRANZA - {$today->format('d/m/Y')} ===");
        $this->newLine();

        $companies = Company::where('is_active', true)->get();

        foreach ($companies as $company) {
            $this->info("--- {$company->business_name} ---");

            // Pagos del día
            $paymentsToday = Payment::byCompany($company->id)
                ->confirmed()
                ->byDateRange($today, $today)
                ->sum('amount');

            $paymentsCount = Payment::byCompany($company->id)
                ->confirmed()
                ->byDateRange($today, $today)
                ->count();

            // Gestiones del día
            $actionsToday = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $company->id))
                ->whereDate('action_date', $today)
                ->count();

            // Total vencido
            $totalOverdue = Debt::byCompany($company->id)->overdue()->sum('pending_amount');
            $countOverdue = Debt::byCompany($company->id)->overdue()->count();

            // Cuotas que vencen mañana
            $dueTomorrow = Installment::whereHas('debt', fn($q) => $q->where('company_id', $company->id))
                ->where('status', 'pending')
                ->whereDate('due_date', $today->copy()->addDay())
                ->count();

            // Promesas para hoy
            $promisesToday = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $company->id))
                ->where('result', 'promise_to_pay')
                ->whereDate('promise_date', $today)
                ->count();

            $this->table(
                ['Indicador', 'Valor'],
                [
                    ['Recaudación hoy', 'S/ ' . number_format($paymentsToday, 2) . " ({$paymentsCount} pagos)"],
                    ['Gestiones hoy', $actionsToday],
                    ['Total vencido', 'S/ ' . number_format($totalOverdue, 2) . " ({$countOverdue} deudas)"],
                    ['Cuotas vencen mañana', $dueTomorrow],
                    ['Promesas para hoy', $promisesToday],
                ]
            );

            $this->newLine();

            // TODO: Enviar este reporte por email a admins/supervisores
            Log::info("Reporte diario generado para {$company->business_name}", [
                'payments_today' => $paymentsToday,
                'actions_today' => $actionsToday,
                'total_overdue' => $totalOverdue,
            ]);
        }

        $this->info('Reporte diario completado.');

        return Command::SUCCESS;
    }
}
