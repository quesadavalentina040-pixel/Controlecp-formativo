<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para las solicitudes del módulo.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para validar solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta de Laravel que identifica al usuario autenticado.

class AsignarRecursoRequest extends FormRequest // Define la validación para asignar un recurso del inventario.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Autoriza solo si hay sesión y el usuario tiene rol administrador de ControlECP.
    }

    public function rules(): array // rules() entrega a Laravel las reglas de validación para asignar un recurso del inventario.
    {
        return [ // Devuelve el arreglo de validaciones por campo.
            'id_inventario' => ['required', 'exists:inventories,id'], // Exige seleccionar un inventario existente.
            'cantidad'      => ['required', 'integer', 'min:1'], // Exige una cantidad entera de al menos una unidad.
        ];
    }

    public function messages(): array // messages() entrega textos personalizados en español para los errores.
    {
        return [ // Devuelve el texto asociado a cada regla que puede fallar.
            'id_inventario.required' => 'Debes seleccionar un recurso del inventario.', // Mensaje si no se seleccionó un recurso.
            'id_inventario.exists'   => 'El recurso seleccionado no existe en el inventario.', // Mensaje si el id no está guardado.
            'cantidad.required'      => 'Debes indicar la cantidad a vincular.', // Mensaje si falta la cantidad.
            'cantidad.integer'       => 'La cantidad debe ser un número entero.', // Mensaje si la cantidad no es un entero.
            'cantidad.min'           => 'La cantidad mínima es 1.', // Mensaje si la cantidad es menor que uno.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() se ejecuta cuando falla una regla y prepara la reapertura del modal.
    {
        session()->flash('modal_abrir', 'modalRecursos'); // Guarda temporalmente el nombre del modal que debe volver a abrirse.
        session()->flash('modal_id', $this->route('id')); // Guarda el id recibido en la ruta para reabrir el modal del registro correcto.
        parent::failedValidation($validator); // Ejecuta el manejo de error original de Laravel para mostrar los errores.
    }
}
