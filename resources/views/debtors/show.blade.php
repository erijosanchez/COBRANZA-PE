@extends('layouts.app')
@section('title', $debtor->full_name)
@section('page-title', 'Detalle del Deudor')

@section('content')
    <div class="row g-3">
        {{-- Info principal --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Información</h6>
                    @can('debtors.edit')
                        <a href="{{ route('debtors.edit', $debtor) }}" class="btn btn-sm btn-outline-primary"><i
                                class="bi bi-pencil"></i></a>
                    @endcan
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $debtor->full_name }}</strong></p>
                    <p class="mb-1 text-muted">{{ $debtor->document_type }}: {{ $debtor->document_number }}</p>
                    @if ($debtor->email)
                        <p class="mb-1"><i class="bi bi-envelope me-1"></i> {{ $debtor->email }}</p>
                    @endif
                    @if ($debtor->phone)
                        <p class="mb-1"><i class="bi bi-phone me-1"></i> {{ $debtor->phone }}</p>
                    @endif
                    @if ($debtor->phone_secondary)
                        <p class="mb-1"><i class="bi bi-phone me-1"></i> {{ $debtor->phone_secondary }}</p>
                    @endif
                    @if ($debtor->address)
                        <p class="mb-1"><i class="bi bi-geo-alt me-1"></i> {{ $debtor->address }}</p>
                    @endif
                    @if ($debtor->district)
                        <p class="mb-1 text-muted small">{{ $debtor->district }}, {{ $debtor->province }},
                            {{ $debtor->department }}</p>
                    @endif
                    @if ($debtor->reference)
                        <p class="mb-1 small"><em>Ref: {{ $debtor->reference }}</em></p>
                    @endif
                    @if ($debtor->notes)
                        <p class="mb-0 small text-muted mt-2">{{ $debtor->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Resumen</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Deudas activas:</span>
                        <span
                            class="fw-bold">{{ $debtor->debts->whereIn('status', ['active', 'partial', 'overdue'])->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total pendiente:</span>
                        <span class="fw-bold text-danger">S/
                            {{ number_format($debtor->debts->whereIn('status', ['active', 'partial', 'overdue'])->sum('pending_amount'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total pagado:</span>
                        <span class="fw-bold text-success">S/
                            {{ number_format($debtor->debts->sum('paid_amount'), 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-grid gap-2">
                <a href="{{ route('debts.create', ['debtor_id' => $debtor->id]) }}" class="btn btn-primary btn-sm"><i
                        class="bi bi-plus"></i> Nueva Deuda</a>
                <a href="{{ route('notifications.send-form', $debtor) }}" class="btn btn-outline-success btn-sm"><i
                        class="bi bi-bell"></i> Enviar Notificación</a>
            </div>
        </div>

        {{-- Deudas --}}
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Deudas</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Concepto</th>
                                <th>Total</th>
                                <th>Pendiente</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debtor->debts as $debt)
                                <tr>
                                    <td><code>{{ $debt->code }}</code></td>
                                    <td>{{ Str::limit($debt->concept, 30) }}</td>
                                    <td>S/ {{ number_format($debt->total_amount, 2) }}</td>
                                    <td class="fw-bold text-danger">S/ {{ number_format($debt->pending_amount, 2) }}</td>
                                    <td>
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
                                        <span
                                            class="badge bg-{{ $colors[$debt->status] ?? 'secondary' }}">{{ ucfirst($debt->status) }}</span>
                                    </td>
                                    <td><a href="{{ route('debts.show', $debt) }}"
                                            class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Sin deudas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Últimos pagos --}}
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Últimos pagos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debtor->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td class="text-success fw-bold">S/ {{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->paymentMethod->name }}</td>
                                    <td><code>{{ $payment->receipt_number }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin pagos registrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Últimas gestiones --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Últimas gestiones</h6>
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
                            @forelse($debtor->collectionActions as $action)
                                <tr>
                                    <td>{{ $action->action_date->format('d/m/Y') }}</td>
                                    <td>{{ $action->type_label }}</td>
                                    <td>{{ $action->result_label }}</td>
                                    <td>{{ $action->user->name }}</td>
                                    <td>{{ Str::limit($action->notes, 30) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Sin gestiones registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
