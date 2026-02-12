<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Payment;
use App\Models\CollectionAction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.debts')->only('debts');
        $this->middleware('permission:reports.payments')->only('payments');
        $this->middleware('permission:reports.collections')->only('collections');
        $this->middleware('permission:reports.export')->only('export');
    }

    public function debts(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Debt::byCompany($companyId)->with(['debtor', 'assignedUser']);

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $debts = $query->orderByDesc('created_at')->paginate(20)->appends($request->query());

        $summary = [
            'total_debts' => Debt::byCompany($companyId)->active()->count(),
            'total_amount' => Debt::byCompany($companyId)->active()->sum('total_amount'),
            'total_pending' => Debt::byCompany($companyId)->active()->sum('pending_amount'),
            'total_paid' => Debt::byCompany($companyId)->active()->sum('paid_amount'),
            'total_overdue' => Debt::byCompany($companyId)->overdue()->count(),
        ];

        return view('reports.debts', compact('debts', 'summary'));
    }

    public function payments(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());

        $query = Payment::byCompany($companyId)
            ->confirmed()
            ->byDateRange($dateFrom, $dateTo)
            ->with(['debtor', 'debt', 'paymentMethod', 'registeredBy']);

        $payments = $query->orderByDesc('payment_date')->paginate(20)->appends($request->query());

        $summary = [
            'total_collected' => Payment::byCompany($companyId)->confirmed()->byDateRange($dateFrom, $dateTo)->sum('amount'),
            'total_transactions' => Payment::byCompany($companyId)->confirmed()->byDateRange($dateFrom, $dateTo)->count(),
            'by_method' => Payment::byCompany($companyId)
                ->confirmed()
                ->byDateRange($dateFrom, $dateTo)
                ->selectRaw('payment_method_id, SUM(amount) as total')
                ->groupBy('payment_method_id')
                ->with('paymentMethod')
                ->get(),
        ];

        return view('reports.payments', compact('payments', 'summary', 'dateFrom', 'dateTo'));
    }

    public function collections(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());

        $actions = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('action_date', [$dateFrom, $dateTo])
            ->with(['debt.debtor', 'user'])
            ->orderByDesc('action_date')
            ->paginate(20)
            ->appends($request->query());

        $summary = [
            'total_actions' => CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
                ->whereBetween('action_date', [$dateFrom, $dateTo])->count(),
            'by_type' => CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
                ->whereBetween('action_date', [$dateFrom, $dateTo])
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->get(),
            'by_result' => CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
                ->whereBetween('action_date', [$dateFrom, $dateTo])
                ->selectRaw('result, COUNT(*) as total')
                ->groupBy('result')
                ->get(),
        ];

        return view('reports.collections', compact('actions', 'summary', 'dateFrom', 'dateTo'));
    }

    public function overdue(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $debts = Debt::byCompany($companyId)
            ->overdue()
            ->with(['debtor', 'assignedUser'])
            ->orderByDesc('days_overdue')
            ->paginate(20)
            ->appends($request->query());

        $summary = [
            'total_overdue' => Debt::byCompany($companyId)->overdue()->count(),
            'total_amount' => Debt::byCompany($companyId)->overdue()->sum('pending_amount'),
            'avg_days' => Debt::byCompany($companyId)->overdue()->avg('days_overdue'),
        ];

        return view('reports.overdue', compact('debts', 'summary'));
    }

    public function export(Request $request, $type)
    {
        $companyId = auth()->user()->company_id;
        $company = auth()->user()->company;

        $data = match ($type) {
            'overdue' => [
                'title' => 'Reporte de Deudas Vencidas',
                'debts' => Debt::byCompany($companyId)->overdue()->with(['debtor', 'assignedUser'])->orderByDesc('days_overdue')->get(),
            ],
            'payments' => [
                'title' => 'Reporte de Pagos',
                'payments' => Payment::byCompany($companyId)->confirmed()
                    ->byDateRange(
                        $request->input('date_from', Carbon::now()->startOfMonth()->toDateString()),
                        $request->input('date_to', Carbon::now()->toDateString())
                    )
                    ->with(['debtor', 'paymentMethod'])
                    ->orderByDesc('payment_date')
                    ->get(),
            ],
            default => abort(404),
        };

        $data['company'] = $company;
        $data['generated_at'] = Carbon::now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView("reports.pdf.{$type}", $data);

        return $pdf->download("reporte-{$type}-" . date('Y-m-d') . '.pdf');
    }
}