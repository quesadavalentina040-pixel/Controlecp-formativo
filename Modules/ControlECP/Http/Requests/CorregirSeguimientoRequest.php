<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de las solicitudes del módulo.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para solicitudes validadas.
use Illuminate\Support\Facades\Auth; // Importa el acceso al usuario autenticado.

class CorregirSeguimientoRequest extends FormRequest // Define las reglas para corregir un seguimiento.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Permite continuar solo a administradores autenticados de ControlECP.
    }

    public function rules(): array // rules() entrega las reglas para validar los campos de corrección.
    {
        return [ // Devuelve las validaciones para que Laravel las aplique.
            'motivo_correccion' => ['required', 'string', 'min:5'], // Exige el motivo de corrección como texto de cinco caracteres o más.
            'avances'           => ['required', 'string', 'min:5'], // Exige describir los avances corregidos con suficiente texto.
            'compromisos'       => ['required', 'string', 'min:5'], // Exige describir los compromisos corregidos.
            'observaciones'     => ['nullable', 'string'], // Permite omitir observaciones; si se envían, deben ser texto.
        ];
    }

    public function messages(): array // messages() entrega mensajes en español si falla una regla.
    {
        return [ // Devuelve los mensajes asociados a cada regla.
            'motivo_correccion.required' => 'Debes indicar el motivo de la corrección.', // Explica que falta el motivo.
            'motivo_correccion.min'      => 'El motivo de corrección debe tener al menos 5 caracteres.', // Explica que el motivo es muy corto.
            'avances.required'           => 'El campo avances corregidos es obligatorio.', // Indica que no se puede omitir avances.
            'avances.min'                => 'Los avances deben tener al menos 5 caracteres.', // Indica que el texto de avances es muy corto.
            'compromisos.required'       => 'El campo compromisos corregidos es obligatorio.', // Indica que no se pueden omitir compromisos.
            'compromisos.min'            => 'Los compromisos deben tener al menos 5 caracteres.', // Indica que los compromisos son muy cortos.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() prepara la reapertura del modal si la validación falla.
    {
        session()->flash('modal_abrir', 'modalCorregirSeguimiento'); // Guarda qué modal debe volver a abrirse.
        session()->flash('modal_id', $this->route('id')); // Guarda el id del seguimiento tomado de la ruta.
        parent::failedValidation($validator); // Invoca el comportamiento de Laravel que retorna los errores.
    }
}
