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

trait ReportesTraits
{

    public function rep_lista_empresas_combo()
    {
        $query = "SELECT '' AS COD_EMPR, 'TODAS' AS NOM_EMPR
				  UNION ALL
				  SELECT COD_EMPR, NOM_EMPR
				  FROM STD.EMPRESA WITH (NOLOCK)
				  WHERE (IND_SISTEMA = 1) AND (COD_ESTADO = 1)";

        $res = DB::select(DB::raw($query));
        return $res;
    }

    public function rep_lista_centros_combo()
    {
        $lista = array();

        $item = new \stdClass();
        $item->COD_CENTRO = '-1';
        $item->NOM_CENTRO = 'Seleccionar';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_CENTRO = 'TOD';
        $item->NOM_CENTRO = 'TODOS';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_CENTRO = 'LIM';
        $item->NOM_CENTRO = 'LIMA';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_CENTRO = 'NOR';
        $item->NOM_CENTRO = 'NORTE';
        $lista[] = $item;

        return $lista;
    }

    public function rep_lista_zonas_comerciales_combo()
    {
        $lista = array();

        $item = new \stdClass();
        $item->COD_ZONA = '-1';
        $item->NOM_ZONA = 'Seleccionar';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_ZONA = '';
        $item->NOM_ZONA = 'TODOS';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_ZONA = 'CEN0000000000002';
        $item->NOM_ZONA = 'LIMA';
        $lista[] = $item;

        $item = new \stdClass();
        $item->COD_ZONA = 'CEN0000000000001';
        $item->NOM_ZONA = 'NORTE';
        $lista[] = $item;

        return $lista;
    }

    public function rep_lista_jefes_ventas_combo($cod_zona)
    {
        $query = "SELECT '-1' AS COD, 'Seleccionar' AS NOMBRE, 0 AS ORDEN
				  UNION ALL
                  SELECT '' AS COD, ' TODOS' AS NOMBRE, 1 AS ORDEN
				  UNION ALL
				  SELECT COD_CATEGORIA AS COD, NOM_CATEGORIA AS NOMBRE, 2 AS ORDEN
				  FROM CMP.CATEGORIA
				  WHERE TXT_GRUPO LIKE 'JEFE_VENTA' 
                  AND IND_OPERACION_AUTO = 1 
                  AND TXT_ABREVIATURA = IIF(? <> '', ?, TXT_ABREVIATURA) 
                  AND IND_DOCUMENTO_VAL = 1 
                  AND COD_ESTADO = 1
				  ORDER BY ORDEN, NOMBRE";

        $res = DB::select(DB::raw($query), [$cod_zona, $cod_zona]);
        return $res;
    }

    public function rep_lista_clientes_combo($cod_jefe_venta)
    {
        $query = "SELECT '-1' AS COD_EMPR_CLI, 'Seleccionar' AS NOM_EMPR_CLI, 0 AS ORDEN
                  UNION ALL
                  SELECT '' AS COD_EMPR_CLI, ' TODOS' AS NOM_EMPR_CLI, 1 AS ORDEN
                  UNION ALL
                  SELECT NOM_EMPR_CLI, COD_EMPR_CLI, 2 AS ORDEN
                  FROM (SELECT MIN(LTRIM(E.NOM_EMPR)) AS NOM_EMPR_CLI, E.COD_EMPR AS COD_EMPR_CLI
                        FROM STD.EMPRESA AS E 
                        INNER JOIN CMP.CONTRATO AS CO ON CO.COD_EMPR_CLIENTE = E.COD_EMPR 
                                AND CO.COD_ESTADO = 1 
                                AND CO.COD_CATEGORIA_TIPO_CONTRATO = 'TCO0000000000068' 
                                AND CO.COD_CATEGORIA_JEFE_VENTA = ?
                        WHERE (E.COD_ESTADO = 1) 
                                AND (CO.COD_CATEGORIA_ESTADO_CONTRATO IN ('ECO0000000000001', 'ECO0000000000002'))
                        GROUP BY E.COD_EMPR) AS T
                  ORDER BY ORDEN, NOM_EMPR_CLI";

        $res = DB::select(DB::raw($query), [$cod_jefe_venta]);
        return $res;
    }

