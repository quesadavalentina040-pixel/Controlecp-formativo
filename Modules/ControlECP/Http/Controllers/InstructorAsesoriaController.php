<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\AsesoriaHistorial;
use Modules\ControlECP\Entities\AsesoriaRecurso;
use Modules\ControlECP\Entities\AsesoriaSeguimiento;
use Modules\ControlECP\Entities\CategoriaAsesoria;
use Modules\ControlECP\Entities\DisponibilidadInstructor;
use Modules\ControlECP\Entities\InstructorCategoria;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Rules\NotWeekendOrColombianHoliday;
use Modules\SICA\Entities\Apprentice;

class InstructorAsesoriaController extends Controller
{
    private function authorizeRole(string $role)
    {
        if (!Auth::check() || !Auth::user()->hasRole($role)) {
            return redirect()->route('controlecp.index')->with('error', 'No tienes permisos para acceder a las asesorías.');
        }

        return null;
    }

    private function instructorAppointments()
    {
        return Asesoria::query()->where('id_usuario_instructor', Auth::id());
    }

    private function instructorVisibleAppointments()
    {
        return $this->excludeAdminCreated($this->instructorAppointments());
    }

    private function excludeAdminCreated($query)
    {
        return $query->whereDoesntHave('usuarioRegistro.roles', function ($roles) {
            $roles->where(function ($roleQuery) {
                $roleQuery->whereIn('slug', ['controlecp.admin', 'superadmin'])
                    ->orWhere('full_access', 'Si');
            });
        });
    }

    private function responsableAsignadoCategoria(CategoriaAsesoria $categoria, User $responsable): bool
    {
        $asignado = InstructorCategoria::where('id_usuario', $responsable->id)
            ->where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
            ->exists();
        $tieneDisponibilidadCategoria = DisponibilidadInstructor::where('id_usuario', $responsable->id)
            ->where('vigente', true)
            ->where('habilitada', true)
            ->where(function ($query) use ($categoria) {
                $query->whereNull('id_categoria_asesoria')
                    ->orWhere('id_categoria_asesoria', $categoria->id_categoria_asesoria);
            })
            ->exists();
        $seleccionoCategoria = DB::table('disponibilidad_categoria_usuario')
            ->where('id_usuario', $responsable->id)
            ->where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
            ->exists();

        if ($this->isAdministrator($responsable)) {
            return (int) $categoria->id_administrador === (int) $responsable->id
                || $asignado || $seleccionoCategoria || $tieneDisponibilidadCategoria;
        }

        return $responsable->hasRole('controlecp.instructor')
            && ($asignado || $seleccionoCategoria || $tieneDisponibilidadCategoria);
    }

    public function indexInstructor(Request $request)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;

