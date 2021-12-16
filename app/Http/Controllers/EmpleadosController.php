<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class EmpleadosController extends Controller
{
    
    public function registro(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $validator = Validator::make(json_decode($req->
        getContent(),true), [
            "nombre" => 'required|max:50',
            "email" => 'required|email|unique:App\Models\User,email|max:30',
            "pass" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            "puesto_trabajo" => 'required|in:Direccion,RRHH,Empleado',
            "salario" => 'required|numeric',
            "biografia" => 'required|max:100'
        ]);

        if($validator -> fails()){
            $respuesta["status"] = 0;
            $respuesta["msg"] = $validator->errors(); 
        } else {

            $datos = $req -> getContent();
            $datos = json_decode($datos); 
    
            $usuario = new User();
            $usuario -> nombre = $datos -> nombre;
            $usuario -> email = $datos -> email;
            $usuario -> pass = Hash::make($datos->pass);
            $usuario -> puesto_trabajo = $datos -> puesto_trabajo;
            $usuario -> salario = $datos -> salario;
            $usuario -> biografia = $datos -> biografia;

            try {
                $usuario->save();
                $respuesta["msg"] = "Usuario Guardado";
            }catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
            }
        }  
        return response()->json($respuesta);
    }

    public function listado_empleados(Request $request){

        $respuesta = ["status" => 1, "msg" => ""];

        if ($request->usuario->puesto_trabajo == 'Direccion'){

            $empleados = DB::table('usuarios')
                ->whereIn('usuarios.puesto_trabajo', ['Empleado', 'RRHH'])
                ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario')
                ->get(); 
           $respuesta['listado_empleados'] = $empleados;

        } elseif ($request->usuario->puesto_trabajo == 'RRHH'){

            $empleados = DB::table('usuarios')
                ->where('usuarios.puesto_trabajo', 'Empleado')
                ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario')
                ->get(); 
            $respuesta['listado_empleados'] = $empleados;

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error";
        }

        return response()->json($respuesta);
    }

    public function detalle_empleado(Request $request, $id){

        $respuesta = ["status" => 1, "msg" => ""];
        $usuario = User::find($id);
        $mostrar = false;

        if($usuario){

            if ($request->usuario->puesto_trabajo == 'Direccion'){

                if($usuario -> puesto_trabajo != "Direccion")
                    $mostrar = true;
    
                    if($mostrar) {
                        $empleado = DB::table('usuarios')
                            ->whereIn('usuarios.puesto_trabajo', ['Empleado', 'RRHH'])
                            ->where('usuarios.id',$usuario->id)
                            ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario')
                            ->first(); 
                        $respuesta['detalle_empleado'] = $empleado;
                    } else  {
                        $respuesta["status"] = 0;
                        $respuesta["msg"] = "No puedes ver los datos de directivos";
                    }

            } elseif ($request->usuario->puesto_trabajo == 'RRHH'){

                if($usuario -> puesto_trabajo != "Direccion" && $usuario -> puesto_trabajo != "RRHH")
                    $mostrar = true;   
        
                    if($mostrar) {
                        $empleado = DB::table('usuarios')
                            ->where('usuarios.puesto_trabajo', 'Empleado')
                            ->where('usuarios.id',$usuario->id)
                            ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario')
                            ->first();
                        $respuesta['detalle_empleado'] = $empleado;
                    } else {
                        $respuesta["status"] = 0;
                        $respuesta["msg"] = "No puedes ver los datos de directivos o de RRHH";
                    }

            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error";
            }
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Usuario no encontrado";
        }

        return response()->json($respuesta);
    }

    public function ver_perfil(Request $request){

        $respuesta = ["status" => 1, "msg" => ""];
        $perfil = $usuario = User::find($request->usuario->id);

        if($perfil){
            $perfil -> makevisible( 'pass');
            $respuesta['datos_perfil'] = $perfil;
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error";  
        }
        return response()->json($respuesta);
    }

    public function modificar_datos(Request $request,$id){

        $respuesta = ["status" => 1, "msg" => ""];

        $usuario = User::find($id);
        $editar = false;
        $validator = Validator::make(json_decode($request->
        getContent(),true), [
            "nombre" => 'max:50',
            "email" => 'email|unique:App\Models\User,email|max:30',
            "pass" => "regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/",
            "puesto_trabajo" => 'in:Direccion,RRHH,Empleado',
            "salario" => 'numeric',
            "biografia" => 'max:100'
        ]);
    
        if($usuario){

            if($request->usuario->puesto_trabajo == 'Direccion'){

                if ($usuario -> puesto_trabajo == "Direccion" && $request->usuario->id == $usuario -> id)
                $editar = true;
                elseif($usuario -> puesto_trabajo == "Direccion")
                    $editar = false;
                else 
                    $editar = true;
                
            } elseif($request->usuario->puesto_trabajo == 'RRHH'){

                if($usuario -> puesto_trabajo == "Direccion" || $usuario -> puesto_trabajo == "RRHH")
                    $editar = false;   
                else 
                    $editar = true;          
            } else {
                $editar = false; 
            }

            if($validator -> fails()){
                $respuesta["status"] = 0;
                $respuesta["msg"] = $validator->errors();  
            } else {

                if ($editar == true){

                    $datos = $request -> getContent();
                    $datos = json_decode($datos); 
                
                    if(isset($datos->nombre))
                    $usuario -> nombre = $datos->nombre;
                    if(isset($datos->email))
                    $usuario -> email = $datos->email;
                    if(isset($datos->pass))
                    $usuario -> pass = $datos->pass;
                    if(isset($datos->puesto_trabajo))
                    $usuario -> puesto_trabajo = $datos->puesto_trabajo;
                    if(isset($datos->salario))
                    $usuario -> salario = $datos->salario;
                    if(isset($datos->biografia))
                    $usuario -> biografia = $datos->biografia;
        
                    try {
                        $usuario->save();
                        $respuesta["msg"] = "Cambios realizados.";
                    }catch (\Exception $e) {
                        $respuesta["status"] = 0;
                        $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
                    }
                    
                } else {
                        $respuesta["status"] = 0;
                        $respuesta["msg"] = "No tienes permisos para editar a este usuario";
                }   
            } 
    } else {
        $respuesta["msg"] = "Usuario no encontrado"; 
        $respuesta["status"] = 0;
    }

        return response()->json($respuesta);  
    }

    public function login(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $email = $req->email;
        $usuario = User::where('email', $email) -> first();

        if ($usuario){

            if (Hash::check($req->pass, $usuario -> pass)){

                do {
                    $token = Hash::make($usuario->id.now());
                } while(User::where('api_token', $token) -> first());

                $usuario -> api_token = $token;
                $usuario -> save();
                $respuesta["msg"] = "Login correcto".$usuario -> api_token;  

            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "La contraseña no es correcta";  
            }

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Usuario no encontrado";  
        }

        return response()->json($respuesta);  


    }

    //Recuperar pass

    //Mail::to($usuario->email)->send(new Notification($passwordGenerada));

}
