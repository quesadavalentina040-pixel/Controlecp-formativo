<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de solicitudes del módulo ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase Laravel que valida datos de solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta para comprobar la cuenta autenticada.

class ReprogramarAsesoriaRequest extends FormRequest // Define las reglas para reprogramar una asesoría existente.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Autoriza solo a administradores autenticados de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones para validar la reprogramación.
    {
        return [ // Devuelve las reglas de validación para Laravel.
            'fecha'       => ['required', 'date'], // Exige una fecha válida para la nueva cita.
            'hora_inicio' => ['required'], // Exige informar la nueva hora de inicio.
            'hora_fin'    => ['required', 'after:hora_inicio'], // Exige que la hora final sea posterior a la inicial.
            'motivo'      => ['required', 'string', 'min:5'], // Exige explicar el cambio con al menos cinco caracteres.
        ];
    }

    public function messages(): array // messages() entrega mensajes personalizados en español.
    {
        return [ // Devuelve cada mensaje asociado a su regla.
            'fecha.required'       => 'Debes indicar la nueva fecha.', // Avisa que no se indicó la fecha.
            'fecha.date'           => 'El formato de fecha no es válido.', // Avisa que la fecha tiene formato incorrecto.
            'hora_inicio.required' => 'La nueva hora de inicio es requerida.', // Avisa que falta la hora inicial.
            'hora_fin.required'    => 'La nueva hora de fin es requerida.', // Avisa que falta la hora final.
            'hora_fin.after'       => 'La hora de fin debe ser posterior a la de inicio.', // Avisa que las horas están en orden incorrecto.
            'motivo.required'      => 'El motivo de la reprogramación es obligatorio.', // Avisa que falta explicar por qué se reprograma.
            'motivo.min'           => 'El motivo debe tener al menos 5 caracteres.', // Avisa que el motivo es demasiado corto.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() prepara la reapertura del modal si falla la validación.
    {
        session()->flash('modal_abrir', 'modalReprogramar'); // Guarda el nombre del modal que debe abrirse de nuevo.
        session()->flash('modal_id', $this->route('id')); // Conserva el identificador de la asesoría que se reprograma.
        parent::failedValidation($validator); // Ejecuta el comportamiento normal de Laravel para devolver los errores.
    }
}
