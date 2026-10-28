<?php // Abre el bloque de código PHP.

namespace Modules\ControlECP\Http\Requests; // Define el espacio de nombres para las solicitudes del módulo.

use Illuminate\Foundation\Http\FormRequest; // Importa la clase base de Laravel para validar solicitudes.
use Illuminate\Support\Facades\Auth; // Importa el acceso a la sesión del usuario.

class StoreAsesoriaRequest extends FormRequest // Define autorización y reglas para crear una solicitud de asesoría.
{
    public function authorize(): bool // authorize() comprueba el permiso; solo permite administradores autorizados de ControlECP.
    {
        return Auth::check() && Auth::user()->hasRole('controlecp.admin'); // Exige que el usuario haya iniciado sesión y sea administrador de ControlECP.
    }

    public function rules(): array // rules() entrega las condiciones de validación para una nueva asesoría.
    {
        return [ // Devuelve el arreglo de reglas para que Laravel lo aplique.
            'id_usuario_aprendiz'   => ['required', 'exists:users,id'], // Exige seleccionar un aprendiz cuyo id exista en users.
            'id_categoria_asesoria' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'], // Exige una categoría de asesoría existente.
            'fecha'                 => ['required', 'date'], // Exige una fecha con formato válido.
            'hora_inicio'           => ['required'], // Exige indicar la hora de inicio.
            'hora_fin'              => ['required', 'after:hora_inicio'], // Exige que la hora de fin sea posterior al inicio.
            'motivo'                => ['required', 'string', 'min:5'], // Exige un motivo escrito de al menos cinco caracteres.
            'id_usuario_instructor' => ['nullable', 'exists:users,id'], // Permite no asignar instructor; si se indica, debe existir.
        ];
    }

    public function messages(): array // messages() entrega textos personalizados en español para los errores.
    {
        return [ // Devuelve cada frase asociada al campo y regla que falló.
            'id_usuario_aprendiz.required'   => 'Debes seleccionar el aprendiz que requiere la asesoría.', // Avisa que falta elegir al aprendiz.
            'id_usuario_aprendiz.exists'     => 'El aprendiz seleccionado no es válido.', // Avisa que el id elegido no corresponde a un usuario existente.
            'id_categoria_asesoria.required' => 'Debes seleccionar una categoría temática.', // Avisa que falta elegir una categoría.
            'id_categoria_asesoria.exists'   => 'La categoría seleccionada no existe.', // Avisa que la categoría enviada no se encontró.
            'fecha.required'                 => 'Debes indicar la fecha de la asesoría.', // Avisa que falta indicar la fecha.
            'fecha.date'                     => 'El formato de fecha no es válido.', // Avisa que el formato de la fecha es incorrecto.
            'hora_inicio.required'           => 'La hora de inicio es obligatoria.', // Avisa que falta la hora de inicio.
            'hora_fin.required'              => 'La hora de finalización es obligatoria.', // Avisa que falta la hora de fin.
            'hora_fin.after'                 => 'La hora de fin debe ser posterior a la hora de inicio.', // Avisa que el orden de las horas no es válido.
            'motivo.required'                => 'El motivo o tema de la asesoría es obligatorio.', // Avisa que falta el motivo.
            'motivo.min'                     => 'El motivo debe tener al menos 5 caracteres.', // Avisa que el motivo tiene menos de cinco caracteres.
            'id_usuario_instructor.exists'   => 'El instructor seleccionado no es válido.', // Avisa que el id del instructor no existe.
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator) // failedValidation() indica que el modal de nueva solicitud debe reabrirse si hay errores.
    {
        session()->flash('modal_abrir', 'modalNuevaSolicitud'); // Indica que debe abrirse otra vez el modal de nueva solicitud.
        parent::failedValidation($validator); // Usa el comportamiento estándar de Laravel para devolver los errores.
    }
}
