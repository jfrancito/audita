<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

/********************** USUARIOS *************************/
// header('Access-Control-Allow-Origin:  *');
// header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
// header('Access-Control-Allow-Headers: *');

Route::group(['middleware' => ['guestaw']], function () {
	Route::any('/', 'UserController@actionLogin');
	Route::any('/login', 'UserController@actionLogin');
	Route::any('/acceso', 'UserController@actionAcceso');
	Route::any('/accesobienvenido/{idempresa}', 'UserController@actionAccesoBienvenido');
});

Route::get('/cerrarsession', 'UserController@actionCerrarSesion');
Route::get('/cambiarperfil', 'UserController@actionCambiarPerfil');

Route::group(['middleware' => ['authaw']], function () {
	Route::get('/bienvenido', 'UserController@actionBienvenido');
	Route::any('/gestion-de-concialicion-cobranza/{idopcion}', 'GestionConciliacionCobranzaController@actionListarConciliacionCobranza');
	Route::post('/ajax-listar-jefes-ventas', 'GestionConciliacionCobranzaController@actionAjaxListarJefesVentas');
	Route::post('/ajax-listar-clientes', 'GestionConciliacionCobranzaController@actionAjaxListarClientes');
	Route::post('/ajax-listar-conciliacion-cobranza', 'GestionConciliacionCobranzaController@actionAjaxListarConciliacionCobranza');
	Route::post('/reporte-conciliacion-cobranza-excel', 'GestionConciliacionCobranzaController@actionExportarConciliacionCobranzaExcel');



});

Route::get('/pruebaemail/{emailfrom}/{nombreusuario}', 'PruebasController@actionPruebaEmail');


Route::get('buscarcliente', function (Illuminate\Http\Request $request) {
	$term = $request->term ?: '';
	$tags = DB::table('STD.EMPRESA')
		->leftJoin('users', 'STD.EMPRESA.COD_EMPR', '=', 'users.usuarioosiris_id')
		->where('NOM_EMPR', 'like', '%' . $term . '%')
		->where('STD.EMPRESA.IND_PROVEEDOR', '=', 1)
		->where('STD.EMPRESA.COD_ESTADO', '=', 1)
		->whereNull('users.usuarioosiris_id')
		->take(100)
		->select(
			DB::raw("
			  STD.EMPRESA.NRO_DOCUMENTO + ' - '+ STD.EMPRESA.NOM_EMPR AS NOMBRE")
		)
		->pluck('NOMBRE', 'NOMBRE');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {
		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}
	return \Response::json($valid_tags);
});


Route::get('buscarempresa', function (Illuminate\Http\Request $request) {
	$term = $request->term ?: '';
	$tags = DB::table('STD.EMPRESA')
		->where('NOM_EMPR', 'like', '%' . $term . '%')
		//->where('STD.EMPRESA.IND_PROVEEDOR','=',1)
		->where('STD.EMPRESA.COD_ESTADO', '=', 1)
		->where('COD_TIPO_DOCUMENTO', '=', 'TDI0000000000006')
		->take(100)
		->select(
			DB::raw("
			  STD.EMPRESA.NRO_DOCUMENTO + ' - '+ STD.EMPRESA.NOM_EMPR AS NOMBRE")
		)
		->pluck('NOMBRE', 'NOMBRE');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {
		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}
	return \Response::json($valid_tags);
});

Route::get('buscarempresalg', function (Illuminate\Http\Request $request) {
	$term = $request->term ?: '';
	$tags = DB::table('STD.EMPRESA')
		->where(function ($query) use ($term) {
			$query->where('STD.EMPRESA.NOM_EMPR', 'like', '%' . $term . '%')
				->orWhere('STD.EMPRESA.NRO_DOCUMENTO', 'like', '%' . $term . '%');
		})
		->where('STD.EMPRESA.COD_ESTADO', '=', 1)
		->where('COD_TIPO_DOCUMENTO', '=', 'TDI0000000000006')
		->take(100)
		->select(
			DB::raw("
			  STD.EMPRESA.NRO_DOCUMENTO + ' - '+ STD.EMPRESA.NOM_EMPR AS NOMBRE")
		)
		->pluck('NOMBRE', 'NOMBRE');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {
		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}
	return \Response::json($valid_tags);
});

Route::get('buscarempresarenta', function (Illuminate\Http\Request $request) {
	$term = $request->term ?: '';
	$tags = DB::table('STD.EMPRESA')
		->where(function ($query) use ($term) {
			$query->where('STD.EMPRESA.NOM_EMPR', 'like', '%' . $term . '%')
				->orWhere('STD.EMPRESA.NRO_DOCUMENTO', 'like', '%' . $term . '%');
		})
		->where('COD_TIPO_DOCUMENTO', '=', 'TDI0000000000006')
		->where('STD.EMPRESA.COD_ESTADO', '=', 1)
		->where('STD.EMPRESA.NRO_DOCUMENTO', 'like', '1%')
		->take(100)
		->select(
			DB::raw("
			  STD.EMPRESA.NRO_DOCUMENTO + ' - '+ STD.EMPRESA.NOM_EMPR AS NOMBRE")
		)
		->pluck('NOMBRE', 'NOMBRE');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {
		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}
	return \Response::json($valid_tags);
});



Route::get('buscarproducto', function (Illuminate\Http\Request $request) {
	$term = $request->term ?: '';
	$tags = DB::table('ALM.PRODUCTO')
		->where('NOM_PRODUCTO', 'like', '%' . $term . '%')
		->where('ALM.PRODUCTO.COD_ESTADO', '=', 1)
		->where('ALM.PRODUCTO.IND_DISPONIBLE', '=', 1)
		->where('ALM.PRODUCTO.IND_MATERIAL_SERVICIO', '=', 'S')
		->where('COD_CATEGORIA_CLASE', '=', '1')
		->take(100)
		->select(
			DB::raw("
			  ALM.PRODUCTO.NOM_PRODUCTO")
		)
		->pluck('NOM_PRODUCTO', 'NOM_PRODUCTO');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {
		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}
	return \Response::json($valid_tags);
});





Route::get('buscarclientey', function (Illuminate\Http\Request $request) {


	$term = $request->term ?: '';

	print_r("1");

	$tags = DB::table('STD.EMPRESA')
		->leftJoin('users', 'STD.EMPRESA.COD_EMPR', '=', 'users.usuarioosiris_id')
		->whereNull('users.usuarioosiris_id')
		->where('STD.EMPRESA.IND_PROVEEDOR', '=', 1)
		->where('STD.EMPRESA.NOM_EMPR', 'like', '%' . $term . '%')
		->where('STD.EMPRESA.COD_ESTADO', '=', 1)
		->select('STD.EMPRESA.COD_EMPR', 'STD.EMPRESA.NOM_EMPR')
		->select(
			DB::raw("
									  STD.EMPRESA.COD_EMPR,
									  STD.EMPRESA.NRO_DOCUMENTO + ' - '+ STD.EMPRESA.NOM_EMPR AS NOMBRE")
		)
		->take(10)
		->pluck('NOMBRE', 'NOMBRE');
	$valid_tags = [];
	foreach ($tags as $id => $tag) {

		$valid_tags[] = ['id' => $id, 'text' => $tag];
	}


	return \Response::json($valid_tags);
});



