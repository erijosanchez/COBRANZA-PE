<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Installment;
use Carbon\Carbon;

class DebtService
{
    public function createDebt(array $data, int $companyId): Debt
    {
        $originalAmount = $data['original_amount'];
        $totalAmount = $originalAmount;

        if ($data['interest_type'] === 'fixed') {
            $totalAmount = $originalAmount * (1 + ($data['interest_rate'] / 100));
        }

        $issueDate = Carbon::parse($data['issue_date']);

        $debt = Debt::create([
            'company_id' => $companyId,
            'debtor_id' => $data['debtor_id'],
            'code' => Debt::generateCode($companyId),
            'concept' => $data['concept'],
            'description' => $data['description'] ?? null,
            'original_amount' => $originalAmount,
            'total_amount' => round($totalAmount, 2),
            'paid_amount' => 0,
            'pending_amount' => round($totalAmount, 2),
            'currency' => $data['currency'],
            'installments_count' => $data['installments_count'],
            'issue_date' => $issueDate,
            'due_date' => $issueDate->copy()->addMonths($data['installments_count']),
            'interest_type' => $data['interest_type'],
            'interest_rate' => $data['interest_type'] === 'none' ? 0 : $data['interest_rate'],
            'status' => 'active',
            'assigned_to' => $data['assigned_to'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->generateInstallments($debt, $totalAmount, $data['installments_count'], $issueDate);

        return $debt;
    }

    private function generateInstallments(Debt $debt, float $totalAmount, int $count, Carbon $issueDate): void
    {
        $installmentAmount = round($totalAmount / $count, 2);
        $remainder = round($totalAmount - ($installmentAmount * $count), 2);

        for ($i = 1; $i <= $count; $i++) {
            $amount = $installmentAmount;
            if ($i === $count) {
                $amount += $remainder;
            }

            Installment::create([
                'debt_id' => $debt->id,
                'number' => $i,
                'amount' => $amount,
                'interest_amount' => 0,
                'penalty_amount' => 0,
                'total_amount' => $amount,
                'paid_amount' => 0,
                'due_date' => $issueDate->copy()->addMonths($i),
                'status' => 'pending',
            ]);
        }
    }

    public function refinanceDebt(Debt $originalDebt, array $newTerms): Debt
    {
        // Cancelar deuda original
        $originalDebt->update(['status' => 'refinanced']);

        // Crear nueva deuda con el saldo pendiente
        $newData = array_merge($newTerms, [
            'debtor_id' => $originalDebt->debtor_id,
            'original_amount' => $originalDebt->pending_amount,
            'concept' => 'Refinanciamiento de ' . $originalDebt->code,
        ]);

        return $this->createDebt($newData, $originalDebt->company_id);
    }

    public function getDebtSummary(int $companyId): array
    {
        return [
            'total_active' => Debt::byCompany($companyId)->active()->count(),
            'total_pending' => Debt::byCompany($companyId)->active()->sum('pending_amount'),
            'total_overdue' => Debt::byCompany($companyId)->overdue()->sum('pending_amount'),
            'total_overdue_count' => Debt::byCompany($companyId)->overdue()->count(),
            'total_paid_month' => Debt::byCompany($companyId)
                ->where('status', 'paid')
                ->whereMonth('updated_at', now()->month)
                ->count(),
            'recovery_rate' => $this->calculateRecoveryRate($companyId),
        ];
    }

    public function calculateRecoveryRate(int $companyId): float
    {
        $totalAmount = Debt::byCompany($companyId)->sum('total_amount');
        $paidAmount = Debt::byCompany($companyId)->sum('paid_amount');

        if ($totalAmount <= 0) return 0;

        return round(($paidAmount / $totalAmount) * 100, 2);
    }
}
