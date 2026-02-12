@extends('layouts.app')
@section('title', 'Gestiones de Cobranza')
@section('page-title', 'Gestiones de Cobranza')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        @can('collections.create')
            <a href="{{ route('collection-actions.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva
                Gestión</a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Todos los tipos</option>
                        @foreach (['phone_call' => 'Llamada', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'visit' => 'Visita', 'letter' => 'Carta', 'legal_notice' => 'Legal'] as $k => $v)
                            <option value="{{ $k }}" {{ request('type') == $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="result" class="form-select form-select-sm">
                        <option value="">Todos los resultados</option>
                        @foreach (['contacted' => 'Contactado', 'no_answer' => 'No contesta', 'promise_to_pay' => 'Promesa', 'refused' => 'Se rehúsa'] as $k => $v)
                            <option value="{{ $k }}" {{ request('result') == $k ? 'selected' : '' }}>
                                {{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm"
                        value="{{ request('date_to') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    <a href="{{ route('collection-actions.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Deudor</th>
                        <th>Deuda</th>
                        <th>Tipo</th>
                        <th>Resultado</th>
                        <th>Promesa</th>
                        <th>Gestor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actions as $action)
                        <tr>
                            <td>{{ $action->action_date->format('d/m/Y') }}</td>
                            <td>{{ $action->action_time ?? '-' }}</td>
                            <td><a href="{{ route('debtors.show', $action->debt->debtor) }}"
                                    class="text-decoration-none">{{ Str::limit($action->debt->debtor->full_name, 20) }}</a>
                            </td>
                            <td><code>{{ $action->debt->code }}</code></td>
                            <td>{{ $action->type_label }}</td>
                            <td>
                                @php $rColors = ['contacted'=>'success','no_answer'=>'secondary','promise_to_pay'=>'info','refused'=>'danger','wrong_number'=>'dark','scheduled'=>'warning']; @endphp
                                <span
                                    class="badge bg-{{ $rColors[$action->result] ?? 'secondary' }}">{{ $action->result_label }}</span>
                            </td>
                            <td>
                                @if ($action->promise_date)
                                    S/{{ number_format($action->promise_amount, 2) }} el
                                    {{ $action->promise_date->format('d/m') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $action->user->name }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('collections.edit')
                                        <a href="{{ route('collection-actions.edit', $action) }}"
                                            class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('collections.delete')
                                        <form method="POST" action="{{ route('collection-actions.destroy', $action) }}"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No se encontraron gestiones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($actions->hasPages())
            <div class="card-footer">{{ $actions->links() }}</div>
        @endif
    </div>
@endsection
