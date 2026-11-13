<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Services\TabulacionService;

class InstructorReporteController extends Controller
{
    private const TIPOS = [
        'asistencias' => 'Asistencias',
        'asesorias' => 'Asesorías',
        'acompanamientos' => 'Acompañamientos',
        'certificacion' => 'Certificación',
        'encuestas' => 'Encuestas',
        'inventario' => 'Inventario usado',
        'pqrs' => 'PQRS atendidas',
    ];

    private const ICONOS = [
        'asistencias' => 'fa-user-check',
        'asesorias' => 'fa-comments',
        'acompanamientos' => 'fa-hand-holding-hand',
        'certificacion' => 'fa-certificate',
        'encuestas' => 'fa-clipboard-question',
        'inventario' => 'fa-boxes-stacked',
        'pqrs' => 'fa-comment-dots',
    ];

    public function index(Request $request, TabulacionService $tabulacionService)
    {
        $this->autorizarInstructor();
        $tipo = $request->input('tipo');
        if ($tipo === null || $tipo === '') {
            return view('controlecp::instructor.reportes', [
                'tipos' => self::TIPOS,
                'iconos' => self::ICONOS,
                'tipo' => null,
            ]);
        }
        $tipo = (string) $tipo;
        abort_unless(array_key_exists($tipo, self::TIPOS), 404);

        $fichas = $this->fichasDisponibles($tipo, (int) Auth::id());
        $filtros = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'ficha_id' => ['nullable', 'integer', Rule::in($fichas->pluck('id')->all())],
        ], [
            'fecha_hasta.after_or_equal' => 'La fecha inicial no puede ser mayor que la fecha final.',
        ]);

        $resultado = $this->generar($tipo, $filtros, (int) Auth::id(), $tabulacionService);
        $filas = $resultado['filas'];
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $filasPaginadas = new LengthAwarePaginator(
            $filas->forPage($pagina, 10)->values(),
            $filas->count(),
            10,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $distribucion = $tipo === 'asesorias'
            ? $filas->groupBy(fn (array $fila) => $fila['estado'] ?: 'Sin estado')->map->count()
            : collect();

        return view('controlecp::instructor.reportes', [
            'tipos' => self::TIPOS,
            'iconos' => self::ICONOS,
            'tipo' => $tipo,
            'resultado' => $resultado,
            'filasPaginadas' => $filasPaginadas,
            'fichas' => $fichas,
            'distribucion' => $distribucion,
        ]);
    }

    public function exportarPdf(Request $request, TabulacionService $tabulacionService)
    {
        $this->autorizarInstructor();
        $filtros = $request->validate([
            'tipo' => ['required', 'string', Rule::in(array_keys(self::TIPOS))],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'ficha_id' => ['nullable', 'integer'],
        ], [
            'fecha_hasta.after_or_equal' => 'La fecha inicial no puede ser mayor que la fecha final.',
        ]);

        $tipo = $filtros['tipo'];
        $fichas = $this->fichasDisponibles($tipo, (int) Auth::id());
        if (! empty($filtros['ficha_id']) && ! $fichas->contains('id', (int) $filtros['ficha_id'])) {
            abort(422, 'La ficha seleccionada no pertenece a tus registros.');
        }

        $resultado = $this->generar($tipo, $filtros, (int) Auth::id(), $tabulacionService);
        if ($resultado['filas']->isEmpty()) {
            return redirect()
                ->route('controlecp.instructor.reportes', array_filter($filtros, fn ($value) => $value !== null && $value !== ''))
                ->with('warning', 'No se encontraron registros para exportar con los filtros seleccionados.');
        }

        $fichaSeleccionada = $fichas->firstWhere('id', $filtros['ficha_id'] ?? null);
        $instructor = Auth::user();
        $rutaLogo = public_path('general/assets/img/logo-ecp.png');
        if (! is_readable($rutaLogo)) {
            throw new \RuntimeException('No se pudo leer el logo de Control ECP para generar el PDF.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('controlecp::instructor.reportes_pdf', [
            'nombreReporte' => self::TIPOS[$tipo],
            'tipo' => $tipo,
            'color' => [
                'asistencias' => '#39A900',
                'asesorias' => '#6A1B9A',
                'acompanamientos' => '#0070B8',
                'certificacion' => '#00838F',
                'encuestas' => '#0070B8',
                'inventario' => '#C0392B',
                'pqrs' => '#E65100',
            ][$tipo],
            'logo' => $rutaLogo,
            'resultado' => $resultado,
            'filtros' => $filtros,
            'ficha' => $fichaSeleccionada,
            'generadoPor' => $instructor->nickname ?: ($instructor->name ?? $instructor->email),
            'generadoEn' => now('America/Bogota')->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('reporte_' . $tipo . '_' . now('America/Bogota')->format('Ymd_His') . '.pdf');
    }

    private function autorizarInstructor(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }

    private function generar(string $tipo, array $filtros, int $instructorId, TabulacionService $tabulacionService): array
    {
        return match ($tipo) {
            'asesorias' => $this->asesorias($filtros, $instructorId),
            'acompanamientos' => $this->acompanamientos($filtros, $instructorId),
            'certificacion' => $this->certificaciones($filtros, $instructorId),
            'encuestas' => $this->encuestas($filtros, $instructorId, $tabulacionService),
            'inventario' => $this->inventario($filtros, $instructorId),
            'pqrs' => $this->pqrs($filtros, $instructorId),
            default => $this->asistencias($filtros, $instructorId),
        };
    }

    private function asistencias(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('controlecp_asistencias') || ! Schema::hasTable('controlecp_asistencia_detalles')) {
            return $this->resultado([], ['Registros' => 0]);
        }

        $detalles = DB::table('controlecp_asistencia_detalles')
            ->select('asistencia_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN presente = 1 THEN 1 ELSE 0 END) as presentes')
            ->selectRaw('SUM(CASE WHEN presente = 0 THEN 1 ELSE 0 END) as ausencias')
            ->groupBy('asistencia_id');

        $query = DB::table('controlecp_asistencias as ca')
            ->join('cronograma as c', 'c.id_cronograma', '=', 'ca.cronograma_id')
            ->leftJoin('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->leftJoin('courses as co', 'co.id', '=', 'c.id_ficha')
            ->leftJoinSub($detalles, 'ad', fn ($join) => $join->on('ad.asistencia_id', '=', 'ca.id'))
            ->where('c.id_instructor', $instructorId)
            ->where('ca.registrado_por', $instructorId)
            ->where('ca.estado', 'Registrada');
        $this->aplicarFecha($query, 'c.fecha', $filtros);
        if (! empty($filtros['ficha_id'])) {
            $query->where('c.id_ficha', $filtros['ficha_id']);
        }

        $filas = $query->select([
            'co.code as ficha',
            'a.nombre as actividad',
            'c.fecha',
            DB::raw('COALESCE(ad.presentes, 0) as presentes'),
            DB::raw('COALESCE(ad.ausencias, 0) as ausencias'),
            DB::raw('ROUND(COALESCE(ad.presentes, 0) * 100.0 / NULLIF(ad.total, 0), 1) as porcentaje'),
        ])->orderByDesc('c.fecha')->get()->map(fn ($fila) => [
            'ficha' => $fila->ficha ?: '—',
            'actividad' => $fila->actividad ?: '—',
            'fecha' => $fila->fecha,
            'presentes' => (int) $fila->presentes,
            'ausencias' => (int) $fila->ausencias,
            'porcentaje' => $fila->porcentaje === null ? 'N/A' : $fila->porcentaje . '%',
        ]);

        $presentes = $filas->sum('presentes');
        $ausencias = $filas->sum('ausencias');

        return $this->resultado($filas, [
            'Registros de asistencia' => $filas->count(),
            'Presentes' => $presentes,
            'Ausencias' => $ausencias,
            'Asistencia promedio' => ($presentes + $ausencias) > 0
                ? round($presentes * 100 / ($presentes + $ausencias), 1) . '%'
                : 'N/A',
        ], [
            'Ficha' => 'ficha',
            'Actividad' => 'actividad',
            'Fecha' => 'fecha',
            'Presentes' => 'presentes',
            'Ausencias' => 'ausencias',
            'Asistencia' => 'porcentaje',
        ]);
    }

    private function asesorias(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('asesorias')) {
            return $this->resultado([], ['Total asesorías' => 0]);
        }

        $query = DB::table('asesorias as asi')
            ->leftJoin('categorias_asesoria as cat', 'cat.id_categoria_asesoria', '=', 'asi.id_categoria_asesoria')
            ->leftJoin('users as aprendiz', 'aprendiz.id', '=', 'asi.id_usuario_aprendiz')
            ->where('asi.id_usuario_instructor', $instructorId)
            ->whereNotExists(function ($roles) {
                $roles->selectRaw('1')
                    ->from('role_user as ru')
                    ->join('roles as r', 'r.id', '=', 'ru.role_id')
                    ->whereColumn('ru.user_id', 'asi.id_usuario_registro')
                    ->where(function ($roleQuery) {
                        $roleQuery->whereIn('r.slug', ['controlecp.admin', 'superadmin'])
                            ->orWhere('r.full_access', 'Si');
                    });
            });
        $this->aplicarFecha($query, 'asi.fecha', $filtros);
        $this->aplicarFichaAprendiz($query, 'aprendiz.person_id', $filtros);
        $fichaCode = DB::table('apprentices as ap')
            ->join('courses as co', 'co.id', '=', 'ap.course_id')
            ->whereColumn('ap.person_id', 'aprendiz.person_id')
            ->when(! empty($filtros['ficha_id']), fn ($query) => $query->where('ap.course_id', $filtros['ficha_id']))
            ->orderBy('co.code')
            ->limit(1)
            ->select('co.code');
        $filas = $query->select([
            'asi.fecha',
            'asi.hora_inicio',
            'asi.hora_fin',
            'asi.estado',
            'cat.nombre as categoria',
        ])->selectSub($fichaCode, 'ficha')
            ->orderByDesc('asi.fecha')
            ->get()
            ->map(fn ($fila) => [
                'fecha' => $fila->fecha,
                'ficha' => $fila->ficha ?: '—',
                'categoria' => $fila->categoria ?: 'Asesoría',
                'estado' => $fila->estado,
                'hora_inicio' => $fila->hora_inicio,
                'hora_fin' => $fila->hora_fin,
            ]);

        return $this->resultado($filas, [
            'Total asesorías' => $filas->count(),
            'Programadas' => $filas->where('estado', 'Programada')->count(),
            'Realizadas' => $filas->where('estado', 'Realizada')->count(),
            'Canceladas' => $filas->where('estado', 'Cancelada')->count(),
        ], [
            'Fecha' => 'fecha',
            'Ficha' => 'ficha',
            'Categoría' => 'categoria',
            'Estado' => 'estado',
            'Hora inicio' => 'hora_inicio',
            'Hora fin' => 'hora_fin',
        ]);
    }

    private function acompanamientos(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('controlecp_acompanamientos') || ! Schema::hasTable('controlecp_acompanamiento_sesiones')) {
            return $this->resultado([], ['Acompañamientos' => 0]);
        }

        $sesiones = DB::table('controlecp_acompanamiento_sesiones')
            ->select('acompanamiento_id')
            ->selectRaw('COUNT(*) as total_sesiones')
            ->selectRaw("SUM(CASE WHEN estado = 'Realizada' THEN 1 ELSE 0 END) as realizadas")
            ->selectRaw("SUM(CASE WHEN estado = 'No asistio' THEN 1 ELSE 0 END) as faltas")
            ->groupBy('acompanamiento_id');
        $query = DB::table('controlecp_acompanamientos as ac')
            ->leftJoinSub($sesiones, 's', fn ($join) => $join->on('s.acompanamiento_id', '=', 'ac.id'))
            ->leftJoin('users as aprendiz', 'aprendiz.id', '=', 'ac.aprendiz_id')
            ->where('ac.instructor_id', $instructorId);
        $this->aplicarFecha($query, 'ac.fecha_inicio', $filtros);
        $this->aplicarFichaAprendiz($query, 'aprendiz.person_id', $filtros);
        $fichaCode = DB::table('apprentices as ap')
            ->join('courses as co', 'co.id', '=', 'ap.course_id')
            ->whereColumn('ap.person_id', 'aprendiz.person_id')
            ->when(! empty($filtros['ficha_id']), fn ($query) => $query->where('ap.course_id', $filtros['ficha_id']))
            ->orderBy('co.code')
            ->limit(1)
            ->select('co.code');
        $filas = $query->select([
            'ac.fecha_inicio',
            'ac.categoria',
            'ac.estado',
            'ac.sesiones_planeadas',
            DB::raw('COALESCE(s.total_sesiones, 0) as total_sesiones'),
            DB::raw('COALESCE(s.realizadas, 0) as realizadas'),
            DB::raw('COALESCE(s.faltas, 0) as faltas'),
        ])->selectSub($fichaCode, 'ficha')
            ->orderByDesc('ac.fecha_inicio')
            ->get()
            ->map(fn ($fila) => [
                'fecha_inicio' => $fila->fecha_inicio,
                'ficha' => $fila->ficha ?: '—',
                'categoria' => $fila->categoria,
                'estado' => $fila->estado,
                'sesiones_planeadas' => (int) $fila->sesiones_planeadas,
                'realizadas' => (int) $fila->realizadas,
                'faltas' => (int) $fila->faltas,
            ]);

        return $this->resultado($filas, [
            'Acompañamientos' => $filas->count(),
            'Sesiones realizadas' => $filas->sum('realizadas'),
            'Faltas' => $filas->sum('faltas'),
            'Activos' => $filas->where('estado', 'Activo')->count(),
        ], [
            'Fecha inicio' => 'fecha_inicio',
            'Ficha' => 'ficha',
            'Categoría' => 'categoria',
            'Estado' => 'estado',
            'Sesiones planeadas' => 'sesiones_planeadas',
            'Realizadas' => 'realizadas',
            'Faltas' => 'faltas',
        ]);
    }

    private function certificaciones(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('controlecp_certificaciones')) {
            return $this->resultado([], ['Certificaciones' => 0]);
        }

        $query = DB::table('controlecp_certificaciones as cert')
            ->join('courses as co', 'co.id', '=', 'cert.course_id')
            ->leftJoin('controlecp_momentos as m', 'm.id', '=', 'cert.momento_id')
            ->where('cert.responsable_id', $instructorId);
        $this->aplicarFecha($query, 'cert.fecha_certificacion', $filtros);
        if (! empty($filtros['ficha_id'])) {
            $query->where('cert.course_id', $filtros['ficha_id']);
        }
        $filas = $query->select([
            'cert.fecha_certificacion',
            'co.code as ficha',
            'm.nombre as momento',
            'cert.estado',
        ])->orderByDesc('cert.fecha_certificacion')->get()->map(fn ($fila) => [
            'fecha' => $fila->fecha_certificacion,
            'ficha' => $fila->ficha ?: '—',
            'momento' => $fila->momento ?: '—',
            'estado' => $fila->estado,
        ]);

        return $this->resultado($filas, [
            'Momentos certificados' => $filas->count(),
            'Fichas certificadas' => $filas->pluck('ficha')->unique()->count(),
        ], [
            'Fecha de certificación' => 'fecha',
            'Ficha' => 'ficha',
            'Momento' => 'momento',
            'Estado' => 'estado',
        ]);
    }

    private function encuestas(array $filtros, int $instructorId, TabulacionService $tabulacionService): array
    {
        if (! Schema::hasTable('encuestas') || ! Schema::hasTable('controlecp_actividades')) {
            return $this->resultado([], ['Encuestas' => 0, 'Respuestas' => 0]);
        }

        $idsEncuesta = DB::table('controlecp_actividades as a')
            ->join('cronograma as c', 'c.id_actividad', '=', 'a.id')
            ->join('encuestas as e', 'e.id_encuesta', '=', 'a.id_encuesta')
            ->where('c.id_instructor', $instructorId)
            ->whereNotNull('a.id_encuesta')
            ->distinct()
            ->pluck('a.id_encuesta');
        $filas = collect();
        $respuestas = 0;
        $satisfacciones = [];
        foreach ($idsEncuesta as $idEncuesta) {
            $resultado = $tabulacionService->calcularResultados((int) $idEncuesta, $filtros, $instructorId);
            $consolidado = $resultado['consolidado'];
            if ($consolidado['total_respuestas'] === 0) {
                continue;
            }
            $respuestas += $consolidado['total_respuestas'];
            if ($consolidado['satisfaccion_porcentaje'] !== null) {
                $satisfacciones[] = $consolidado['satisfaccion_porcentaje'] / 20;
            }
            $filas->push([
                'encuesta' => $resultado['encuesta']->nombre,
                'respuestas' => $consolidado['total_respuestas'],
                'participacion' => $consolidado['tasa_participacion'],
                'satisfaccion' => $consolidado['satisfaccion_promedio'],
                'preguntas_evaluadas' => $consolidado['preguntas_evaluadas'],
            ]);
        }

        return $this->resultado($filas, [
            'Encuestas con respuestas' => $filas->count(),
            'Respuestas recibidas' => $respuestas,
            'Satisfacción promedio' => $satisfacciones
                ? number_format(array_sum($satisfacciones) / count($satisfacciones), 1) . ' / 5'
                : 'N/A',
        ], [
            'Encuesta' => 'encuesta',
            'Respuestas' => 'respuestas',
            'Participación' => 'participacion',
            'Satisfacción promedio' => 'satisfaccion',
            'Preguntas evaluadas' => 'preguntas_evaluadas',
        ]);
    }

    private function inventario(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('movimientos_inventario')) {
            return $this->resultado([], ['Movimientos' => 0, 'Unidades movidas' => 0]);
        }

        $query = DB::table('movimientos_inventario as mi')
            ->join('inventario as i', 'i.id_inventario', '=', 'mi.id_inventario')
            ->leftJoin('cronograma as c', 'c.id_cronograma', '=', 'mi.id_cronograma')
            ->leftJoin('courses as co', 'co.id', '=', 'c.id_ficha')
            ->leftJoin('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->where('mi.id_usuario', $instructorId);
        $this->aplicarFecha($query, 'mi.fecha', $filtros);
        if (! empty($filtros['ficha_id'])) {
            $query->where('c.id_ficha', $filtros['ficha_id']);
        }
        $filas = $query->select([
            'mi.fecha',
            'co.code as ficha',
            'i.nombre as recurso',
            'mi.tipo',
            'mi.cantidad',
            'a.nombre as actividad',
        ])->orderByDesc('mi.fecha')->get()->map(fn ($fila) => [
            'fecha' => $fila->fecha,
            'ficha' => $fila->ficha ?: '—',
            'recurso' => $fila->recurso,
            'movimiento' => $fila->tipo,
            'cantidad' => (int) $fila->cantidad,
            'actividad' => $fila->actividad ?: '—',
        ]);

        return $this->resultado($filas, [
            'Movimientos registrados' => $filas->count(),
            'Unidades movidas' => $filas->sum('cantidad'),
            'Entregas' => $filas->whereIn('movimiento', ['Entrega', 'Salida'])->count(),
        ], [
            'Fecha' => 'fecha',
            'Ficha' => 'ficha',
            'Recurso' => 'recurso',
            'Movimiento' => 'movimiento',
            'Cantidad' => 'cantidad',
            'Actividad' => 'actividad',
        ]);
    }

    private function pqrs(array $filtros, int $instructorId): array
    {
        if (! Schema::hasTable('controlecp_pqrs')) {
            return $this->resultado([], ['PQRS atendidas' => 0, 'Cambios de estado' => 0, 'Respuestas' => 0]);
        }

        $base = DB::table('controlecp_pqrs as p')
            ->where('p.id_usuario_instructor_destino', $instructorId);
        if (Schema::hasColumn('controlecp_pqrs', 'deleted_at')) {
            $base->whereNull('p.deleted_at');
        }
        $this->aplicarFichaAprendiz($base, 'p.id_usuario_aprendiz', $filtros, true);

        $eventos = collect();
        if (Schema::hasTable('controlecp_pqrs_historial')) {
            $historial = clone $base;
            $historial->join('controlecp_pqrs_historial as h', 'h.id_pqrs', '=', 'p.id')
                ->where('h.id_usuario', $instructorId);
            $this->aplicarFecha($historial, 'h.fecha', $filtros);
            $eventos = $eventos->merge($historial->select([
                'h.fecha',
                'p.radicado',
                'p.asunto',
                DB::raw("'Cambio de estado' as evento"),
                DB::raw("CONCAT(COALESCE(h.estado_anterior, 'Nuevo'), ' → ', h.estado_nuevo) as detalle"),
            ])->get()->map(fn ($fila) => [
                'fecha' => $fila->fecha,
                'radicado' => $fila->radicado,
                'asunto' => $fila->asunto,
                'evento' => $fila->evento,
                'detalle' => $fila->detalle,
            ]));
        }
        if (Schema::hasTable('controlecp_pqrs_respuestas')) {
            $respuestas = clone $base;
            $respuestas->join('controlecp_pqrs_respuestas as r', 'r.id_pqrs', '=', 'p.id')
                ->where('r.id_usuario', $instructorId);
            $this->aplicarFecha($respuestas, 'r.fecha', $filtros);
            $eventos = $eventos->merge($respuestas->select([
                'r.fecha',
                'p.radicado',
                'p.asunto',
                DB::raw("'Respuesta enviada' as evento"),
                'r.respuesta as detalle',
            ])->get()->map(fn ($fila) => [
                'fecha' => $fila->fecha,
                'radicado' => $fila->radicado,
                'asunto' => $fila->asunto,
                'evento' => $fila->evento,
                'detalle' => $fila->detalle,
            ]));
        }
        $eventos = $eventos->sortByDesc('fecha')->values();

        return $this->resultado($eventos, [
            'PQRS atendidas' => $eventos->pluck('radicado')->unique()->count(),
            'Cambios de estado' => $eventos->where('evento', 'Cambio de estado')->count(),
            'Respuestas enviadas' => $eventos->where('evento', 'Respuesta enviada')->count(),
        ], [
            'Fecha' => 'fecha',
            'Radicado' => 'radicado',
            'Asunto' => 'asunto',
            'Gestión' => 'evento',
            'Detalle' => 'detalle',
        ]);
    }

    private function aplicarFecha($query, string $columna, array $filtros): void
    {
        if (! empty($filtros['fecha_desde'])) {
            $query->whereDate($columna, '>=', $filtros['fecha_desde']);
        }
        if (! empty($filtros['fecha_hasta'])) {
            $query->whereDate($columna, '<=', $filtros['fecha_hasta']);
        }
    }

    private function aplicarFichaAprendiz($query, string $columnaUsuario, array $filtros, bool $userId = false): void
    {
        if (empty($filtros['ficha_id'])) {
            return;
        }
        $query->whereExists(function ($subquery) use ($columnaUsuario, $filtros, $userId) {
            $subquery->selectRaw('1')
                ->from('users as u')
                ->join('apprentices as ap', 'ap.person_id', '=', 'u.person_id')
                ->whereColumn($userId ? 'u.id' : 'u.person_id', $columnaUsuario)
                ->where('ap.course_id', $filtros['ficha_id']);
        });
    }

    private function fichasDisponibles(string $tipo, int $instructorId): Collection
    {
        $ids = match ($tipo) {
            'asistencias' => Schema::hasTable('controlecp_asistencias')
                ? DB::table('controlecp_asistencias as ca')->join('cronograma as c', 'c.id_cronograma', '=', 'ca.cronograma_id')
                    ->where('c.id_instructor', $instructorId)->where('ca.registrado_por', $instructorId)->distinct()->pluck('c.id_ficha')
                : collect(),
            'certificacion' => Schema::hasTable('controlecp_certificaciones')
                ? DB::table('controlecp_certificaciones')->where('responsable_id', $instructorId)->distinct()->pluck('course_id')
                : collect(),
            'encuestas' => Schema::hasTable('controlecp_actividades')
                ? DB::table('cronograma as c')->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
                    ->where('c.id_instructor', $instructorId)->whereNotNull('a.id_encuesta')
                    ->distinct()->pluck('c.id_ficha')
                : collect(),
            'inventario' => Schema::hasTable('movimientos_inventario')
                ? DB::table('movimientos_inventario as mi')->join('cronograma as c', 'c.id_cronograma', '=', 'mi.id_cronograma')
                    ->where('mi.id_usuario', $instructorId)->distinct()->pluck('c.id_ficha')
                : collect(),
            'asesorias' => Schema::hasTable('asesorias')
                ? DB::table('asesorias as a')->join('users as u', 'u.id', '=', 'a.id_usuario_aprendiz')
                    ->join('apprentices as ap', 'ap.person_id', '=', 'u.person_id')
                    ->where('a.id_usuario_instructor', $instructorId)
                    ->whereNotExists(function ($roles) {
                        $roles->selectRaw('1')
                            ->from('role_user as ru')
                            ->join('roles as r', 'r.id', '=', 'ru.role_id')
                            ->whereColumn('ru.user_id', 'a.id_usuario_registro')
                            ->where(function ($roleQuery) {
                                $roleQuery->whereIn('r.slug', ['controlecp.admin', 'superadmin'])
                                    ->orWhere('r.full_access', 'Si');
                            });
                    })->distinct()->pluck('ap.course_id')
                : collect(),
            'acompanamientos' => Schema::hasTable('controlecp_acompanamientos')
                ? DB::table('controlecp_acompanamientos as ac')->join('users as u', 'u.id', '=', 'ac.aprendiz_id')
                    ->join('apprentices as ap', 'ap.person_id', '=', 'u.person_id')
                    ->where('ac.instructor_id', $instructorId)->distinct()->pluck('ap.course_id')
                : collect(),
            'pqrs' => Schema::hasTable('controlecp_pqrs')
                ? DB::table('controlecp_pqrs as p')->join('users as u', 'u.id', '=', 'p.id_usuario_aprendiz')
                    ->join('apprentices as ap', 'ap.person_id', '=', 'u.person_id')
                    ->where('p.id_usuario_instructor_destino', $instructorId)->distinct()->pluck('ap.course_id')
                : collect(),
            default => collect(),
        };

        if ($ids->isEmpty() || ! Schema::hasTable('courses')) {
            return collect();
        }

        return DB::table('courses')->whereIn('id', $ids)->orderBy('code')->get(['id', 'code']);
    }

    private function resultado($filas, array $indicadores, array $columnas = []): array
    {
        return [
            'filas' => collect($filas),
            'indicadores' => $indicadores,
            'columnas' => $columnas,
        ];
    }
}
