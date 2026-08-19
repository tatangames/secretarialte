<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Departamentos;
use App\Models\Lugar;
use App\Models\ObjetoEspecifico;
use App\Models\Proveedor;
use App\Models\Rubro;
use App\Models\TipoCompra;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{

    public function indexLugares(){
        return view('backend.admin.configuracion.lugares.vistalugares');
    }

    public function tablaLugares(){

        $lista = Lugar::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.lugares.tablalugares', compact('lista'));
    }

    public function nuevaLugares(Request $request){
        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new Lugar();
        $dato->nombre = $request->nombre;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function informacionLugares(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Lugar::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    public function editarLugares(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Lugar::where('id', $request->id)->first()){

            Lugar::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }






}
