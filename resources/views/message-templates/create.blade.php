@extends('layouts.app')
@section('title', 'Nueva Plantilla')
@section('page-title', 'Nueva Plantilla de Mensaje')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('message-templates.store') }}">
                        @csrf
                        @include('message-templates._form')
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('message-templates.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
