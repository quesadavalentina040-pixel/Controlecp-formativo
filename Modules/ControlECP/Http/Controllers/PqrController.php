<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\Pqrs;
use Modules\ControlECP\Entities\PqrsHistorial;
use Modules\ControlECP\Entities\PqrsRespuesta;
use Modules\ControlECP\Entities\TipoPqrs;
use App\Models\User;

class PqrController extends Controller
{
    /**
     * Valida permisos de Administrador o Instructor.
     */
    private function verificarAcceso()
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de PQRS de Control ECP.');
        }

        return null;
    }

    /**
     * 1. BANDEJA PRINCIPAL DE PQRS
     * Separa en dos grupos:
     *  - $pqrsAdmin     → dirigidas a Administración (sin instructor), el admin puede responder
     *  - $pqrsInstructor → dirigidas a un instructor, el admin solo puede visualizar
     */
    public function index(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        // ── Filtros comunes ──────────────────────────────────────────
        $applyFilters = function ($query) use ($request) {
            if ($request->filled('buscar')) {
                $b = trim($request->input('buscar'));
                $query->where(function ($q) use ($b) {
                    $q->where('radicado', 'like', "%{$b}%")
                      ->orWhere('asunto',   'like', "%{$b}%")
                      ->orWhere('descripcion', 'like', "%{$b}%")
                      ->orWhereHas('aprendiz', fn($qa) =>
                          $qa->where('nickname', 'like', "%{$b}%")
                             ->orWhere('email',   'like', "%{$b}%")
                      );
                });
            }
            if ($request->filled('id_tipo_pqrs')) {
                $query->where('id_tipo_pqrs', $request->input('id_tipo_pqrs'));
            }
            if ($request->filled('estado')) {
                $query->where('estado', $request->input('estado'));
            }
            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_radicacion', '>=', $request->input('fecha_desde'));
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_radicacion', '<=', $request->input('fecha_hasta'));
            }
        };

        // ── Grupo 1: PQRS de Administración (sin instructor asignado) ──
        $queryAdmin = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas', 'historial'])
            ->whereNull('id_usuario_instructor_destino');
        $applyFilters($queryAdmin);
        $pqrsAdmin = $queryAdmin->orderBy('fecha_radicacion', 'desc')->paginate(10, ['*'], 'page_admin')->withQueryString();

        // ── Grupo 2: PQRS de Instructores (con instructor asignado) ──
        $queryInstructor = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas', 'historial'])
            ->whereNotNull('id_usuario_instructor_destino');
        if ($request->filled('id_instructor')) {
            $queryInstructor->where('id_usuario_instructor_destino', $request->input('id_instructor'));
        }
        $applyFilters($queryInstructor);
        $pqrsInstructor = $queryInstructor->orderBy('fecha_radicacion', 'desc')->paginate(10, ['*'], 'page_inst')->withQueryString();

        // ── Métricas ─────────────────────────────────────────────────
        $total      = Pqrs::count();
        $pendientes = Pqrs::where('estado', 'Pendiente')->count();
        $enTramite  = Pqrs::where('estado', 'En trámite')->count();
        $resueltas  = Pqrs::whereIn('estado', ['Resuelto', 'Cerrado'])->count();

        // ── Catálogos ─────────────────────────────────────────────────
        $tipos        = TipoPqrs::orderBy('nombre')->get();
        $instructores = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))->get();

        // ── Datos para gráficas del modal de reporte ──────────────────
        $reporteTiposLabels = $tipos->pluck('nombre')->toArray();
        $reporteTiposCounts = $tipos->map(fn($t) => Pqrs::where('id_tipo_pqrs', $t->id_tipo_pqrs)->count())->toArray();

        $reporteEstadosLabels = ['Pendientes', 'En trámite', 'Resueltos'];
        $reporteEstadosCounts = [$pendientes, $enTramite, $resueltas];

        $reporteInstructoresLabels = ['Administración General'];
        $reporteInstructoresCounts = [Pqrs::whereNull('id_usuario_instructor_destino')->count()];
        foreach ($instructores as $inst) {
            $reporteInstructoresLabels[] = $inst->nickname;
            $reporteInstructoresCounts[] = Pqrs::where('id_usuario_instructor_destino', $inst->id)->count();
        }

        return view('controlecp::administrador.pqr', compact(
            'pqrsAdmin',
            'pqrsInstructor',
            'total', 'pendientes', 'enTramite', 'resueltas',
            'tipos', 'instructores',
            'reporteTiposLabels', 'reporteTiposCounts',
            'reporteEstadosLabels', 'reporteEstadosCounts',
            'reporteInstructoresLabels', 'reporteInstructoresCounts'
        ));
    }

    /**
     * 2. DETALLE EN FORMATO JSON (Para cargar en ventana modal sin salir de la bandeja)
     */
    public function getDetalleJson($id)
    {
        $pqr = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas.usuario', 'historial.usuario'])->findOrFail($id);

        $respuestas = $pqr->respuestas->map(function ($r) {
            return [
                'id'        => $r->id_pqrs_respuesta,
                'fecha'     => $r->fecha ? $r->fecha->format('d/m/Y H:i') : '',
                'autor'     => $r->usuario ? $r->usuario->nickname : 'Sistema',
                'respuesta' => $r->respuesta,
            ];
        });

        $historial = $pqr->historial->map(function ($h) {
            return [
                'id'              => $h->id_pqrs_historial,
                'fecha'           => $h->fecha ? $h->fecha->format('d/m/Y H:i') : '',
                'autor'           => $h->usuario ? $h->usuario->nickname : 'Sistema',
                'estado_anterior' => $h->estado_anterior,
                'estado_nuevo'    => $h->estado_nuevo,
                'observacion'     => $h->observacion,
            ];
        });

        return response()->json([
            'id_pqrs'            => $pqr->id_pqrs,
            'radicado'           => $pqr->radicado,
            'asunto'             => $pqr->asunto,
            'descripcion'        => $pqr->descripcion,
            'estado'             => $pqr->estado,
            'estado_badge'       => $pqr->estado_badge,
            'tipo_id'            => $pqr->id_tipo_pqrs,
            'tipo_nombre'        => $pqr->tipo ? $pqr->tipo->nombre : 'Petición',
            'tipo_badge'         => $pqr->tipo_badge,
            'fecha_radicacion'   => $pqr->fecha_radicacion ? $pqr->fecha_radicacion->format('d/m/Y H:i') : '',
            'aprendiz'           => [
                'id'       => $pqr->aprendiz ? $pqr->aprendiz->id : null,
                'nickname' => $pqr->aprendiz ? $pqr->aprendiz->nickname : 'Aprendiz Desconocido',
                'email'    => $pqr->aprendiz ? $pqr->aprendiz->email : 'Sin correo registrado',
            ],
            'instructor'         => [
                'id'       => $pqr->instructorDestino ? $pqr->instructorDestino->id : null,
                'nickname' => $pqr->instructorDestino ? $pqr->instructorDestino->nickname : 'Administración General (Sin asignar)',
            ],
            'respuestas'         => $respuestas,
            'historial'          => $historial,
        ]);
    }

    /**
     * 3. CAMBIAR ESTADO DE UNA PQRS CON AUDITORÍA (Pendiente -> En trámite -> Resuelta)
     */
    public function cambiarEstado(Request $request, $id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $validated = $request->validate([
            'estado'      => 'required|in:Pendiente,En trámite,Resuelta,Resuelto,Cerrada',
            'observacion' => 'nullable|string|max:500',
        ]);

        $pqr = Pqrs::findOrFail($id);
        $estadoAnterior = $pqr->estado;
        $estadoNuevo    = $validated['estado'];

        DB::beginTransaction();
        try {
            $pqr->update(['estado' => $estadoNuevo]);

            PqrsHistorial::create([
                'id_pqrs'         => $pqr->id_pqrs,
                'id_usuario'      => Auth::id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $estadoNuevo,
                'observacion'     => $validated['observacion'] ?: "Cambio de estado realizado por " . (Auth::user()->nickname ?? 'Administrador'),
                'fecha'           => now(),
            ]);

            DB::commit();

            return back()->with('success', "El estado de la solicitud {$pqr->radicado} ha cambiado a '{$estadoNuevo}'. Se notificó al aprendiz.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    /**
     * 3.1. FORMULARIO DEDICADO PARA RADICAR PQRS
     * HU 3: El administrador NO puede radicar una PQRS. Solo los aprendices pueden hacerlo.
     */
    public function create()
    {
        // HU 3: Bloquear acceso al administrador
        $usuario = Auth::user();
        if ($usuario && $usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.administrador.pqr')
                ->with('error', 'El administrador no puede radicar una PQRS. Las solicitudes son radicadas únicamente por los aprendices.');
        }

        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $radicado = Pqrs::generarRadicado();
        $tipos = TipoPqrs::orderBy('nombre')->get();
        $instructores = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))->get();
        $aprendices = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.apprentice'))->get();
        if ($aprendices->isEmpty()) {
            $aprendices = User::limit(25)->get();
        }

        return view('controlecp::administrador.pqr_create', compact(
            'radicado',
            'tipos',
            'instructores',
            'aprendices'
        ));
    }

    /**
     * 3.2. VISTA DEDICADA DETALLE DE PQRS
     */
    public function show($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $pqr = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas.usuario', 'historial.usuario'])->findOrFail($id);
        $tipos = TipoPqrs::orderBy('nombre')->get();
        $instructores = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))->get();
        $estados = ['Pendiente', 'En trámite', 'Resuelto', 'Cerrado'];

        return view('controlecp::administrador.pqr_show', compact('pqr', 'tipos', 'instructores', 'estados'));
    }

    /**
     * 3.3. HISTORIAL PERSONAL Y TRAZABILIDAD EN FORMATO JSON (HU 8)
     */
    public function getHistorialJson($id)
    {
        $pqr = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas.usuario', 'historial.usuario'])->findOrFail($id);

        $eventos = collect();

        // 1. Evento de Radicación
        $nombreSolicitante = $pqr->aprendiz ? ($pqr->aprendiz->nickname ?? $pqr->aprendiz->name) : 'Solicitante';
        $eventos->push([
            'tipo'        => 'radicacion',
            'titulo'      => 'Radicación inicial de la solicitud',
            'badge'       => 'Radicada',
            'badge_class' => 'bg-secondary text-white',
            'fecha'       => $pqr->fecha_radicacion ? $pqr->fecha_radicacion->format('d/m/Y H:i') : '',
            'timestamp'   => $pqr->fecha_radicacion ? $pqr->fecha_radicacion->timestamp : 0,
            'autor'       => $nombreSolicitante,
            'descripcion' => "Solicitud radicada bajo el tipo {$pqr->tipo_nombre} con asunto: \"{$pqr->asunto}\".",
            'icono'       => 'fas fa-file-circle-plus',
            'color'       => '#0288D1',
        ]);

        // 2. Eventos de Cambios de Estado (Auditoría)
        foreach ($pqr->historial as $h) {
            $badgeClass = match ($h->estado_nuevo) {
                'Resuelta', 'Resuelto' => 'bg-success text-white',
                'En trámite', 'En tramite' => 'bg-primary text-white',
                'Cerrada', 'Cerrado' => 'bg-dark text-white',
                default => 'bg-warning text-dark',
            };

            $eventos->push([
                'tipo'        => 'estado',
                'titulo'      => "Cambio de estado: " . ($h->estado_anterior ? "{$h->estado_anterior} → " : "") . "{$h->estado_nuevo}",
                'badge'       => $h->estado_nuevo,
                'badge_class' => $badgeClass,
                'fecha'       => $h->fecha ? $h->fecha->format('d/m/Y H:i') : '',
                'timestamp'   => $h->fecha ? $h->fecha->timestamp : 0,
                'autor'       => $h->usuario ? ($h->usuario->nickname ?? $h->usuario->name) : 'Sistema',
                'descripcion' => $h->observacion ?: 'Cambio de estado registrado en el sistema.',
                'icono'       => 'fas fa-arrows-rotate',
                'color'       => '#F59E0B',
            ]);
        }

        // 3. Eventos de Respuestas Oficiales Emitidas (HU 7)
        foreach ($pqr->respuestas as $r) {
            $esAdmin = $r->usuario && $r->usuario->hasRole('controlecp.admin');
            $esInstructor = $r->usuario && $r->usuario->hasRole('controlecp.instructor');
            $rolLabel = $esAdmin ? 'Administrador' : ($esInstructor ? 'Instructor' : 'Funcionario');

            $eventos->push([
                'tipo'        => 'respuesta',
                'titulo'      => "Respuesta oficial emitida por {$rolLabel}",
                'badge'       => "Respuesta ({$rolLabel})",
                'badge_class' => 'bg-success text-white',
                'fecha'       => $r->fecha ? $r->fecha->format('d/m/Y H:i') : '',
                'timestamp'   => $r->fecha ? $r->fecha->timestamp : 0,
                'autor'       => ($r->usuario ? ($r->usuario->nickname ?? $r->usuario->name) : 'Sistema') . " ({$rolLabel})",
                'descripcion' => $r->respuesta,
                'icono'       => 'fas fa-reply',
                'color'       => '#10B981',
            ]);
        }

        $eventosOrdenados = $eventos->sortByDesc('timestamp')->values();

        return response()->json([
            'id_pqrs'          => $pqr->id_pqrs,
            'radicado'         => $pqr->radicado,
            'asunto'           => $pqr->asunto,
            'estado'           => $pqr->estado,
            'estado_badge'     => $pqr->estado_badge,
            'tipo_nombre'      => $pqr->tipo ? $pqr->tipo->nombre : 'Petición',
            'fecha_radicacion' => $pqr->fecha_radicacion ? $pqr->fecha_radicacion->format('d/m/Y H:i') : '',
            'aprendiz'         => $nombreSolicitante,
            'eventos'          => $eventosOrdenados,
        ]);
    }

    /**
     * 4. REGISTRAR RESPUESTA FORMAL A UNA PQRS (HU 3 & HU 7)
     * Permite responder como Administrador y actualiza correctamente el estado a 'Resuelta'.
     */
    public function responder(Request $request, $id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $validated = $request->validate([
            'respuesta'      => 'required|string|min:10',
            'cambiar_estado' => 'nullable|string',
            'estado'         => 'nullable|string',
        ], [
            'respuesta.min'      => 'La respuesta debe ser clara y contener al menos 10 caracteres.',
            'respuesta.required' => 'El texto de la respuesta es obligatorio.',
        ]);

        $pqr = Pqrs::findOrFail($id);

        DB::beginTransaction();
        try {
            // Guardar respuesta oficial
            PqrsRespuesta::create([
                'id_pqrs'    => $pqr->id_pqrs,
                'id_usuario' => Auth::id(),
                'respuesta'  => trim($validated['respuesta']),
                'fecha'      => now(),
            ]);

            $estadoAnterior = $pqr->estado;

            // HU 3: Determinar qué estado eligió el usuario en el formulario.
            // Se acepta tanto 'cambiar_estado' (modal bandeja) como 'estado' (vista show).
            $estadoSolicitado = $request->input('cambiar_estado')
                             ?: $request->input('estado')
                             ?: 'Resuelto';

            // Normalización: mapear cualquier variante al valor exacto que usa la BD.
            // Los valores válidos son los de Pqrs::ESTADOS: Pendiente, En trámite, Resuelto, Cerrado
            $estadoNuevo = match (strtolower(trim($estadoSolicitado))) {
                'en trámite', 'en tramite' => 'En trámite',
                'cerrado', 'cerrada'       => 'Cerrado',
                'pendiente'                => 'Pendiente',
                'mantener'                 => $estadoAnterior,   // no cambiar
                default                    => 'Resuelto',         // resuelta / resuelto / cualquier otro
            };

            // HU 3: Persistir el nuevo estado usando update() para garantizar que se guarda
            $pqr->update(['estado' => $estadoNuevo]);

            // Identificar rol emisor (Administrador / Instructor)
            $usuarioActual = Auth::user();
            $rolEmisor  = ($usuarioActual && $usuarioActual->hasRole('controlecp.admin')) ? 'Administrador' : 'Instructor';
            $nombreEmisor = $usuarioActual ? ($usuarioActual->nickname ?? $usuarioActual->name) : 'Sistema';

            // Registrar en historial de trazabilidad
            PqrsHistorial::create([
                'id_pqrs'         => $pqr->id_pqrs,
                'id_usuario'      => Auth::id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $estadoNuevo,
                'observacion'     => "Respuesta oficial emitida por {$rolEmisor} ({$nombreEmisor}). Estado actualizado a '{$estadoNuevo}'.",
                'fecha'           => now(),
            ]);

            DB::commit();

            return back()->with('success', "Respuesta registrada para {$pqr->radicado}. Estado actualizado a '{$estadoNuevo}'.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar respuesta: ' . $e->getMessage());
        }
    }

    /**
     * 5. RADICAR NUEVA PQRS
     * HU 3: El administrador NO puede radicar. Solo los aprendices están autorizados.
     */
    public function store(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        // HU 3: Bloquear radicación por parte del administrador
        $usuario = Auth::user();
        if ($usuario && $usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.administrador.pqr')
                ->with('error', 'El administrador no puede radicar una PQRS. Las solicitudes deben ser radicadas por los aprendices desde su panel.');
        }

        // Resolver descripción si viene como mensaje (compatibilidad formulario aprendiz)
        if (!$request->filled('descripcion') && $request->filled('mensaje')) {
            $request->merge(['descripcion' => $request->input('mensaje')]);
        }

        $validated = $request->validate([
            'id_tipo_pqrs'                  => 'required|exists:tipos_pqrs,id_tipo_pqrs',
            'id_usuario_aprendiz'           => 'required|exists:users,id',
            'id_usuario_instructor_destino' => 'nullable|exists:users,id',
            'asunto'                        => 'required|string|max:255',
            'descripcion'                   => 'required|string|min:10',
        ], [
            'id_tipo_pqrs.required' => 'Debes seleccionar el tipo de PQRS.',
            'asunto.required'       => 'El asunto de la solicitud es obligatorio.',
            'descripcion.required'  => 'La descripción de la solicitud es obligatoria.',
            'descripcion.min'       => 'La descripción debe tener al menos 10 caracteres.',
        ]);

        DB::beginTransaction();
        try {
            $radicado = Pqrs::generarRadicado();

            $pqr = Pqrs::create([
                'radicado'                      => $radicado,
                'id_usuario_aprendiz'           => $validated['id_usuario_aprendiz'],
                'id_tipo_pqrs'                  => $validated['id_tipo_pqrs'],
                'id_usuario_instructor_destino' => $validated['id_usuario_instructor_destino'] ?: null,
                'asunto'                        => trim($validated['asunto']),
                'descripcion'                   => trim($validated['descripcion']),
                'estado'                        => 'Pendiente',
                'fecha_radicacion'              => now(),
            ]);

            PqrsHistorial::create([
                'id_pqrs'         => $pqr->id_pqrs,
                'id_usuario'      => Auth::id(),
                'estado_anterior' => null,
                'estado_nuevo'    => 'Pendiente',
                'observacion'     => "Solicitud radicada por " . ($usuario->nickname ?? $usuario->name) . ".",
                'fecha'           => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('controlecp.administrador.pqr')
                ->with('success', "¡PQRS radicada exitosamente! Radicado: {$radicado}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al radicar la PQRS: ' . $e->getMessage());
        }
    }

    /**
     * 6. REPORTES DE PQRS (Con soporte para impresión PDF)
     */
    public function reporte(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $query = Pqrs::with(['tipo', 'aprendiz', 'instructorDestino', 'respuestas']);

        if ($request->filled('id_tipo_pqrs')) {
            $query->where('id_tipo_pqrs', $request->input('id_tipo_pqrs'));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('id_instructor')) {
            if ($request->input('id_instructor') === 'admin') {
                $query->whereNull('id_usuario_instructor_destino');
            } else {
                $query->where('id_usuario_instructor_destino', $request->input('id_instructor'));
            }
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_radicacion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_radicacion', '<=', $request->input('fecha_hasta'));
        }

        $pqrs = $query->orderBy('fecha_radicacion', 'desc')->get();

        $total      = $pqrs->count();
        $pendientes = $pqrs->where('estado', 'Pendiente')->count();
        $enTramite  = $pqrs->where('estado', 'En trámite')->count();
        $resueltas  = $pqrs->whereIn('estado', ['Resuelta', 'Resuelto', 'Cerrado', 'Cerrada'])->count();

        $tipos = TipoPqrs::all();
        $instructores = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))->get();

        // Datos para gráficas en la vista de reporte
        $reporteTiposLabels = [];
        $reporteTiposCounts = [];
        foreach ($tipos as $t) {
            $reporteTiposLabels[] = $t->nombre;
            $reporteTiposCounts[] = $pqrs->where('id_tipo_pqrs', $t->id_tipo_pqrs)->count();
        }

        $reporteEstadosLabels = ['Pendientes', 'En trámite', 'Resueltas'];
        $reporteEstadosCounts = [$pendientes, $enTramite, $resueltas];

        $reporteInstructoresLabels = ['Administración General'];
        $reporteInstructoresCounts = [$pqrs->whereNull('id_usuario_instructor_destino')->count()];
        foreach ($instructores as $inst) {
            $reporteInstructoresLabels[] = $inst->nickname;
            $reporteInstructoresCounts[] = $pqrs->where('id_usuario_instructor_destino', $inst->id)->count();
        }

        $tasaResolucion = $total > 0 ? round(($resueltas / $total) * 100, 1) : 0;

        return view('controlecp::administrador.pqr_reporte', compact(
            'pqrs',
            'total',
            'pendientes',
            'enTramite',
            'resueltas',
            'tipos',
            'instructores',
            'reporteTiposLabels',
            'reporteTiposCounts',
            'reporteEstadosLabels',
            'reporteEstadosCounts',
            'reporteInstructoresLabels',
            'reporteInstructoresCounts',
            'tasaResolucion'
        ));
    }

    /**
     * 7. ELIMINAR PQRS (Con SoftDeletes)
     */
    public function destroy($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $pqr = Pqrs::findOrFail($id);
        $pqr->delete();

        return back()->with('success', "La solicitud {$pqr->radicado} ha sido eliminada.");
    }
}
