@php
        // Separar información: Los que tienen acuerdo (para resumen) y todos (para detalle)
        $tab_resumen = collect($lista_acuerdos)->filter(function($item) {
            return !empty(trim($item->ACUERDO_DESCUENTO));
        });

        $agrupadoPorEmpresa = $tab_resumen->groupBy(function($item) {
            return trim($item->EMPRESA);
        });

        // Agrupar por ABREVIATURA para los TABS dinámicos
        $agrupadoPorAbreviatura = collect($lista_acuerdos)->groupBy('ABREVIATURA');

        $coloresAccento = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
        $montoTotalGeneral = $tab_resumen->sum('CAN_TOTAL');
    @endphp

    <div class="col-xs-12">
        <div class="tab-container">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab-resumen" data-toggle="tab">RESUMEN</a></li>
                @foreach($agrupadoPorAbreviatura as $abreviatura => $datos)
                    @php $id_tab = preg_replace('/[^A-Za-z0-9]/', '', $abreviatura); @endphp
                    <li><a href="#tab-{{ $id_tab }}" data-toggle="tab">{{ $abreviatura ?: 'SIN ABREV.' }}</a></li>
                @endforeach
            </ul>
            <div class="tab-content" style="padding: 20px; background: #f8fafc; border: 1px solid #ddd; border-top: none; border-radius: 0 0 12px 12px;">
                
                <!-- TAB RESUMEN (DISEÑO MASTER-LIST) -->
                <div id="tab-resumen" class="tab-pane active animated fadeIn">
                    <!-- CUADRO CONSOLIDADO GENERAL -->
                    @if(count($agrupadoPorEmpresa) > 0)
                    <div class="panel panel-default" style="border: none; border-radius: 15px; margin-bottom: 40px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; background: white;">
                        <div class="panel-heading" style="background: #1e293b; color: white; padding: 15px 25px; border: none; display: flex; align-items: center;">
                            <i class="mdi mdi-view-dashboard" style="font-size: 1.5em; margin-right: 12px;"></i>
                            <span style="font-weight: 800; font-size: 1.1em; text-transform: uppercase; letter-spacing: 1px;">Consolidado General por Empresa</span>
                        </div>
                        <div class="panel-body" style="padding: 0;">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <thead>
                                    <tr style="background-color: #f1f5f9; color: #475569; font-size: 0.85em; font-weight: 800; text-transform: uppercase;">
                                        <th style="padding: 12px 25px; border-bottom: 2px solid #e2e8f0;">Nombre de la Empresa</th>
                                        <th class="text-right" style="padding: 12px 25px; border-bottom: 2px solid #e2e8f0; width: 250px;">Total Acumulado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agrupadoPorEmpresa as $nomeEmpresa => $datos)
                                        <tr>
                                            <td style="padding: 12px 25px; font-weight: 600; color: #334155;">{{ $nomeEmpresa }}</td>
                                            <td class="text-right" style="padding: 12px 25px; font-weight: 700; color: #334155; font-size: 1.1em;">
                                                S/ {{ number_format($datos->sum('CAN_TOTAL'), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f8fafc; border-top: 2px solid #e2e8f0;">
                                        <td style="padding: 15px 25px; font-weight: 800; color: #1e293b; font-size: 1.1em;">TOTAL GENERAL</td>
                                        <td class="text-right" style="padding: 15px 25px;">
                                            <span style="background: #10b981; color: white; padding: 5px 15px; border-radius: 8px; font-weight: 800; font-size: 1.3em; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">
                                                S/ {{ number_format($montoTotalGeneral, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <hr style="border-top: 2px dashed #cbd5e1; margin-bottom: 40px; opacity: 0.5;">

                    @php $loop_index = 0; @endphp
                    @foreach($agrupadoPorEmpresa as $nomeEmpresa => $datosEmpresa)
                        @php
                            $empresa = $datosEmpresa->first()->EMPRESA;
                            $id_empresa = 'ID' . preg_replace('/[^A-Za-z0-9]/', '', trim($empresa));
                            $color = $coloresAccento[$loop_index % count($coloresAccento)];
                            $loop_index++;
                        @endphp
                        <div class="panel panel-default panel-table card-reporte" 
                             style="border: none; border-radius: 15px; margin-bottom: 40px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); overflow: hidden; background: white;">
                            
                            <!-- Cabecera Premium con Gradiente Sutil -->
                            <div class="panel-heading fila-empresa clickable" data-id="{{ $id_empresa }}" 
                                 style="background: linear-gradient(to right, #ffffff, #f1f5f9); border-left: 6px solid {{ $color }}; padding: 20px 30px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
                                
                                <div style="display: flex; align-items: center;">
                                    <div style="background-color: {{ $color }}1a; padding: 10px; border-radius: 10px; margin-right: 20px;">
                                        <i class="icon-toggle-empresa mdi mdi-chevron-down" style="font-size: 1.8em; color: {{ $color }};"></i>
                                    </div>
                                    <div>
                                        <span style="font-weight: 800; font-size: 1.3em; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">{{ $empresa }}</span>
                                        <span style="font-size: 0.8em; color: #64748b; font-weight: 500;">Reporte Consolidado de Acuerdos</span>
                                    </div>
                                </div>
                                
                                <div style="text-align: right;">
                                    <div style="background: {{ $color }}; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 12px {{ $color }}4d;">
                                        <span style="font-size: 0.7em; font-weight: 800; color: #ffffffcc; display: block; text-transform: uppercase;">Total General</span>
                                        <span style="font-weight: 800; font-size: 1.5em; color: #ffffff; display: block; line-height: 1.2;">
                                            S/ {{ number_format($datosEmpresa->sum('CAN_TOTAL'), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-body detalle-empresa-{{ $id_empresa }}" style="padding: 0;">
                                <table class="table table-hover" style="margin-bottom: 0;">
                                    <thead>
                                        <tr style="background-color: #f8fafc; color: #475569; font-size: 0.9em; font-weight: 700;">
                                            <th style="padding: 15px 35px; border-bottom: 2px solid #f1f5f9;">DESCRIPCION DEL ACUERDO</th>
                                            <th class="text-right" style="padding: 15px 35px; border-bottom: 2px solid #f1f5f9; width: 250px;">IMPORTE TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $agrupadoPorPeriodo = $datosEmpresa->groupBy('PERIODO');
                                        @endphp

                                        @foreach($agrupadoPorPeriodo as $periodo => $datosPeriodo)
                                            @php
                                                $id_periodo = $id_empresa . preg_replace('/[^A-Za-z0-9]/', '', trim($periodo));
                                            @endphp
                                            <tr class="fila-periodo clickable" data-id="{{ $id_periodo }}" 
                                                style="background-color: #f1f5f9; cursor: pointer; transition: all 0.2s;">
                                                <td style="padding: 15px 35px; font-weight: 700; color: #334155; font-size: 1.05em;">
                                                    <i class="icon-toggle mdi mdi-plus-box" style="margin-right: 12px; color: {{ $color }}; font-size: 1.2em;"></i> 
                                                    PERIODO: {{ $periodo }}
                                                </td>
                                                <td class="text-right" style="padding: 15px 35px; font-weight: 800; font-size: 1.2em; color: #334155;">
                                                    S/ {{ number_format($datosPeriodo->sum('CAN_TOTAL'), 2) }}
                                                </td>
                                            </tr>
                                            
                                            @php
                                                $agrupadoPorAcuerdo = $datosPeriodo->groupBy('ACUERDO_DESCUENTO');
                                            @endphp

                                            @foreach($agrupadoPorAcuerdo as $acuerdo => $datosAcuerdo)
                                                <tr class="detalle-{{ $id_periodo }}" style="display: none; background-color: #ffffff;">
                                                    <td style="padding: 12px 75px; color: #64748b; font-weight: 500; border-top: 1px solid #f8fafc;">
                                                        <div style="display: flex; align-items: center;">
                                                            <div style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $color }}; margin-right: 12px; opacity: 0.5;"></div>
                                                            {{ $acuerdo }}
                                                        </div>
                                                    </td>
                                                    <td class="text-right" style="padding: 12px 35px; color: #475569; font-weight: 600; border-top: 1px solid #f8fafc;">
                                                        S/ {{ number_format($datosAcuerdo->sum('CAN_TOTAL'), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                    @else
                        <div class="alert alert-info" style="border-radius: 12px; border: none; background: #e0f2fe; color: #0369a1; padding: 25px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <i class="mdi mdi-information-outline" style="font-size: 2em; display: block; margin-bottom: 10px;"></i>
                            <span style="font-weight: 700; font-size: 1.1em;">No hay datos disponibles para el resumen</span><br>
                            Intente ajustando los filtros de fecha o empresa, o verifique los acuerdos.
                        </div>
                    @endif
                </div>

                <!-- TABS DINAMICOS POR ABREVIATURA -->
                @foreach($agrupadoPorAbreviatura as $abreviatura => $datos)
                    @php $id_tab = preg_replace('/[^A-Za-z0-9]/', '', $abreviatura); @endphp
                    <div id="tab-{{ $id_tab }}" class="tab-pane animated fadeIn">
                        <div class="table-responsive" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <table class="table table-condensed table-hover table-bordered" style="font-size: 0.85em; margin-bottom: 0;">
                                <thead style="background: #1e293b; color: white;">
                                    <tr>
                                        <th class="text-center" style="min-width: 150px;">ACUERDO DESC.</th>
                                        <th class="text-center" style="min-width: 100px;">COD. DOC.</th>
                                        <th class="text-center" style="min-width: 80px;">PERIODO</th>
                                        <th class="text-center">TIPO</th>
                                        <th class="text-center">SERIE</th>
                                        <th class="text-center">NRO. DOC.</th>
                                        <th class="text-center" style="min-width: 90px;">FECHA EMISIÓN</th>
                                        <th class="text-center" style="min-width: 120px;">FECHA REGISTRO</th>
                                        <th class="text-center" style="min-width: 180px;">EMPRESA</th>
                                        <th class="text-center">SUBTOTAL</th>
                                        <th class="text-center">IMPUESTO</th>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ESTADO</th>
                                        <th class="text-center" style="min-width: 200px;">GLOSA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datos->sortBy('FEC_EMISION') as $reg) {{-- Ensure date ordering --}}
                                        @php
                                            $sin_acuerdo = empty(trim($reg->ACUERDO_DESCUENTO));
                                            $row_style = $sin_acuerdo ? 'background-color: #fff7ed; color: #9a3412;' : '';
                                        @endphp
                                        <tr style="{{ $row_style }}">
                                            <td class="text-center {{ $sin_acuerdo ? 'bg-warning' : '' }}" style="font-weight: bold;">
                                                {{ $reg->ACUERDO_DESCUENTO ?: '--- SIN ACUERDO ---' }}
                                            </td>
                                            <td class="text-center">{{ $reg->COD_DOCUMENTO_CTBLE }}</td>
                                            <td class="text-center">{{ $reg->PERIODO }}</td>
                                            <td class="text-center">{{ $reg->TIPO_DOCUMENTO }}</td>
                                            <td class="text-center">{{ $reg->NRO_SERIE }}</td>
                                            <td class="text-center">{{ $reg->NRO_DOC }}</td>
                                            <td class="text-center">{{ date('d/m/Y', strtotime($reg->FEC_EMISION)) }}</td>
                                            <td class="text-center">{{ $reg->FEC_USUARIO_CREA_AUD ? date('d/m/Y H:i:s', strtotime($reg->FEC_USUARIO_CREA_AUD)) : '---' }}</td>
                                            <td>{{ $reg->EMPRESA }}</td>
                                            <td class="text-right">S/ {{ number_format($reg->CAN_SUB_TOTAL, 2) }}</td>
                                            <td class="text-right">S/ {{ number_format($reg->CAN_IMPUESTO_VTA, 2) }}</td>
                                            <td class="text-right" style="font-weight: bold;">S/ {{ number_format($reg->CAN_TOTAL, 2) }}</td>
                                            <td class="text-center">
                                                <span class="label {{ $reg->COD_CATEGORIA_ESTADO_DOC_CTBLE == 'EDC0000000000001' ? 'label-success' : 'label-warning' }}">
                                                    {{ $reg->TXT_CATEGORIA_ESTADO_DOC_CTBLE }}
                                                </span>
                                            </td>
                                            <td style="font-size: 0.9em;">{{ $reg->TXT_GLOSA }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
