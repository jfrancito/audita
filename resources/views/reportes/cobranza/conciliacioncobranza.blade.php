@extends('template_lateral')
@section('style')
<link rel="stylesheet" type="text/css" href="{{ asset('public/lib/select2/css/select2.min.css') }} " />
<link rel="stylesheet" type="text/css" href="{{ asset('public/lib/sweetalert/css/sweetalert.css') }} " />
<link rel="stylesheet" type="text/css"
  href="{{ asset('public/lib/datetimepicker/css/bootstrap-datetimepicker.min.css') }} " />
<link rel="stylesheet" type="text/css" href="{{ asset('public/lib/datatables/css/dataTables.bootstrap.min.css') }} " />
<link rel="stylesheet" type="text/css" href="{{ asset('public/css/reportes/reportegeneral.css?v=' . $version) }} " />
@stop
@section('section')
<div class="be-content">
  <div class="main-content container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="panel panel-default panel-table">
          <div class="panel-heading">Conciliación Cobranza
          </div>
          <div class="panel-body">

            <div class="row filtro-row">
              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Empresa:</label>
                  <select name="cod_empresa" id="cod_empresa" class="form-control select2">
                    @foreach($combo_empresas as $item)
                      <option value="{{ $item->COD_EMPR }}" {{ $item->COD_EMPR == Session::get('empresas')->COD_EMPR ? 'selected' : '' }}>{{ $item->NOM_EMPR }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Centro:</label>
                  <select name="cod_centro" id="cod_centro" class="form-control select2">
                    @foreach($combo_centros as $item)
                      <option value="{{ $item->COD_CENTRO }}" {{ $item->COD_CENTRO == 'TOD' ? 'selected' : '' }}>
                        {{ $item->NOM_CENTRO }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Zona Comercial:</label>
                  <select name="cod_zona" id="cod_zona" class="form-control select2">
                    @foreach($combo_zonas as $item)
                      <option value="{{ $item->COD_ZONA }}" {{ $item->COD_ZONA == '-1' ? 'selected' : '' }}>
                        {{ $item->NOM_ZONA }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 ajax_jefe_venta">
                <div class="form-group">
                  <label>Jefe Venta:</label>
                  <select name="cod_jefe_venta" id="cod_jefe_venta" class="form-control select2">
                    @foreach($combo_jefes as $item)
                      <option value="{{ $item->COD }}" {{ $item->COD == '-1' ? 'selected' : '' }}>{{ $item->NOMBRE }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 ajax_cliente">
                <div class="form-group">
                  <label>Cliente:</label>
                  <select name="cod_cliente" id="cod_cliente" class="form-control select2">
                    @foreach($combo_clientes as $item)
                      <option value="{{ $item->COD_EMPR_CLI }}" {{ $item->COD_EMPR_CLI == '-1' ? 'selected' : '' }}>
                        {{ $item->NOM_EMPR_CLI }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Fecha Inicio:</label>
                  <div data-min-view="2" data-date-format="dd-mm-yyyy" class="input-group date datetimepicker">
                    <input size="16" type="text" value="{{$fechainicio}}" id='fechainicio' name='fechainicio'
                      class="form-control">
                    <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Fecha Fin:</label>
                  <div data-min-view="2" data-date-format="dd-mm-yyyy" class="input-group date datetimepicker">
                    <input size="16" type="text" value="{{$fechafin}}" id='fechafin' name='fechafin'
                      class="form-control">
                    <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group" style="padding-top: 25px;">
                  <button type="button" id="btnBuscar" class="btn btn-space btn-primary btn-lg"
                    onclick="abrircargando()">
                    <i class="icon icon-left mdi mdi-search"></i> Buscar
                  </button>
                </div>
              </div>

            </div>

            <div class="divider-report">
              <hr>
              <div class="divider-text">RESULTADOS DEL REPORTE</div>
            </div>

            <div class="row ajax_lista_conciliacion_cobranza">
              <!-- Aquí se cargará el resultado del reporte -->
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop
@section('script')
<script src="{{ asset('public/lib/jquery-ui/jquery-ui.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/lib/sweetalert/js/sweetalert.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/lib/moment.js/min/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/lib/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"
  type="text/javascript"></script>
<script src="{{ asset('public/lib/datatables/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/lib/datatables/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/lib/select2/js/select2.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/js/reportes/coinciliacioncobranza.js?v=' . $version) }}" type="text/javascript"></script>
@stop