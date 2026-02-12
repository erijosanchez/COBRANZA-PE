@extends('layouts.app')
@section('title', 'Deudores')
@section('page-title', 'Deudores')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        @can('debtors.create')
            <a href="{{ route('debtors.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo Deudor</a>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Buscar por nombre, documento, teléfono..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('debtors.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Deudas activas</th>
                        <th>Total pendiente</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debtors as $debtor)
                        <tr>
                            <td>{{ $debtor->document_type }}: {{ $debtor->document_number }}</td>
                            <td><a href="{{ route('debtors.show', $debtor) }}"
                                    class="text-decoration-none fw-semibold">{{ $debtor->full_name }}</a></td>
                            <td>{{ $debtor->phone ?? '-' }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $debtor->active_debts_count }}</span></td>
                            <td class="fw-bold {{ ($debtor->total_pending ?? 0) > 0 ? 'text-danger' : '' }}">
                                S/ {{ number_format($debtor->total_pending ?? 0, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $debtor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $debtor->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('debtors.show', $debtor) }}" class="btn btn-outline-primary"
                                        title="Ver"><i class="bi bi-eye"></i></a>
                                    @can('debtors.edit')
                                        <a href="{{ route('debtors.edit', $debtor) }}" class="btn btn-outline-secondary"
                                            title="Editar"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No se encontraron deudores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($debtors->hasPages())
            <div class="card-footer">{{ $debtors->links() }}</div>
        @endif
    </div>
@endsection
