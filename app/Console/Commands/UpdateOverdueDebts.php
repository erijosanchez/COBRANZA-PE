<?php

namespace App\Console\Commands;

use App\Models\Debt;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateOverdueDebts extends Command
{
    protected $signature = 'debts:update-overdue';
    protected $description = 'Actualiza el estado de morosidad de deudas y cuotas vencidas';

    public function handle()
    {
        $this->info('Iniciando actualización de morosidad...');
        $today = Carbon::today();

        // 1. Actualizar cuotas vencidas
        $overdueInstallments = Installment::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', $today)
            ->update(['status' => 'overdue']);

        $this->info("Cuotas marcadas como vencidas: {$overdueInstallments}");

        // 2. Actualizar deudas
        $debts = Debt::whereIn('status', ['active', 'partial'])
            ->where('due_date', '<', $today)
            ->get();

        $updated = 0;
        foreach ($debts as $debt) {
            $days = $today->diffInDays($debt->due_date);
            $debt->update([
                'status' => 'overdue',
                'days_overdue' => $days,
            ]);
            $updated++;
        }

        // 3. Actualizar días de mora en deudas ya vencidas
        $existingOverdue = Debt::where('status', 'overdue')->get();
        foreach ($existingOverdue as $debt) {
            $days = $today->diffInDays($debt->due_date);
            $debt->update(['days_overdue' => $days]);
        }

        $this->info("Deudas actualizadas a vencidas: {$updated}");
        $this->info("Deudas vencidas con días actualizados: {$existingOverdue->count()}");

        // 4. Calcular intereses moratorios
        $this->calculatePenalties($today);

        $this->info('Proceso completado exitosamente.');

        return Command::SUCCESS;
    }

    private function calculatePenalties(Carbon $today): void
    {
        $debtsWithInterest = Debt::where('status', 'overdue')
            ->where('interest_type', '!=', 'none')
            ->where('pending_amount', '>', 0)
            ->get();

        $penaltiesApplied = 0;

        foreach ($debtsWithInterest as $debt) {
            $overdueInstallments = $debt->installments()
                ->where('status', 'overdue')
                ->get();

            foreach ($overdueInstallments as $installment) {
                $daysLate = $today->diffInDays($installment->due_date);
                if ($daysLate <= 0) continue;

                $penaltyAmount = match ($debt->interest_type) {
                    'daily' => $installment->amount * ($debt->interest_rate / 100) * $daysLate,
                    'monthly' => $installment->amount * ($debt->interest_rate / 100) * ($daysLate / 30),
                    'fixed' => 0, // Ya se calculó al crear la deuda
                    default => 0,
                };

                if ($penaltyAmount > 0) {
                    $penaltyAmount = round($penaltyAmount, 2);
                    $installment->update([
                        'penalty_amount' => $penaltyAmount,
                        'total_amount' => $installment->amount + $installment->interest_amount + $penaltyAmount,
                    ]);
                    $penaltiesApplied++;
                }
            }

            // Recalcular total de la deuda
            $newTotal = $debt->installments()->sum('total_amount');
            $debt->update([
                'total_amount' => $newTotal,
                'pending_amount' => $newTotal - $debt->paid_amount,
            ]);
        }

        $this->info("Intereses moratorios calculados en {$penaltiesApplied} cuotas");
    }
}
