@extends('layouts.app')
@section('title', 'Nuevo Método de Pago')
@section('page-title', 'Nuevo Método de Pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.payment-methods.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}"
                                maxlength="20">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('settings.payment-methods.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
