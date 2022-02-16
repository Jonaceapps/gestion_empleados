<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\recoverPass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class EmpleadosController extends Controller
{
    //Registrar empleado
    public function registro(Request $req){

        $respuesta = ["status" => 1, "msg" => ""]; 
        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $usuario = new User();
        $usuario -> nombre = $datos -> nombre;
        $usuario -> email = $datos -> email;
        $usuario -> pass = Hash::make($datos->pass);
        $usuario -> puesto_trabajo = $datos -> puesto_trabajo;
        $usuario -> salario = $datos -> salario;
        $usuario -> biografia = $datos -> biografia;

        $validator = Validator::make(json_decode($req->
        getContent(),true), [
            "nombre" => 'required|unique:App\Models\User,nombre|max:50',
            "email" => 'required|email|unique:App\Models\User,email|max:30',
            "pass" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            "puesto_trabajo" => 'required|in:Direccion,RRHH,Empleado',
            "salario" => 'required|numeric',
            "biografia" => 'required|max:100'
        ]);

        if($validator -> fails()){
            $respuesta["status"] = 0;
            $respuesta["msg"] = "".$validator->errors();         
        } else {
            try {
                $usuario->save();
                $respuesta["msg"] = "Registro completado";
            } catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error";  
            }
        }  
        return response()->json($respuesta);
     
    }
    //Ver listado de empleados
    public function listado_empleados(Request $request){

        $respuesta = ["status" => 1, "msg" => ""];

        if ($request->usuario->puesto_trabajo == 'Direccion'){

            $empleados = DB::table('usuarios')
                ->whereIn('usuarios.puesto_trabajo', ['Empleado', 'RRHH'])
                ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario', 'usuarios.biografia', 'usuarios.imagen')
                ->get(); 
           $respuesta['listado_empleados'] = $empleados;
           $respuesta["msg"] = "Listado obtenido";  

        } elseif ($request->usuario->puesto_trabajo == 'RRHH'){

            $empleados = DB::table('usuarios')
                ->where('usuarios.puesto_trabajo', 'Empleado')
                ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario', 'usuarios.biografia', 'usuarios.imagen')
                ->get(); 
            $respuesta['listado_empleados'] = $empleados;
            $respuesta["msg"] = "Listado obtenido";  

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Permisos no validos";
        }

        return response()->json($respuesta);
    }
    //Ver detalle de un empleado
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
                            ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario','usuarios.email', 'usuarios.biografia')
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
                            ->select('usuarios.id','usuarios.nombre','usuarios.puesto_trabajo','usuarios.salario','usuarios.email')
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
    //Ver perfil usuario logeado
    public function ver_perfil(Request $request){

        $respuesta = ["status" => 1, "msg" => ""];
        $perfil = $usuario = User::find($request->usuario->id);

        if($perfil){
            $perfil -> makevisible( 'pass');
            $respuesta['msg'] = "Datos obtenidos";
            $respuesta['datos_perfil'] = $perfil;
        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Se ha producido un error";  
        }
        return response()->json($respuesta);
    }
    //Modificar datos de un usuario
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
                $respuesta["msg"] = "".$validator->errors();  
            } else {

                if ($editar == true){

                    $datos = $request -> getContent();
                    $datos = json_decode($datos); 
                
                    if(isset($datos->nombre))
                    $usuario -> nombre = $datos->nombre;
                    if(isset($datos->email))
                    $usuario -> email = $datos->email;

                    if(isset($datos->pass)){
                    $usuario -> pass = Hash::make($datos->pass);
                    $usuario -> api_token = null;
                    }

                    if(isset($datos->puesto_trabajo))
                    $usuario -> puesto_trabajo = $datos->puesto_trabajo;
                    if(isset($datos->salario))
                    $usuario -> salario = $datos->salario;
                    if(isset($datos->biografia))
                    $usuario -> biografia = $datos->biografia;
        
                    try {
                        $usuario->save();
                        $respuesta["msg"] = "Cambios realizados";
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
    //Login Usuario
    public function login(Request $req){

        $respuesta = ["status" => 1, "msg" => "", "api_token" => ""];     
        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $email = $datos->email;
        $pass = $datos->pass;
        $usuario = User::where('email', $datos->email) -> first();

        if ($usuario){
            if (Hash::check( $datos->pass, $usuario->pass)){
                do {
                    $token = Hash::make($usuario->id.now());
                } while(User::where('api_token', $token) -> first());

                $usuario -> api_token = $token;
                $usuario -> save();
                $respuesta["msg"] = "Login correcto";
                $respuesta["api_token"] = $usuario -> api_token; 
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
    //Recuperar Contraseña
    public function recoverPass(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $datos = $req -> getContent();
        $datos = json_decode($datos); 
    
        $email = $datos->email;
        $usuario = User::where('email', $email) -> first();

        if($usuario){
            
            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
            $caracteresLenght = strlen($caracteres);
            $longitud = 8;
            $newPassword = "";
          
            for($i = 0; $i<$longitud; $i++) {
               $newPassword .= $caracteres[rand(0, $caracteresLenght -1)];
            }
            $usuario->api_token = null;
            $usuario->pass = Hash::make($newPassword);
            $usuario -> save();
            Mail::to($usuario->email)->send(new recoverPass($newPassword));
            $respuesta["msg"] = "Se ha enviado una contraseña nueva a tu email";  

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Email no encontrado";  
        }

        return response()->json($respuesta);  

    }

    public function uploadImage(Request $req){
        $respuesta = ["status" => 1, "msg" => ""];
        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $usuario = $usuario = User::where('id', $req->usuario->id) -> first();
        $image = $datos->image;  // your base64 encoded

        if($image && $usuario){
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10).'.'.'png';

            try {
                Storage::disk('public')->put($imageName, base64_decode($image));
                $imageUrl = "http://localhost/gestion_empleados/public/storage/".$imageName;
                $usuario->imagen = $imageUrl;
                $usuario -> save();        
                $respuesta["msg"] = "Imagen guardada";        
            } 
            catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error al guardar la imagen";  
            }

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Imagen o usuario no encontrado";  
        }

        return response()->json($respuesta);  

    }
    

}
