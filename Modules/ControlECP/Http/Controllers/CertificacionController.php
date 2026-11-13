<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Services\CertificacionFichaService;
use Modules\ControlECP\Services\CertificacionNotificacionService;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Course;

class CertificacionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $courseIdsForListing = $this->fichasConAsistenciaRegistrada()
            ->pluck('id_ficha')
            ->merge(DB::table('controlecp_certificaciones')->distinct()->pluck('course_id'))
            ->unique()
            ->values();
        $fichas = Course::query()
            ->whereIn('id', $courseIdsForListing)
            ->orderBy('code')
            ->get(['id', 'code']);
        $momentos = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'orden']);
        $responsables = User::query()
            ->whereIn('id', DB::table('controlecp_certificaciones')->select('responsable_id')->distinct())
            ->orderBy('nickname')
            ->get(['id', 'nickname']);

        $cursos = Course::with('program')
            ->whereIn('id', $courseIdsForListing)
            ->when($request->filled('ficha_id'), fn ($query) => $query->whereKey($request->input('ficha_id')))
            ->when(!$request->filled('ficha_id') && $request->filled('ficha'), fn ($query) => $query->where('code', 'like', '%' . trim($request->input('ficha')) . '%'))
            ->orderBy('code')
            ->get();
        $courseIds = $cursos->modelKeys();
        $momentIds = $momentos->pluck('id');

        $actividadesPorMomento = DB::table('controlecp_actividades')
            ->whereIn('momento_id', $momentIds)
            ->whereNull('deleted_at')
            ->where('estado', '!=', 'Inactivo')
            ->orderBy('numero')
            ->get(['id', 'momento_id', 'numero', 'nombre'])
            ->groupBy('momento_id');

        $actividadesFinalizadas = DB::table('cronograma as c')
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->whereIn('c.id_ficha', $courseIds)
            ->whereIn('a.momento_id', $momentIds)
            ->where('c.estado', 'Finalizada')
            ->whereNull('a.deleted_at')
            ->select('c.id_ficha', 'a.momento_id', 'a.id', 'a.numero', 'a.nombre')
            ->distinct()
            ->get()
            ->groupBy(fn ($actividad) => $actividad->id_ficha . '-' . $actividad->momento_id);

        $certificaciones = DB::table('controlecp_certificaciones as c')
            ->join('users as u', 'u.id', '=', 'c.responsable_id')
            ->whereIn('c.course_id', $courseIds)
            ->whereIn('c.momento_id', $momentIds)
            ->select('c.*', 'u.nickname')
            ->get()
            ->groupBy(fn ($certificacion) => $certificacion->course_id . '-' . $certificacion->momento_id);

        $filas = collect();
        foreach ($cursos as $curso) {
            $secuenciaCompleta = true;
            foreach ($momentos as $momento) {
                $clave = $curso->id . '-' . $momento->id;
                $actividades = $actividadesPorMomento->get($momento->id, collect())->values();
                $finalizadas = $actividadesFinalizadas->get($clave, collect())->keyBy('id');
                $certificacionesMomento = $certificaciones->get($clave, collect());
                $certificacionAdmin = $certificacionesMomento->firstWhere('responsable_tipo', 'admin');
                $certificacionInstructor = $certificacionesMomento->firstWhere('responsable_tipo', 'instructor');
                $certificacion = $certificacionAdmin ?? $certificacionInstructor;
                $pendientes = $actividades->reject(fn ($actividad) => $finalizadas->has($actividad->id))->values();
                $actividadesListas = $actividades->isNotEmpty() && $pendientes->isEmpty();
                $momentoCertificado = $certificacionesMomento->isNotEmpty();
                $puedeCertificar = !$momentoCertificado && $secuenciaCompleta && $actividadesListas;
                $bloqueadoPorSecuencia = !$momentoCertificado && !$secuenciaCompleta && $actividadesListas;
                $estado = $certificacionAdmin
                    ? 'Certificada'
                    : ($certificacionInstructor
                        ? 'Certificada por instructor'
                        : ($puedeCertificar ? 'Pendiente de certificar' : 'En progreso'));
                $responsableNombre = $certificacion?->nickname;

                $filas->push([
                    'course_id' => $curso->id,
                    'ficha' => $curso->code,
                    'programa' => $curso->program?->name ?: 'Sin programa',
                    'momento_id' => $momento->id,
                    'momento' => $momento->nombre,
                    'momento_orden' => $momento->orden,
                    'actividades' => $actividades,
                    'finalizadas' => $finalizadas->values(),
                    'pendientes' => $pendientes,
                    'total' => $actividades->count(),
                    'completadas' => $finalizadas->count(),
                    'porcentaje' => $actividades->isEmpty() ? 0 : (int) round($finalizadas->count() * 100 / $actividades->count()),
                    'estado' => $estado,
                    'estado_admin' => $momentoCertificado
                        ? 'Certificada'
                        : ($puedeCertificar ? 'Pendiente de certificar' : 'En progreso'),
                    'puede_certificar' => $puedeCertificar,
                    'bloqueado_por_secuencia' => $bloqueadoPorSecuencia,
                    'certificacion' => $certificacion,
                    'certificaciones' => $certificacionesMomento,
                    'certificacion_admin' => $certificacionAdmin,
                    'certificacion_instructor' => $certificacionInstructor,
                    'responsable' => $responsableNombre,
                ]);
                $secuenciaCompleta = $secuenciaCompleta && $momentoCertificado;
            }
        }

        $filas = $filas
            ->groupBy('course_id')
            ->map(function ($momentosFicha) use ($request) {
                $coincideConFiltros = $momentosFicha->contains(function ($fila) use ($request) {
                    if ($request->filled('momento_id') && (int) $request->input('momento_id') !== (int) $fila['momento_id']) {
                        return false;
                    }
                    if (($request->filled('responsable_id') || $request->filled('fecha_desde') || $request->filled('fecha_hasta'))
                        && !$fila['certificaciones']->contains(function ($certificacion) use ($request) {
                            return (!$request->filled('responsable_id') || (int) $certificacion->responsable_id === (int) $request->input('responsable_id'))
                                && (!$request->filled('fecha_desde') || $certificacion->fecha_certificacion >= $request->input('fecha_desde') . ' 00:00:00')
                                && (!$request->filled('fecha_hasta') || $certificacion->fecha_certificacion <= $request->input('fecha_hasta') . ' 23:59:59');
                        })) {
                        return false;
                    }

                    return true;
                });
                if (!$coincideConFiltros) {
                    return null;
                }

                $momentosFicha = $momentosFicha->sortBy('momento_orden')->values();
                $certificados = $momentosFicha->filter(fn ($fila) => $fila['certificaciones']->isNotEmpty())->values();
                $paraCertificar = $momentosFicha->where('estado_admin', 'Pendiente de certificar')->values();
                $certificacionesFicha = $certificados->count();
                $totalActividades = $momentosFicha->sum('total');
                $actividadesCompletadas = $momentosFicha->sum('completadas');
                $estado = $certificacionesFicha === $momentosFicha->count()
                    ? 'Certificada'
                    : ($paraCertificar->isNotEmpty() ? 'Pendiente de certificar' : 'En progreso');
                if ($request->filled('estado') && $request->input('estado') !== $estado) {
                    return null;
                }

                $responsables = $certificados
                    ->flatMap(fn ($fila) => $fila['certificaciones']->map(
                        fn ($certificacion) => $certificacion->nickname . ' (' . ($certificacion->responsable_tipo === 'instructor' ? 'Instructor' : 'Administrador') . ')'
                    ))
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'course_id' => $momentosFicha[0]['course_id'],
                    'ficha' => $momentosFicha[0]['ficha'],
                    'programa' => $momentosFicha[0]['programa'],
                    'momentos' => $momentosFicha,
                    'momentos_para_certificar' => $paraCertificar,
                    'certificaciones_ficha' => $certificacionesFicha,
                    'certificado_completo' => $momentosFicha->count() >= 3
                        && $certificacionesFicha === $momentosFicha->count(),
                    'completadas' => $actividadesCompletadas,
                    'total' => $totalActividades,
                    'porcentaje' => $totalActividades === 0 ? 0 : (int) round($actividadesCompletadas * 100 / $totalActividades),
                    'estado' => $estado,
                    'responsable' => $responsables->isEmpty() ? null : $responsables->implode(', '),
                ];
            })
            ->filter()
            ->sortBy('ficha')
            ->values();
        $porPagina = $request->boolean('reporte') ? max($filas->count(), 1) : 10;
        $pagina = $request->boolean('reporte') ? 1 : LengthAwarePaginator::resolveCurrentPage();
        $filasPagina = $filas->slice(($pagina - 1) * $porPagina, $porPagina)->values();
        $stats = [
            'fichas' => $filas->count(),
            'para_certificar' => $filas->where('estado', 'Pendiente de certificar')->count(),
            'certificadas' => $filas->where('estado', 'Certificada')->count(),
            'pendientes' => $filas->where('estado', 'En progreso')->count(),
            'uno' => $filas->where('certificaciones_ficha', 1)->count(),
            'dos' => $filas->where('certificaciones_ficha', 2)->count(),
            'tres' => $filas->where('certificaciones_ficha', '>=', 3)->count(),
        ];
        $registros = new LengthAwarePaginator(
            $filasPagina,
            $filas->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('controlecp::administrador.certificados', compact(
            'registros',
            'fichas',
            'momentos',
            'responsables',
            'stats'
        ));
    }

    public function certificar(int $fichaId, int $momentoId)
    {
        $this->authorizeAdmin();

        $curso = Course::with('program')->findOrFail($fichaId);
        if (!$this->fichasConAsistenciaRegistrada()->where('cronograma.id_ficha', $fichaId)->exists()) {
            throw ValidationException::withMessages([
                'certificacion' => 'No se puede certificar: la ficha aún no tiene asistencias registradas.',
            ]);
        }

        $momento = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->where('id', $momentoId)
            ->first();
        abort_unless($momento, 404);

        $actividades = DB::table('controlecp_actividades')
            ->where('momento_id', $momentoId)
            ->whereNull('deleted_at')
            ->where('estado', '!=', 'Inactivo')
            ->orderBy('numero')
            ->get(['id', 'nombre']);
        $finalizadasIds = DB::table('cronograma')
            ->where('id_ficha', $fichaId)
            ->where('estado', 'Finalizada')
            ->whereIn('id_actividad', $actividades->pluck('id'))
            ->distinct()
            ->pluck('id_actividad');
        $pendientes = $actividades->reject(fn ($actividad) => $finalizadasIds->contains($actividad->id));

        if ($actividades->isEmpty() || $pendientes->isNotEmpty()) {
            throw ValidationException::withMessages([
                'certificacion' => 'No se puede certificar: faltan actividades por finalizar en el momento seleccionado.',
            ]);
        }

        $certificacion = DB::transaction(function () use ($curso, $momento) {
            Course::whereKey($curso->id)->lockForUpdate()->firstOrFail();
            app(CertificacionFichaService::class)
                ->validarSiguienteMomento((int) $curso->id, (int) $momento->id);

            $id = DB::table('controlecp_certificaciones')->insertGetId([
                'course_id' => $curso->id,
                'momento_id' => $momento->id,
                'responsable_id' => Auth::id(),
                'responsable_tipo' => 'admin',
                'fecha_certificacion' => now(),
                'estado' => 'Certificada',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->notificarCertificacion($curso, $momento, $id);
            app(CertificacionNotificacionService::class)->notificarSiSoloFaltaUnMomento($curso);

            return $id;
        });

        return redirect()->route('controlecp.administrador.certificados')
            ->with('success', "Momento {$momento->nombre} de la ficha {$curso->code} registrado como certificado. La emisión oficial debe realizarse en SOFIA Plus.");
    }

    public function descargarConstancia(int $fichaId)
    {
        $this->authorizeAdmin();

        $curso = Course::with('program')->findOrFail($fichaId);

        return app(CertificacionFichaService::class)->descargarConstancia($curso);
    }

    private function notificarCertificacion(Course $curso, object $momento, int $certificacionId): void
    {
        $aprendizPersonIds = Apprentice::where('course_id', $curso->id)->pluck('person_id');
        $actividadIds = DB::table('controlecp_actividades')
            ->where('momento_id', $momento->id)
            ->whereNull('deleted_at')
            ->pluck('id');
        $instructorIds = DB::table('cronograma')
            ->where('id_ficha', $curso->id)
            ->whereIn('id_actividad', $actividadIds)
            ->distinct()
            ->pluck('id_instructor');
        $receptores = User::query()
            ->where(function ($query) use ($aprendizPersonIds, $instructorIds) {
                $query->where(function ($aprendices) use ($aprendizPersonIds) {
                    $aprendices->whereIn('person_id', $aprendizPersonIds)
                        ->whereHas('roles', fn ($roles) => $roles->where('slug', 'controlecp.apprentice'));
                })->orWhere(function ($instructores) use ($instructorIds) {
                    $instructores->whereIn('id', $instructorIds)
                        ->whereHas('roles', fn ($roles) => $roles->where('slug', 'controlecp.instructor'));
                });
            })
            ->where('id', '!=', Auth::id())
            ->get(['id']);

        foreach ($receptores as $receptor) {
            Notificacion::crear(
                idReceptor: (int) $receptor->id,
                idCausante: (int) Auth::id(),
                titulo: 'Momento de Cultura de Paz certificado',
                mensaje: "La ficha {$curso->code} completó y certificó el momento {$momento->nombre}. La emisión oficial se realiza en SOFIA Plus.",
                referenciaTipo: 'certificacion',
                referenciaId: $certificacionId,
                claveEvento: "certificacion-{$certificacionId}-usuario-{$receptor->id}"
            );
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.admin'), 403);
    }

    private function fichasConAsistenciaRegistrada(): Builder
    {
        return DB::table('cronograma')
            ->join('controlecp_asistencias', 'controlecp_asistencias.cronograma_id', '=', 'cronograma.id_cronograma')
            ->where('controlecp_asistencias.estado', 'Registrada')
            ->select('cronograma.id_ficha')
            ->distinct();
    }
}
