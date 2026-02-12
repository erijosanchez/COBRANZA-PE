<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-danger {
            color: #dc3545;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $company->business_name }}</h2>
        <p>RUC: {{ $company->ruc }} | {{ $company->address }}</p>
        <h3>{{ $title }}</h3>
        <p>Generado: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Deudor</th>
                <th>Documento</th>
                <th>Concepto</th>
                <th class="text-right">Pendiente</th>
                <th>Días mora</th>
                <th>Vencimiento</th>
                <th>Gestor</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPending = 0; @endphp
            @foreach ($debts as $debt)
                @php $totalPending += $debt->pending_amount; @endphp
                <tr>
                    <td>{{ $debt->code }}</td>
                    <td>{{ $debt->debtor->full_name }}</td>
                    <td>{{ $debt->debtor->document_number }}</td>
                    <td>{{ $debt->concept }}</td>
                    <td class="text-right text-danger">S/ {{ number_format($debt->pending_amount, 2) }}</td>
                    <td>{{ $debt->days_overdue }}</td>
                    <td>{{ $debt->due_date->format('d/m/Y') }}</td>
                    <td>{{ $debt->assignedUser?->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">TOTAL</th>
                <th class="text-right text-danger">S/ {{ number_format($totalPending, 2) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        CobranzaPE - Sistema de Gestión de Cobranzas | {{ $generated_at }}
    </div>
</body>

</html>
