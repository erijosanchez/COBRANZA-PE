<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Payment;
use App\Models\CollectionAction;
use App\Models\Debtor;
use Carbon\Carbon;

class ReportService
{
    public function getDashboardData(int $companyId): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return [
            'kpis' => [
                'total_debtors' => Debtor::byCompany($companyId)->active()->count(),
                'total_debts_active' => Debt::byCompany($companyId)->active()->count(),
                'total_pending' => Debt::byCompany($companyId)->active()->sum('pending_amount'),
                'total_overdue' => Debt::byCompany($companyId)->overdue()->sum('pending_amount'),
                'collections_month' => Payment::byCompany($companyId)->confirmed()->byDateRange($startOfMonth, $endOfMonth)->sum('amount'),
                'collections_today' => Payment::byCompany($companyId)->confirmed()->byDateRange($today, $today)->sum('amount'),
                'actions_today' => CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))->whereDate('action_date', $today)->count(),
            ],
            'monthly_collections' => $this->getMonthlyCollections($companyId, 6),
            'debt_by_status' => $this->getDebtByStatus($companyId),
            'top_gestores' => $this->getTopGestores($companyId),
            'aging_report' => $this->getAgingReport($companyId),
        ];
    }

    public function getMonthlyCollections(int $companyId, int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $amount = Payment::byCompany($companyId)
                ->confirmed()
                ->byDateRange(
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString()
                )
                ->sum('amount');

            $data[] = [
                'month' => $month->format('M Y'),
                'short' => $month->format('M'),
                'amount' => round($amount, 2),
            ];
        }
        return $data;
    }

    public function getDebtByStatus(int $companyId): array
    {
        return Debt::byCompany($companyId)
            ->selectRaw('status, COUNT(*) as count, SUM(pending_amount) as total')
            ->groupBy('status')
            ->get()
            ->toArray();
    }

    public function getTopGestores(int $companyId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->where('action_date', '>=', $startOfMonth)
            ->selectRaw('user_id, COUNT(*) as total_actions')
            ->groupBy('user_id')
            ->orderByDesc('total_actions')
            ->with('user:id,name')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getAgingReport(int $companyId): array
    {
        $ranges = [
            ['label' => '1-30 días', 'min' => 1, 'max' => 30],
            ['label' => '31-60 días', 'min' => 31, 'max' => 60],
            ['label' => '61-90 días', 'min' => 61, 'max' => 90],
            ['label' => '91-180 días', 'min' => 91, 'max' => 180],
            ['label' => '180+ días', 'min' => 181, 'max' => 99999],
        ];

        $report = [];
        foreach ($ranges as $range) {
            $debts = Debt::byCompany($companyId)
                ->overdue()
                ->whereBetween('days_overdue', [$range['min'], $range['max']]);

            $report[] = [
                'label' => $range['label'],
                'count' => (clone $debts)->count(),
                'amount' => round((clone $debts)->sum('pending_amount'), 2),
            ];
        }

        return $report;
    }
}
