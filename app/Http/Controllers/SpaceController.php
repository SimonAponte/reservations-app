<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Rules\ValidSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{
    public function addSpace(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:10|max:100',
            'price' => 'required|numeric|regex:/^\d{1,6}(\.\d{1,2})?$/',
            'schedule' => ['required','array', new ValidSchedule],
            'capacity' => 'required|numeric|integer|min:1|max:255'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['schedule'] = json_encode($data['schedule']);
        
        Space::create($data);
        return response()->json(['message' => 'Espacio creado exitosamente'], 201);
    }
    
    public function getSpaces()
    {
     
        $spaces = Space::all();
    
        if ($spaces->isEmpty()) {
            return response()->json(['message' => 'No se encontraron espacios'], 404);
        }
    
   
        $spaces->transform(function ($space) {
            
            $space->schedule = json_decode($space->schedule);
            
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $space->schedule = null;
            }
    
            return $space;
        });
    
        return response()->json($spaces, 200);
    }

    public function getSpaceById($id){

        $space = Space::find($id);

        if(!$space){
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }

        $space->schedule = json_decode($space->schedule);

        return response()->json($space, 200);

    }

    public function updateSpaceById(Request $request, $id){

        $space = Space::find($id);

        if(!$space){
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|min:10|max:100',
            'price' => 'sometimes|numeric|regex:/^\d{1,6}(\.\d{1,2})?$/',
            'schedule' => ['sometimes', 'array', new ValidSchedule],
            'capacity' => 'sometimes|numeric|integer|min:1|max:255'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        if($request->has('name')){
            $space->name = $request->name;
        }

        if($request->has('price')){
            $space->price = $request->price;
        }

        if($request->has('schedule')){
            $space->schedule = json_encode($request->schedule);
        }

        if($request->has('capacity')){
            $space->capacity = $request->capacity;
        }
        $space->update();
        return response()->json(['message' => 'Espacio actualizado exitosamente'], 200);

    }

    public function deleteSpaceById($id){
            
        $space = Space::find($id);

        if(!$space){
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }

        $space->delete();
        return response()->json(['message' => 'Espacio eliminado exitosamente'], 200);

    }
}
