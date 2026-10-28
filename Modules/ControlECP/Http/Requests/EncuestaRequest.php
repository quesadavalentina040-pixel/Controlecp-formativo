<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para las solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para validar solicitudes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta de Laravel que identifica al usuario autenticado.

class EncuestaRequest extends FormRequest // Define autorización y validaciones para crear o actualizar encuestas.
{
    /**
     * Solo usuarios con rol administrador de Control ECP están autorizados.
     */
    public function authorize(): bool // authorize() indica si el usuario puede procesar esta solicitud.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Autoriza solo a una persona autenticada con rol administrador de ControlECP.
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array // rules() entrega a Laravel las reglas de validación de la encuesta.
    {
        return [ // Devuelve las reglas agrupadas por nombre de campo.
            'nombre'          => ['required', 'string', 'max:255'], // Exige un nombre de texto de hasta 255 caracteres.
            'descripcion'     => ['nullable', 'string', 'max:2000'], // Permite descripción vacía o texto de hasta 2000 caracteres.
            'actividades_ids' => ['nullable', 'array'], // Permite no asociar actividades; si se envían, deben llegar como lista.
            'actividades_ids.*' => ['integer', 'exists:controlecp_actividades,id'], // Exige que cada id de la lista sea entero y exista como actividad de ControlECP.
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array // messages() define textos personalizados para los errores.
    {
        return [ // Devuelve los mensajes que se muestran en pantalla.
            'nombre.required' => 'El nombre de la encuesta es obligatorio.', // Avisa si falta el nombre.
            'nombre.string'   => 'El nombre debe ser una cadena de texto.', // Avisa si el nombre no es texto.
            'nombre.max'      => 'El nombre no puede exceder los 255 caracteres.', // Avisa si el nombre supera el límite.
            'descripcion.max' => 'La descripción no puede superar los 2000 caracteres.', // Avisa si la descripción es demasiado larga.
        ];
    }
}
