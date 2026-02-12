@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Deudores activos</div>
                        <div class="fw-bold fs-5">{{ number_format($totalDebtors) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Deudas activas</div>
                        <div class="fw-bold fs-5">{{ number_format($totalDebtsActive) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total vencido</div>
                        <div class="fw-bold fs-5">S/ {{ number_format($totalOverdue, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cobrado este mes</div>
                        <div class="fw-bold fs-5">S/ {{ number_format($collectionsThisMonth, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Segunda fila KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total pendiente</div>
                        <div class="fw-bold fs-5">S/ {{ number_format($totalPending, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cobrado hoy</div>
                        <div class="fw-bold fs-5">S/ {{ number_format($collectionsToday, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Gestiones hoy</div>
                        <div class="fw-bold fs-5">{{ number_format($actionsToday) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-calendar-x-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cuotas vencidas</div>
                        <div class="fw-bold fs-5">{{ number_format($overdueInstallments) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Deudas más vencidas --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle text-danger me-1"></i> Mayor morosidad</h6>
                    <a href="{{ route('reports.overdue') }}" class="btn btn-sm btn-outline-primary">Ver todo</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Deudor</th>
                                    <th>Pendiente</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topOverdue as $debt)
                                    <tr>
                                        <td>
                                            <a href="{{ route('debts.show', $debt) }}" class="text-decoration-none">
                                                {{ Str::limit($debt->debtor->full_name, 25) }}
                                            </a>
                                        </td>
                                        <td class="text-danger fw-bold">S/ {{ number_format($debt->pending_amount, 2) }}
                                        </td>
                                        <td><span class="badge bg-danger">{{ $debt->days_overdue }}d</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Sin deudas vencidas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Próximas cuotas por vencer --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-calendar-event text-warning me-1"></i> Cuotas próximas a vencer</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Deudor</th>
                                    <th>Cuota</th>
                                    <th>Vence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingInstallments as $inst)
                                    <tr>
                                        <td>{{ Str::limit($inst->debt->debtor->full_name, 25) }}</td>
                                        <td>S/ {{ number_format($inst->total_amount, 2) }}</td>
                                        <td>{{ $inst->due_date->format('d/m') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Sin cuotas próximas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Últimos pagos --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-credit-card text-success me-1"></i> Últimos pagos</h6>
                    <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-primary">Ver todo</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Deudor</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td>{{ Str::limit($payment->debtor->full_name, 20) }}</td>
                                        <td class="text-success fw-bold">S/ {{ number_format($payment->amount, 2) }}</td>
                                        <td><span class="badge bg-secondary">{{ $payment->paymentMethod->name }}</span>
                                        </td>
                                        <td>{{ $payment->payment_date->format('d/m') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Sin pagos recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Promesas de pago --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-hand-index text-primary me-1"></i> Promesas de pago</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Deudor</th>
                                    <th>Promesa</th>
                                    <th>Fecha</th>
                                    <th>Gestor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPromises as $promise)
                                    <tr>
                                        <td>{{ Str::limit($promise->debt->debtor->full_name, 20) }}</td>
                                        <td>S/ {{ number_format($promise->promise_amount, 2) }}</td>
                                        <td>{{ $promise->promise_date->format('d/m') }}</td>
                                        <td>{{ $promise->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Sin promesas pendientes
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
