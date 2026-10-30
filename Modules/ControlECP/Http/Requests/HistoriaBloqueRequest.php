<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para solicitudes de ControlECP.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para solicitudes validadas.
use Illuminate\Support\Facades\Auth; // Importa el acceso al usuario autenticado.
use Modules\ControlECP\Entities\BloqueHistoria; // Importa el modelo de bloques multimedia de la historia.

class HistoriaBloqueRequest extends FormRequest // Define autorización y reglas para crear o editar bloques de historia.
{
    /**
     * Solo administradores de Control ECP están autorizados.
     */
    public function authorize(): bool // authorize() determina si el usuario puede procesar esta solicitud.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Permite continuar solo a administradores autenticados de ControlECP.
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array // rules() construye las validaciones para los campos recibidos.
    {
        $id = $this->route('id'); // Lee de la ruta el id del bloque cuando se está editando.
        $isUpdate = !empty($id); // Define verdadero si hay id y por tanto se trata de una actualización.

        $rules = [ // Inicializa las reglas generales que aplican a cualquier bloque.
            'titulo'         => ['required', 'string', 'max:255'], // Exige un título de texto de hasta 255 caracteres.
            'texto'          => ['required', 'string'], // Exige que el texto o descripción esté presente y sea texto.
            'tipo_contenido' => ['required', 'in:imagen,audio,video'], // Exige elegir uno de los tipos admitidos.
        ];

        $bloqueExistente = $isUpdate ? BloqueHistoria::find($id) : null; // Busca el bloque existente al editar; al crear deja null.

        if ($this->tipo_contenido === 'imagen') { // Aplica las reglas específicas cuando el contenido elegido es una imagen.
            $requiereArchivo = !$isUpdate || empty($bloqueExistente?->ruta_archivo) || $bloqueExistente?->tipo_contenido !== 'imagen'; // Exige archivo si es nuevo, no tenía archivo o cambió a imagen.

            $rules['archivo_imagen'] = [ // Agrega reglas para el archivo de imagen.
                $requiereArchivo ? 'required' : 'nullable', // Lo exige en creación o cambio de tipo; permite omitirlo al conservar una imagen existente.
                'file', // Verifica que el dato enviado sea un archivo.
                'mimes:jpg,jpeg,png', // Permite únicamente imágenes JPG, JPEG o PNG.
                'max:5120', // Limita el tamaño a 5120 KB, equivalentes a 5 MB.
            ];
        } elseif ($this->tipo_contenido === 'audio') { // Aplica las reglas específicas cuando se eligió audio.
            $requiereArchivo = !$isUpdate || empty($bloqueExistente?->ruta_archivo) || $bloqueExistente?->tipo_contenido !== 'audio'; // Exige archivo si es nuevo, no tenía audio o cambió a audio.

            $rules['archivo_audio'] = [ // Agrega reglas para el archivo de audio.
                $requiereArchivo ? 'required' : 'nullable', // Lo exige en creación o cambio de tipo; permite conservar el audio existente.
                'file', // Comprueba que se haya enviado un archivo.
                'mimes:mp3,wav,ogg', // Acepta los formatos MP3, WAV u OGG.
                'max:10240', // Limita el tamaño a 10240 KB, equivalentes a 10 MB.
            ];
        } elseif ($this->tipo_contenido === 'video') { // Aplica las reglas específicas cuando se eligió video.
            $rules['url_video'] = ['required', 'url', 'max:500']; // Exige una URL válida de hasta 500 caracteres.
        }

        return $rules; // Devuelve las reglas generales y las específicas del tipo seleccionado.
    }

    /**
     * Validación adicional para asegurar enlaces válidos de YouTube o Vimeo.
     */
    public function withValidator($validator) // withValidator() permite agregar una comprobación después de las reglas normales.
    {
        $validator->after(function ($validator) { // Registra una función que se ejecuta después de las validaciones principales.
            if ($this->tipo_contenido === 'video' && $this->filled('url_video')) { // Comprueba el proveedor solo si es video y se escribió una URL.
                $url = $this->url_video; // Guarda en una variable la URL proporcionada.
                $esYoutube = preg_match('/(?:youtu\.be\/|youtube\.com)/i', $url); // Busca en la URL un dominio o formato de enlace de YouTube.
                $esVimeo = preg_match('/vimeo\.com/i', $url); // Busca en la URL el dominio de Vimeo.

                if (!$esYoutube && !$esVimeo) { // Entra si el enlace no corresponde ni a YouTube ni a Vimeo.
                    $validator->errors()->add('url_video', 'El enlace de video debe ser una URL válida de YouTube o Vimeo.'); // Agrega un error personalizado al campo de URL.
                }
            }
        });
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array // messages() define mensajes personalizados para las reglas de arriba.
    {
        return [ // Devuelve mensajes en español para los distintos campos.
            'titulo.required'         => 'El título del bloque es obligatorio.', // Avisa si no se escribió título.
            'titulo.max'              => 'El título no puede superar los 255 caracteres.', // Avisa si el título supera el límite.
            'texto.required'          => 'El texto o descripción del bloque es obligatorio.', // Avisa si falta el texto descriptivo.
            'tipo_contenido.required' => 'Debes seleccionar el tipo de contenido multimedia.', // Avisa si no se eligió un tipo.
            'tipo_contenido.in'       => 'El tipo de contenido debe ser imagen, audio o video.', // Avisa si el tipo no está entre los permitidos.

            'archivo_imagen.required' => 'Debes adjuntar una imagen para el bloque (formatos permitidos: JPG o PNG).', // Avisa si hace falta adjuntar imagen.
            'archivo_imagen.file'     => 'El archivo seleccionado para imagen no es válido.', // Avisa si el archivo no se reconoce como carga válida.
            'archivo_imagen.mimes'    => 'La imagen debe tener formato JPG o PNG.', // Avisa si el formato de imagen no está permitido.
            'archivo_imagen.max'      => 'La imagen no puede exceder los 5 MB de tamaño.', // Avisa si la imagen supera el límite.

            'archivo_audio.required'  => 'Debes adjuntar un archivo de audio (formatos permitidos: MP3, WAV u OGG).', // Avisa si falta el audio.
            'archivo_audio.file'      => 'El archivo de audio seleccionado no es válido.', // Avisa si el dato no es un archivo válido.
            'archivo_audio.mimes'     => 'El audio debe estar en formato MP3, WAV u OGG.', // Avisa si la extensión de audio no está permitida.
            'archivo_audio.max'       => 'El archivo de audio no puede superar los 10 MB.', // Avisa si el audio excede el tamaño máximo.

            'url_video.required'      => 'Debes ingresar el enlace del video.', // Avisa si falta ingresar la URL.
            'url_video.url'           => 'Debes ingresar una URL válida (ejemplo: https://www.youtube.com/watch?v=...).', // Avisa si el valor no tiene formato de URL válido.
            'url_video.max'           => 'La URL del video es demasiado larga.', // Avisa si la URL supera los 500 caracteres.
        ];
    }
}
