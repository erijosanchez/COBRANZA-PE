@extends('layouts.app')
@section('title', 'Deuda ' . $debt->code)
@section('page-title', 'Detalle de Deuda')

@section('content')
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

    <div class="row g-3">
        {{-- Info --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><code>{{ $debt->code }}</code></h6>
                    <span class="badge bg-{{ $colors[$debt->status] ?? 'secondary' }}">{{ ucfirst($debt->status) }}</span>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Deudor:</strong> <a
                            href="{{ route('debtors.show', $debt->debtor) }}">{{ $debt->debtor->full_name }}</a></p>
                    <p class="mb-1"><strong>Concepto:</strong> {{ $debt->concept }}</p>
                    <p class="mb-1"><strong>Emisión:</strong> {{ $debt->issue_date->format('d/m/Y') }}</p>
                    <p class="mb-1"><strong>Vencimiento:</strong> {{ $debt->due_date->format('d/m/Y') }}</p>
                    <p class="mb-1"><strong>Interés:</strong>
                        {{ $debt->interest_type === 'none' ? 'Sin interés' : $debt->interest_rate . '% ' . $debt->interest_type }}
                    </p>
                    <p class="mb-1"><strong>Gestor:</strong> {{ $debt->assignedUser?->name ?? 'Sin asignar' }}</p>
                    @if ($debt->days_overdue > 0)
                        <p class="mb-1 text-danger"><strong>Días mora:</strong> {{ $debt->days_overdue }}</p>
                    @endif
                    @if ($debt->notes)
                        <p class="mb-0 text-muted small mt-2">{{ $debt->notes }}</p>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Monto original:</span>
                        <span>S/ {{ number_format($debt->original_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Monto total:</span>
                        <span class="fw-bold">S/ {{ number_format($debt->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Pagado:</span>
                        <span class="text-success">S/ {{ number_format($debt->paid_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pendiente:</span>
                        <span class="fw-bold text-danger">S/ {{ number_format($debt->pending_amount, 2) }}</span>
                    </div>

                    {{-- Progress bar --}}
                    @php $pct = $debt->total_amount > 0 ? ($debt->paid_amount / $debt->total_amount) * 100 : 0; @endphp
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                    </div>
                    <small class="text-muted">{{ number_format($pct, 1) }}% pagado</small>
                </div>
            </div>

            <div class="mt-3 d-grid gap-2">
                @if (!in_array($debt->status, ['paid', 'cancelled']))
                    <a href="{{ route('payments.create', ['debt_id' => $debt->id]) }}" class="btn btn-success btn-sm"><i
                            class="bi bi-cash"></i> Registrar Pago</a>
                    <a href="{{ route('collection-actions.create', ['debt_id' => $debt->id]) }}"
                        class="btn btn-primary btn-sm"><i class="bi bi-telephone"></i> Registrar Gestión</a>
                    @can('debts.edit')
                        <a href="{{ route('debts.edit', $debt) }}" class="btn btn-outline-primary btn-sm"><i
                                class="bi bi-pencil"></i> Editar</a>
                        <form method="POST" action="{{ route('debts.recalculate', $debt) }}" class="d-grid">
                            @csrf
                            <button class="btn btn-outline-warning btn-sm"><i class="bi bi-calculator"></i> Recalcular</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>

        <div class="col-md-8">
            {{-- Cuotas --}}
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Cuotas ({{ $debt->installments->count() }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vencimiento</th>
                                <th>Monto</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($debt->installments as $inst)
                                @php
                                    $instColors = [
                                        'pending' => 'secondary',
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'overdue' => 'danger',
                                        'cancelled' => 'dark',
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $inst->number }}</td>
                                    <td>{{ $inst->due_date->format('d/m/Y') }}</td>
                                    <td>S/ {{ number_format($inst->total_amount, 2) }}</td>
                                    <td class="text-success">S/ {{ number_format($inst->paid_amount, 2) }}</td>
                                    <td class="fw-bold">S/ {{ number_format($inst->remaining, 2) }}</td>
                                    <td><span
                                            class="badge bg-{{ $instColors[$inst->status] ?? 'secondary' }}">{{ ucfirst($inst->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagos --}}
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Pagos registrados</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Recibo</th>
                                <th>Registrado por</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debt->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td class="text-success fw-bold">S/ {{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->paymentMethod->name }}</td>
                                    <td><code>{{ $payment->receipt_number }}</code></td>
                                    <td>{{ $payment->registeredBy->name }}</td>
                                    <td><span
                                            class="badge bg-{{ $payment->status == 'confirmed' ? 'success' : 'warning' }}">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Sin pagos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Gestiones --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Gestiones de cobranza</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Resultado</th>
                                <th>Gestor</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debt->collectionActions as $action)
                                <tr>
                                    <td>{{ $action->action_date->format('d/m/Y') }}</td>
                                    <td>{{ $action->type_label }}</td>
                                    <td>{{ $action->result_label }}</td>
                                    <td>{{ $action->user->name }}</td>
                                    <td>{{ Str::limit($action->notes, 40) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Sin gestiones</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
