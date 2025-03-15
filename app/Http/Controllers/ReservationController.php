<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReservationController extends Controller
{
    public function addReservation(Request $request)
    {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'space_id' => 'required|exists:spaces,id',
            'reservation_date' => 'required|date_format:Y-m-d',
            'start_hour' => 'required|date_format:H:i',
            'end_hour' => 'required|date_format:H:i|after:start_hour',
        ]);

        // Si la validación falla, retornar errores
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Validar que la fecha de reserva no sea anterior a la fecha actual
        $reservationDate = Carbon::parse($request->reservation_date.' '.$request->start_hour, 'UTC');
        $currentDate = Carbon::now();
        
        if ($reservationDate->lt($currentDate)) {  // lt() significa "less than" (menor que)
            return response()->json(['error' => 'La fecha de reserva no puede ser anteriores a la fecha y hora actuales'], 400);
        }

        // Obtener el espacio
        $space = Space::find($request->space_id);

        // Decodificar el JSON de horarios
        $availability = json_decode($space->schedule, true);

        // Verificar si el JSON es válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'El horario del espacio no es válido'], 400);
        }

        // Obtener el día de la semana de la fecha de reserva
        $dayOfWeek = strtolower($reservationDate->englishDayOfWeek);  // Ej: "monday", "tuesday"

        // Verificar si el día tiene horarios disponibles
        if (is_null($availability[$dayOfWeek])) {
            return response()->json(['error' => 'No hay horarios disponibles para este día'], 400);
        }

        // Validar que la hora de reserva esté dentro de los rangos permitidos
        $startHour = $request->start_hour;
        $endHour = $request->end_hour;
        $isValid = false;

        foreach ($availability[$dayOfWeek] as $timeSlot) {
            if ($startHour >= $timeSlot['start'] && $endHour <= $timeSlot['end']) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            return response()->json(['error' => 'El horario de reserva no está dentro de los rangos permitidos'], 400);
        }

        // Obtener el usuario autenticado con JWT
        $user = JWTAuth::user();

        // Verificar si el usuario ya tiene una reserva el mismo día
        $existingReservation = $user->spaces()
            ->wherePivot('reservation_date', $request->reservation_date)
            ->exists();

        if ($existingReservation) {
            return response()->json(['error' => 'Solo puedes tener una reserva para este día'], 409);
        }

        // Validar la capacidad del espacio
        $overlappingReservations = DB::table('space_user')
            ->where('space_id', $space->id)
            ->where('reservation_date', $request->reservation_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_hour', [$request->start_hour, $request->end_hour])
                    ->orWhereBetween('end_hour', [$request->start_hour, $request->end_hour])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_hour', '<=', $request->start_hour)
                                ->where('end_hour', '>=', $request->end_hour);
                    });
            })
            ->count();

        if ($overlappingReservations >= $space->capacity) {
            return response()->json(['error' => 'El espacio ha alcanzado su capacidad máxima para este horario'], 400);
        }

        // Crear la reserva en la tabla pivot
        $user->spaces()->attach($space->id, [
            'reservation_date' => $request->reservation_date,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
        ]);

        // Retornar respuesta exitosa
        return response()->json(['message' => 'Reserva creada exitosamente'], 201);
    }

    public function deleteReservationById($id)
    {

        // Obtener el usuario autenticado con JWT
        $user = JWTAuth::user();

        // Obtener la reserva específica del usuario
        $userReservation = $user->spaces()
            ->wherePivot('id', $id)  // Buscar por el ID de la reserva en la tabla pivot
            ->first();

        if (!$userReservation) {
            return response()->json(['error' => "reserva no encontrada"], 404);
        }

        // Crear un objeto Carbon con la fecha y hora de inicio de la reserva
        $reservationDateTime = Carbon::parse($userReservation->reservation->reservation_date . ' ' . $userReservation->reservation->start_hour, 'UTC');

        // Obtener la fecha y hora actual
        $currentDateTime = Carbon::now();

        // Calcular la diferencia en horas
        $hoursDifference = $currentDateTime->diffInHours($reservationDateTime, false);
        

        // Verificar si la reserva comienza en menos de una hora
        if ($hoursDifference < 1 && $hoursDifference > 0) {
            return response()->json(['error' => 'No puedes cancelar esta reserva ya que falta una hora o menos para empezar'], 400);
        }

        if ($hoursDifference < 0) {
            return response()->json(['error' => 'No puedes cancelar una reserva que ya ha pasado, es parte del historial'], 400);
        }


        // Eliminar la reserva en la tabla pivot usando el ID de la reserva
        DB::table('space_user')->where('id', $id)->delete();

        // Retornar respuesta exitosa
        return response()->json(['message' => 'Reserva eliminada exitosamente'], 200);

    }

    public function getReservations()
    {
        // Obtener el usuario autenticado con JWT
        $user = JWTAuth::user();

        // Obtener las reservas del usuario con los campos necesarios
        $reservations = $user->spaces()
            ->select(            
                'spaces.name',
                'spaces.price',
                'space_user.reservation_date',
                'space_user.start_hour',
                'space_user.end_hour'
            )
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json(['message' => 'No se encontraron reservaciones'], 404);
        }

        // Transformar la respuesta para incluir el costo estimado
        $simplifiedReservations = $reservations->map(function ($reservation) {
            // Calcular la duración de la reserva en horas
            $start = Carbon::parse($reservation->start_hour);
            $end = Carbon::parse($reservation->end_hour);
            $hoursDifference = $start->floatDiffInHours($end); // Diferencia en horas con decimales

            // Calcular el costo estimado
            $costoEstimado = $hoursDifference * $reservation->price;

            // Retornar la estructura simplificada
            return [
                'name' => $reservation->name,
                'reservation_date' => $reservation->reservation_date,
                'start_hour' => $reservation->start_hour,
                'end_hour' => $reservation->end_hour,
                'estimated_cost' => $costoEstimado,
            ];
        });

        return response()->json($simplifiedReservations, 200);
    }

}
