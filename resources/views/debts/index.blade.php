@extends('layouts.app')
@section('title', 'Deudas')
@section('page-title', 'Deudas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        @can('debts.create')
            <a href="{{ route('debts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva Deuda</a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Buscar código, concepto, deudor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        @foreach (['active' => 'Activa', 'partial' => 'Parcial', 'overdue' => 'Vencida', 'paid' => 'Pagada', 'cancelled' => 'Cancelada'] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">Todos los gestores</option>
                        @foreach ($gestores as $g)
                            <option value="{{ $g->id }}" {{ request('assigned_to') == $g->id ? 'selected' : '' }}>
                                {{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}" placeholder="Desde">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('debts.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Deudor</th>
                        <th>Concepto</th>
                        <th>Total</th>
                        <th>Pendiente</th>
                        <th>Vence</th>
                        <th>Días mora</th>
                        <th>Estado</th>
                        <th>Gestor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debts as $debt)
                        @php
                            $colors = [
                                'active' => 'primary',
                                'paid' => 'success',
                                'partial' => 'warning',
                                'overdue' => 'danger',
                                'cancelled' => 'secondary',
                                'refinanced' => 'info',
                            ];
                        @endphp
                        <tr>
                            <td><code>{{ $debt->code }}</code></td>
                            <td><a href="{{ route('debtors.show', $debt->debtor) }}"
                                    class="text-decoration-none">{{ Str::limit($debt->debtor->full_name, 22) }}</a></td>
                            <td>{{ Str::limit($debt->concept, 20) }}</td>
                            <td>S/ {{ number_format($debt->total_amount, 2) }}</td>
                            <td class="fw-bold text-danger">S/ {{ number_format($debt->pending_amount, 2) }}</td>
                            <td>{{ $debt->due_date->format('d/m/Y') }}</td>
                            <td>
                                @if ($debt->days_overdue > 0)
                                    <span class="badge bg-danger">{{ $debt->days_overdue }}d</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span
                                    class="badge bg-{{ $colors[$debt->status] ?? 'secondary' }}">{{ ucfirst($debt->status) }}</span>
                            </td>
                            <td>{{ $debt->assignedUser?->name ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('debts.show', $debt) }}" class="btn btn-outline-primary"><i
                                            class="bi bi-eye"></i></a>
                                    <a href="{{ route('payments.create', ['debt_id' => $debt->id]) }}"
                                        class="btn btn-outline-success" title="Registrar pago"><i
                                            class="bi bi-cash"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No se encontraron deudas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($debts->hasPages())
            <div class="card-footer">{{ $debts->links() }}</div>
        @endif
    </div>
@endsection