    public function rep_conciliacion_cobranza_buscar($fechainicio, $fechafin, $cod_zona, $cod_jefe_venta, $cod_empresa, $cod_centro, $cod_cliente)
    {
        $f_ini = date('Y-m-d', strtotime($fechainicio));
        $f_fin = date('Y-m-d', strtotime($fechafin));

        $res = DB::select("EXEC WebConciliacionCobranza 
            @FEC_INI = ?,
            @FEC_FIN = ?,
            @ZONA = ?,
            @VENDEDOR = ?,
            @COD_EMPR = ?,
            @COD_CENTRO = ?,
            @CLIENTE = ?",
            [$f_ini, $f_fin, (string) $cod_zona, (string) $cod_jefe_venta, (string) $cod_empresa, (string) $cod_centro, (string) $cod_cliente]
        );

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_ventas($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        // User requested +1 day to fechainicio
        $date_ini->modify('+1 day');

        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT		DOC.COD_DOCUMENTO_CTBLE,DOC.NRO_SERIE,DOC.NRO_DOC,DOC.TXT_EMPR_EMISOR,DOC.TXT_EMPR_RECEPTOR,
                        DOC.TXT_CATEGORIA_TIPO_DOC,DOC.TXT_CATEGORIA_MONEDA,DOC.FEC_EMISION,DOC.IND_MATERIAL_SERVICIO,
                        DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE,DOC.TXT_CATEGORIA_TIPO_PAGO,DOC.TXT_GLOSA,DOC.COD_USUARIO_CREA_AUD,
                        DOC.COD_ESTADO,DOC.COD_PERIODO,HAB.COD_CONTRATO,HAB.TXT_REFERENCIA,
                        IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HAB.IND_ABONO_CARGO = 'C', 1, -1) * HAB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
            INNER JOIN CMP.HABILITACION(NOLOCK) HAB ON DOC.COD_DOCUMENTO_CTBLE = HAB.COD_DOCUMENTO_CTBLE AND HAB.COD_ESTADO =1
            WHERE HAB.COD_CONTRATO IN ($placeholders)
            AND FEC_HABILITACION BETWEEN ? AND ?
            AND DOC.COD_ESTADO = 1
            AND DOC.IND_COMPRA_VENTA = 'V'
            AND HAB.COD_PRODUCTO = 'PRD0000000004739'
        ";

        $params = array_merge($listado_contratos, [$f_ini_sql, $f_fin_sql]);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_nd($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        // User requested +1 day to fechainicio
        $date_ini->modify('+1 day');

        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
			SELECT
				DOC.COD_DOCUMENTO_CTBLE
			   ,DOC.NRO_SERIE
			   ,DOC.NRO_DOC
			   ,DOC.TXT_EMPR_EMISOR
			   ,DOC.TXT_EMPR_RECEPTOR
			   ,DOC.TXT_CATEGORIA_TIPO_DOC
			   ,DOC.TXT_CATEGORIA_MONEDA
			   ,DOC.FEC_EMISION
			   ,DOC.IND_MATERIAL_SERVICIO
			   ,DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE
			   ,DOC.TXT_CATEGORIA_TIPO_PAGO
			   ,DOC.TXT_GLOSA
			   ,DOC.COD_USUARIO_CREA_AUD
			   ,DOC.COD_ESTADO
			   ,DOC.COD_PERIODO
			   ,DOC.TXT_REFERENCIA
			   ,DOC.COD_CONTRATO_DOC AS COD_CONTRATO
			   ,IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(AM.CAN_DEBE_MN > 0, 1, -1) * DP.CAN_VALOR_VENTA_IGV AS CAN_IMPORTE
			   ,ISNULL(CAT_SUP.COD_CATEGORIA, '') AS CAT_SUP_COD_CATEGORIA
			   ,ISNULL(CAT_INF.COD_CATEGORIA, '') AS CAT_INF_COD_CATEGORIA
			   ,IIF(DOC.IND_MATERIAL_SERVICIO = 'S', CAT_INF.NOM_CATEGORIA, CAT_SUP.NOM_CATEGORIA) AS CATEGORIA
			   ,IIF(DOC.IND_MATERIAL_SERVICIO = 'S', CAT_INF.NOM_CATEGORIA, CAT_INF.NOM_CATEGORIA) AS SUB_CATEGORIA
			   ,DP.TXT_NOMBRE_PRODUCTO
			   ,DP.CAN_PRODUCTO
			   ,DP.CAN_PRECIO_UNIT
			   ,DOC.TXT_EMPR_DOC
			   ,ISNULL(ROUND((DP.CAN_PESO_PRODUCTO * DP.CAN_PRODUCTO) / 50, 2) * IIF(RTRIM(LTRIM(A.COD_ASIENTO_EXTORNADO)) <> '', -1, 1), 0) AS PESO_ORDEN_50
			   ,E.NOM_EMPR
			   ,E.TXT_ABREVIATURA
			   ,P.COD_CATEGORIA_FAMILIA
			   ,P.COD_CATEGORIA_SUB_FAMILIA
               ,DOC.COD_CONTRATO_DOC AS COD_CONTRATO
			FROM CMP.DOCUMENTO_CTBLE DOC
						LEFT JOIN ALM.CENTRO(NOLOCK) CEN
				ON DOC.COD_CENTRO = CEN.COD_CENTRO

			LEFT JOIN CMP.CATEGORIA CAT_MOD
				ON DOC.COD_CATEGORIA_MODULO = CAT_MOD.COD_CATEGORIA

			INNER JOIN CMP.CATEGORIA(NOLOCK) CAT
				ON DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE = CAT.COD_CATEGORIA
			LEFT JOIN CON.ASIENTO(NOLOCK) A
				ON DOC.COD_DOCUMENTO_CTBLE = A.TXT_REFERENCIA
				AND A.COD_ESTADO = 1
			INNER JOIN CON.PERIODO(NOLOCK) PE
				ON DOC.COD_PERIODO = PE.COD_PERIODO
			INNER JOIN CON.ASIENTO_MOVIMIENTO(NOLOCK) AM
				ON AM.COD_ASIENTO = A.COD_ASIENTO
				AND AM.COD_ESTADO = 1
			INNER JOIN CON.ASIENTO_MOVIMIENTO_DOCUMENTO(NOLOCK) AMD
				ON AM.COD_ASIENTO_MOVIMIENTO = AMD.COD_ASIENTO_MOVIMIENTO
				AND AMD.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
				AND AMD.COD_ESTADO = 1
			INNER JOIN CMP.DETALLE_PRODUCTO(NOLOCK) DP
				ON DOC.COD_DOCUMENTO_CTBLE = DP.COD_TABLA
				AND DP.COD_ESTADO = 1
			INNER JOIN ALM.PRODUCTO(NOLOCK) P
				ON DP.COD_PRODUCTO = P.COD_PRODUCTO
			LEFT JOIN CMP.CATEGORIA CAT_INF
				ON CAT_INF.COD_CATEGORIA = IIF(DOC.IND_MATERIAL_SERVICIO = 'S', P.COD_CATEGORIA_SERVICIO, P.COD_CATEGORIA_SUB_FAMILIA)
			LEFT JOIN CMP.CATEGORIA CAT_SUP
				ON CAT_INF.COD_CATEGORIA_SUP = CAT_SUP.COD_CATEGORIA
			LEFT JOIN CMP.ORDEN(NOLOCK) OV
				ON OV.COD_ORDEN = DOC.TXT_REFERENCIA
				AND DOC.TXT_TIPO_REFERENCIA = 'CMP.ORDEN'
			LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
				ON C_OV.COD_CONTRATO = OV.COD_CONTRATO
				AND C_OV.COD_ESTADO = 1
			LEFT JOIN ALM.CENTRO(NOLOCK) C
				ON C_OV.COD_CENTRO = C.COD_CENTRO
			LEFT JOIN STD.REPRESENTANTE_VENTA_CUOTA(NOLOCK) RVC
				ON RVC.COD_CATEGORIA_REPVEN = C_OV.COD_CATEGORIA_JEFE_VENTA COLLATE SQL_Latin1_General_CP1_CI_AS
				AND RVC.COD_PERIODO = PE.COD_PERIODO COLLATE SQL_Latin1_General_CP1_CI_AS
				AND RVC.IND_COMISION = 1
			LEFT JOIN STD.EMPRESA(NOLOCK) E
				ON E.COD_EMPR = DOC.COD_EMPR
			WHERE DOC.COD_ESTADO = 1
			AND DOC.IND_COMPRA_VENTA = 'V'
			AND DOC.COD_CATEGORIA_TIPO_DOC IN (SELECT
					TD.COD_TIPO_DOCUMENTO
				FROM STD.TIPO_DOCUMENTO(NOLOCK) TD
				WHERE TD.TXT_INDICADOR = 3
				AND TD.COD_TIPO_DOCUMENTO IN ('TDO0000000000020')--INTERNOS
			)
			AND DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE NOT IN ('EDC0000000000012')
			AND doc.FEC_EMISION BETWEEN ? AND ?
			AND doc.COD_CONTRATO_DOC IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_pagos($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT 
                DOC.COD_DOCUMENTO_CTBLE, DOC.NRO_SERIE, DOC.NRO_DOC, DOC.TXT_EMPR_EMISOR, DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC, DOC.TXT_CATEGORIA_MONEDA, DOC.FEC_EMISION, DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE, DOC.TXT_CATEGORIA_TIPO_PAGO, DOC.TXT_GLOSA, DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO, DOC.COD_PERIODO, HB.COD_CONTRATO, HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_RV
                ON C_RV.COD_CATEGORIA = C_OV.COD_CATEGORIA_JEFE_VENTA
            LEFT JOIN ALM.CENTRO(NOLOCK) C
                ON C_RV.TXT_ABREVIATURA = C.COD_CENTRO
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_CA
                ON C_CA.COD_CATEGORIA = C_OV.COD_CATEGORIA_CANAL_VENTA
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_SCA
                ON C_SCA.COD_CATEGORIA = C_OV.COD_CATEGORIA_SUB_CANAL
            LEFT JOIN STD.EMPRESA E
                ON E.COD_EMPR = HB.COD_EMPR
                    AND E.COD_ESTADO = 1
            LEFT JOIN STD.EMPRESA EC
                ON EC.COD_EMPR = C_OV.COD_EMPR_CLIENTE
                    AND EC.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE
                HB.COD_PRODUCTO IN('PRD0000000009443')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND AM.IND_EXTORNO=0
                AND DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE NOT IN ('EDC0000000000012')
                AND C_OV.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_caja_banco($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT 
                DOC.COD_DOCUMENTO_CTBLE, DOC.NRO_SERIE, DOC.NRO_DOC, DOC.TXT_EMPR_EMISOR, DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC, DOC.TXT_CATEGORIA_MONEDA, DOC.FEC_EMISION, DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE, DOC.TXT_CATEGORIA_TIPO_PAGO, DOC.TXT_GLOSA, DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO, DOC.COD_PERIODO, HB.COD_CONTRATO, HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_RV
                ON C_RV.COD_CATEGORIA = C_OV.COD_CATEGORIA_JEFE_VENTA
            LEFT JOIN ALM.CENTRO(NOLOCK) C
                ON C_RV.TXT_ABREVIATURA = C.COD_CENTRO
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_CA
                ON C_CA.COD_CATEGORIA = C_OV.COD_CATEGORIA_CANAL_VENTA
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_SCA
                ON C_SCA.COD_CATEGORIA = C_OV.COD_CATEGORIA_SUB_CANAL
            LEFT JOIN STD.EMPRESA E
                ON E.COD_EMPR = HB.COD_EMPR
                    AND E.COD_ESTADO = 1
            LEFT JOIN STD.EMPRESA EC
                ON EC.COD_EMPR = C_OV.COD_EMPR_CLIENTE
                    AND EC.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE
                HB.COD_PRODUCTO IN( 'PRD0000000009442')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND C_OV.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_nc($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT 
                DOC.COD_DOCUMENTO_CTBLE, DOC.NRO_SERIE, DOC.NRO_DOC, DOC.TXT_EMPR_EMISOR, DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC, DOC.TXT_CATEGORIA_MONEDA, DOC.FEC_EMISION, DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE, DOC.TXT_CATEGORIA_TIPO_PAGO, DOC.TXT_GLOSA, DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO, DOC.COD_PERIODO, HB.COD_CONTRATO, HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            WHERE
                HB.COD_PRODUCTO IN('PRD0000000004752')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND HB.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_adelanto($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT 
                DOC.COD_DOCUMENTO_CTBLE, DOC.NRO_SERIE, DOC.NRO_DOC, DOC.TXT_EMPR_EMISOR, DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC, DOC.TXT_CATEGORIA_MONEDA, DOC.FEC_EMISION, DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE, DOC.TXT_CATEGORIA_TIPO_PAGO, DOC.TXT_GLOSA, DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO, DOC.COD_PERIODO, HB.COD_CONTRATO, HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_RV
                ON C_RV.COD_CATEGORIA = C_OV.COD_CATEGORIA_JEFE_VENTA
            LEFT JOIN ALM.CENTRO(NOLOCK) C
                ON C_RV.TXT_ABREVIATURA = C.COD_CENTRO
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_CA
                ON C_CA.COD_CATEGORIA = C_OV.COD_CATEGORIA_CANAL_VENTA
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_SCA
                ON C_SCA.COD_CATEGORIA = C_OV.COD_CATEGORIA_SUB_CANAL
            LEFT JOIN STD.EMPRESA E
                ON E.COD_EMPR = HB.COD_EMPR
                    AND E.COD_ESTADO = 1
            LEFT JOIN STD.EMPRESA EC
                ON EC.COD_EMPR = C_OV.COD_EMPR_CLIENTE
                    AND EC.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE
                HB.COD_PRODUCTO IN('PRD0000000004736')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND HB.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_extorno($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT
                DOC.COD_DOCUMENTO_CTBLE,DOC.NRO_SERIE,DOC.NRO_DOC,DOC.TXT_EMPR_EMISOR,DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC,DOC.TXT_CATEGORIA_MONEDA,DOC.FEC_EMISION,DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE,DOC.TXT_CATEGORIA_TIPO_PAGO,DOC.TXT_GLOSA,DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO,DOC.COD_PERIODO,HB.COD_CONTRATO,HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE HB.COD_PRODUCTO IN('PRD0000000004175')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND DOC.IND_COMPRA_VENTA = 'V'
                AND AM.IND_EXTORNO=1
                AND DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE IN ('EDC0000000000012', 'EDC0000000000003')
                AND C_OV.COD_CONTRATO IN ($placeholders)

            UNION ALL

            SELECT
                DOC.COD_DOCUMENTO_CTBLE,DOC.NRO_SERIE,DOC.NRO_DOC,DOC.TXT_EMPR_EMISOR,DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC,DOC.TXT_CATEGORIA_MONEDA,DOC.FEC_EMISION,DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE,DOC.TXT_CATEGORIA_TIPO_PAGO,DOC.TXT_GLOSA,DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO,DOC.COD_PERIODO,HB.COD_CONTRATO,HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION(NOLOCK) HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE HB.COD_PRODUCTO IN('PRD0000000004175')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND DOC.IND_COMPRA_VENTA = 'V'
                AND AM.IND_EXTORNO=1
                AND DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE IN ('EDC0000000000002')
                AND C_OV.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos, [$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_regularizacion($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT
                DOC.COD_DOCUMENTO_CTBLE,DOC.NRO_SERIE,DOC.NRO_DOC,DOC.TXT_EMPR_EMISOR,DOC.TXT_EMPR_RECEPTOR,
                DOC.TXT_CATEGORIA_TIPO_DOC,DOC.TXT_CATEGORIA_MONEDA,DOC.FEC_EMISION,DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE,DOC.TXT_CATEGORIA_TIPO_PAGO,DOC.TXT_GLOSA,DOC.COD_USUARIO_CREA_AUD,
                DOC.COD_ESTADO,DOC.COD_PERIODO,HB.COD_CONTRATO,HB.TXT_REFERENCIA,
                IIF(HB.IND_ABONO_CARGO='C',HB.CAN_IMPORTE,HB.CAN_IMPORTE*-1) AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_RV
                ON C_RV.COD_CATEGORIA = C_OV.COD_CATEGORIA_JEFE_VENTA
            LEFT JOIN ALM.CENTRO(NOLOCK) C
                ON C_RV.TXT_ABREVIATURA = C.COD_CENTRO
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_CA
                ON C_CA.COD_CATEGORIA = C_OV.COD_CATEGORIA_CANAL_VENTA
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_SCA
                ON C_SCA.COD_CATEGORIA = C_OV.COD_CATEGORIA_SUB_CANAL
            LEFT JOIN STD.EMPRESA E
                ON E.COD_EMPR = HB.COD_EMPR
                    AND E.COD_ESTADO = 1
            LEFT JOIN STD.EMPRESA EC
                ON EC.COD_EMPR = C_OV.COD_EMPR_CLIENTE
                    AND EC.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE
                HB.COD_PRODUCTO IN('PRD0000000014585')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND AM.IND_EXTORNO=0
                AND DOC.COD_CATEGORIA_ESTADO_DOC_CTBLE NOT IN ('EDC0000000000012')
                AND HB.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_compensacion($fechainicio, $fechafin, $listado_contratos)
    {
        $date_ini = \DateTime::createFromFormat('d-m-Y', $fechainicio);
        $date_fin = \DateTime::createFromFormat('d-m-Y', $fechafin);

        if (!$date_ini || !$date_fin) {
            return [];
        }

        $date_ini->modify('+1 day');
        $f_ini_sql = $date_ini->format('Ymd');
        $f_fin_sql = $date_fin->format('Ymd');

        if (empty($listado_contratos)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($listado_contratos), '?'));

        $query = "
            SELECT
                DOC.COD_DOCUMENTO_CTBLE,DOC.NRO_SERIE,DOC.NRO_DOC,DOC.TXT_EMPR_EMISOR,DOC.TXT_EMPR_RECEPTOR,DOC.TXT_CATEGORIA_TIPO_DOC,DOC.TXT_CATEGORIA_MONEDA,DOC.FEC_EMISION,DOC.IND_MATERIAL_SERVICIO,
                DOC.TXT_CATEGORIA_ESTADO_DOC_CTBLE,DOC.TXT_CATEGORIA_TIPO_PAGO,DOC.TXT_GLOSA,DOC.COD_USUARIO_CREA_AUD,DOC.COD_ESTADO,DOC.COD_PERIODO,HB.COD_CONTRATO,HB.TXT_REFERENCIA,
                IIF(DOC.COD_CATEGORIA_MONEDA = 'MON0000000000001', 1, DOC.CAN_TIPO_CAMBIO) * IIF(HB.IND_ABONO_CARGO = 'C', 1, -1) * HB.CAN_IMPORTE AS CAN_IMPORTE
            FROM CMP.HABILITACION HB
            LEFT JOIN CMP.DOCUMENTO_CTBLE(NOLOCK) DOC
                ON HB.COD_DOCUMENTO_CTBLE = DOC.COD_DOCUMENTO_CTBLE
            LEFT JOIN CMP.CONTRATO C_OV (NOLOCK)
                ON C_OV.COD_CONTRATO = HB.COD_CONTRATO
                    AND C_OV.COD_ESTADO = 1
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_RV
                ON C_RV.COD_CATEGORIA = C_OV.COD_CATEGORIA_JEFE_VENTA
            LEFT JOIN ALM.CENTRO(NOLOCK) C
                ON C_RV.TXT_ABREVIATURA = C.COD_CENTRO
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_CA
                ON C_CA.COD_CATEGORIA = C_OV.COD_CATEGORIA_CANAL_VENTA
            LEFT JOIN CMP.CATEGORIA(NOLOCK) C_SCA
                ON C_SCA.COD_CATEGORIA = C_OV.COD_CATEGORIA_SUB_CANAL
            LEFT JOIN STD.EMPRESA E
                ON E.COD_EMPR = HB.COD_EMPR
                    AND E.COD_ESTADO = 1
            LEFT JOIN STD.EMPRESA EC
                ON EC.COD_EMPR = C_OV.COD_EMPR_CLIENTE
                    AND EC.COD_ESTADO = 1
            LEFT JOIN CON.ASIENTO_MOVIMIENTO AM ON AM.COD_ASIENTO_MOVIMIENTO=HB.COD_ASIENTO_MOVIMIENTO
            WHERE
                HB.COD_PRODUCTO IN( 'PRD0000000013955')
                AND HB.COD_ESTADO = 1
                AND HB.FEC_HABILITACION BETWEEN ? AND ?
                AND DOC.COD_ESTADO = 1
                AND HB.COD_CONTRATO IN ($placeholders)
        ";

        $params = array_merge([$f_ini_sql, $f_fin_sql], $listado_contratos);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

    public function rep_conciliacion_cobranza_detalle_cartas($listado_cod_empr, $listado_cod_empr_cliente)
    {
        if (empty($listado_cod_empr) || empty($listado_cod_empr_cliente)) {
            return [];
        }

        $placeholders_empr = implode(',', array_fill(0, count($listado_cod_empr), '?'));
        $placeholders_cli = implode(',', array_fill(0, count($listado_cod_empr_cliente), '?'));

        $query = "
            SELECT DC.COD_DOC_COBRO, DC.FEC_OPERACION, DC.TXT_GLOSA,
                DDC.MONTO_TRANSICION * -1 AS TOTAL_P
            FROM TES.DOCUMENTO_COBRO DC
            LEFT JOIN TES.DETALLE_DOCUMENTO_COBRO DDC
                ON DC.COD_DOC_COBRO = DDC.COD_DOC_COBRO
            WHERE DC.COD_ESTADO = 1
                AND DC.COD_CATEGORIA_ESTADO = 'EOR0000000000014'
                AND DDC.COD_EMPR_TRANSICION IN ($placeholders_empr)
                AND DC.COD_EMPR_CLIENTE IN ($placeholders_cli)
        ";

        $params = array_merge($listado_cod_empr, $listado_cod_empr_cliente);
        $res = DB::select(DB::raw($query), $params);

        return $res;
    }

}