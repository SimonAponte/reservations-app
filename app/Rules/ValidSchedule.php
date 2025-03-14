<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidSchedule implements ValidationRule
{
    /**
     * Run the validation rule.
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $schedule = $value;

        // Definir los días de la semana
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Verificar si es un array
        if (!is_array($schedule)) {
            $fail("El campo $attribute debe ser un JSON válido.");
            return;
        }

        // Verificar que todos los días estén presentes
        foreach ($days as $day) {
            if (!array_key_exists($day, $schedule)) {
                $fail("El campo $attribute debe incluir el día $day.");
                return;
            }
        }

        // Validar la estructura de cada día
        foreach ($days as $day) {
            if (isset($schedule[$day])) {
                // Verificar que no sea un array vacío
                if (is_array($schedule[$day]) && empty($schedule[$day])) {
                    $fail("El día $day no puede tener un array vacío. Usa null si no hay horarios.");
                    return;
                }

                // Si no es null, debe ser un array de bloques de horarios
                if (!is_null($schedule[$day])) {
                    // Verificar que sea un array
                    if (!is_array($schedule[$day])) {
                        $fail("El horario para $day debe ser un array.");
                        return;
                    }

                    // Validar cada bloque de horario
                    foreach ($schedule[$day] as $index => $block) {
                        // Verificar que tenga 'start' y 'end'
                        if (!isset($block['start']) || !isset($block['end'])) {
                            $fail("Cada bloque de horario para $day debe tener 'start' y 'end'.");
                            return;
                        }

                        // Validar el formato de las horas (HH:MM)
                        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $block['start']) ||
                            !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $block['end'])) {
                            $fail("El formato de la hora para $day debe ser HH:MM.");
                            return;
                        }

                        // Convertir las horas a objetos Carbon
                        $start = Carbon::createFromFormat('H:i', $block['start']);
                        $end = Carbon::createFromFormat('H:i', $block['end']);

                        // Verificar que la hora de inicio sea menor que la hora de finalización
                        if ($start->gte($end)) {
                            $fail("El horario para $day (en la sesion $index) tiene una hora de inicio mayor o igual a la hora de finalización.");
                            return;
                        }

                        // Verificar que no haya superposición de horarios
                        for ($i = $index + 1; $i < count($schedule[$day]); $i++) {
                            $nextStart = Carbon::createFromFormat('H:i', $schedule[$day][$i]['start']);
                            $nextEnd = Carbon::createFromFormat('H:i', $schedule[$day][$i]['end']);

                            if ($start->lt($nextEnd) && $end->gt($nextStart)) {
                                $fail("El horario para $day (bloque $index) se superpone con otro bloque.");
                                return;
                            }
                        }
                    }
                }
            }
        }
    }
}
