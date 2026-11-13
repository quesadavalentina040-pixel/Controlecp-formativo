<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\AsesoriaRecurso;
use Modules\ControlECP\Entities\CategoriaInventario;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\CronogramaRecurso;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Entities\RecursoNovedad;

class InstructorInventarioController extends Controller
{
    private function authorizeInstructor(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }

    private function recurso(int $id): Inventario
    {
        $this->authorizeInstructor();
        return Inventario::findOrFail($id);
    }

    public function index(Request $request)
    {
        $this->authorizeInstructor();
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias_inventario,id_categoria_inventario'],
            'estado_fisico' => ['nullable', Rule::in(Inventario::ESTADOS_FISICOS)],
            'semaforo' => ['nullable', Rule::in(['verde', 'amarillo', 'rojo'])],
        ]);

        $query = Inventario::with(['categoria', 'fotos'])->where('estado', '!=', 'Inactivo');
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . trim($request->input('buscar')) . '%');
        }
        if ($request->filled('categoria_id')) {
            $query->where('id_categoria_inventario', $request->input('categoria_id'));
        }
        if ($request->filled('estado_fisico')) {
            $query->where('estado_fisico', $request->input('estado_fisico'));
        }
        if ($request->filled('semaforo')) {
            $semaforo = $request->input('semaforo');
            $query->where(function ($stockQuery) use ($semaforo) {
                if ($semaforo === 'rojo') {
                    $stockQuery->where('estado', 'Agotado')
                        ->orWhere('cantidad_disponible', '<=', 0)
                        ->orWhereRaw('cantidad_disponible <= CASE WHEN umbral_critico > 0 THEN umbral_critico ELSE 5 END');
                } elseif ($semaforo === 'amarillo') {
                    $stockQuery->where('estado', '!=', 'Agotado')
                        ->where('cantidad_disponible', '>', 0)
                        ->whereRaw('cantidad_disponible <= CASE WHEN umbral_regular > 0 THEN umbral_regular ELSE 15 END')
                        ->whereRaw('cantidad_disponible > CASE WHEN umbral_critico > 0 THEN umbral_critico ELSE 5 END');
                } else {
                    $stockQuery->where('estado', '!=', 'Agotado')
                        ->whereRaw('cantidad_disponible > CASE WHEN umbral_regular > 0 THEN umbral_regular ELSE 15 END');
                }
            });
        }

        $recursos = $query->orderBy('nombre')->paginate(10)->withQueryString();
        $todosActivos = Inventario::where('estado', '!=', 'Inactivo')
            ->get(['estado', 'cantidad_total', 'cantidad_disponible', 'umbral_critico', 'umbral_regular']);
        $totalRecursos = Inventario::count();
        $bajasCount = Inventario::where('estado', 'Inactivo')->count();
        $totalUnidades = (int) $todosActivos->sum('cantidad_total');
        $totalDisponibles = (int) $todosActivos->sum('cantidad_disponible');
        $totalPrestados = max(0, $totalUnidades - $totalDisponibles);
        $stockBajoCount = $todosActivos->filter(fn ($recurso) => $recurso->semaforo !== 'verde')->count();
        $categorias = CategoriaInventario::whereHas('inventarios', fn ($query) => $query->where('estado', '!=', 'Inactivo'))
            ->orderBy('nombre')->get();
        return view('controlecp::instructor.inventario', compact(
            'recursos',
            'categorias',
            'totalRecursos',
            'bajasCount',
            'totalUnidades',
            'totalDisponibles',
            'totalPrestados',
            'stockBajoCount'
        ));
    }

    public function show(int $id)
    {
        $recurso = $this->recurso($id);
        $recurso->load([
            'categoria',
            'fotos',
            'novedades.usuario',
            'movimientos.usuario',
            'movimientos.movimientoOrigen',
            'movimientos.movimientosHijos',
            'cronogramaRecursos.cronograma.actividad',
            'asesoriaRecursos',
        ]);

        $actividades = Cronograma::with('actividad')
            ->where('id_instructor', Auth::id())
            ->whereNotIn('estado', ['Cancelada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get();
        $asesorias = Asesoria::with('categoria')
            ->where('id_usuario_instructor', Auth::id())
            ->whereIn('estado', ['Programada', 'Realizada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get();
        $entregasPendientes = $recurso->movimientos
            ->where('id_usuario', Auth::id())
            ->whereIn('tipo', ['Entrega', 'Salida'])
            ->filter(fn ($movimiento) => $movimiento->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad') < $movimiento->cantidad)
            ->values();

        return view('controlecp::instructor.inventario_show', compact(
            'recurso',
            'actividades',
            'asesorias',
            'entregasPendientes'
        ));
    }

    public function disponibilidad(Request $request, int $id)
    {
        $recurso = $this->recurso($id);
        $data = $request->validate([
            'destino' => ['required', 'string', 'max:40'],
        ]);
        [$tipo, $destinoId] = $this->parseDestino($data['destino']);
        [$fecha, $inicio, $fin] = $this->obtenerHorarioDestino($tipo, $destinoId);
        $disponible = $this->calcularDisponibilidad($recurso, $tipo, $destinoId, $fecha, $inicio, $fin);

        return response()->json([
            'disponible' => $disponible > 0 && $recurso->estado !== 'Inactivo',
            'cantidad_disponible' => $disponible,
            'fecha' => $fecha,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
        ]);
    }

    public function entregar(Request $request, int $id)
    {
        $recurso = $this->recurso($id);
        $data = $request->validate([
            'destino' => ['required', 'string', 'max:40'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
            'observacion' => ['nullable', 'string', 'max:1000'],
            'foto' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:4096'],
        ]);
        [$tipo, $destinoId] = $this->parseDestino($data['destino']);
        if ($recurso->estado === 'Inactivo') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'destino' => 'No se puede entregar un recurso dado de baja.',
            ]);
        }

        $rutaPublica = null;
        try {
            $ruta = $request->file('foto')->store('inventario/movimientos', 'public');
            $rutaPublica = 'storage/' . $ruta;
            DB::transaction(function () use ($recurso, $data, $tipo, $destinoId, $rutaPublica) {
                $inventario = Inventario::whereKey($recurso->id_inventario)->lockForUpdate()->firstOrFail();
                [$fecha, $inicio, $fin] = $this->obtenerHorarioDestino($tipo, $destinoId);
                $disponibilidad = $this->calcularDisponibilidad($inventario, $tipo, $destinoId, $fecha, $inicio, $fin);
                $asociacion = $this->obtenerAsociacion($tipo, $destinoId, $inventario->id_inventario, true);
                $estadoAnterior = $inventario->estado;
                $movimientosPrevios = MovimientoInventario::where('id_inventario', $inventario->id_inventario)
                    ->whereIn('tipo', ['Entrega', 'Salida'])
                    ->where($tipo === 'actividad' ? 'id_cronograma' : 'id_asesoria', $destinoId)
                    ->get(['id_movimiento_inventario', 'tipo', 'cantidad', 'id_movimiento_inventario_origen']);
                $yaEntregado = $movimientosPrevios->sum(function ($movimiento) {
                    if ($movimiento->tipo === 'Devolución') return 0;
                    $devuelto = MovimientoInventario::where('id_movimiento_inventario_origen', $movimiento->id_movimiento_inventario)
                        ->where('tipo', 'Devolución')->sum('cantidad');
                    return max(0, (int) $movimiento->cantidad - (int) $devuelto);
                });
                $reservadoSinEntrega = max(0, (int) $asociacion->cantidad - (int) $yaEntregado);
                $stockExtra = max(0, (int) $data['cantidad'] - $reservadoSinEntrega);

                if (($inventario->estado !== 'Disponible' && $reservadoSinEntrega < (int) $data['cantidad'])
                    || $disponibilidad < (int) $data['cantidad']
                    || $inventario->cantidad_disponible < $stockExtra) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cantidad' => 'El recurso no está disponible en esa fecha y hora o no hay unidades suficientes.',
                    ]);
                }
                if ($stockExtra > 0) {
                    $inventario->cantidad_disponible -= $stockExtra;
                    if ($inventario->cantidad_disponible === 0) $inventario->estado = 'Agotado';
                    $inventario->save();
                    $asociacion->increment('cantidad', $stockExtra);
                }

                MovimientoInventario::create([
                    'id_inventario' => $inventario->id_inventario,
                    'id_usuario' => Auth::id(),
                    'id_cronograma' => $tipo === 'actividad' ? $destinoId : null,
                    'id_asesoria' => $tipo === 'asesoria' ? $destinoId : null,
                    'tipo' => 'Entrega',
                    'cantidad' => $data['cantidad'],
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $inventario->estado,
                    'observacion' => trim($data['observacion'] ?? '') ?: 'Entrega para ' . $tipo . '.',
                    'foto' => $rutaPublica,
                    'fecha' => now(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($rutaPublica) {
                Storage::disk('public')->delete(str_replace('storage/', '', $rutaPublica));
            }
            throw $exception;
        }

        return back()->with('success', 'Entrega registrada con fotografía y asociada a la actividad o asesoría.');
    }

    public function devolver(Request $request, int $id)
    {
        $recurso = $this->recurso($id);
        $data = $request->validate([
            'id_movimiento_entrega' => ['required', 'integer', 'exists:movimientos_inventario,id_movimiento_inventario'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
            'observacion' => ['nullable', 'string', 'max:1000'],
            'foto' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:4096'],
        ]);
        $rutaPublica = null;
        try {
            $ruta = $request->file('foto')->store('inventario/movimientos', 'public');
            $rutaPublica = 'storage/' . $ruta;
            $parcial = DB::transaction(function () use ($recurso, $data, $rutaPublica) {
                $entrega = MovimientoInventario::where('id_inventario', $recurso->id_inventario)
                    ->whereIn('tipo', ['Entrega', 'Salida'])->where('id_usuario', Auth::id())
                    ->lockForUpdate()->findOrFail($data['id_movimiento_entrega']);
                if ($entrega->id_cronograma) {
                    Cronograma::where('id_instructor', Auth::id())->findOrFail($entrega->id_cronograma);
                } elseif ($entrega->id_asesoria) {
                    Asesoria::where('id_usuario_instructor', Auth::id())->findOrFail($entrega->id_asesoria);
                } else {
                    abort(404);
                }
                $devuelto = MovimientoInventario::where('id_movimiento_inventario_origen', $entrega->id_movimiento_inventario)
                    ->where('tipo', 'Devolución')->sum('cantidad');
                $saldo = (int) $entrega->cantidad - (int) $devuelto;
                if ((int) $data['cantidad'] > $saldo) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cantidad' => "La cantidad no puede superar el saldo pendiente de {$saldo}.",
                    ]);
                }

                $inventario = Inventario::whereKey($recurso->id_inventario)->lockForUpdate()->firstOrFail();
                $inventario->cantidad_disponible = min($inventario->cantidad_total, $inventario->cantidad_disponible + (int) $data['cantidad']);
                if ($inventario->cantidad_disponible > 0 && $inventario->estado === 'Agotado') {
                    $inventario->estado = 'Disponible';
                }
                $inventario->save();

                $idCronograma = $entrega->id_cronograma;
                $idAsesoria = $entrega->id_asesoria;
                $asociacion = $idCronograma
                    ? CronogramaRecurso::where('id_cronograma', $idCronograma)->where('id_inventario', $inventario->id_inventario)->lockForUpdate()->first()
                    : AsesoriaRecurso::where('id_asesoria', $idAsesoria)->where('id_inventario', $inventario->id_inventario)->lockForUpdate()->first();
                if ($asociacion) {
                    $asociacion->cantidad = max(0, (int) $asociacion->cantidad - (int) $data['cantidad']);
                    if ($asociacion->cantidad === 0) $asociacion->delete();
                    else $asociacion->save();
                }

                $nuevoDevuelto = (int) $devuelto + (int) $data['cantidad'];
                $restante = (int) $entrega->cantidad - $nuevoDevuelto;
                $observacion = trim($data['observacion'] ?? '') ?: 'Devolución de recurso.';
                if ($restante > 0) {
                    $observacion = "[ALERTA DEVOLUCIÓN INCOMPLETA] Quedan {$restante} unidad(es) pendientes. " . $observacion;
                }
                MovimientoInventario::create([
                    'id_inventario' => $inventario->id_inventario,
                    'id_usuario' => Auth::id(),
                    'id_movimiento_inventario_origen' => $entrega->id_movimiento_inventario,
                    'id_cronograma' => $idCronograma,
                    'id_asesoria' => $idAsesoria,
                    'tipo' => 'Devolución',
                    'cantidad' => $data['cantidad'],
                    'estado_anterior' => 'Prestado / Entregado',
                    'estado_nuevo' => $inventario->estado,
                    'observacion' => $observacion,
                    'foto' => $rutaPublica,
                    'fecha' => now(),
                ]);

                if ($restante > 0) {
                    $administradores = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.admin')->orWhere('full_access', 'Si'))
                        ->pluck('id');
                    foreach ($administradores as $administradorId) {
                        Notificacion::crear(
                            idReceptor: (int) $administradorId,
                            idCausante: (int) Auth::id(),
                            titulo: 'Devolución incompleta de inventario',
                            mensaje: "La devolución de {$inventario->nombre} quedó incompleta; restan {$restante} unidad(es).",
                            referenciaTipo: 'inventario',
                            referenciaId: (int) $inventario->id_inventario,
                            claveEvento: 'inventario-devolucion-incompleta-' . $entrega->id_movimiento_inventario . '-' . now()->timestamp . '-' . $administradorId
                        );
                    }
                }
                return $restante > 0;
            });
        } catch (\Throwable $exception) {
            if ($rutaPublica) {
                Storage::disk('public')->delete(str_replace('storage/', '', $rutaPublica));
            }
            throw $exception;
        }

        return back()->with(
            $parcial ? 'error' : 'success',
            $parcial ? 'Devolución incompleta registrada; se notificó a administración.' : 'Devolución completa registrada.'
        );
    }

    public function novedad(Request $request, int $id)
    {
        $recurso = $this->recurso($id);
        $data = $request->validate([
            'descripcion' => ['required', 'string', 'min:10', 'max:200'],
        ]);

        RecursoNovedad::create([
            'id_inventario' => $recurso->id_inventario,
            'id_usuario' => Auth::id(),
            'descripcion' => trim($data['descripcion']),
            'fecha' => now(),
        ]);

        return back()->with('success', 'Novedad registrada con fecha y autor.');
    }

    private function parseDestino(string $destino): array
    {
        if (!preg_match('/^(actividad|asesoria):([1-9][0-9]*)$/D', $destino, $matches)) {
            abort(422, 'Selecciona una actividad o asesoría válida.');
        }
        return [$matches[1], (int) $matches[2]];
    }

    private function obtenerHorarioDestino(string $tipo, int $id): array
    {
        if ($tipo === 'actividad') {
            $destino = Cronograma::where('id_instructor', Auth::id())
                ->whereNotIn('estado', ['Cancelada'])
                ->findOrFail($id);
        } else {
            $destino = Asesoria::where('id_usuario_instructor', Auth::id())
                ->whereIn('estado', ['Programada', 'Realizada'])
                ->findOrFail($id);
        }
        return [$destino->fecha->format('Y-m-d'), substr($destino->hora_inicio, 0, 8), substr($destino->hora_fin, 0, 8)];
    }

    private function obtenerAsociacion(string $tipo, int $destinoId, int $recursoId, bool $crear)
    {
        if ($tipo === 'actividad') {
            $association = CronogramaRecurso::where('id_cronograma', $destinoId)
                ->where('id_inventario', $recursoId)->lockForUpdate()->first();
            if (!$association && $crear) {
                $association = CronogramaRecurso::create([
                    'id_cronograma' => $destinoId,
                    'id_inventario' => $recursoId,
                    'cantidad' => 0,
                ]);
            }
        } else {
            $association = AsesoriaRecurso::where('id_asesoria', $destinoId)
                ->where('id_inventario', $recursoId)->lockForUpdate()->first();
            if (!$association && $crear) {
                $association = AsesoriaRecurso::create([
                    'id_asesoria' => $destinoId,
                    'id_inventario' => $recursoId,
                    'cantidad' => 0,
                ]);
            }
        }
        return $association;
    }

    private function calcularDisponibilidad(Inventario $inventario, string $tipo, int $destinoId, string $fecha, string $inicio, string $fin): int
    {
        $reservadoCronogramas = (int) DB::table('cronograma_recursos as cr')
            ->join('cronograma as c', 'c.id_cronograma', '=', 'cr.id_cronograma')
            ->where('cr.id_inventario', $inventario->id_inventario)->where('c.fecha', $fecha)
            ->whereNotIn('c.estado', ['Cancelada'])->where('c.hora_inicio', '<', $fin)->where('c.hora_fin', '>', $inicio)
            ->sum('cr.cantidad');
        $reservadoAsesorias = (int) DB::table('asesoria_recursos as ar')
            ->join('asesorias as a', 'a.id_asesoria', '=', 'ar.id_asesoria')
            ->where('ar.id_inventario', $inventario->id_inventario)->where('a.fecha', $fecha)
            ->whereNotIn('a.estado', ['Cancelada'])->where('a.hora_inicio', '<', $fin)->where('a.hora_fin', '>', $inicio)
            ->sum('ar.cantidad');
        $actual = $this->obtenerAsociacion($tipo, $destinoId, $inventario->id_inventario, false);
        return max(0, $inventario->cantidad_total - $reservadoCronogramas - $reservadoAsesorias + (int) ($actual?->cantidad ?? 0));
    }
}
