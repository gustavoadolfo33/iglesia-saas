<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte Movimientos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            margin-bottom: 14px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .subtitle {
            margin: 4px 0 0 0;
            color: #444;
        }

        .meta {
            margin-top: 10px;
        }

        .meta td {
            padding: 2px 6px 2px 0;
            vertical-align: top;
        }

        .summary {
            margin: 14px 0;
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .summary .label {
            color: #444;
        }

        .summary .value {
            font-size: 14px;
            font-weight: 700;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data th,
        table.data td {
            border: 1px solid #ddd;
            padding: 7px;
        }

        table.data th {
            background: #f2f2f2;
            text-align: left;
            font-weight: 700;
        }

        .right {
            text-align: right;
        }

        .badge-income {
            color: #0a7a2f;
            font-weight: 700;
        }

        .badge-expense {
            color: #b00020;
            font-weight: 700;
        }

        .footer {
            margin-top: 14px;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        // Iglesia (si viene en la colección)
        $churchName = optional($church)->name;


        // Mes en texto
        $monthNames = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        $monthLabel = $monthNames[$month] ?? (string) $month;
    @endphp

    <div class="header">
        <p class="title">Reporte Mensual de Movimientos</p>
        <p class="subtitle">
            Mes: <strong>{{ $monthLabel }} {{ $year }}</strong>
            @if($churchName)
                — Iglesia: <strong>{{ $churchName }}</strong>
            @endif
        </p>

        <table class="meta">
            <tr>
                <td><strong>Generado:</strong></td>
                <td>{{ now()->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Total Ingresos</div>
                <div class="value">Bs {{ number_format($totalIncome ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="label">Total Egresos</div>
                <div class="value">Bs {{ number_format($totalExpense ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="label">Saldo</div>
                <div class="value">Bs {{ number_format($balance ?? 0, 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 85px;">Fecha</th>
                <th style="width: 70px;">Tipo</th>
                <th style="width: 140px;">Categoría</th>
                <th>Descripción</th>
                <th style="width: 90px;">Referencia</th>
                <th class="right" style="width: 90px;">Monto (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
                <tr>
                    <td>{{ optional($m->date)->format('Y-m-d') }}</td>
                    <td>
                        @if($m->type === 'income')
                            <span class="badge-income">Ingreso</span>
                        @else
                            <span class="badge-expense">Egreso</span>
                        @endif
                    </td>
                    <td>{{ optional($m->category)->name }}</td>
                    <td>{{ $m->description }}</td>
                    <td>{{ $m->reference }}</td>
                    <td class="right">{{ number_format((float) $m->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No existen movimientos para este periodo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte es un documento interno para control y revisión contable.</p>
    </div>

</body>

</html>