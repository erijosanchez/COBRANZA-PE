<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $methods = PaymentMethod::where('company_id', $companyId)->orderBy('name')->get();

        return view('settings.payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('settings.payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        PaymentMethod::create($validated);

        return redirect()->route('settings.payment-methods.index')->with('success', 'Método de pago creado.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        return view('settings.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()->route('settings.payment-methods.index')->with('success', 'Método actualizado.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'No se puede eliminar un método con pagos asociados.');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Método eliminado.');
    }
}
