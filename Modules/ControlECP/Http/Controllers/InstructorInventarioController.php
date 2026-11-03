<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\AsesoriaRecurso;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\CronogramaRecurso;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Entities\RecursoNovedad;
use Modules\ControlECP\Services\NotificacionService;

class InstructorInventarioController extends Controller
{
    private function autorizar(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }

    public function index(Request $request)
    {
        $this->autorizar();
        $query = Inventario::with(['categoria', 'fotos'])->where('estado', '!=', 'Inactivo');
        $query->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%' . trim($request->buscar) . '%'));
        $query->when($request->filled('categoria_id'), fn ($q) => $q->where('id_categoria_inventario', $request->categoria_id));
        $query->when($request->filled('estado_fisico'), fn ($q) => $q->where('estado_fisico', $request->estado_fisico));
        $query->when($request->filled('semaforo'), function ($q) use ($request) {
            $sem = strtolower($request->semaforo);
            if ($sem === 'verde') $q->whereColumn('cantidad_disponible', '>', 'umbral_regular');
            if ($sem === 'amarillo') $q->whereColumn('cantidad_disponible', '<=', 'umbral_regular')->whereColumn('cantidad_disponible', '>', 'umbral_critico');
            if ($sem === 'rojo') $q->where(function ($sub) {
                $sub->where('estado', 'Agotado')->orWhere('cantidad_disponible', '<=', 0)->orWhereColumn('cantidad_disponible', '<=', 'umbral_critico');
            });
        });

        $recursos = $query->orderBy('nombre')->paginate(12)->withQueryString();
        $categorias = Inventario::with('categoria')->where('estado', '!=', 'Inactivo')->get()->pluck('categoria')->filter()->unique('id_categoria_inventario')->sortBy('nombre');
        $cronogramas = Cronograma::with('actividad')->where('id_instructor', Auth::id())->whereIn('estado', ['Pendiente', 'En proceso'])->orderBy('fecha')->get();
        $asesorias = Asesoria::where('id_usuario_instructor', Auth::id())->whereIn('estado', ['Pendiente', 'Programada', 'En proceso'])->orderBy('fecha')->get();

        return view('controlecp::instructor.inventario', compact('recursos', 'categorias', 'cronogramas', 'asesorias'));
    }

    public function show(int $id)
    {
        $this->autorizar();
        $recurso = Inventario::with(['categoria', 'fotos', 'novedades.usuario', 'movimientos.usuario', 'movimientos.movimientosHijos'])
            ->where('estado', '!=', 'Inactivo')->findOrFail($id);

        return view('controlecp::instructor.inventario_show', compact('recurso'));
    }

