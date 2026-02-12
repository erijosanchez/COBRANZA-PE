@extends('layouts.app')
@section('title', 'Nueva Gestión')
@section('page-title', 'Registrar Gestión de Cobranza')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('collection-actions.store') }}">
                        @csrf
                        <div class="row g-3">
                            @if ($selectedDebt)
                                <input type="hidden" name="debt_id" value="{{ $selectedDebt->id }}">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <strong>Deuda:</strong> {{ $selectedDebt->code }} -
                                        {{ $selectedDebt->debtor->full_name }}
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12">
                                    <label class="form-label">Deuda *</label>
                                    <select name="debt_id" class="form-select @error('debt_id') is-invalid @enderror"
                                        required>
                                        <option value="">Seleccionar...</option>
                                        @foreach ($debts as $debt)
                                            <option value="{{ $debt->id }}">{{ $debt->code }} -
                                                {{ $debt->debtor->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('debt_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-md-4">
                                <label class="form-label">Tipo de gestión *</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach (['phone_call' => 'Llamada telefónica', 'whatsapp' => 'WhatsApp', 'email' => 'Correo electrónico', 'visit' => 'Visita presencial', 'letter' => 'Carta', 'legal_notice' => 'Notificación legal', 'promise_to_pay' => 'Promesa de pago', 'other' => 'Otro'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('type') == $k ? 'selected' : '' }}>
                                            {{ $v }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Resultado *</label>
                                <select name="result" class="form-select @error('result') is-invalid @enderror"
                                    id="resultSelect" required>
                                    @foreach (['contacted' => 'Contactado', 'no_answer' => 'No contesta', 'promise_to_pay' => 'Promesa de pago', 'refused' => 'Se rehúsa', 'wrong_number' => 'Número equivocado', 'scheduled' => 'Reprogramado', 'other' => 'Otro'] as $k => $v)
                                        <option value="{{ $k }}" {{ old('result') == $k ? 'selected' : '' }}>
                                            {{ $v }}</option>
                                    @endforeach
                                </select>
                                @error('result')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="action_date"
                                    class="form-control @error('action_date') is-invalid @enderror"
                                    value="{{ old('action_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora</label>
                                <input type="time" name="action_time" class="form-control"
                                    value="{{ old('action_time', date('H:i')) }}">
                            </div>

                            <div class="col-md-4" id="promiseDateDiv" style="display:none;">
                                <label class="form-label">Fecha promesa de pago</label>
                                <input type="date" name="promise_date" class="form-control"
                                    value="{{ old('promise_date') }}">
                            </div>
                            <div class="col-md-4" id="promiseAmountDiv" style="display:none;">
                                <label class="form-label">Monto prometido</label>
                                <input type="number" name="promise_amount" class="form-control"
                                    value="{{ old('promise_amount') }}" step="0.01">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Notas / Observaciones</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('collection-actions.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('resultSelect').addEventListener('change', function() {
            const show = this.value === 'promise_to_pay';
            document.getElementById('promiseDateDiv').style.display = show ? 'block' : 'none';
            document.getElementById('promiseAmountDiv').style.display = show ? 'block' : 'none';
        });
        document.getElementById('resultSelect').dispatchEvent(new Event('change'));
    </script>
@endpush
