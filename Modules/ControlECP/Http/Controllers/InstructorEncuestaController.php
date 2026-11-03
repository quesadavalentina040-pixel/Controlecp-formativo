<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\OpcionPregunta;
use Modules\ControlECP\Entities\Pregunta;

class InstructorEncuestaController extends Controller
{
    private function user()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('controlecp.instructor'), 403);
        return $user;
    }

    public function index()
    {
        $user = $this->user();
        $encuestas = Encuesta::with(['preguntas', 'actividades'])
            ->withCount(['preguntas', 'actividades'])
            ->where('id_usuario', $user->id)
            ->orderByDesc('fecha_creacion')->get();
        return view('controlecp::instructor.encuestas', compact('encuestas'));
    }

    public function store(Request $request)
    {
        $user = $this->user();
        $data = $request->validate(['nombre' => ['required', 'string', 'max:255'], 'descripcion' => ['nullable', 'string', 'max:2000']]);
        Encuesta::create(['id_usuario' => $user->id, 'nombre' => trim($data['nombre']), 'descripcion' => trim($data['descripcion'] ?? ''), 'estado' => 'Borrador', 'fecha_creacion' => now()]);
        return back()->with('success', 'Encuesta creada en estado Borrador.');
    }

    public function update(Request $request, int $id)
    {
        $encuesta = $this->propia($id);
        if ($encuesta->tieneRespuestas()) throw ValidationException::withMessages(['encuesta' => 'No puedes modificar una encuesta que ya tiene respuestas.']);
        $data = $request->validate(['nombre' => ['required', 'string', 'max:255'], 'descripcion' => ['nullable', 'string', 'max:2000']]);
        $encuesta->update(['nombre' => trim($data['nombre']), 'descripcion' => trim($data['descripcion'] ?? '')]);
        return back()->with('success', 'Encuesta actualizada.');
    }

    public function agregarPregunta(Request $request, int $id)
    {
        $encuesta = $this->propia($id);
        if ($encuesta->tieneRespuestas()) throw ValidationException::withMessages(['pregunta' => 'No puedes modificar preguntas con respuestas registradas.']);
        $data = $request->validate(['texto' => ['required', 'string', 'max:1000'], 'tipo' => ['required', 'in:Opcion_multiple,Escala,Texto'], 'opciones' => ['nullable', 'array'], 'opciones.*' => ['nullable', 'string', 'max:255']]);
        $opciones = array_values(array_filter(array_map('trim', $data['opciones'] ?? [])));
        if ($data['tipo'] === 'Opcion_multiple' && count($opciones) < 2) throw ValidationException::withMessages(['opciones' => 'Una pregunta de opción múltiple requiere al menos dos opciones.']);
        DB::transaction(function () use ($encuesta, $data, $opciones) {
            $pregunta = $encuesta->preguntas()->create(['texto' => trim($data['texto']), 'tipo' => $data['tipo'], 'obligatoria' => true, 'orden' => ($encuesta->preguntas()->max('orden') ?? 0) + 1]);
            foreach ($opciones as $index => $opcion) OpcionPregunta::create(['id_pregunta' => $pregunta->id_pregunta, 'texto' => $opcion, 'orden' => $index + 1]);
        });
        return back()->with('success', 'Pregunta agregada.');
    }

    public function activar(int $id)
    {
        $encuesta = $this->propia($id);
        if (!$encuesta->preguntas()->exists()) throw ValidationException::withMessages(['encuesta' => 'Agrega al menos una pregunta antes de activar.']);
        $encuesta->update(['estado' => 'Activa']);
        return back()->with('success', 'Encuesta activada.');
    }

    public function desactivar(int $id)
    {
        $encuesta = $this->propia($id);
        if ($encuesta->tieneProgramacionesPendientes()) throw ValidationException::withMessages(['encuesta' => 'No puedes desactivar una encuesta asociada a actividades pendientes.']);
        $encuesta->update(['estado' => 'Inactiva']);
        return back()->with('success', 'Encuesta desactivada.');
    }

    private function propia(int $id): Encuesta
    {
        return Encuesta::where('id_encuesta', $id)->where('id_usuario', Auth::id())->firstOrFail();
    }
}
