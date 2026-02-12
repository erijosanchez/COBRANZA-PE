@extends('layouts.app')
@section('title', 'Editar Deuda')
@section('page-title', 'Editar Deuda: ' . $debt->code)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('debts.update', $debt) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Concepto *</label>
                                <input type="text" name="concept" class="form-control"
                                    value="{{ old('concept', $debt->concept) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de interés</label>
                                <select name="interest_type" class="form-select">
                                    @foreach (['none' => 'Sin interés', 'fixed' => 'Fijo (%)', 'daily' => 'Diario (%)', 'monthly' => 'Mensual (%)'] as $k => $v)
                                        <option value="{{ $k }}"
                                            {{ old('interest_type', $debt->interest_type) == $k ? 'selected' : '' }}>
                                            {{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tasa (%)</label>
                                <input type="number" name="interest_rate" class="form-control"
                                    value="{{ old('interest_rate', $debt->interest_rate) }}" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gestor asignado</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Sin asignar</option>
                                    @foreach ($gestores as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('assigned_to', $debt->assigned_to) == $g->id ? 'selected' : '' }}>
                                            {{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Descripción</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $debt->description) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $debt->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('debts.show', $debt) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
