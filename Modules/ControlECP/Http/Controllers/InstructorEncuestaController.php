<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\Actividad;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Entities\OpcionPregunta;
use Modules\ControlECP\Entities\Pregunta;

class InstructorEncuestaController extends Controller
{
    private function encuestaPropia(int $id): Encuesta
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);

        return Encuesta::where('id_usuario', Auth::id())->findOrFail($id);
    }

    private function encuestaEditable(int $id): Encuesta
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);

        $encuesta = Encuesta::with('usuario.roles')->findOrFail($id);
        $esPropia = (int) $encuesta->id_usuario === (int) Auth::id();
        $esAdministrativa = $encuesta->usuario?->hasRole('controlecp.admin') ?? false;

        abort_unless(
            $esPropia || ($esAdministrativa && $encuesta->estado !== 'Inactiva'),
            403
        );

        return $encuesta;
    }

    private function notificarCambioEncuesta(Encuesta $encuesta, string $detalle): void
    {
        $administrador = $encuesta->usuario;
        if (!$administrador?->hasRole('controlecp.admin')) {
            return;
        }

        $instructor = Auth::user();
        $nombreInstructor = $instructor?->nickname ?: $instructor?->full_name ?: 'Un instructor';

        Notificacion::crear(
            idReceptor: (int) $administrador->id,
            idCausante: (int) Auth::id(),
            titulo: 'Cambio en una encuesta del programa',
            mensaje: "{$nombreInstructor} {$detalle} en la encuesta \"{$encuesta->nombre}\".",
            referenciaTipo: 'encuesta',
            referenciaId: (int) $encuesta->id_encuesta,
            claveEvento: 'encuesta-instructor-' . $encuesta->id_encuesta . '-' . Str::uuid()
        );
    }

    public function index(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', Rule::in(['Borrador', 'Activa', 'Inactiva'])],
        ]);

        $query = Encuesta::with('actividades')
            ->withCount(['preguntas', 'actividades'])
            ->where('id_usuario', Auth::id());

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

        return redirect()->route('controlecp.instructor.repositorio.encuestas', $request->query());
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'min:10', 'max:200'],
        ]);

        $encuesta = Encuesta::create([
            'id_usuario' => Auth::id(),
            'nombre' => trim($datos['nombre']),
            'descripcion' => trim($datos['descripcion'] ?? ''),
            'estado' => 'Borrador',
            'fecha_creacion' => now(),
        ]);

        return redirect()->route('controlecp.instructor.repositorio.encuestas')
            ->with('success', 'Encuesta creada como borrador. Agrega al menos una pregunta para poder activarla.')
            ->with('open_edit_modal', $encuesta->id_encuesta);
    }

    public function show(int $id)
    {
        $encuesta = $this->encuestaEditable($id)->load('preguntas.opciones', 'actividades');
        return view('controlecp::instructor.encuesta_show', [
            'encuesta' => $encuesta,
            'tieneRespuestas' => $encuesta->tieneRespuestas(),
            'esPropietario' => (int) $encuesta->id_usuario === (int) Auth::id(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $encuesta = $this->encuestaEditable($id);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'min:10', 'max:200'],
            'actividades_ids' => ['nullable', 'array'],
            'actividades_ids.*' => ['integer', 'distinct', 'exists:controlecp_actividades,id'],
        ]);

        $actividadesProgramadasIds = DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->whereNotNull('id_actividad')
            ->pluck('id_actividad')
            ->map(fn ($activityId) => (int) $activityId);
        $actividadesInstructorIds = Actividad::withTrashed()
            ->whereIn('id', $actividadesProgramadasIds)
            ->where(function ($query) use ($encuesta) {
                $query->whereNull('id_encuesta')
                    ->orWhere('id_encuesta', $encuesta->id_encuesta);
            })
            ->pluck('id')
            ->map(fn ($activityId) => (int) $activityId);
        $actividadesYaAsociadasIds = Actividad::withTrashed()
            ->where('id_encuesta', $encuesta->id_encuesta)
            ->pluck('id')
            ->map(fn ($activityId) => (int) $activityId);
        $actividadesPermitidas = $actividadesInstructorIds
            ->merge($actividadesYaAsociadasIds)
            ->unique()
            ->values();
        $nuevasActividadesIds = collect($datos['actividades_ids'] ?? [])
            ->map(fn ($activityId) => (int) $activityId)
            ->unique()
            ->values();

        abort_unless($nuevasActividadesIds->diff($actividadesPermitidas)->isEmpty(), 403);

        $nombreAnterior = $encuesta->nombre;
        $descripcionAnterior = $encuesta->descripcion;
        $actividadesAnteriores = $actividadesYaAsociadasIds->sort()->values()->all();
        $actividadesNuevas = $nuevasActividadesIds->sort()->values()->all();
        $detalleCambios = [];
        if ($nombreAnterior !== trim($datos['nombre'])) {
            $detalleCambios[] = 'cambió el nombre de "' . $nombreAnterior . '" a "' . trim($datos['nombre']) . '"';
        }
        if ($descripcionAnterior !== trim($datos['descripcion'] ?? '')) {
            $detalleCambios[] = 'modificó la descripción';
        }
        if ($actividadesAnteriores !== $actividadesNuevas) {
            $detalleCambios[] = 'actualizó las actividades asociadas';
        }

        DB::transaction(function () use ($encuesta, $datos, $actividadesInstructorIds, $nuevasActividadesIds, $detalleCambios) {
            $encuesta->update([
                'nombre' => trim($datos['nombre']),
                'descripcion' => trim($datos['descripcion'] ?? ''),
            ]);

            Actividad::withTrashed()
                ->where('id_encuesta', $encuesta->id_encuesta)
                ->whereIn('id', $actividadesInstructorIds)
                ->whereNotIn('id', $nuevasActividadesIds)
                ->update(['id_encuesta' => null, 'encuesta_id' => null]);

            if ($nuevasActividadesIds->isNotEmpty()) {
                Actividad::withTrashed()
                    ->whereIn('id', $nuevasActividadesIds)
                    ->whereIn('id', $actividadesInstructorIds)
                    ->update([
                        'id_encuesta' => $encuesta->id_encuesta,
                        'encuesta_id' => $encuesta->id_encuesta,
                    ]);
            }

            if ($detalleCambios !== []) {
                $this->notificarCambioEncuesta($encuesta, implode(' y ', $detalleCambios));
            }
        });

        return back()->with('success', 'Los datos de la encuesta se actualizaron.')
            ->with('open_edit_modal', $encuesta->id_encuesta);
    }

    public function agregarPregunta(Request $request, int $id)
    {
        $encuesta = $this->encuestaEditable($id);
        if ($encuesta->tieneRespuestas()) {
            return back()->with('error', 'La encuesta ya tiene respuestas; no se pueden modificar sus preguntas.')
                ->with('open_edit_modal', $encuesta->id_encuesta);
        }

        $datos = $request->validate([
            'texto' => ['required', 'string', 'min:10', 'max:200'],
            'tipo' => ['required', Rule::in(['Opcion_multiple', 'Escala', 'Texto'])],
            'opciones_texto' => ['nullable', 'string', 'max:2000'],
            'obligatoria' => ['nullable', 'boolean'],
        ]);
        $opciones = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', $datos['opciones_texto'] ?? '') ?: []),
            fn ($opcion) => $opcion !== ''
        ));
        if ($datos['tipo'] === 'Opcion_multiple' && count($opciones) < 2) {
            return back()->withInput()->withErrors([
                'opciones' => 'Las preguntas de opción múltiple requieren al menos dos opciones.',
            ])->with('open_edit_modal', $encuesta->id_encuesta);
        }

        DB::transaction(function () use ($encuesta, $datos, $opciones, $request) {
            $pregunta = Pregunta::create([
                'id_encuesta' => $encuesta->id_encuesta,
                'texto' => trim($datos['texto']),
                'tipo' => $datos['tipo'],
                'obligatoria' => $request->boolean('obligatoria'),
                'orden' => ($encuesta->preguntas()->max('orden') ?? 0) + 1,
            ]);
            foreach ($opciones as $index => $texto) {
                OpcionPregunta::create([
                    'id_pregunta' => $pregunta->id_pregunta,
                    'texto' => $texto,
                    'orden' => $index + 1,
                ]);
            }

            $this->notificarCambioEncuesta(
                $encuesta,
                "añadió la pregunta \"{$pregunta->texto}\""
            );
        });

        return back()->with('success', 'Pregunta agregada correctamente.')
            ->with('open_edit_modal', $encuesta->id_encuesta);
    }

    public function actualizarPregunta(Request $request, int $id, int $preguntaId)
    {
        $encuesta = $this->encuestaEditable($id);
        if ($encuesta->tieneRespuestas()) {
            return back()->with('error', 'La encuesta ya tiene respuestas; no se pueden modificar sus preguntas.')
                ->with('open_edit_modal', $encuesta->id_encuesta);
        }

        $datos = $request->validate([
            'texto' => ['required', 'string', 'min:10', 'max:200'],
            'tipo' => ['required', Rule::in(['Opcion_multiple', 'Escala', 'Texto'])],
            'opciones_texto' => ['nullable', 'string', 'max:2000'],
            'obligatoria' => ['nullable', 'boolean'],
        ]);
        $opciones = array_values(array_filter(
            array_map('trim', preg_split('/\R/u', $datos['opciones_texto'] ?? '') ?: []),
            fn ($opcion) => $opcion !== ''
        ));
        if ($datos['tipo'] === 'Opcion_multiple' && count($opciones) < 2) {
            return back()->withInput()->withErrors([
                'opciones' => 'Las preguntas de opción múltiple requieren al menos dos opciones.',
            ])->with('open_edit_modal', $encuesta->id_encuesta);
        }

        $pregunta = Pregunta::where('id_encuesta', $encuesta->id_encuesta)->findOrFail($preguntaId);
        $opcionesAnteriores = $pregunta->opciones()->pluck('texto')->all();
        $opcionesLimpias = $datos['tipo'] === 'Opcion_multiple' ? $opciones : [];
        $huboCambios = $pregunta->texto !== trim($datos['texto'])
            || $pregunta->tipo !== $datos['tipo']
            || (bool) $pregunta->obligatoria !== $request->boolean('obligatoria')
            || $opcionesAnteriores !== $opcionesLimpias;

        DB::transaction(function () use ($encuesta, $pregunta, $datos, $opciones, $request, $huboCambios) {
            $pregunta->update([
                'texto' => trim($datos['texto']),
                'tipo' => $datos['tipo'],
                'obligatoria' => $request->boolean('obligatoria'),
            ]);
            OpcionPregunta::where('id_pregunta', $pregunta->id_pregunta)->delete();
            foreach ($opciones as $index => $texto) {
                OpcionPregunta::create([
                    'id_pregunta' => $pregunta->id_pregunta,
                    'texto' => $texto,
                    'orden' => $index + 1,
                ]);
            }

            if ($huboCambios) {
                $this->notificarCambioEncuesta(
                    $encuesta,
                    "modificó la pregunta \"{$pregunta->texto}\""
                );
            }
        });

        return back()->with('success', 'Pregunta actualizada correctamente.')
            ->with('open_edit_modal', $encuesta->id_encuesta);
    }

    public function eliminarPregunta(int $id, int $preguntaId)
    {
        $encuesta = $this->encuestaEditable($id);
        if ($encuesta->tieneRespuestas()) {
            return back()->with('error', 'La encuesta ya tiene respuestas; no se pueden modificar sus preguntas.')
                ->with('open_edit_modal', $encuesta->id_encuesta);
        }

        $pregunta = Pregunta::where('id_encuesta', $encuesta->id_encuesta)->findOrFail($preguntaId);
        DB::transaction(function () use ($encuesta, $pregunta) {
            $textoPregunta = $pregunta->texto;
            OpcionPregunta::where('id_pregunta', $pregunta->id_pregunta)->delete();
            $pregunta->delete();

            $this->notificarCambioEncuesta(
                $encuesta,
                "eliminó la pregunta \"{$textoPregunta}\""
            );
        });

        return back()->with('success', 'Pregunta eliminada.')
            ->with('open_edit_modal', $encuesta->id_encuesta);
    }

    public function activar(int $id)
    {
        $encuesta = $this->encuestaPropia($id);
        if (!$encuesta->preguntas()->exists()) {
            return back()->with('error', 'Agrega al menos una pregunta antes de activar la encuesta.');
        }

        $encuesta->update(['estado' => 'Activa']);
        return back()->with('success', 'Encuesta activada y disponible para tus actividades.');
    }

    public function desactivar(int $id)
    {
        $encuesta = $this->encuestaPropia($id);
        if ($encuesta->tieneProgramacionesPendientes()) {
            $actividades = $encuesta->nombresActividadesConProgramacionesPendientes()->implode(', ');
            return back()->with('error', "No se puede desactivar mientras existan programaciones pendientes: {$actividades}.");
        }

        $encuesta->update(['estado' => 'Inactiva']);
        return back()->with('success', 'Encuesta desactivada. Sus respuestas se conservaron.');
    }
}
