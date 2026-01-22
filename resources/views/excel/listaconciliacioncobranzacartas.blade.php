<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
    <table>
        <thead>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <th>COD_DOC_COBRO</th>
                <th>FEC_OPERACION</th>
                <th>TXT_GLOSA</th>
                <th>TOTAL_P</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lista_detalle_cartas as $item)
                <tr>
                    <td>{{ $item->COD_DOC_COBRO }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->FEC_OPERACION)) }}</td>
                    <td>{{ $item->TXT_GLOSA }}</td>
                    <td align="right">{{ number_format($item->TOTAL_P, 2) }}</td>
                    <td align="right">{{ number_format($item->TOTAL_P, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>