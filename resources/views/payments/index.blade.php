@extends('layouts.app')
@section('title', 'Pagos')
@section('page-title', 'Pagos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        @can('payments.create')
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo Pago</a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Buscar recibo, deudor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach (['confirmed' => 'Confirmado', 'pending' => 'Pendiente', 'rejected' => 'Rechazado', 'reversed' => 'Reversado'] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_method_id" class="form-select form-select-sm">
                        <option value="">Todos los métodos</option>
                        @foreach ($paymentMethods as $m)
                            <option value="{{ $m->id }}"
                                {{ request('payment_method_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ request('date_to') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Recibo</th>
                        <th>Fecha</th>
                        <th>Deudor</th>
                        <th>Deuda</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td><code>{{ $payment->receipt_number }}</code></td>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td><a href="{{ route('debtors.show', $payment->debtor) }}"
                                    class="text-decoration-none">{{ Str::limit($payment->debtor->full_name, 20) }}</a></td>
                            <td><a href="{{ route('debts.show', $payment->debt) }}"
                                    class="text-decoration-none"><code>{{ $payment->debt->code }}</code></a></td>
                            <td class="fw-bold text-success">S/ {{ number_format($payment->amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $payment->paymentMethod->name }}</span></td>
                            <td>
                                @php
                                    $pColors = [
                                        'confirmed' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        'reversed' => 'dark',
                                    ];
                                @endphp
                                <span
                                    class="badge bg-{{ $pColors[$payment->status] ?? 'secondary' }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td>{{ $payment->registeredBy->name }}</td>
                            <td>
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary"><i
                                        class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No se encontraron pagos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
@endsection
