<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ValidarPermisoUsuario
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $respuesta = ["status" => 1, "msg" => ""];
        if($request->usuario->puesto_trabajo == 'Direccion' 
        || $request->usuario->puesto_trabajo == 'RRHH'){
            $respuesta["msg"] = "Todo Ok, tienes los permisos";
            return $next($request);
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Este usuario no tiene los permisos";  
        }
        return response()->json($respuesta);  
    }
}
