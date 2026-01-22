<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

use App\Modelos\WEBGrupoopcion;
use App\Modelos\WEBOpcion;
use App\Modelos\WEBRol;
use App\Modelos\WEBRolOpcion;
use App\Modelos\STDEmpresaDireccion;
use App\Modelos\TESCuentaBancaria;
use App\Modelos\CMPCategoria;
use App\Modelos\STDEmpresa;
use App\Modelos\WEBUserEmpresaCentro;
use App\Modelos\ALMCentro;
use App\Modelos\WEBListaPersonal;
use App\Modelos\LqgLiquidacionGasto;

use App\Modelos\Tercero;



use App\User;


use App\Modelos\VMergeOC;
use App\Modelos\FeFormaPago;
use App\Modelos\FeDetalleDocumento;
use App\Modelos\FeDocumento;
use App\Modelos\Estado;
use App\Modelos\CMPOrden;


use App\Modelos\FeDocumentoHistorial;
use App\Modelos\SGDUsuario;

use App\Modelos\STDTrabajador;
use App\Modelos\Archivo;
use App\Modelos\CMPDocAsociarCompra;
use App\Modelos\CMPDetalleProducto;
use App\Modelos\CMPDocumentoCtble;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Session;
use View;
use Stdclass;

use App\Traits\GeneralesTraits;
use App\Traits\ReportesTraits;



class GestionConciliacionCobranzaController extends Controller
{

    use GeneralesTraits;
    use ReportesTraits;

