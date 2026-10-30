<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres de las solicitudes del módulo.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase de Laravel que valida datos de solicitudes.
use Illuminate\Support\Facades\Auth; // Importa el acceso al usuario autenticado.
use Illuminate\Validation\Rule; // Importa reglas de validación que se construyen dinámicamente.

class PoeRequest extends FormRequest // Define permisos y validaciones para los datos de un POE.
{
    /**
     * Determina si el usuario está autorizado para esta solicitud.
     */
    public function authorize(): bool // authorize() define qué usuarios pueden enviar esta solicitud.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Exige sesión activa y rol administrador de ControlECP.
    }

    /**
     * Reglas de validación para la creación de un POE inicial.
     */
    public function rules(): array // rules() entrega las reglas para validar el POE y su archivo.
    {
        $idPoe = $this->route('id') ?? $this->route('poe')?->id_poe; // Lee el id desde la ruta para permitir conservar el código del POE al editar.

        return [ // Devuelve las reglas agrupadas por campo.
            'codigo' => [ // Inicia la lista de reglas para el código del POE.
                'required', // Exige que el campo tenga un valor.
                'string', // Exige que el valor sea texto.
                'max:100', // Limita el código a cien caracteres.
                Rule::unique('poes', 'codigo')->ignore($idPoe, 'id_poe'), // Exige que el código sea único, excepto el registro que se está editando.
            ],
            'nombre' => [ // Inicia las reglas para el nombre del POE.
                'required', // Exige que se indique el nombre.
                'string', // Exige que el nombre sea texto.
                'max:255', // Limita el nombre a 255 caracteres.
            ],
            'id_usuario' => [ // Inicia las reglas para el usuario líder.
                'required', // Exige seleccionar un líder.
                'integer', // Exige que el id del líder sea un número entero.
                'exists:users,id', // Comprueba que el id exista en la tabla de usuarios.
            ],
            'archivo' => [ // Inicia las reglas para el archivo del POE.
                'required', // Exige adjuntar un archivo.
                'file', // Comprueba que el dato recibido sea un archivo cargado.
                'mimes:pdf,docx', // Acepta únicamente documentos PDF o Word DOCX.
                'max:10240', // Limita el tamaño a 10240 KB, equivalentes a 10 MB.
            ],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array // messages() proporciona textos personalizados para los errores de validación.
    {
        return [ // Devuelve mensajes específicos para cada regla indicada.
            'codigo.required'     => 'El código del POE es obligatorio.', // Avisa si falta el código.
            'codigo.unique'       => 'Ya existe un POE registrado con este código.', // Avisa si otro POE ya usa el código.
            'nombre.required'     => 'El nombre del POE es obligatorio.', // Avisa si falta el nombre.
            'id_usuario.required' => 'Debe seleccionar un líder para el POE.', // Avisa si no se seleccionó un líder.
            'id_usuario.exists'   => 'El líder seleccionado no es válido en el sistema.', // Avisa si el usuario indicado no existe.
            'archivo.required'    => 'Debe adjuntar el archivo del POE.', // Avisa si no se adjuntó el documento.
            'archivo.file'        => 'El archivo adjunto no es válido.', // Avisa si el dato no es un archivo válido.
            'archivo.mimes'       => 'Solo se permiten archivos en formato PDF (.pdf) o Word (.docx).', // Avisa si la extensión no está permitida.
            'archivo.max'         => 'El archivo no puede superar los 10 MB de tamaño.', // Avisa si el archivo excede el límite.
        ];
    }
}
