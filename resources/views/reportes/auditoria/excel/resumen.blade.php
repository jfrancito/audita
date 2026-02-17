@php
    $tabs = [
        ['id' => 'sec', 'label' => 'SEC', 'name' => 'SECADO', 'data' => $lista_secado],
        ['id' => 'pil', 'label' => 'PIL', 'name' => 'PILADO', 'data' => $lista_pilado],
        ['id' => 'sel', 'label' => 'SEL', 'name' => 'SELECTORA', 'data' => $lista_selectora],
        ['id' => 'emb', 'label' => 'EMB', 'name' => 'EMBOLSADO', 'data' => $lista_embolsado],
        ['id' => 'ane', 'label' => 'AÑE', 'name' => 'AÑEJAMIENTO', 'data' => $lista_anejamiento],
        ['id' => 'mes', 'label' => 'MES', 'name' => 'MEZCLA', 'data' => $lista_mezcla],
        ['id' => 'rep', 'label' => 'REP', 'name' => 'REPROCESO', 'data' => $lista_reproceso],
        ['id' => 'com', 'label' => 'COM', 'name' => 'COMPACTADO', 'data' => $lista_compactado],
        ['id' => 'pul', 'label' => 'PUL', 'name' => 'PULIDO', 'data' => $lista_pulido],
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
            $item_arr = (array)$item;
            $zona = (isset($item_arr['CENTRO']) ? $item_arr['CENTRO'] : (isset($item_arr['ZONA']) ? $item_arr['ZONA'] : (isset($item_arr['Centro']) ? $item_arr['Centro'] : 'SIN ZONA')));
            $fecha_str = (isset($item_arr['FECHA']) ? $item_arr['FECHA'] : (isset($item_arr['FECHA REG']) ? $item_arr['FECHA REG'] : (isset($item_arr['Fecha']) ? $item_arr['Fecha'] : null)));
            if (!$fecha_str) continue;
            
            $fecha_dt = null;
            if (strpos($fecha_str, '/') !== false) {
                $parts = explode('/', explode(' ', $fecha_str)[0]);
                if (count($parts) == 3) {
                    if (strlen($parts[0]) == 4) { $fecha_dt = strtotime($fecha_str); }
                    else { $fecha_dt = strtotime($parts[2] . '-' . $parts[1] . '-' . $parts[0]); }
                }
            }
            if (!$fecha_dt) { $fecha_dt = strtotime($fecha_str); }
            if (!$fecha_dt) continue;

            $per_key = date('Y-m', $fecha_dt);
            $periodo_valido = false;
            foreach($periodos as $p) { if($p['key'] == $per_key) { $periodo_valido = true; break; } }
            if(!$periodo_valido) continue;

            // Cantidad
            $cantidad = 0;
            if ($tab_id == 'sec') {
                $cantidad = (float)(isset($item_arr['CANTIDAD']) ? $item_arr['CANTIDAD'] : 0);
            } elseif ($tab_id == 'com') {
                $cantidad = (float)(isset($item_arr['CAN_MAQUILA']) ? $item_arr['CAN_MAQUILA'] : 0);
            } else {
                $cantidad = (float)(isset($item_arr['CAN_PRODUCTO_KG_50']) ? $item_arr['CAN_PRODUCTO_KG_50'] : 0);
            }

            // Importe
            $importe = (float)(isset($item_arr['IMPORTE']) ? $item_arr['IMPORTE'] : 0);

            if (!isset($resumen[$zona])) {
                $resumen[$zona] = [];
                $zonas[] = $zona;
            }
            if (!isset($resumen[$zona][$servicio])) {
                $resumen[$zona][$servicio] = [];
                foreach($periodos as $p) { $resumen[$zona][$servicio][$p['key']] = ['can' => 0, 'imp' => 0]; }
            }
            
            $resumen[$zona][$servicio][$per_key]['can'] += $cantidad;
            $resumen[$zona][$servicio][$per_key]['imp'] += $importe;
        }
    }
    sort($zonas);
@endphp

