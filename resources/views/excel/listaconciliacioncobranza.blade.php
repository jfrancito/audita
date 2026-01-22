@php
    // 1. Identificar todas las columnas dinámicas (RVs)
    $columnas_rv = [];
    foreach ($lista_conciliacion as $item) {
        $key = $item->RV ?? 'SIN_RV';
        if (!isset($columnas_rv[$key])) {
            $columnas_rv[$key] = $item->NOM_VENDEDOR ?? $key;
        }
    }
    asort($columnas_rv);

    // 2. Agrupar montos por RV
    $data_matrix = [];
    foreach ($lista_conciliacion as $item) {
        $key = $item->RV ?? 'SIN_RV';
        if (!isset($data_matrix[$key])) {
            $data_matrix[$key] = [
                'SALDO_PREV' => 0,
                'VENTAS' => 0,
                'ND' => 0,
                'PG' => 0,
                'COBROS' => 0,
                'MONTO_TRANSICION' => 0,
                'NC' => 0,
                'ADL' => 0,
                'EXT' => 0,
                'REG' => 0,
                'CMP' => 0,
                'SALDO_ACTUAL' => 0
            ];
        }
        $data_matrix[$key]['SALDO_PREV'] += $item->SALDO_PREV ?? 0;
        $data_matrix[$key]['VENTAS'] += $item->VENTAS ?? 0;
        $data_matrix[$key]['ND'] += $item->ND ?? 0;
        $data_matrix[$key]['PG'] += $item->PG ?? 0;
        $data_matrix[$key]['COBROS'] += $item->COBROS ?? 0;
        $data_matrix[$key]['MONTO_TRANSICION'] += $item->MONTO_TRANSICION ?? 0;
        $data_matrix[$key]['NC'] += $item->NC ?? 0;
        $data_matrix[$key]['ADL'] += $item->ADL ?? 0;
        $data_matrix[$key]['EXT'] += $item->EXT ?? 0;
        $data_matrix[$key]['REG'] += $item->REG ?? 0;
        $data_matrix[$key]['CMP'] += $item->CMP ?? 0;
        $data_matrix[$key]['SALDO_ACTUAL'] += $item->SALDO_ACTUAL ?? 0;
    }

    // 3. Funciones auxiliares
    $fmtNeg = function ($valor) {
        if ($valor < 0) {
            return '(' . number_format(abs($valor), 2) . ')';
        }
        return number_format($valor, 2);
    };

    $sumFila = function ($campo) use ($data_matrix) {
        return array_sum(array_column($data_matrix, $campo));
    };

    // Cálculos de subgrupos
    $getGrpMas = function ($id) use ($data_matrix) {
        return ($data_matrix[$id]['VENTAS'] + $data_matrix[$id]['ND'] + $data_matrix[$id]['PG']);
    };
    $getGrpCobranza = function ($id) use ($data_matrix) {
        return ($data_matrix[$id]['COBROS'] + $data_matrix[$id]['MONTO_TRANSICION']);
    };
    $getGrpOtrasMenos = function ($id) use ($data_matrix) {
        return ($data_matrix[$id]['ADL'] + $data_matrix[$id]['EXT']);
    };
    $getGrpMenos = function ($id) use ($data_matrix, $getGrpCobranza, $getGrpOtrasMenos) {
        return $getGrpCobranza($id) + $data_matrix[$id]['NC'] + $getGrpOtrasMenos($id);
    };
    $getGrpOtroNivel1 = function ($id) use ($data_matrix) {
        return ($data_matrix[$id]['REG'] + $data_matrix[$id]['CMP']);
    };

    $getSaldoMostrado = function ($id) use ($data_matrix) {
        return (
            $data_matrix[$id]['SALDO_PREV'] + $data_matrix[$id]['VENTAS'] + $data_matrix[$id]['ND'] + $data_matrix[$id]['PG'] +
            $data_matrix[$id]['COBROS'] + $data_matrix[$id]['MONTO_TRANSICION'] + $data_matrix[$id]['NC'] +
            $data_matrix[$id]['ADL'] + $data_matrix[$id]['EXT'] + $data_matrix[$id]['REG'] + $data_matrix[$id]['CMP']
        );
    };

    $getColorCobranza = function ($id) use ($data_matrix) {
        $izq = ($data_matrix[$id]['VENTAS'] + $data_matrix[$id]['SALDO_PREV'] + $data_matrix[$id]['ND'] + $data_matrix[$id]['PG'])
            - ($data_matrix[$id]['COBROS'] + $data_matrix[$id]['MONTO_TRANSICION'])
            - ($data_matrix[$id]['ADL'] + $data_matrix[$id]['EXT'])
            - $data_matrix[$id]['NC']
            + $data_matrix[$id]['REG'];
        $der = ($data_matrix[$id]['SALDO_ACTUAL'] - $data_matrix[$id]['MONTO_TRANSICION']);
        return (round($izq, 2) == round($der, 2)) ? '#FFF8DC' : '#F08080';
    };

    $totHorizCobranza = array_sum(array_map($getGrpCobranza, array_keys($columnas_rv)));
    $totHorizOtrasMenos = array_sum(array_map($getGrpOtrasMenos, array_keys($columnas_rv)));
    $totHorizMostrado = array_sum(array_map($getSaldoMostrado, array_keys($columnas_rv)));

    $totV = $sumFila('VENTAS');
    $totSP = $sumFila('SALDO_PREV');
    $totND = $sumFila('ND');
    $totPG = $sumFila('PG');
    $totC = $sumFila('COBROS');
    $totMT = $sumFila('MONTO_TRANSICION');
    $totAD = $sumFila('ADL');
    $totEX = $sumFila('EXT');
    $totNC = $sumFila('NC');
    $totRE = $sumFila('REG');
    $totSA = $sumFila('SALDO_ACTUAL');

    $izqT = ($totV + $totSP + $totND + $totPG) - ($totC + $totMT) - ($totAD + $totEX) - $totNC + $totRE;
    $derT = ($totSA - $totMT);
    $colorTotal = (round($izqT, 2) == round($derT, 2)) ? '#FFF8DC' : '#F08080';

    $total_columnas = count($columnas_rv) + 4;
