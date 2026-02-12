<?php

namespace App\Http\Controllers;

use App\Models\CollectionAction;
use App\Models\Debt;
use Illuminate\Http\Request;

class CollectionActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:collections.index')->only('index');
        $this->middleware('permission:collections.create')->only(['create', 'store']);
        $this->middleware('permission:collections.edit')->only(['edit', 'update']);
        $this->middleware('permission:collections.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = CollectionAction::whereHas('debt', fn($q) => $q->where('company_id', $companyId))
            ->with(['debt.debtor', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('action_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('action_date', '<=', $request->date_to);
        }

        $actions = $query->orderByDesc('action_date')->orderByDesc('action_time')->paginate(15)->appends($request->query());

        return view('collection-actions.index', compact('actions'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $debts = Debt::byCompany($companyId)->active()->with('debtor')->get();

        $selectedDebt = $request->filled('debt_id') ? Debt::with('debtor')->find($request->debt_id) : null;

        return view('collection-actions.create', compact('debts', 'selectedDebt'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'debt_id' => 'required|exists:debts,id',
            'type' => 'required|in:phone_call,whatsapp,email,visit,letter,legal_notice,promise_to_pay,other',
            'result' => 'required|in:contacted,no_answer,promise_to_pay,refused,wrong_number,scheduled,other',
            'action_date' => 'required|date',
            'action_time' => 'nullable|date_format:H:i',
            'promise_date' => 'nullable|required_if:result,promise_to_pay|date|after:today',
            'promise_amount' => 'nullable|required_if:result,promise_to_pay|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $debt = Debt::where('id', $validated['debt_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        CollectionAction::create([
            'debt_id' => $debt->id,
            'debtor_id' => $debt->debtor_id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'result' => $validated['result'],
            'action_date' => $validated['action_date'],
            'action_time' => $validated['action_time'] ?? null,
            'promise_date' => $validated['promise_date'] ?? null,
            'promise_amount' => $validated['promise_amount'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('collection-actions.index')->with('success', 'Gestión de cobranza registrada.');
    }

    public function edit(CollectionAction $collectionAction)
    {
        $companyId = auth()->user()->company_id;
        $debt = $collectionAction->debt;

        if ($debt->company_id !== $companyId) {
            abort(403);
        }

        return view('collection-actions.edit', compact('collectionAction'));
    }

    public function update(Request $request, CollectionAction $collectionAction)
    {
        $validated = $request->validate([
            'type' => 'required|in:phone_call,whatsapp,email,visit,letter,legal_notice,promise_to_pay,other',
            'result' => 'required|in:contacted,no_answer,promise_to_pay,refused,wrong_number,scheduled,other',
            'action_date' => 'required|date',
            'action_time' => 'nullable|date_format:H:i',
            'promise_date' => 'nullable|date',
            'promise_amount' => 'nullable|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $collectionAction->update($validated);

        return redirect()->route('collection-actions.index')->with('success', 'Gestión actualizada.');
    }

    public function destroy(CollectionAction $collectionAction)
    {
        if ($collectionAction->debt->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $collectionAction->delete();

        return back()->with('success', 'Gestión eliminada.');
    }

    public function byDebt(Debt $debt)
    {
        if ($debt->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $actions = $debt->collectionActions()
            ->with('user')
            ->orderByDesc('action_date')
            ->paginate(15);

        return view('collection-actions.by-debt', compact('debt', 'actions'));
    }
}
