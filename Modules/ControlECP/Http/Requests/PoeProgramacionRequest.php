<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para las solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para validar solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta de Laravel que identifica al usuario autenticado.

class PoeProgramacionRequest extends FormRequest // Define autorización y reglas para programar una entrega POE.
{
    /**
     * Determina si el usuario está autorizado para esta solicitud.
     */
    public function authorize(): bool // authorize() indica si el usuario actual puede hacer esta solicitud.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Autoriza solo si hay sesión y el usuario es administrador de ControlECP.
    }

    /**
     * Reglas de validación para programar una subida de POE.
     */
    public function rules(): array // rules() entrega a Laravel las reglas para validar los datos de programación.
    {
        return [ // Devuelve las reglas agrupadas por campo.
            'id_usuario_nuevo_lider' => [ // Inicia la validación del nuevo líder asignado.
                'required', // Exige seleccionar un usuario.
                'integer', // Exige que el identificador sea entero.
                'exists:users,id', // Comprueba que el id corresponda a un usuario existente.
            ],
            'fecha_limite' => [ // Inicia la validación de la fecha límite.
                'required', // Exige indicar una fecha.
                'date', // Verifica que el valor tenga un formato de fecha válido.
                'after_or_equal:today', // Exige que la fecha sea hoy o posterior, no una fecha pasada.
            ],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array // messages() define los textos personalizados de error.
    {
        return [ // Devuelve mensajes asociados a cada regla.
            'id_usuario_nuevo_lider.required' => 'Debe seleccionar el nuevo líder para la subida.', // Avisa si no se seleccionó un nuevo líder.
            'id_usuario_nuevo_lider.exists'   => 'El usuario seleccionado como nuevo líder no es válido.', // Avisa si el id enviado no pertenece a un usuario.
            'fecha_limite.required'           => 'La fecha límite de subida es obligatoria.', // Avisa si no se indicó la fecha límite.
            'fecha_limite.date'               => 'La fecha límite debe ser una fecha válida.', // Avisa si el valor no se reconoce como fecha.
            'fecha_limite.after_or_equal'     => 'La fecha límite no puede ser anterior a hoy.', // Avisa si se eligió un día pasado.
        ];
    }
}
