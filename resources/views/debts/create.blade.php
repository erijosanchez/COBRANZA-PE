@extends('layouts.app')
@section('title', 'Nueva Deuda')
@section('page-title', 'Registrar Nueva Deuda')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('debts.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Deudor *</label>
                                <select name="debtor_id" class="form-select @error('debtor_id') is-invalid @enderror"
                                    required>
                                    <option value="">Seleccionar deudor...</option>
                                    @foreach ($debtors as $debtor)
                                        <option value="{{ $debtor->id }}"
                                            {{ old('debtor_id', $selectedDebtor?->id) == $debtor->id ? 'selected' : '' }}>
                                            {{ $debtor->document_number }} - {{ $debtor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('debtor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Concepto *</label>
                                <input type="text" name="concept"
                                    class="form-control @error('concept') is-invalid @enderror" value="{{ old('concept') }}"
                                    required>
                                @error('concept')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Moneda *</label>
                                <select name="currency" class="form-select">
                                    <option value="PEN" {{ old('currency') == 'PEN' ? 'selected' : '' }}>Soles (PEN)
                                    </option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>Dólares (USD)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monto original *</label>
                                <input type="number" name="original_amount"
                                    class="form-control @error('original_amount') is-invalid @enderror"
                                    value="{{ old('original_amount') }}" step="0.01" min="0.01" required>
                                @error('original_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nro. de cuotas *</label>
                                <input type="number" name="installments_count"
                                    class="form-control @error('installments_count') is-invalid @enderror"
                                    value="{{ old('installments_count', 1) }}" min="1" max="120" required>
                                @error('installments_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de emisión *</label>
                                <input type="date" name="issue_date"
                                    class="form-control @error('issue_date') is-invalid @enderror"
                                    value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de interés *</label>
                                <select name="interest_type"
                                    class="form-select @error('interest_type') is-invalid @enderror" id="interestType">
                                    <option value="none" {{ old('interest_type') == 'none' ? 'selected' : '' }}>Sin
                                        interés</option>
                                    <option value="fixed" {{ old('interest_type') == 'fixed' ? 'selected' : '' }}>Fijo (%)
                                    </option>
                                    <option value="daily" {{ old('interest_type') == 'daily' ? 'selected' : '' }}>Diario
                                        (%)</option>
                                    <option value="monthly" {{ old('interest_type') == 'monthly' ? 'selected' : '' }}>
                                        Mensual (%)</option>
                                </select>
                                @error('interest_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="interestRateDiv">
                                <label class="form-label">Tasa de interés (%)</label>
                                <input type="number" name="interest_rate" class="form-control"
                                    value="{{ old('interest_rate', 0) }}" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gestor asignado</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Sin asignar</option>
                                    @foreach ($gestores as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('assigned_to') == $g->id ? 'selected' : '' }}>{{ $g->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('debts.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar
                                Deuda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('interestType').addEventListener('change', function() {
            document.getElementById('interestRateDiv').style.display = this.value === 'none' ? 'none' : 'block';
        });
        document.getElementById('interestType').dispatchEvent(new Event('change'));
    </script>
@endpush
