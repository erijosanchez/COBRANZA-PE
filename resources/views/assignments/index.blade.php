@extends('layouts.app')
@section('title', 'Asignaciones')
@section('page-title', 'Asignaciones de Cobranza')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        @can('assignments.create')
            <a href="{{ route('assignments.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva
                Asignación</a>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Deudor</th>
                        <th>Gestor</th>
                        <th>Asignado por</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td><a href="{{ route('debtors.show', $a->debtor) }}"
                                    class="text-decoration-none">{{ $a->debtor->full_name }}</a></td>
                            <td>{{ $a->user->name }}</td>
                            <td>{{ $a->assignedByUser->name }}</td>
                            <td>{{ $a->assigned_date->format('d/m/Y') }}</td>
                            <td><span
                                    class="badge bg-{{ $a->is_active ? 'success' : 'secondary' }}">{{ $a->is_active ? 'Activa' : 'Inactiva' }}</span>
                            </td>
                            <td>
                                @if ($a->is_active)
                                    <form method="POST" action="{{ route('assignments.deactivate', $a) }}"
                                        class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Desactivar?')"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Sin asignaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($assignments->hasPages())
            <div class="card-footer">{{ $assignments->links() }}</div>
        @endif
    </div>
@endsection
