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

    // 3. Funciones auxiliares de formato y cálculo
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

    // Valor mostrado en SALDO TOTAL DE COBRANZA (Suma según fórmula anterior)
    $getSaldoMostrado = function ($id) use ($data_matrix) {
        return (
            $data_matrix[$id]['SALDO_PREV'] + $data_matrix[$id]['VENTAS'] + $data_matrix[$id]['ND'] + $data_matrix[$id]['PG'] +
            $data_matrix[$id]['COBROS'] + $data_matrix[$id]['MONTO_TRANSICION'] + $data_matrix[$id]['NC'] +
            $data_matrix[$id]['ADL'] + $data_matrix[$id]['EXT'] + $data_matrix[$id]['REG'] + $data_matrix[$id]['CMP']
        );
    };

    // Lógica de color (Fórmula IIF proporcionada)
    $getColorCobranza = function ($id) use ($data_matrix) {
        $izq = ($data_matrix[$id]['VENTAS'] + $data_matrix[$id]['SALDO_PREV'] + $data_matrix[$id]['ND'] + $data_matrix[$id]['PG'])
            - ($data_matrix[$id]['COBROS'] + $data_matrix[$id]['MONTO_TRANSICION'])
            - ($data_matrix[$id]['ADL'] + $data_matrix[$id]['EXT'])
            - $data_matrix[$id]['NC']
            + $data_matrix[$id]['REG'];

        $der = ($data_matrix[$id]['SALDO_ACTUAL'] - $data_matrix[$id]['MONTO_TRANSICION']);

        // Usamos una pequeña tolerancia para errores de redondeo de flotantes si fuera necesario, 
        // pero aquí lo pondremos directo según la lógica solicitada.
        return (round($izq, 2) == round($der, 2)) ? 'bg-cornsilk' : 'bg-lightcoral';
    };

    // Totales horizontales
    $totHorizCobranza = array_sum(array_map($getGrpCobranza, array_keys($columnas_rv)));
    $totHorizOtrasMenos = array_sum(array_map($getGrpOtrasMenos, array_keys($columnas_rv)));
    $totHorizMostrado = array_sum(array_map($getSaldoMostrado, array_keys($columnas_rv)));

    // Color para el total general
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
    $colorTotal = (round($izqT, 2) == round($derT, 2)) ? 'bg-cornsilk' : 'bg-lightcoral';

@endphp

