@extends('layouts.app')
@section('title', 'Nueva Asignación')
@section('page-title', 'Nueva Asignación')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('assignments.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Deudor *</label>
                            <select name="debtor_id" class="form-select @error('debtor_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($debtors as $d)
                                    <option value="{{ $d->id }}" {{ old('debtor_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->document_number }} - {{ $d->full_name }}</option>
                                @endforeach
                            </select>
                            @error('debtor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gestor *</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($gestores as $g)
                                    <option value="{{ $g->id }}" {{ old('user_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha *</label>
                            <input type="date" name="assigned_date" class="form-control"
                                value="{{ old('assigned_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('assignments.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
