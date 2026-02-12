<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Installment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DebtSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $debtors = Debtor::where('company_id', $company->id)->get();
        $gestores = User::role('gestor')->where('company_id', $company->id)->get();

        $debtsData = [
            [
                'debtor_index' => 0,
                'concept' => 'Préstamo personal',
                'original_amount' => 5000,
                'installments_count' => 12,
                'interest_type' => 'monthly',
                'interest_rate' => 3.5,
                'issue_date' => Carbon::now()->subMonths(6),
                'status' => 'overdue',
            ],
            [
                'debtor_index' => 1,
                'concept' => 'Crédito comercial',
                'original_amount' => 15000,
                'installments_count' => 24,
                'interest_type' => 'monthly',
                'interest_rate' => 2.5,
                'issue_date' => Carbon::now()->subMonths(3),
                'status' => 'active',
            ],
            [
                'debtor_index' => 2,
                'concept' => 'Préstamo vehicular',
                'original_amount' => 25000,
                'installments_count' => 36,
                'interest_type' => 'daily',
                'interest_rate' => 0.1,
                'issue_date' => Carbon::now()->subMonths(8),
                'status' => 'partial',
            ],
            [
                'debtor_index' => 3,
                'concept' => 'Línea de crédito empresarial',
                'original_amount' => 50000,
                'installments_count' => 12,
                'interest_type' => 'fixed',
                'interest_rate' => 15,
                'issue_date' => Carbon::now()->subMonths(2),
                'status' => 'active',
            ],
            [
                'debtor_index' => 4,
                'concept' => 'Préstamo de consumo',
                'original_amount' => 3000,
                'installments_count' => 6,
                'interest_type' => 'none',
                'interest_rate' => 0,
                'issue_date' => Carbon::now()->subMonths(4),
                'status' => 'overdue',
            ],
            [
                'debtor_index' => 0,
                'concept' => 'Refinanciamiento',
                'original_amount' => 8000,
                'installments_count' => 18,
                'interest_type' => 'monthly',
                'interest_rate' => 4.0,
                'issue_date' => Carbon::now()->subMonth(),
                'status' => 'active',
            ],
        ];

        foreach ($debtsData as $index => $data) {
            $debtor = $debtors[$data['debtor_index']];
            $gestor = $gestores[$index % $gestores->count()];

            $totalAmount = $data['interest_type'] === 'fixed'
                ? $data['original_amount'] * (1 + $data['interest_rate'] / 100)
                : $data['original_amount'];

            $paidAmount = match ($data['status']) {
                'partial' => round($totalAmount * 0.3, 2),
                'overdue' => round($totalAmount * 0.1, 2),
                default => 0,
            };

            $debt = Debt::create([
                'company_id' => $company->id,
                'debtor_id' => $debtor->id,
                'code' => Debt::generateCode($company->id),
                'concept' => $data['concept'],
                'original_amount' => $data['original_amount'],
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $totalAmount - $paidAmount,
                'currency' => 'PEN',
                'installments_count' => $data['installments_count'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['issue_date']->copy()->addMonths($data['installments_count']),
                'interest_type' => $data['interest_type'],
                'interest_rate' => $data['interest_rate'],
                'status' => $data['status'],
                'days_overdue' => in_array($data['status'], ['overdue']) ? rand(15, 90) : 0,
                'assigned_to' => $gestor->id,
            ]);

            // Crear cuotas
            $installmentAmount = round($totalAmount / $data['installments_count'], 2);

            for ($i = 1; $i <= $data['installments_count']; $i++) {
                $dueDate = $data['issue_date']->copy()->addMonths($i);
                $installmentStatus = 'pending';
                $installmentPaid = 0;

                if ($data['status'] === 'partial' && $i <= 3) {
                    $installmentStatus = 'paid';
                    $installmentPaid = $installmentAmount;
                } elseif ($data['status'] === 'overdue' && $i == 1) {
                    $installmentStatus = 'paid';
                    $installmentPaid = $installmentAmount;
                }

                if ($dueDate->lt(now()) && $installmentStatus === 'pending') {
                    $installmentStatus = 'overdue';
                }

                Installment::create([
                    'debt_id' => $debt->id,
                    'number' => $i,
                    'amount' => $installmentAmount,
                    'interest_amount' => 0,
                    'penalty_amount' => 0,
                    'total_amount' => $installmentAmount,
                    'paid_amount' => $installmentPaid,
                    'due_date' => $dueDate,
                    'paid_date' => $installmentStatus === 'paid' ? $dueDate : null,
                    'status' => $installmentStatus,
                ]);
            }
        }
    }
}
