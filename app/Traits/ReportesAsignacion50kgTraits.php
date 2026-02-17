<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;
use View;
use Session;
use Hashids;
use Nexmo;
use Keygen;
use Mail;
use PDO;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

trait ReportesAsignacion50kgTraits
{
    public function rep_asignacion_50kg_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));

        $lista = DB::select('exec [RPS].ERG_INGRESO_SECADO_INDUSTRIAL_DETALLADO_IAIN 
                            @COD_EMPR=?, 
                            @FEC_INICIO=?, 
                            @FEC_FIN=?',
            [$cod_empresa, $f_ini, $f_fin]
        );
        return $lista;
    }

    public function rep_asignacion_pilado_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));

        $lista = DB::select('exec [RPS].ERG_INGRESO_PILADO_DETALLADO_IAIN 
                            @COD_EMPR=?, 
                            @FEC_INICIO=?, 
                            @FEC_FIN=?',
            [$cod_empresa, $f_ini, $f_fin]
        );
        return $lista;
    }

    public function rep_asignacion_selectora_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_SELECTORA_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_embolsado_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_EMBOLSADO_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_anejamiento_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_ANEJAMIENTO_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_mezcla_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_MEZCLA_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_reproceso_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_REPROCESO_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_compactado_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_COMPACTADO_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

    public function rep_asignacion_pulido_buscar($fechainicio, $fechafin, $cod_empresa)
    {
        $f_ini = date('d/m/Y', strtotime($fechainicio));
        $f_fin = date('d/m/Y', strtotime($fechafin));
        $lista = DB::select('exec [RPS].ERG_INGRESO_PULIDO_DETALLADO_IAIN @COD_EMPR=?, @FEC_INICIO=?, @FEC_FIN=?', [$cod_empresa, $f_ini, $f_fin]);
        return $lista;
    }

}