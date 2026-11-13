<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\EncuestaRespuesta;
use Modules\ControlECP\Entities\EncuestaRespuestaDetalle;
use Modules\SICA\Entities\Apprentice;

class AprendizEncuestaController extends Controller
{
    private function aprendiz(): Apprentice
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        abort_unless(Auth::user()->person_id, 404, 'No se encontró la ficha del aprendiz.');

        return Apprentice::where('person_id', Auth::user()->person_id)->firstOrFail();
    }

    private function actividadHabilitada(int $cronogramaId): array
    {
        $aprendiz = $this->aprendiz();
        $cronograma = Cronograma::with(['actividad.momento', 'ficha'])
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

        abort_unless($asistio, 403, 'La encuesta solo está disponible para aprendices que asistieron a la actividad.');

        $encuestaId = $cronograma->actividad?->id_encuesta;
        $encuesta = $encuestaId
            ? Encuesta::with('preguntas.opciones')->find($encuestaId)
            : null;

        abort_unless($encuesta, 404, 'No hay una encuesta asociada a esta actividad.');

        return [$aprendiz, $cronograma, $encuesta];
    }

    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        $filters = $request->validate([
            'encuesta' => ['nullable', 'integer', 'exists:encuestas,id_encuesta'],
        ]);
        $encuestaId = $filters['encuesta'] ?? null;

        $aprendiz = Auth::user()->person_id
            ? Apprentice::where('person_id', Auth::user()->person_id)
                ->whereHas('course', fn ($query) => $query->where('status', 'Activo'))
                ->first()
            : null;

        if (!$aprendiz) {
            return view('controlecp::aprendiz.encuesta', [
                'encuestas' => collect(),
                'sinFichaActiva' => true,
            ]);
        }

        $cronogramas = Cronograma::with(['actividad.momento', 'ficha'])
            ->where('id_ficha', $aprendiz->course_id)
            ->where('estado', 'Finalizada')
            ->whereHas('actividad', function ($query) use ($encuestaId) {
                $query->whereNotNull('id_encuesta')
                    ->when($encuestaId, fn ($activityQuery) => $activityQuery->where('id_encuesta', $encuestaId));
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $encuestas = Encuesta::with('preguntas.opciones')
            ->whereIn('id_encuesta', $cronogramas->pluck('actividad.id_encuesta')->filter()->unique())
            ->get()
            ->keyBy('id_encuesta');

        $respuestas = EncuestaRespuesta::with('detalles.opcion')
            ->where('id_usuario', Auth::id())
            ->whereIn('id_cronograma', $cronogramas->pluck('id_cronograma'))
            ->get()
            ->keyBy('id_cronograma');

        $asistencias = DB::table('controlecp_asistencia_detalles as detalle')
            ->join('controlecp_asistencias as asistencia', 'asistencia.id', '=', 'detalle.asistencia_id')
            ->whereIn('asistencia.cronograma_id', $cronogramas->pluck('id_cronograma'))
            ->where('asistencia.estado', 'Registrada')
            ->where('detalle.aprendiz_id', $aprendiz->id)
            ->where('detalle.presente', true)
            ->pluck('asistencia.cronograma_id')
            ->flip();

        $encuestas = $cronogramas->filter(function ($cronograma) use ($encuestas, $respuestas) {
            $encuesta = $encuestas->get($cronograma->actividad?->id_encuesta);
            return $encuesta && ($encuesta->estado === 'Activa' || $respuestas->has($cronograma->id_cronograma));
        })->map(function ($cronograma) use ($encuestas, $respuestas, $asistencias) {
            $cronograma->encuesta = $encuestas->get($cronograma->actividad->id_encuesta);
            $cronograma->respuestaAprendiz = $respuestas->get($cronograma->id_cronograma);
            $cronograma->respondida = (bool) $cronograma->respuestaAprendiz;
            $cronograma->asistio = $asistencias->has($cronograma->id_cronograma);

            return $cronograma;
        })->values();

        return view('controlecp::aprendiz.encuesta', [
            'encuestas' => $encuestas,
            'sinFichaActiva' => false,
        ]);
    }

    public function show(int $cronogramaId)
    {
        [$aprendiz, $cronograma, $encuesta] = $this->actividadHabilitada($cronogramaId);
        $respuesta = EncuestaRespuesta::with('detalles.opcion')
            ->where('id_cronograma', $cronograma->id_cronograma)
            ->where('id_usuario', Auth::id())
            ->first();

        abort_unless($encuesta->estado === 'Activa' || $respuesta, 404, 'La encuesta ya no está disponible.');

        return view('controlecp::aprendiz.encuesta_responder', compact('aprendiz', 'cronograma', 'encuesta', 'respuesta'));
    }

    public function showCompartida(int $encuestaId)
    {
        $aprendiz = $this->aprendiz();
        $encuesta = Encuesta::findOrFail($encuestaId);
        abort_unless($encuesta->estado === 'Activa', 404, 'La encuesta ya no está disponible.');

        $cronogramas = Cronograma::query()
            ->where('id_ficha', $aprendiz->course_id)
            ->where('estado', 'Finalizada')
            ->whereHas('actividad', fn ($query) => $query->where('id_encuesta', $encuesta->id_encuesta))
            ->whereExists(function ($query) use ($aprendiz) {
                $query->selectRaw('1')
                    ->from('controlecp_asistencia_detalles as detalle')
                    ->join('controlecp_asistencias as asistencia', 'asistencia.id', '=', 'detalle.asistencia_id')
                    ->whereColumn('asistencia.cronograma_id', 'cronograma.id_cronograma')
                    ->where('asistencia.estado', 'Registrada')
                    ->where('detalle.aprendiz_id', $aprendiz->id)
                    ->where('detalle.presente', true);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get(['id_cronograma']);

        if ($cronogramas->isEmpty()) {
            return redirect()->route('controlecp.aprendiz.encuesta')
                ->with('error', 'Esta encuesta no está disponible para tu ficha porque no tienes asistencia registrada en una actividad finalizada asociada.');
        }

        $respuestasExistentes = EncuestaRespuesta::where('id_usuario', Auth::id())
            ->whereIn('id_cronograma', $cronogramas->pluck('id_cronograma'))
            ->pluck('id_cronograma')
            ->flip();
        $cronogramaId = $cronogramas->first(
            fn ($cronograma) => !$respuestasExistentes->has($cronograma->id_cronograma)
        )?->id_cronograma ?? $cronogramas->first()->id_cronograma;

        [$aprendiz, $cronograma, $encuesta] = $this->actividadHabilitada((int) $cronogramaId);
        $respuesta = EncuestaRespuesta::with('detalles.opcion')
            ->where('id_cronograma', $cronograma->id_cronograma)
            ->where('id_usuario', Auth::id())
            ->first();

        return view('controlecp::aprendiz.encuesta_responder', compact('aprendiz', 'cronograma', 'encuesta', 'respuesta'));
    }

    public function store(Request $request, int $cronogramaId)
    {
        [$aprendiz, $cronograma, $encuesta] = $this->actividadHabilitada($cronogramaId);
        abort_unless($encuesta->estado === 'Activa', 404, 'La encuesta ya no está disponible para responder.');

        if (EncuestaRespuesta::where('id_cronograma', $cronograma->id_cronograma)
            ->where('id_usuario', Auth::id())->exists()) {
            return redirect()->route('controlecp.aprendiz.encuesta')
                ->withInput(['cronograma_id' => $cronograma->id_cronograma])
                ->with('info', 'Ya respondiste la encuesta de esta actividad.');
        }

        $rules = [];
        foreach ($encuesta->preguntas as $pregunta) {
            $key = 'respuestas.' . $pregunta->id_pregunta;
            $presence = $pregunta->obligatoria ? 'required' : 'nullable';

            if ($pregunta->tipo === 'Opcion_multiple') {
                $rules[$key] = [
                    $presence,
                    'integer',
                    Rule::exists('opciones_pregunta', 'id')->where(
                        fn ($query) => $query->where('id_pregunta', $pregunta->id_pregunta)
                    ),
                ];
            } elseif ($pregunta->tipo === 'Escala') {
                $rules[$key] = [$presence, 'integer', 'between:1,5'];
            } else {
                $rules[$key] = [$presence, 'string', 'min:10', 'max:200'];
            }
        }

        $validated = $request->validate($rules, [
            'required' => 'Responde todas las preguntas obligatorias.',
            'exists' => 'Selecciona una opción válida para cada pregunta.',
            'between' => 'La calificación debe estar entre 1 y 5.',
        ]);

        DB::transaction(function () use ($validated, $encuesta, $cronograma) {
            $lockedCronograma = Cronograma::whereKey($cronograma->id_cronograma)
                ->lockForUpdate()
                ->firstOrFail();
            $alreadyAnswered = EncuestaRespuesta::where('id_cronograma', $lockedCronograma->id_cronograma)
                ->where('id_usuario', Auth::id())
                ->exists();

            if ($alreadyAnswered) {
                throw ValidationException::withMessages([
                    'respuestas' => 'Esta encuesta ya fue respondida para la actividad.',
                ]);
            }

            $respuesta = EncuestaRespuesta::create([
                'id_cronograma' => $lockedCronograma->id_cronograma,
                'id_usuario' => Auth::id(),
                'fecha' => now(),
            ]);

            foreach ($encuesta->preguntas as $pregunta) {
                $valor = $validated['respuestas'][$pregunta->id_pregunta] ?? null;
                if ($valor === null || $valor === '') {
                    continue;
                }

                EncuestaRespuestaDetalle::create([
                    'id_encuesta_respuesta' => $respuesta->id_encuesta_respuesta,
                    'id_pregunta' => $pregunta->id_pregunta,
                    'id_opcion_pregunta' => $pregunta->tipo === 'Opcion_multiple' ? (int) $valor : null,
                    'valor' => $pregunta->tipo === 'Opcion_multiple' ? null : (string) $valor,
                ]);
            }
        });

        return redirect()->route('controlecp.aprendiz.encuesta')
            ->with('success', 'Tus respuestas fueron guardadas. Gracias por compartir tu opinión.');
    }
}
