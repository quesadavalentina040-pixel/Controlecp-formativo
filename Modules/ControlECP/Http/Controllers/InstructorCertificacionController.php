<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Services\CertificacionFichaService;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Course;

class InstructorCertificacionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeInstructor();
        $request->validate([
            'ficha' => ['nullable', 'string', 'max:100'],
            'actividad' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:Certificada,Pendiente de certificar,En progreso'],
            'desde' => ['nullable', 'date', 'required_with:hasta', 'before_or_equal:hasta'],
            'hasta' => ['nullable', 'date', 'required_with:desde', 'after_or_equal:desde'],
        ]);
        $ownedCourseIds = DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->distinct()
            ->pluck('id_ficha');
        $courses = Course::with('program')
            ->whereIn('id', $ownedCourseIds)
            ->orderBy('code')
            ->get(['id', 'code', 'program_id']);
        $moments = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'orden']);
        $activities = DB::table('controlecp_actividades')
            ->whereIn('momento_id', $moments->pluck('id'))
            ->whereNull('deleted_at')
            ->where('estado', '!=', 'Inactivo')
            ->orderBy('numero')
            ->get(['id', 'momento_id', 'numero', 'nombre'])
            ->groupBy('momento_id');
        $activityOptions = $activities->flatten(1)->values();
        $matchingActivityMomentIds = $request->filled('actividad')
            ? $activityOptions
                ->filter(function ($activity) use ($request) {
                    $search = trim((string) $request->input('actividad'));
                    return stripos((string) $activity->numero, $search) !== false
                        || stripos((string) $activity->nombre, $search) !== false;
                })
                ->pluck('momento_id')
                ->unique()
                ->all()
            : [];
        $finished = DB::table('cronograma as c')
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->whereIn('c.id_ficha', $ownedCourseIds)
            ->whereIn('a.momento_id', $moments->pluck('id'))
            ->where('c.estado', 'Finalizada')
            ->whereNull('a.deleted_at')
            ->select('c.id_ficha', 'a.momento_id', 'a.id', 'a.numero', 'a.nombre')
            ->distinct()
            ->get()
            ->groupBy(fn ($activity) => $activity->id_ficha . '-' . $activity->momento_id);
        $responsablesInstructor = DB::table('role_user as ru')
            ->join('roles as r', 'r.id', '=', 'ru.role_id')
            ->where('r.slug', 'controlecp.instructor')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user as admin_ru')
                    ->join('roles as admin_role', 'admin_role.id', '=', 'admin_ru.role_id')
                    ->whereColumn('admin_ru.user_id', 'ru.user_id')
                    ->where('admin_role.slug', 'controlecp.admin');
            })
            ->pluck('ru.user_id')
            ->all();
        $certifications = DB::table('controlecp_certificaciones as c')
            ->whereIn('c.course_id', $ownedCourseIds)
            ->whereIn('c.momento_id', $moments->pluck('id'))
            ->where('c.responsable_tipo', 'instructor')
            ->whereIn('c.responsable_id', $responsablesInstructor)
            ->get()
            ->keyBy(fn ($certification) => $certification->course_id . '-' . $certification->momento_id);
        $allCertifications = DB::table('controlecp_certificaciones as c')
            ->join('users as u', 'u.id', '=', 'c.responsable_id')
            ->whereIn('c.course_id', $ownedCourseIds)
            ->whereIn('c.momento_id', $moments->pluck('id'))
            ->orderBy('c.fecha_certificacion')
            ->get(['c.*', 'u.nickname'])
            ->groupBy(fn ($certification) => $certification->course_id . '-' . $certification->momento_id);

        $rows = collect();
        foreach ($courses as $course) {
            $secuenciaCompleta = true;
            $momentRows = collect();
            foreach ($moments as $moment) {
                $key = $course->id . '-' . $moment->id;
                $momentActivities = $activities->get($moment->id, collect())->values();
                $completed = $finished->get($key, collect())->keyBy('id');
                $pending = $momentActivities->reject(fn ($activity) => $completed->has($activity->id))->values();
                $certification = $certifications->get($key);
                $momentCertifications = $allCertifications->get($key, collect());
                $momentCertified = $momentCertifications->isNotEmpty();
                $activitiesReady = $momentActivities->isNotEmpty() && $pending->isEmpty();
                $canCertify = !$momentCertified && $secuenciaCompleta && $activitiesReady;
                $momentRows->push([
                    'id' => $moment->id,
                    'nombre' => $moment->nombre,
                    'orden' => $moment->orden,
                    'actividades' => $momentActivities,
                    'finalizadas' => $completed->values(),
                    'pendientes' => $pending,
                    'total' => $momentActivities->count(),
                    'completadas' => $completed->count(),
                    'porcentaje' => $momentActivities->isEmpty()
                        ? 0
                        : (int) round($completed->count() * 100 / $momentActivities->count()),
                    'certificacion' => $certification ?? $momentCertifications->first(),
                    'certificaciones' => $momentCertifications,
                    'puede_certificar' => $canCertify,
                    'bloqueado_por_secuencia' => !$momentCertified && !$secuenciaCompleta && $activitiesReady,
                    'estado' => $momentCertified
                        ? 'Certificada'
                        : ($canCertify ? 'Pendiente de certificar' : 'En progreso'),
                ]);
                $secuenciaCompleta = $secuenciaCompleta && $momentCertified;
            }
            if ($request->filled('ficha') && stripos((string) $course->code, trim((string) $request->input('ficha'))) === false) {
                continue;
            }
            $certificacionesCurso = $momentRows->where('estado', 'Certificada')->count();
            $certificacionCompleta = $moments->count() >= 3
                && $certificacionesCurso === $moments->count();
            if ($request->filled('actividad')) {
                $momentRows = $momentRows->whereIn('id', $matchingActivityMomentIds);
            }
            if ($request->filled('estado')) {
                $momentRows = $momentRows->where('estado', $request->input('estado'));
            }
            if ($request->filled('desde') || $request->filled('hasta')) {
                $desde = $request->input('desde');
                $hasta = $request->input('hasta');
                $momentRows = $momentRows->filter(function ($momentRow) use ($desde, $hasta) {
                    $fecha = substr((string) ($momentRow['certificacion']->fecha_certificacion ?? ''), 0, 10);
                    return $fecha !== ''
                        && (!$desde || $fecha >= $desde)
                        && (!$hasta || $fecha <= $hasta);
                });
            }
            $momentRows = $momentRows->values();
            if (($request->filled('actividad') || $request->filled('estado') || $request->filled('desde') || $request->filled('hasta'))
                && $momentRows->isEmpty()) {
                continue;
            }
            $rows->push([
                'course' => $course,
                'momentos' => $momentRows,
                'certificaciones' => $certificacionesCurso,
                'certificacion_completa' => $certificacionCompleta,
            ]);
        }

        $stats = [
            'para_certificar' => $rows->filter(fn ($row) => $row['momentos']->contains('estado', 'Pendiente de certificar'))->count(),
            'certificadas' => $rows->filter(fn ($row) => $row['momentos']->contains('estado', 'Certificada'))->count(),
            'pendientes' => $rows->filter(fn ($row) => $row['momentos']->contains('estado', 'En progreso'))->count(),
        ];
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator(
            $rows->slice(($page - 1) * $perPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('controlecp::instructor.certificados', compact('rows', 'stats', 'moments', 'activityOptions', 'responsablesInstructor'));
    }

    public function certificar(int $fichaId, int $momentoId)
    {
        $this->authorizeInstructor();
        $course = Course::with('program')->findOrFail($fichaId);
        abort_unless(DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->where('id_ficha', $course->id)
            ->exists(), 404);
        $moment = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->where('id', $momentoId)
            ->first();
        abort_unless($moment, 404);

        $certificationId = DB::transaction(function () use ($course, $moment) {
            Course::whereKey($course->id)->lockForUpdate()->firstOrFail();
            abort_unless(DB::table('cronograma')
                ->where('id_instructor', Auth::id())
                ->where('id_ficha', $course->id)
                ->exists(), 404);

            app(CertificacionFichaService::class)
                ->validarSiguienteMomento((int) $course->id, (int) $moment->id);

            $activities = DB::table('controlecp_actividades')
                ->where('momento_id', $moment->id)
                ->whereNull('deleted_at')
                ->where('estado', '!=', 'Inactivo')
                ->orderBy('numero')
                ->get(['id', 'numero', 'nombre']);
            $finishedIds = DB::table('cronograma')
                ->where('id_ficha', $course->id)
                ->where('estado', 'Finalizada')
                ->whereIn('id_actividad', $activities->pluck('id'))
                ->distinct()
                ->pluck('id_actividad');
            $pending = $activities->reject(fn ($activity) => $finishedIds->contains($activity->id));
            if ($activities->isEmpty() || $pending->isNotEmpty()) {
                $missing = $pending->pluck('nombre')->implode(', ');
                throw ValidationException::withMessages([
                    'certificacion' => $activities->isEmpty()
                        ? 'No se puede certificar: el momento no tiene actividades activas.'
                        : 'No se puede certificar; faltan estas actividades: ' . $missing . '.',
                ]);
            }

            $id = DB::table('controlecp_certificaciones')->insertGetId([
                'course_id' => $course->id,
                'momento_id' => $moment->id,
                'responsable_id' => Auth::id(),
                'responsable_tipo' => 'instructor',
                'fecha_certificacion' => now(),
                'estado' => 'Certificada',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->notificarCertificacion($course, $moment, $id);

            return $id;
        });

        return redirect()->route('controlecp.instructor.certificados')
            ->with('success', "El momento {$moment->nombre} de la ficha {$course->code} quedó certificado.");
    }

    public function descargarConstancia(int $fichaId)
    {
        $this->authorizeInstructor();
        $course = Course::with('program')->findOrFail($fichaId);
        abort_unless(DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->where('id_ficha', $course->id)
            ->exists(), 404);

        return app(CertificacionFichaService::class)->descargarConstancia($course);
    }

    private function notificarCertificacion(Course $course, object $moment, int $certificationId): void
    {
        $apprenticePersonIds = Apprentice::where('course_id', $course->id)->pluck('person_id');
        $administrators = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.admin'))
            ->pluck('id');
        $apprentices = User::whereIn('person_id', $apprenticePersonIds)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.apprentice'))
            ->pluck('id');
        foreach ($administrators->merge($apprentices)->push((int) Auth::id())->unique() as $receiverId) {
            Notificacion::crear(
                idReceptor: (int) $receiverId,
                idCausante: (int) Auth::id(),
                titulo: 'Momento de Cultura de Paz certificado',
                mensaje: "La ficha {$course->code} completó y certificó el momento {$moment->nombre}.",
                referenciaTipo: 'certificacion',
                referenciaId: $certificationId,
                claveEvento: "certificacion-{$certificationId}-usuario-{$receiverId}"
            );
        }
    }

    private function authorizeInstructor(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }
}
