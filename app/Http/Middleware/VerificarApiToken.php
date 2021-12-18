<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VerificarApiToken
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
        $apiToken = $request -> api_token;
        $usuario = User::where('api_token', $apiToken)->first();
        
        if(!$usuario || empty($apiToken)){
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Usuario no encontrado o api token incorrecta";  
        } else {
            $respuesta["msg"] = "Api token OK";
            $request -> usuario = $usuario;
            return $next($request);
        }

        return response()->json($respuesta);  
    }
}