@endphp

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>

    <table>
        <tr>
            <td colspan="2"><strong>Empresa:</strong></td>
            <td colspan="{{ $total_columnas - 6 }}">{{ $nom_empresa }}</td>
            <td colspan="4" rowspan="4" align="right">
                {{-- Excel doesn't handle remote assets well in some versions, but we try --}}
                {{-- <img src="{{ public_path('img/indulogo.png') }}" width="120"> --}}
            </td>
        </tr>
        <tr>
            <td colspan="2"><strong>Centro:</strong></td>
            <td colspan="{{ $total_columnas - 6 }}">{{ $nom_centro }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Responsable:</strong></td>
            <td colspan="{{ $total_columnas - 6 }}">{{ $nom_jefe_venta }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Cliente:</strong></td>
            <td colspan="{{ $total_columnas - 6 }}">{{ $nom_cliente }}</td>
        </tr>
        <tr>
            <td colspan="{{ $total_columnas }}" align="center">
                <h2>CONCILIACION COBRANZA</h2>
                <p>Desde {{ $fechainicio }} hasta {{ $fechafin }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="{{ $total_columnas - 2 }}"></td>
            <td colspan="2" align="right">
                <strong>Usuario:</strong> {{ Session::get('usuario')->usuario }}<br>
                {{ date('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th colspan="3" style="background-color: #f1f5f9; text-align: center;">DESCRIPCIÓN</th>
                @foreach($columnas_rv as $id => $nombre)
                    <th style="background-color: #f1f5f9; text-align: center;">{{ $nombre }}</th>
                @endforeach
                <th style="background-color: #dcfce7; text-align: center;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="3">I - SALDO INICIAL DE CUENTAS POR COBRAR</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['SALDO_PREV']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('SALDO_PREV')) }}</td>
            </tr>

            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">MAS</td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr>
                <td></td>
                <td>VENTAS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['VENTAS']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('VENTAS')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>ND</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['ND']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('ND')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>PAGOS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['PG']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('PG')) }}</td>
            </tr>

            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">MENOS</td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td></td>
                <td style="font-weight: bold; color: #2563eb;">COBRANZA</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right" style="font-weight: bold;">{{ $fmtNeg($getGrpCobranza($id)) }}</td>
                @endforeach
                <td align="right" style="font-weight: bold;">{{ $fmtNeg($totHorizCobranza) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>CAJA & BANCOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['COBROS']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('COBROS')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>CARTAS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['MONTO_TRANSICION']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('MONTO_TRANSICION')) }}</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td></td>
                <td style="font-weight: bold; color: #2563eb;">NC</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['NC']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('NC')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="font-style: italic; color: #64748b;">DEVOLUCIONES Y DESCUENTOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right" style="font-style: italic; color: #64748b;">{{ $fmtNeg($data_matrix[$id]['NC']) }}
                    </td>
                @endforeach
                <td align="right" style="font-style: italic; color: #64748b;">{{ $fmtNeg($sumFila('NC')) }}</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td></td>
                <td style="font-weight: bold; color: #2563eb;">OTROS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right" style="font-weight: bold;">{{ $fmtNeg($getGrpOtrasMenos($id)) }}</td>
                @endforeach
                <td align="right" style="font-weight: bold;">{{ $fmtNeg($totHorizOtrasMenos) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>ADELANTOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['ADL']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('ADL')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>EXTORNOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['EXT']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('EXT')) }}</td>
            </tr>

            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">OTROS</td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr>
                <td></td>
                <td>REGULARIZACION</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['REG']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('REG')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>COMPENSACIONES</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right">{{ $fmtNeg($data_matrix[$id]['CMP']) }}</td>
                @endforeach
                <td align="right">{{ $fmtNeg($sumFila('CMP')) }}</td>
            </tr>

            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="3">II - SALDO FINAL DE CUENTAS POR COBRAR</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td></td>
                @endforeach
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="color: #2563eb; font-weight: bold;">SALDO TOTAL DE COBRANZA</td>
                @foreach($columnas_rv as $id => $nombre)
                    @php $bg = $getColorCobranza($id); @endphp
                    <td align="right" style="font-weight: bold; background-color: {{ $bg }};">
                        {{ $fmtNeg($getSaldoMostrado($id)) }}
                    </td>
                @endforeach
                <td align="right" style="font-weight: bold; background-color: {{ $colorTotal }};">
                    {{ $fmtNeg($totHorizMostrado) }}
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="color: #2563eb; font-weight: bold;">SALDO TOTAL CTA. CTE.</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td align="right" style="font-weight: bold;">{{ $fmtNeg($data_matrix[$id]['SALDO_ACTUAL']) }}</td>
                @endforeach
                <td align="right" style="font-weight: bold;">{{ $fmtNeg($sumFila('SALDO_ACTUAL')) }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>