<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Entities\Pqrs;
use Modules\ControlECP\Entities\PqrsHistorial;
use Modules\ControlECP\Entities\PqrsRespuesta;

class InstructorPqrsController extends Controller
{
    private function pqrsAsignada(int $id): Pqrs
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);

        return Pqrs::where('id_usuario_instructor_destino', Auth::id())->findOrFail($id);
    }

    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'tipo' => ['nullable', 'integer', 'exists:tipos_pqrs,id_tipo_pqrs'],
            'estado' => ['nullable', Rule::in(['Pendiente', 'En trámite', 'Resuelto'])],
        ]);

        $query = Pqrs::with([
            'tipoPqrs',
            'aprendiz',
            'respuestas.usuario',
            'historial.usuario',
        ])
            ->where('id_usuario_instructor_destino', Auth::id());
        if ($request->filled('buscar')) {
            $termino = trim($request->input('buscar'));
            $query->where(function ($pqrQuery) use ($termino) {
                $pqrQuery->where('radicado', 'like', "%{$termino}%")
                    ->orWhere('asunto', 'like', "%{$termino}%");
            });
        }
        if ($request->filled('tipo')) {
            $query->where('id_tipo_pqrs', $request->input('tipo'));
        }
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'Resuelto') {
                $query->whereIn('estado', ['Resuelto', 'Resuelta']);
            } else {
                $query->where('estado', $request->input('estado'));
            }
        }

        $pqrs = $query->orderByDesc('fecha_radicacion')->paginate(10)->withQueryString();
        $base = Pqrs::where('id_usuario_instructor_destino', Auth::id());
        $totales = [
            'radicadas' => (clone $base)->count(),
            'pendientes' => (clone $base)->where('estado', 'Pendiente')->count(),
            'en_tramite' => (clone $base)->where('estado', 'En trámite')->count(),
            'resueltas' => (clone $base)->whereIn('estado', ['Resuelto', 'Resuelta', 'Cerrado', 'Cerrada'])->count(),
        ];
        $tipos = DB::table('tipos_pqrs')->orderBy('nombre')->get(['id_tipo_pqrs', 'nombre']);

        return view('controlecp::instructor.pqr', compact('pqrs', 'totales', 'tipos'));
    }

    public function show(int $id)
    {
        $pqr = $this->pqrsAsignada($id)->load([
            'tipoPqrs',
            'aprendiz',
            'respuestas.usuario',
            'historial.usuario',
        ]);
        $pqr->setRelation('historial', $pqr->historial->sortBy('fecha')->values());
        $pqr->setRelation('respuestas', $pqr->respuestas->sortBy('fecha')->values());

        return view('controlecp::instructor.pqr_show', compact('pqr'));
    }

    public function cambiarEstado(Request $request, int $id)
    {
        $pqr = $this->pqrsAsignada($id);
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['Pendiente', 'En trámite', 'Resuelto'])],
            'observacion' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $estadoAnterior = $pqr->estado;

        DB::transaction(function () use ($pqr, $datos, $estadoAnterior) {
            $pqr->update(['estado' => $datos['estado']]);
            PqrsHistorial::create([
                'id_pqrs' => $pqr->id,
                'id_usuario' => Auth::id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $datos['estado'],
                'observacion' => trim($datos['observacion']),
                'fecha' => now(),
            ]);
            Notificacion::crear(
                idReceptor: (int) $pqr->id_usuario_aprendiz,
                idCausante: (int) Auth::id(),
                titulo: 'Actualización de tu PQRS',
                mensaje: "La solicitud {$pqr->radicado} cambió a estado '{$datos['estado']}'.",
                referenciaTipo: 'pqr',
                referenciaId: (int) $pqr->id,
                claveEvento: 'pqr-estado-' . $pqr->id . '-' . now()->timestamp
            );
        });

        return back()->with('success', "Estado actualizado para {$pqr->radicado}; se notificó al aprendiz.");
    }

    public function responder(Request $request, int $id)
    {
        $pqr = $this->pqrsAsignada($id);
        $datos = $request->validate([
            'respuesta' => ['required', 'string', 'min:10', 'max:200'],
        ], [
            'respuesta.required' => 'Escribe una respuesta antes de enviarla.',
        ]);
        $respuesta = trim($datos['respuesta']);
        if ($respuesta === '') {
            return back()->withInput()->withErrors(['respuesta' => 'La respuesta no puede estar vacía.']);
        }

        DB::transaction(function () use ($pqr, $respuesta) {
            PqrsRespuesta::create([
                'id_pqrs' => $pqr->id,
                'id_usuario' => Auth::id(),
                'respuesta' => $respuesta,
                'fecha' => now(),
            ]);

            $estado = $pqr->estado;
            PqrsHistorial::create([
                'id_pqrs' => $pqr->id,
                'id_usuario' => Auth::id(),
                'estado_anterior' => $estado,
                'estado_nuevo' => $estado,
                'observacion' => 'Respuesta enviada al aprendiz.',
                'fecha' => now(),
            ]);

            Notificacion::crear(
                idReceptor: (int) $pqr->id_usuario_aprendiz,
                idCausante: (int) Auth::id(),
                titulo: 'Respuesta a tu PQRS',
                mensaje: "El instructor respondió la solicitud {$pqr->radicado}.",
                referenciaTipo: 'pqr',
                referenciaId: (int) $pqr->id,
                claveEvento: 'pqr-respuesta-' . $pqr->id . '-' . now()->timestamp
            );
        });

        return back()->with('success', "Respuesta enviada al aprendiz para {$pqr->radicado}.");
    }
}
