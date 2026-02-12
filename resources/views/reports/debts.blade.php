@extends('layouts.app')
@section('title', 'Reporte de Deudas')
@section('page-title', 'Reporte de Deudas')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Total deudas activas</div>
                    <div class="fw-bold fs-4">{{ $summary['total_debts'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Monto total</div>
                    <div class="fw-bold fs-4">S/ {{ number_format($summary['total_amount'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Total pendiente</div>
                    <div class="fw-bold fs-4 text-danger">S/ {{ number_format($summary['total_pending'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Total vencidas</div>
                    <div class="fw-bold fs-4 text-danger">{{ $summary['total_overdue'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach (['active' => 'Activa', 'partial' => 'Parcial', 'overdue' => 'Vencida', 'paid' => 'Pagada', 'cancelled' => 'Cancelada'] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}"></div>
                <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ request('date_to') }}"></div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    <a href="{{ route('reports.debts') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Deudor</th>
                        <th>Concepto</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                        <th>Estado</th>
                        <th>Gestor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debts as $debt)
                        @php $colors = ['active'=>'primary','paid'=>'success','partial'=>'warning','overdue'=>'danger','cancelled'=>'secondary']; @endphp
                        <tr>
                            <td><a href="{{ route('debts.show', $debt) }}"><code>{{ $debt->code }}</code></a></td>
                            <td>{{ Str::limit($debt->debtor->full_name, 22) }}</td>
                            <td>{{ Str::limit($debt->concept, 20) }}</td>
                            <td>S/ {{ number_format($debt->total_amount, 2) }}</td>
                            <td class="text-success">S/ {{ number_format($debt->paid_amount, 2) }}</td>
                            <td class="text-danger fw-bold">S/ {{ number_format($debt->pending_amount, 2) }}</td>
                            <td><span
                                    class="badge bg-{{ $colors[$debt->status] ?? 'secondary' }}">{{ ucfirst($debt->status) }}</span>
                            </td>
                            <td>{{ $debt->assignedUser?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($debts->hasPages())
            <div class="card-footer">{{ $debts->links() }}</div>
        @endif
    </div>
@endsection