    public function asociar(Request $request, int $id)
    {
        $this->autorizar();
        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
            'id_cronograma' => ['nullable', 'integer'],
            'id_asesoria' => ['nullable', 'integer'],
        ]);
        abort_if(empty($data['id_cronograma']) && empty($data['id_asesoria']), 422, 'Seleccione una actividad o asesoría.');
        $recurso = Inventario::where('estado', '!=', 'Inactivo')->findOrFail($id);
        abort_if($recurso->cantidad_disponible < $data['cantidad'], 422, 'La cantidad solicitada no está disponible.');

        DB::transaction(function () use ($data, $recurso) {
            if (!empty($data['id_cronograma'])) {
                $cronograma = Cronograma::where('id_cronograma', $data['id_cronograma'])->where('id_instructor', Auth::id())->firstOrFail();
                abort_if(CronogramaRecurso::where(['id_cronograma' => $cronograma->id_cronograma, 'id_inventario' => $recurso->id_inventario])->exists(), 422, 'El recurso ya está asociado a la actividad.');
                CronogramaRecurso::create(['id_cronograma' => $cronograma->id_cronograma, 'id_inventario' => $recurso->id_inventario, 'cantidad' => $data['cantidad']]);
            } else {
                $asesoria = Asesoria::where('id_asesoria', $data['id_asesoria'])->where('id_usuario_instructor', Auth::id())->firstOrFail();
                abort_if(AsesoriaRecurso::where(['id_asesoria' => $asesoria->id_asesoria, 'id_inventario' => $recurso->id_inventario])->exists(), 422, 'El recurso ya está asociado a la asesoría.');
                AsesoriaRecurso::create(['id_asesoria' => $asesoria->id_asesoria, 'id_inventario' => $recurso->id_inventario, 'cantidad' => $data['cantidad']]);
            }
            $recurso->decrement('cantidad_disponible', $data['cantidad']);
            $recurso->refresh();
            if ($recurso->cantidad_disponible === 0) $recurso->update(['estado' => 'Agotado']);
        });

        return back()->with('success', 'Recurso asociado correctamente y cantidad reservada.');
    }

    public function entrega(Request $request, int $id)
    {
        $this->autorizar();
        $recurso = Inventario::where('estado', '!=', 'Inactivo')->findOrFail($id);
        $data = $request->validate(['cantidad' => "required|integer|min:1|max:{$recurso->cantidad_disponible}", 'observacion' => 'nullable|string|max:1000', 'foto' => 'required|image|mimes:jpeg,jpg,png|max:4096']);
        return $this->registrarMovimiento($recurso, $data, 'Entrega', -1);
    }

    public function devolucion(Request $request, int $id)
    {
        $this->autorizar();
        $recurso = Inventario::where('estado', '!=', 'Inactivo')->findOrFail($id);
        $data = $request->validate(['cantidad' => 'required|integer|min:1', 'id_movimiento_entrega' => 'nullable|exists:movimientos_inventario,id_movimiento_inventario', 'observacion' => 'nullable|string|max:1000', 'foto' => 'required|image|mimes:jpeg,jpg,png|max:4096']);
        $entrega = !empty($data['id_movimiento_entrega']) ? MovimientoInventario::where('id_inventario', $id)->where('id_movimiento_inventario', $data['id_movimiento_entrega'])->where('tipo', 'Entrega')->firstOrFail() : null;
        $data['origen'] = $entrega?->id_movimiento_inventario;
        return $this->registrarMovimiento($recurso, $data, 'Devolución', 1);
    }

    private function registrarMovimiento(Inventario $recurso, array $data, string $tipo, int $signo)
    {
        $ruta = 'storage/' . $data['foto']->store('inventario/movimientos', 'public');
        $alerta = '';
        DB::transaction(function () use ($recurso, $data, $tipo, $signo, $ruta, &$alerta) {
            if ($signo > 0) {
                $recurso->cantidad_disponible = min($recurso->cantidad_total, $recurso->cantidad_disponible + $data['cantidad']);
            } else {
                $recurso->cantidad_disponible -= $data['cantidad'];
            }
            $recurso->estado = $recurso->cantidad_disponible > 0 ? 'Disponible' : 'Agotado';
            $recurso->save();
            $movimiento = MovimientoInventario::create([
                'id_inventario' => $recurso->id_inventario,
                'id_usuario' => Auth::id(),
                'id_movimiento_inventario_origen' => $data['origen'] ?? null,
                'tipo' => $tipo,
                'cantidad' => $data['cantidad'],
                'estado_anterior' => $recurso->getOriginal('estado'),
                'estado_nuevo' => $recurso->estado,
                'observacion' => $data['observacion'] ?? null,
                'foto' => $ruta,
                'fecha' => now(),
            ]);
            if ($tipo === 'Devolución' && !empty($data['origen'])) {
                $entrega = MovimientoInventario::with('movimientosHijos')->find($data['origen']);
                $devuelto = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                if ($devuelto < $entrega->cantidad) {
                    $alerta = "Devolución incompleta: faltan " . ($entrega->cantidad - $devuelto) . " unidad(es).";
                    foreach (User::whereHas('roles', fn ($q) => $q->where('slug', 'controlecp.admin'))->pluck('id') as $adminId) {
                        app(NotificacionService::class)->crear(['id_usuario_receptor' => $adminId, 'id_usuario_causante' => Auth::id(), 'titulo' => 'Devolución incompleta', 'mensaje' => $alerta, 'modulo' => 'Inventario', 'clave_evento' => "inventario-devolucion-{$movimiento->id_movimiento_inventario}"]);
                    }
                }
            }
        });
        return back()->with($alerta ? 'warning' : 'success', $alerta ?: "{$tipo} registrada correctamente.");
    }

    public function novedad(Request $request, int $id)
    {
        $this->autorizar();
        $request->validate(['descripcion' => 'required|string|max:1000']);
        Inventario::where('estado', '!=', 'Inactivo')->findOrFail($id);
        RecursoNovedad::create(['id_inventario' => $id, 'id_usuario' => Auth::id(), 'descripcion' => trim($request->descripcion), 'fecha' => now()]);
        return back()->with('success', 'Novedad registrada correctamente.');
    }
}
