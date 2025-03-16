<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Rules\ValidSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/spaces",
     *     summary="Crear un nuevo espacio",
     *     security={{"bearerAuth": {}}},
     *     tags={"Spaces"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para crear un nuevo espacio",
     *         @OA\JsonContent(
     *             required={"name", "price", "schedule", "capacity"},
     *             @OA\Property(property="name", type="string", minLength=10, maxLength=100, example="Sala de Conferencias A"),
     *             @OA\Property(property="price", type="number", format="float", example=50.75),
     *             @OA\Property(
     *                 property="schedule",
     *                 type="object",
     *                 @OA\Property(
     *                     property="monday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="tuesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="wednesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="thursday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="friday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="saturday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="sunday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="capacity", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Espacio creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio creado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", example={
     *                 "name": {"El campo nombre es obligatorio."},
     *                 "price": {"El campo precio debe ser un número válido."},
     *                 "schedule": {"El campo horario no es válido."},
     *                 "capacity": {"El campo capacidad debe ser un número entero."}
     *             })
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Get(
     *     path="/api/spaces",
     *     summary="Obtener todos los espacios",
     *     security={{"bearerAuth": {}}},
     *     tags={"Spaces"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de espacios",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Sala de Conferencias A"),
     *                 @OA\Property(property="price", type="number", format="float", example=50.75),
     *                 @OA\Property(
     *                     property="schedule",
     *                     type="object",
     *                     @OA\Property(
     *                         property="monday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="tuesday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="wednesday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="thursday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="friday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="saturday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="sunday",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="start", type="string", example="09:00"),
     *                             @OA\Property(property="end", type="string", example="18:00")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="capacity", type="integer", example=20)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron espacios",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No se encontraron espacios")
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/spaces/{id}",
     *     summary="Obtener un espacio por ID",
     *     security={{"bearerAuth": {}}},
     *     tags={"Spaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del espacio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del espacio",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Sala de Conferencias A"),
     *             @OA\Property(property="price", type="number", format="float", example=50.75),
     *             @OA\Property(
     *                 property="schedule",
     *                 type="object",
     *                 @OA\Property(
     *                     property="monday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="tuesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="wednesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="thursday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="friday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="saturday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="sunday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="capacity", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Espacio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio no encontrado")
     *         )
     *     )
     * )
     */

    public function getSpaceById($id){

        $space = Space::find($id);

        if(!$space){
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }

        $space->schedule = json_decode($space->schedule);

        return response()->json($space, 200);

    }

    /**
     * @OA\Patch(
     *     path="/api/spaces/{id}",
     *     summary="Actualizar un espacio por ID",
     *     security={{"bearerAuth": {}}},
     *     tags={"Spaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del espacio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos para actualizar el espacio",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", minLength=10, maxLength=100, example="Sala de Conferencias A"),
     *             @OA\Property(property="price", type="number", format="float", example=50.75),
     *             @OA\Property(
     *                 property="schedule",
     *                 type="object",
     *                 @OA\Property(
     *                     property="monday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="tuesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="wednesday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="thursday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="friday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="saturday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="sunday",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", example="09:00"),
     *                         @OA\Property(property="end", type="string", example="18:00")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="capacity", type="integer", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Espacio actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Espacio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", example={
     *                 "name": {"El campo nombre es obligatorio."},
     *                 "price": {"El campo precio debe ser un número válido."},
     *                 "schedule": {"El campo horario no es válido."},
     *                 "capacity": {"El campo capacidad debe ser un número entero."}
     *             })
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Delete(
     *     path="/api/spaces/{id}",
     *     summary="Eliminar un espacio por ID",
     *     security={{"bearerAuth": {}}},
     *     tags={"Spaces"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del espacio",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Espacio eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Espacio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio no encontrado")
     *         )
     *     )
     * )
     */

    public function deleteSpaceById($id){
            
        $space = Space::find($id);

        if(!$space){
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }

        $space->delete();
        return response()->json(['message' => 'Espacio eliminado exitosamente'], 200);

    }
}
