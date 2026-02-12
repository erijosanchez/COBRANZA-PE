@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('page-title', 'Crear Usuario')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        @include('users._form')
                        <div class="mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
