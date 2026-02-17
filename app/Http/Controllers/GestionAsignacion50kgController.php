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
use App\Traits\ReportesAsignacion50kgTraits;

class GestionAsignacion50kgController extends Controller
{

    use GeneralesTraits;
    use ReportesAsignacion50kgTraits;

    public function actionListarAsignacion50kg($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->funciones->getUrl($idopcion, 'Ver');
        if ($validarurl <> 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Asignacion de 50kg');
        $cod_empresa = Session::get('usuario')->usuarioosiris_id;
        $funcion = $this;
        return View::make(
            'reportes/auditoria/asignacion50kg',
            [
                'funcion' => $funcion,
                'idopcion' => $idopcion,
                'fechainicio' => date('d-m-Y'),
                'fechafin' => date('d-m-Y'),
            ]
        );
    }


    public function actionAjaxListarAsignacion50kg(Request $request)
    {
        $fechafin_raw = $request->get('fechafin');
        $fechainicio_raw = $request->get('fechainicio');

        // Normalizar fechas a Y-m-d para PHP
        $fechainicio = date('Y-m-d', strtotime(str_replace('/', '-', $fechainicio_raw)));
        $fechafin = date('Y-m-d', strtotime(str_replace('/', '-', $fechafin_raw)));

        $cod_empresa = Session::get('empresas')->COD_EMPR;

        $lista_secado = $this->rep_asignacion_50kg_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_pilado = $this->rep_asignacion_pilado_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_selectora = $this->rep_asignacion_selectora_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_embolsado = $this->rep_asignacion_embolsado_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_anejamiento = $this->rep_asignacion_anejamiento_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_mezcla = $this->rep_asignacion_mezcla_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_reproceso = $this->rep_asignacion_reproceso_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_compactado = $this->rep_asignacion_compactado_buscar($fechainicio, $fechafin, $cod_empresa);
        $lista_pulido = $this->rep_asignacion_pulido_buscar($fechainicio, $fechafin, $cod_empresa);

        // Guardar en sesión para acelerar el Excel
        $cache_key = 'data_resumen_' . $cod_empresa . '_' . $fechainicio . '_' . $fechafin;
        Session::put($cache_key, [
            'sec' => $lista_secado,
            'pil' => $lista_pilado,
            'sel' => $lista_selectora,
            'emb' => $lista_embolsado,
            'ane' => $lista_anejamiento,
            'mes' => $lista_mezcla,
            'rep' => $lista_reproceso,
            'com' => $lista_compactado,
            'pul' => $lista_pulido
        ]);

        $nom_empresa = Session::get('empresas')->NOM_EMPR;

        return View::make(
            'reportes/auditoria/ajax/listaasignacion50kg',
            [
                'lista_secado' => $lista_secado,
                'lista_pilado' => $lista_pilado,
                'lista_selectora' => $lista_selectora,
                'lista_embolsado' => $lista_embolsado,
                'lista_anejamiento' => $lista_anejamiento,
                'lista_mezcla' => $lista_mezcla,
                'lista_reproceso' => $lista_reproceso,
                'lista_compactado' => $lista_compactado,
                'lista_pulido' => $lista_pulido,
                'fechainicio' => $fechainicio,
                'fechafin' => $fechafin,
                'cod_empresa' => $cod_empresa,
                'nom_empresa' => $nom_empresa,
            ]
        );
    }

    public function actionExportarExcelAsignacion50kg(Request $request)
    {
        $fechainicio_raw = $request->get('fechainicio');
        $fechafin_raw = $request->get('fechafin');

        // Normalizar fechas
        $fechainicio = date('Y-m-d', strtotime(str_replace('/', '-', $fechainicio_raw)));
        $fechafin = date('Y-m-d', strtotime(str_replace('/', '-', $fechafin_raw)));

        $cod_empresa = Session::get('empresas')->COD_EMPR;
        $nom_empresa = Session::get('empresas')->NOM_EMPR;

        $cache_key = 'data_resumen_' . $cod_empresa . '_' . $fechainicio . '_' . $fechafin;
        $cached_data = Session::get($cache_key);

        if ($cached_data) {
            $lista_secado = $cached_data['sec'];
            $lista_pilado = $cached_data['pil'];
            $lista_selectora = $cached_data['sel'];
            $lista_embolsado = $cached_data['emb'];
            $lista_anejamiento = $cached_data['ane'];
            $lista_mezcla = $cached_data['mes'];
            $lista_reproceso = $cached_data['rep'];
            $lista_compactado = $cached_data['com'];
            $lista_pulido = $cached_data['pul'];
        } else {
            $lista_secado = $this->rep_asignacion_50kg_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_pilado = $this->rep_asignacion_pilado_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_selectora = $this->rep_asignacion_selectora_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_embolsado = $this->rep_asignacion_embolsado_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_anejamiento = $this->rep_asignacion_anejamiento_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_mezcla = $this->rep_asignacion_mezcla_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_reproceso = $this->rep_asignacion_reproceso_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_compactado = $this->rep_asignacion_compactado_buscar($fechainicio, $fechafin, $cod_empresa);
            $lista_pulido = $this->rep_asignacion_pulido_buscar($fechainicio, $fechafin, $cod_empresa);
        }

        // Colocar la cookie antes de iniciar la generación del Excel
        setcookie('download_started', 'true', time() + 20, '/');

        \Excel::create('Reporte Asignación 50kg', function ($excel) use ($lista_secado, $lista_pilado, $lista_selectora, $lista_embolsado, $lista_anejamiento, $lista_mezcla, $lista_reproceso, $lista_compactado, $lista_pulido, $fechainicio, $fechafin, $nom_empresa) {
            $excel->sheet('RESUMEN', function ($sheet) use ($lista_secado, $lista_pilado, $lista_selectora, $lista_embolsado, $lista_anejamiento, $lista_mezcla, $lista_reproceso, $lista_compactado, $lista_pulido, $fechainicio, $fechafin, $nom_empresa) {
                $sheet->loadView('reportes/auditoria/excel/resumen', [
                    'lista_secado' => $lista_secado,
                    'lista_pilado' => $lista_pilado,
                    'lista_selectora' => $lista_selectora,
                    'lista_embolsado' => $lista_embolsado,
                    'lista_anejamiento' => $lista_anejamiento,
                    'lista_mezcla' => $lista_mezcla,
                    'lista_reproceso' => $lista_reproceso,
                    'lista_compactado' => $lista_compactado,
                    'lista_pulido' => $lista_pulido,
                    'fechainicio' => $fechainicio,
                    'fechafin' => $fechafin,
                    'nom_empresa' => $nom_empresa,
                ]);
                $sheet->freezeFirstRowAndColumn();
                $sheet->setFreeze('C4');

                // Recalcular periodos para saber las columnas de merge
                $periodos = [];
                // La variable $fechainicio y $fechafin ya estan en Y-m-d
                $iter_date_loop = strtotime(date('Y-m-01', strtotime($fechainicio)));
                $end_date_loop = strtotime(date('Y-m-01', strtotime($fechafin)));

                while ($iter_date_loop <= $end_date_loop) {
                    $periodos[] = date('Y-m', $iter_date_loop);
                    $iter_date_loop = strtotime("+1 month", $iter_date_loop);
                }

                // NOTA: PHPExcel_Cell::stringFromColumnIndex es 0-indexed (A=0, B=1, C=2...)
                // Estructura:
                // A (0): ZONA
                // B (1): SERVICIO
                // C (2) a [2 + count - 1]: CANTIDAD
                // [2 + count]: Total CANT
                // [2 + count + 1] a [2 + count + 1 + count - 1]: IMPORTE
                // [2 + count + 1 + count]: Total IMP

                $col_start_cant = 2; // C
                $count_pers = count($periodos);
                $col_end_cant = $col_start_cant + $count_pers - 1;
                $col_total_cant = $col_end_cant + 1;

                $col_start_imp = $col_total_cant + 1;
                $col_end_imp = $col_start_imp + $count_pers - 1;
                $col_total_imp = $col_end_imp + 1;

                // Fila 2 y 3 son las cabeceras
                $sheet->mergeCells('A2:A3'); // ZONA
                $sheet->mergeCells('B2:B3'); // SERVICIO

                // Merge titulo CANTIDAD (Fila 2)
                $sheet->mergeCells(\PHPExcel_Cell::stringFromColumnIndex($col_start_cant) . '2:' . \PHPExcel_Cell::stringFromColumnIndex($col_end_cant) . '2');

                // Merge Total CANTIDAD (filas 2-3)
                $sheet->mergeCells(\PHPExcel_Cell::stringFromColumnIndex($col_total_cant) . '2:' . \PHPExcel_Cell::stringFromColumnIndex($col_total_cant) . '3');

                // Merge titulo IMPORTE (Fila 2)
                $sheet->mergeCells(\PHPExcel_Cell::stringFromColumnIndex($col_start_imp) . '2:' . \PHPExcel_Cell::stringFromColumnIndex($col_end_imp) . '2');

                // Merge Total IMPORTE (filas 2-3)
                $sheet->mergeCells(\PHPExcel_Cell::stringFromColumnIndex($col_total_imp) . '2:' . \PHPExcel_Cell::stringFromColumnIndex($col_total_imp) . '3');
            });

            $tabs = [
                ['id' => 'SEC', 'title' => 'INGRESO DE SECADO INDUSTRIAL (ANEXO 02)', 'data' => $lista_secado],
                ['id' => 'PIL', 'title' => 'INGRESO DE PILADO (ANEXO 04)', 'data' => $lista_pilado],
                ['id' => 'SEL', 'title' => 'INGRESO DE SELECTORA (ANEXO 05)', 'data' => $lista_selectora],
                ['id' => 'EMB', 'title' => 'INGRESO DE EMBOLSADO (ANEXO 06)', 'data' => $lista_embolsado],
                ['id' => 'ANE', 'title' => 'INGRESO DE AÑEJAMIENTO (ANEXO 07)', 'data' => $lista_anejamiento],
                ['id' => 'MES', 'title' => 'INGRESO DE MEZCLA (ANEXO 08)', 'data' => $lista_mezcla],
                ['id' => 'REP', 'title' => 'INGRESO DE REPROCESO (ANEXO 09)', 'data' => $lista_reproceso],
                ['id' => 'COM', 'title' => 'INGRESOS DE COMPACTADOS (ANEXO 10)', 'data' => $lista_compactado],
                ['id' => 'PUL', 'title' => 'INGRESOS DE PULIDO (ANEXO 11)', 'data' => $lista_pulido],
            ];

            foreach ($tabs as $tab) {
                $excel->sheet($tab['id'], function ($sheet) use ($tab, $fechainicio, $fechafin) {
                    $sheet->loadView('reportes/auditoria/excel/generico', [
                        'title' => $tab['title'],
                        'data' => $tab['data'],
                        'fechainicio' => $fechainicio,
                        'fechafin' => $fechafin,
                    ]);
                    $sheet->setFreeze('A3'); // Congelar cabeceras
                });
            }
            // Colocar la cookie justo antes de disparar la exportacion
            setcookie('download_started', 'true', time() + 20, '/');
        })->export('xlsx');
    }
}
