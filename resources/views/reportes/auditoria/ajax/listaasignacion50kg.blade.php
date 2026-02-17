@php
    $tabs = [
        ['id' => 'sec', 'label' => 'SEC', 'name' => 'SECADO', 'title' => 'INGRESO DE SECADO INDUSTRIAL (ANEXO 02)', 'data' => $lista_secado],
        ['id' => 'pil', 'label' => 'PIL', 'name' => 'PILADO', 'title' => 'INGRESO DE PILADO (ANEXO 04)', 'data' => $lista_pilado],
        ['id' => 'sel', 'label' => 'SEL', 'name' => 'SELECTORA', 'title' => 'INGRESO DE SELECTORA (ANEXO 05)', 'data' => $lista_selectora],
        ['id' => 'emb', 'label' => 'EMB', 'name' => 'EMBOLSADO', 'title' => 'INGRESO DE EMBOLSADO (ANEXO 06)', 'data' => $lista_embolsado],
        ['id' => 'ane', 'label' => 'AÑE', 'name' => 'AÑEJAMIENTO', 'title' => 'INGRESO DE AÑEJAMIENTO (ANEXO 07)', 'data' => $lista_anejamiento],
        ['id' => 'mes', 'label' => 'MES', 'name' => 'MEZCLA', 'title' => 'INGRESO DE MEZCLA (ANEXO 08)', 'data' => $lista_mezcla],
        ['id' => 'rep', 'label' => 'REP', 'name' => 'REPROCESO', 'title' => 'INGRESO DE REPROCESO (ANEXO 09)', 'data' => $lista_reproceso],
        ['id' => 'com', 'label' => 'COM', 'name' => 'COMPACTADO', 'title' => 'INGRESOS DE COMPACTADOS (ANEXO 10)', 'data' => $lista_compactado],
        ['id' => 'pul', 'label' => 'PUL', 'name' => 'PULIDO', 'title' => 'INGRESOS DE PULIDO (ANEXO 11)', 'data' => $lista_pulido],
    ];

    // Generar rango de meses/años dinámicamente de forma CRONOLÓGICA ESTRICTA
    $periodos = [];
    $meses_full = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
        7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SETIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];

    $yS = (int)date('Y', strtotime($fechainicio));
    $mS = (int)date('n', strtotime($fechainicio));
    $yE = (int)date('Y', strtotime($fechafin));
    $mE = (int)date('n', strtotime($fechafin));

    for ($y = $yS; $y <= $yE; $y++) {
        $m_start = ($y == $yS) ? $mS : 1;
        $m_end = ($y == $yE) ? $mE : 12;
        for ($m = $m_start; $m <= $m_end; $m++) {
            $key = $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $periodos[] = [
                'key' => $key,
                'label' => $meses_full[$m] . ' ' . $y
            ];
        }
    }

    $resumen = [];
    $zonas = [];

    foreach ($tabs as $tab) {
        $servicio = $tab['name'];
        $tab_id = $tab['id'];
        foreach ($tab['data'] as $item) {
            $item_arr = (array) $item;

            // Detectar Zona/Centro
            $zona = (isset($item_arr['CENTRO']) ? $item_arr['CENTRO'] : (isset($item_arr['ZONA']) ? $item_arr['ZONA'] : (isset($item_arr['Centro']) ? $item_arr['Centro'] : 'SIN ZONA')));

            // Atributo FECHA solicitado específicamente para agrupamiento
            $fecha_str = (isset($item_arr['FECHA']) ? $item_arr['FECHA'] : (isset($item_arr['FECHA REG']) ? $item_arr['FECHA REG'] : (isset($item_arr['Fecha']) ? $item_arr['Fecha'] : null)));
            if (!$fecha_str)
                continue;

            // Intentar parsear fecha de forma robusta (soporte para d/m/Y y Y-m-d)
            $fecha_dt = null;
            if (strpos($fecha_str, '/') !== false) {
                $parts = explode('/', explode(' ', $fecha_str)[0]);
                if (count($parts) == 3) {
                    if (strlen($parts[0]) == 4) { // Y/m/d
                        $fecha_dt = strtotime($fecha_str);
                    } else { // d/m/Y
                        $fecha_dt = strtotime($parts[2] . '-' . $parts[1] . '-' . $parts[0]);
                    }
                }
            }
            if (!$fecha_dt) {
                $fecha_dt = strtotime($fecha_str);
            }

            if (!$fecha_dt)
                continue;

            $per_key = date('Y-m', $fecha_dt);

            // Solo procesar si está dentro del rango visual
            $periodo_valido = false;
            foreach ($periodos as $p) {
                if ($p['key'] == $per_key) {
                    $periodo_valido = true;
                    break;
                }
            }
            if (!$periodo_valido)
                continue;

            // Calculo Cantidad
            $cantidad = 0;
            if ($tab_id == 'sec') {
                $cantidad = (float) (isset($item_arr['CANTIDAD']) ? $item_arr['CANTIDAD'] : 0);
            } elseif ($tab_id == 'com') {
                $cantidad = (float) (isset($item_arr['CAN_MAQUILA']) ? $item_arr['CAN_MAQUILA'] : 0);
            } else {
                $cantidad = (float) (isset($item_arr['CAN_PRODUCTO_KG_50']) ? $item_arr['CAN_PRODUCTO_KG_50'] : 0);
            }

            // Calculo Importe
            $importe = (float) (isset($item_arr['IMPORTE']) ? $item_arr['IMPORTE'] : 0);

            if (!isset($resumen[$zona])) {
                $resumen[$zona] = [];
                $zonas[] = $zona;
            }
            if (!isset($resumen[$zona][$servicio])) {
                $resumen[$zona][$servicio] = [];
                foreach ($periodos as $p) {
                    $resumen[$zona][$servicio][$p['key']] = ['can' => 0, 'imp' => 0];
                }
            }

            $resumen[$zona][$servicio][$per_key]['can'] += $cantidad;
            $resumen[$zona][$servicio][$per_key]['imp'] += $importe;
        }
    }
    sort($zonas);
