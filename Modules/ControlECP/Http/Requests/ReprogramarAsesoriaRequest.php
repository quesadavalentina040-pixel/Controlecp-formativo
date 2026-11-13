<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de solicitudes del módulo ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase Laravel que valida datos de solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta para comprobar la cuenta autenticada.
use Modules\ControlECP\Rules\NotWeekendOrColombianHoliday;

class ReprogramarAsesoriaRequest extends FormRequest // Define las reglas para reprogramar una asesoría existente.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Autoriza solo a administradores autenticados de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones para validar la reprogramación.
    {
        return [ // Devuelve las reglas de validación para Laravel.
            'fecha'       => ['required', 'date', new NotWeekendOrColombianHoliday()], // Exige una fecha hábil para la nueva cita.
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'], // Limita el inicio al horario de atención.
            'hora_fin'    => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'], // Exige una hora final posterior y no mayor a las 3:00 p. m.
            'motivo'      => ['required', 'string', 'min:10', 'max:200'], // Exige explicar el cambio con 10 a 200 caracteres.
        ];
    }

    public function messages(): array // messages() entrega mensajes personalizados en español.
    {
        return [ // Devuelve cada mensaje asociado a su regla.
            'fecha.required'       => 'Debes indicar la nueva fecha.', // Avisa que no se indicó la fecha.
            'fecha.date'           => 'El formato de fecha no es válido.', // Avisa que la fecha tiene formato incorrecto.
            'hora_inicio.required' => 'La nueva hora de inicio es requerida.', // Avisa que falta la hora inicial.
            'hora_inicio.after_or_equal' => 'La asesoría debe iniciar a partir de las 8:00 a. m.',
            'hora_inicio.before' => 'La asesoría debe iniciar antes de las 3:00 p. m.',
            'hora_fin.required'    => 'La nueva hora de fin es requerida.', // Avisa que falta la hora final.
            'hora_fin.after'       => 'La hora de fin debe ser posterior a la de inicio.', // Avisa que las horas están en orden incorrecto.
            'hora_fin.before_or_equal' => 'La asesoría debe finalizar como máximo a las 3:00 p. m.',
            'motivo.required'      => 'El motivo de la reprogramación es obligatorio.', // Avisa que falta explicar por qué se reprograma.
            'motivo.min'           => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max'           => 'El motivo no puede superar los 200 caracteres.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() prepara la reapertura del modal si falla la validación.
    {
        session()->flash('modal_abrir', 'modalReprogramar'); // Guarda el nombre del modal que debe abrirse de nuevo.
        session()->flash('modal_id', $this->route('id')); // Conserva el identificador de la asesoría que se reprograma.
        parent::failedValidation($validator); // Ejecuta el comportamiento normal de Laravel para devolver los errores.
    }
}
