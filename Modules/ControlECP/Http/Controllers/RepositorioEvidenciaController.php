<?php

namespace Modules\ControlECP\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\EvidenciaEcp;
use Modules\ControlECP\Entities\MovimientoInventario;

class RepositorioEvidenciaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.admin'), 403);

        $evidencias = collect();

        EvidenciaEcp::with(['actividad.momento', 'ficha.program', 'registradoPor'])
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
            ->each(function (EvidenciaEcp $evidencia) use ($evidencias) {
                $rutaActividad = 'controlecp/evidencias/' . $evidencia->actividad_id . '/';
                $esEvidenciaActividad = $evidencia->origen
                    ? $evidencia->origen === 'actividad'
                    : ($evidencia->tipo === 'foto'
                        ? str_starts_with(ltrim($evidencia->archivo ?? '', '/'), $rutaActividad)
                        : !$evidencia->tiene_asistencia_registrada);
                $esVideo = $evidencia->tipo === 'video';

                $evidencias->push([
                    'categoria' => $esEvidenciaActividad ? 'Actividades' : 'Asistencias',
                    'ficha_id' => $evidencia->course_id,
                    'ficha' => $evidencia->ficha?->code,
                    'usuario_id' => $evidencia->user_id,
                    'titulo' => $evidencia->actividad?->nombre ?: ($esEvidenciaActividad ? 'Evidencia de actividad' : 'Evidencia de asistencia'),
                    'detalle' => trim(collect([
                        $evidencia->ficha?->code ? 'Ficha ' . $evidencia->ficha->code : null,
                        $evidencia->ficha?->program?->name,
                        $evidencia->actividad?->momento?->nombre,
                        $evidencia->descripcion,
                    ])->filter()->implode(' · ')),
                    'tipo' => $esVideo ? 'Video' : 'Imagen',
                    'url' => $esVideo ? $evidencia->enlace : $evidencia->url,
                    'url_video_embed' => $esVideo ? $this->videoEmbedUrl($evidencia->enlace) : null,
                    'es_imagen' => !$esVideo,
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
            ->where('a.estado', 'Registrada')
            ->where('d.justificada', true)
            ->whereNotNull('d.excusa_imagen')
            ->select([
                'd.excusa_imagen',
                'd.observacion',
                'd.created_at',
                'c.id_ficha as ficha_id',
                'a.registrado_por as usuario_id',
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
                    'usuario_id' => $excusa->usuario_id,
                    'titulo' => 'Excusa de ' . trim("{$excusa->first_name} {$excusa->first_last_name} {$excusa->second_last_name}"),
                    'detalle' => trim(collect([
                        $excusa->ficha ? 'Ficha ' . $excusa->ficha : null,
                        $excusa->actividad,
                        $excusa->observacion,
                    ])->filter()->implode(' · ')),
                    'tipo' => 'Imagen',
                    'url' => Storage::disk('public')->url($excusa->excusa_imagen),
                    'url_video_embed' => null,
                    'es_imagen' => true,
                    'es_video' => false,
                    'usuario' => $excusa->nickname,
                    'fecha' => $excusa->created_at,
                ]);
            });

        MovimientoInventario::with(['inventario', 'usuario'])
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
                    'usuario_id' => $movimiento->id_usuario,
                    'titulo' => $recurso . ' · ' . $movimiento->tipo,
                    'detalle' => trim(collect([
                        $recurso,
                        'Cantidad: ' . $movimiento->cantidad,
                        $movimiento->observacion,
                    ])->filter()->implode(' · ')),
                    'tipo' => 'Imagen',
                    'url' => asset($movimiento->foto),
                    'url_video_embed' => null,
                    'es_imagen' => true,
                    'es_video' => false,
                    'usuario' => $movimiento->usuario?->nickname ?: $movimiento->usuario?->full_name,
                    'fecha' => $movimiento->fecha,
                ]);
            });

        $conteoCategorias = $evidencias->countBy('categoria');
        $cantidadExcusas = $conteoCategorias->get('Excusas', 0);
        $cantidadActividades = $conteoCategorias->get('Actividades', 0);
        $cantidadAsistencias = $conteoCategorias->get('Asistencias', 0);
        $cantidadInventario = $evidencias
            ->where('categoria', 'Inventario')
            ->filter(fn (array $evidencia) => !empty($evidencia['inventario_id']))
            ->unique('inventario_id')
            ->count();

        $categoriasPermitidas = ['Excusas', 'Actividades', 'Asistencias', 'Inventario'];
        $filtroCategoria = $request->input('categoria');
        if (!in_array($filtroCategoria, $categoriasPermitidas, true)) {
            $filtroCategoria = null;
        }

        $evidenciasCategoria = $filtroCategoria
            ? $evidencias->where('categoria', $filtroCategoria)
            : collect();
        $filtroFicha = $request->input('ficha');
        $filtroFicha = is_string($filtroFicha) ? trim($filtroFicha) : '';
        $filtroUsuario = $request->input('usuario');
        $filtroUsuario = is_string($filtroUsuario) ? trim($filtroUsuario) : '';
        $filtroFichaId = $request->input('ficha_id');
        $filtroFichaId = is_scalar($filtroFichaId) ? trim((string) $filtroFichaId) : '';
        $filtroProductoId = $request->input('producto_id');
        $filtroProductoId = is_scalar($filtroProductoId) && ctype_digit((string) $filtroProductoId)
            ? trim((string) $filtroProductoId)
            : '';
        $filtroMovimientoId = $request->input('movimiento_id');
        $filtroMovimientoId = is_scalar($filtroMovimientoId) && ctype_digit((string) $filtroMovimientoId)
            ? trim((string) $filtroMovimientoId)
            : '';
        if ($filtroCategoria === 'Inventario') {
            $filtroFichaId = '';
        } else {
            $filtroProductoId = '';
            $filtroMovimientoId = '';
        }

        $coincideConFiltros = static function (array $evidencia) use ($filtroFicha, $filtroUsuario) {
            if ($filtroFicha !== '' && mb_stripos((string) ($evidencia['ficha'] ?? ''), $filtroFicha) === false) {
                return false;
            }

            if ($filtroUsuario !== '' && mb_stripos((string) ($evidencia['usuario'] ?? ''), $filtroUsuario) === false) {
                return false;
            }

            return true;
        };
        $evidenciasCoincidentes = $evidenciasCategoria->filter($coincideConFiltros);
        $productos = $evidenciasCoincidentes
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
        $fichas = $evidenciasCoincidentes
            ->filter(fn (array $evidencia) => !empty($evidencia['ficha_id']) && !empty($evidencia['ficha']))
            ->unique('ficha_id')
            ->map(fn (array $evidencia) => [
                'id' => $evidencia['ficha_id'],
                'codigo' => $evidencia['ficha'],
            ])
            ->sortBy('codigo')
            ->values();
        $usuarios = $evidenciasCategoria
            ->filter(fn (array $evidencia) => !empty($evidencia['usuario_id']) && !empty($evidencia['usuario']))
            ->unique('usuario_id')
            ->map(fn (array $evidencia) => [
                'id' => $evidencia['usuario_id'],
                'nombre' => $evidencia['usuario'],
            ])
            ->sortBy('nombre')
            ->values();

        $evidencias = $evidenciasCoincidentes
            ->filter(fn (array $evidencia) => $filtroFichaId === '' || (string) ($evidencia['ficha_id'] ?? '') === $filtroFichaId)
            ->sortByDesc(fn (array $evidencia) => strtotime((string) $evidencia['fecha']) ?: 0)
            ->values();

        $porPagina = 8;
        $pagina = LengthAwarePaginator::resolveCurrentPage();
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
        if ($filtroCategoria === 'Inventario') {
            $evidenciasProducto = $filtroProductoId === ''
                ? collect()
                : $evidenciasCoincidentes->filter(
                    fn (array $evidencia) => (string) ($evidencia['inventario_id'] ?? '') === $filtroProductoId
                )->values();
            if ($filtroMovimientoId !== '' && !$request->query->has('page')) {
                $indiceMovimiento = $evidenciasProducto->search(
                    fn (array $evidencia) => (string) $evidencia['movimiento_id'] === $filtroMovimientoId
                );
                if ($indiceMovimiento !== false) {
                    $pagina = intdiv($indiceMovimiento, $porPagina) + 1;
                }
            }
            $registros = new LengthAwarePaginator(
                $evidenciasProducto->sortByDesc(fn (array $evidencia) => strtotime((string) $evidencia['fecha']) ?: 0)
                    ->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
                $evidenciasProducto->count(),
                $porPagina,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $fichaSeleccionada = 'Inventario';
            $paginacion = $filtroProductoId === '' ? $productosPaginados : $registros;
        } else {
            $evidenciaFichaSeleccionada = $evidenciasCategoria->first(
                fn (array $evidencia) => (string) ($evidencia['ficha_id'] ?? '') === $filtroFichaId
            );
            $fichaSeleccionada = $evidenciaFichaSeleccionada['ficha'] ?? 'Sin ficha';
            $registros = new LengthAwarePaginator(
                $evidencias->slice(($pagina - 1) * $porPagina, $porPagina)->values(),
                $evidencias->count(),
                $porPagina,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $paginacion = $filtroFichaId === '' ? $fichasPaginadas : $registros;
        }

        return view('controlecp::administrador.repositorio.evidencias', compact(
            'registros',
            'fichasPaginadas',
            'paginacion',
            'filtroCategoria',
            'fichas',
            'usuarios',
            'filtroFicha',
            'filtroUsuario',
            'filtroFichaId',
            'filtroProductoId',
            'filtroMovimientoId',
            'fichaSeleccionada',
            'productos',
            'productosPaginados',
            'productoSeleccionado',
            'cantidadExcusas',
            'cantidadActividades',
            'cantidadAsistencias',
            'cantidadInventario'
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
}
