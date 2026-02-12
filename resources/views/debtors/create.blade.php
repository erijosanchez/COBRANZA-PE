@extends('layouts.app')
@section('title', 'Nuevo Deudor')
@section('page-title', 'Nuevo Deudor')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('debtors.store') }}">
                        @csrf
                        @include('debtors._form')
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('debtors.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
