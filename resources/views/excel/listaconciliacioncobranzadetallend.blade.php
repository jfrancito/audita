<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
    <table>
        <thead>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <th>RV</th>
                <th>COD_DOCUMENTO_CTBLE</th>
                <th>NRO_SERIE</th>
                <th>NRO_DOC</th>
                <th>TXT_EMPR_EMISOR</th>
                <th>TXT_EMPR_RECEPTOR</th>
                <th>TXT_CATEGORIA_TIPO_DOC</th>
                <th>TXT_CATEGORIA_MONEDA</th>
                <th>FEC_EMISION</th>
                <th>IND_MATERIAL_SERVICIO</th>
                <th>TXT_CATEGORIA_ESTADO_DOC_CTBLE</th>
                <th>TXT_CATEGORIA_TIPO_PAGO</th>
                <th>TXT_GLOSA</th>
                <th>COD_USUARIO_CREA_AUD</th>
                <th>COD_ESTADO</th>
                <th>COD_PERIODO</th>
                <th>COD_CONTRATO</th>
                <th>TXT_REFERENCIA</th>
                <th>CAN_IMPORTE</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lista_detalle_nd as $item)
                <tr>
                    <td>{{ $mapping_rv[$item->COD_CONTRATO] ?? '' }}</td>
                    <td>{{ $item->COD_DOCUMENTO_CTBLE }}</td>
                    <td>{{ $item->NRO_SERIE }}</td>
                    <td>{{ $item->NRO_DOC }}</td>
                    <td>{{ $item->TXT_EMPR_EMISOR }}</td>
                    <td>{{ $item->TXT_EMPR_RECEPTOR }}</td>
                    <td>{{ $item->TXT_CATEGORIA_TIPO_DOC }}</td>
                    <td>{{ $item->TXT_CATEGORIA_MONEDA }}</td>
                    <td>{{ date('d/m/Y', strtotime($item->FEC_EMISION)) }}</td>
                    <td>{{ $item->IND_MATERIAL_SERVICIO }}</td>
                    <td>{{ $item->TXT_CATEGORIA_ESTADO_DOC_CTBLE }}</td>
                    <td>{{ $item->TXT_CATEGORIA_TIPO_PAGO }}</td>
                    <td>{{ $item->TXT_GLOSA }}</td>
                    <td>{{ $item->COD_USUARIO_CREA_AUD }}</td>
                    <td>{{ $item->COD_ESTADO }}</td>
                    <td>{{ $item->COD_PERIODO }}</td>
                    <td>{{ $item->COD_CONTRATO }}</td>
                    <td>{{ $item->TXT_REFERENCIA }}</td>
                    <td align="right">{{ number_format($item->CAN_IMPORTE, 2) }}</td>
                    <td align="right">{{ number_format($item->CAN_IMPORTE, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>