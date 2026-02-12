@extends('layouts.app')
@section('title', 'Editar Deudor')
@section('page-title', 'Editar Deudor')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('debtors.update', $debtor) }}">
                        @csrf
                        @method('PUT')
                        @include('debtors._form', ['debtor' => $debtor])
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    id="is_active" {{ old('is_active', $debtor->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Activo</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('debtors.show', $debtor) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