<div class="row">
    <div class="col-xs-12">
        <div class="header-report-container">
            <div class="header-report-left">
                <table class="table-info-header">
                    <tr>
                        <td><strong>Empresa:</strong></td>
                        <td>{{ $nom_empresa }}</td>
                    </tr>
                    <tr>
                        <td><strong>Centro:</strong></td>
                        <td>{{ $nom_centro }}</td>
                    </tr>
                    <tr>
                        <td><strong>Responsable:</strong></td>
                        <td>{{ $nom_jefe_venta }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>{{ $nom_cliente }}</td>
                    </tr>
                </table>
            </div>
            <div class="header-report-center">
                <h3>CONCILIACION COBRANZA</h3>
                <p>Desde {{ $fechainicio }} hasta {{ $fechafin }}</p>
                <div class="text-center">
                    <form action="{{ url('/reporte-conciliacion-cobranza-excel') }}" method="POST"
                        onsubmit="descargasExcel()">
                        {!! csrf_field() !!}
                        <input type="hidden" name="fechainicio" value="{{ $fechainicio }}">
                        <input type="hidden" name="fechafin" value="{{ $fechafin }}">
                        <input type="hidden" name="cod_zona" value="{{ $cod_zona }}">
                        <input type="hidden" name="cod_jefe_venta" value="{{ $cod_jefe_venta }}">
                        <input type="hidden" name="cod_empresa" value="{{ $cod_empresa }}">
                        <input type="hidden" name="cod_centro" value="{{ $cod_centro }}">
                        <input type="hidden" name="cod_cliente" value="{{ $cod_cliente }}">
                        <button type="submit" class="btn btn-space btn-success btn-export-excel">
                            <i class="fa fa-file-excel-o"></i> Exportar a Excel
                        </button>
                    </form>
                </div>
            </div>
            <div class="header-report-right">
                <div class="logo-report">
                    <img src="{{ asset('public/img/indulogo.png') }}" alt="Logo">
                </div>
                <div class="info-user-report">
                    <p><strong>Usuario:</strong> {{ Session::get('usuario')->usuario }}</p>
                    <p>{{ date('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-report-cuadro">
        <thead>
            <tr>
                <th colspan="3" class="text-center bg-gray-light">DESCRIPCIÓN</th>
                @foreach($columnas_rv as $id => $nombre)
                    <th class="text-center rv-column">{{ $nombre }}</th>
                @endforeach
                <th class="text-center total-column success">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <!-- I - SALDO INICIAL -->
            <tr class="row-main-header">
                <td colspan="3">I - SALDO INICIAL DE CUENTAS POR COBRAR</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['SALDO_PREV']) }}</td>
                @endforeach
                <td class="text-right font-bold text-total">{{ $fmtNeg($sumFila('SALDO_PREV')) }}</td>
            </tr>

            <!-- SECCIÓN MAS -->
            <tr class="row-section">
                <td>MAS</td>
                <td colspan="2"></td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr>
                <td></td>
                <td>VENTAS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['VENTAS']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('VENTAS')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>ND</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['ND']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('ND')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>PAGOS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['PG']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('PG')) }}</td>
            </tr>

            <!-- SECCIÓN MENOS -->
            <tr class="row-section">
                <td>MENOS</td>
                <td colspan="2"></td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr class="row-group">
                <td></td>
                <td class="font-bold text-blue">COBRANZA</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right font-bold">{{ $fmtNeg($getGrpCobranza($id)) }}</td>
                @endforeach
                <td class="text-right text-total font-bold">{{ $fmtNeg($totHorizCobranza) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>CAJA & BANCOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['COBROS']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('COBROS')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>CARTAS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['MONTO_TRANSICION']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('MONTO_TRANSICION')) }}</td>
            </tr>
            <tr class="row-group">
                <td></td>
                <td class="font-bold text-blue">NC</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['NC']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('NC')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="italic text-muted">DEVOLUCIONES Y DESCUENTOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right text-muted italic">{{ $fmtNeg($data_matrix[$id]['NC']) }}</td>
                @endforeach
                <td class="text-right text-total text-muted italic">{{ $fmtNeg($sumFila('NC')) }}</td>
            </tr>
            <tr class="row-group">
                <td></td>
                <td class="font-bold text-blue">OTROS</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right font-bold">{{ $fmtNeg($getGrpOtrasMenos($id)) }}</td>
                @endforeach
                <td class="text-right text-total font-bold">{{ $fmtNeg($totHorizOtrasMenos) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>ADELANTOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['ADL']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('ADL')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>EXTORNOS</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['EXT']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('EXT')) }}</td>
            </tr>

            <!-- SECCIÓN OTROS -->
            <tr class="row-section">
                <td>OTROS</td>
                <td colspan="2"></td>
                @foreach($columnas_rv as $id => $nombre) <td></td> @endforeach <td></td>
            </tr>
            <tr>
                <td></td>
                <td>REGULARIZACION</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['REG']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('REG')) }}</td>
            </tr>
            <tr>
                <td></td>
                <td>COMPENSACIONES</td>
                <td></td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right">{{ $fmtNeg($data_matrix[$id]['CMP']) }}</td>
                @endforeach
                <td class="text-right text-total">{{ $fmtNeg($sumFila('CMP')) }}</td>
            </tr>

            <!-- II - SALDO FINAL -->
            <tr class="row-main-header border-double">
                <td colspan="3">II - SALDO FINAL DE CUENTAS POR COBRAR</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right"></td>
                @endforeach
                <td class="text-right text-total"></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="text-blue font-bold">SALDO TOTAL DE COBRANZA</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right font-bold {{ $getColorCobranza($id) }}">
                        {{ $fmtNeg($getSaldoMostrado($id)) }}
                    </td>
                @endforeach
                <td class="text-right font-bold text-total {{ $colorTotal }}">
                    {{ $fmtNeg($totHorizMostrado) }}
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="font-bold text-blue">SALDO TOTAL CTA. CTE.</td>
                @foreach($columnas_rv as $id => $nombre)
                    <td class="text-right font-bold">{{ $fmtNeg($data_matrix[$id]['SALDO_ACTUAL']) }}</td>
                @endforeach
                <td class="text-right font-bold text-total">{{ $fmtNeg($sumFila('SALDO_ACTUAL')) }}</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        document.cookie = name + '=; Max-Age=-99999999; path=/;';
    }

    function descargasExcel() {
        abrircargando();
        var checkDownload = setInterval(function () {
            var cookieValue = getCookie('download_started');
            if (cookieValue == 'true') {
                cerrarcargando();
                eraseCookie('download_started');
                clearInterval(checkDownload);
            }
        }, 500);
    }
</script>

<style>
    .table-report-cuadro {
        font-size: 11px;
        background: #fff;
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .table-report-cuadro thead th {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        border: 1px solid #cbd5e1 !important;
        vertical-align: middle;
        padding: 10px !important;
    }

    .row-main-header {
        background: #e2e8f0 !important;
        font-weight: 800;
    }

    .row-section {
        background: #f1f5f9 !important;
        font-weight: 700;
    }

    .row-group {
        background: #f8fafc;
    }

    .table-report-cuadro td {
        padding: 5px 8px !important;
        border: 1px solid #e2e8f0 !important;
    }

    .bg-cornsilk {
        background-color: #FFF8DC !important;
    }

    /* Cornsilk */
    .bg-lightcoral {
        background-color: #F08080 !important;
        color: #fff !important;
    }

    /* LightCoral */
    .font-bold {
        font-weight: 700;
    }

    .text-blue {
        color: #2563eb;
    }

    .italic {
        font-style: italic;
    }

    .border-double {
        border-top: 3px double #94a3b8 !important;
    }

    .text-total {
        border-left: 2px solid #cbd5e1 !important;
    }
</style>