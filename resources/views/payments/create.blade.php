@extends('layouts.app')
@section('title', 'Nuevo Pago')
@section('page-title', 'Registrar Pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if ($selectedDebt)
                        <div class="alert alert-info">
                            <strong>Deuda:</strong> {{ $selectedDebt->code }} - {{ $selectedDebt->debtor->full_name }}
                            | <strong>Pendiente:</strong> S/ {{ number_format($selectedDebt->pending_amount, 2) }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payments.store') }}">
                        @csrf
                        <div class="row g-3">
                            @if ($selectedDebt)
                                <input type="hidden" name="debt_id" value="{{ $selectedDebt->id }}">
                            @else
                                <div class="col-md-12">
                                    <label class="form-label">Deuda *</label>
                                    <select name="debt_id" class="form-select @error('debt_id') is-invalid @enderror"
                                        required>
                                        <option value="">Seleccionar deuda...</option>
                                        @foreach ($debtors as $debtor)
                                            @foreach ($debtor->debts->whereIn('status', ['active', 'partial', 'overdue']) as $debt)
                                                <option value="{{ $debt->id }}">{{ $debt->code }} -
                                                    {{ $debtor->full_name }} (Pend:
                                                    S/{{ number_format($debt->pending_amount, 2) }})</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    @error('debt_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            @if ($installments->count() > 0)
                                <div class="col-md-12">
                                    <label class="form-label">Cuota (opcional)</label>
                                    <select name="installment_id" class="form-select">
                                        <option value="">Pago general (sin cuota específica)</option>
                                        @foreach ($installments as $inst)
                                            <option value="{{ $inst->id }}">Cuota #{{ $inst->number }} - Vence:
                                                {{ $inst->due_date->format('d/m/Y') }} -
                                                S/{{ number_format($inst->remaining, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <label class="form-label">Monto *</label>
                                <input type="number" name="amount"
                                    class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                                    step="0.01" min="0.01" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de pago *</label>
                                <input type="date" name="payment_date"
                                    class="form-control @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Método de pago *</label>
                                <select name="payment_method_id"
                                    class="form-select @error('payment_method_id') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($paymentMethods as $m)
                                        <option value="{{ $m->id }}"
                                            {{ old('payment_method_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referencia / Nro. operación</label>
                                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success"><i class="bi bi-cash"></i> Registrar
                                Pago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
