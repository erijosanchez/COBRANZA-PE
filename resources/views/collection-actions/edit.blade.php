@extends('layouts.app')
@section('title', 'Editar Gestión')
@section('page-title', 'Editar Gestión de Cobranza')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('collection-actions.update', $collectionAction) }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo *</label>
                                <select name="type" class="form-select" required>
                                    @foreach (['phone_call' => 'Llamada', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'visit' => 'Visita', 'letter' => 'Carta', 'legal_notice' => 'Legal', 'promise_to_pay' => 'Promesa', 'other' => 'Otro'] as $k => $v)
                                        <option value="{{ $k }}"
                                            {{ $collectionAction->type == $k ? 'selected' : '' }}>{{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Resultado *</label>
                                <select name="result" class="form-select" required>
                                    @foreach (['contacted' => 'Contactado', 'no_answer' => 'No contesta', 'promise_to_pay' => 'Promesa', 'refused' => 'Se rehúsa', 'wrong_number' => 'Nro equivocado', 'scheduled' => 'Reprogramado', 'other' => 'Otro'] as $k => $v)
                                        <option value="{{ $k }}"
                                            {{ $collectionAction->result == $k ? 'selected' : '' }}>{{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Fecha *</label>
                                <input type="date" name="action_date" class="form-control"
                                    value="{{ $collectionAction->action_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora</label>
                                <input type="time" name="action_time" class="form-control"
                                    value="{{ $collectionAction->action_time }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha promesa</label>
                                <input type="date" name="promise_date" class="form-control"
                                    value="{{ $collectionAction->promise_date?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monto prometido</label>
                                <input type="number" name="promise_amount" class="form-control"
                                    value="{{ $collectionAction->promise_amount }}" step="0.01">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" class="form-control" rows="3">{{ $collectionAction->notes }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('collection-actions.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
