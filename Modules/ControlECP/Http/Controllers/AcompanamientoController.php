<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Acompanamiento;
use Modules\ControlECP\Entities\AcompanamientoHistorial;
use Modules\ControlECP\Entities\AcompanamientoSesion;
use Modules\ControlECP\Services\NotificacionService;

class AcompanamientoController extends Controller
{
    public function __construct(private NotificacionService $notificaciones) {}

    private function instructor()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('controlecp.instructor'), 403);
        return $user;
    }

    public function index()
    {
        $instructor = $this->instructor();
        $acompanamientos = Acompanamiento::with(['aprendiz.person', 'sesiones'])
            ->where('id_instructor', $instructor->id)->latest()->get();
        $aprendices = User::whereHas('roles', fn ($q) => $q->where('slug', 'controlecp.apprentice'))
            ->with('person')->orderBy('nickname')->get();
        return view('controlecp::instructor.acompaniamiento', compact('acompanamientos', 'aprendices'));
    }

    public function store(Request $request)
    {
        $instructor = $this->instructor();
        $data = $request->validate([
            'id_aprendiz' => ['required', 'exists:users,id'],
            'categoria' => ['required', 'string', 'max:100'],
            'motivo' => ['required', 'string', 'max:5000'],
            'fecha_inicio' => ['required', 'date'],
            'intervalo_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'total_sesiones' => ['required', 'integer', 'min:1', 'max:52'],
        ]);
        $aprendizValido = User::whereKey($data['id_aprendiz'])
            ->whereHas('roles', fn ($q) => $q->where('slug', 'controlecp.apprentice'))->exists();
        abort_unless($aprendizValido, 422, 'El usuario seleccionado no es aprendiz.');
        if (Acompanamiento::where('id_instructor', $instructor->id)->where('id_aprendiz', $data['id_aprendiz'])->where('estado', 'Activo')->exists()) {
            throw ValidationException::withMessages(['id_aprendiz' => 'El aprendiz ya tiene un acompañamiento activo contigo.']);
        }

        $acompanamiento = DB::transaction(function () use ($data, $instructor) {
            $acompanamiento = Acompanamiento::create($data + ['id_instructor' => $instructor->id]);
            $this->crearSesiones($acompanamiento, $data['fecha_inicio'], $data['total_sesiones'], $data['intervalo_dias']);
            return $acompanamiento;
        });
        $this->avisar($acompanamiento, 'Acompañamiento programado', 'Tienes sesiones de acompañamiento programadas.', 'crear');
        return back()->with('success', 'Acompañamiento y sesiones creados correctamente.');
    }

    private function crearSesiones(Acompanamiento $acompanamiento, string $inicio, int $cantidad, int $intervalo): void
    {
        $fecha = Carbon::parse($inicio);
        for ($i = 0; $i < $cantidad; $i++) {
            $this->validarCruce($acompanamiento->id_instructor, $fecha->toDateString(), '08:00', '09:00');
            $acompanamiento->sesiones()->create(['fecha' => $fecha->toDateString(), 'hora_inicio' => '08:00', 'hora_fin' => '09:00']);
            $fecha->addDays($intervalo);
        }
    }

    private function validarCruce(int $instructorId, string $fecha, string $inicio, string $fin, ?int $excepto = null): void
    {
        $query = AcompanamientoSesion::whereHas('acompanamiento', fn ($q) => $q->where('id_instructor', $instructorId))
            ->whereDate('fecha', $fecha)->where('estado', '!=', 'Cancelada')
            ->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio);
        if ($excepto) $query->where('id_sesion', '!=', $excepto);
        if ($query->exists()) throw ValidationException::withMessages(['fecha' => 'El instructor ya tiene otra sesión de acompañamiento en ese horario.']);
        if (DB::table('cronograma')->where('id_instructor', $instructorId)->whereDate('fecha', $fecha)->where('estado', '!=', 'Cancelada')->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists()) {
            throw ValidationException::withMessages(['fecha' => 'El horario cruza con una actividad programada.']);
        }
        if (DB::table('asesorias')->where('id_usuario_instructor', $instructorId)->whereDate('fecha', $fecha)->where('estado', '!=', 'Cancelada')->where('hora_inicio', '<', $fin)->where('hora_fin', '>', $inicio)->exists()) {
            throw ValidationException::withMessages(['fecha' => 'El horario cruza con una asesoría.']);
        }
    }

    public function registrar(Request $request, int $id)
    {
        $sesion = $this->sesionPropia($id);
        $data = $request->validate(['observaciones' => ['required', 'string'], 'avances' => ['nullable', 'string'], 'compromisos' => ['nullable', 'string']]);
        $anteriores = $sesion->only(['estado', 'observaciones', 'avances', 'compromisos']);
        $sesion->update($data + ['estado' => 'Realizada', 'registrada_en' => now()]);
        $this->historial($sesion, 'registro', null, $anteriores);
        return back()->with('success', 'Sesión registrada como realizada.');
    }

    public function noAsistio(Request $request, int $id)
    {
        $sesion = $this->sesionPropia($id);
        $data = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        $sesion->update(['estado' => 'No asistió', 'motivo' => $data['motivo'], 'registrada_en' => now()]);
        $this->historial($sesion, 'no_asistio', $data['motivo']);
        $this->avisar($sesion->acompanamiento, 'Sesión de acompañamiento actualizada', 'Una sesión requiere revisión.', 'inasistencia-'.$sesion->id_sesion);
        $this->notificaciones->crear(['id_usuario_receptor' => Auth::id(), 'id_usuario_causante' => Auth::id(), 'modulo' => 'acompañamiento', 'titulo' => 'Sesión sin asistencia', 'mensaje' => 'Una sesión de acompañamiento requiere revisión.', 'referencia_tipo' => 'acompañamiento', 'referencia_id' => $sesion->acompanamiento->id_acompanamiento, 'clave_evento' => 'acompanamiento-instructor-inasistencia-'.$sesion->id_sesion]);
        return back()->with('success', 'La sesión fue marcada como no asistida.');
    }

    public function reprogramar(Request $request, int $id)
    {
        $sesion = $this->sesionPropia($id);
        $data = $request->validate(['fecha' => ['required', 'date'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'], 'motivo' => ['required', 'string', 'max:2000']]);
        $this->validarCruce($sesion->acompanamiento->id_instructor, $data['fecha'], $data['hora_inicio'], $data['hora_fin'], $sesion->id_sesion);
        $anteriores = $sesion->only(['fecha', 'hora_inicio', 'hora_fin']);
        $sesion->update($data);
        $this->historial($sesion, 'reprogramacion', $data['motivo'], $anteriores);
        $this->avisar($sesion->acompanamiento, 'Sesión reprogramada', 'La fecha de una sesión de acompañamiento fue actualizada.', 'reprogramacion-'.$sesion->id_sesion);
        return back()->with('success', 'Sesión reprogramada.');
    }

    public function cancelar(Request $request, int $id)
    {
        $sesion = $this->sesionPropia($id);
        $data = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        $sesion->update(['estado' => 'Cancelada', 'motivo' => $data['motivo']]);
        $this->historial($sesion, 'cancelacion', $data['motivo']);
        $this->avisar($sesion->acompanamiento, 'Sesión cancelada', 'Una sesión de acompañamiento fue cancelada.', 'cancelacion-'.$sesion->id_sesion);
        return back()->with('success', 'Sesión cancelada.');
    }

    public function cerrar(Request $request, int $id)
    {
        $acompanamiento = $this->propio($id);
        if ($acompanamiento->sesiones()->whereIn('estado', ['Programada'])->exists()) {
            throw ValidationException::withMessages(['cierre' => 'Registra o cancela las sesiones pendientes antes de cerrar.']);
        }
        $data = $request->validate(['resumen_final' => ['required', 'string'], 'objetivo_cumplido' => ['required', 'in:Sí,No,Parcialmente']]);
        $acompanamiento->update($data + ['estado' => 'Finalizado']);
        AcompanamientoHistorial::create(['id_acompanamiento' => $id, 'id_usuario' => Auth::id(), 'accion' => 'cierre', 'datos_anteriores' => $data]);
        return back()->with('success', 'Acompañamiento finalizado.');
    }

    public function agregarSesiones(Request $request, int $id)
    {
        $acompanamiento = $this->propio($id);
        $data = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:52'], 'motivo' => ['required', 'string', 'max:2000']]);
        abort_if($acompanamiento->estado !== 'Activo', 422, 'El acompañamiento ya está finalizado.');
        $ultima = $acompanamiento->sesiones()->latest('fecha')->first();
        $inicio = Carbon::parse($ultima->fecha)->addDays($acompanamiento->intervalo_dias)->toDateString();
        $this->crearSesiones($acompanamiento, $inicio, $data['cantidad'], $acompanamiento->intervalo_dias);
        $acompanamiento->increment('total_sesiones', $data['cantidad']);
        AcompanamientoHistorial::create(['id_acompanamiento' => $id, 'id_usuario' => Auth::id(), 'accion' => 'sesiones_agregadas', 'motivo' => $data['motivo']]);
        return back()->with('success', 'Sesiones agregadas.');
    }

    private function propio(int $id): Acompanamiento
    {
        return Acompanamiento::where('id_acompanamiento', $id)->where('id_instructor', Auth::id())->firstOrFail();
    }
    private function sesionPropia(int $id): AcompanamientoSesion
    {
        $sesion = AcompanamientoSesion::with('acompanamiento')->findOrFail($id);
        abort_unless($sesion->acompanamiento->id_instructor === Auth::id(), 403);
        abort_if($sesion->acompanamiento->estado !== 'Activo' && $sesion->estado === 'Programada', 422, 'El acompañamiento ya está finalizado.');
        return $sesion;
    }
    private function historial(AcompanamientoSesion $sesion, string $accion, ?string $motivo = null, ?array $anteriores = null): void
    {
        AcompanamientoHistorial::create(['id_acompanamiento' => $sesion->id_acompanamiento, 'id_sesion' => $sesion->id_sesion, 'id_usuario' => Auth::id(), 'accion' => $accion, 'motivo' => $motivo, 'datos_anteriores' => $anteriores]);
    }
    private function avisar(Acompanamiento $acompanamiento, string $titulo, string $mensaje, string $evento): void
    {
        $this->notificaciones->crear(['id_usuario_receptor' => $acompanamiento->id_aprendiz, 'id_usuario_causante' => Auth::id(), 'modulo' => 'acompañamiento', 'titulo' => $titulo, 'mensaje' => $mensaje, 'referencia_tipo' => 'acompañamiento', 'referencia_id' => $acompanamiento->id_acompanamiento, 'clave_evento' => 'acompanamiento-'.$evento.'-'.$acompanamiento->id_acompanamiento]);
    }
}
