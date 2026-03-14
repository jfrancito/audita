<?php

namespace App\Traits;

use App\Modelos\AcuerdoComercial;
use Illuminate\Support\Facades\DB;

trait ReportesAcuerdoComercialTraits
{
    public function rep_acuerdo_comercial_buscar($fechainicio, $fechafin, $nom_empresa_filtro = '')
    {
        $f_ini = date('Y-m-d', strtotime($fechainicio));
        $f_fin = date('Y-m-d', strtotime($fechafin));

        $res = AcuerdoComercial::from('ACUERDO_COMERCIALES (NOLOCK)')
            ->empresa($nom_empresa_filtro)
            ->whereRaw("CONVERT(DATE, FEC_EMISION) BETWEEN ? AND ?", [$f_ini, $f_fin])
            ->orderBy('FEC_EMISION', 'ASC')
            ->get();

        return $res;
    }

    public function rep_acuerdo_comercial_lista_empresas()
    {
        return AcuerdoComercial::from('ACUERDO_COMERCIALES (NOLOCK)')
            ->select('EMPRESA')
            ->distinct()
            ->where('EMPRESA', '<>', '')
            ->whereNotNull('EMPRESA')
            ->orderBy('EMPRESA')
            ->get();
    }
}
