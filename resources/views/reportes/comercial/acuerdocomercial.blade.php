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
          <div class="panel-heading">Acuerdo Comercial
          </div>
          <div class="panel-body">
            <input type="hidden" name="idopcion" id='idopcion' value="{{$idopcion}}">
            <input type="hidden" name="token" id='token' value="{{ csrf_token() }}">

            <div class="row filtro-row">

              <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="form-group">
                  <label>Empresa:</label>
                  <select name="cod_empresa" id="cod_empresa" class="form-control select2">
                    <option value="TODAS">TODAS</option>
                    @foreach($combo_empresa as $item)
                      <option value="{{$item->EMPRESA}}">{{$item->EMPRESA}}</option>
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
                  <button type="button" id="btnBuscar" class="btn btn-space btn-primary btn-lg">
                    <i class="icon icon-left mdi mdi-search"></i> Buscar
                  </button>
                  <button type="button" id="btnExcel" class="btn btn-space btn-success btn-lg">
                    <i class="icon icon-left mdi mdi-download"></i> Excel
                  </button>
                </div>
              </div>

            </div>

            <div class="divider-report">
              <hr>
              <div class="divider-text">RESULTADOS DEL REPORTE</div>
            </div>

            <div class="row ajax_lista_acuerdo_comercial">
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
<script src="{{ asset('public/js/reportes/acuerdocomercial.js?v=' . $version) }}" type="text/javascript"></script>
@stop