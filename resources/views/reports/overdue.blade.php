@extends('layouts.app')
@section('title', 'Reporte de Morosidad')
@section('page-title', 'Reporte de Morosidad')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Deudas vencidas</div>
                    <div class="fw-bold fs-4 text-danger">{{ $summary['total_overdue'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Monto vencido total</div>
                    <div class="fw-bold fs-4 text-danger">S/ {{ number_format($summary['total_amount'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Promedio días mora</div>
                    <div class="fw-bold fs-4">{{ number_format($summary['avg_days'], 0) }} días</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('reports.export', 'overdue') }}" class="btn btn-outline-danger btn-sm"><i
                class="bi bi-file-pdf"></i> Exportar PDF</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Deudor</th>
                        <th>Concepto</th>
                        <th>Pendiente</th>
                        <th>Días mora</th>
                        <th>Vencimiento</th>
                        <th>Gestor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debts as $debt)
                        <tr>
                            <td><a href="{{ route('debts.show', $debt) }}"><code>{{ $debt->code }}</code></a></td>
                            <td>{{ Str::limit($debt->debtor->full_name, 22) }}</td>
                            <td>{{ Str::limit($debt->concept, 20) }}</td>
                            <td class="text-danger fw-bold">S/ {{ number_format($debt->pending_amount, 2) }}</td>
                            <td><span class="badge bg-danger">{{ $debt->days_overdue }}d</span></td>
                            <td>{{ $debt->due_date->format('d/m/Y') }}</td>
                            <td>{{ $debt->assignedUser?->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('collection-actions.create', ['debt_id' => $debt->id]) }}"
                                    class="btn btn-sm btn-outline-primary" title="Gestionar"><i
                                        class="bi bi-telephone"></i></a>
                            </td>
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
