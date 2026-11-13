<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Services\CertificacionFichaService;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Course;

class AprendizCertificadoController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        abort_unless(Auth::user()->person_id, 404, 'No se encontró la ficha del aprendiz.');

        $courseIds = Apprentice::where('person_id', Auth::user()->person_id)
            ->pluck('course_id')
            ->unique()
            ->values();
        $courses = Course::with('program')
            ->whereIn('id', $courseIds)
            ->orderByDesc('id')
            ->get();
        $moments = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'orden']);
        $certifications = DB::table('controlecp_certificaciones')
            ->whereIn('course_id', $courses->modelKeys())
            ->whereIn('momento_id', $moments->pluck('id'))
            ->get(['course_id', 'momento_id', 'fecha_certificacion'])
            ->groupBy(fn ($certification) => $certification->course_id . '-' . $certification->momento_id);

        $records = $courses->map(function (Course $course) use ($moments, $certifications) {
            $courseMoments = $moments->map(function ($moment) use ($course, $certifications) {
                $certified = $certifications->get($course->id . '-' . $moment->id, collect())->isNotEmpty();

                return [
                    'nombre' => $moment->nombre,
                    'orden' => $moment->orden,
                    'certificada' => $certified,
                    'fecha' => $certifications->get($course->id . '-' . $moment->id, collect())
                        ->first()?->fecha_certificacion,
                ];
            });
            $completedCount = $courseMoments->where('certificada', true)->count();

            return [
                'course' => $course,
                'momentos' => $courseMoments,
                'completados' => $completedCount,
                'total' => $courseMoments->count(),
                'completo' => $courseMoments->count() >= 3 && $completedCount === $courseMoments->count(),
            ];
        });

        return view('controlecp::aprendiz.certificado', compact('records'));
    }

    public function descargar(int $fichaId, CertificacionFichaService $certificacionService)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        abort_unless(Auth::user()->person_id, 404, 'No se encontró la ficha del aprendiz.');
        abort_unless(
            Apprentice::where('person_id', Auth::user()->person_id)
                ->where('course_id', $fichaId)
                ->exists(),
            404
        );

        $course = Course::with('program')->findOrFail($fichaId);

        return $certificacionService->descargarConstancia($course);
    }
}
