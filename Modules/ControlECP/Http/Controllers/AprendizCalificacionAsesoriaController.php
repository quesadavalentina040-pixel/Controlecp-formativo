<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\CalificacionAsesoria;
use Modules\ControlECP\Entities\Notificacion;

class AprendizCalificacionAsesoriaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'in:Programada,Realizada'],
        ]);
        $baseQuery = Asesoria::where('id_usuario_aprendiz', Auth::id())
            ->whereIn('estado', ['Programada', 'Realizada']);
        $asesoriasQuery = (clone $baseQuery)->with(['categoria', 'instructor.person', 'calificacion']);

        if (!empty($filters['estado'])) {
            $asesoriasQuery->where('estado', $filters['estado']);
        }
        if (!empty($filters['q'])) {
            $term = trim($filters['q']);
            $asesoriasQuery->where(function ($query) use ($term) {
                $query->where('motivo', 'like', "%{$term}%")
                    ->orWhereHas('categoria', fn ($categoryQuery) => $categoryQuery->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('instructor', function ($instructorQuery) use ($term) {
                        $instructorQuery->where('nickname', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhereHas('person', function ($personQuery) use ($term) {
                                $personQuery->where('first_name', 'like', "%{$term}%")
                                    ->orWhere('first_last_name', 'like', "%{$term}%")
                                    ->orWhere('second_last_name', 'like', "%{$term}%");
                            });
                    });
            });
        }

        $totales = [
            'pendientes' => (clone $baseQuery)->where('estado', 'Realizada')->whereDoesntHave('calificacion')->count(),
            'calificadas' => (clone $baseQuery)->whereHas('calificacion')->count(),
            'por_finalizar' => (clone $baseQuery)->where('estado', 'Programada')->count(),
        ];
        $asesorias = $asesoriasQuery->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->paginate(10)->withQueryString();

        return view('controlecp::aprendiz.calificacion_asesoria', compact('asesorias', 'totales', 'filters'));
    }

    public function store(Request $request, int $asesoriaId)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);

        $asesoria = Asesoria::where('id_usuario_aprendiz', Auth::id())
            ->where('estado', 'Realizada')
            ->findOrFail($asesoriaId);

        if ($asesoria->calificacion()->exists()) {
            return redirect()->route('controlecp.aprendiz.calificacion_asesoria')
                ->with('info', 'Ya calificaste esta asesoría.');
        }

        $datos = $request->validate([
            'estrellas' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'min:10', 'max:200'],
        ], [
            'estrellas.required' => 'Selecciona de 1 a 5 estrellas antes de enviar.',
            'estrellas.between' => 'La calificación debe estar entre 1 y 5 estrellas.',
        ]);

        try {
            DB::transaction(function () use ($asesoria, $datos) {
                Asesoria::whereKey($asesoria->id_asesoria)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($asesoria->calificacion()->exists()) {
                    throw ValidationException::withMessages([
                        'estrellas' => 'Ya calificaste esta asesoría.',
                    ]);
                }

                CalificacionAsesoria::create([
                    'asesoria_id' => $asesoria->id_asesoria,
                    'user_id' => Auth::id(),
                    'estrellas' => $datos['estrellas'],
                    'comentario' => isset($datos['comentario']) ? trim($datos['comentario']) : null,
                ]);

                if ($asesoria->id_usuario_instructor) {
                    Notificacion::crear(
                        idReceptor: (int) $asesoria->id_usuario_instructor,
                        idCausante: (int) Auth::id(),
                        titulo: 'Nueva calificación de asesoría',
                        mensaje: 'El aprendiz calificó tu asesoría con ' . $datos['estrellas'] . ' estrellas.',
                        referenciaTipo: 'asesoria',
                        referenciaId: (int) $asesoria->id_asesoria,
                        claveEvento: 'asesoria_' . $asesoria->id_asesoria . '_calificacion'
                    );
                }
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            $mensaje = strtolower($exception->getMessage());
            $violacionUnica = in_array((string) ($exception->errorInfo[0] ?? $exception->getCode()), ['23000', '23505'], true)
                && (
                    str_contains($mensaje, 'controlecp_calificaciones_asesoria_asesoria_id_unique')
                    || (
                        str_contains($mensaje, 'unique constraint failed')
                        && str_contains($mensaje, 'controlecp_calificaciones_asesoria.asesoria_id')
                    )
                );

            if (!$violacionUnica) {
                throw $exception;
            }

            return redirect()->route('controlecp.aprendiz.calificacion_asesoria')
                ->with('info', 'Ya calificaste esta asesoría.');
        }

        return redirect()->route('controlecp.aprendiz.calificacion_asesoria')
            ->with('success', 'Tu calificación fue guardada. Gracias por compartir tu opinión.');
    }
}
