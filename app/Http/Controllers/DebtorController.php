<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Debtor;

class DebtorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:debtors.index')->only('index');
        $this->middleware('permission:debtors.create')->only(['create', 'store']);
        $this->middleware('permission:debtors.edit')->only(['edit', 'update']);
        $this->middleware('permission:debtors.delete')->only('destroy');
        $this->middleware('permission:debtors.show')->only('show');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Debtor::byCompany($companyId)->withCount([
            'debts as active_debts_count' => fn($q) => $q->active(),
        ])->withSum([
            'debts as total_pending' => fn($q) => $q->active(),
        ], 'pending_amount');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $debtors = $query->orderBy('full_name')->paginate(15)->appends($request->query());

        return view('debtors.index', compact('debtors'));
    }

    public function create()
    {
        return view('debtors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:DNI,RUC,CE,PASAPORTE',
            'document_number' => 'required|string|max:20',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;

        // Verificar duplicado
        $exists = Debtor::where('company_id', $companyId)
            ->where('document_number', $validated['document_number'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['document_number' => 'Ya existe un deudor con este número de documento.']);
        }

        $validated['company_id'] = $companyId;
        Debtor::create($validated);

        return redirect()->route('debtors.index')->with('success', 'Deudor registrado exitosamente.');
    }

    public function show(Debtor $debtor)
    {
        $this->authorizeCompany($debtor);

        $debtor->load([
            'debts' => fn($q) => $q->orderByDesc('created_at'),
            'debts.installments',
            'debts.assignedUser',
            'collectionActions' => fn($q) => $q->orderByDesc('action_date')->limit(20),
            'collectionActions.user',
            'payments' => fn($q) => $q->confirmed()->orderByDesc('payment_date')->limit(20),
            'payments.paymentMethod',
        ]);

        return view('debtors.show', compact('debtor'));
    }

    public function edit(Debtor $debtor)
    {
        $this->authorizeCompany($debtor);
        return view('debtors.edit', compact('debtor'));
    }

    public function update(Request $request, Debtor $debtor)
    {
        $this->authorizeCompany($debtor);

        $validated = $request->validate([
            'document_type' => 'required|in:DNI,RUC,CE,PASAPORTE',
            'document_number' => 'required|string|max:20',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Verificar duplicado excluyendo el actual
        $exists = Debtor::where('company_id', auth()->user()->company_id)
            ->where('document_number', $validated['document_number'])
            ->where('id', '!=', $debtor->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['document_number' => 'Ya existe otro deudor con este número de documento.']);
        }

        $debtor->update($validated);

        return redirect()->route('debtors.show', $debtor)->with('success', 'Deudor actualizado exitosamente.');
    }

    public function destroy(Debtor $debtor)
    {
        $this->authorizeCompany($debtor);

        if ($debtor->debts()->active()->exists()) {
            return back()->with('error', 'No se puede eliminar un deudor con deudas activas.');
        }

        $debtor->delete();

        return redirect()->route('debtors.index')->with('success', 'Deudor eliminado exitosamente.');
    }

    public function debts(Debtor $debtor)
    {
        $this->authorizeCompany($debtor);

        $debts = $debtor->debts()
            ->with(['installments', 'assignedUser'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('debtors.debts', compact('debtor', 'debts'));
    }

    private function authorizeCompany($model): void
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
