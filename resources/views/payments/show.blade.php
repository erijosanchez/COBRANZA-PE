@extends('layouts.app')
@section('title', 'Pago ' . $payment->receipt_number)
@section('page-title', 'Detalle de Pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recibo: <code>{{ $payment->receipt_number }}</code></h6>
                    @php $pColors = ['confirmed' => 'success', 'pending' => 'warning', 'rejected' => 'danger', 'reversed' => 'dark']; @endphp
                    <span
                        class="badge bg-{{ $pColors[$payment->status] ?? 'secondary' }}">{{ ucfirst($payment->status) }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Deudor:</td>
                            <td><a href="{{ route('debtors.show', $payment->debtor) }}">{{ $payment->debtor->full_name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deuda:</td>
                            <td><a href="{{ route('debts.show', $payment->debt) }}">{{ $payment->debt->code }}</a> -
                                {{ $payment->debt->concept }}</td>
                        </tr>
                        @if ($payment->installment)
                            <tr>
                                <td class="text-muted">Cuota:</td>
                                <td>#{{ $payment->installment->number }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Monto:</td>
                            <td class="fw-bold text-success fs-5">S/ {{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fecha:</td>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Método:</td>
                            <td>{{ $payment->paymentMethod->name }}</td>
                        </tr>
                        @if ($payment->reference)
                            <tr>
                                <td class="text-muted">Referencia:</td>
                                <td>{{ $payment->reference }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Registrado por:</td>
                            <td>{{ $payment->registeredBy->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Fecha registro:</td>
                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if ($payment->notes)
                            <tr>
                                <td class="text-muted">Notas:</td>
                                <td>{{ $payment->notes }}</td>
                            </tr>
                        @endif
                    </table>

                    @if ($payment->status === 'confirmed')
                        <div class="d-flex gap-2 mt-3">
                            <form method="POST" action="{{ route('payments.reverse', $payment) }}">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('¿Reversar este pago?')"><i
                                        class="bi bi-arrow-counterclockwise"></i> Reversar</button>
                            </form>
                        </div>
                    @elseif($payment->status === 'pending')
                        <div class="d-flex gap-2 mt-3">
                            <form method="POST" action="{{ route('payments.confirm', $payment) }}">@csrf<button
                                    class="btn btn-success btn-sm"><i class="bi bi-check"></i> Confirmar</button></form>
                            <form method="POST" action="{{ route('payments.reject', $payment) }}">@csrf<button
                                    class="btn btn-danger btn-sm"><i class="bi bi-x"></i> Rechazar</button></form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
