<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\AsesoriaHistorial;
use Modules\ControlECP\Entities\AsesoriaRecurso;
use Modules\ControlECP\Entities\AsesoriaSeguimiento;
use Modules\ControlECP\Entities\CategoriaAsesoria;
use Modules\ControlECP\Entities\DisponibilidadInstructor;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Services\NotificacionService;

class InstructorAsesoriaController extends Controller
{
    public function __construct(private NotificacionService $notificaciones) {}

    private function instructor(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('controlecp.instructor'), 403);
        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->instructor();
        $query = Asesoria::with(['categoria', 'aprendiz.person', 'seguimientos', 'recursos.inventario'])
            ->where('id_usuario_instructor', $user->id);
        if ($request->filled('categoria_id')) $query->where('id_categoria_asesoria', $request->categoria_id);
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('fecha_desde')) $query->whereDate('fecha', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->whereDate('fecha', '<=', $request->fecha_hasta);
        if ($request->filled('aprendiz')) {
            $term = trim($request->aprendiz);
            $query->whereHas('aprendiz', fn ($q) => $q->where('nickname', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereHas('person', fn ($p) => $p->where('first_name', 'like', "%{$term}%")
                    ->orWhere('first_last_name', 'like', "%{$term}%")
                    ->orWhere('document_number', 'like', "%{$term}%")));
        }
        $asesorias = $query->orderByDesc('fecha')->orderByDesc('hora_inicio')->get();
        $categorias = CategoriaAsesoria::where('estado', 'Activo')->orderBy('nombre')->get();
        $aprendices = User::whereHas('roles', fn ($q) => $q->where('slug', 'controlecp.apprentice'))->with('person')->get();
        $inventarios = Inventario::where('estado', '!=', 'Inactivo')->orderBy('nombre')->get();
        $disponibilidades = DisponibilidadInstructor::where('id_usuario', $user->id)->orderByDesc('anio')->orderBy('trimestre')->orderBy('dia_semana')->get();
        $indicadores = [
            'programadas' => $asesorias->where('estado', 'Programada')->count(),
            'realizadas' => $asesorias->where('estado', 'Realizada')->count(),
            'canceladas' => $asesorias->where('estado', 'Cancelada')->count(),
        ];
        return view('controlecp::instructor.asesorias', compact('asesorias', 'categorias', 'aprendices', 'inventarios', 'disponibilidades', 'indicadores'));
    }

    public function guardarDisponibilidad(Request $request)
    {
        $user = $this->instructor();
        $data = $request->validate([
            'anio' => ['required', 'integer', 'min:2024', 'max:2035'],
            'trimestre' => ['required', 'integer', 'min:1', 'max:4'],
            'franjas' => ['required', 'array', 'min:1'],
            'franjas.*.dia_semana' => ['required', 'integer', 'between:1,7'],
            'franjas.*.hora_inicio' => ['required', 'date_format:H:i'],
            'franjas.*.hora_fin' => ['required', 'date_format:H:i', 'after:franjas.*.hora_inicio'],
        ]);
        foreach ($data['franjas'] as $franja) {
            $this->validarCruceFranja($user->id, $data['anio'], $data['trimestre'], $franja);
        }
        DB::transaction(function () use ($data, $user) {
            DisponibilidadInstructor::where('id_usuario', $user->id)->where('anio', $data['anio'])->where('trimestre', $data['trimestre'])->update(['vigente' => false]);
            foreach ($data['franjas'] as $franja) {
                DisponibilidadInstructor::create([
                    'id_usuario' => $user->id, 'anio' => $data['anio'], 'trimestre' => $data['trimestre'],
                    'dia_semana' => $franja['dia_semana'], 'hora_inicio' => $franja['hora_inicio'],
                    'hora_fin' => $franja['hora_fin'], 'habilitada' => true, 'vigente' => true,
                    'fecha_registro' => now(),
                ]);
            }
        });
        return back()->with('success', 'Disponibilidad guardada y versión anterior conservada.');
    }

    private function validarCruceFranja(int $userId, int $anio, int $trimestre, array $franja): void
    {
        $cruce = DisponibilidadInstructor::where('id_usuario', $userId)->where('anio', $anio)->where('trimestre', $trimestre)
            ->where('vigente', true)->where('dia_semana', $franja['dia_semana'])
            ->where('hora_inicio', '<', $franja['hora_fin'])->where('hora_fin', '>', $franja['hora_inicio'])->exists();
        if ($cruce) throw ValidationException::withMessages(['franjas' => 'Hay franjas de disponibilidad cruzadas.']);
        $this->validarCruceProgramacion($userId, $franja);
    }

    private function validarCruceProgramacion(int $userId, array $franja): void
    {
        $dia = (int) $franja['dia_semana'];
        $inicio = $franja['hora_inicio'];
        $fin = $franja['hora_fin'];
        $hayActividad = DB::table('cronograma')->where('id_instructor', $userId)->where('estado', '!=', 'Cancelada')
            ->whereRaw('DAYOFWEEK(fecha) = ?', [$dia === 7 ? 1 : $dia + 1])
            ->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists();
        $hayAsesoria = Asesoria::where('id_usuario_instructor', $userId)->where('estado', '!=', 'Cancelada')
            ->whereRaw('DAYOFWEEK(fecha) = ?', [$dia === 7 ? 1 : $dia + 1])
            ->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists();
        if ($hayActividad || $hayAsesoria) throw ValidationException::withMessages(['franjas' => 'Una franja cruza con una actividad o asesoría programada.']);
    }

    public function solicitar(Request $request)
    {
        $user = $this->instructor();
        $data = $request->validate([
            'id_usuario_aprendiz' => ['required', 'exists:users,id'],
            'id_categoria_asesoria' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'motivo' => ['required', 'string', 'min:5'],
        ]);
        if (!$this->disponible($user->id, $data['fecha'], $data['hora_inicio'], $data['hora_fin'])) {
            throw ValidationException::withMessages(['fecha' => 'No tienes disponibilidad o existe un cruce en ese horario.']);
        }
        $asesoria = Asesoria::create($data + ['id_usuario_instructor' => $user->id, 'id_usuario_registro' => $user->id, 'estado' => 'Programada', 'fecha_solicitud' => now()]);
        $this->notificar($asesoria->aprendiz, 'Nueva asesoría registrada', 'Tienes una asesoría programada.', $asesoria);
        return back()->with('success', 'Solicitud registrada y asesoría programada.');
    }

    private function disponible(int $userId, string $fecha, string $inicio, string $fin): bool
    {
        $carbon = Carbon::parse($fecha);
        $trimestre = (int) ceil($carbon->month / 3);
        $dia = $carbon->dayOfWeekIso;
        $franja = DisponibilidadInstructor::where('id_usuario', $userId)->where('anio', $carbon->year)->where('trimestre', $trimestre)
            ->where('dia_semana', $dia)->where('vigente', true)->where('habilitada', true)
            ->where('hora_inicio', '<=', $inicio)->where('hora_fin', '>=', $fin)->exists();
        if (!$franja) return false;
        return !Asesoria::where('id_usuario_instructor', $userId)->whereDate('fecha', $fecha)->where('estado', '!=', 'Cancelada')->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists()
            && !DB::table('cronograma')->where('id_instructor', $userId)->whereDate('fecha', $fecha)->where('estado', '!=', 'Cancelada')->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists();
    }

    public function reprogramar(Request $request, int $id)
    {
        $asesoria = $this->propia($id);
        $data = $request->validate(['fecha' => ['required', 'date', 'after_or_equal:today'], 'hora_inicio' => ['required'], 'hora_fin' => ['required', 'after:hora_inicio'], 'motivo' => ['required', 'string', 'min:5']]);
        if (!$this->disponible(Auth::id(), $data['fecha'], $data['hora_inicio'], $data['hora_fin'])) throw ValidationException::withMessages(['fecha' => 'El nuevo horario no está disponible.']);
        $anteriores = ['fecha' => $asesoria->fecha, 'hora_inicio' => $asesoria->hora_inicio, 'hora_fin' => $asesoria->hora_fin];
        $asesoria->update(['fecha' => $data['fecha'], 'hora_inicio' => $data['hora_inicio'], 'hora_fin' => $data['hora_fin']]);
        AsesoriaHistorial::create(['id_asesoria' => $id, 'id_usuario' => Auth::id(), 'accion' => 'reprogramar', 'fecha_anterior' => $anteriores['fecha'], 'hora_anterior' => $anteriores['hora_inicio'], 'fecha_nueva' => $data['fecha'], 'hora_nueva' => $data['hora_inicio'], 'motivo' => $data['motivo'], 'fecha' => now()]);
        $this->notificar($asesoria->aprendiz, 'Asesoría reprogramada', 'La fecha de tu asesoría fue actualizada.', $asesoria);
        return back()->with('success', 'Asesoría reprogramada.');
    }

    public function cancelar(Request $request, int $id)
    {
        $asesoria = $this->propia($id);
        $data = $request->validate(['motivo' => ['required', 'string', 'min:5']]);
        $asesoria->update(['estado' => 'Cancelada', 'motivo_cancelacion' => $data['motivo'], 'id_usuario_cancelador' => Auth::id()]);
        AsesoriaHistorial::create(['id_asesoria' => $id, 'id_usuario' => Auth::id(), 'accion' => 'cancelar', 'motivo' => $data['motivo'], 'fecha' => now()]);
        $this->notificar($asesoria->aprendiz, 'Asesoría cancelada', 'Tu asesoría fue cancelada.', $asesoria);
        return back()->with('success', 'Asesoría cancelada.');
    }

    public function seguimiento(Request $request, int $id)
    {
        $asesoria = $this->propia($id);
        $data = $request->validate(['avances' => ['required', 'string', 'min:5'], 'compromisos' => ['required', 'string', 'min:5'], 'observaciones' => ['nullable', 'string']]);
        AsesoriaSeguimiento::create(['id_asesoria' => $id, 'id_usuario' => Auth::id(), 'avances' => $data['avances'], 'compromisos' => $data['compromisos'], 'observaciones' => $data['observaciones'] ?? null, 'fecha' => now()]);
        $asesoria->update(['estado' => 'Realizada']);
        return back()->with('success', 'Seguimiento registrado y asesoría finalizada.');
    }

    public function recurso(Request $request, int $id)
    {
        $asesoria = $this->propia($id);
        $data = $request->validate(['id_inventario' => ['required', 'exists:inventario,id_inventario'], 'cantidad' => ['required', 'integer', 'min:1']]);
        $inventario = Inventario::findOrFail($data['id_inventario']);
        abort_if($inventario->cantidad_disponible < $data['cantidad'], 422, 'Cantidad no disponible.');
        DB::transaction(function () use ($asesoria, $inventario, $data) {
            $inventario->decrement('cantidad_disponible', $data['cantidad']);
            AsesoriaRecurso::create(['id_asesoria' => $asesoria->id_asesoria, 'id_inventario' => $inventario->id_inventario, 'cantidad' => $data['cantidad']]);
        });
        return back()->with('success', 'Recurso asignado.');
    }

    private function propia(int $id): Asesoria
    {
        return Asesoria::where('id_asesoria', $id)->where('id_usuario_instructor', Auth::id())->firstOrFail();
    }

    private function notificar($aprendiz, string $titulo, string $mensaje, Asesoria $asesoria): void
    {
        if (!$aprendiz) return;
        $this->notificaciones->crear(['id_usuario_receptor' => $aprendiz->id, 'id_usuario_causante' => Auth::id(), 'modulo' => 'Asesorías', 'titulo' => $titulo, 'mensaje' => $mensaje, 'referencia_tipo' => 'asesoria', 'referencia_id' => $asesoria->id_asesoria, 'clave_evento' => 'asesoria-instructor-'.$asesoria->id_asesoria.'-'.uniqid()]);
    }
}
