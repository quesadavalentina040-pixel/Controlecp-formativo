<?php

namespace Modules\ControlECP\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\ActividadEcp;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\EncuestaEcp;
use Modules\ControlECP\Entities\EvidenciaEcp;
use Modules\ControlECP\Entities\CalificacionEcp;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Apprentice;

class ActividadController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // HU3 — Listado agrupado por momento
    // ══════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a Actividades de Control ECP.');
        }

        $momentos = Momento::with([
            'actividades' => fn($q) => $q->withCount('evidencias')->withCount('calificaciones'),
        ])
        ->withCount(['actividades', 'actividades as activas_count' => fn($q) => $q->where('estado', 'Activo')])
        ->orderBy('orden')
        ->get();

        $encuestasActivas = EncuestaEcp::where('estado', 'Activo')->get();

        $stats = [
            'total'       => ActividadEcp::count(),
            'activas'     => ActividadEcp::where('estado', 'Activo')->count(),
            'inactivas'   => ActividadEcp::where('estado', 'Inactivo')->count(),
            'finalizadas' => ActividadEcp::where('estado', 'Finalizado')->count(),
            'momentos'    => $momentos->count(),
            'fichas'      => Course::where('status', 'Activo')->count(),
        ];

        return view('controlecp::administrador.actividades', compact(
            'momentos', 'encuestasActivas', 'stats'
        ));
    }

    // ══════════════════════════════════════════════════════════
    // HU1 / HU2 — Registrar actividad (con validaciones)
    // ══════════════════════════════════════════════════════════

    public function store(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        // HU2 — Validar campos obligatorios
        $request->validate([
            'momento_id'  => 'required|exists:controlecp_momentos,id',
            'nombre'      => 'required|string|max:200',
            'objetivo'    => 'required|string',
            'duracion'    => 'required|string|max:30',
            'duracion_min'=> 'required|integer|min:15',
        ], [
            'momento_id.required'  => 'Debes seleccionar un momento.',
            'nombre.required'      => 'El nombre de la actividad es obligatorio.',
            'objetivo.required'    => 'El objetivo pedagógico es obligatorio.',
            'duracion.required'    => 'La duración es obligatoria.',
            'duracion_min.required'=> 'La duración en minutos es obligatoria.',
        ]);

        // HU2 — Nombre no repetido en el mismo momento
        $existe = ActividadEcp::where('momento_id', $request->momento_id)
            ->where('nombre', $request->nombre)
            ->exists();
        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una actividad con ese nombre en el momento seleccionado.',
                'field'   => 'nombre',
            ], 422);
        }

        // HU8 — Validar encuesta si se eligió componente encuesta
        $encuestaId = null;
        if ($request->boolean('tiene_encuesta')) {
            if (empty($request->encuesta_id)) {
                $hayEncuestas = EncuestaEcp::where('estado', 'Activo')->exists();
                if (!$hayEncuestas) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Primero cree y active una encuesta en el repositorio antes de asociarla a esta actividad.',
                        'field'   => 'encuesta_id',
                    ], 422);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar una encuesta activa del repositorio.',
                    'field'   => 'encuesta_id',
                ], 422);
            }
            $encuesta = EncuestaEcp::where('id', $request->encuesta_id)
                ->where('estado', 'Activo')->first();
            if (!$encuesta) {
                return response()->json([
                    'success' => false,
                    'message' => 'La encuesta seleccionada no está activa. Elige una encuesta activa del repositorio.',
                    'field'   => 'encuesta_id',
                ], 422);
            }
            $encuestaId = $encuesta->id;
        }

        // Número de orden automático dentro del momento
        $maxNumero = ActividadEcp::where('momento_id', $request->momento_id)->count();
        $numero    = str_pad($maxNumero + 1, 2, '0', STR_PAD_LEFT);

        // HU1 — Registrar con estado Activo
        $actividad = ActividadEcp::create([
            'momento_id'       => $request->momento_id,
            'numero'           => $numero,
            'nombre'           => trim($request->nombre),
            'descripcion'      => trim($request->descripcion ?? ''),
            'objetivo'         => trim($request->objetivo),
            'duracion'         => $request->duracion,
            'duracion_min'     => (int) $request->duracion_min,
            'metodologia'      => trim($request->metodologia ?? ''),
            'estado'           => 'Activo',
            'tiene_asistencia' => $request->boolean('tiene_asistencia', true),
            'encuesta_id'      => $encuestaId,
            'tiene_evidencia'  => $request->boolean('tiene_evidencia', true),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "La actividad \"{$actividad->nombre}\" fue registrada exitosamente con estado Activo.",
            'actividad' => $this->formatActividad($actividad->load('momento', 'encuesta')),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU4 — Editar actividad
    // ══════════════════════════════════════════════════════════

    public function update(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:200',
            'objetivo'    => 'required|string',
            'duracion'    => 'required|string|max:30',
            'duracion_min'=> 'required|integer|min:15',
        ], [
            'nombre.required'  => 'El nombre de la actividad es obligatorio.',
            'objetivo.required'=> 'El objetivo pedagógico es obligatorio.',
        ]);

        // HU2 — Nombre no repetido en el mismo momento (excluye la propia actividad)
        $existe = ActividadEcp::where('momento_id', $actividad->momento_id)
            ->where('nombre', $request->nombre)
            ->where('id', '!=', $id)
            ->exists();
        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otra actividad con ese nombre en el mismo momento.',
                'field'   => 'nombre',
            ], 422);
        }

        // HU7 — Validar encuesta si ya tiene respuestas no permite cambiarla
        $encuestaId = $actividad->encuesta_id;
        if ($request->boolean('tiene_encuesta')) {
            $nuevaEncuestaId = $request->input('encuesta_id');
            if ($nuevaEncuestaId && $nuevaEncuestaId != $encuestaId) {
                // Solo bloquear cambio si la encuesta actual tiene respuestas
                // (simplificado: no hay tabla de respuestas aún, se permite cambio)
                $encuestaId = $nuevaEncuestaId;
            }
        } else {
            $encuestaId = null;
        }

        $actividad->update([
            'nombre'           => trim($request->nombre),
            'descripcion'      => trim($request->descripcion ?? ''),
            'objetivo'         => trim($request->objetivo),
            'duracion'         => $request->duracion,
            'duracion_min'     => (int) $request->duracion_min,
            'metodologia'      => trim($request->metodologia ?? ''),
            'tiene_asistencia' => $request->boolean('tiene_asistencia', true),
            'encuesta_id'      => $encuestaId,
            'tiene_evidencia'  => $request->boolean('tiene_evidencia', true),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "La actividad \"{$actividad->nombre}\" fue actualizada correctamente.",
            'actividad' => $this->formatActividad($actividad->fresh()->load('momento', 'encuesta')),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU5 — Cambiar momento de una actividad
    // ══════════════════════════════════════════════════════════

    public function cambiarMomento(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::with('momento')->findOrFail($id);
        $nuevoMomentoId = $request->input('momento_id');

        $nuevoMomento = Momento::findOrFail($nuevoMomentoId);

        if ($actividad->momento_id == $nuevoMomentoId) {
            return response()->json([
                'success' => false,
                'message' => 'La actividad ya pertenece a ese momento.',
            ], 422);
        }

        // Recalcular número de orden en el nuevo momento
        $maxNumero = ActividadEcp::where('momento_id', $nuevoMomentoId)->count();
        $numero    = str_pad($maxNumero + 1, 2, '0', STR_PAD_LEFT);

        $momentoAnterior = $actividad->momento->nombre;
        $actividad->update([
            'momento_id' => $nuevoMomentoId,
            'numero'     => $numero,
        ]);

        return response()->json([
            'success' => true,
            'message' => "La actividad fue movida de \"{$momentoAnterior}\" a \"{$nuevoMomento->nombre}\" y los contadores fueron actualizados.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU10 — Ver los 3 componentes de una actividad
    // ══════════════════════════════════════════════════════════

    public function componentes($id)
    {
        $actividad = ActividadEcp::with(['momento', 'encuesta'])->findOrFail($id);

        return response()->json([
            'success'          => true,
            'actividad_id'     => $actividad->id,
            'actividad_nombre' => $actividad->nombre,
            'momento'          => $actividad->momento?->nombre,
            'estado'           => $actividad->estado,
            'componentes'      => [
                'asistencia' => [
                    'activo'       => $actividad->tiene_asistencia,
                    'descripcion'  => 'Registro de asistencia por ficha. Al programar la actividad se carga el checklist de aprendices de esa ficha.',
                    'icono'        => 'fa-clipboard-list',
                    'color'        => '#39A900',
                ],
                'encuesta' => [
                    'activo'       => !is_null($actividad->encuesta_id),
                    'encuesta_id'  => $actividad->encuesta_id,
                    'encuesta'     => $actividad->encuesta?->titulo,
                    'descripcion'  => 'Encuesta del repositorio asociada a esta actividad.',
                    'icono'        => 'fa-clipboard-question',
                    'color'        => '#0070B8',
                ],
                'evidencia' => [
                    'activo'      => $actividad->tiene_evidencia,
                    'descripcion' => 'Fotos (jpg/png) o enlaces de video como evidencia de la actividad.',
                    'icono'       => 'fa-photo-film',
                    'color'       => '#6A1B9A',
                ],
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU11 — Desactivar actividad
    // ══════════════════════════════════════════════════════════

    public function deactivate($id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::findOrFail($id);

        if ($actividad->estado === 'Inactivo') {
            return response()->json(['success' => false, 'message' => 'La actividad ya está inactiva.']);
        }
        if ($actividad->estado === 'Finalizado') {
            return response()->json(['success' => false, 'message' => 'Una actividad finalizada no se puede desactivar directamente.']);
        }

        // HU11 — No desactivar si tiene programaciones pendientes
        // (simplificado: verificar si tiene evidencias sin completar)
        $evidenciasPendientes = EvidenciaEcp::where('actividad_id', $id)->count();
        if ($evidenciasPendientes > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede desactivar \"{$actividad->nombre}\": tiene {$evidenciasPendientes} evidencia(s) registrada(s). Finaliza la actividad en lugar de desactivarla.",
            ]);
        }

        $actividad->estado = 'Inactivo';
        $actividad->save();

        return response()->json([
            'success' => true,
            'message' => "La actividad \"{$actividad->nombre}\" fue desactivada y retirada de las opciones activas. Su información y registros históricos se conservan.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU12 — Activar actividad
    // ══════════════════════════════════════════════════════════

    public function activate($id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::findOrFail($id);

        if ($actividad->estado === 'Activo') {
            return response()->json(['success' => false, 'message' => 'La actividad ya está activa.']);
        }

        $actividad->estado = 'Activo';
        $actividad->save();

        return response()->json([
            'success' => true,
            'message' => "La actividad \"{$actividad->nombre}\" fue reactivada y está disponible para programar.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU13 / HU14 — Finalizar actividad (con validación de asistencia)
    // ══════════════════════════════════════════════════════════

    public function finalizar(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::findOrFail($id);

        if ($actividad->estado === 'Finalizado') {
            return response()->json(['success' => false, 'message' => 'La actividad ya está finalizada.']);
        }
        if ($actividad->estado === 'Inactivo') {
            return response()->json(['success' => false, 'message' => 'No puedes finalizar una actividad inactiva. Actívala primero.']);
        }

        // HU14 — Verificar asistencia registrada (si el componente asistencia está activo)
        if ($actividad->tiene_asistencia) {
            $evidenciasRegistradas = EvidenciaEcp::where('actividad_id', $id)->count();
            if ($evidenciasRegistradas === 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede finalizar \"{$actividad->nombre}\": la actividad no tiene asistencia ni evidencias registradas. Registra al menos una evidencia antes de finalizar.",
                    'tipo'    => 'sin_asistencia',
                ]);
            }
        }

        $actividad->estado = 'Finalizado';
        $actividad->save();

        // HU13 — Habilitar encuesta para aprendices si la actividad tiene encuesta
        $encuestaHabilitada = false;
        if ($actividad->encuesta_id) {
            $encuesta = EncuestaEcp::find($actividad->encuesta_id);
            if ($encuesta && $encuesta->estado === 'Activo') {
                $encuestaHabilitada = true;
            }
        }

        $mensaje = "La actividad \"{$actividad->nombre}\" fue finalizada exitosamente.";
        if ($encuestaHabilitada) {
            $mensaje .= " La encuesta asociada está disponible para los aprendices.";
        }

        return response()->json([
            'success'              => true,
            'message'              => $mensaje,
            'encuesta_habilitada'  => $encuestaHabilitada,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU15 / HU16 — Subir evidencia con validación de tipo
    // ══════════════════════════════════════════════════════════

    public function subirEvidencia(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $actividad = ActividadEcp::findOrFail($id);

        if (!$actividad->tiene_evidencia) {
            return response()->json(['success' => false, 'message' => 'Esta actividad no tiene habilitado el componente de evidencia.']);
        }

        $tipo = $request->input('tipo'); // 'foto' o 'video'

        // HU16 — Validar tipo de evidencia
        if ($tipo === 'foto') {
            $request->validate([
                'archivo'   => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5 MB
                'course_id' => 'required|exists:courses,id',
            ], [
                'archivo.required' => 'Debes seleccionar una foto.',
                'archivo.image'    => 'El archivo debe ser una imagen.',
                'archivo.mimes'    => 'Solo se aceptan fotos en formato JPG o PNG.',
                'archivo.max'      => 'La foto no puede superar los 5 MB.',
            ]);

            $path = $request->file('archivo')->store(
                'controlecp/evidencias/' . $actividad->id,
                'public'
            );

            $evidencia = EvidenciaEcp::create([
                'actividad_id'   => $actividad->id,
                'course_id'      => $request->course_id,
                'user_id'        => $usuario->id,
                'tipo'           => 'foto',
                'archivo'        => $path,
                'descripcion'    => $request->input('descripcion', ''),
            ]);

        } elseif ($tipo === 'video') {
            $request->validate([
                'enlace'    => 'required|url|max:500',
                'course_id' => 'required|exists:courses,id',
            ], [
                'enlace.required' => 'Debes ingresar el enlace del video.',
                'enlace.url'      => 'El enlace debe ser una URL válida (YouTube, Drive, etc.).',
            ]);

            $evidencia = EvidenciaEcp::create([
                'actividad_id'   => $actividad->id,
                'course_id'      => $request->course_id,
                'user_id'        => $usuario->id,
                'tipo'           => 'video',
                'enlace'         => $request->enlace,
                'descripcion'    => $request->input('descripcion', ''),
            ]);

        } else {
            // HU16 — Tipo no válido
            return response()->json([
                'success' => false,
                'message' => 'Tipo de evidencia no válido. Solo se aceptan fotos (JPG/PNG) o enlaces de video.',
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Evidencia registrada exitosamente.',
            'evidencia' => [
                'id'             => $evidencia->id,
                'tipo'           => $evidencia->tipo,
                'url'            => $evidencia->url,
                'descripcion'    => $evidencia->descripcion,
                'fecha_registro' => $evidencia->fecha_registro->format('d/m/Y H:i'),
                'usuario'        => $usuario->full_name,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU17 — Ver evidencias de una actividad
    // ══════════════════════════════════════════════════════════

    public function evidencias($id)
    {
        $actividad = ActividadEcp::with('momento')->findOrFail($id);

        $evidencias = EvidenciaEcp::with(['ficha.program', 'registradoPor.person'])
            ->where('actividad_id', $id)
            ->orderByDesc('fecha_registro')
            ->get()
            ->map(fn($e) => [
                'id'             => $e->id,
                'tipo'           => $e->tipo,
                'url'            => $e->url,
                'descripcion'    => $e->descripcion,
                'fecha_registro' => $e->fecha_registro?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
                'usuario'        => $e->registradoPor?->full_name ?? 'Desconocido',
                'ficha'          => $e->ficha ? "{$e->ficha->code} — " . ($e->ficha->program?->name ?? '') : 'Sin ficha',
            ]);

        return response()->json([
            'success'            => true,
            'actividad_id'       => $actividad->id,
            'actividad_nombre'   => $actividad->nombre,
            'total_evidencias'   => $evidencias->count(),
            'evidencias'         => $evidencias,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // HU18 — Consultar calificaciones (promedio + comentarios)
    // ══════════════════════════════════════════════════════════

    public function calificaciones($id)
    {
        $actividad = ActividadEcp::with('momento')->findOrFail($id);

        $calificaciones = CalificacionEcp::with(['aprendiz.person', 'ficha.program'])
            ->where('actividad_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $promedio      = $calificaciones->avg('estrellas') ?? 0;
        $distribucion  = [];
        for ($i = 5; $i >= 1; $i--) {
            $count          = $calificaciones->where('estrellas', $i)->count();
            $distribucion[] = [
                'estrellas' => $i,
                'cantidad'  => $count,
                'pct'       => $calificaciones->count() > 0
                    ? round(($count / $calificaciones->count()) * 100)
                    : 0,
            ];
        }

        return response()->json([
            'success'          => true,
            'actividad_id'     => $actividad->id,
            'actividad_nombre' => $actividad->nombre,
            'total'            => $calificaciones->count(),
            'promedio'         => round($promedio, 1),
            'distribucion'     => $distribucion,
            'comentarios'      => $calificaciones->map(fn($c) => [
                'id'         => $c->id,
                'estrellas'  => $c->estrellas,
                'comentario' => $c->comentario,
                'aprendiz'   => $c->aprendiz?->full_name ?? 'Anónimo',
                'ficha'      => $c->ficha ? $c->ficha->code : '—',
                'fecha'      => $c->created_at?->format('d/m/Y') ?? '—',
            ]),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // Helper privado para formatear la actividad como JSON
    // ══════════════════════════════════════════════════════════

    private function formatActividad(ActividadEcp $a): array
    {
        return [
            'id'               => $a->id,
            'numero'           => $a->numero,
            'nombre'           => $a->nombre,
            'descripcion'      => $a->descripcion,
            'objetivo'         => $a->objetivo,
            'duracion'         => $a->duracion,
            'duracion_min'     => $a->duracion_min,
            'metodologia'      => $a->metodologia,
            'estado'           => $a->estado,
            'tiene_asistencia' => $a->tiene_asistencia,
            'encuesta_id'      => $a->encuesta_id,
            'encuesta'         => $a->encuesta?->titulo,
            'tiene_evidencia'  => $a->tiene_evidencia,
            'momento_id'       => $a->momento_id,
            'momento_nombre'   => $a->momento?->nombre,
            'momento_color'    => $a->momento?->color,
            'momento_icono'    => $a->momento?->icono,
        ];
    }
}
