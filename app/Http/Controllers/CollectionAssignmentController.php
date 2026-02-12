<?php

namespace App\Http\Controllers;

use App\Models\CollectionAssignment;
use App\Models\Debtor;
use App\Models\User;
use Illuminate\Http\Request;

class CollectionAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:assignments.index')->only('index');
        $this->middleware('permission:assignments.create')->only(['create', 'store']);
        $this->middleware('permission:assignments.edit')->only(['edit', 'update']);
        $this->middleware('permission:assignments.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = CollectionAssignment::where('company_id', $companyId)
            ->with(['debtor', 'user', 'assignedByUser']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $assignments = $query->orderByDesc('assigned_date')->paginate(15)->appends($request->query());

        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        return view('assignments.index', compact('assignments', 'gestores'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $debtors = Debtor::byCompany($companyId)->active()->orderBy('full_name')->get();
        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        return view('assignments.create', compact('debtors', 'gestores'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'debtor_id' => 'required|exists:debtors,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Desactivar asignaciones previas del mismo deudor
        CollectionAssignment::where('company_id', $companyId)
            ->where('debtor_id', $validated['debtor_id'])
            ->where('is_active', true)
            ->update(['is_active' => false, 'end_date' => now()]);

        CollectionAssignment::create([
            'company_id' => $companyId,
            'debtor_id' => $validated['debtor_id'],
            'user_id' => $validated['user_id'],
            'assigned_by' => auth()->id(),
            'assigned_date' => $validated['assigned_date'],
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Actualizar deudas activas del deudor
        \App\Models\Debt::where('company_id', $companyId)
            ->where('debtor_id', $validated['debtor_id'])
            ->active()
            ->update(['assigned_to' => $validated['user_id']]);

        return redirect()->route('assignments.index')->with('success', 'Asignación creada exitosamente.');
    }

    public function edit(CollectionAssignment $assignment)
    {
        if ($assignment->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $companyId = auth()->user()->company_id;
        $gestores = User::byCompany($companyId)->role('gestor')->active()->get();

        return view('assignments.edit', compact('assignment', 'gestores'));
    }

    public function update(Request $request, CollectionAssignment $assignment)
    {
        if ($assignment->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $assignment->update($validated);

        return redirect()->route('assignments.index')->with('success', 'Asignación actualizada.');
    }

    public function destroy(CollectionAssignment $assignment)
    {
        if ($assignment->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $assignment->delete();

        return back()->with('success', 'Asignación eliminada.');
    }

    public function deactivate(CollectionAssignment $assignment)
    {
        if ($assignment->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $assignment->update(['is_active' => false, 'end_date' => now()]);

        return back()->with('success', 'Asignación desactivada.');
    }
}