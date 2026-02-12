@extends('layouts.app')
@section('title', 'Enviar Notificación')
@section('page-title', 'Enviar Notificación')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Enviar a: <strong>{{ $debtor->full_name }}</strong></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('notifications.send') }}">
                        @csrf
                        <input type="hidden" name="debtor_id" value="{{ $debtor->id }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Canal *</label>
                                <select name="channel" class="form-select" required>
                                    <option value="whatsapp">WhatsApp{{ $debtor->phone ? ' (' . $debtor->phone . ')' : '' }}
                                    </option>
                                    <option value="email">Email{{ $debtor->email ? ' (' . $debtor->email . ')' : '' }}</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Deuda relacionada</label>
                                <select name="debt_id" class="form-select">
                                    <option value="">General</option>
                                    @foreach ($debts as $d)
                                        <option value="{{ $d->id }}">{{ $d->code }} -
                                            S/{{ number_format($d->pending_amount, 2) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plantilla</label>
                                <select name="template_id" class="form-select" id="templateSelect">
                                    <option value="">Mensaje personalizado</option>
                                    @foreach ($templates as $t)
                                        <option value="{{ $t->id }}" data-body="{{ $t->body }}">
                                            {{ $t->name }} ({{ $t->channel }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Mensaje *</label>
                                <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" id="messageBox"
                                    required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Variables: {nombre}, {monto}, {fecha_vencimiento},
                                    {empresa}</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('debtors.show', $debtor) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('templateSelect').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.dataset.body) {
                document.getElementById('messageBox').value = opt.dataset.body;
            }
        });
    </script>
@endpush
