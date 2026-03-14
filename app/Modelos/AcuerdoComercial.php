<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class AcuerdoComercial extends Model
{
    protected $table = 'ACUERDO_COMERCIALES';
    public $timestamps = false;

    public function scopeEmpresa($query, $nom_empresa)
    {
        if (trim($nom_empresa) != 'TODAS' && trim($nom_empresa) != '') {
            $query->where('EMPRESA', '=', $nom_empresa);
        }
    }
}
