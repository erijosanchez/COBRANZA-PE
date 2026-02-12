@extends('layouts.app')
@section('title', 'Reporte de Pagos')
@section('page-title', 'Reporte de Pagos')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Total recaudado</div>
                    <div class="fw-bold fs-4 text-success">S/ {{ number_format($summary['total_collected'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Transacciones</div>
                    <div class="fw-bold fs-4">{{ $summary['total_transactions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Por método</div>
                    @foreach ($summary['by_method'] as $pm)
                        <div class="d-flex justify-content-between small">
                            <span>{{ $pm->paymentMethod->name }}</span>
                            <span class="fw-bold">S/ {{ number_format($pm->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ $dateFrom }}"></div>
                <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ $dateTo }}"></div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    <a href="{{ route('reports.export', ['type' => 'payments', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                        class="btn btn-outline-danger btn-sm"><i class="bi bi-file-pdf"></i> PDF</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Recibo</th>
                        <th>Deudor</th>
                        <th>Deuda</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $p)
                        <tr>
                            <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                            <td><code>{{ $p->receipt_number }}</code></td>
                            <td>{{ Str::limit($p->debtor->full_name, 22) }}</td>
                            <td><code>{{ $p->debt->code }}</code></td>
                            <td class="text-success fw-bold">S/ {{ number_format($p->amount, 2) }}</td>
                            <td>{{ $p->paymentMethod->name }}</td>
                            <td>{{ $p->registeredBy->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection
