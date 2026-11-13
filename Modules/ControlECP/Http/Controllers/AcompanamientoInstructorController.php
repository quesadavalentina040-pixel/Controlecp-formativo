<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Modules\ControlECP\Entities\AcompanamientoCategoria;
use Modules\ControlECP\Entities\AcompanamientoEvidencia;
use Modules\ControlECP\Entities\AcompanamientoHistorial;
use Modules\ControlECP\Entities\AcompanamientoPedagogico;
use Modules\ControlECP\Entities\AcompanamientoSesion;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Rules\NotWeekendOrColombianHoliday;
use Modules\SICA\Entities\Apprentice;

class AcompanamientoInstructorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeInstructor();
        $request->validate([
            'mes' => ['nullable', 'date_format:Y-m'],
            'fecha' => ['nullable', 'date'],
            'aprendiz' => ['nullable', 'string', 'max:150'],
            'estado_color' => ['nullable', 'in:verde,amarillo,rojo'],
        ]);
        $month = Carbon::createFromFormat('Y-m', $request->input('mes', now()->format('Y-m')))->startOfMonth();
        $monthStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $monthEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $filtrarSesiones = function ($query) use ($request) {
            $query->when($request->filled('fecha'), fn ($sessions) => $sessions->whereDate('fecha', $request->input('fecha')));

            match ($request->input('estado_color')) {
                'verde' => $query->where('estado', 'Realizada'),
                'amarillo' => $query->where('estado', 'Programada')->whereDate('fecha', '>=', today()->toDateString()),
                'rojo' => $query->where(function ($sessions) {
                    $sessions->where('estado', 'No asistio')
                        ->orWhere(fn ($scheduled) => $scheduled->where('estado', 'Programada')->whereDate('fecha', '<', today()->toDateString()));
                }),
                default => null,
            };
        };

        $acompanamientosQuery = $this->owned()->with([
            'aprendiz.person',
            'sesiones',
            'sesiones.evidencias',
            'historial.usuario',
        ])->when($request->filled('aprendiz'), function ($query) use ($request) {
            $term = '%' . trim($request->input('aprendiz')) . '%';
            $query->whereHas('aprendiz', function ($learner) use ($term) {
                $learner->where('nickname', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhereHas('person', fn ($person) => $person
                        ->where('first_name', 'like', $term)
                        ->orWhere('first_last_name', 'like', $term)
                        ->orWhere('second_last_name', 'like', $term));
            });
        })->when($request->filled('fecha') || $request->filled('estado_color'), fn ($query) => $query->whereHas('sesiones', $filtrarSesiones));
        $acompanamientos = $acompanamientosQuery->orderByDesc('created_at')->paginate(10)->withQueryString();
        $acompanamientos->getCollection()->each(function ($acompanamiento) use ($request) {
            $fecha = $request->input('fecha');
            $estadoColor = $request->input('estado_color');
            $sesionesVisibles = $acompanamiento->sesiones->filter(function ($session) use ($fecha, $estadoColor) {
                if ($fecha && $session->fecha?->toDateString() !== $fecha) {
                    return false;
                }

                return match ($estadoColor) {
                    'verde' => $session->estado === 'Realizada',
                    'amarillo' => $session->estado === 'Programada' && !$session->fecha->lt(today()),
                    'rojo' => $session->estado === 'No asistio'
                        || ($session->estado === 'Programada' && $session->fecha->lt(today())),
                    default => true,
                };
            })->values();
            $acompanamiento->setAttribute('sesiones_visibles', $sesionesVisibles);
        });
        $aprendices = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.apprentice'))
            ->with('person')->orderBy('nickname')->get();
        $categoriasAcompanamiento = AcompanamientoCategoria::query()
            ->where('instructor_id', Auth::id())
            ->orderBy('nombre')
            ->get();
        $aprendicesPorPersona = Apprentice::with('course')
            ->whereIn('person_id', $acompanamientos->getCollection()->pluck('aprendiz.person_id')->filter()->unique())
            ->get()->keyBy('person_id');
        $sessionsCalendar = $this->ownedSessions()
            ->with(['acompanamiento.aprendiz.person', 'historial'])
            ->whereBetween('fecha', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('fecha')->orderBy('hora_inicio')->get()->groupBy(fn ($session) => $session->fecha->toDateString());
        $days = [];
        for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
            $sessions = $sessionsCalendar->get($day->toDateString(), collect());
            $days[] = [
                'date' => $day->copy(),
                'in_month' => $day->month === $month->month,
                'sessions' => $sessions,
                'session_groups' => $sessions->groupBy(function ($session) {
                    if ($session->estado === 'Realizada') {
                        return 'verde';
                    }
                    if ($session->estado === 'No asistio' || ($session->estado === 'Programada' && $session->fecha->lt(today()))) {
                        return 'rojo';
                    }
                    if ($session->estado === 'Programada') {
                        return 'amarillo';
                    }
                    return 'neutro';
                }),
            ];
        }

        $stats = [
            'activos' => $this->owned()->where('estado', 'Activo')->count(),
            'realizadas' => $this->ownedSessions()->where('estado', 'Realizada')->count(),
            'pendientes' => $this->ownedSessions()->where('estado', 'Programada')->count(),
            'no_asistio' => $this->ownedSessions()->where('estado', 'No asistio')->count(),
        ];

        return view('controlecp::instructor.acompaniamiento', compact(
            'acompanamientos',
            'aprendices',
            'categoriasAcompanamiento',
            'aprendicesPorPersona',
            'days',
            'month',
            'stats'
        ));
    }

    public function crearCategoria(Request $request)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);
        $nombre = trim($data['nombre']);
        $category = AcompanamientoCategoria::query()
            ->where('instructor_id', Auth::id())
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->first();

        if (!$category) {
            $category = AcompanamientoCategoria::create([
                'instructor_id' => Auth::id(),
                'nombre' => $nombre,
            ]);
        }

        return response()->json([
            'id' => $category->id,
            'nombre' => $category->nombre,
        ], $category->wasRecentlyCreated ? 201 : 200);
    }

    public function store(Request $request)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'aprendiz_id' => ['required', 'integer', 'exists:users,id'],
            'categoria' => ['required', 'string', 'max:100'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
            'fecha_inicio' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'duracion_horas' => ['required', 'integer', 'between:1,12'],
            'intervalo_dias' => ['required', 'integer', 'between:1,365'],
            'sesiones_planeadas' => ['required', 'integer', 'between:1,100'],
        ]);
        if (!User::whereKey($data['aprendiz_id'])
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.apprentice'))->exists()) {
            throw ValidationException::withMessages(['aprendiz_id' => 'El aprendiz seleccionado no es válido.']);
        }
        $horaFin = $this->calculateEndTime($data['hora_inicio'], (int) $data['duracion_horas']);
        $sessionDates = $this->buildWorkingSessionDates(
            Carbon::parse($data['fecha_inicio']),
            (int) $data['intervalo_dias'],
            (int) $data['sesiones_planeadas']
        );
        $acompanamientoId = DB::transaction(function () use ($data, $horaFin, $sessionDates) {
            User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            $activoExistente = $this->owned()
                ->where('aprendiz_id', $data['aprendiz_id'])
                ->where('estado', 'Activo')
                ->exists();
            if ($activoExistente) {
                throw ValidationException::withMessages([
                    'aprendiz_id' => 'Ya tienes un acompañamiento activo con este aprendiz.',
                ]);
            }

            foreach ($sessionDates as $date) {
                $this->assertNoScheduleConflict(
                    (int) Auth::id(),
                    $date,
                    $data['hora_inicio'],
                    $horaFin
                );
            }

            $acompanamiento = AcompanamientoPedagogico::create([
                'instructor_id' => Auth::id(),
                'aprendiz_id' => $data['aprendiz_id'],
                'categoria' => trim($data['categoria']),
                'motivo' => trim($data['motivo']),
                'fecha_inicio' => $data['fecha_inicio'],
                'intervalo_dias' => $data['intervalo_dias'],
                'sesiones_planeadas' => $data['sesiones_planeadas'],
                'estado' => 'Activo',
            ]);
            $sesionesCreadas = [];
            foreach ($sessionDates as $number => $date) {
                $session = $this->createSession($acompanamiento, $number + 1, $date, $data['hora_inicio'], $horaFin);
                $sesionesCreadas[] = $session;
                $this->log($acompanamiento, $session, 'Sesion creada', null, [
                    'fecha' => $session->fecha->toDateString(),
                    'hora_inicio' => $session->hora_inicio,
                    'hora_fin' => $session->hora_fin,
                ]);
            }
            $this->notifyLearnerSessions($acompanamiento, $sesionesCreadas, 'Tienes');
            $this->log($acompanamiento, null, 'Acompanamiento creado', null, [
                'categoria' => $acompanamiento->categoria,
                'fecha_inicio' => $acompanamiento->fecha_inicio->toDateString(),
                'intervalo_dias' => $acompanamiento->intervalo_dias,
                'sesiones_planeadas' => $acompanamiento->sesiones_planeadas,
            ]);

            return $acompanamiento->id;
        });

        return redirect()->route('controlecp.instructor.acompaniamiento')
            ->with('success', 'Acompañamiento creado y sesiones agendadas. Se notificó al aprendiz de la cantidad de sesiones y de la próxima fecha.');
    }

    public function actualizar(Request $request, int $accompanimentId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'categoria' => ['required', 'string', 'max:100'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $accompaniment = $this->owned()->findOrFail($accompanimentId);
        $before = [
            'categoria' => $accompaniment->categoria,
            'motivo' => $accompaniment->motivo,
        ];
        $accompaniment->update([
            'categoria' => trim($data['categoria']),
            'motivo' => trim($data['motivo']),
        ]);
        $this->log($accompaniment, null, 'Acompanamiento actualizado', null, [
            'antes' => $before,
            'despues' => [
                'categoria' => $accompaniment->categoria,
                'motivo' => $accompaniment->motivo,
            ],
        ]);

        return back()->with('success', 'Acompañamiento actualizado correctamente.');
    }

    public function registrarSesion(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'observaciones' => ['required', 'string', 'min:10', 'max:200'],
            'avances' => ['required', 'string', 'min:10', 'max:200'],
            'compromisos' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $session = $this->ownedSessions()->with('acompanamiento')->findOrFail($sessionId);
        $this->assertOpenScheduledSession($session);
        if ($session->fecha->isFuture()) {
            throw ValidationException::withMessages(['sesion' => 'No puedes registrar una sesión antes de la fecha programada.']);
        }
        DB::transaction(function () use ($session, $data) {
            $before = $this->sessionSnapshot($session);
            $session->update([
                'estado' => 'Realizada',
                'observaciones' => trim($data['observaciones']),
                'avances' => trim($data['avances']),
                'compromisos' => trim($data['compromisos']),
                'registrada_en' => now(),
                'motivo' => null,
            ]);
            $this->log($session->acompanamiento, $session, 'Sesion registrada', null, [
                'antes' => $before,
                'despues' => $this->sessionSnapshot($session->fresh()),
            ]);
        });

        return back()->with('success', 'Sesión registrada correctamente.');
    }

    public function noAsistio(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $data = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:200']]);
        $session = $this->ownedSessions()->with('acompanamiento')->findOrFail($sessionId);
        $this->assertOpenScheduledSession($session);
        if ($session->fecha->isFuture()) {
            throw ValidationException::withMessages(['sesion' => 'No puedes marcar inasistencia antes de la fecha programada.']);
        }
        DB::transaction(function () use ($session, $data) {
            $before = $this->sessionSnapshot($session);
            $session->update([
                'estado' => 'No asistio',
                'motivo' => trim($data['motivo']),
                'registrada_en' => now(),
            ]);
            $this->log($session->acompanamiento, $session, 'Aprendiz no asistio', $data['motivo'], [
                'antes' => $before,
                'despues' => $this->sessionSnapshot($session->fresh()),
            ]);
            $this->notifyInstructor($session->acompanamiento, 'El aprendiz no asistió a una sesión de acompañamiento.');
        });

        return back()->with('success', 'La sesión quedó marcada como “No asistió”.');
    }

    public function reprogramarSesion(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'duracion_horas' => ['required', 'integer', 'between:1,12'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $end = $this->calculateEndTime($data['hora_inicio'], (int) $data['duracion_horas']);
        $session = $this->ownedSessions()->with('acompanamiento')->findOrFail($sessionId);
        $this->assertOpenScheduledSession($session);
        $date = Carbon::parse($data['fecha']);
        $this->assertNoScheduleConflict((int) Auth::id(), $date, $data['hora_inicio'], $end, $session->id);

        DB::transaction(function () use ($session, $data, $end) {
            $before = $this->sessionSnapshot($session);
            $session->update([
                'fecha' => $data['fecha'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $end,
                'motivo' => trim($data['motivo']),
                'aviso_un_dia_enviado_en' => null,
                'aviso_sin_registro_enviado_en' => null,
            ]);
            $this->log($session->acompanamiento, $session, 'Sesion reprogramada', $data['motivo'], [
                'antes' => $before,
                'despues' => $this->sessionSnapshot($session->fresh()),
            ]);
            $this->notifyLearnerSession($session->acompanamiento, $session->fresh(), 'Sesión de acompañamiento reprogramada');
        });

        return back()->with('success', 'Sesión reprogramada; se notificó al aprendiz.');
    }

    public function cancelarSesion(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $data = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:200']]);
        $session = $this->ownedSessions()->with('acompanamiento')->findOrFail($sessionId);
        $this->assertOpenScheduledSession($session);
        DB::transaction(function () use ($session, $data) {
            $before = $this->sessionSnapshot($session);
            $session->update(['estado' => 'Cancelada', 'motivo' => trim($data['motivo'])]);
            $this->log($session->acompanamiento, $session, 'Sesion cancelada', $data['motivo'], [
                'antes' => $before,
                'despues' => $this->sessionSnapshot($session->fresh()),
            ]);
            $this->notifyLearnerSession($session->acompanamiento, $session, 'Sesión de acompañamiento cancelada');
        });
        return back()->with('success', 'Sesión cancelada y aprendiz notificado.');
    }

    public function corregirSesion(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'observaciones' => ['required', 'string', 'min:10', 'max:200'],
            'avances' => ['required', 'string', 'min:10', 'max:200'],
            'compromisos' => ['required', 'string', 'min:10', 'max:200'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $session = $this->ownedSessions()->with('acompanamiento')->findOrFail($sessionId);
        if ($session->estado !== 'Realizada') {
            throw ValidationException::withMessages(['sesion' => 'Solo puedes corregir el registro de una sesión realizada.']);
        }

        DB::transaction(function () use ($session, $data) {
            $before = $this->sessionSnapshot($session);
            $session->update([
                'observaciones' => trim($data['observaciones']),
                'avances' => trim($data['avances']),
                'compromisos' => trim($data['compromisos']),
            ]);
            $this->log($session->acompanamiento, $session, 'Registro corregido', $data['motivo'], [
                'antes' => $before,
                'despues' => $this->sessionSnapshot($session->fresh()),
            ]);
        });
        return back()->with('success', 'Corrección guardada; el contenido anterior quedó en el historial reservado.');
    }

    public function guardarEvidencia(Request $request, int $sessionId)
    {
        $this->authorizeInstructor();
        $session = $this->ownedSessions()->with('acompanamiento.aprendiz')->findOrFail($sessionId);
        if ($session->acompanamiento->estado !== 'Activo') {
            throw ValidationException::withMessages([
                'sesion' => 'Solo puedes agregar evidencias a sesiones de acompañamientos activos.',
            ]);
        }

        $data = $request->validate([
            'tipo' => ['required', 'in:foto,video'],
            'archivo' => ['required_if:tipo,foto', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'enlace' => ['required_if:tipo,video', 'nullable', 'url', 'max:500'],
            'descripcion' => ['nullable', 'string', 'min:10', 'max:200'],
        ], [
            'archivo.mimes' => 'Solo se aceptan fotos JPG o PNG.',
            'enlace.url' => 'Ingresa un enlace de video válido.',
        ]);

        $aprendiz = Apprentice::query()
            ->where('person_id', $session->acompanamiento->aprendiz->person_id)
            ->first();
        if (!$aprendiz?->course_id) {
            throw ValidationException::withMessages([
                'sesion' => 'El aprendiz no tiene una ficha asociada para guardar la evidencia.',
            ]);
        }

        $evidencia = new AcompanamientoEvidencia([
            'sesion_id' => $session->id,
            'course_id' => $aprendiz->course_id,
            'user_id' => Auth::id(),
            'tipo' => $data['tipo'],
            'enlace' => $data['enlace'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_registro' => now(),
        ]);
        if ($data['tipo'] === 'foto') {
            $archivo = $request->file('archivo')->store(
                'controlecp/evidencias/acompanamientos/' . $session->acompanamiento_id,
                'public'
            );
            if (!$archivo) {
                throw new RuntimeException('No se pudo guardar el archivo de evidencia.');
            }
            $evidencia->archivo = $archivo;
        }
        $evidencia->save();

        return redirect()->route('controlecp.instructor.acompaniamiento')
            ->with('success', 'Evidencia agregada al repositorio.')
            ->with('abrir_sesiones', $session->acompanamiento_id);
    }

    public function agregarSesiones(Request $request, int $accompanimentId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'between:1,100'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $accompaniment = $this->owned()->with('sesiones')->findOrFail($accompanimentId);
        if ($accompaniment->estado !== 'Activo') {
            throw ValidationException::withMessages(['acompanamiento' => 'No se pueden agregar sesiones a un acompañamiento finalizado.']);
        }
        if ($accompaniment->sesiones_planeadas + (int) $data['cantidad'] > 100) {
            throw ValidationException::withMessages(['cantidad' => 'El acompañamiento no puede superar las 100 sesiones.']);
        }
        DB::transaction(function () use ($accompaniment, $data) {
            $last = $accompaniment->sesiones->sortByDesc('numero')->first();
            $startDate = $last
                ? $last->fecha->copy()->addDays($accompaniment->intervalo_dias)
                : $accompaniment->fecha_inicio->copy();
            $sessionDates = $this->buildWorkingSessionDates(
                $startDate,
                (int) $accompaniment->intervalo_dias,
                (int) $data['cantidad']
            );
            $numberStart = $last ? $last->numero + 1 : 1;
            $hourStart = $last?->hora_inicio ?? '09:00:00';
            $hourEnd = $last?->hora_fin ?? '10:00:00';
            $sesionesAgregadas = [];
            foreach ($sessionDates as $index => $date) {
                $this->assertNoScheduleConflict((int) Auth::id(), $date, $hourStart, $hourEnd);
                $session = $this->createSession($accompaniment, $numberStart + $index, $date, $hourStart, $hourEnd);
                $sesionesAgregadas[] = $session;
                $this->log($accompaniment, $session, 'Sesion agregada', $data['motivo'], [
                    'fecha' => $session->fecha->toDateString(),
                    'hora_inicio' => $session->hora_inicio,
                    'hora_fin' => $session->hora_fin,
                ]);
            }
            $this->notifyLearnerSessions($accompaniment, $sesionesAgregadas, 'Se agregaron');
            $accompaniment->increment('sesiones_planeadas', (int) $data['cantidad']);
            $this->log($accompaniment, null, 'Sesiones ampliadas', $data['motivo'], [
                'cantidad_agregada' => (int) $data['cantidad'],
                'total' => $accompaniment->fresh()->sesiones_planeadas,
            ]);
        });
        return back()->with('success', 'Sesiones agregadas al acompañamiento; se notificó un resumen al aprendiz.');
    }

    public function cerrar(Request $request, int $accompanimentId)
    {
        $this->authorizeInstructor();
        $data = $request->validate([
            'resumen_final' => ['required', 'string', 'min:10', 'max:200'],
            'objetivo_cumplido' => ['required', 'in:0,1'],
        ]);
        $accompaniment = $this->owned()->with('sesiones')->findOrFail($accompanimentId);
        if ($accompaniment->estado !== 'Activo') {
            throw ValidationException::withMessages(['acompanamiento' => 'El acompañamiento ya está finalizado.']);
        }
        $pending = $accompaniment->sesiones->where('estado', 'Programada')->count();
        if ($pending > 0) {
            throw ValidationException::withMessages([
                'acompanamiento' => "No se puede cerrar: quedan {$pending} sesiones pendientes. Regístralas, marca la inasistencia o cancélalas primero.",
            ]);
        }
        DB::transaction(function () use ($accompaniment, $data) {
            $accompaniment->update([
                'estado' => 'Finalizado',
                'resumen_final' => trim($data['resumen_final']),
                'objetivo_cumplido' => $data['objetivo_cumplido'] === '1',
                'cerrado_en' => now(),
            ]);
            $this->log($accompaniment, null, 'Acompanamiento finalizado', null, [
                'resumen_final' => $accompaniment->resumen_final,
                'objetivo_cumplido' => $accompaniment->objetivo_cumplido,
            ]);
        });
        return back()->with('success', 'Acompañamiento finalizado.');
    }

    private function buildWorkingSessionDates(Carbon $start, int $intervalDays, int $count): array
    {
        $calendar = app(\Modules\ControlECP\Services\ColombianWorkingDayService::class);
        $dates = [];
        $date = $start->copy();

        for ($index = 0; $index < $count; $index++) {
            if ($index > 0 && $date->lte($dates[$index - 1])) {
                $date = $dates[$index - 1]->copy()->addDay();
            }

            $attempts = 0;
            while (! $calendar->isWorkingDay($date->toDateString())) {
                if ($attempts++ >= 7) {
                    throw ValidationException::withMessages([
                        'fecha_inicio' => 'No se pudo calcular una fecha hábil para todas las sesiones.',
                    ]);
                }
                $date->addDay();
            }

            $dates[] = $date->copy();
            $date->addDays($intervalDays);
        }

        return $dates;
    }

    private function authorizeInstructor(): void
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);
    }

    private function owned(): Builder
    {
        return AcompanamientoPedagogico::query()->where('instructor_id', Auth::id());
    }

    private function ownedSessions(): Builder
    {
        return AcompanamientoSesion::query()->whereHas('acompanamiento', fn ($query) => $query->where('instructor_id', Auth::id()));
    }

    private function assertOpenScheduledSession(AcompanamientoSesion $session): void
    {
        if ($session->acompanamiento->estado !== 'Activo' || $session->estado !== 'Programada') {
            throw ValidationException::withMessages(['sesion' => 'La sesión ya no está disponible para esta acción.']);
        }
    }

    private function calculateEndTime(string $start, int $durationHours): string
    {
        $startTime = Carbon::createFromFormat('H:i', $start);
        $endMinutes = ((int) $startTime->format('H') * 60) + (int) $startTime->format('i') + ($durationHours * 60);
        if ($endMinutes > 15 * 60) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La sesión debe finalizar como máximo a las 3:00 p. m. Ajusta la hora de inicio o la duración.',
            ]);
        }

        return $startTime->addHours($durationHours)->format('H:i:s');
    }

    private function assertNoScheduleConflict(
        int $instructorId,
        Carbon $date,
        string $start,
        string $end,
        ?int $exceptSessionId = null
    ): void {
        $activityConflict = DB::table('cronograma')
            ->where('id_instructor', $instructorId)
            ->whereDate('fecha', $date->toDateString())
            ->where('estado', '!=', 'Cancelada')
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
        $appointmentConflict = Asesoria::where('id_usuario_instructor', $instructorId)
            ->whereDate('fecha', $date->toDateString())
            ->where('estado', '!=', 'Cancelada')
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
        $accompanimentConflict = $this->ownedSessions()
            ->whereDate('fecha', $date->toDateString())
            ->where('estado', 'Programada')
            ->when($exceptSessionId, fn ($query) => $query->where('id', '!=', $exceptSessionId))
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
        if ($activityConflict || $appointmentConflict || $accompanimentConflict) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La sesión se cruza con otra actividad, asesoría o sesión de acompañamiento ya programada.',
            ]);
        }
    }

    private function createSession(
        AcompanamientoPedagogico $accompaniment,
        int $number,
        Carbon $date,
        string $start,
        string $end
    ): AcompanamientoSesion {
        return AcompanamientoSesion::create([
            'acompanamiento_id' => $accompaniment->id,
            'numero' => $number,
            'fecha' => $date->toDateString(),
            'hora_inicio' => $start,
            'hora_fin' => $end,
            'estado' => 'Programada',
        ]);
    }

    private function log(
        AcompanamientoPedagogico $accompaniment,
        ?AcompanamientoSesion $session,
        string $action,
        ?string $reason,
        ?array $data
    ): void {
        AcompanamientoHistorial::create([
            'acompanamiento_id' => $accompaniment->id,
            'sesion_id' => $session?->id,
            'usuario_id' => Auth::id(),
            'accion' => $action,
            'motivo' => $reason,
            'datos' => $data,
        ]);
    }

    private function sessionSnapshot(AcompanamientoSesion $session): array
    {
        return [
            'numero' => $session->numero,
            'fecha' => $session->fecha?->toDateString(),
            'hora_inicio' => $session->hora_inicio,
            'hora_fin' => $session->hora_fin,
            'estado' => $session->estado,
            'observaciones' => $session->observaciones,
            'avances' => $session->avances,
            'compromisos' => $session->compromisos,
            'motivo' => $session->motivo,
        ];
    }

    private function notifyLearnerSession(
        AcompanamientoPedagogico $accompaniment,
        AcompanamientoSesion $session,
        string $title
    ): void {
        $message = match ($session->estado) {
            'Cancelada' => "La sesión de acompañamiento #{$session->numero} del {$session->fecha->format('d/m/Y')} de " .
                substr($session->hora_inicio, 0, 5) . ' a ' . substr($session->hora_fin, 0, 5) . ' fue cancelada.',
            default => "Tienes una sesión de acompañamiento #{$session->numero} el {$session->fecha->format('d/m/Y')} de " .
                substr($session->hora_inicio, 0, 5) . ' a ' . substr($session->hora_fin, 0, 5) . '.',
        };
        $this->notify(
            (int) $accompaniment->aprendiz_id,
            $title,
            $message,
            $accompaniment,
            'acompanamiento-sesion-' . $session->id . '-' . $session->estado . '-' . $session->updated_at?->timestamp
        );
    }

    private function notifyLearnerSessions(
        AcompanamientoPedagogico $accompaniment,
        array $sessions,
        string $intro
    ): void {
        $nextSession = $sessions[0];
        $count = count($sessions);
        $nextSession->update(['aviso_aprendiz_enviado_en' => now()]);

        $countText = $count === 1 ? '1 sesión' : "{$count} sesiones";
        $summary = $intro === 'Tienes'
            ? "Tienes {$countText} de acompañamiento por hacer."
            : ($count === 1 ? 'Se agregó 1 sesión de acompañamiento.' : "Se agregaron {$count} sesiones de acompañamiento.");
        $message = $summary . ' La próxima es la sesión ' .
            "#{$nextSession->numero} el {$nextSession->fecha->format('d/m/Y')} de " .
            substr($nextSession->hora_inicio, 0, 5) . ' a ' . substr($nextSession->hora_fin, 0, 5) . '.';

        $this->notify(
            (int) $accompaniment->aprendiz_id,
            'Resumen de sesiones de acompañamiento',
            $message,
            $accompaniment,
            'acompanamiento-resumen-' . $accompaniment->id . '-' . $nextSession->id . '-' . now()->format('YmdHisv')
        );
    }

    private function notifyInstructor(AcompanamientoPedagogico $accompaniment, string $message): void
    {
        $this->notify(
            (int) $accompaniment->instructor_id,
            'Aviso de acompañamiento',
            $message,
            $accompaniment,
            'acompanamiento-aviso-' . $accompaniment->id . '-' . now()->format('YmdHisv')
        );
    }

    private function notify(
        int $receiverId,
        string $title,
        string $message,
        AcompanamientoPedagogico $accompaniment,
        string $key
    ): void {
        Notificacion::crear(
            idReceptor: $receiverId,
            idCausante: (int) Auth::id(),
            titulo: $title,
            mensaje: $message,
            referenciaTipo: 'acompanamiento',
            referenciaId: (int) $accompaniment->id,
            claveEvento: $key
        );
    }
}
