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

Route::middleware(['login-api-token', 'permisos']) -> prefix('usuarios') -> group(function(){

    Route::post('/login',[EmpleadosController::class, 'login'])->withoutMiddleware(['login-api-token', 'permisos']);
    Route::get('/recoverPass',[EmpleadosController::class, 'recoverPass'])->withoutMiddleware(['login-api-token', 'permisos']);
    Route::get('/listado_empleados',[EmpleadosController::class, 'listado_empleados']);
    Route::post('/modificar_datos/{id}',[EmpleadosController::class, 'modificar_datos']);
    Route::put('/registro',[EmpleadosController::class, 'registro']);
    Route::get('/ver_perfil',[EmpleadosController::class, 'ver_perfil'])->withoutMiddleware('permisos');
    Route::get('/detalle_empleado/{id}',[EmpleadosController::class, 'detalle_empleado']);

});