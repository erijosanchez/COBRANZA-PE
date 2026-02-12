<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Payment;
use App\Models\CollectionAction;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // KPIs principales
        $totalDebtors = Debtor::byCompany($companyId)->active()->count();

        $totalDebtsActive = Debt::byCompany($companyId)->active()->count();

        $totalPending = Debt::byCompany($companyId)
            ->active()
            ->sum('pending_amount');

        $totalOverdue = Debt::byCompany($companyId)
            ->overdue()
            ->sum('pending_amount');

        $collectionsThisMonth = Payment::byCompany($companyId)
            ->confirmed()
            ->byDateRange($startOfMonth, $endOfMonth)
            ->sum('amount');

        $collectionsToday = Payment::byCompany($companyId)
            ->confirmed()
            ->byDateRange($today, $today)
            ->sum('amount');

        $actionsToday = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->whereDate('action_date', $today)
            ->count();

        $overdueInstallments = Installment::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->overdue()
            ->count();

        // Deudas más antiguas vencidas
        $topOverdue = Debt::byCompany($companyId)
            ->overdue()
            ->with('debtor')
            ->orderByDesc('days_overdue')
            ->limit(10)
            ->get();

        // Próximas cuotas por vencer (7 días)
        $upcomingInstallments = Installment::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->pending()
            ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
            ->with('debt.debtor')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Promesas de pago pendientes
        $pendingPromises = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->promises()
            ->where('promise_date', '>=', $today)
            ->with(['debt.debtor', 'user'])
            ->orderBy('promise_date')
            ->limit(10)
            ->get();

        // Últimos pagos
        $recentPayments = Payment::byCompany($companyId)
            ->confirmed()
            ->with(['debtor', 'debt', 'paymentMethod'])
            ->orderByDesc('payment_date')
            ->limit(10)
            ->get();

        // Recaudación por mes (últimos 6 meses)
        $monthlyCollections = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $amount = Payment::byCompany($companyId)
                ->confirmed()
                ->byDateRange($month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString())
                ->sum('amount');
            $monthlyCollections[] = [
                'month' => $month->format('M Y'),
                'amount' => $amount,
            ];
        }

        return view('dashboard', compact(
            'totalDebtors',
            'totalDebtsActive',
            'totalPending',
            'totalOverdue',
            'collectionsThisMonth',
            'collectionsToday',
            'actionsToday',
            'overdueInstallments',
            'topOverdue',
            'upcomingInstallments',
            'pendingPromises',
            'recentPayments',
            'monthlyCollections'
        ));
    }
}