        $filters = $request->validate([
            'categoria_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:Solicitada,Programada,Realizada,Cancelada,Rechazada'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'aprendiz' => ['nullable', 'string', 'max:100'],
            'anio' => ['nullable', 'integer', 'min:2024', 'max:2035'],
            'trimestre' => ['nullable', 'integer', 'between:1,4'],
            'calendario_mes' => ['nullable', 'date_format:Y-m'],
            'vista' => ['nullable', 'in:calendario,solicitudes'],
        ]);
        $calendarMonth = Carbon::createFromFormat('Y-m', $filters['calendario_mes'] ?? now()->format('Y-m'))
            ->startOfMonth();
        $calendarStart = $calendarMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $calendarDays = [];
        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $calendarDays[] = $day->copy();
        }
        $calendarMonthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $calendarTitle = $calendarMonthNames[$calendarMonth->month] . ' De ' . $calendarMonth->year;
        $calendarPreviousMonth = $calendarMonth->copy()->subMonth()->format('Y-m');
        $calendarNextMonth = $calendarMonth->copy()->addMonth()->format('Y-m');
        $calendarCurrentMonth = now()->format('Y-m');
        $query = $this->instructorVisibleAppointments()->with([
            'categoria', 'aprendiz.person', 'instructor', 'calificacion.aprendiz', 'historial.usuario', 'seguimientos.usuario',
            'seguimientos.correcciones', 'recursos.inventario',
        ]);
        if (!empty($filters['categoria_id'])) $query->where('id_categoria_asesoria', $filters['categoria_id']);
        if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);
        if (!empty($filters['fecha_desde'])) $query->whereDate('fecha', '>=', $filters['fecha_desde']);
        if (!empty($filters['fecha_hasta'])) $query->whereDate('fecha', '<=', $filters['fecha_hasta']);
        if (!empty($filters['anio']) && !empty($filters['trimestre'])) {
            [$from, $to] = $this->quarterRange((int) $filters['anio'], (int) $filters['trimestre']);
            $query->whereBetween('fecha', [$from->toDateString(), $to->toDateString()]);
        }
        if (!empty($filters['aprendiz'])) {
            $term = trim($filters['aprendiz']);
            $query->whereHas('aprendiz', function ($userQuery) use ($term) {
                $userQuery->where('nickname', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('person', function ($personQuery) use ($term) {
                        $personQuery->where('first_name', 'like', "%{$term}%")
                            ->orWhere('first_last_name', 'like', "%{$term}%")
                            ->orWhere('second_last_name', 'like', "%{$term}%")
                            ->orWhere('document_number', 'like', "%{$term}%");
                    });
            });
        }
        $calendarAppointments = (clone $query)
            ->whereBetween('fecha', [$calendarMonth->toDateString(), $calendarMonth->copy()->endOfMonth()->toDateString()])
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy(fn ($appointment) => $appointment->fecha?->format('Y-m-d'));
        $asesorias = $query->orderByDesc('fecha')->orderByDesc('hora_inicio')->paginate(10)->withQueryString();
        $categorias = CategoriaAsesoria::where('estado', 'Activa')->orderBy('nombre')->get();
        $categoriaDisponibilidadSeleccionada = DB::table('disponibilidad_categoria_usuario')
            ->where('id_usuario', Auth::id())
            ->value('id_categoria_asesoria')
            ?? DisponibilidadInstructor::where('id_usuario', Auth::id())
                ->where('vigente', true)->orderByDesc('id_disponibilidad_instructor')
                ->value('id_categoria_asesoria');
        $today = today();
        $trimestreActual = (int) ceil($today->month / 3);
        $anioActual = $today->year;
        $disponibilidadActual = DisponibilidadInstructor::where('id_usuario', Auth::id())
            ->where('anio', $anioActual)->where('trimestre', $trimestreActual)
            ->where('vigente', true)->with('categoria')->orderBy('dia_semana')->orderBy('hora_inicio')->get();
        $aprendices = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.apprentice'))
            ->with('person')->orderBy('nickname')->get();
        $aprendicesPorPersona = Apprentice::with('course')
            ->whereIn('person_id', $asesorias->getCollection()->pluck('aprendiz.person_id')->filter()->unique())
            ->get()->keyBy('person_id');
        $historialAprendices = $this->excludeAdminCreated(Asesoria::with('categoria'))
            ->whereIn('id_usuario_aprendiz', $asesorias->getCollection()->pluck('id_usuario_aprendiz')->unique())
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')->get()
            ->groupBy('id_usuario_aprendiz');
        $recursos = Inventario::query()->where('estado', 'Disponible')
            ->where('cantidad_disponible', '>', 0)->orderBy('nombre')->get();
        $stats = [
            'programadas' => $this->instructorVisibleAppointments()->where('estado', 'Programada')->count(),
            'realizadas' => $this->instructorVisibleAppointments()->where('estado', 'Realizada')->count(),
            'canceladas' => $this->instructorVisibleAppointments()->where('estado', 'Cancelada')->count(),
        ];
        return view('controlecp::instructor.asesorias', compact(
            'asesorias', 'categorias', 'disponibilidadActual', 'categoriaDisponibilidadSeleccionada',
            'aprendices', 'aprendicesPorPersona', 'historialAprendices', 'recursos',
            'stats', 'filters', 'calendarDays', 'calendarAppointments', 'calendarTitle',
            'calendarMonth', 'calendarPreviousMonth', 'calendarNextMonth', 'calendarCurrentMonth'
        ));
    }

    public function indexAprendiz(Request $request)
    {
        if ($response = $this->authorizeRole('controlecp.apprentice')) return $response;
        $filtros = $request->validate([
            'fecha' => ['nullable', 'date'],
            'asesoria' => ['nullable', 'string', 'max:120'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'estado' => ['nullable', 'in:Solicitada,Programada,Realizada,Cancelada,Rechazada'],
        ]);
        $query = $this->excludeAdminCreated(Asesoria::with([
            'categoria', 'instructor.person', 'historial',
        ]))->where('id_usuario_aprendiz', Auth::id());
        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha', $filtros['fecha']);
        }
        if (!empty($filtros['asesoria'])) {
            $termino = trim($filtros['asesoria']);
            $query->where(function ($asesoriaQuery) use ($termino) {
                $asesoriaQuery->where('motivo', 'like', "%{$termino}%")
                    ->orWhereHas('instructor', function ($instructorQuery) use ($termino) {
                        $instructorQuery->where('nickname', 'like', "%{$termino}%")
                            ->orWhere('email', 'like', "%{$termino}%")
                            ->orWhereHas('person', function ($personQuery) use ($termino) {
                                $personQuery->where('first_name', 'like', "%{$termino}%")
                                    ->orWhere('first_last_name', 'like', "%{$termino}%")
                                    ->orWhere('second_last_name', 'like', "%{$termino}%");
                            });
                    });
            });
        }
        if (!empty($filtros['categoria_id'])) {
            $query->where('id_categoria_asesoria', $filtros['categoria_id']);
        }
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        $asesorias = $query->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->paginate(10)->withQueryString();
        $calendarioAsesorias = Asesoria::with(['categoria', 'instructor.person'])
            ->where('id_usuario_aprendiz', Auth::id())
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get()
            ->map(fn (Asesoria $asesoria) => [
                'id' => $asesoria->id_asesoria,
                'fecha' => $asesoria->fecha?->format('Y-m-d'),
                'inicio' => substr($asesoria->hora_inicio, 0, 5),
                'fin' => substr($asesoria->hora_fin, 0, 5),
                'categoria' => $asesoria->categoria?->nombre ?? 'Sin categoría',
                'responsable' => $asesoria->instructor?->person?->full_name ?? $asesoria->instructor?->nickname ?? 'Pendiente de asignación',
                'estado' => $asesoria->estado,
                'motivo' => $asesoria->motivo,
            ]);
        $solicitudesAprendiz = $this->excludeAdminCreated(
            Asesoria::where('id_usuario_aprendiz', Auth::id())
        );
        $totales = [
            'total' => (clone $solicitudesAprendiz)->count(),
            'aceptadas' => (clone $solicitudesAprendiz)->whereIn('estado', ['Programada', 'Realizada'])->count(),
            'rechazadas' => (clone $solicitudesAprendiz)->where('estado', 'Rechazada')->count(),
        ];
        $categorias = CategoriaAsesoria::where('estado', 'Activa')->orderBy('nombre')->get();
        return view('controlecp::aprendiz.asesorias', compact('asesorias', 'calendarioAsesorias', 'categorias', 'totales', 'filtros'));
    }

    public function guardarDisponibilidad(Request $request)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate([
            'anio' => ['required', 'integer', 'min:2024', 'max:2035'],
            'trimestre' => ['required', 'integer', 'between:1,4'],
            'franjas' => ['required', 'array', 'min:1'],
            'franjas.*.dia_semana' => ['required', 'integer', 'between:1,5'],
            'franjas.*.hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'franjas.*.hora_fin' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before_or_equal:15:00'],
        ]);
        $categoriaId = DB::table('disponibilidad_categoria_usuario')
            ->where('id_usuario', Auth::id())->value('id_categoria_asesoria');
        if (!$categoriaId || !CategoriaAsesoria::where('id_categoria_asesoria', $categoriaId)->where('estado', 'Activa')->exists()) {
            throw ValidationException::withMessages([
                'id_categoria_asesoria' => 'Selecciona y guarda una categoría activa antes de registrar disponibilidad.',
            ]);
        }
        $slots = collect($data['franjas'])->map(fn ($slot) => [
            'dia_semana' => (int) $slot['dia_semana'],
            'hora_inicio' => $slot['hora_inicio'],
            'hora_fin' => $slot['hora_fin'],
        ]);
        foreach ($slots as $index => $slot) {
            if ($slot['hora_fin'] <= $slot['hora_inicio']) {
                throw ValidationException::withMessages(['franjas' => 'La hora final de cada franja debe ser posterior a la hora inicial.']);
            }
            foreach ($slots->slice($index + 1) as $other) {
                if ($slot['dia_semana'] === $other['dia_semana']
                    && $slot['hora_inicio'] < $other['hora_fin']
                    && $slot['hora_fin'] > $other['hora_inicio']) {
                    throw ValidationException::withMessages(['franjas' => 'Las franjas del mismo día no pueden cruzarse.']);
                }
            }
        }
        [$from, $to] = $this->quarterRange((int) $data['anio'], (int) $data['trimestre']);
        foreach ($slots as $slot) {
            $conflict = DB::table('cronograma')
                ->where('id_instructor', Auth::id())
                ->whereBetween('fecha', [$from->toDateString(), $to->toDateString()])
                ->where('estado', '!=', 'Cancelada')
                ->whereRaw('DAYOFWEEK(fecha) = ?', [$slot['dia_semana'] === 7 ? 1 : $slot['dia_semana'] + 1])
                ->where('hora_inicio', '<', $slot['hora_fin'])
                ->where('hora_fin', '>', $slot['hora_inicio'])
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['franjas' => 'Una franja coincide con una actividad o sesión programada en el cronograma.']);
            }
            $conflict = $this->instructorAppointments()
                ->whereBetween('fecha', [$from->toDateString(), $to->toDateString()])
                ->whereRaw('DAYOFWEEK(fecha) = ?', [$slot['dia_semana'] === 7 ? 1 : $slot['dia_semana'] + 1])
                ->whereNotIn('estado', ['Cancelada', 'Rechazada'])
                ->where('hora_inicio', '<', $slot['hora_fin'])
                ->where('hora_fin', '>', $slot['hora_inicio'])
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['franjas' => 'Una franja coincide con una asesoría ya programada.']);
            }
        }
        DB::transaction(function () use ($data, $slots, $categoriaId) {
            DisponibilidadInstructor::where('id_usuario', Auth::id())
                ->where('anio', $data['anio'])->where('trimestre', $data['trimestre'])
                ->where('vigente', true)->update(['vigente' => false]);
            foreach ($slots as $slot) {
                DisponibilidadInstructor::create([
                    'id_usuario' => Auth::id(),
                    'id_categoria_asesoria' => (int) $categoriaId,
                    'anio' => $data['anio'],
                    'trimestre' => $data['trimestre'],
                    'dia_semana' => $slot['dia_semana'],
                    'hora_inicio' => $slot['hora_inicio'],
                    'hora_fin' => $slot['hora_fin'],
                    'habilitada' => true,
                    'vigente' => true,
                    'fecha_registro' => now(),
                ]);
            }
        });
        return back()->with('success', 'Disponibilidad trimestral guardada. La versión anterior quedó en el historial.');
    }

    public function guardarCategoriaDisponibilidad(Request $request)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;

        $data = $request->validate([
            'id_categoria_asesoria' => ['required', 'integer', 'exists:categorias_asesoria,id_categoria_asesoria'],
        ]);
        if (!CategoriaAsesoria::where('id_categoria_asesoria', $data['id_categoria_asesoria'])
            ->where('estado', 'Activa')->exists()) {
            return back()->withErrors(['id_categoria_asesoria' => 'Selecciona una categoría activa.'])
                ->withInput()->with('modal_abrir', 'modalDisponibilidadInstructor');
        }

        DB::transaction(function () use ($data) {
            DB::table('disponibilidad_categoria_usuario')->updateOrInsert(
                ['id_usuario' => Auth::id()],
                ['id_categoria_asesoria' => (int) $data['id_categoria_asesoria']]
            );
            DisponibilidadInstructor::where('id_usuario', Auth::id())
                ->where('vigente', true)
                ->update(['id_categoria_asesoria' => (int) $data['id_categoria_asesoria']]);
        });

        return back()->with('success', 'La categoría de tu disponibilidad fue guardada; tus días y horarios no cambiaron.')
            ->with('modal_abrir', 'modalDisponibilidadInstructor');
    }

    public function guardarFranjaDisponibilidad(Request $request)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;

        $validator = Validator::make($request->all(), [
            'dia_semana' => ['required', 'integer', 'between:1,5'],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'],
        ], [
            'hora_inicio.after_or_equal' => 'La disponibilidad debe iniciar a partir de las 8:00 a. m.',
            'hora_inicio.before' => 'La disponibilidad debe iniciar antes de las 3:00 p. m.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'hora_fin.before_or_equal' => 'La disponibilidad debe finalizar como máximo a las 3:00 p. m.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal_abrir', 'modalDisponibilidadInstructor');
        }

        $categoriaId = DB::table('disponibilidad_categoria_usuario')
            ->where('id_usuario', Auth::id())->value('id_categoria_asesoria');
        if (!$categoriaId || !CategoriaAsesoria::where('id_categoria_asesoria', $categoriaId)->where('estado', 'Activa')->exists()) {
            return back()->withErrors(['id_categoria_asesoria' => 'Selecciona y guarda una categoría activa antes de agregar franjas.'])
                ->withInput()->with('modal_abrir', 'modalDisponibilidadInstructor');
        }

        $today = today();
        $year = $today->year;
        $quarter = (int) ceil($today->month / 3);
        if ($this->availabilityHasConflict($year, $quarter, (int) $request->dia_semana, $request->hora_inicio, $request->hora_fin)) {
            return back()->withErrors([
                'disponibilidad' => 'La franja se cruza con otra franja, asesoría o actividad programada.',
            ])->withInput()->with('modal_abrir', 'modalDisponibilidadInstructor');
        }

        DisponibilidadInstructor::create([
            'id_usuario' => Auth::id(),
            'id_categoria_asesoria' => (int) $categoriaId,
            'anio' => $year,
            'trimestre' => $quarter,
            'dia_semana' => (int) $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'habilitada' => true,
            'vigente' => true,
            'fecha_registro' => now(),
        ]);

        return back()->with('success', 'La franja de disponibilidad fue agregada.')
            ->with('modal_abrir', 'modalDisponibilidadInstructor');
    }

    public function actualizarFranjaDisponibilidad(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;

        $slot = DisponibilidadInstructor::where('id_disponibilidad_instructor', $id)
            ->where('id_usuario', Auth::id())
            ->where('vigente', true)
            ->firstOrFail();
        $validator = Validator::make($request->all(), [
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'],
        ], [
            'hora_inicio.after_or_equal' => 'La disponibilidad debe iniciar a partir de las 8:00 a. m.',
            'hora_inicio.before' => 'La disponibilidad debe iniciar antes de las 3:00 p. m.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'hora_fin.before_or_equal' => 'La disponibilidad debe finalizar como máximo a las 3:00 p. m.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('modal_abrir', 'modalEditarDisponibilidadInstructor')
                ->with('modal_id', $id);
        }

        if ($this->availabilityHasConflict(
            (int) $slot->anio,
            (int) $slot->trimestre,
            (int) $slot->dia_semana,
            $request->hora_inicio,
            $request->hora_fin,
            $id
        )) {
            return back()->withErrors([
                'disponibilidad' => 'El horario se cruza con otra franja, asesoría o actividad programada.',
            ])->withInput()->with('modal_abrir', 'modalEditarDisponibilidadInstructor')->with('modal_id', $id);
        }

        $slot->update([
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
        ]);

        return back()->with('success', 'El horario de disponibilidad fue actualizado.')
            ->with('modal_abrir', 'modalDisponibilidadInstructor');
    }

    public function cambiarEstadoFranjaDisponibilidad(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;

        $data = $request->validate([
            'habilitada' => ['required', 'boolean'],
        ]);
        $slot = DisponibilidadInstructor::where('id_disponibilidad_instructor', $id)
            ->where('id_usuario', Auth::id())
            ->where('vigente', true)
            ->firstOrFail();

        if ((bool) $data['habilitada'] && !$slot->habilitada && $this->availabilityHasConflict(
            (int) $slot->anio,
            (int) $slot->trimestre,
            (int) $slot->dia_semana,
            substr($slot->hora_inicio, 0, 5),
            substr($slot->hora_fin, 0, 5),
            $id
        )) {
            return back()->withErrors([
                'disponibilidad' => 'No se puede activar la franja porque se cruza con otra franja, asesoría o actividad programada.',
            ])->with('modal_abrir', 'modalDisponibilidadInstructor');
        }

        $slot->habilitada = (bool) $data['habilitada'];
        $slot->save();

        return back()->with('success', $slot->habilitada
            ? 'La franja de disponibilidad fue activada.'
            : 'La franja de disponibilidad fue desactivada.')
            ->with('modal_abrir', 'modalDisponibilidadInstructor');
    }

    public function instructoresDisponibles(Request $request)
    {
        if (!Auth::check() || (!Auth::user()->hasRole('controlecp.instructor') && !Auth::user()->hasRole('controlecp.apprentice'))) {
            return response()->json(['success' => false, 'mensaje' => 'No tienes permisos para consultar disponibilidad.'], 403);
        }
        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'fecha' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'],
        ]);
        if (!CategoriaAsesoria::where('id_categoria_asesoria', $data['categoria_id'])
            ->where('estado', 'Activa')->exists()) {
            throw ValidationException::withMessages([
                'categoria_id' => 'La categoría seleccionada no está activa.',
            ]);
        }
        $fecha = Carbon::parse($data['fecha']);
        if ($fecha->isToday() && $data['hora_inicio'] <= now()->format('H:i')) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La hora de inicio debe ser posterior a la hora actual.',
            ]);
        }
        $instructorIds = InstructorCategoria::where('id_categoria_asesoria', $data['categoria_id'])->pluck('id_usuario');
        $available = User::whereIn('id', $instructorIds)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.instructor'))
            ->with('person')->get()
            ->filter(fn (User $user) => $this->instructorHasAvailability(
                (int) $user->id, $fecha, $data['hora_inicio'], $data['hora_fin'], (int) $data['categoria_id']
            ) && !$this->hasScheduleConflict((int) $user->id, $fecha, $data['hora_inicio'], $data['hora_fin']))
            ->map(fn (User $user) => ['id' => $user->id, 'nombre' => $user->full_name, 'email' => $user->email])
            ->values();

        return response()->json([
            'success' => true,
            'instructores' => $available,
            'mensaje' => $available->isEmpty() ? 'No hay instructores disponibles en esa fecha y franja. Elige otra fecha.' : null,
        ]);
    }

    public function responsablesPorCategoria(Request $request)
    {
        if (!Auth::check() || !Auth::user()->hasRole('controlecp.apprentice')) {
            return response()->json(['success' => false, 'mensaje' => 'No tienes permisos para consultar responsables.'], 403);
        }

        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'],
        ]);
        $categoria = CategoriaAsesoria::where('id_categoria_asesoria', $data['categoria_id'])
            ->where('estado', 'Activa')->first();
        if (!$categoria) {
            throw ValidationException::withMessages([
                'categoria_id' => 'La categoría seleccionada no está activa.',
            ]);
        }

        $responsableIds = InstructorCategoria::where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
            ->pluck('id_usuario')
            ->concat(DB::table('disponibilidad_categoria_usuario')
                ->where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
                ->pluck('id_usuario'))
            ->concat(DisponibilidadInstructor::where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
                ->where('vigente', true)->where('habilitada', true)->pluck('id_usuario'))
            ->unique()
            ->values();
        $instructores = User::whereIn('id', $responsableIds)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.instructor'))
            ->orderBy('nickname')->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nombre' => $user->full_name,
                'email' => $user->email,
                'tipo' => 'instructor',
            ]);
        $administradorIds = $responsableIds
            ->when($categoria->id_administrador, fn ($ids) => $ids->push($categoria->id_administrador))
            ->unique()
            ->values();
        $administradores = User::whereIn('id', $administradorIds)->whereHas('roles', function ($query) {
            $query->whereIn('slug', ['controlecp.admin', 'superadmin'])
                ->orWhere('full_access', 'Si');
        })->orderBy('nickname')->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nombre' => $user->full_name,
                'email' => $user->email,
                'tipo' => 'administrador',
            ]);
        $responsables = $administradores->concat($instructores)->unique('id')->values();

        return response()->json([
            'success' => true,
            'responsables' => $responsables,
            'mensaje' => $responsables->isEmpty() ? 'No hay instructores ni administradores disponibles para esta solicitud.' : null,
        ]);
    }

    public function calendarioResponsable(Request $request)
    {
        if (!Auth::check() || !Auth::user()->hasRole('controlecp.apprentice')) {
            return response()->json(['success' => false, 'mensaje' => 'No tienes permisos para consultar disponibilidad.'], 403);
        }

        $data = $request->validate([
            'categoria_id' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $categoria = CategoriaAsesoria::where('id_categoria_asesoria', $data['categoria_id'])
            ->where('estado', 'Activa')->first();
        if (!$categoria) {
            throw ValidationException::withMessages([
                'categoria_id' => 'La categoría seleccionada no está activa.',
            ]);
        }

        $responsable = User::findOrFail($data['responsable_id']);
        $esAdministrador = $this->isAdministrator($responsable);
        if (!$this->responsableAsignadoCategoria($categoria, $responsable)) {
            throw ValidationException::withMessages([
                'responsable_id' => 'El responsable seleccionado no está asignado a esta categoría.',
            ]);
        }

        $desde = today();
        $hasta = $desde->copy()->addDays(90);
        $dias = [];
        $festivos = collect(range($desde->year, $hasta->year))
            ->flatMap(fn ($year) => $this->festivosColombia($year))
            ->flip();

        $disponibilidad = DisponibilidadInstructor::where('id_usuario', $responsable->id)
            ->where('vigente', true)->where('habilitada', true)
            ->whereBetween('anio', [$desde->year, $hasta->year])
            ->where(function ($query) use ($categoria) {
                $query->whereNull('id_categoria_asesoria')
                    ->orWhere('id_categoria_asesoria', $categoria->id_categoria_asesoria);
            })
            ->get()->groupBy(fn ($slot) => $slot->anio . '-' . $slot->trimestre . '-' . $slot->dia_semana);
        $hayDisponibilidadEnDiasLaborables = false;
        $asesoriasOcupadas = Asesoria::where('id_usuario_instructor', $responsable->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->whereNotIn('estado', ['Cancelada', 'Rechazada'])
            ->get(['fecha', 'hora_inicio', 'hora_fin'])
            ->groupBy(fn ($item) => Carbon::parse($item->fecha)->toDateString());
        $cronogramaOcupado = DB::table('cronograma')
            ->where('id_instructor', $responsable->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->where('estado', '!=', 'Cancelada')
            ->get(['fecha', 'hora_inicio', 'hora_fin'])
            ->groupBy(fn ($item) => Carbon::parse($item->fecha)->toDateString());

        for ($date = $desde->copy(); $date->lte($hasta); $date->addDay()) {
            $key = $date->year . '-' . (int) ceil($date->month / 3) . '-' . $date->dayOfWeekIso;
            $slots = $disponibilidad->get($key, collect())
                ->map(fn ($slot) => [
                    'inicio' => max('08:00', substr($slot->hora_inicio, 0, 5)),
                    'fin' => min('15:00', substr($slot->hora_fin, 0, 5)),
                ])->all();
            $slots = array_values(array_filter($slots, fn ($slot) => $slot['inicio'] < $slot['fin']));
            $occupied = $asesoriasOcupadas->get($date->toDateString(), collect())
                ->concat($cronogramaOcupado->get($date->toDateString(), collect()))
                ->map(fn ($item) => [
                    'inicio' => substr($item->hora_inicio, 0, 5),
                    'fin' => substr($item->hora_fin, 0, 5),
                ])->all();
            $esFinDeSemana = $date->isWeekend();
            $esFestivo = $festivos->has($date->toDateString());
            if (!$esFinDeSemana && !$esFestivo && count($slots) > 0) {
                $hayDisponibilidadEnDiasLaborables = true;
            }
            if ($date->isToday()) {
                $slots = array_map(fn ($slot) => [
                    'inicio' => max($slot['inicio'], now()->format('H:i')),
                    'fin' => $slot['fin'],
                ], $slots);
                $slots = array_values(array_filter($slots, fn ($slot) => $slot['inicio'] < $slot['fin']));
            }
            $franjas = $this->freeIntervals($slots, $occupied);
            $dias[] = [
                'fecha' => $date->toDateString(),
                'disponible' => !$esFinDeSemana && !$esFestivo && count($franjas) > 0,
                'no_laborable' => $esFinDeSemana || $esFestivo,
                'ocupado' => !$esFinDeSemana && !$esFestivo && count($slots) > 0 && count($franjas) === 0,
                'franjas' => $franjas,
            ];
        }
        $mensajeDisponibilidad = !$hayDisponibilidadEnDiasLaborables
            ? 'Este responsable aún no registra horarios disponibles en días hábiles para los próximos 90 días.'
            : (collect($dias)->doesntContain(fn ($day) => $day['disponible'])
                ? 'El responsable no tiene horarios libres en los próximos 90 días; sus franjas disponibles están ocupadas.'
                : null);

        return response()->json([
            'success' => true,
            'es_administrador' => $esAdministrador,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'dias' => $dias,
            'mensaje' => $mensajeDisponibilidad,
        ]);
    }

    private function festivosColombia(int $year): array
    {
        $festivos = collect([
            Carbon::create($year, 1, 1),
            Carbon::create($year, 5, 1),
            Carbon::create($year, 7, 20),
            Carbon::create($year, 8, 7),
            Carbon::create($year, 12, 8),
            Carbon::create($year, 12, 25),
        ]);
        foreach ([[1, 6], [3, 19], [6, 29], [8, 15], [10, 12], [11, 1], [11, 11]] as [$month, $day]) {
            $festivos->push($this->moverAFestivoLunes(Carbon::create($year, $month, $day)));
        }

        $pascua = Carbon::createFromTimestampUTC(easter_date($year))->startOfDay();
        $festivos->push(
            $pascua->copy()->subDays(3),
            $pascua->copy()->subDays(2),
            $this->moverAFestivoLunes($pascua->copy()->addDays(39)),
            $this->moverAFestivoLunes($pascua->copy()->addDays(60)),
            $this->moverAFestivoLunes($pascua->copy()->addDays(68)),
        );

        return $festivos->map(fn (Carbon $date) => $date->toDateString())->all();
    }

    private function moverAFestivoLunes(Carbon $date): Carbon
    {
        return $date->isMonday() ? $date : $date->next(Carbon::MONDAY);
    }

    private function freeIntervals(array $available, array $occupied): array
    {
        $free = [];
        foreach ($available as $slot) {
            $segments = [$slot];
            foreach ($occupied as $busy) {
                $nextSegments = [];
                foreach ($segments as $segment) {
                    if ($busy['inicio'] >= $segment['fin'] || $busy['fin'] <= $segment['inicio']) {
                        $nextSegments[] = $segment;
                        continue;
                    }
                    if ($busy['inicio'] > $segment['inicio']) {
                        $nextSegments[] = ['inicio' => $segment['inicio'], 'fin' => $busy['inicio']];
                    }
                    if ($busy['fin'] < $segment['fin']) {
                        $nextSegments[] = ['inicio' => $busy['fin'], 'fin' => $segment['fin']];
                    }
                }
                $segments = $nextSegments;
            }
            array_push($free, ...$segments);
        }

        usort($free, fn ($left, $right) => $left['inicio'] <=> $right['inicio']);
        return $free;
    }

    public function storeSolicitud(Request $request, ?string $role = null)
    {
        $role = $role ?: (Auth::user()?->hasRole('controlecp.instructor') ? 'controlecp.instructor' : 'controlecp.apprentice');
        if ($response = $this->authorizeRole($role)) return $response;
        $data = $request->validate([
            'id_usuario_aprendiz' => [$role === 'controlecp.instructor' ? 'required' : 'nullable', 'integer', 'exists:users,id'],
            'id_categoria_asesoria' => ['required', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'id_usuario_instructor' => ['required', 'integer', 'exists:users,id'],
            'motivo' => ['nullable', 'string', 'min:10', 'max:200'],
            'fecha' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'],
        ]);
        $learnerId = $role === 'controlecp.instructor' ? (int) $data['id_usuario_aprendiz'] : (int) Auth::id();
        if (!User::whereKey($learnerId)->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.apprentice'))->exists()) {
            throw ValidationException::withMessages(['id_usuario_aprendiz' => 'Selecciona un aprendiz válido.']);
        }
        $instructorId = (int) $data['id_usuario_instructor'];
        $responsable = User::findOrFail($instructorId);
        $esAdministrador = $this->isAdministrator($responsable);
        if (!$esAdministrador && !$responsable->hasRole('controlecp.instructor')) {
            throw ValidationException::withMessages(['id_usuario_instructor' => 'Selecciona un instructor o administrador válido.']);
        }
        $categoria = CategoriaAsesoria::where('id_categoria_asesoria', $data['id_categoria_asesoria'])
            ->where('estado', 'Activa')->first();
        if (!$categoria) {
            throw ValidationException::withMessages(['id_categoria_asesoria' => 'La categoría seleccionada no está activa.']);
        }
        $fecha = Carbon::parse($data['fecha']);
        if ($role === 'controlecp.apprentice' && $fecha->gt(today()->addDays(90))) {
            throw ValidationException::withMessages([
                'fecha' => 'Selecciona una fecha dentro de los próximos 90 días del calendario.',
            ]);
        }
        if ($role === 'controlecp.apprentice'
            && ($fecha->isWeekend() || in_array($fecha->toDateString(), $this->festivosColombia($fecha->year), true))) {
            throw ValidationException::withMessages([
                'fecha' => 'Selecciona un día hábil; no se pueden solicitar asesorías en fines de semana o festivos.',
            ]);
        }
        if ($role === 'controlecp.apprentice' && !$this->responsableAsignadoCategoria($categoria, $responsable)) {
            throw ValidationException::withMessages(['id_usuario_instructor' => 'El responsable no está asignado a esta categoría.']);
        }
        if ($role === 'controlecp.instructor' && !$esAdministrador && !InstructorCategoria::where('id_usuario', $instructorId)
            ->where('id_categoria_asesoria', $data['id_categoria_asesoria'])->exists()) {
            throw ValidationException::withMessages(['id_usuario_instructor' => 'El instructor no está asignado a esta categoría.']);
        }
        if ($fecha->isToday() && $data['hora_inicio'] <= now()->format('H:i')) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'La hora de inicio debe ser posterior a la hora actual.',
            ]);
        }
        if ($data['hora_inicio'] < '08:00' || $data['hora_fin'] > '15:00') {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El horario de la asesoría debe estar entre las 8:00 a. m. y las 3:00 p. m.',
            ]);
        }
        DB::transaction(function () use ($data, $role, $learnerId, $instructorId, $fecha, $categoria, $esAdministrador) {
            User::whereKey($learnerId)->lockForUpdate()->firstOrFail();
            $slotQuery = DisponibilidadInstructor::where('id_usuario', $instructorId)
                ->where('anio', $fecha->year)->where('trimestre', (int) ceil($fecha->month / 3))
                ->where('dia_semana', $fecha->dayOfWeekIso)->where('vigente', true)
                ->where('habilitada', true)->where('hora_inicio', '<=', $data['hora_inicio'])
                ->where('hora_fin', '>=', $data['hora_fin']);
            $slotQuery->where(function ($query) use ($categoria) {
                $query->whereNull('id_categoria_asesoria')
                    ->orWhere('id_categoria_asesoria', $categoria->id_categoria_asesoria);
            });
            $slot = $slotQuery->lockForUpdate()->first();
            if (!$slot) {
                throw ValidationException::withMessages(['id_usuario_instructor' => 'El horario ya no está disponible. Actualiza el calendario y elige otra franja.']);
            }
            if ($this->hasScheduleConflict($instructorId, $fecha, $data['hora_inicio'], $data['hora_fin'])) {
                throw ValidationException::withMessages(['id_usuario_instructor' => 'El responsable ya tiene otra actividad en esa franja. Actualiza el calendario y elige otro horario.']);
            }
            $solicitudDuplicada = Asesoria::where('id_usuario_aprendiz', $learnerId)
                ->where('id_categoria_asesoria', $data['id_categoria_asesoria'])
                ->whereDate('fecha', $fecha->toDateString())
                ->where('hora_inicio', $data['hora_inicio'])
                ->where('hora_fin', $data['hora_fin'])
                ->whereIn('estado', ['Solicitada', 'Programada'])
                ->exists();
            if ($solicitudDuplicada) {
                throw ValidationException::withMessages([
                    'fecha' => 'Ya tienes una solicitud pendiente para esta categoría, fecha y horario.',
                ]);
            }
            $asesoria = Asesoria::create([
                'id_categoria_asesoria' => $data['id_categoria_asesoria'],
                'id_usuario_aprendiz' => $learnerId,
                'id_usuario_instructor' => $instructorId,
                'id_usuario_registro' => Auth::id(),
                'motivo' => filled($data['motivo'] ?? null) ? $data['motivo'] : 'Solicitud de asesoría',
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'estado' => $role === 'controlecp.apprentice' ? 'Solicitada' : 'Programada',
                'fecha_solicitud' => now(),
            ]);
            $mensaje = $role === 'controlecp.apprentice'
                ? 'El aprendiz solicitó una asesoría para el ' . $fecha->format('d/m/Y') . ' de ' . $data['hora_inicio'] . ' a ' . $data['hora_fin'] . '.'
                : 'Se reservó una asesoría para el ' . $fecha->format('d/m/Y') . ' de ' . $data['hora_inicio'] . ' a ' . $data['hora_fin'] . '.';
            if ($instructorId !== (int) Auth::id()) {
                $this->notify(
                    $instructorId,
                    $role === 'controlecp.apprentice' ? 'Nueva solicitud de asesoría' : 'Nueva asesoría reservada',
                    $mensaje,
                    $asesoria
                );
            }
            if ($role === 'controlecp.instructor') {
                $this->notify($learnerId, 'Asesoría programada', $mensaje, $asesoria);
            }
        });

        $route = $role === 'controlecp.instructor' ? 'controlecp.instructor.asesorias' : 'controlecp.aprendiz.asesorias';
        $success = $role === 'controlecp.apprentice'
            ? 'Tu solicitud fue enviada al responsable seleccionado para su aprobación.'
            : 'La asesoría quedó reservada y se notificó a las personas involucradas.';
        return redirect()->route($route)->with('success', $success);
    }

    public function storeSolicitudInstructor(Request $request)
    {
        return $this->storeSolicitud($request, 'controlecp.instructor');
    }

    public function storeSolicitudAprendiz(Request $request)
    {
        return $this->storeSolicitud($request, 'controlecp.apprentice');
    }

    public function cancelarSolicitudAprendiz(int $id)
    {
        if ($response = $this->authorizeRole('controlecp.apprentice')) return $response;

        $asesoria = Asesoria::where('id_usuario_aprendiz', Auth::id())
            ->whereKey($id)
            ->firstOrFail();

        if (!in_array($asesoria->estado, ['Solicitada', 'Programada'], true)) {
            throw ValidationException::withMessages([
                'asesoria' => 'Solo puedes cancelar solicitudes pendientes o asesorías programadas.',
            ]);
        }

        DB::transaction(function () use ($asesoria) {
            $asesoria = Asesoria::whereKey($asesoria->id_asesoria)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $asesoria->id_usuario_aprendiz !== (int) Auth::id()
                || !in_array($asesoria->estado, ['Solicitada', 'Programada'], true)) {
                throw ValidationException::withMessages([
                    'asesoria' => 'Esta asesoría ya no se puede cancelar.',
                ]);
            }

            $estadoAnterior = $asesoria->estado;
            $motivo = 'Cancelada por el aprendiz';
            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'Cancelo',
                'fecha_anterior' => $asesoria->fecha,
                'hora_anterior' => $asesoria->hora_inicio,
                'motivo' => $motivo,
                'fecha' => now(),
            ]);
            $asesoria->update([
                'estado' => 'Cancelada',
                'motivo_cancelacion' => $motivo,
                'id_usuario_cancelador' => Auth::id(),
            ]);

            if ($asesoria->id_usuario_instructor) {
                $this->notify(
                    (int) $asesoria->id_usuario_instructor,
                    $estadoAnterior === 'Solicitada' ? 'Solicitud cancelada por el aprendiz' : 'Asesoría cancelada por el aprendiz',
                    'El aprendiz canceló '
                        . ($estadoAnterior === 'Solicitada' ? 'su solicitud de asesoría para el ' : 'la asesoría programada para el ')
                        . Carbon::parse($asesoria->fecha)->format('d/m/Y')
                        . ' de ' . substr($asesoria->hora_inicio, 0, 5)
                        . ' a ' . substr($asesoria->hora_fin, 0, 5) . '.',
                    $asesoria
                );
            }
        });

        return redirect()->route('controlecp.aprendiz.asesorias')
            ->with('success', 'La asesoría fue cancelada y el horario quedó disponible.');
    }

    public function aceptarSolicitudInstructor(int $id)
    {
        if ($response = $this->authorizeDecisionRole()) return $response;

        DB::transaction(function () use ($id) {
            $asesoria = $this->decisionAppointments()
                ->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($asesoria->estado !== 'Solicitada') {
                throw ValidationException::withMessages([
                    'asesoria' => 'Solo puedes aceptar solicitudes pendientes.',
                ]);
            }

            $fecha = Carbon::parse($asesoria->fecha);
            if (!$this->isAdministrator(Auth::user()) && !$this->instructorHasAvailability(
                    (int) Auth::id(),
                    $fecha,
                    substr($asesoria->hora_inicio, 0, 5),
                    substr($asesoria->hora_fin, 0, 5),
                    (int) $asesoria->id_categoria_asesoria
                )) {
                throw ValidationException::withMessages([
                    'asesoria' => 'La franja ya no pertenece a tu disponibilidad. Contacta al aprendiz para proponer otro horario.',
                ]);
            }
            if ($this->hasScheduleConflict(
                (int) Auth::id(),
                $fecha,
                substr($asesoria->hora_inicio, 0, 5),
                substr($asesoria->hora_fin, 0, 5),
                $id
            )) {
                throw ValidationException::withMessages([
                    'asesoria' => 'El horario ya no está libre. Contacta al aprendiz para proponer otro horario.',
                ]);
            }

            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'Acepto',
                'motivo' => 'Solicitud aceptada por el responsable.',
                'fecha' => now(),
            ]);
            $asesoria->update(['estado' => 'Programada']);
            $this->notify(
                (int) $asesoria->id_usuario_aprendiz,
                'Asesoría aceptada',
                'Tu solicitud de asesoría fue aceptada por el responsable seleccionado.',
                $asesoria
            );
        });

        return back()->with('success', 'Solicitud aceptada; se notificó al aprendiz.');
    }

    public function rechazarSolicitudInstructor(Request $request, int $id)
    {
        if ($response = $this->authorizeDecisionRole()) return $response;

        $data = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $asesoria = $this->decisionAppointments()
                ->whereKey($id)->lockForUpdate()->firstOrFail();

            if ($asesoria->estado !== 'Solicitada') {
                throw ValidationException::withMessages([
                    'asesoria' => 'Solo puedes rechazar solicitudes pendientes.',
                ]);
            }

            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'Rechazo',
                'motivo' => $data['motivo'],
                'fecha' => now(),
            ]);
            $asesoria->update(['estado' => 'Rechazada']);
            $this->notify(
                (int) $asesoria->id_usuario_aprendiz,
                'Asesoría rechazada',
                'Tu solicitud de asesoría fue rechazada por el responsable. Motivo: ' . $data['motivo'],
                $asesoria
            );
        });

        return back()->with('success', 'Solicitud rechazada; se notificó al aprendiz.');
    }

    public function reprogramar(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'hora_inicio' => ['required', 'date_format:H:i', 'after_or_equal:08:00', 'before:15:00'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:15:00'],
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ], [
            'hora_inicio.after_or_equal' => 'La hora de inicio debe ser a partir de las 8:00 a. m.',
            'hora_inicio.before' => 'La hora de inicio debe ser antes de las 3:00 p. m.',
            'hora_fin.before_or_equal' => 'La hora de fin no puede ser después de las 3:00 p. m.',
        ]);
        $asesoria = $this->instructorVisibleAppointments()->whereKey($id)->firstOrFail();
        if ($asesoria->estado !== 'Programada') {
            throw ValidationException::withMessages(['asesoria' => 'Solo puedes reprogramar asesorías programadas.']);
        }
        $newDate = Carbon::parse($data['fecha']);
        if (!$this->instructorHasAvailability((int) Auth::id(), $newDate, $data['hora_inicio'], $data['hora_fin'], (int) $asesoria->id_categoria_asesoria)
            || $this->hasScheduleConflict((int) Auth::id(), $newDate, $data['hora_inicio'], $data['hora_fin'], $id)) {
            throw ValidationException::withMessages(['fecha' => 'La nueva franja no está disponible o se cruza con otra actividad.']);
        }
        DB::transaction(function () use ($asesoria, $data) {
            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'Reprogramo',
                'fecha_anterior' => $asesoria->fecha,
                'hora_anterior' => $asesoria->hora_inicio,
                'fecha_nueva' => $data['fecha'],
                'hora_nueva' => $data['hora_inicio'],
                'motivo' => $data['motivo'],
                'fecha' => now(),
            ]);
            $asesoria->update([
                'fecha' => $data['fecha'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
            ]);
            $this->notify(
                (int) $asesoria->id_usuario_aprendiz,
                'Asesoría reprogramada',
                'Tu asesoría ahora es el ' . Carbon::parse($data['fecha'])->format('d/m/Y') . ' de ' . $data['hora_inicio'] . ' a ' . $data['hora_fin'] . '. Motivo: ' . $data['motivo'],
                $asesoria
            );
        });
        return back()->with('success', 'Asesoría reprogramada; el cambio quedó en el historial y se notificó al aprendiz.');
    }

    public function cancelar(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:200']]);
        $asesoria = $this->instructorVisibleAppointments()->whereKey($id)->firstOrFail();
        if ($asesoria->estado !== 'Programada') {
            throw ValidationException::withMessages(['asesoria' => 'Solo puedes cancelar asesorías programadas.']);
        }
        DB::transaction(function () use ($asesoria, $data) {
            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'Cancelo',
                'fecha_anterior' => $asesoria->fecha,
                'hora_anterior' => $asesoria->hora_inicio,
                'motivo' => $data['motivo'],
                'fecha' => now(),
            ]);
            $asesoria->update([
                'estado' => 'Cancelada',
                'motivo_cancelacion' => $data['motivo'],
                'id_usuario_cancelador' => Auth::id(),
            ]);
            $this->notify((int) $asesoria->id_usuario_aprendiz, 'Asesoría cancelada', 'Tu instructor canceló la asesoría: ' . $data['motivo'], $asesoria);
        });
        return back()->with('success', 'Asesoría cancelada; el horario quedó libre y se notificó al aprendiz.');
    }

    public function guardarSeguimiento(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate([
            'avances' => ['required', 'string', 'min:10', 'max:200'],
            'compromisos' => ['required', 'string', 'min:10', 'max:200'],
            'observaciones' => ['nullable', 'string', 'min:10', 'max:200'],
        ]);
        $asesoria = $this->instructorVisibleAppointments()->whereKey($id)->firstOrFail();
        if ($asesoria->estado !== 'Programada') {
            throw ValidationException::withMessages(['asesoria' => 'Solo se puede registrar seguimiento a una asesoría programada.']);
        }
        DB::transaction(function () use ($data, $asesoria) {
            AsesoriaSeguimiento::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'avances' => $data['avances'],
                'compromisos' => $data['compromisos'],
                'observaciones' => $data['observaciones'] ?? null,
                'fecha' => now(),
            ]);
            $asesoria->update(['estado' => 'Realizada']);
            $this->notify(
                (int) $asesoria->id_usuario_aprendiz,
                'Asesoría realizada: califícala',
                'Tu asesoría finalizó. Ingresa a Calificación de asesoría y evalúa tu experiencia con estrellas.',
                $asesoria
            );
        });
        return back()->with('success', 'Seguimiento registrado y asesoría marcada como realizada.');
    }

    public function corregirSeguimiento(Request $request, int $seguimientoId)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate([
            'avances' => ['required', 'string', 'min:10', 'max:200'],
            'compromisos' => ['required', 'string', 'min:10', 'max:200'],
            'observaciones' => ['nullable', 'string', 'min:10', 'max:200'],
            'motivo_correccion' => ['required', 'string', 'min:10', 'max:200'],
        ]);
        $original = AsesoriaSeguimiento::where('id_usuario', Auth::id())
            ->whereNull('id_asesoria_seguimiento_corrige')
            ->whereHas('asesoria', fn ($query) => $this->excludeAdminCreated(
                $query->where('id_usuario_instructor', Auth::id())
            ))
            ->findOrFail($seguimientoId);
        AsesoriaSeguimiento::create([
            'id_asesoria' => $original->id_asesoria,
            'id_usuario' => Auth::id(),
            'id_asesoria_seguimiento_corrige' => $original->id_asesoria_seguimiento,
            'avances' => $data['avances'],
            'compromisos' => $data['compromisos'],
            'observaciones' => $data['observaciones'] ?? null,
            'motivo_correccion' => $data['motivo_correccion'],
            'fecha' => now(),
        ]);
        return back()->with('success', 'Corrección guardada; el seguimiento original se conservó.');
    }

    public function agregarRecurso(Request $request, int $id)
    {
        if ($response = $this->authorizeRole('controlecp.instructor')) return $response;
        $data = $request->validate([
            'id_inventario' => ['required', 'integer', 'exists:inventario,id_inventario'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);
        $asesoria = $this->instructorVisibleAppointments()->whereKey($id)->firstOrFail();
        if (!in_array($asesoria->estado, ['Programada', 'Realizada'], true)) {
            throw ValidationException::withMessages(['asesoria' => 'No se pueden agregar recursos a esta asesoría.']);
        }
        DB::transaction(function () use ($data, $asesoria) {
            $recurso = Inventario::whereKey($data['id_inventario'])->lockForUpdate()->firstOrFail();
            if ($recurso->estado !== 'Disponible' || $recurso->cantidad_disponible < $data['cantidad']) {
                throw ValidationException::withMessages(['cantidad' => 'No hay suficientes unidades disponibles de ese recurso.']);
            }
            $estadoAnterior = $recurso->estado;
            $recurso->cantidad_disponible -= $data['cantidad'];
            if ($recurso->cantidad_disponible === 0) $recurso->estado = 'Agotado';
            $recurso->save();
            MovimientoInventario::create([
                'id_inventario' => $recurso->id_inventario,
                'id_usuario' => Auth::id(),
                'id_asesoria' => $asesoria->id_asesoria,
                'tipo' => 'Salida',
                'cantidad' => $data['cantidad'],
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $recurso->estado,
                'observacion' => 'Recurso asignado a asesoría.',
                'fecha' => now(),
            ]);
            $actual = AsesoriaRecurso::where('id_asesoria', $asesoria->id_asesoria)
                ->where('id_inventario', $recurso->id_inventario)->first();
            if ($actual) {
                $actual->increment('cantidad', $data['cantidad']);
            } else {
                AsesoriaRecurso::create([
                    'id_asesoria' => $asesoria->id_asesoria,
                    'id_inventario' => $recurso->id_inventario,
                    'cantidad' => $data['cantidad'],
                ]);
            }
        });
        return back()->with('success', 'Recurso asignado y disponibilidad de inventario actualizada.');
    }

    private function quarterRange(int $year, int $quarter): array
    {
        $startMonth = (($quarter - 1) * 3) + 1;
        $from = Carbon::create($year, $startMonth, 1)->startOfDay();
        return [$from, $from->copy()->addMonths(3)->subDay()->endOfDay()];
    }

    private function availabilityHasConflict(
        int $year,
        int $quarter,
        int $day,
        string $start,
        string $end,
        ?int $exceptSlotId = null
    ): bool {
        $dayOfWeek = $day === 7 ? 1 : $day + 1;
        [$from, $to] = $this->quarterRange($year, $quarter);

        $slotConflict = DisponibilidadInstructor::where('id_usuario', Auth::id())
            ->where('anio', $year)
            ->where('trimestre', $quarter)
            ->where('dia_semana', $day)
            ->where('vigente', true)
            ->where('habilitada', true)
            ->when($exceptSlotId, fn ($query) => $query->where('id_disponibilidad_instructor', '!=', $exceptSlotId))
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
        if ($slotConflict) return true;

        $cronogramaConflict = DB::table('cronograma')
            ->where('id_instructor', Auth::id())
            ->whereBetween('fecha', [$from->toDateString(), $to->toDateString()])
            ->where('estado', '!=', 'Cancelada')
            ->whereRaw('DAYOFWEEK(fecha) = ?', [$dayOfWeek])
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
        if ($cronogramaConflict) return true;

        return $this->instructorAppointments()
            ->whereBetween('fecha', [$from->toDateString(), $to->toDateString()])
            ->whereRaw('DAYOFWEEK(fecha) = ?', [$dayOfWeek])
            ->whereNotIn('estado', ['Cancelada', 'Rechazada'])
            ->where('hora_inicio', '<', $end)
            ->where('hora_fin', '>', $start)
            ->exists();
    }

    private function instructorHasAvailability(int $instructorId, Carbon $date, string $start, string $end, int $categoryId): bool
    {
        return DisponibilidadInstructor::where('id_usuario', $instructorId)
            ->where('anio', $date->year)->where('trimestre', (int) ceil($date->month / 3))
            ->where('dia_semana', $date->dayOfWeekIso)->where('vigente', true)->where('habilitada', true)
            ->where(function ($query) use ($categoryId) {
                $query->whereNull('id_categoria_asesoria')
                    ->orWhere('id_categoria_asesoria', $categoryId);
            })
            ->where('hora_inicio', '<=', $start)->where('hora_fin', '>=', $end)->exists();
    }

    private function hasScheduleConflict(int $instructorId, Carbon $date, string $start, string $end, ?int $exceptAppointmentId = null): bool
    {
        $appointmentConflict = Asesoria::where('id_usuario_instructor', $instructorId)
            ->whereDate('fecha', $date->toDateString())->whereNotIn('estado', ['Cancelada', 'Rechazada'])
            ->when($exceptAppointmentId, fn ($query) => $query->where('id_asesoria', '!=', $exceptAppointmentId))
            ->where('hora_inicio', '<', $end)->where('hora_fin', '>', $start)->exists();
        if ($appointmentConflict) return true;

        return DB::table('cronograma')->where('id_instructor', $instructorId)
            ->whereDate('fecha', $date->toDateString())
            ->where('estado', '!=', 'Cancelada')
            ->where('hora_inicio', '<', $end)->where('hora_fin', '>', $start)->exists();
    }

    private function isAdministrator(User $user): bool
    {
        return $user->roles()->where(function ($query) {
            $query->whereIn('slug', ['controlecp.admin', 'superadmin'])
                ->orWhere('full_access', 'Si');
        })->exists();
    }

    private function authorizeDecisionRole()
    {
        if (!Auth::check() || (!Auth::user()->hasRole('controlecp.instructor') && !$this->isAdministrator(Auth::user()))) {
            return redirect()->route('controlecp.index')->with('error', 'No tienes permisos para responder solicitudes de asesoría.');
        }

        return null;
    }

    private function decisionAppointments()
    {
        $query = $this->instructorAppointments();
        if ($this->isAdministrator(Auth::user())) {
            return $query->whereHas('usuarioRegistro.roles', fn ($roles) => $roles->where('slug', 'controlecp.apprentice'));
        }

        return $this->excludeAdminCreated($query);
    }

    private function notify(int $receiverId, string $title, string $message, Asesoria $asesoria): void
    {
        Notificacion::crear(
            idReceptor: $receiverId,
            idCausante: (int) Auth::id(),
            titulo: $title,
            mensaje: $message,
            referenciaTipo: 'asesoria',
            referenciaId: (int) $asesoria->id_asesoria,
            claveEvento: 'asesoria_' . $asesoria->id_asesoria . '_' . now()->format('YmdHisv') . '_' . $receiverId
        );
    }
}
