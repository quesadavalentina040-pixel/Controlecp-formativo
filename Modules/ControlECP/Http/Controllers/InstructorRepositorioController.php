<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\Actividad;
use Modules\ControlECP\Entities\AcompanamientoEvidencia;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\EvidenciaEcp;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Services\TabulacionService;

class InstructorRepositorioController extends Controller
{
    private function autorizarInstructor(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }

    private function encuestasVisiblesParaInstructor()
    {
        return Encuesta::where(function ($query) {
            $query->where('estado', '!=', 'Inactiva')
                ->orWhereDoesntHave('usuario.roles', function ($roles) {
                    $roles->where(function ($roleQuery) {
                        $roleQuery->whereIn('slug', ['controlecp.admin', 'superadmin'])
                            ->orWhere('full_access', 'Si');
                    });
                });
        });
    }

    public function evidencias(Request $request)
    {
        $this->autorizarInstructor();
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'categoria' => ['nullable', Rule::in(['Actividades', 'Asistencias', 'Acompañamientos', 'Excusas', 'Inventario'])],
            'ficha_id' => ['nullable', 'integer'],
            'producto_id' => ['nullable', 'integer'],
            'movimiento_id' => ['nullable', 'integer'],
        ]);

        $evidencias = EvidenciaEcp::with(['actividad.momento', 'ficha.program', 'registradoPor'])
            ->where('user_id', Auth::id())
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('cronograma as c')
                    ->whereColumn('c.id_actividad', 'controlecp_evidencias.actividad_id')
                    ->whereColumn('c.id_ficha', 'controlecp_evidencias.course_id')
                    ->where('c.id_instructor', Auth::id());
            })
            ->where(function ($query) {
                $query->where(function ($fotoQuery) {
                    $fotoQuery->where('tipo', 'foto')->whereNotNull('archivo');
                })->orWhere(function ($videoQuery) {
                    $videoQuery->where('tipo', 'video')->whereNotNull('enlace');
                });
            })
            ->addSelect([
                'tiene_asistencia_registrada' => DB::table('cronograma as c')
                    ->join('controlecp_asistencias as a', 'a.cronograma_id', '=', 'c.id_cronograma')
                    ->selectRaw('1')
                    ->whereColumn('c.id_actividad', 'controlecp_evidencias.actividad_id')
                    ->whereColumn('c.id_ficha', 'controlecp_evidencias.course_id')
                    ->where('a.estado', 'Registrada')
                    ->limit(1),
            ])
            ->orderByDesc('fecha_registro')
            ->get()
            ->map(function (EvidenciaEcp $evidencia) {
                $rutaActividad = 'controlecp/evidencias/' . $evidencia->actividad_id . '/';
                $esActividad = $evidencia->origen
                    ? $evidencia->origen === 'actividad'
                    : ($evidencia->tipo === 'foto'
                        ? str_starts_with(ltrim($evidencia->archivo ?? '', '/'), $rutaActividad)
                        : !$evidencia->tiene_asistencia_registrada);
                $esVideo = $evidencia->tipo === 'video';

                return [
                    'categoria' => $esActividad ? 'Actividades' : 'Asistencias',
                    'ficha_id' => $evidencia->ficha?->id,
                    'ficha' => $evidencia->ficha?->code,
                    'titulo' => $evidencia->actividad?->nombre ?: ($esActividad ? 'Evidencia de actividad' : 'Evidencia de asistencia'),
                    'detalle' => trim(collect([
                        $evidencia->ficha?->code ? 'Ficha ' . $evidencia->ficha->code : null,
                        $evidencia->ficha?->program?->name,
                        $evidencia->actividad?->momento?->nombre,
                        $evidencia->descripcion,
                    ])->filter()->implode(' · ')),
                    'tipo' => $esVideo ? 'Video' : 'Imagen',
                    'url' => $esVideo ? $evidencia->enlace : $evidencia->url,
                    'url_video_embed' => $esVideo ? $this->videoEmbedUrl($evidencia->enlace) : null,
                    'es_video' => $esVideo,
                    'usuario' => $evidencia->registradoPor?->nickname ?: $evidencia->registradoPor?->full_name,
                    'fecha' => $evidencia->fecha_registro,
                ];
            });

        AcompanamientoEvidencia::with([
            'sesion.acompanamiento.aprendiz.person',
            'ficha.program',
            'registradoPor',
        ])
            ->where('user_id', Auth::id())
            ->whereHas('sesion.acompanamiento', fn ($query) => $query->where('instructor_id', Auth::id()))
            ->where(function ($query) {
                $query->where(fn ($fotoQuery) => $fotoQuery->where('tipo', 'foto')->whereNotNull('archivo'))
                    ->orWhere(fn ($videoQuery) => $videoQuery->where('tipo', 'video')->whereNotNull('enlace'));
            })
            ->orderByDesc('fecha_registro')
            ->get()
            ->each(function (AcompanamientoEvidencia $evidencia) use ($evidencias) {
                $esVideo = $evidencia->tipo === 'video';
                $session = $evidencia->sesion;
                $acompanamiento = $session?->acompanamiento;

                $evidencias->push([
                    'categoria' => 'Acompañamientos',
                    'ficha_id' => $evidencia->ficha?->id,
                    'ficha' => $evidencia->ficha?->code,
                    'titulo' => 'Evidencia de sesión #' . ($session?->numero ?? '—')
                        . ' · ' . ($acompanamiento?->aprendiz?->person?->full_name ?? $acompanamiento?->aprendiz?->nickname ?? 'Aprendiz'),
                    'detalle' => trim(collect([
                        $evidencia->ficha?->code ? 'Ficha ' . $evidencia->ficha->code : null,
                        $evidencia->ficha?->program?->name,
                        $session?->fecha?->format('d/m/Y'),
                        $evidencia->descripcion,
                    ])->filter()->implode(' · ')),
                    'tipo' => $esVideo ? 'Video' : 'Imagen',
                    'url' => $esVideo ? $evidencia->enlace : $evidencia->url,
                    'url_video_embed' => $esVideo ? $this->videoEmbedUrl($evidencia->enlace) : null,
                    'es_video' => $esVideo,
                    'usuario' => $evidencia->registradoPor?->nickname ?: $evidencia->registradoPor?->full_name,
                    'fecha' => $evidencia->fecha_registro,
                ]);
            });

        DB::table('controlecp_asistencia_detalles as d')
            ->join('controlecp_asistencias as a', 'a.id', '=', 'd.asistencia_id')
            ->join('cronograma as c', 'c.id_cronograma', '=', 'a.cronograma_id')
            ->leftJoin('controlecp_actividades as act', 'act.id', '=', 'c.id_actividad')
            ->leftJoin('courses as co', 'co.id', '=', 'c.id_ficha')
            ->leftJoin('apprentices as ap', 'ap.id', '=', 'd.aprendiz_id')
            ->leftJoin('people as p', 'p.id', '=', 'ap.person_id')
            ->leftJoin('users as u', 'u.id', '=', 'a.registrado_por')
            ->where('c.id_instructor', Auth::id())
            ->where('a.registrado_por', Auth::id())
            ->where('a.estado', 'Registrada')
            ->where('d.justificada', true)
            ->whereNotNull('d.excusa_imagen')
            ->select([
                'c.id_ficha as ficha_id',
                'd.excusa_imagen',
                'd.observacion',
                'd.created_at',
                'act.nombre as actividad',
                'co.code as ficha',
                'p.first_name',
                'p.first_last_name',
                'p.second_last_name',
                'u.nickname',
            ])
            ->orderByDesc('d.created_at')
            ->get()
            ->each(function ($excusa) use ($evidencias) {
                $evidencias->push([
                    'categoria' => 'Excusas',
                    'ficha_id' => $excusa->ficha_id,
                    'ficha' => $excusa->ficha,
                    'titulo' => 'Excusa de ' . trim("{$excusa->first_name} {$excusa->first_last_name} {$excusa->second_last_name}"),
                    'detalle' => trim(collect([
                        $excusa->ficha ? 'Ficha ' . $excusa->ficha : null,
                        $excusa->actividad,
                        $excusa->observacion,
                    ])->filter()->implode(' · ')),
                    'tipo' => 'Imagen',
                    'url' => Storage::disk('public')->url($excusa->excusa_imagen),
                    'url_video_embed' => null,
                    'es_video' => false,
                    'usuario' => $excusa->nickname,
                    'fecha' => $excusa->created_at,
                ]);
            });

        MovimientoInventario::with(['inventario', 'usuario'])
            ->where('id_usuario', Auth::id())
            ->whereNotNull('foto')
            ->orderByDesc('fecha')
            ->get()
            ->each(function (MovimientoInventario $movimiento) use ($evidencias) {
                $recurso = $movimiento->inventario?->nombre ?? 'Recurso de inventario';
                $evidencias->push([
                    'categoria' => 'Inventario',
                    'movimiento_id' => $movimiento->id_movimiento_inventario,
                    'inventario_id' => $movimiento->id_inventario,
                    'inventario_nombre' => $recurso,
                    'ficha_id' => null,
                    'ficha' => null,
                    'titulo' => $recurso . ' · ' . $movimiento->tipo,
                    'detalle' => trim(collect([
                        $recurso,
                        'Cantidad: ' . $movimiento->cantidad,
                        $movimiento->observacion,
                    ])->filter()->implode(' · ')),
                    'tipo' => 'Imagen',
                    'url' => asset($movimiento->foto),
                    'url_video_embed' => null,
                    'es_video' => false,
                    'usuario' => $movimiento->usuario?->nickname ?: $movimiento->usuario?->full_name,
                    'fecha' => $movimiento->fecha,
                ]);
            });

        $categorias = ['Actividades', 'Asistencias', 'Acompañamientos', 'Excusas', 'Inventario'];
        $totalActividades = $evidencias->where('categoria', 'Actividades')->count();
        $totalAsistencias = $evidencias->where('categoria', 'Asistencias')->count();
        $totalAcompanamientos = $evidencias->where('categoria', 'Acompañamientos')->count();
        $totalExcusas = $evidencias->where('categoria', 'Excusas')->count();
        $totalInventario = $evidencias
            ->where('categoria', 'Inventario')
            ->filter(fn (array $evidencia) => !empty($evidencia['inventario_id']))
            ->unique('inventario_id')
            ->count();
        $filtroCategoria = $filtros['categoria'] ?? '';
        $busqueda = trim($filtros['buscar'] ?? '');
        $filtroFichaId = (string) ($filtros['ficha_id'] ?? '');
        $filtroProductoId = (string) ($filtros['producto_id'] ?? '');
        $filtroMovimientoId = (string) ($filtros['movimiento_id'] ?? '');
        if ($filtroCategoria !== 'Inventario') {
            $filtroProductoId = '';
            $filtroMovimientoId = '';
        }

        $evidenciasCategoria = $evidencias->filter(function (array $evidencia) use ($filtroCategoria, $busqueda) {
            if ($filtroCategoria && $evidencia['categoria'] !== $filtroCategoria) {
                return false;
            }

            if ($busqueda !== '') {
                $texto = implode(' ', [
                    $evidencia['categoria'],
                    $evidencia['titulo'],
                    $evidencia['detalle'],
                    $evidencia['usuario'] ?? '',
                ]);

                return mb_stripos($texto, $busqueda) !== false;
            }

            return true;
        })->sortByDesc(fn (array $evidencia) => strtotime((string) $evidencia['fecha']) ?: 0)->values();

        $productos = $evidenciasCategoria
            ->where('categoria', 'Inventario')
            ->filter(fn (array $evidencia) => !empty($evidencia['inventario_id']))
            ->groupBy('inventario_id')
            ->map(fn ($movimientos, $id) => [
                'id' => (string) $id,
                'nombre' => $movimientos->first()['inventario_nombre'],
            ])
            ->sortBy('nombre')
            ->values();
        if ($filtroProductoId !== '' && !$productos->contains(fn (array $producto) => $producto['id'] === $filtroProductoId)) {
            $filtroProductoId = '';
        }
        $productoSeleccionado = $productos->first(
            fn (array $producto) => $producto['id'] === $filtroProductoId
        );

        $fichas = $evidenciasCategoria
            ->filter(fn (array $evidencia) => !empty($evidencia['ficha_id']) && !empty($evidencia['ficha']))
            ->unique('ficha_id')
            ->map(fn (array $evidencia) => [
                'id' => $evidencia['ficha_id'],
                'codigo' => $evidencia['ficha'],
            ])
            ->sortBy('codigo')
            ->values();

        $fichaSeleccionada = $fichas->first(
            fn (array $ficha) => (string) $ficha['id'] === $filtroFichaId
        );
        if (!$fichaSeleccionada) {
            $filtroFichaId = '';
        }

        $evidenciasFicha = $filtroFichaId === ''
            ? collect()
            : $evidenciasCategoria
                ->filter(fn (array $evidencia) => (string) ($evidencia['ficha_id'] ?? '') === $filtroFichaId)
                ->values();
        if ($filtroCategoria === 'Inventario') {
            $evidenciasFicha = $evidenciasCategoria;
        }

        $evidenciasProducto = $filtroProductoId === ''
            ? collect()
            : $evidenciasCategoria->filter(
                fn (array $evidencia) => (string) ($evidencia['inventario_id'] ?? '') === $filtroProductoId
            )->values();
        $porPagina = 8;
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        if ($filtroMovimientoId !== '' && !$request->query->has('page')) {
            $indiceMovimiento = $evidenciasProducto->search(
                fn (array $evidencia) => (string) $evidencia['movimiento_id'] === $filtroMovimientoId
            );
            if ($indiceMovimiento !== false) {
                $pagina = intdiv($indiceMovimiento, $porPagina) + 1;
            }
        }
        $fichasPaginadas = new LengthAwarePaginator(
            $fichas->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $fichas->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $productosPaginados = new LengthAwarePaginator(
            $productos->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $productos->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $evidenciasParaMostrar = $filtroCategoria === 'Inventario'
            ? $evidenciasProducto
            : $evidenciasFicha;
        $registros = new LengthAwarePaginator(
            $evidenciasParaMostrar->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
            $evidenciasParaMostrar->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $paginacion = $filtroCategoria === 'Inventario'
            ? ($filtroProductoId === '' ? $productosPaginados : $registros)
            : ($filtroFichaId === '' ? $fichasPaginadas : $registros);

        return view('controlecp::instructor.repositorio.evidencias', compact(
            'registros', 'fichas', 'fichasPaginadas', 'paginacion', 'fichaSeleccionada', 'filtroFichaId',
            'productos', 'productosPaginados', 'productoSeleccionado', 'filtroProductoId',
            'filtroMovimientoId',
            'categorias', 'totalActividades', 'totalAsistencias', 'totalAcompanamientos', 'totalExcusas', 'totalInventario',
            'filtroCategoria', 'busqueda'
        ));
    }

    private function videoEmbedUrl(?string $url): ?string
    {
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true)) {
            $videoId = $host === 'youtu.be'
                ? trim($path, '/')
                : (($query['v'] ?? null) ?: (preg_match('~^/(?:embed|shorts)/([^/]+)~', $path, $matches) ? $matches[1] : null));

            return $videoId && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)
                ? 'https://www.youtube-nocookie.com/embed/' . $videoId
                : null;
        }

        if (in_array($host, ['drive.google.com', 'www.drive.google.com'], true)) {
            $fileId = preg_match('~^/file/d/([A-Za-z0-9_-]+)~', $path, $matches)
                ? $matches[1]
                : ($query['id'] ?? null);

            return $fileId && preg_match('/^[A-Za-z0-9_-]+$/', $fileId)
                ? 'https://drive.google.com/file/d/' . $fileId . '/preview'
                : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)
            && preg_match('~^/([0-9]+)(?:/|$)~', $path, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return null;
    }

    public function encuestas(Request $request, TabulacionService $tabulacionService)
    {
        $this->autorizarInstructor();
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', Rule::in(['Borrador', 'Activa', 'Inactiva'])],
        ]);

        $consultaVisible = $this->encuestasVisiblesParaInstructor();
        $query = (clone $consultaVisible)->with(['actividades', 'usuario.roles', 'preguntas.opciones'])
            ->withCount(['preguntas', 'actividades']);

        if ($request->filled('buscar')) {
            $termino = trim($request->input('buscar'));
            $query->where(function ($encuestaQuery) use ($termino) {
                $encuestaQuery->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $encuestas = $query->orderByDesc('fecha_creacion')
            ->orderByDesc('id_encuesta')
            ->paginate(10)
            ->withQueryString();

        $encuestas->getCollection()->each(function (Encuesta $encuesta) {
            $puedeGestionar = (int) $encuesta->id_usuario === (int) Auth::id()
                || ($encuesta->usuario?->hasRole('controlecp.admin') ?? false);

            if ($puedeGestionar) {
                $encuesta->setAttribute('tiene_respuestas', $encuesta->tieneRespuestas());
            }
        });

        $actividadesInstructor = DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->whereNotNull('id_actividad')
            ->pluck('id_actividad')
            ->map(fn ($activityId) => (int) $activityId);
        $actividadesEncuestas = DB::table('controlecp_actividades')
            ->whereIn('id_encuesta', $encuestas->getCollection()->pluck('id_encuesta'))
            ->pluck('id');
        $actividadesDisponibles = Actividad::withTrashed()
            ->whereIn('id', $actividadesInstructor->merge($actividadesEncuestas)->unique())
            ->orderBy('numero')
            ->get();

        $indicadores = [
            'total' => (clone $consultaVisible)->count(),
            'activas' => (clone $consultaVisible)->where('estado', 'Activa')->count(),
            'borradores' => (clone $consultaVisible)->where('estado', 'Borrador')->count(),
            'inactivas' => (clone $consultaVisible)->where('estado', 'Inactiva')->count(),
        ];

        $encuestasTabulables = $tabulacionService->obtenerEncuestasDisponibles((int) Auth::id())
            ->pluck('id_encuesta')
            ->all();

        return view('controlecp::instructor.repositorio.encuestas', compact(
            'encuestas',
            'indicadores',
            'encuestasTabulables',
            'actividadesDisponibles',
            'actividadesInstructor'
        ));
    }

    public function tabulacion(Request $request, TabulacionService $tabulacionService)
    {
        $this->autorizarInstructor();

        $instructorId = (int) Auth::id();
        $encuestas = $tabulacionService->obtenerEncuestasDisponibles($instructorId);
        $request->validate([
            'id_encuesta' => ['nullable', 'integer', Rule::in($encuestas->pluck('id_encuesta')->all())],
        ]);

        $idEncuesta = $request->input('id_encuesta') ?: $encuestas->first()?->id_encuesta;
        $encuestaActual = null;
        $filtrosDisponibles = ['actividades' => collect(), 'fichas' => collect()];
        $resultados = null;

        if ($idEncuesta) {
            $encuestaActual = Encuesta::findOrFail($idEncuesta);
            $filtrosDisponibles = $tabulacionService->obtenerFiltrosParaEncuesta((int) $idEncuesta, $instructorId);
            $filtros = $request->validate([
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
                'id_actividad' => [
                    'nullable',
                    'integer',
                    Rule::in($filtrosDisponibles['actividades']->pluck('id')->all()),
                ],
                'id_ficha' => [
                    'nullable',
                    'integer',
                    Rule::in($filtrosDisponibles['fichas']->pluck('id')->all()),
                ],
            ], [
                'fecha_hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            ]);
            $resultados = $tabulacionService->calcularResultados((int) $idEncuesta, $filtros, $instructorId);
        }

        return view('controlecp::instructor.repositorio.tabulacion', compact(
            'encuestas', 'encuestaActual', 'filtrosDisponibles', 'resultados'
        ));
    }

    public function exportarTabulacionPdf(Request $request, TabulacionService $tabulacionService)
    {
        $this->autorizarInstructor();

        $instructorId = (int) Auth::id();
        $encuestas = $tabulacionService->obtenerEncuestasDisponibles($instructorId);
        $datos = $request->validate([
            'id_encuesta' => ['required', 'integer', Rule::in($encuestas->pluck('id_encuesta')->all())],
        ]);

        $idEncuesta = (int) $datos['id_encuesta'];
        $filtrosDisponibles = $tabulacionService->obtenerFiltrosParaEncuesta($idEncuesta, $instructorId);
        $filtros = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'id_actividad' => [
                'nullable',
                'integer',
                Rule::in($filtrosDisponibles['actividades']->pluck('id')->all()),
            ],
            'id_ficha' => [
                'nullable',
                'integer',
                Rule::in($filtrosDisponibles['fichas']->pluck('id')->all()),
            ],
        ], [
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $resultados = $tabulacionService->calcularResultados($idEncuesta, $filtros, $instructorId);
        $encuesta = $resultados['encuesta'];

        if ($resultados['consolidado']['total_respuestas'] === 0) {
            return redirect()
                ->back()
                ->with('warning', 'No es posible exportar el informe porque la encuesta no tiene respuestas registradas con los filtros seleccionados.');
        }

        $actividad = ! empty($filtros['id_actividad'])
            ? $filtrosDisponibles['actividades']->firstWhere('id', $filtros['id_actividad'])
            : null;
        $ficha = ! empty($filtros['id_ficha'])
            ? $filtrosDisponibles['fichas']->firstWhere('id', $filtros['id_ficha'])
            : null;
        $nombreActividad = $actividad ? "Actividad: {$actividad->nombre}" : null;
        $codigoFicha = $ficha ? "Ficha: {$ficha->code}" : null;
        $fechaGeneracion = Carbon::now('America/Bogota')->format('d/m/Y H:i');
        $slugEncuesta = Str::slug($encuesta->nombre) ?: 'encuesta';
        $fechaNombre = Carbon::now('America/Bogota')->format('Ymd');
        $nombreDescarga = "tabulacion_{$slugEncuesta}_{$fechaNombre}.pdf";

        $pdf = Pdf::loadView('controlecp::instructor.repositorio.tabulacion_pdf', compact(
            'encuesta',
            'resultados',
            'filtros',
            'nombreActividad',
            'codigoFicha',
            'fechaGeneracion'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($nombreDescarga);
    }
}