    public function actionListarConciliacionCobranza($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->funciones->getUrl($idopcion, 'Ver');
        if ($validarurl <> 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Conciliacion de Cobranzas');
        $cod_empresa = Session::get('usuario')->usuarioosiris_id;
        $funcion = $this;

        $combo_empresas = $this->rep_lista_empresas_combo();
        $combo_centros = $this->rep_lista_centros_combo();
        $combo_zonas = $this->rep_lista_zonas_comerciales_combo();

        $combo_jefes = array();
        $combo_clientes = array();

        return View::make(
            'reportes/cobranza/conciliacioncobranza',
            [
                'funcion' => $funcion,
                'idopcion' => $idopcion,
                'combo_empresas' => $combo_empresas,
                'combo_centros' => $combo_centros,
                'combo_zonas' => $combo_zonas,
                'combo_jefes' => $combo_jefes,
                'combo_clientes' => $combo_clientes,
                'fechainicio' => date('d-m-Y'),
                'fechafin' => date('d-m-Y'),
            ]
        );
    }

    public function actionAjaxListarJefesVentas(Request $request)
    {
        $cod_zona = $request->get('cod_zona');
        $combo_jefes = $this->rep_lista_jefes_ventas_combo($cod_zona);

        return View::make(
            'reportes/cobranza/ajax/listajefesventas',
            [
                'combo_jefes' => $combo_jefes,
            ]
        );
    }

    public function actionAjaxListarClientes(Request $request)
    {
        $cod_jefe_venta = $request->get('cod_jefe_venta');
        $combo_clientes = $this->rep_lista_clientes_combo($cod_jefe_venta);

        return View::make(
            'reportes/cobranza/ajax/listaclientes',
            [
                'combo_clientes' => $combo_clientes,
            ]
        );
    }

    public function actionAjaxListarConciliacionCobranza(Request $request)
    {
        $fechainicio = $request->get('fechainicio');
        $fechafin = $request->get('fechafin');
        $cod_zona = $request->get('cod_zona');
        $cod_jefe_venta = $request->get('cod_jefe_venta');
        $cod_empresa = $request->get('cod_empresa');
        $cod_centro = $request->get('cod_centro');
        $cod_cliente = $request->get('cod_cliente');

        $lista_conciliacion = $this->rep_conciliacion_cobranza_buscar($fechainicio, $fechafin, $cod_zona, $cod_jefe_venta, $cod_empresa, $cod_centro, $cod_cliente);

        $nom_empresa = DB::table('STD.EMPRESA')->where('COD_EMPR', $cod_empresa)->value('NOM_EMPR');

        $nom_centro = 'TODOS';
        if ($cod_centro == 'LIM')
            $nom_centro = 'LIMA';
        if ($cod_centro == 'NOR')
            $nom_centro = 'NORTE';

        $nom_zona = 'TODOS';
        if ($cod_zona == 'CEN0000000000002')
            $nom_zona = 'LIMA';
        if ($cod_zona == 'CEN0000000000001')
            $nom_zona = 'NORTE';

        $nom_jefe_venta = 'TODOS';
        if ($cod_jefe_venta != '' && $cod_jefe_venta != '-1') {
            $nom_jefe_venta = DB::table('CMP.CATEGORIA')->where('COD_CATEGORIA', $cod_jefe_venta)->value('NOM_CATEGORIA');
        }

        $nom_cliente = 'TODOS';
        if ($cod_cliente != '' && $cod_cliente != '-1') {
            $nom_cliente = DB::table('STD.EMPRESA')->where('COD_EMPR', $cod_cliente)->value('NOM_EMPR');
        }

        return View::make(
            'reportes/cobranza/ajax/listaconciliacioncobranza',
            [
                'lista_conciliacion' => $lista_conciliacion,
                'fechainicio' => $fechainicio,
                'fechafin' => $fechafin,
                'nom_empresa' => $nom_empresa,
                'nom_centro' => $nom_centro,
                'nom_zona' => $nom_zona,
                'nom_jefe_venta' => $nom_jefe_venta,
                'nom_cliente' => $nom_cliente,
                'cod_empresa' => $cod_empresa,
                'cod_centro' => $cod_centro,
                'cod_zona' => $cod_zona,
                'cod_jefe_venta' => $cod_jefe_venta,
                'cod_cliente' => $cod_cliente,
            ]
        );
    }

    public function actionExportarConciliacionCobranzaExcel(Request $request)
    {
        $fechainicio = $request->get('fechainicio');
        $fechafin = $request->get('fechafin');
        $cod_zona = $request->get('cod_zona');
        $cod_jefe_venta = $request->get('cod_jefe_venta');
        $cod_empresa = $request->get('cod_empresa');
        $cod_centro = $request->get('cod_centro');
        $cod_cliente = $request->get('cod_cliente');

        $lista_conciliacion = $this->rep_conciliacion_cobranza_buscar($fechainicio, $fechafin, $cod_zona, $cod_jefe_venta, $cod_empresa, $cod_centro, $cod_cliente);

        $nom_empresa = DB::table('STD.EMPRESA')->where('COD_EMPR', $cod_empresa)->value('NOM_EMPR');
        $nom_centro = ($cod_centro == 'LIM') ? 'LIMA' : (($cod_centro == 'NOR') ? 'NORTE' : 'TODOS');
        $nom_zona = ($cod_zona == 'CEN0000000000002') ? 'LIMA' : (($cod_zona == 'CEN0000000000001') ? 'NORTE' : 'TODOS');
        $nom_jefe_venta = ($cod_jefe_venta != '' && $cod_jefe_venta != '-1') ? DB::table('CMP.CATEGORIA')->where('COD_CATEGORIA', $cod_jefe_venta)->value('NOM_CATEGORIA') : 'TODOS';
        $nom_cliente = ($cod_cliente != '' && $cod_cliente != '-1') ? DB::table('STD.EMPRESA')->where('COD_EMPR', $cod_cliente)->value('NOM_EMPR') : 'TODOS';

        // Detalle de Ventas para el segundo TAB
        $listado_contratos = [];
        $listado_cod_empr = [];
        $listado_cod_empr_cliente = [];
        $mapping_rv = [];
        foreach ($lista_conciliacion as $item) {
            if (isset($item->COD_CONTRATO) && $item->COD_CONTRATO != '') {
                $listado_contratos[] = $item->COD_CONTRATO;
                $mapping_rv[$item->COD_CONTRATO] = $item->NOM_VENDEDOR ?? ($item->RV ?? '');
            }
            if (isset($item->COD_EMPR) && $item->COD_EMPR != '') {
                $listado_cod_empr[] = $item->COD_EMPR;
            }
            if (isset($item->COD_EMPR_CLIENTE) && $item->COD_EMPR_CLIENTE != '') {
                $listado_cod_empr_cliente[] = $item->COD_EMPR_CLIENTE;
            }
        }
        $listado_contratos = array_unique($listado_contratos);
        $listado_cod_empr = array_unique($listado_cod_empr);
        $listado_cod_empr_cliente = array_unique($listado_cod_empr_cliente);

        $lista_detalle_ventas = $this->rep_conciliacion_cobranza_detalle_ventas($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_nd = $this->rep_conciliacion_cobranza_detalle_nd($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_pagos = $this->rep_conciliacion_cobranza_detalle_pagos($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_caja_banco = $this->rep_conciliacion_cobranza_detalle_caja_banco($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_nc = $this->rep_conciliacion_cobranza_detalle_nc($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_adelanto = $this->rep_conciliacion_cobranza_detalle_adelanto($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_extorno = $this->rep_conciliacion_cobranza_detalle_extorno($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_regularizacion = $this->rep_conciliacion_cobranza_detalle_regularizacion($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_compensacion = $this->rep_conciliacion_cobranza_detalle_compensacion($fechainicio, $fechafin, $listado_contratos);
        $lista_detalle_cartas = $this->rep_conciliacion_cobranza_detalle_cartas($listado_cod_empr, $listado_cod_empr_cliente);

        $titulo = 'Conciliación Cobranza';

        $archivo = \Excel::create($titulo, function ($excel) use ($lista_conciliacion, $lista_detalle_ventas, $lista_detalle_nd, $lista_detalle_pagos, $lista_detalle_caja_banco, $lista_detalle_nc, $lista_detalle_adelanto, $lista_detalle_extorno, $lista_detalle_regularizacion, $lista_detalle_compensacion, $lista_detalle_cartas, $mapping_rv, $fechainicio, $fechafin, $nom_empresa, $nom_centro, $nom_zona, $nom_jefe_venta, $nom_cliente) {

            $excel->sheet('Conciliación', function ($sheet) use ($lista_conciliacion, $fechainicio, $fechafin, $nom_empresa, $nom_centro, $nom_zona, $nom_jefe_venta, $nom_cliente) {
                $sheet->loadView('excel/listaconciliacioncobranza', [
                    'lista_conciliacion' => $lista_conciliacion,
                    'fechainicio' => $fechainicio,
                    'fechafin' => $fechafin,
                    'nom_empresa' => $nom_empresa,
                    'nom_centro' => $nom_centro,
                    'nom_zona' => $nom_zona,
                    'nom_jefe_venta' => $nom_jefe_venta,
                    'nom_cliente' => $nom_cliente,
                ]);
            });

            $excel->sheet('VENTAS', function ($sheet) use ($lista_detalle_ventas, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzadetalleventas', [
                    'lista_detalle_ventas' => $lista_detalle_ventas,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('ND', function ($sheet) use ($lista_detalle_nd, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzadetallend', [
                    'lista_detalle_nd' => $lista_detalle_nd,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('CARTAS', function ($sheet) use ($lista_detalle_cartas) {
                $sheet->loadView('excel/listaconciliacioncobranzacartas', [
                    'lista_detalle_cartas' => $lista_detalle_cartas
                ]);
            });

            $excel->sheet('PAGOS', function ($sheet) use ($lista_detalle_pagos, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzadetallepagos', [
                    'lista_detalle_pagos' => $lista_detalle_pagos,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('CAJA Y BANCO', function ($sheet) use ($lista_detalle_caja_banco, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzacajaybanco', [
                    'lista_detalle_caja_banco' => $lista_detalle_caja_banco,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('NC', function ($sheet) use ($lista_detalle_nc, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzanc', [
                    'lista_detalle_nc' => $lista_detalle_nc,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('ADELANTO', function ($sheet) use ($lista_detalle_adelanto, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzaadelanto', [
                    'lista_detalle_adelanto' => $lista_detalle_adelanto,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('EXTORNO', function ($sheet) use ($lista_detalle_extorno, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzaextorno', [
                    'lista_detalle_extorno' => $lista_detalle_extorno,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('REGULARIZACION', function ($sheet) use ($lista_detalle_regularizacion, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzaregularizacion', [
                    'lista_detalle_regularizacion' => $lista_detalle_regularizacion,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->sheet('COMPENSACION', function ($sheet) use ($lista_detalle_compensacion, $mapping_rv) {
                $sheet->loadView('excel/listaconciliacioncobranzacompensacion', [
                    'lista_detalle_compensacion' => $lista_detalle_compensacion,
                    'mapping_rv' => $mapping_rv
                ]);
            });

            $excel->setActiveSheetIndex(0);

        });

        setcookie('download_started', 'true', time() + 20, '/');
        return $archivo->export('xlsx');
    }

}
