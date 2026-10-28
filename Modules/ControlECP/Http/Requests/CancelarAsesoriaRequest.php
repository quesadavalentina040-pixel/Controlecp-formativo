<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de las solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase de Laravel que prepara y valida solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta para consultar el usuario autenticado.

class CancelarAsesoriaRequest extends FormRequest // Define autorización y validaciones para cancelar una asesoría.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Exige una sesión activa y el rol administrador de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones que debe cumplir el motivo de cancelación.
    {
        return [ // Devuelve el arreglo de reglas para Laravel.
            'motivo' => ['required', 'string', 'min:5'], // Exige un motivo de texto con al menos cinco caracteres.
        ];
    }

    public function messages(): array // messages() entrega mensajes claros en español para los errores.
    {
        return [ // Asocia cada regla con su mensaje personalizado.
            'motivo.required' => 'Debes indicar el motivo de la cancelación.', // Se muestra cuando se deja vacío el motivo.
            'motivo.min'      => 'El motivo debe tener al menos 5 caracteres.', // Se muestra cuando el texto es demasiado corto.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() prepara la reapertura del modal cuando falla una regla.
    {
        session()->flash('modal_abrir', 'modalCancelar'); // Guarda el nombre del modal que debe abrirse de nuevo.
        session()->flash('modal_id', $this->route('id')); // Conserva el id de la asesoría indicado en la ruta.
        parent::failedValidation($validator); // Deja que Laravel devuelva los errores de validación al formulario.
    }
}