@endphp

<div class="tab-container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#resumen" data-toggle="tab">RESUMEN</a></li>
        @foreach($tabs as $tab)
            <li><a href="#{{ $tab['id'] }}" data-toggle="tab">{{ $tab['label'] }}</a></li>
        @endforeach
    </ul>
    <div class="tab-content">
        <!-- PESTAÑA RESUMEN -->
        <div id="resumen" class="tab-pane active cont">
            <div class="header-reporte"
                style="background-color: #b2ebf2; padding: 10px; font-weight: bold; margin-bottom: 15px; border: 1px solid #80deea;">
                {{ $nom_empresa }} - DESDE EL: {{ date('d/m/Y', strtotime($fechainicio)) }} HASTA EL:
                {{ date('d/m/Y', strtotime($fechafin)) }}
            </div>

            <div style="width: 100%; overflow-x: auto;">
                <table class="table table-striped table-hover table-bordered" style="width: 100%; font-size: 11px;">
                    <thead style="background-color: #f7bb1e; color: black; font-weight: bold;">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle; text-align: center;">ZONA</th>
                            <th rowspan="2" style="vertical-align: middle; text-align: center;">SERVICIO</th>
                            <th colspan="{{ count($periodos) }}"
                                style="text-align: center; background-color: #ff9800; color: white;">CANTIDAD TN</th>
                            <th rowspan="2"
                                style="vertical-align: middle; text-align: center; background-color: #ff9800; color: white;">
                                Total<br>CANTIDAD</th>
                            <th colspan="{{ count($periodos) }}"
                                style="text-align: center; background-color: #4caf50; color: white;">Suma de IMPORTE
                            </th>
                            <th rowspan="2"
                                style="vertical-align: middle; text-align: center; background-color: #4caf50; color: white;">
                                Total<br>IMPORTE</th>
                        </tr>
                        <tr>
                            @foreach($periodos as $p)
                                <th style="text-align: center; white-space: nowrap; background-color: #ffb74d;">
                                    {{ $p['label'] }}
                                </th>
                            @endforeach
                            @foreach($periodos as $p)
                                <th style="text-align: center; white-space: nowrap; background-color: #81c784;">
                                    {{ $p['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                                                                                    $totales_per_can = [];
                            $totales_per_imp = [];
                            foreach ($periodos as $p) {
                                $totales_per_can[$p['key']] = 0;
                                $totales_per_imp[$p['key']] = 0;
                            }
                            $total_gen_can = 0;
                            $total_gen_imp = 0;
                        @endphp
                        @foreach($zonas as $zona)
                                    @php $first = true; @endphp
                            @foreach($resumen[$zona] as $servicio => $data_per)
                                    <tr>
                                    @if($first)
                                        <td rowspan="{{ count($resumen[$zona]) }}" style="vertical-align: middle; font-weight: bold;">{{ $zona }}</td>
                                        @php $first = false; @endphp
                                    @endif
                                        <td>{{ $servicio }}</td>


                                {{-- Bloque Cantidad --}}
                                @php $total_fila_can = 0; @endphp
                                @foreach($periodos as $p)
                                    @php $valor = $data_per[$p['key']]['can']; @endphp
                                    <td style="text-align: right;">{{ $valor > 0 ? number_format($valor, 2) : '-' }}</td>
                                    @php 
                                                                                                        $total_fila_can += $valor;
                                        $totales_per_can[$p['key']] += $valor;
                                    @endphp

                                @endforeach
                                <td style="text-align: right; font-weight: bold; background-color: #fff3e0;">{{ number_format($total_fila_can, 2) }}</td>
                                                    {{-- Bloque Importe --}}
                                                        @php $total_fila_imp = 0; @endphp
                                                        @foreach($periodos as $p)
                                                            @php $valor = $data_per[$p['key']]['imp']; @endphp
                                                            <td style="text-align: right;">{{ $valor > 0 ? number_format($valor, 2) : '-' }}</td>
                                                            @php 
                                                                                                                                        $total_fila_imp += $valor;
                                                                $totales_per_imp[$p['key']] += $valor;
                                                            @endphp

                                                        @endforeach

                                                                                    <td style="text-align: right; font-weight: bold; background-color: #e8f5e9;">{{ number_format($total_fila_imp, 2) }}</td>

                                                     @php 
                                                                                                                                $total_gen_can += $total_fila_can;
                                                        $total_gen_imp += $total_fila_imp;
                                                    @endphp
                                                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    <tfoot style="background-color: #f1f5f9; font-weight: bold;">
                    <tr>
                            <td colspan="2" style="text-align: center;">TOTALES</td>
                            @foreach($periodos as $p)


                                  <td style="text-align: right;">{{ number_format($totales_per_can[$p['key']], 2) }}</td>
                            @endforeach
                        <td style="text-align: right; background-color: #fff3e0;">{{ number_format($total_gen_can, 2) }}</td>
                        @foreach($periodos as $p)

                               <td style="text-align: right;">{{ number_format($totales_per_imp[$p['key']], 2) }}</td>
                        @endforeach
                <td style="text-align: right; background-color: #e8f5e9;">{{ number_format($total_gen_imp, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@foreach($tabs as $tab)
    <div id="{{ $tab['id'] }}" class="tab-pane cont">
    <div class="header-reporte"
    style="background-color: #b2ebf2; padding: 10px; font-weight: bold; margin-bottom: 15px; border: 1px solid #80deea;">
    {{ $tab['title'] }} - DESDE EL: {{ date('d/m/Y', strtotime($fechainicio)) }} HASTA EL: {{ date('d/m/Y', strtotime($fechafin)) }}
    </div>
            <div style="width: 100%; overflow-x: auto;">
                <table id="table_{{ $tab['id'] }}" class="table table-striped table-hover table-fw-widget" style="width: 100%">
                    <thead>
                        <tr>
                            @if(count($tab['data']) > 0)
                                @foreach($tab['data'][0] as $column => $value)
                                    <th style="white-space: nowrap;">{{$column}}</th>
                                @endforeach
                            @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tab['data'] as $item)
                                    <tr>
                                        @foreach($item as $value)
                                            <td style="white-space: nowrap;">{{$value}}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
@endforeach
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        @foreach($tabs as $tab)
            $('#table_{{ $tab['id'] }}').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                },
                "order": [],
                "pageLength": 25,
                "scrollX": true
            });
        @endforeach
    });
</script>