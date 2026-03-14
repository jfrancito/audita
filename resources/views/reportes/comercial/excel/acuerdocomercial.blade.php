<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">REPORTE ACUERDO COMERCIAL</th>
            </tr>
            <tr>
                <th colspan="2" style="text-align: center;">Periodo: {{ date('d/m/Y', strtotime($fechainicio)) }} al {{ date('d/m/Y', strtotime($fechafin)) }}</th>
            </tr>
            <tr></tr>
        </thead>
    </table>

    @php
        // Separar información igual que en la vista
        $tab_resumen = collect($lista_acuerdos)->filter(function($item) {
            return !empty(trim($item->ACUERDO_DESCUENTO));
        });
        
        $agrupadoPorEmpresa = $tab_resumen->groupBy(function($item) {
            return trim($item->EMPRESA);
        });
        
        $montoTotalGeneral = $tab_resumen->sum('CAN_TOTAL');
    @endphp

    <!-- CONSOLIDADO GENERAL EN EXCEL -->
    @if(count($agrupadoPorEmpresa) > 0)
    <table>
        <thead>
            <tr>
                <th colspan="2" style="background-color: #1e293b; color: #ffffff; font-weight: bold; border: 1px solid #000; text-align: left;">CONSOLIDADO GENERAL POR EMPRESA</th>
            </tr>
            <tr style="background-color: #f1f5f9;">
                <th style="font-weight: bold; border: 1px solid #000; text-align: left;">Nombre de la Empresa</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: right;">Total Acumulado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agrupadoPorEmpresa as $nomeEmpresa => $datos)
                <tr>
                    <td style="border: 1px solid #000; text-align: left;">{{ $nomeEmpresa }}</td>
                    <td style="border: 1px solid #000; text-align: right;">S/ {{ number_format($datos->sum('CAN_TOTAL'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc;">
                <th style="font-weight: bold; border: 1px solid #000; text-align: left;">TOTAL GENERAL</th>
                <th style="font-weight: bold; border: 1px solid #000; text-align: right;">S/ {{ number_format($montoTotalGeneral, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <table><tr></tr></table>
    @endif

    @foreach($agrupadoPorEmpresa as $empresa => $datosEmpresa)
        <table>
            <thead>
                <tr>
                    <th style="background-color: #d9e9f3; font-weight: bold; border: 1px solid #000; text-align: left;">{{ $empresa }}</th>
                    <th style="background-color: #d9e9f3; font-weight: bold; border: 1px solid #000; text-align: right;">S/ {{ number_format($datosEmpresa->sum('CAN_TOTAL'), 2) }}</th>
                </tr>
                <tr>
                    <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; text-align: left;">Concepto / Periodo</th>
                    <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; text-align: right;">Suma de Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $agrupadoPorPeriodo = $datosEmpresa->groupBy('PERIODO');
                @endphp

                @foreach($agrupadoPorPeriodo as $periodo => $datosPeriodo)
                    <tr>
                        <td style="font-weight: bold; border: 1px solid #000; text-align: left;">PERIODO: {{ $periodo }}</td>
                        <td style="font-weight: bold; border: 1px solid #000; text-align: right;">S/ {{ number_format($datosPeriodo->sum('CAN_TOTAL'), 2) }}</td>
                    </tr>
                    
                    @php
                        $agrupadoPorAcuerdo = $datosPeriodo->groupBy('ACUERDO_DESCUENTO');
                    @endphp

                    @foreach($agrupadoPorAcuerdo as $acuerdo => $datosAcuerdo)
                        <tr>
                            <td style="border: 1px solid #000; text-align: left;">{{ $acuerdo }}</td>
                            <td style="text-align: right; border: 1px solid #000;">S/ {{ number_format($datosAcuerdo->sum('CAN_TOTAL'), 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; text-align: left;">TOTAL EMPRESA</th>
                    <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; text-align: right;">S/ {{ number_format($datosEmpresa->sum('CAN_TOTAL'), 2) }}</th>
                </tr>
            </tfoot>
        </table>
        <table><tr></tr></table> <!-- Espacio entre empresas -->
    @endforeach
</body>
</html>
