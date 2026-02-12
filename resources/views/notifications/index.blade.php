@extends('layouts.app')
@section('title', 'Notificaciones')
@section('page-title', 'Registro de Notificaciones')

@section('content')
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="channel" class="form-select form-select-sm">
                        <option value="">Todos los canales</option>
                        <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviado</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
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
                        <th>Deudor</th>
                        <th>Canal</th>
                        <th>Destinatario</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->debtor->full_name }}</td>
                            <td>
                                @php $chColors = ['whatsapp'=>'success','email'=>'primary','sms'=>'info']; @endphp
                                <span
                                    class="badge bg-{{ $chColors[$log->channel] ?? 'secondary' }}">{{ ucfirst($log->channel) }}</span>
                            </td>
                            <td>{{ $log->recipient }}</td>
                            <td>{{ Str::limit($log->message, 50) }}</td>
                            <td>
                                <span
                                    class="badge bg-{{ $log->status == 'sent' ? 'success' : ($log->status == 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($log->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Sin notificaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
