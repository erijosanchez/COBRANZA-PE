@extends('layouts.app')
@section('title', 'Editar Método de Pago')
@section('page-title', 'Editar Método de Pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.payment-methods.update', $paymentMethod) }}">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $paymentMethod->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="code" class="form-control"
                                value="{{ old('code', $paymentMethod->code) }}" maxlength="20">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    {{ $paymentMethod->is_active ? 'checked' : '' }}>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('settings.payment-methods.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
