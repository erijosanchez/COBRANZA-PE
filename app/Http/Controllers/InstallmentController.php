<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Debt $debt)
    {
        $this->authorizeCompany($debt);

        $installments = $debt->installments()->orderBy('number')->get();

        return view('installments.index', compact('debt', 'installments'));
    }

    public function update(Request $request, Installment $installment)
    {
        $debt = $installment->debt;
        $this->authorizeCompany($debt);

        $validated = $request->validate([
            'due_date' => 'required|date',
            'penalty_amount' => 'nullable|numeric|min:0',
        ]);

        $installment->update([
            'due_date' => $validated['due_date'],
            'penalty_amount' => $validated['penalty_amount'] ?? 0,
            'total_amount' => $installment->amount + $installment->interest_amount + ($validated['penalty_amount'] ?? 0),
        ]);

        return back()->with('success', 'Cuota actualizada.');
    }

    private function authorizeCompany($model): void
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}