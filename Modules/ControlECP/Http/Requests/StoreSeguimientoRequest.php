<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de las solicitudes del módulo ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase de Laravel que valida datos de formularios.
use Illuminate\Support\Facades\Auth; // Importa el acceso al usuario que inició sesión.

class StoreSeguimientoRequest extends FormRequest // Define autorización y validaciones para registrar un seguimiento.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Exige sesión iniciada y rol administrador de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones de validación para registrar el seguimiento.
    {
        return [ // Devuelve las reglas que Laravel aplicará a los datos.
            'avances'       => ['required', 'string', 'min:5'], // Exige avances escritos en texto con mínimo cinco caracteres.
            'compromisos'   => ['required', 'string', 'min:5'], // Exige compromisos escritos en texto con mínimo cinco caracteres.
            'observaciones' => ['nullable', 'string'], // Permite no enviar observaciones; si se envían, deben ser texto.
        ];
    }

    public function messages(): array // messages() entrega textos en español para los errores de validación.
    {
        return [ // Devuelve el mensaje correspondiente a cada regla.
            'avances.required'     => 'El campo avances es obligatorio.', // Se muestra si no se escribieron avances.
            'avances.min'          => 'Los avances deben tener al menos 5 caracteres.', // Se muestra si avances es demasiado corto.
            'compromisos.required' => 'El campo compromisos es obligatorio.', // Se muestra si no se escribieron compromisos.
            'compromisos.min'      => 'Los compromisos deben tener al menos 5 caracteres.', // Se muestra si compromisos es demasiado corto.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() prepara la reapertura del modal si falla alguna regla.
    {
        session()->flash('modal_abrir', 'modalSeguimiento'); // Guarda en sesión el nombre del modal que debe abrirse.
        session()->flash('modal_id', $this->route('id')); // Conserva el id de la asesoría o registro indicado en la ruta.
        parent::failedValidation($validator); // Ejecuta el retorno estándar de Laravel con los mensajes de error.
    }
}
