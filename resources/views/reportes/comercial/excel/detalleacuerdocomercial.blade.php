<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table>
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ACUERDO DESC.</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">COD. DOC.</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">PERIODO</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">TIPO</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">SERIE</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">NRO. DOC.</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">FECHA</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">EMPRESA</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">SUBTOTAL</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">IMPUESTO</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">TOTAL</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ESTADO</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">GLOSA</th>
            </tr>
        </thead>
        <tbody>
            @foreach(collect($datos)->sortBy('FEC_EMISION') as $reg)
                @php
                    $sin_acuerdo = empty(trim($reg->ACUERDO_DESCUENTO));
                @endphp
                <tr>
                    <td style="border: 1px solid #000; text-align: left; {{ $sin_acuerdo ? 'background-color: #fffde7; color: #f44336;' : '' }}">
                        {{ $reg->ACUERDO_DESCUENTO ?: '--- SIN ACUERDO ---' }}
                    </td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->COD_DOCUMENTO_CTBLE }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->PERIODO }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->TIPO_DOCUMENTO }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->NRO_SERIE }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->NRO_DOC }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ date('d/m/Y', strtotime($reg->FEC_EMISION)) }}</td>
                    <td style="border: 1px solid #000; text-align: left;">{{ $reg->EMPRESA }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ number_format($reg->CAN_SUB_TOTAL, 2) }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ number_format($reg->CAN_IMPUESTO_VTA, 2) }}</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold;">{{ number_format($reg->CAN_TOTAL, 2) }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $reg->TXT_CATEGORIA_ESTADO_DOC_CTBLE }}</td>
                    <td style="border: 1px solid #000; text-align: left;">{{ $reg->TXT_GLOSA }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
