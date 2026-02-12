@extends('layouts.app')
@section('title', 'Métodos de Pago')
@section('page-title', 'Métodos de Pago')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <a href="{{ route('settings.payment-methods.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i>
            Nuevo Método</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($methods as $m)
                        <tr>
                            <td class="fw-semibold">{{ $m->name }}</td>
                            <td><code>{{ $m->code }}</code></td>
                            <td><span
                                    class="badge bg-{{ $m->is_active ? 'success' : 'secondary' }}">{{ $m->is_active ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('settings.payment-methods.edit', $m) }}"
                                        class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('settings.payment-methods.destroy', $m) }}"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
