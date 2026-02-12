@extends('layouts.app')
@section('title', 'Reporte de Gestiones')
@section('page-title', 'Reporte de Gestiones')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <div class="text-muted small">Total gestiones</div>
                    <div class="fw-bold fs-4">{{ $summary['total_actions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Por tipo</div>
                    @foreach ($summary['by_type'] as $t)
                        <div class="d-flex justify-content-between small">
                            <span>{{ str_replace('_', ' ', ucfirst($t->type)) }}</span>
                            <span class="fw-bold">{{ $t->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Por resultado</div>
                    @foreach ($summary['by_result'] as $r)
                        <div class="d-flex justify-content-between small">
                            <span>{{ str_replace('_', ' ', ucfirst($r->result)) }}</span>
                            <span class="fw-bold">{{ $r->total }}</span>
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
                        <th>Deudor</th>
                        <th>Deuda</th>
                        <th>Tipo</th>
                        <th>Resultado</th>
                        <th>Gestor</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($actions as $a)
                        <tr>
                            <td>{{ $a->action_date->format('d/m/Y') }}</td>
                            <td>{{ Str::limit($a->debt->debtor->full_name, 20) }}</td>
                            <td><code>{{ $a->debt->code }}</code></td>
                            <td>{{ $a->type_label }}</td>
                            <td>{{ $a->result_label }}</td>
                            <td>{{ $a->user->name }}</td>
                            <td>{{ Str::limit($a->notes, 30) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($actions->hasPages())
            <div class="card-footer">{{ $actions->links() }}</div>
        @endif
    </div>
@endsection
