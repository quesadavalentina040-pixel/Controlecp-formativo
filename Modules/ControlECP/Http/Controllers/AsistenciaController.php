<?php

namespace Modules\ControlECP\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Apprentice;
use Modules\ControlECP\Entities\AsistenciaRegistro;

/**
 * AsistenciaController — Control ECP
 *
 * Implementa los 13 casos de uso del módulo de asistencia:
 * HU-001 Registrar asistencia       HU-008 Ver detalle actividad
 * HU-002 Registrar evidencia        HU-009 Consultar por aprendiz
 * HU-003 Validar registro           HU-010 Ver historial de cambios
 * HU-004 Editar asistencia          HU-011 Actividades sin asistencia
 * HU-005 Anular asistencia          HU-012 Mostrar indicadores
 * HU-006 Mostrar listado            HU-013 Generar reporte
 * HU-007 Filtrar asistencias
 *
 * Preparado para integración con API REST Central ERP SENA Empresa / SICEFA.
 */
class AsistenciaController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // PANEL ADMINISTRADOR
    // ════════════════════════════════════════════════════════════════════════

    /**
     * HU-006 · HU-007 · HU-011 · HU-012
     * Vista principal del administrador: listado, filtros e indicadores.
     */
    public function adminIndex(Request $request)
    {
        if (!Auth::user()->hasRole('controlecp.admin')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'Sin permisos para Asistencia.');
        }

        // ── Filtros (HU-007) ──────────────────────────────────────────────
        $filtros = $request->only(['ficha', 'actividad_id', 'instructor_id', 'fecha', 'estado', 'documento']);

        $query = AsistenciaRegistro::with(['course.program', 'instructor.person'])
            ->where('estado', '!=', 'ANULADA');

        if (!empty($filtros['ficha'])) {
            $query->whereHas('course', fn($q) => $q->where('code', 'like', '%'.$filtros['ficha'].'%'));
        }
        if (!empty($filtros['actividad_id'])) {
            $query->where('actividad_id', $filtros['actividad_id']);
        }
        if (!empty($filtros['instructor_id'])) {
            $query->where('instructor_id', $filtros['instructor_id']);
        }
        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['documento'])) {
            $query->where('document_number', 'like', '%'.$filtros['documento'].'%');
        }

        $registros = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->withQueryString();

        // ── HU-012: Indicadores ────────────────────────────────────────────
        $indicadores = $this->calcularIndicadores();

        // ── HU-011: Actividades sin asistencia ────────────────────────────
        $actividadesSinAsistencia = $this->getActividadesSinAsistencia();

        // Instructores para el filtro
        $instructores = \App\Models\User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))
            ->with('person')
            ->get();

        return view('controlecp::administrador.asistencia', compact(
            'registros', 'filtros', 'indicadores',
            'actividadesSinAsistencia', 'instructores'
        ));
    }

    // ════════════════════════════════════════════════════════════════════════
    // PANEL INSTRUCTOR
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Vista principal del instructor: selector de ficha/actividad.
     */
    public function instructorIndex()
    {
        if (!Auth::user()->hasRole('controlecp.instructor')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'Sin permisos para registrar asistencias.');
        }

        $fichas = Course::with(['program'])
            ->withCount('apprentices')
            ->where(fn($q) => $q->where('status', 'Activo')->orWhereNull('status'))
            ->orderByDesc('code')->get();

        $actividades = $this->getCatalogoActividades();

        $historial = AsistenciaRegistro::where('instructor_id', Auth::id())
            ->with(['course.program'])
            ->orderByDesc('fecha')->orderByDesc('id')
            ->take(25)->get();

        return view('controlecp::instructor.asistencia', compact('fichas', 'actividades', 'historial'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // API ENDPOINTS (AJAX)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Carga la lista de aprendices de una ficha desde la API de SICEFA o BD local.
     * Usado por el instructor al presionar "Cargar Lista".
     */
    public function getAprendicesFicha(Request $request, int $fichaId)
    {
        $course = Course::with(['program', 'apprentices.person'])->find($fichaId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Ficha no encontrada.'], 404);
        }

        $actividadId = $request->integer('actividad_id');
        $fecha       = $request->input('fecha', today()->format('Y-m-d'));

        // Intentar API Central de SICEFA primero
        $apiConfig      = Cache::store('file')->get('controlecp_fichas_api_config', ['url' => '', 'timeout' => 5]);
        $aprendicesApi  = $this->fetchAprendicesDeApi($course->code, $apiConfig);
        $fuente         = 'bd_local';

        if (!empty($aprendicesApi)) {
            $aprendices = $aprendicesApi;
            $fuente     = 'api_central';
        } else {
            $aprendices = $course->apprentices
                ->filter(fn($a) => $a->person !== null)
                ->map(fn($a) => [
                    'id'              => $a->id,
                    'document_type'   => $a->person->document_type,
                    'document_number' => $a->person->document_number,
                    'full_name'       => trim("{$a->person->first_name} {$a->person->first_last_name} {$a->person->second_last_name}"),
                    'email'           => $a->person->misena_email ?: ($a->person->personal_email ?: ''),
                    'status'          => $a->apprentice_status ?: 'En formación',
                ])->values()->toArray();
        }

        // Registros ya guardados hoy para este instructor/ficha/actividad
        $registrosGuardados = AsistenciaRegistro::where('course_id', $fichaId)
            ->where('actividad_id', $actividadId)
            ->whereDate('fecha', $fecha)
            ->get(['document_number', 'estado', 'observacion', 'evidencia_url'])
            ->keyBy('document_number')
            ->map(fn($r) => [
                'estado'       => $r->estado,
                'observacion'  => $r->observacion,
                'evidencia_url' => $r->evidencia_url,
            ])->toArray();

        return response()->json([
            'success'           => true,
            'ficha_code'        => $course->code,
            'program_name'      => $course->program?->name ?? 'Sin programa',
            'total'             => count($aprendices),
            'fuente'            => $fuente,
            'aprendices'        => $aprendices,
            'registros_guardados' => $registrosGuardados,
        ]);
    }

    /**
     * HU-001 · HU-002 · HU-003
     * Guarda o actualiza la asistencia completa de una sesión.
     * Soporta evidencia opcional (foto/video) y validaciones completas.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('controlecp.instructor')) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'ficha_id'         => 'required|exists:courses,id',
            'actividad_id'     => 'required|integer|min:1|max:18',
            'actividad_nombre' => 'required|string|max:200',
            'momento_id'       => 'required|integer|min:1|max:3',
            'fecha'            => 'required|date',
            'registros'        => 'required|array|min:1',
            'registros.*.apprentice_id'    => 'nullable|integer',
            'registros.*.document_number'  => 'required|string',
            'registros.*.full_name'        => 'required|string',
            'registros.*.estado'           => 'required|in:ASISTIO,FALTO,EXCUSA,RETARDO',
            'registros.*.observacion'      => 'nullable|string|max:500',
        ], [
            'ficha_id.required'      => 'Selecciona una ficha.',
            'actividad_id.required'  => 'Selecciona una actividad.',
            'fecha.required'         => 'La fecha es obligatoria.',
            'registros.min'          => 'Debe haber al menos un aprendiz.',
        ]);

        // HU-003: Validar que no exista ya asistencia completa para ficha+actividad+fecha
        // (se permite guardar si hay cambios, no bloquear)

        $fuente    = $request->input('fuente', 'bd_local');
        $instructor = Auth::user();

        DB::beginTransaction();
        try {
            $guardados   = 0;
            $actualizados = 0;

            foreach ($validated['registros'] as $reg) {
                $existing = AsistenciaRegistro::where([
                    'course_id'       => $validated['ficha_id'],
                    'actividad_id'    => $validated['actividad_id'],
                    'fecha'           => $validated['fecha'],
                    'document_number' => $reg['document_number'],
                ])->first();

                if ($existing && $existing->is_anulado) {
                    continue; // HU-005: no se edita lo anulado
                }

                if ($existing) {
                    // HU-003: registrar en historial antes de editar
                    $existing->registrarCambio('EDICION_RAPIDA', [
                        'estado_anterior' => $existing->estado,
                        'estado_nuevo'    => $reg['estado'],
                    ], $instructor->id);

                    $existing->update([
                        'estado'       => $reg['estado'],
                        'observacion'  => $reg['observacion'] ?? $existing->observacion,
                        'instructor_id' => $instructor->id,
                        'fuente'       => $fuente,
                        'historial_cambios' => $existing->historial_cambios,
                    ]);
                    $actualizados++;
                } else {
                    AsistenciaRegistro::create([
                        'course_id'        => $validated['ficha_id'],
                        'instructor_id'    => $instructor->id,
                        'actividad_id'     => $validated['actividad_id'],
                        'actividad_nombre' => $validated['actividad_nombre'],
                        'momento_id'       => $validated['momento_id'],
                        'apprentice_id'    => $reg['apprentice_id'] ?? null,
                        'document_number'  => $reg['document_number'],
                        'full_name'        => $reg['full_name'],
                        'estado'           => $reg['estado'],
                        'observacion'      => $reg['observacion'] ?? null,
                        'fecha'            => $validated['fecha'],
                        'fuente'           => $fuente,
                        'historial_cambios' => [[
                            'accion'     => 'REGISTRO_INICIAL',
                            'usuario_id' => $instructor->id,
                            'timestamp'  => now()->toIso8601String(),
                        ]],
                    ]);
                    $guardados++;
                }
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => "Asistencia guardada: {$guardados} nuevos, {$actualizados} actualizados.",
                'guardados'    => $guardados,
                'actualizados' => $actualizados,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * HU-002 — Subir evidencia (foto o enlace de video) para un registro.
     */
    public function subirEvidencia(Request $request)
    {
        if (!Auth::user()->hasRole('controlecp.instructor')) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'ficha_id'       => 'required|exists:courses,id',
            'actividad_id'   => 'required|integer',
            'fecha'          => 'required|date',
            'document_number' => 'required|string',
            'evidencia'      => 'required_without:enlace_video',
            'enlace_video'   => 'required_without:evidencia|nullable|url',
        ]);

        $registro = AsistenciaRegistro::where([
            'course_id'       => $request->ficha_id,
            'actividad_id'    => $request->actividad_id,
            'fecha'           => $request->fecha,
            'document_number' => $request->document_number,
        ])->first();

        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado. Guarde la asistencia primero.'], 404);
        }

        if ($request->hasFile('evidencia')) {
            $path = $request->file('evidencia')->store(
                'asistencia/evidencias/' . $request->ficha_id,
                'public'
            );
            $registro->evidencia_url  = Storage::url($path);
            $registro->evidencia_tipo = 'foto';
        } elseif ($request->enlace_video) {
            $registro->evidencia_url  = $request->enlace_video;
            $registro->evidencia_tipo = 'enlace';
        }

        $registro->registrarCambio('EVIDENCIA_SUBIDA', [
            'tipo' => $registro->evidencia_tipo,
            'url'  => $registro->evidencia_url,
        ], Auth::id());

        $registro->save();

        return response()->json([
            'success'       => true,
            'message'       => 'Evidencia registrada correctamente.',
            'evidencia_url'  => $registro->evidencia_url,
            'evidencia_tipo' => $registro->evidencia_tipo,
        ]);
    }

    /**
     * HU-004 — Editar un registro de asistencia con motivo obligatorio.
     */
    public function editar(Request $request, int $id)
    {
        $validated = $request->validate([
            'estado'         => 'required|in:ASISTIO,FALTO,EXCUSA,RETARDO',
            'observacion'    => 'nullable|string|max:500',
            'motivo_edicion' => 'required|string|min:5|max:500',
        ], [
            'motivo_edicion.required' => 'El motivo de edición es obligatorio.',
            'motivo_edicion.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $registro = AsistenciaRegistro::findOrFail($id);

        if ($registro->is_anulado) {
            return response()->json(['success' => false, 'message' => 'No se puede editar un registro anulado.'], 422);
        }

        $registro->registrarCambio('EDICION', [
            'estado_anterior'    => $registro->estado,
            'estado_nuevo'       => $validated['estado'],
            'observacion_anterior' => $registro->observacion,
            'observacion_nueva'  => $validated['observacion'],
            'motivo'             => $validated['motivo_edicion'],
        ], Auth::id());

        $registro->update([
            'estado'         => $validated['estado'],
            'observacion'    => $validated['observacion'],
            'motivo_edicion' => $validated['motivo_edicion'],
            'editado_por'    => Auth::id(),
            'editado_at'     => now(),
            'historial_cambios' => $registro->historial_cambios,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado. El historial conserva el cambio.',
        ]);
    }

    /**
     * HU-005 — Anular un registro de asistencia con motivo obligatorio.
     */
    public function anular(Request $request, int $id)
    {
        $validated = $request->validate([
            'motivo_anulacion' => 'required|string|min:5|max:500',
        ], [
            'motivo_anulacion.required' => 'El motivo de anulación es obligatorio.',
        ]);

        $registro = AsistenciaRegistro::findOrFail($id);

        if ($registro->is_anulado) {
            return response()->json(['success' => false, 'message' => 'Este registro ya está anulado.'], 422);
        }

        $registro->registrarCambio('ANULACION', [
            'estado_anterior'  => $registro->estado,
            'motivo_anulacion' => $validated['motivo_anulacion'],
        ], Auth::id());

        $registro->update([
            'estado'           => 'ANULADA',
            'motivo_anulacion' => $validated['motivo_anulacion'],
            'anulado_por'      => Auth::id(),
            'anulado_at'       => now(),
            'historial_cambios' => $registro->historial_cambios,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registro anulado. El historial conserva el cambio.',
        ]);
    }

    /**
     * HU-008 — Detalle de una actividad: todos los aprendices con su estado.
     */
    public function detalle(int $fichaId, int $actividadId, string $fecha)
    {
        $registros = AsistenciaRegistro::where('course_id', $fichaId)
            ->where('actividad_id', $actividadId)
            ->whereDate('fecha', $fecha)
            ->with(['instructor.person'])
            ->get();

        if ($registros->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay registros para esta sesión.'], 404);
        }

        $resumen = [
            'total'   => $registros->count(),
            'asistio' => $registros->where('estado', 'ASISTIO')->count(),
            'falto'   => $registros->where('estado', 'FALTO')->count(),
            'excusa'  => $registros->where('estado', 'EXCUSA')->count(),
            'retardo' => $registros->where('estado', 'RETARDO')->count(),
            'anulada' => $registros->where('estado', 'ANULADA')->count(),
        ];

        $instructor = $registros->first()?->instructor;

        return response()->json([
            'success'    => true,
            'resumen'    => $resumen,
            'instructor' => $instructor ? [
                'id'        => $instructor->id,
                'nombre'    => $instructor->full_name,
                'documento' => $instructor->person?->document_number,
            ] : null,
            'registros'  => $registros->map(fn($r) => [
                'id'              => $r->id,
                'full_name'       => $r->full_name,
                'document_number' => $r->document_number,
                'estado'          => $r->estado,
                'estado_label'    => $r->estado_label,
                'estado_badge'    => $r->estado_badge,
                'observacion'     => $r->observacion,
                'evidencia_url'   => $r->evidencia_url,
                'evidencia_tipo'  => $r->evidencia_tipo,
            ]),
        ]);
    }

    /**
     * HU-009 — Historial de asistencia de un aprendiz con porcentaje.
     */
    public function historialAprendiz(Request $request)
    {
        $documento = trim($request->input('documento', ''));
        if (empty($documento)) {
            return response()->json(['success' => false, 'message' => 'Ingresa el número de documento.'], 422);
        }

        $registros = AsistenciaRegistro::where('document_number', $documento)
            ->where('estado', '!=', 'ANULADA')
            ->with(['course.program'])
            ->orderByDesc('fecha')
            ->get();

        if ($registros->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron registros para este aprendiz.'], 404);
        }

        $total   = $registros->count();
        $asistio = $registros->where('estado', 'ASISTIO')->count();
        $excusa  = $registros->where('estado', 'EXCUSA')->count();
        $retardo = $registros->where('estado', 'RETARDO')->count();
        $falto   = $registros->where('estado', 'FALTO')->count();

        $porcentaje = $total > 0
            ? round((($asistio + $excusa + $retardo) / $total) * 100, 1)
            : 0;

        return response()->json([
            'success'     => true,
            'full_name'   => $registros->first()->full_name,
            'documento'   => $documento,
            'total'       => $total,
            'asistio'     => $asistio,
            'falto'       => $falto,
            'excusa'      => $excusa,
            'retardo'     => $retardo,
            'porcentaje_asistencia' => $porcentaje,
            'registros'   => $registros->map(fn($r) => [
                'fecha'           => $r->fecha?->format('d/m/Y'),
                'ficha'           => $r->course?->code,
                'actividad'       => $r->actividad_nombre,
                'momento'         => $r->momento_nombre,
                'estado'          => $r->estado,
                'estado_label'    => $r->estado_label,
                'estado_badge'    => $r->estado_badge,
                'observacion'     => $r->observacion,
            ]),
        ]);
    }

    /**
     * HU-010 — Historial de cambios de un registro (auditoría).
     */
    public function historialCambios(int $id)
    {
        $registro = AsistenciaRegistro::with(['instructor.person', 'editor.person', 'anulador.person'])
            ->findOrFail($id);

        return response()->json([
            'success'    => true,
            'registro'   => [
                'id'              => $registro->id,
                'full_name'       => $registro->full_name,
                'document_number' => $registro->document_number,
                'estado_actual'   => $registro->estado_label,
                'fecha'           => $registro->fecha?->format('d/m/Y'),
                'actividad'       => $registro->actividad_nombre,
            ],
            'historial'  => $registro->historial_cambios ?? [],
            'editado_por' => $registro->editor ? $registro->editor->full_name : null,
            'anulado_por' => $registro->anulador ? $registro->anulador->full_name : null,
        ]);
    }

    /**
     * HU-013 — Generar reporte de asistencia (JSON estructurado, exportable a PDF desde la vista).
     */
    public function reporte(Request $request)
    {
        $fichaId     = $request->integer('ficha_id');
        $actividadId = $request->integer('actividad_id');
        $fecha       = $request->input('fecha');
        $instructorId = $request->integer('instructor_id');

        $query = AsistenciaRegistro::with(['course.program', 'instructor.person'])
            ->where('estado', '!=', 'ANULADA');

        if ($fichaId)      $query->where('course_id', $fichaId);
        if ($actividadId)  $query->where('actividad_id', $actividadId);
        if ($fecha)        $query->whereDate('fecha', $fecha);
        if ($instructorId) $query->where('instructor_id', $instructorId);

        $registros = $query->orderBy('fecha')->orderBy('full_name')->get();

        $resumen = [
            'total'             => $registros->count(),
            'asistio'           => $registros->where('estado', 'ASISTIO')->count(),
            'falto'             => $registros->where('estado', 'FALTO')->count(),
            'excusa'            => $registros->where('estado', 'EXCUSA')->count(),
            'retardo'           => $registros->where('estado', 'RETARDO')->count(),
            'porcentaje_asistencia' => $registros->count() > 0
                ? round(($registros->whereIn('estado', ['ASISTIO','EXCUSA','RETARDO'])->count() / $registros->count()) * 100, 1)
                : 0,
            'generado_en'       => now()->format('d/m/Y H:i:s'),
            'generado_por'      => Auth::user()->full_name,
        ];

        return response()->json([
            'success'   => true,
            'resumen'   => $resumen,
            'registros' => $registros->map(fn($r) => [
                'fecha'           => $r->fecha?->format('d/m/Y'),
                'ficha'           => $r->course?->code,
                'programa'        => $r->course?->program?->name,
                'actividad'       => $r->actividad_nombre,
                'momento'         => $r->momento_nombre,
                'document_number' => $r->document_number,
                'full_name'       => $r->full_name,
                'estado'          => $r->estado_label,
                'observacion'     => $r->observacion,
                'instructor'      => $r->instructor?->full_name,
                'evidencia'       => $r->evidencia_url ? 'Sí' : 'No',
            ]),
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    /** HU-012: Calcula los indicadores generales del módulo. */
    private function calcularIndicadores(): array
    {
        $total   = AsistenciaRegistro::where('estado', '!=', 'ANULADA')->count();
        $asistio = AsistenciaRegistro::where('estado', 'ASISTIO')->count();
        $falto   = AsistenciaRegistro::where('estado', 'FALTO')->count();
        $excusa  = AsistenciaRegistro::where('estado', 'EXCUSA')->count();
        $retardo = AsistenciaRegistro::where('estado', 'RETARDO')->count();

        // Actividades con al menos un registro
        $actividadesConRegistro = AsistenciaRegistro::where('estado', '!=', 'ANULADA')
            ->distinct()->pluck('actividad_id')->count();

        // Fichas con al menos un registro
        $fichasConRegistro = AsistenciaRegistro::where('estado', '!=', 'ANULADA')
            ->distinct()->pluck('course_id')->count();

        return [
            'total'                  => $total,
            'asistio'                => $asistio,
            'falto'                  => $falto,
            'excusa'                 => $excusa,
            'retardo'                => $retardo,
            'porcentaje_asistencia'  => $total > 0 ? round((($asistio + $excusa + $retardo) / $total) * 100, 1) : 0,
            'porcentaje_ausencia'    => $total > 0 ? round(($falto / $total) * 100, 1) : 0,
            'actividades_finalizadas' => $actividadesConRegistro,
            'actividades_total'      => 18,
            'actividades_pendientes' => max(0, 18 - $actividadesConRegistro),
            'fichas_con_registro'    => $fichasConRegistro,
            'hoy'                    => AsistenciaRegistro::whereDate('fecha', today())->where('estado', '!=', 'ANULADA')->count(),
        ];
    }

    /** HU-011: Devuelve las actividades ECP que no tienen ningún registro. */
    private function getActividadesSinAsistencia(): array
    {
        $actividades   = $this->getCatalogoActividades();
        $conRegistro   = AsistenciaRegistro::where('estado', '!=', 'ANULADA')
            ->distinct()->pluck('actividad_id')->toArray();

        return array_filter($actividades, fn($a) => !in_array($a['id'], $conRegistro));
    }

    /**
     * Intenta obtener aprendices de la API REST Central de SICEFA.
     * Devuelve [] si falla — el controlador usará BD local como respaldo.
     *
     * CONTRATO DE INTEGRACIÓN (preparado para conectar):
     *   GET {sicefa_api_url}/{ficha_code}/aprendices
     *   Authorization: Bearer {token}
     *   Respuesta esperada: { "aprendices": [ { "id", "document_type",
     *     "document_number", "full_name", "email", "status" } ] }
     */
    private function fetchAprendicesDeApi(string $fichaCode, array $apiConfig): array
    {
        $url = $apiConfig['url'] ?? '';
        if (empty($url)) return [];

        // Mock local → no hacer HTTP, retornar vacío y usar BD
        if (str_contains($url, '127.0.0.1') || str_contains($url, 'localhost') || str_contains($url, 'controlecp/sicefa')) {
            return [];
        }

        try {
            $token   = $apiConfig['token'] ?? '';
            $timeout = (int) ($apiConfig['timeout'] ?? 5);

            $req = \Illuminate\Support\Facades\Http::timeout($timeout)->acceptJson();
            if ($token) $req = $req->withToken($token);

            // Endpoint SICEFA: GET /api/v1/fichas/{code}/aprendices
            $response = $req->get(rtrim($url, '/') . "/{$fichaCode}/aprendices");

            if ($response->successful()) {
                $data = $response->json();
                return $data['aprendices'] ?? $data['data'] ?? [];
            }
        } catch (\Exception) {
            // Sin conexión: usa BD local
        }

        return [];
    }

    /** Catálogo oficial de las 18 actividades pedagógicas ECP. */
    private function getCatalogoActividades(): array
    {
        return [
            ['id'=>1,  'numero'=>'01','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Reconocimiento y Gestión Emocional'],
            ['id'=>2,  'numero'=>'02','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Taller de Autorreflexión y Autoconcepto'],
            ['id'=>3,  'numero'=>'03','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Proyecto de Vida y Sueños Integrales'],
            ['id'=>4,  'numero'=>'04','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Identificación de Fortalezas y Oportunidades'],
            ['id'=>5,  'numero'=>'05','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Expresión Asertiva de Sentimientos'],
            ['id'=>6,  'numero'=>'06','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Mapeo Corporal y Autocuidado'],
            ['id'=>7,  'numero'=>'07','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Control del Estrés e Impulsos'],
            ['id'=>8,  'numero'=>'08','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Historia Personal y Raíces Culturales'],
            ['id'=>9,  'numero'=>'09','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Principios, Valores y Ética Personal'],
            ['id'=>10, 'numero'=>'10','momento_id'=>1,'momento'=>'Momento 1','momento_color'=>'#39A900','nombre'=>'Compromiso Conmigo Mismo'],
            ['id'=>11, 'numero'=>'01','momento_id'=>2,'momento'=>'Momento 2','momento_color'=>'#0070B8','nombre'=>'Círculos de Diálogo y Empatía'],
            ['id'=>12, 'numero'=>'02','momento_id'=>2,'momento'=>'Momento 2','momento_color'=>'#0070B8','nombre'=>'Dinámicas de Trabajo Colaborativo'],
            ['id'=>13, 'numero'=>'03','momento_id'=>2,'momento'=>'Momento 2','momento_color'=>'#0070B8','nombre'=>'Comunicación No Violenta (CNV)'],
            ['id'=>14, 'numero'=>'04','momento_id'=>2,'momento'=>'Momento 2','momento_color'=>'#0070B8','nombre'=>'Acuerdos de Convivencia y Respeto'],
            ['id'=>15, 'numero'=>'01','momento_id'=>3,'momento'=>'Momento 3','momento_color'=>'#6A1B9A','nombre'=>'Taller de Mediación Pacífica'],
            ['id'=>16, 'numero'=>'02','momento_id'=>3,'momento'=>'Momento 3','momento_color'=>'#6A1B9A','nombre'=>'Negociación y Estrategias Ganar-Ganar'],
            ['id'=>17, 'numero'=>'03','momento_id'=>3,'momento'=>'Momento 3','momento_color'=>'#6A1B9A','nombre'=>'Estudio de Casos y Juego de Roles'],
            ['id'=>18, 'numero'=>'04','momento_id'=>3,'momento'=>'Momento 3','momento_color'=>'#6A1B9A','nombre'=>'Plan de Acción y Pactos de Paz'],
        ];
    }
}
