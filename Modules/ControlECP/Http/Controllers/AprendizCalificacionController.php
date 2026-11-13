<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\CalificacionEcp;
use Modules\ControlECP\Entities\Cronograma;
use Modules\SICA\Entities\Apprentice;

class AprendizCalificacionController extends Controller
{
    private function aprendiz(): Apprentice
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        abort_unless(Auth::user()->person_id, 404, 'No se encontró la ficha del aprendiz.');

        return Apprentice::where('person_id', Auth::user()->person_id)->firstOrFail();
    }

    private function actividadAsistida(int $cronogramaId, Apprentice $aprendiz): Cronograma
    {
        $cronograma = Cronograma::with(['actividad.momento'])
            ->where('id_ficha', $aprendiz->course_id)
            ->where('estado', 'Finalizada')
            ->findOrFail($cronogramaId);

        $asistio = DB::table('controlecp_asistencia_detalles as detalle')
            ->join('controlecp_asistencias as asistencia', 'asistencia.id', '=', 'detalle.asistencia_id')
            ->where('asistencia.cronograma_id', $cronograma->id_cronograma)
            ->where('asistencia.estado', 'Registrada')
            ->where('detalle.aprendiz_id', $aprendiz->id)
            ->where('detalle.presente', true)
            ->exists();

        abort_unless($asistio, 403, 'Solo puedes calificar actividades finalizadas a las que asististe.');

        return $cronograma;
    }

    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'in:pendiente,calificada'],
        ]);

        $aprendiz = Auth::user()->person_id
            ? Apprentice::where('person_id', Auth::user()->person_id)
                ->whereHas('course', fn ($query) => $query->where('status', 'Activo'))
                ->first()
            : null;

        if (!$aprendiz) {
            return view('controlecp::aprendiz.calificacion_actividad', [
                'cronogramas' => new LengthAwarePaginator(
                    [],
                    0,
                    10,
                    LengthAwarePaginator::resolveCurrentPage(),
                    ['path' => $request->url(), 'query' => $request->query()]
                ),
                'sinFichaActiva' => true,
                'totales' => ['total' => 0, 'pendientes' => 0, 'calificadas' => 0],
                'filters' => $filters,
            ]);
        }

        $cronogramas = Cronograma::with(['actividad.momento'])
            ->where('id_ficha', $aprendiz->course_id)
            ->where('estado', 'Finalizada')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $asistencias = DB::table('controlecp_asistencia_detalles as detalle')
            ->join('controlecp_asistencias as asistencia', 'asistencia.id', '=', 'detalle.asistencia_id')
            ->whereIn('asistencia.cronograma_id', $cronogramas->pluck('id_cronograma'))
            ->where('asistencia.estado', 'Registrada')
            ->where('detalle.aprendiz_id', $aprendiz->id)
            ->where('detalle.presente', true)
            ->pluck('asistencia.cronograma_id')
            ->flip();

        $cronogramas = $cronogramas
            ->filter(fn (Cronograma $cronograma) => $asistencias->has($cronograma->id_cronograma))
            ->unique('id_actividad')
            ->values();

        $calificaciones = CalificacionEcp::where('course_id', $aprendiz->course_id)
            ->where('user_id', Auth::id())
            ->whereIn('actividad_id', $cronogramas->pluck('id_actividad')->unique())
            ->get()
            ->keyBy('actividad_id');

        $cronogramas = $cronogramas->map(function (Cronograma $cronograma) use ($calificaciones) {
            $cronograma->calificacionAprendiz = $calificaciones->get($cronograma->id_actividad);

            return $cronograma;
        });
        $totales = [
            'total' => $cronogramas->count(),
            'pendientes' => $cronogramas->filter(fn (Cronograma $cronograma) => !$cronograma->calificacionAprendiz)->count(),
            'calificadas' => $cronogramas->filter(fn (Cronograma $cronograma) => (bool) $cronograma->calificacionAprendiz)->count(),
        ];
        $filtrados = $cronogramas
            ->when(!empty($filters['estado']), fn ($items) => $items->filter(
                fn (Cronograma $cronograma) => $filters['estado'] === 'calificada'
                    ? (bool) $cronograma->calificacionAprendiz
                    : !$cronograma->calificacionAprendiz
            ))
            ->when(!empty($filters['q']), function ($items) use ($filters) {
                $termino = mb_strtolower(trim($filters['q']));

                return $items->filter(function (Cronograma $cronograma) use ($termino) {
                    $texto = implode(' ', [
                        $cronograma->actividad?->nombre ?? '',
                        $cronograma->actividad?->numero ?? '',
                        $cronograma->actividad?->momento?->nombre ?? '',
                    ]);

                    return mb_stripos($texto, $termino) !== false;
                });
            })
            ->values();
        $porPagina = 10;
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $cronogramas = new LengthAwarePaginator(
            $filtrados->forPage($pagina, $porPagina)->values(),
            $filtrados->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('controlecp::aprendiz.calificacion_actividad', [
            'cronogramas' => $cronogramas,
            'sinFichaActiva' => false,
            'totales' => $totales,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request, int $cronogramaId)
    {
        $aprendiz = $this->aprendiz();
        $cronograma = $this->actividadAsistida($cronogramaId, $aprendiz);

        if (CalificacionEcp::where('actividad_id', $cronograma->id_actividad)
            ->where('course_id', $aprendiz->course_id)
            ->where('user_id', Auth::id())
            ->exists()) {
            return redirect()->route('controlecp.aprendiz.calificacion_actividad')
                ->with('info', 'Ya calificaste esta actividad.');
        }

        $datos = $request->validate([
            'estrellas' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'min:10', 'max:200'],
        ], [
            'estrellas.required' => 'Selecciona de 1 a 5 estrellas antes de enviar.',
            'estrellas.between' => 'La calificación debe estar entre 1 y 5 estrellas.',
        ]);

        try {
            DB::transaction(function () use ($cronograma, $aprendiz, $datos) {
                Cronograma::whereKey($cronograma->id_cronograma)
                    ->lockForUpdate()
                    ->firstOrFail();

                $yaCalificada = CalificacionEcp::where('actividad_id', $cronograma->id_actividad)
                    ->where('course_id', $aprendiz->course_id)
                    ->where('user_id', Auth::id())
                    ->exists();

                if ($yaCalificada) {
                    throw ValidationException::withMessages([
                        'estrellas' => 'Ya calificaste esta actividad.',
                    ]);
                }

                CalificacionEcp::create([
                    'actividad_id' => $cronograma->id_actividad,
                    'course_id' => $aprendiz->course_id,
                    'user_id' => Auth::id(),
                    'estrellas' => $datos['estrellas'],
                    'comentario' => isset($datos['comentario']) ? trim($datos['comentario']) : null,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            $mensaje = strtolower($exception->getMessage());
            $violacionUnica = in_array((string) ($exception->errorInfo[0] ?? $exception->getCode()), ['23000', '23505'], true)
                && (
                    str_contains($mensaje, 'uq_calificacion_actividad_ficha_aprendiz')
                    || str_contains($mensaje, 'controlecp_calificaciones_actividad_id_course_id_user_id_unique')
                    || (
                        str_contains($mensaje, 'unique constraint failed')
                        && str_contains($mensaje, 'controlecp_calificaciones.actividad_id')
                        && str_contains($mensaje, 'controlecp_calificaciones.course_id')
                        && str_contains($mensaje, 'controlecp_calificaciones.user_id')
                    )
                );

            if (!$violacionUnica) {
                throw $exception;
            }

            return redirect()->route('controlecp.aprendiz.calificacion_actividad')
                ->with('info', 'Ya calificaste esta actividad.');
        }

        return redirect()->route('controlecp.aprendiz.calificacion_actividad')
            ->with('success', 'Tu calificación fue guardada. Gracias por compartir tu opinión.');
    }
}
