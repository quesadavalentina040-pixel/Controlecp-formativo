<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de las solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase de Laravel para validar solicitudes entrantes.
use Illuminate\Support\Facades\Auth; // Importa la herramienta para comprobar la sesión del usuario.

class PoeVersionRequest extends FormRequest // Define permisos y reglas para cargar una nueva versión de POE.
{
    /**
     * Determina si el usuario está autorizado para esta solicitud.
     */
    public function authorize(): bool // authorize() indica quién puede enviar una versión nueva.
    {
        return Auth::check(); // Permite continuar si existe un usuario con sesión iniciada.
    }

    /**
     * Reglas de validación para subir una nueva versión del POE.
     */
    public function rules(): array // rules() especifica las condiciones de los datos de la nueva versión.
    {
        return [ // Devuelve las reglas separadas por campo.
            'cambio' => [ // Inicia las validaciones para la descripción del cambio.
                'required', // Exige describir qué cambió en esta versión.
                'string', // Exige que la descripción sea texto.
                'min:5', // Exige al menos cinco caracteres.
                'max:1000', // Limita la descripción a mil caracteres.
            ],
            'archivo' => [ // Inicia las reglas para el archivo que se va a subir.
                'required', // Exige que se adjunte el documento.
                'file', // Verifica que la entrada sea un archivo cargado.
                'mimes:pdf,docx', // Acepta PDF o Word en formato DOCX.
                'max:10240', // Limita el archivo a 10240 KB, es decir, 10 MB.
            ],
            'cumplir_programacion' => [ // Inicia las reglas para el indicador de cumplimiento de programación.
                'nullable', // Permite omitir el campo.
                'boolean', // Si se envía, exige un valor verdadero/falso compatible.
            ],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array // messages() define los textos que verá el usuario si una regla falla.
    {
        return [ // Devuelve los mensajes relacionados con cada campo y regla.
            'cambio.required'  => 'El campo ¿Qué se modificó? es obligatorio.', // Avisa si se dejó vacía la descripción.
            'cambio.min'       => 'Debe describir el cambio con al menos 5 caracteres.', // Avisa si la descripción es demasiado corta.
            'cambio.max'       => 'La descripción del cambio no puede superar los 1000 caracteres.', // Avisa si se excedió el límite.
            'archivo.required' => 'Debe adjuntar el archivo de la nueva versión.', // Avisa si falta adjuntar el archivo.
            'archivo.file'     => 'El archivo adjunto no es válido.', // Avisa si Laravel no reconoce un archivo válido.
            'archivo.mimes'    => 'Solo se permiten archivos en formato PDF (.pdf) o Word (.docx).', // Avisa si el tipo no está permitido.
            'archivo.max'      => 'El archivo no puede superar los 10 MB de tamaño.', // Avisa si el archivo supera el límite.
        ];
    }
}
