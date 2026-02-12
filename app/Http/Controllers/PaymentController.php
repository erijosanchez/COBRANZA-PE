<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Installment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payments.index')->only('index');
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.edit')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only('destroy');
        $this->middleware('permission:payments.show')->only('show');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Payment::byCompany($companyId)
            ->with(['debtor', 'debt', 'paymentMethod', 'registeredBy']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                    ->orWhereHas('debtor', fn($dq) => $dq->search($request->search));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderByDesc('payment_date')->paginate(15)->appends($request->query());
        $paymentMethods = PaymentMethod::where('company_id', $companyId)->active()->get();

        return view('payments.index', compact('payments', 'paymentMethods'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $debtors = Debtor::byCompany($companyId)->active()->orderBy('full_name')->get();
        $paymentMethods = PaymentMethod::where('company_id', $companyId)->active()->get();

        $selectedDebt = null;
        $installments = collect();

        if ($request->filled('debt_id')) {
            $selectedDebt = Debt::with('debtor')->find($request->debt_id);
            $installments = $selectedDebt?->installments()->pending()->orderBy('number')->get() ?? collect();
        }

        return view('payments.create', compact('debtors', 'paymentMethods', 'selectedDebt', 'installments'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'debt_id' => 'required|exists:debts,id',
            'installment_id' => 'nullable|exists:installments,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $debt = Debt::where('id', $validated['debt_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $payment = Payment::create([
            'company_id' => $companyId,
            'debt_id' => $debt->id,
            'installment_id' => $validated['installment_id'] ?? null,
            'debtor_id' => $debt->debtor_id,
            'payment_method_id' => $validated['payment_method_id'],
            'registered_by' => auth()->id(),
            'receipt_number' => Payment::generateReceipt($companyId),
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'confirmed',
        ]);

        // Actualizar cuota si se especificó
        if ($payment->installment_id) {
            $payment->installment->recalculate();
        }

        // Recalcular deuda
        $debt->recalculateAmounts();

        return redirect()->route('payments.show', $payment)->with('success', 'Pago registrado exitosamente. Recibo: ' . $payment->receipt_number);
    }

    public function show(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->load(['debtor', 'debt', 'installment', 'paymentMethod', 'registeredBy']);

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $this->authorizeCompany($payment);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Solo se pueden editar pagos pendientes.');
        }

        $companyId = auth()->user()->company_id;
        $paymentMethods = PaymentMethod::where('company_id', $companyId)->active()->get();

        return view('payments.edit', compact('payment', 'paymentMethods'));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorizeCompany($payment);

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.show', $payment)->with('success', 'Pago actualizado.');
    }

    public function destroy(Payment $payment)
    {
        $this->authorizeCompany($payment);

        $debt = $payment->debt;
        $installment = $payment->installment;

        $payment->delete();

        if ($installment) {
            $installment->recalculate();
        }
        $debt->recalculateAmounts();

        return redirect()->route('payments.index')->with('success', 'Pago eliminado.');
    }

    public function confirm(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->update(['status' => 'confirmed']);
        $payment->debt->recalculateAmounts();

        if ($payment->installment) {
            $payment->installment->recalculate();
        }

        return back()->with('success', 'Pago confirmado.');
    }

    public function reject(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->update(['status' => 'rejected']);
        $payment->debt->recalculateAmounts();

        return back()->with('success', 'Pago rechazado.');
    }

    public function reverse(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->update(['status' => 'reversed']);
        $payment->debt->recalculateAmounts();

        if ($payment->installment) {
            $payment->installment->recalculate();
        }

        return back()->with('success', 'Pago reversado.');
    }

    private function authorizeCompany($model): void
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