<table>
    <tr>
        <th colspan="{{ (count($periodos) * 2) + 4 }}" style="font-weight: bold; text-align: center; font-size: 14px; background-color: #f1f5f9;">{{ $nom_empresa }} - DESDE EL: {{ date('d/m/Y', strtotime($fechainicio)) }} HASTA EL: {{ date('d/m/Y', strtotime($fechafin)) }}</th>
    </tr>
    <thead>
        <!-- FILA 1: Títulos de Grupos -->
        <tr>
            <th style="background-color: #f7bb1e; color: black; font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">ZONA</th>
            <th style="background-color: #f7bb1e; color: black; font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">SERVICIO</th>
            <th colspan="{{ count($periodos) }}" style="background-color: #ff9800; color: white; font-weight: bold; border: 1px solid #000; text-align: center;">CANTIDAD TN</th>
            <th style="background-color: #ff9800; color: white; font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">Total CANTIDAD</th>
            <th colspan="{{ count($periodos) }}" style="background-color: #4caf50; color: white; font-weight: bold; border: 1px solid #000; text-align: center;">Suma de IMPORTE</th>
            <th style="background-color: #4caf50; color: white; font-weight: bold; border: 1px solid #000; text-align: center; vertical-align: middle;">Total IMPORTE</th>
        </tr>
        <!-- FILA 2: Meses (Dejar vacías las celdas de Zona y Servicio para simular el rowspan) -->
        <tr>
            <th style="background-color: #f7bb1e; border: 1px solid #000;"></th> <!-- Espacio vacío para ZONA -->
            <th style="background-color: #f7bb1e; border: 1px solid #000;"></th> <!-- Espacio vacío para SERVICIO -->
            
            @foreach($periodos as $p)
                <th style="background-color: #ffb74d; color: black; font-weight: bold; border: 1px solid #000; text-align: center;">{{ $p['label'] }}</th>
            @endforeach
            
            <th style="background-color: #ff9800; border: 1px solid #000;"></th> <!-- Espacio vacío para Total Cantidad -->

            @foreach($periodos as $p)
                <th style="background-color: #81c784; color: black; font-weight: bold; border: 1px solid #000; text-align: center;">{{ $p['label'] }}</th>
            @endforeach

            <th style="background-color: #4caf50; border: 1px solid #000;"></th> <!-- Espacio vacío para Total Importe -->
        </tr>
    </thead>
    <tbody>
        @php 
            $totales_per_can = [];
            $totales_per_imp = [];
            foreach($periodos as $p) { 
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
                    <td style="border: 1px solid #000; font-weight: {{ $first ? 'bold' : 'normal' }};">{{ $zona }}</td>
                    <td style="border: 1px solid #000;">{{ $servicio }}</td>
                    @php 
                        $total_fila_can = 0;
                        $total_fila_imp = 0;
                        $first = false; 
                    @endphp
                    
                    {{-- Cantidad --}}
                    @foreach($periodos as $p)
                        @php $valor = $data_per[$p['key']]['can']; @endphp
                        <td style="text-align: right; border: 1px solid #000;">{{ $valor > 0 ? number_format($valor, 2, '.', '') : '-' }}</td>
                        @php 
                            $total_fila_can += $valor;
                            $totales_per_can[$p['key']] += $valor;
                        @endphp
                    @endforeach
                    <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #fff3e0;">{{ number_format($total_fila_can, 2, '.', '') }}</td>
                    
                    {{-- Importe --}}
                    @foreach($periodos as $p)
                        @php $valor = $data_per[$p['key']]['imp']; @endphp
                        <td style="text-align: right; border: 1px solid #000;">{{ $valor > 0 ? number_format($valor, 2, '.', '') : '-' }}</td>
                        @php 
                            $total_fila_imp += $valor;
                            $totales_per_imp[$p['key']] += $valor;
                        @endphp
                    @endforeach
                    <td style="text-align: right; font-weight: bold; border: 1px solid #000; background-color: #e8f5e9;">{{ number_format($total_fila_imp, 2, '.', '') }}</td>

                    @php 
                        $total_gen_can += $total_fila_can; 
                        $total_gen_imp += $total_fila_imp;
                    @endphp
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #000; text-align: center;">TOTALES</td>
            @foreach($periodos as $p)
                <td style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($totales_per_can[$p['key']], 2, '.', '') }}</td>
            @endforeach
            <td style="background-color: #ff9800; color: white; font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($total_gen_can, 2, '.', '') }}</td>
            @foreach($periodos as $p)
                <td style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($totales_per_imp[$p['key']], 2, '.', '') }}</td>
            @endforeach
            <td style="background-color: #4caf50; color: white; font-weight: bold; border: 1px solid #000; text-align: right;">{{ number_format($total_gen_imp, 2, '.', '') }}</td>
        </tr>
    </tfoot>
</table>
