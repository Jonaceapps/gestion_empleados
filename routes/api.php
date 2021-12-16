<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpleadosController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::prefix('usuarios')->group(function(){
    Route::post('/login',[EmpleadosController::class, 'login']);
    Route::get('/recoverPass',[EmpleadosController::class, 'recoverPass']);
});

Route::middleware(['login-api-token', 'permisos'])->get('/listado_empleados',[EmpleadosController::class, 'listado_empleados']);
Route::middleware(['login-api-token', 'permisos'])->get('/modificar_datos/{id}',[EmpleadosController::class, 'modificar_datos']);
Route::middleware(['login-api-token', 'permisos'])->get('/registro',[EmpleadosController::class, 'registro']);
Route::middleware(['login-api-token'])->get('/ver_perfil',[EmpleadosController::class, 'ver_perfil']);
Route::middleware(['login-api-token', 'permisos'])->get('/detalle_empleado/{id}',[EmpleadosController::class, 'detalle_empleado']);

    //Route::get('/validar_permisos', function(){}) -> middleware('permisos')

/*Route::middleware('permisos') -> prefix('usuarios2') -> group(function(){

        Route::put('/registro',[EmpleadosController::class, 'registro']);


});*/