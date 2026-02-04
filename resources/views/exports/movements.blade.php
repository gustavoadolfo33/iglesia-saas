<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Referencia</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($movements as $m)
            <tr>
                <td>{{ $m->date->format('Y-m-d') }}</td>
                <td>{{ $m->type === 'income' ? 'Ingreso' : 'Egreso' }}</td>
                <td>{{ optional($m->category)->name }}</td>
                <td>{{ $m->description }}</td>
                <td>{{ $m->reference }}</td>
                <td>{{ number_format($m->amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"><strong>Total Ingresos</strong></td>
            <td>{{ number_format($totalIncome, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5"><strong>Total Egresos</strong></td>
            <td>{{ number_format($totalExpense, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5"><strong>Saldo</strong></td>
            <td>{{ number_format($balance, 2) }}</td>
        </tr>
    </tfoot>
</table>