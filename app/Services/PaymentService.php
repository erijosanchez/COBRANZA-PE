<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Debt;
use App\Models\Installment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function registerPayment(array $data, int $companyId, int $userId): Payment
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            $debt = Debt::where('id', $data['debt_id'])
                ->where('company_id', $companyId)
                ->firstOrFail();

            $payment = Payment::create([
                'company_id' => $companyId,
                'debt_id' => $debt->id,
                'installment_id' => $data['installment_id'] ?? null,
                'debtor_id' => $debt->debtor_id,
                'payment_method_id' => $data['payment_method_id'],
                'registered_by' => $userId,
                'receipt_number' => Payment::generateReceipt($companyId),
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
            ]);

            // Actualizar cuota si se especificó
            if ($payment->installment_id) {
                $this->applyToInstallment($payment);
            } else {
                // Distribuir pago automáticamente entre cuotas pendientes
                $this->distributePayment($payment);
            }

            // Recalcular deuda
            $debt->recalculateAmounts();

            return $payment;
        });
    }

    private function applyToInstallment(Payment $payment): void
    {
        $installment = $payment->installment;
        $installment->recalculate();
    }

    private function distributePayment(Payment $payment): void
    {
        $remaining = $payment->amount;
        $installments = $payment->debt->installments()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('number')
            ->get();

        foreach ($installments as $installment) {
            if ($remaining <= 0) break;

            $pendingOnInstallment = $installment->total_amount - $installment->paid_amount;

            if ($remaining >= $pendingOnInstallment) {
                $installment->update([
                    'paid_amount' => $installment->total_amount,
                    'status' => 'paid',
                    'paid_date' => $payment->payment_date,
                ]);
                $remaining -= $pendingOnInstallment;
            } else {
                $installment->update([
                    'paid_amount' => $installment->paid_amount + $remaining,
                    'status' => 'partial',
                ]);
                $remaining = 0;
            }
        }
    }

    public function reversePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'reversed']);

            if ($payment->installment) {
                $payment->installment->recalculate();
            }

            $payment->debt->recalculateAmounts();
        });
    }

    public function getPaymentStats(int $companyId, string $dateFrom, string $dateTo): array
    {
        $base = Payment::byCompany($companyId)->confirmed()->byDateRange($dateFrom, $dateTo);

        return [
            'total' => (clone $base)->sum('amount'),
            'count' => (clone $base)->count(),
            'average' => (clone $base)->avg('amount') ?? 0,
            'by_method' => (clone $base)
                ->selectRaw('payment_method_id, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('payment_method_id')
                ->with('paymentMethod')
                ->get(),
            'by_day' => (clone $base)
                ->selectRaw('DATE(payment_date) as date, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }
}
