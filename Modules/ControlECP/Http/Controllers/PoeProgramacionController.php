<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ControlECP\Entities\Poe;
use Modules\ControlECP\Entities\PoeProgramacion;
use Modules\ControlECP\Http\Requests\PoeProgramacionRequest;
use Modules\ControlECP\Services\PoeService;

class PoeProgramacionController extends Controller // Controlador de creación, consulta y cambios de programación de entregas POE.
{
    protected PoeService $poeService;

    public function __construct(PoeService $poeService) // __construct(): Recibe y conserva el servicio de POE; se ejecuta al construir el controlador.
    {
        $this->poeService = $poeService; // Guarda el servicio inyectado para usar su lógica de programación y fechas.
    }

    private function verificarAccesoAdmin() // verificarAccesoAdmin(): Comprueba el rol administrador antes de gestionar programaciones; se usa en las acciones protegidas.
    {
        $usuario = Auth::user(); // Auth::user() devuelve la cuenta autenticada o null si no hay sesión.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Comprueba que exista sesión y tenga el rol administrador requerido.
            return redirect() // route() prepara el destino y with() adjunta un aviso temporal para la página siguiente.
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para gestionar programaciones.');
        }
        return null; // Devuelve null para señalar que el usuario sí puede continuar.
    }

    /**
     * 12. Listar programaciones con filtro de estado (para modal o AJAX)
     */
    public function index(Request $request) // index(): Actualiza estados vencidos y consulta programaciones filtradas; se usa al cargar el listado o modal.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Detiene la acción cuando el control de acceso ya preparó una redirección.
            return $res;
        }

        $this->poeService->actualizarEstadosAtrasadas(); // Recalcular atrasadas al consultar | Actualiza el estado de las entregas vencidas antes de construir el resultado mostrado.

        $query = PoeProgramacion::with(['poe', 'liderActual.person', 'nuevoLider.person']) // with() carga los POE y ambos líderes con sus personas relacionadas para evitar consultas adicionales.
            ->orderBy('id_poe_programacion', 'desc'); // orderBy() solicita primero las programaciones de identificador más alto.

        if ($request->filled('estado')) { // filled() solo es verdadero si llegó un valor no vacío en el parámetro estado.
            $estado = $request->input('estado'); // input() lee el filtro enviado desde la interfaz.
            if ($estado === 'Cancelada') { // Las canceladas se identifican con su bandera; los demás estados usan ambas condiciones.
                $query->where('cancelada', true); // where() agrega condición SQL que selecciona únicamente programaciones canceladas.
            } else {
                $query->where('cancelada', false)->where('estado', $estado); // Excluye canceladas y limita los resultados al estado solicitado.
            }
        }

        if ($request->filled('id_poe')) { // Aplica un filtro opcional para mostrar solo programaciones de un POE concreto.
            $query->where('id_poe', $request->input('id_poe')); // where() agrega la condición SQL usando el identificador recibido.
        }

        $programaciones = $query->get()->map(function ($p) { // get() ejecuta la consulta; map() transforma los modelos en datos apropiados para JSON.
            $estadoVisual = $p->cancelada ? 'Cancelada' : $p->estado; // Muestra «Cancelada» cuando la bandera está activa; en caso contrario usa el estado registrado.
            return [ // Arma cada registro con valores relacionados y fechas preparadas para la interfaz.
                'id_poe_programacion'     => $p->id_poe_programacion,
                'id_poe'                  => $p->id_poe,
                'poe_codigo'              => $p->poe?->codigo ?? 'N/A',
                'poe_nombre'              => $p->poe?->nombre ?? 'N/A',
                'lider_actual'            => $p->liderActual ? $this->poeService->nombreCompletoUsuario($p->liderActual) : 'N/A',
                'nuevo_lider'             => $p->nuevoLider ? $this->poeService->nombreCompletoUsuario($p->nuevoLider) : 'N/A',
                'id_usuario_nuevo_lider'  => $p->id_usuario_nuevo_lider,
                'fecha_limite'            => $p->fecha_limite ? $p->fecha_limite->format('Y-m-d') : '',
                'fecha_limite_formateada' => $p->fecha_limite ? $p->fecha_limite->format('d/m/Y') : '',
                'fecha_original'          => $p->fecha_original ? $p->fecha_original->format('d/m/Y') : null,
                'estado'                  => $p->estado,
                'cancelada'               => $p->cancelada,
                'estado_visual'           => $estadoVisual,
                'motivo_cambio'           => $p->motivo_cambio,
                'fecha_cumplimiento'      => $p->fecha_cumplimiento ? $p->fecha_cumplimiento->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json([ // Devuelve la colección transformada en una respuesta JSON para la página o el modal.
            'programaciones' => $programaciones,
        ]);
    }

    /**
     * 8. Programar fecha de subida para un POE
     */
    public function store(PoeProgramacionRequest $request, $idPoe) // store(): Valida y programa la fecha de entrega de un POE; se usa al registrar una nueva programación.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Si falla la comprobación de administrador, termina con la redirección preparada.
            return $res;
        }

        $poe = Poe::findOrFail($idPoe); // Busca el POE solicitado o responde 404 cuando el identificador no corresponde a uno existente.

        try {
            $this->poeService->programarSubida($poe, $request->validated(), Auth::user()); // validated() entrega solo campos aprobados por PoeProgramacionRequest; Auth::user() identifica al autor.

            return redirect() // Al guardar correctamente, regresa al repositorio y muestra confirmación temporal.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('success', "¡Programación registrada exitosamente para el POE {$poe->codigo}!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $msg = collect($ve->errors())->flatten()->first(); // Convierte los errores de validación en una lista y toma el primer mensaje para mostrarlo.
            return redirect() // Vuelve al formulario, conserva los valores enviados y presenta el mensaje de validación.
                ->back()
                ->withInput()
                ->with('error', $msg);
        } catch (\Throwable $e) {
            return redirect() // Captura otros fallos y vuelve al formulario mostrando su causa.
                ->back()
                ->withInput()
                ->with('error', 'Error al programar: ' . $e->getMessage());
        }
    }

    /**
     * 13. Reprogramar una subida (cambiar fecha límite o nuevo líder, motivo obligatorio)
     */
    public function reprogramar(Request $request, $idProgramacion) // reprogramar(): Valida y cambia fecha o líder con su motivo; se usa al reprogramar una entrega existente.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Impide que usuarios sin rol administrador modifiquen programaciones.
            return $res;
        }

        $request->validate([ // validate() verifica campos obligatorios, formatos, fechas válidas y límites; falla si algo no cumple.
            'fecha_limite'           => ['required', 'date', 'after_or_equal:today'],
            'id_usuario_nuevo_lider' => ['required', 'integer', 'exists:users,id'],
            'motivo_cambio'          => ['required', 'string', 'min:5', 'max:500'],
        ], [ // Estos mensajes sustituyen textos genéricos para los errores configurados.
            'fecha_limite.required'           => 'La nueva fecha límite es obligatoria.',
            'fecha_limite.after_or_equal'     => 'La nueva fecha límite no puede ser anterior a hoy.',
            'id_usuario_nuevo_lider.required' => 'Debe seleccionar el nuevo líder.',
            'motivo_cambio.required'          => 'El motivo de reprogramación es obligatorio.',
            'motivo_cambio.min'               => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $prog = PoeProgramacion::with('poe')->findOrFail($idProgramacion); // Carga la programación y su POE relacionado; findOrFail responde 404 si no existe.

        try {
            $this->poeService->reprogramar($prog, $request->only(['fecha_limite', 'id_usuario_nuevo_lider', 'motivo_cambio']), Auth::user()); // only() limita los campos entregados al servicio y Auth::user() identifica quién reprograma.

            return redirect() // Confirma el cambio y regresa al listado.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('success', "¡Programación del POE {$prog->poe?->codigo} reprogramada exitosamente!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $msg = collect($ve->errors())->flatten()->first(); // Extrae el primer mensaje de validación producido por las reglas del servicio.
            return redirect() // Vuelve a la página anterior con el mensaje temporal de error.
                ->back()
                ->with('error', $msg);
        } catch (\Throwable $e) {
            return redirect() // Maneja otros errores sin exponer una respuesta técnica directa al navegador.
                ->back()
                ->with('error', 'Error al reprogramar: ' . $e->getMessage());
        }
    }

    /**
     * 13. Cancelar una programación (motivo obligatorio, cancelada = true)
     */
    public function cancelar(Request $request, $idProgramacion) // cancelar(): Valida el motivo y cancela una programación; se usa al retirar una entrega pendiente.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Requiere el rol administrador para cambiar el estado de una programación.
            return $res;
        }

        $request->validate([ // validate() exige un motivo de texto con longitud permitida antes de efectuar la cancelación.
            'motivo_cambio' => ['required', 'string', 'min:5', 'max:500'],
        ], [ // Define mensajes más claros para los fallos de los campos indicados.
            'motivo_cambio.required' => 'El motivo de cancelación es obligatorio.',
            'motivo_cambio.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $prog = PoeProgramacion::with('poe')->findOrFail($idProgramacion); // Recupera la programación y su POE para informar su código en la respuesta.

        try {
            $this->poeService->cancelar($prog, $request->input('motivo_cambio'), Auth::user()); // Envía el motivo y el usuario que ejecuta la cancelación al servicio de negocio.

            return redirect() // Regresa al repositorio con una confirmación para la interfaz.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('success', "¡Programación del POE {$prog->poe?->codigo} cancelada exitosamente!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $msg = collect($ve->errors())->flatten()->first(); // Toma el primer error de validación para informar por qué no se realizó la acción.
            return redirect() // Regresa a la página previa y comunica el error como dato temporal de sesión.
                ->back()
                ->with('error', $msg);
        } catch (\Throwable $e) {
            return redirect() // Captura fallos no previstos y muestra un mensaje en la página anterior.
                ->back()
                ->with('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }
}
