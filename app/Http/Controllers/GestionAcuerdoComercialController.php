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
use PHPExcel_Cell;

use App\Traits\GeneralesTraits;
use App\Traits\ReportesAcuerdoComercialTraits;
use App\Traits\ReportesTraits;


class GestionAcuerdoComercialController extends Controller
{

    use GeneralesTraits;
    use ReportesAcuerdoComercialTraits;
    use ReportesTraits;


    public function actionListarAcuerdoComercial($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->funciones->getUrl($idopcion, 'Ver');
        if ($validarurl <> 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Acuerdo Comerciales');
        $combo_empresa = $this->rep_acuerdo_comercial_lista_empresas();
        
        $funcion = $this;
        return View::make(
            'reportes/comercial/acuerdocomercial',
            [
                'funcion' => $funcion,
                'idopcion' => $idopcion,
                'fechainicio' => date('d-m-Y'),
                'fechafin' => date('d-m-Y'),
                'combo_empresa' => $combo_empresa,
            ]
        );
    }

    public function actionAjaxListarAcuerdoComercial(Request $request)
    {
        $fechafin_raw = $request->get('fechafin');
        $fechainicio_raw = $request->get('fechainicio');
        $idopcion = $request->get('idopcion');
        $cod_empresa_filtro = $request->get('cod_empresa');

        // Normalizar fechas a Y-m-d para PHP
        $fechainicio = date('Y-m-d', strtotime(str_replace('/', '-', $fechainicio_raw)));
        $fechafin = date('Y-m-d', strtotime(str_replace('/', '-', $fechafin_raw)));

        $lista_acuerdos = $this->rep_acuerdo_comercial_buscar($fechainicio, $fechafin, $cod_empresa_filtro);

        // Guardar en sesión para acelerar el Excel
        $cod_empresa = Session::get('empresas')->COD_EMPR;
        $cache_key = 'data_acuerdo_' . $cod_empresa . '_' . $fechainicio . '_' . $fechafin . '_' . $cod_empresa_filtro;
        Session::put($cache_key, [
            'data' => $lista_acuerdos
        ]);

        return View::make(
            'reportes/comercial/ajax/listaacuerdocomercial',
            [
                'lista_acuerdos' => $lista_acuerdos,
                'fechainicio' => $fechainicio,
                'fechafin' => $fechafin,
                'idopcion' => $idopcion,
            ]
        );
    }

    public function actionExportarExcelAcuerdoComercial(Request $request)
    {
        $fechainicio_raw = $request->get('fechainicio');
        $fechafin_raw = $request->get('fechafin');
        $cod_empresa_filtro = $request->get('cod_empresa');

        $fechainicio = date('Y-m-d', strtotime(str_replace('/', '-', $fechainicio_raw)));
        $fechafin = date('Y-m-d', strtotime(str_replace('/', '-', $fechafin_raw)));

        $cod_empresa = Session::get('empresas')->COD_EMPR;
        $cache_key = 'data_acuerdo_' . $cod_empresa . '_' . $fechainicio . '_' . $fechafin . '_' . $cod_empresa_filtro;
        $cached_data = Session::get($cache_key);

        if ($cached_data) {
            $lista_acuerdos = $cached_data['data'];
        } else {
            $lista_acuerdos = $this->rep_acuerdo_comercial_buscar($fechainicio, $fechafin, $cod_empresa_filtro);
        }

        setcookie('download_started', 'true', time() + 20, '/');

        \Excel::create('Reporte Acuerdo Comercial', function ($excel) use ($lista_acuerdos, $fechainicio, $fechafin) {
            
            // Pestaña de Resumen Principal
            $excel->sheet('RESUMEN', function ($sheet) use ($lista_acuerdos, $fechainicio, $fechafin) {
                $sheet->loadView('reportes/comercial/excel/acuerdocomercial', [
                    'lista_acuerdos' => $lista_acuerdos,
                    'fechainicio' => $fechainicio,
                    'fechafin' => $fechafin,
                ]);
            });

            // Pestañas Dinámicas por ABREVIATURA
            $agrupadoPorAbreviatura = collect($lista_acuerdos)->groupBy('ABREVIATURA');
            
            foreach($agrupadoPorAbreviatura as $abreviatura => $datos) {
                $nombre_pestaña = substr((string)$abreviatura ?: 'SIN ABREV', 0, 30);
                // Limpiar caracteres no permitidos en nombres de pestañas de Excel
                $nombre_pestaña = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $nombre_pestaña);

                $excel->sheet($nombre_pestaña, function ($sheet) use ($datos) {
                    $sheet->loadView('reportes/comercial/excel/detalleacuerdocomercial', [
                        'datos' => $datos
                    ]);
                });
            }

        })->export('xlsx');
    }

}
