<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para las solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para solicitudes con validación.
use Illuminate\Support\Facades\Auth; // Importa la herramienta de Laravel que obtiene el usuario autenticado.

class ProgramarAsesoriaRequest extends FormRequest // Define autorización y validaciones para programar una asesoría.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Requiere una sesión activa y el rol administrador de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones para validar la programación recibida.
    {
        return [ // Devuelve las reglas que Laravel debe comprobar.
            'id_usuario_instructor' => ['required', 'exists:users,id'], // Exige seleccionar un instructor existente en users.
            'fecha'                 => ['required', 'date'], // Exige una fecha con formato válido.
            'hora_inicio'           => ['required'], // Exige indicar una hora de inicio.
            'hora_fin'              => ['required', 'after:hora_inicio'], // Exige hora final posterior a la hora inicial.
        ];
    }

    public function messages(): array // messages() entrega textos personalizados en español para los errores.
    {
        return [ // Devuelve mensajes identificados por campo y regla.
            'id_usuario_instructor.required' => 'Debes asignar un instructor para programar la asesoría.', // Avisa que falta seleccionar un instructor.
            'id_usuario_instructor.exists'   => 'El instructor seleccionado no es válido.', // Avisa que el id elegido no existe.
            'fecha.required'                 => 'Debes indicar la fecha del encuentro.', // Avisa que falta la fecha.
            'fecha.date'                     => 'El formato de fecha no es válido.', // Avisa cuando la fecha no tiene formato válido.
            'hora_inicio.required'           => 'La hora de inicio es requerida.', // Avisa que falta la hora inicial.
            'hora_fin.required'              => 'La hora de fin es requerida.', // Avisa que falta la hora final.
            'hora_fin.after'                 => 'La hora de fin debe ser posterior a la de inicio.', // Avisa que la hora final no es posterior a la inicial.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() conserva el modal que debe abrirse si falla la validación.
    {
        session()->flash('modal_abrir', 'modalProgramar'); // Solicita que se vuelva a abrir el modal de programación.
        session()->flash('modal_programar_id', $this->route('id')); // Conserva el id del registro para abrir el modal correcto.
        parent::failedValidation($validator); // Ejecuta el manejo estándar de errores de Laravel.
    }
}
