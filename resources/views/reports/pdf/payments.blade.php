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

        .text-success {
            color: #198754;
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
        <p>RUC: {{ $company->ruc }}</p>
        <h3>{{ $title }}</h3>
        <p>Generado: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Recibo</th>
                <th>Deudor</th>
                <th>Documento</th>
                <th class="text-right">Monto</th>
                <th>Método</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach ($payments as $p)
                @php $total += $p->amount; @endphp
                <tr>
                    <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $p->receipt_number }}</td>
                    <td>{{ $p->debtor->full_name }}</td>
                    <td>{{ $p->debtor->document_number }}</td>
                    <td class="text-right text-success">S/ {{ number_format($p->amount, 2) }}</td>
                    <td>{{ $p->paymentMethod->name }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">TOTAL RECAUDADO</th>
                <th class="text-right text-success">S/ {{ number_format($total, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        CobranzaPE - Sistema de Gestión de Cobranzas | {{ $generated_at }}
    </div>
</body>

</html>
