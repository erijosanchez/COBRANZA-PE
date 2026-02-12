<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Installment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:debts.index')->only('index');
        $this->middleware('permission:debts.create')->only(['create', 'store']);
        $this->middleware('permission:debts.edit')->only(['edit', 'update']);
        $this->middleware('permission:debts.delete')->only('destroy');
        $this->middleware('permission:debts.show')->only('show');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Debt::byCompany($companyId)->with(['debtor', 'assignedUser']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('interest_type')) {
            $query->where('interest_type', $request->interest_type);
        }

        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $debts = $query->orderByDesc('created_at')->paginate(15)->appends($request->query());

        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        return view('debts.index', compact('debts', 'gestores'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $debtors = Debtor::byCompany($companyId)->active()->orderBy('full_name')->get();
        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        $selectedDebtor = $request->filled('debtor_id') ? Debtor::find($request->debtor_id) : null;

        return view('debts.create', compact('debtors', 'gestores', 'selectedDebtor'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'debtor_id' => 'required|exists:debtors,id',
            'concept' => 'required|string|max:255',
            'description' => 'nullable|string',
            'original_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:PEN,USD',
            'installments_count' => 'required|integer|min:1|max:120',
            'issue_date' => 'required|date',
            'interest_type' => 'required|in:none,fixed,daily,monthly',
            'interest_rate' => 'required_unless:interest_type,none|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Verificar que el deudor pertenece a la empresa
        $debtor = Debtor::where('id', $validated['debtor_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Calcular monto total
        $originalAmount = $validated['original_amount'];
        $totalAmount = $originalAmount;

        if ($validated['interest_type'] === 'fixed') {
            $totalAmount = $originalAmount * (1 + ($validated['interest_rate'] / 100));
        }

        $issueDate = Carbon::parse($validated['issue_date']);

        $debt = Debt::create([
            'company_id' => $companyId,
            'debtor_id' => $debtor->id,
            'code' => Debt::generateCode($companyId),
            'concept' => $validated['concept'],
            'description' => $validated['description'] ?? null,
            'original_amount' => $originalAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'pending_amount' => $totalAmount,
            'currency' => $validated['currency'],
            'installments_count' => $validated['installments_count'],
            'issue_date' => $issueDate,
            'due_date' => $issueDate->copy()->addMonths($validated['installments_count']),
            'interest_type' => $validated['interest_type'],
            'interest_rate' => $validated['interest_type'] === 'none' ? 0 : $validated['interest_rate'],
            'status' => 'active',
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        // Generar cuotas
        $installmentAmount = round($totalAmount / $validated['installments_count'], 2);
        $remainder = round($totalAmount - ($installmentAmount * $validated['installments_count']), 2);

        for ($i = 1; $i <= $validated['installments_count']; $i++) {
            $amount = $installmentAmount;
            // Ajustar la última cuota por redondeo
            if ($i === $validated['installments_count']) {
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

        return redirect()->route('debts.show', $debt)->with('success', 'Deuda registrada con ' . $validated['installments_count'] . ' cuotas.');
    }

    public function show(Debt $debt)
    {
        $this->authorizeCompany($debt);

        $debt->load([
            'debtor',
            'assignedUser',
            'installments' => fn($q) => $q->orderBy('number'),
            'payments' => fn($q) => $q->with('paymentMethod', 'registeredBy')->orderByDesc('payment_date'),
            'collectionActions' => fn($q) => $q->with('user')->orderByDesc('action_date'),
        ]);

        return view('debts.show', compact('debt'));
    }

    public function edit(Debt $debt)
    {
        $this->authorizeCompany($debt);

        if ($debt->status === 'paid') {
            return back()->with('error', 'No se puede editar una deuda pagada.');
        }

        $companyId = auth()->user()->company_id;
        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        return view('debts.edit', compact('debt', 'gestores'));
    }

    public function update(Request $request, Debt $debt)
    {
        $this->authorizeCompany($debt);

        if ($debt->status === 'paid') {
            return back()->with('error', 'No se puede editar una deuda pagada.');
        }

        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'description' => 'nullable|string',
            'interest_type' => 'required|in:none,fixed,daily,monthly',
            'interest_rate' => 'required_unless:interest_type,none|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $debt->update($validated);

        return redirect()->route('debts.show', $debt)->with('success', 'Deuda actualizada exitosamente.');
    }

    public function destroy(Debt $debt)
    {
        $this->authorizeCompany($debt);

        if ($debt->paid_amount > 0) {
            return back()->with('error', 'No se puede eliminar una deuda con pagos registrados.');
        }

        $debt->delete();

        return redirect()->route('debts.index')->with('success', 'Deuda eliminada exitosamente.');
    }

    public function recalculate(Debt $debt)
    {
        $this->authorizeCompany($debt);
        $debt->updateOverdueStatus();
        $debt->recalculateAmounts();

        return back()->with('success', 'Montos recalculados exitosamente.');
    }

    public function cancel(Debt $debt)
    {
        $this->authorizeCompany($debt);

        $debt->update(['status' => 'cancelled']);

        return back()->with('success', 'Deuda cancelada exitosamente.');
    }

    private function authorizeCompany($model): void
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
