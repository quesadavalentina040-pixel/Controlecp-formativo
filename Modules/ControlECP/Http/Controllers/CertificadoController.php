<?php

namespace Modules\ControlECP\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Apprentice;
use Modules\ControlECP\Entities\Certificado;
use Modules\ControlECP\Entities\AsistenciaRegistro;

/**
 * CertificadoController — Control ECP
 *
 * Implementa los 13 casos de uso del módulo de certificados:
 * HU-001 Mostrar listado          HU-008 Ver detalle
 * HU-002 Filtrar y buscar         HU-009 Momentos certificados por ficha
 * HU-003 Ver progreso por momento HU-010 Registro sin emisión oficial
 * HU-004 Identificar pendientes   HU-011 Notificar
 * HU-005 Certificar ficha/momento HU-012 Ver historial
 * HU-006 Validar 100% momento     HU-013 Generar reporte
 * HU-007 Evitar duplicados
 *
 * Preparado para integración con API REST Central ERP SENA Empresa / SICEFA.
 */
class CertificadoController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // PANEL ADMINISTRADOR
    // ════════════════════════════════════════════════════════════════════════

    /**
     * HU-001 · HU-002 · HU-004 · HU-009 · HU-012
     * Vista principal: listado, filtros, indicadores e identificación de pendientes.
     */
    public function adminIndex(Request $request)
    {
        if (!Auth::user()->hasRole('controlecp.admin')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'Sin permisos para acceder a Certificados.');
        }

        // ── HU-002: Filtros ──────────────────────────────────────────────
        $filtros = $request->only(['ficha', 'momento_id', 'responsable_id', 'fecha_desde', 'fecha_hasta', 'estado']);

        $query = Certificado::with(['course.program', 'responsable.person'])
            ->orderByDesc('fecha_certificacion')
            ->orderByDesc('id');

        if (!empty($filtros['ficha'])) {
            $query->whereHas('course', fn($q) => $q->where('code', 'like', '%'.$filtros['ficha'].'%'));
        }
        if (!empty($filtros['momento_id'])) {
            $query->where('momento_id', $filtros['momento_id']);
        }
        if (!empty($filtros['responsable_id'])) {
            $query->where('certificado_por', $filtros['responsable_id']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_certificacion', '>=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_certificacion', '<=', $filtros['fecha_hasta']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        $certificados = $query->paginate(10)->withQueryString();

        // ── HU-009: Momentos certificados por ficha ──────────────────────
        $avancePorFichaData = $this->calcularAvancePorFicha(10, $request->integer('fichas_page', 1));
        $avancePorFicha     = $avancePorFichaData['items'];
        $avancePaginator    = $avancePorFichaData['paginator'];

        // ── HU-004: Fichas pendientes de certificar ──────────────────────
        $fichasPendientes = $this->getFichasPendientes();

        // ── Indicadores generales ────────────────────────────────────────
        $indicadores = $this->calcularIndicadores();

        // Responsables para el filtro
        $responsables = \App\Models\User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.admin'))
            ->with('person')->get();

        return view('controlecp::administrador.certificados', compact(
            'certificados', 'filtros', 'avancePorFicha', 'avancePaginator',
            'fichasPendientes', 'indicadores', 'responsables'
        ));
    }

    /**
     * HU-005 · HU-006 · HU-007 · HU-010 · HU-011
     * Certifica una ficha en un momento específico.
     */
    public function certificar(Request $request)
    {
        if (!Auth::user()->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'momento_id'  => 'required|integer|in:1,2,3',
            'observacion' => 'nullable|string|max:500',
        ], [
            'course_id.required'  => 'Selecciona una ficha.',
            'momento_id.required' => 'Selecciona el momento.',
            'momento_id.in'       => 'El momento debe ser 1, 2 o 3.',
        ]);

        // HU-007: Evitar duplicados
        $existente = Certificado::where('course_id', $validated['course_id'])
            ->where('momento_id', $validated['momento_id'])
            ->where('estado', 'CERTIFICADA')
            ->first();

        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Esta ficha ya tiene el '
                    .Certificado::MOMENTO_NOMBRES[$validated['momento_id']]
                    .' certificado (ID '.$existente->id.'). No se puede duplicar.',
                'duplicado' => true,
            ], 422);
        }

        // HU-006: Validar 100% del momento
        $validacion = Certificado::puedeSerCertificada($validated['course_id'], $validated['momento_id']);

        if (!$validacion['puede']) {
            $faltantesNombres = $this->nombresActividades($validacion['faltantes_ids']);
            return response()->json([
                'success'    => false,
                'message'    => 'No se puede certificar: el momento tiene solo '
                    .$validacion['porcentaje'].'% de actividades con registro. '
                    .'Faltan: '.implode(', ', $faltantesNombres).'.',
                'validacion' => $validacion,
            ], 422);
        }

        // Obtener snapshot de actividades completadas
        $actividadesCompletadas = AsistenciaRegistro::where('course_id', $validated['course_id'])
            ->where('momento_id', $validated['momento_id'])
            ->where('estado', '!=', 'ANULADA')
            ->distinct()
            ->pluck('actividad_id')
            ->toArray();

        // Porcentaje de asistencia de la ficha
        $totalRegistros = AsistenciaRegistro::where('course_id', $validated['course_id'])
            ->where('momento_id', $validated['momento_id'])
            ->where('estado', '!=', 'ANULADA')
            ->count();

        $totalAsistio = AsistenciaRegistro::where('course_id', $validated['course_id'])
            ->where('momento_id', $validated['momento_id'])
            ->where('estado', 'ASISTIO')
            ->count();

        $porcentaje = $totalRegistros > 0 ? round(($totalAsistio / $totalRegistros) * 100, 2) : 0;
        $totalAprendices = Apprentice::where('course_id', $validated['course_id'])->count();

        DB::beginTransaction();
        try {
            // HU-010: Registro sin emisión oficial (emitido_sofia = false)
            $cert = Certificado::create([
                'course_id'              => $validated['course_id'],
                'momento_id'             => $validated['momento_id'],
                'certificado_por'        => Auth::id(),
                'estado'                 => 'CERTIFICADA',
                'fecha_certificacion'    => today(),
                'observacion'            => $validated['observacion'],
                'actividades_completadas' => $actividadesCompletadas,
                'porcentaje_asistencia'  => $porcentaje,
                'total_aprendices'       => $totalAprendices,
                'emitido_sofia'          => false, // HU-010: no conecta con SOFIA Plus
                'sicefa_token_verificacion' => Certificado::generarTokenVerificacion(
                    $validated['course_id'], $validated['momento_id']
                ),
                'historial_cambios' => [[
                    'accion'     => 'CERTIFICACION_INICIAL',
                    'usuario_id' => Auth::id(),
                    'datos'      => [
                        'porcentaje' => $porcentaje,
                        'actividades_completadas' => count($actividadesCompletadas),
                    ],
                    'timestamp'  => now()->toIso8601String(),
                ]],
            ]);

            // HU-011: Marcar notificación pendiente
            $cert->update(['notificacion_generada' => true, 'notificacion_at' => now()]);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => Certificado::MOMENTO_NOMBRES[$validated['momento_id']]
                    .' certificado exitosamente. ID: '.$cert->id
                    .'  [HU-010: Registro sin emisión oficial — SOFIA Plus no conectado]',
                'cert_id'  => $cert->id,
                'validacion' => $validacion,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    /**
     * HU-008 — Ver detalle completo de un certificado.
     */
    public function detalle(int $id)
    {
        $cert = Certificado::with(['course.program', 'responsable.person', 'anulador.person'])
            ->findOrFail($id);

        $progreso = Certificado::progresoPorMomento($cert->course_id);

        return response()->json([
            'success'    => true,
            'id'         => $cert->id,
            'ficha'      => $cert->course?->code,
            'programa'   => $cert->course?->program?->name,
            'momento'    => $cert->momento_nombre,
            'momento_color' => $cert->momento_color,
            'estado'     => $cert->estado,
            'estado_badge' => $cert->estado_badge,
            'fecha'      => $cert->fecha_certificacion?->format('d/m/Y'),
            'responsable' => $cert->responsable?->full_name,
            'doc_responsable' => $cert->responsable?->person?->document_number,
            'porcentaje_asistencia' => $cert->porcentaje_asistencia,
            'total_aprendices' => $cert->total_aprendices,
            'actividades_completadas' => count($cert->actividades_completadas ?? []),
            'observacion' => $cert->observacion,
            'emitido_sofia' => $cert->emitido_sofia,
            'notificacion_generada' => $cert->notificacion_generada,
            'token_verificacion' => $cert->sicefa_token_verificacion,
            'is_synced'  => $cert->is_synced,
            'progreso_ficha' => $progreso,
        ]);
    }

    /**
     * HU-012 — Historial de cambios de un certificado.
     */
    public function historial(int $id)
    {
        $cert = Certificado::with(['responsable.person', 'anulador.person'])
            ->findOrFail($id);

        return response()->json([
            'success'    => true,
            'ficha'      => $cert->course?->code,
            'momento'    => $cert->momento_nombre,
            'estado'     => $cert->estado,
            'historial'  => $cert->historial_cambios ?? [],
            'responsable' => $cert->responsable?->full_name,
            'anulado_por' => $cert->anulador?->full_name,
        ]);
    }

    /**
     * HU-003 — Progreso por momento de una ficha específica (AJAX).
     */
    public function progresoPorFicha(int $fichaId)
    {
        $course = Course::with('program')->find($fichaId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Ficha no encontrada.'], 404);
        }

        $progreso = Certificado::progresoPorMomento($fichaId);

        return response()->json([
            'success'  => true,
            'ficha'    => $course->code,
            'programa' => $course->program?->name,
            'progreso' => $progreso,
        ]);
    }

    /**
     * HU-013 — Generar reporte de certificaciones.
     */
    public function reporte(Request $request)
    {
        $query = Certificado::with(['course.program', 'responsable.person'])
            ->orderBy('momento_id')
            ->orderBy('fecha_certificacion');

        if ($request->filled('ficha')) {
            $query->whereHas('course', fn($q) => $q->where('code', 'like', '%'.$request->ficha.'%'));
        }
        if ($request->filled('momento_id')) {
            $query->where('momento_id', $request->momento_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $certificados = $query->get();

        // Fichas con los 3 momentos certificados = completamente certificadas
        $fichasCompletas = $certificados->where('estado', 'CERTIFICADA')
            ->groupBy('course_id')
            ->filter(fn($g) => $g->pluck('momento_id')->sort()->values()->toArray() === [1,2,3])
            ->count();

        $fichasConUno   = $certificados->where('estado', 'CERTIFICADA')->groupBy('course_id')
            ->filter(fn($g) => $g->count() === 1)->count();
        $fichasConDos   = $certificados->where('estado', 'CERTIFICADA')->groupBy('course_id')
            ->filter(fn($g) => $g->count() === 2)->count();

        return response()->json([
            'success' => true,
            'resumen' => [
                'total_certificaciones' => $certificados->count(),
                'certificadas'          => $certificados->where('estado', 'CERTIFICADA')->count(),
                'anuladas'              => $certificados->where('estado', 'ANULADA')->count(),
                'fichas_completas'      => $fichasCompletas,
                'fichas_con_1_momento'  => $fichasConUno,
                'fichas_con_2_momentos' => $fichasConDos,
                'generado_en'           => now()->format('d/m/Y H:i'),
                'generado_por'          => Auth::user()->full_name,
            ],
            'registros' => $certificados->map(fn($c) => [
                'id'                    => $c->id,
                'ficha'                 => $c->course?->code,
                'programa'              => $c->course?->program?->name,
                'momento'               => $c->momento_nombre,
                'estado'                => $c->estado,
                'fecha'                 => $c->fecha_certificacion?->format('d/m/Y'),
                'responsable'           => $c->responsable?->full_name,
                'porcentaje_asistencia' => $c->porcentaje_asistencia.'%',
                'total_aprendices'      => $c->total_aprendices,
                'notificacion'          => $c->notificacion_generada ? 'Sí' : 'No',
                'sofia_plus'            => $c->emitido_sofia ? 'Emitido' : 'No emitido',
            ]),
        ]);
    }

    /**
     * Panel de certificados del Instructor (vista de solo lectura del progreso).
     */
    public function instructorIndex()
    {
        if (!Auth::user()->hasRole('controlecp.instructor')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'Sin permisos para acceder a Certificados.');
        }

        // Fichas activas con progreso de certificación
        $fichas = Course::with(['program'])
            ->withCount('apprentices')
            ->where(fn($q) => $q->where('status', 'Activo')->orWhereNull('status'))
            ->orderByDesc('code')
            ->get()
            ->map(function ($ficha) {
                $ficha->progreso = Certificado::progresoPorMomento($ficha->id);
                $momentosCertificados = collect($ficha->progreso)->where('certificado', true)->count();
                $ficha->momentos_certificados = $momentosCertificados;
                return $ficha;
            });

        $indicadores = $this->calcularIndicadores();

        return view('controlecp::instructor.certificados', compact('fichas', 'indicadores'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    /** HU-009: Calcula avance de certificación para cada ficha activa. */
    private function calcularAvancePorFicha(int $perPage = 10, int $page = 1): array
    {
        $fichas = Course::with(['program'])
            ->withCount('apprentices')
            ->where(fn($q) => $q->where('status', 'Activo')->orWhereNull('status'))
            ->orderByDesc('code')
            ->paginate($perPage, ['*'], 'fichas_page', $page);

        $items = collect($fichas->items())->map(function ($ficha) {
            $certs = Certificado::where('course_id', $ficha->id)
                ->where('estado', 'CERTIFICADA')
                ->pluck('momento_id')->toArray();

            $progreso = [];
            foreach ([1, 2, 3] as $m) {
                $analisis = Certificado::puedeSerCertificada($ficha->id, $m);
                $progreso[$m] = [
                    'certificado' => in_array($m, $certs),
                    'puede'       => $analisis['puede'],
                    'porcentaje'  => $analisis['porcentaje'],
                    'completadas' => $analisis['completadas'],
                    'total'       => $analisis['total'],
                ];
            }

            return [
                'id'               => $ficha->id,
                'code'             => $ficha->code,
                'programa'         => $ficha->program?->name ?? 'Sin programa',
                'total_aprendices' => $ficha->apprentices_count,
                'progreso'         => $progreso,
                'certs_count'      => count($certs),
                'completa'         => count($certs) === 3,
            ];
        });

        return [
            'items'       => $items,
            'paginator'   => $fichas,
        ];
    }

    /** HU-004: Fichas que completaron un momento pero aún no están certificadas. */
    private function getFichasPendientes(): \Illuminate\Support\Collection
    {
        $fichas = Course::with('program')
            ->where(fn($q) => $q->where('status', 'Activo')->orWhereNull('status'))
            ->get();

        $pendientes = collect();
        foreach ($fichas as $ficha) {
            foreach ([1, 2, 3] as $m) {
                $yaCertificado = Certificado::where('course_id', $ficha->id)
                    ->where('momento_id', $m)
                    ->where('estado', 'CERTIFICADA')
                    ->exists();

                if ($yaCertificado) continue;

                $analisis = Certificado::puedeSerCertificada($ficha->id, $m);
                if ($analisis['puede']) {
                    $pendientes->push([
                        'ficha_id'  => $ficha->id,
                        'ficha'     => $ficha->code,
                        'programa'  => $ficha->program?->name ?? 'Sin programa',
                        'momento_id' => $m,
                        'momento'   => Certificado::MOMENTO_NOMBRES[$m],
                        'color'     => Certificado::MOMENTO_COLORES[$m],
                    ]);
                }
            }
        }
        return $pendientes;
    }

    /** Calcula los indicadores generales del módulo de certificados. */
    private function calcularIndicadores(): array
    {
        $total      = Certificado::count();
        $certCount  = Certificado::where('estado', 'CERTIFICADA')->count();
        $anuladas   = Certificado::where('estado', 'ANULADA')->count();
        $sinSofia   = Certificado::where('emitido_sofia', false)->where('estado', 'CERTIFICADA')->count();

        $fichasCompletas = Certificado::where('estado', 'CERTIFICADA')
            ->selectRaw('course_id, COUNT(DISTINCT momento_id) as total_momentos')
            ->groupBy('course_id')
            ->having('total_momentos', '=', 3)
            ->count();

        $m1 = Certificado::where('momento_id', 1)->where('estado', 'CERTIFICADA')->count();
        $m2 = Certificado::where('momento_id', 2)->where('estado', 'CERTIFICADA')->count();
        $m3 = Certificado::where('momento_id', 3)->where('estado', 'CERTIFICADA')->count();

        return compact('total', 'certCount', 'anuladas', 'sinSofia', 'fichasCompletas', 'm1', 'm2', 'm3');
    }

    /** Nombres de actividades para mensajes de error de validación. */
    private function nombresActividades(array $ids): array
    {
        $catalogo = [
            1=>'Reconocimiento y Gestión Emocional', 2=>'Taller de Autorreflexión',
            3=>'Proyecto de Vida', 4=>'Fortalezas y Oportunidades',
            5=>'Expresión Asertiva', 6=>'Mapeo Corporal', 7=>'Control del Estrés',
            8=>'Historia Personal', 9=>'Principios y Valores', 10=>'Compromiso Conmigo',
            11=>'Círculos de Diálogo', 12=>'Trabajo Colaborativo',
            13=>'Comunicación No Violenta', 14=>'Acuerdos de Convivencia',
            15=>'Mediación Pacífica', 16=>'Negociación Ganar-Ganar',
            17=>'Estudio de Casos', 18=>'Plan de Acción y Pactos de Paz',
        ];
        return array_map(fn($id) => $catalogo[$id] ?? "Actividad $id", $ids);
    }
}
