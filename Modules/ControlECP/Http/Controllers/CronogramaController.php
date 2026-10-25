<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ControlECP\Entities\ActividadEcp;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\CronogramaHistorial;
use Modules\ControlECP\Entities\CronogramaRecurso;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\Notificacion;
use Modules\SICA\Entities\Course;

class CronogramaController extends Controller
{
    /**
     * HU-006: Vista principal del Cronograma para Administrador
     * Vista Calendario, Vista Lista, Semáforo y Filtros.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de cronograma.');
        }

        // ── 1. Construcción de Query con Filtros ──────────────────
        $query = Cronograma::with([
            'actividad.momento',
            'ficha.program',
            'instructor.person',
            'creador.person',
            'recursos.inventario',
        ]);

        // Filtro por Ficha
        if ($request->filled('ficha_id')) {
            $query->where('id_ficha', $request->ficha_id);
        }

        // Filtro por Instructor
        if ($request->filled('instructor_id')) {
            $query->where('id_instructor', $request->instructor_id);
        }

        // Filtro por Momento Pedagógico
        if ($request->filled('momento_id')) {
            $query->whereHas('actividad', function ($q) use ($request) {
                $q->where('momento_id', $request->momento_id);
            });
        }

        // Filtro por Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por Rango de Fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Búsqueda por texto (actividad, ficha, lugar)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('lugar', 'like', "%{$term}%")
                  ->orWhereHas('actividad', function ($qa) use ($term) {
                      $qa->where('nombre', 'like', "%{$term}%")
                         ->orWhere('numero', 'like', "%{$term}%");
                  })
                  ->orWhereHas('ficha', function ($qf) use ($term) {
                      $qf->where('code', 'like', "%{$term}%");
                  })
                  ->orWhereHas('instructor.person', function ($qp) use ($term) {
                      $qp->where('first_name', 'like', "%{$term}%")
                         ->orWhere('first_last_name', 'like', "%{$term}%");
                  });
            });
        }

        $todasSesiones = $query->orderBy('fecha', 'asc')
                               ->orderBy('hora_inicio', 'asc')
                               ->get();

        // Filtro adicional por Semáforo si se especificó
        if ($request->filled('semaforo') && $request->semaforo !== 'todos') {
            $filtroSem = $request->semaforo;
            $todasSesiones = $todasSesiones->filter(function ($item) use ($filtroSem) {
                if ($filtroSem === 'sin_color') {
                    return is_null($item->semaforo);
                }
                return $item->semaforo === $filtroSem;
            });
        }

        // ── 2. Estadísticas de Semáforo (Criterio 6) ───────────────
        $todasParaEstadisticas = Cronograma::all();
        $conteoRojo = 0;
        $conteoAmarillo = 0;
        $conteoVerde = 0;
        $conteoPospuestas = 0;
        $conteoFinalizadas = 0;

        foreach ($todasParaEstadisticas as $sesion) {
            if ($sesion->estado === 'Pospuesta') {
                $conteoPospuestas++;
            } elseif ($sesion->estado === 'Finalizada') {
                $conteoFinalizadas++;
            } elseif ($sesion->estado === 'Pendiente') {
                $sem = $sesion->semaforo;
                if ($sem === 'rojo') {
                    $conteoRojo++;
                } elseif ($sem === 'amarillo') {
                    $conteoAmarillo++;
                } elseif ($sem === 'verde') {
                    $conteoVerde++;
                }
            }
        }

        $stats = [
            'total'       => $todasParaEstadisticas->count(),
            'pendientes'  => $conteoRojo + $conteoAmarillo + $conteoVerde,
            'rojo'        => $conteoRojo,
            'amarillo'    => $conteoAmarillo,
            'verde'       => $conteoVerde,
            'pospuestas'  => $conteoPospuestas,
            'finalizadas' => $conteoFinalizadas,
        ];

        // ── 3. Catálogos para Modales y Filtros ───────────────────
        $fichas = Course::where('status', 'Activo')
            ->with('program')
            ->orderBy('code')
            ->get();

        $momentos = Momento::with(['actividades' => fn($q) => $q->where('estado', 'Activo')])
            ->orderBy('orden')
            ->get();

        $actividades = ActividadEcp::where('estado', 'Activo')
            ->with('momento')
            ->orderBy('momento_id')
            ->orderBy('numero')
            ->get();

        // Instructores vinculados a Control ECP (o con rol de instructor)
        $instructores = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', ['controlecp.instructor', 'instructor']);
        })->with('person')->get();

        // Si no hubiera usuarios con ese rol aún en la BD de prueba, cargar todos con persona
        if ($instructores->isEmpty()) {
            $instructores = User::with('person')->whereNotNull('person_id')->limit(20)->get();
        }

        // Recursos de inventario disponibles
        $recursosInventario = Inventario::where('estado', '!=', 'Inactivo')
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('controlecp::administrador.cronograma', compact(
            'todasSesiones',
            'stats',
            'fichas',
            'momentos',
            'actividades',
            'instructores',
            'recursosInventario'
        ));
    }

    /**
     * Endpoint JSON de eventos para el calendario mensual.
     */
    public function getEventos(Request $request): JsonResponse
    {
        $query = Cronograma::with([
            'actividad.momento',
            'ficha.program',
            'instructor.person',
            'recursos.inventario',
        ]);

        // Filtrar por año y mes si se envían
        if ($request->filled('year') && $request->filled('month')) {
            $year = (int) $request->year;
            $month = (int) $request->month;
            $inicioMes = Carbon::createFromDate($year, $month, 1)->startOfMonth()->subDays(7)->toDateString();
            $finMes = Carbon::createFromDate($year, $month, 1)->endOfMonth()->addDays(7)->toDateString();
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        }

        if ($request->filled('ficha_id')) {
            $query->where('id_ficha', $request->ficha_id);
        }
        if ($request->filled('instructor_id')) {
            $query->where('id_instructor', $request->instructor_id);
        }
        if ($request->filled('momento_id')) {
            $query->whereHas('actividad', function ($q) use ($request) {
                $q->where('momento_id', $request->momento_id);
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $eventos = $query->get()->map(function ($sesion) {
            $p = $sesion->instructor?->person;
            $nomInstructor = $p ? "{$p->first_name} {$p->first_last_name}" : ($sesion->instructor?->nickname ?? 'Sin asignar');

            return [
                'id'                 => $sesion->id_cronograma,
                'titulo'             => $sesion->titulo_calendario, // Criterio 1: Actividad X – Momento Y
                'actividad_nombre'   => $sesion->actividad?->nombre ?? 'Actividad',
                'actividad_numero'   => $sesion->actividad?->numero ?? '',
                'momento_nombre'     => $sesion->actividad?->momento?->nombre ?? '',
                'momento_color'      => $sesion->actividad?->momento?->color ?? '#39A900',
                'ficha_codigo'       => $sesion->ficha?->code ?? 'Ficha',
                'ficha_programa'     => $sesion->ficha?->program?->name ?? '',
                'fecha'              => $sesion->fecha ? Carbon::parse($sesion->fecha)->toDateString() : '',
                'fecha_formateada'   => $sesion->fecha ? Carbon::parse($sesion->fecha)->translatedFormat('d M Y') : '',
                'hora_inicio'        => $sesion->hora_inicio,
                'hora_fin'           => $sesion->hora_fin,
                'horario'            => $sesion->horario_formateado,
                'lugar'              => $sesion->lugar,
                'estado'             => $sesion->estado,
                'semaforo'           => $sesion->semaforo, // Criterio 6: rojo, amarillo, verde o null
                'semaforo_texto'     => $sesion->semaforo_texto,
                'dias_restantes'     => $sesion->dias_restantes,
                'instructor_id'      => $sesion->id_instructor,
                'instructor_nombre'  => $nomInstructor,
                'recursos_count'     => $sesion->recursos->count(),
                'recursos'           => $sesion->recursos->map(fn($r) => [
                    'id_inventario' => $r->id_inventario,
                    'nombre'        => $r->inventario?->nombre ?? 'Recurso',
                    'cantidad'      => $r->cantidad,
                ]),
                'fecha_original'     => $sesion->fecha_original ? Carbon::parse($sesion->fecha_original)->toDateString() : null,
                'motivo_posposicion' => $sesion->motivo_posposicion,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $eventos,
        ]);
    }

    /**
     * Retorna detalle completo en JSON de una programación específica.
     */
    public function show($id): JsonResponse
    {
        $sesion = Cronograma::with([
            'actividad.momento',
            'ficha.program',
            'instructor.person',
            'creador.person',
            'finalizador.person',
            'recursos.inventario',
            'historial.autor.person',
            'historial.instructorAnterior.person',
            'historial.instructorNuevo.person',
        ])->find($id);

        if (!$sesion) {
            return response()->json(['success' => false, 'message' => 'Sesión no encontrada.'], 404);
        }

        $p = $sesion->instructor?->person;
        $nomInstructor = $p ? "{$p->first_name} {$p->first_last_name}" : ($sesion->instructor?->nickname ?? 'Sin asignar');

        $c = $sesion->creador?->person;
        $nomCreador = $c ? "{$c->first_name} {$c->first_last_name}" : ($sesion->creador?->nickname ?? 'Administrador');

        return response()->json([
            'success' => true,
            'data'    => [
                'id_cronograma'      => $sesion->id_cronograma,
                'id_actividad'       => $sesion->id_actividad,
                'actividad_nombre'   => $sesion->actividad?->nombre,
                'actividad_numero'   => $sesion->actividad?->numero,
                'id_momento'         => $sesion->actividad?->momento_id,
                'momento_nombre'     => $sesion->actividad?->momento?->nombre,
                'id_ficha'           => $sesion->id_ficha,
                'ficha_code'         => $sesion->ficha?->code,
                'ficha_programa'     => $sesion->ficha?->program?->name,
                'id_instructor'      => $sesion->id_instructor,
                'instructor_nombre'  => $nomInstructor,
                'creador_nombre'     => $nomCreador,
                'fecha'              => $sesion->fecha ? Carbon::parse($sesion->fecha)->toDateString() : '',
                'fecha_formateada'   => $sesion->fecha ? Carbon::parse($sesion->fecha)->translatedFormat('l, d \d\e F \d\e Y') : '',
                'hora_inicio'        => substr($sesion->hora_inicio, 0, 5),
                'hora_fin'           => substr($sesion->hora_fin, 0, 5),
                'horario'            => $sesion->horario_formateado,
                'lugar'              => $sesion->lugar,
                'estado'             => $sesion->estado,
                'semaforo'           => $sesion->semaforo,
                'semaforo_texto'     => $sesion->semaforo_texto,
                'dias_restantes'     => $sesion->dias_restantes,
                'fecha_original'     => $sesion->fecha_original ? Carbon::parse($sesion->fecha_original)->toDateString() : null,
                'motivo_posposicion' => $sesion->motivo_posposicion,
                'recursos'           => $sesion->recursos->map(fn($r) => [
                    'id_cronograma_recurso' => $r->id_cronograma_recurso,
                    'id_inventario'         => $r->id_inventario,
                    'nombre'                => $r->inventario?->nombre ?? 'Recurso',
                    'cantidad'              => $r->cantidad,
                    'disponible_actual'     => $r->inventario?->cantidad_disponible ?? 0,
                ]),
                'historial'          => $sesion->historial->map(function ($h) {
                    $aut = $h->autor?->person;
                    $nomAut = $aut ? "{$aut->first_name} {$aut->first_last_name}" : ($h->autor?->nickname ?? 'Sistema');
                    $ant = $h->instructorAnterior?->person;
                    $nomAnt = $ant ? "{$ant->first_name} {$ant->first_last_name}" : null;
                    $nue = $h->instructorNuevo?->person;
                    $nomNue = $nue ? "{$nue->first_name} {$nue->first_last_name}" : null;

                    return [
                        'accion'              => $h->accion,
                        'motivo'              => $h->motivo,
                        'fecha_anterior'      => $h->fecha_anterior ? Carbon::parse($h->fecha_anterior)->toDateString() : null,
                        'fecha_nueva'         => $h->fecha_nueva ? Carbon::parse($h->fecha_nueva)->toDateString() : null,
                        'autor'               => $nomAut,
                        'instructor_anterior' => $nomAnt,
                        'instructor_nuevo'    => $nomNue,
                        'fecha_registro'      => $h->fecha ? Carbon::parse($h->fecha)->translatedFormat('d M Y, h:i A') : $h->created_at->format('d/m/Y H:i'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Criterio 1 & 2: Programar actividad y validar campos obligatorios.
     * Criterio 3: Validar disponibilidad del instructor.
     * Criterio 4: Asociar recursos con validación de disponibilidad.
     * Criterio 12: Notificar al instructor responsable.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        // Criterio 2: Validar campos obligatorios
        $request->validate([
            'id_actividad'  => 'required|exists:controlecp_actividades,id',
            'id_ficha'      => 'required|exists:courses,id',
            'id_instructor' => 'required|exists:users,id',
            'fecha'         => 'required|date',
            'hora_inicio'   => 'required|date_format:H:i',
            'hora_fin'      => 'required|date_format:H:i|after:hora_inicio',
            'lugar'         => 'required|string|max:200',
            'recursos'      => 'nullable|array',
            'recursos.*.id_inventario' => 'required|exists:inventario,id_inventario',
            'recursos.*.cantidad'      => 'required|integer|min:1',
        ], [
            'id_actividad.required'  => 'La actividad es obligatoria.',
            'id_actividad.exists'    => 'La actividad seleccionada no es válida.',
            'id_ficha.required'      => 'La ficha de formación es obligatoria.',
            'id_ficha.exists'        => 'La ficha seleccionada no existe.',
            'id_instructor.required' => 'El instructor responsable es obligatorio.',
            'id_instructor.exists'   => 'El instructor seleccionado no existe.',
            'fecha.required'         => 'La fecha de programación es obligatoria.',
            'fecha.date'             => 'La fecha ingresada no tiene un formato válido.',
            'hora_inicio.required'   => 'La hora de inicio es obligatoria.',
            'hora_fin.required'      => 'La hora de fin es obligatoria.',
            'hora_fin.after'         => 'La hora de fin debe ser posterior a la hora de inicio.',
            'lugar.required'         => 'El lugar o aula es obligatorio.',
        ]);

        // Criterio 3: Validar disponibilidad del instructor
        $conflicto = $this->validarDisponibilidadInstructor(
            (int) $request->id_instructor,
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin
        );

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => $conflicto,
                'field'   => 'id_instructor',
            ], 422);
        }

        // Criterio 4: Validar disponibilidad de recursos
        $recursosAAsociar = $request->input('recursos', []);
        $validacionRecursos = $this->validarRecursosDisponibles($recursosAAsociar);
        if ($validacionRecursos !== true) {
            return response()->json([
                'success' => false,
                'message' => $validacionRecursos,
                'field'   => 'recursos',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Criterio 1: Registrar con estado "Pendiente"
            $cronograma = Cronograma::create([
                'id_actividad'       => $request->id_actividad,
                'id_ficha'           => $request->id_ficha,
                'id_instructor'      => $request->id_instructor,
                'id_usuario_creador' => $user->id,
                'fecha'              => $request->fecha,
                'hora_inicio'        => $request->hora_inicio,
                'hora_fin'           => $request->hora_fin,
                'lugar'              => trim($request->lugar),
                'estado'             => 'Pendiente',
            ]);

            // Criterio 4: Asociar recursos y reservar
            foreach ($recursosAAsociar as $itemRecurso) {
                CronogramaRecurso::create([
                    'id_cronograma' => $cronograma->id_cronograma,
                    'id_inventario' => $itemRecurso['id_inventario'],
                    'cantidad'      => (int) $itemRecurso['cantidad'],
                ]);

                // Descontar temporalmente o reservar cantidad disponible
                $inv = Inventario::find($itemRecurso['id_inventario']);
                if ($inv && $inv->cantidad_disponible >= $itemRecurso['cantidad']) {
                    $inv->decrement('cantidad_disponible', (int) $itemRecurso['cantidad']);
                }
            }

            // Historial de auditoría
            CronogramaHistorial::create([
                'id_cronograma'    => $cronograma->id_cronograma,
                'id_usuario_autor' => $user->id,
                'accion'           => 'crear',
                'fecha_nueva'      => $request->fecha,
                'motivo'           => 'Programación inicial de actividad en cronograma.',
                'fecha'            => now(),
            ]);

            // Criterio 12: Notificar al instructor responsable
            $actividadObj = ActividadEcp::with('momento')->find($request->id_actividad);
            $fichaObj = Course::find($request->id_ficha);
            $fechaHumana = Carbon::parse($request->fecha)->format('d/m/Y');

            Notificacion::crear(
                idReceptor: (int) $request->id_instructor,
                idCausante: $user->id,
                titulo: "Nueva Actividad Programada",
                mensaje: "Se te ha programado la actividad '{$actividadObj?->nombre}' con la ficha {$fichaObj?->code} para el {$fechaHumana} de {$request->hora_inicio} a {$request->hora_fin} en {$request->lugar}.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'programar'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Actividad programada exitosamente con estado Pendiente!',
                'data'    => [
                    'id_cronograma'     => $cronograma->id_cronograma,
                    'titulo_calendario' => $cronograma->titulo_calendario,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la programación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Criterio 8: Editar programación (modificar fecha, horario, lugar, ficha o recursos).
     * Notifica a los involucrados y muestra confirmación.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $cronograma = Cronograma::with('recursos')->find($id);
        if (!$cronograma) {
            return response()->json(['success' => false, 'message' => 'Programación no encontrada.'], 404);
        }

        $request->validate([
            'id_actividad'  => 'required|exists:controlecp_actividades,id',
            'id_ficha'      => 'required|exists:courses,id',
            'fecha'         => 'required|date',
            'hora_inicio'   => 'required|date_format:H:i',
            'hora_fin'      => 'required|date_format:H:i|after:hora_inicio',
            'lugar'         => 'required|string|max:200',
            'recursos'      => 'nullable|array',
            'recursos.*.id_inventario' => 'required|exists:inventario,id_inventario',
            'recursos.*.cantidad'      => 'required|integer|min:1',
        ]);

        // Criterio 3: Validar disponibilidad del instructor en nuevo horario
        $conflicto = $this->validarDisponibilidadInstructor(
            (int) $cronograma->id_instructor,
            $request->fecha,
            $request->hora_inicio,
            $request->hora_fin,
            (int) $cronograma->id_cronograma
        );

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => $conflicto,
                'field'   => 'hora_inicio',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $fechaAnterior = $cronograma->fecha;

            // Devolver cantidades anteriores de recursos a inventario
            foreach ($cronograma->recursos as $recursoViejo) {
                $inv = Inventario::find($recursoViejo->id_inventario);
                if ($inv) {
                    $inv->increment('cantidad_disponible', $recursoViejo->cantidad);
                }
            }
            // Eliminar recursos anteriores
            CronogramaRecurso::where('id_cronograma', $cronograma->id_cronograma)->delete();

            // Validar y asociar nuevos recursos
            $nuevosRecursos = $request->input('recursos', []);
            $valRecursos = $this->validarRecursosDisponibles($nuevosRecursos);
            if ($valRecursos !== true) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $valRecursos], 422);
            }

            foreach ($nuevosRecursos as $item) {
                CronogramaRecurso::create([
                    'id_cronograma' => $cronograma->id_cronograma,
                    'id_inventario' => $item['id_inventario'],
                    'cantidad'      => (int) $item['cantidad'],
                ]);
                $inv = Inventario::find($item['id_inventario']);
                if ($inv) {
                    $inv->decrement('cantidad_disponible', (int) $item['cantidad']);
                }
            }

            // Actualizar datos
            $cronograma->update([
                'id_actividad' => $request->id_actividad,
                'id_ficha'     => $request->id_ficha,
                'fecha'        => $request->fecha,
                'hora_inicio'  => $request->hora_inicio,
                'hora_fin'     => $request->hora_fin,
                'lugar'        => trim($request->lugar),
            ]);

            // Registrar en historial
            CronogramaHistorial::create([
                'id_cronograma'    => $cronograma->id_cronograma,
                'id_usuario_autor' => $user->id,
                'accion'           => 'editar',
                'fecha_anterior'   => $fechaAnterior,
                'fecha_nueva'      => $request->fecha,
                'motivo'           => $request->input('motivo_edicion', 'Actualización de datos de la sesión.'),
                'fecha'            => now(),
            ]);

            // Criterio 12: Notificar al instructor responsable
            $fechaFormateada = Carbon::parse($request->fecha)->format('d/m/Y');
            Notificacion::crear(
                idReceptor: (int) $cronograma->id_instructor,
                idCausante: $user->id,
                titulo: "Modificación de Actividad Programada",
                mensaje: "Se actualizaron los detalles de la actividad '{$cronograma->actividad?->nombre}'. Nueva fecha: {$fechaFormateada}, horario: {$request->hora_inicio} a {$request->hora_fin}, lugar: {$request->lugar}.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'editar'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Programación actualizada correctamente!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Criterio 10: Reasignar instructor
     * Valida que el nuevo instructor esté libre, registra el cambio y notifica al anterior y al nuevo.
     */
    public function reasignar(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $cronograma = Cronograma::with(['actividad', 'ficha'])->find($id);
        if (!$cronograma) {
            return response()->json(['success' => false, 'message' => 'Programación no encontrada.'], 404);
        }

        $request->validate([
            'nuevo_instructor_id' => 'required|exists:users,id',
            'motivo_reasignacion' => 'required|string|min:5|max:500',
        ], [
            'nuevo_instructor_id.required' => 'Debes seleccionar el nuevo instructor.',
            'nuevo_instructor_id.exists'   => 'El instructor seleccionado no existe.',
            'motivo_reasignacion.required' => 'El motivo de reasignación es obligatorio.',
        ]);

        $nuevoInstructorId = (int) $request->nuevo_instructor_id;
        $instructorAnteriorId = (int) $cronograma->id_instructor;

        if ($nuevoInstructorId === $instructorAnteriorId) {
            return response()->json([
                'success' => false,
                'message' => 'El instructor seleccionado ya es el responsable actual.',
            ], 422);
        }

        // Criterio 10: Validar que el nuevo instructor esté libre en esa fecha y horario
        $conflicto = $this->validarDisponibilidadInstructor(
            $nuevoInstructorId,
            $cronograma->fecha,
            $cronograma->hora_inicio,
            $cronograma->hora_fin,
            (int) $cronograma->id_cronograma
        );

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => "Conflicto: {$conflicto}",
                'field'   => 'nuevo_instructor_id',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cronograma->update([
                'id_instructor' => $nuevoInstructorId,
            ]);

            // Auditoría en historial
            CronogramaHistorial::create([
                'id_cronograma'                  => $cronograma->id_cronograma,
                'id_usuario_autor'               => $user->id,
                'id_usuario_instructor_anterior' => $instructorAnteriorId,
                'id_usuario_instructor_nuevo'    => $nuevoInstructorId,
                'accion'                         => 'reasignar',
                'fecha_nueva'                    => $cronograma->fecha,
                'motivo'                         => trim($request->motivo_reasignacion),
                'fecha'                          => now(),
            ]);

            $fechaSesion = Carbon::parse($cronograma->fecha)->format('d/m/Y');
            $actNombre = $cronograma->actividad?->nombre ?? 'Actividad';

            // Notificar al instructor anterior
            Notificacion::crear(
                idReceptor: $instructorAnteriorId,
                idCausante: $user->id,
                titulo: "Reasignación de Actividad",
                mensaje: "Has sido desasignado de la actividad '{$actNombre}' del {$fechaSesion}. Motivo: {$request->motivo_reasignacion}.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'reasignar'
            );

            // Notificar al nuevo instructor
            Notificacion::crear(
                idReceptor: $nuevoInstructorId,
                idCausante: $user->id,
                titulo: "Nueva Actividad Asignada",
                mensaje: "Se te ha asignado como responsable de la actividad '{$actNombre}' para el {$fechaSesion} en el horario {$cronograma->horario_formateado}.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'reasignar'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Instructor reasignado con éxito y notificaciones enviadas a ambos instructores!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al reasignar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Criterio 11: Posponer actividad
     * Asignándole nueva fecha y motivo. Cambia a "Pospuesta", conserva fecha original, registra motivo y notifica.
     */
    public function posponer(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $cronograma = Cronograma::with('actividad')->find($id);
        if (!$cronograma) {
            return response()->json(['success' => false, 'message' => 'Programación no encontrada.'], 404);
        }

        $request->validate([
            'nueva_fecha'   => 'required|date|after_or_equal:today',
            'hora_inicio'   => 'nullable|date_format:H:i',
            'hora_fin'      => 'nullable|date_format:H:i',
            'motivo'        => 'required|string|min:5|max:1000',
        ], [
            'nueva_fecha.required'        => 'La nueva fecha es obligatoria.',
            'nueva_fecha.after_or_equal'  => 'La nueva fecha debe ser igual o posterior a hoy.',
            'motivo.required'             => 'El motivo de posposición es obligatorio.',
            'motivo.min'                  => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $nuevaHoraInicio = $request->hora_inicio ?: $cronograma->hora_inicio;
        $nuevaHoraFin    = $request->hora_fin ?: $cronograma->hora_fin;

        // Validar disponibilidad del instructor en la nueva fecha y hora
        $conflicto = $this->validarDisponibilidadInstructor(
            (int) $cronograma->id_instructor,
            $request->nueva_fecha,
            $nuevaHoraInicio,
            $nuevaHoraFin,
            (int) $cronograma->id_cronograma
        );

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => "No se puede posponer a esa fecha/hora: {$conflicto}",
                'field'   => 'nueva_fecha',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $fechaPrevia = $cronograma->fecha;
            $fechaOriginal = $cronograma->fecha_original ?: $fechaPrevia;

            $cronograma->update([
                'fecha_original'     => $fechaOriginal,
                'fecha'              => $request->nueva_fecha,
                'hora_inicio'        => $nuevaHoraInicio,
                'hora_fin'           => $nuevaHoraFin,
                'estado'             => 'Pospuesta',
                'motivo_posposicion' => trim($request->motivo),
            ]);

            // Auditoría en historial
            CronogramaHistorial::create([
                'id_cronograma'    => $cronograma->id_cronograma,
                'id_usuario_autor' => $user->id,
                'accion'           => 'posponer',
                'fecha_anterior'   => $fechaPrevia,
                'fecha_nueva'      => $request->nueva_fecha,
                'motivo'           => trim($request->motivo),
                'fecha'            => now(),
            ]);

            // Criterio 12: Notificar al instructor responsable
            $fechaAntHum = Carbon::parse($fechaPrevia)->format('d/m/Y');
            $fechaNueHum = Carbon::parse($request->nueva_fecha)->format('d/m/Y');
            Notificacion::crear(
                idReceptor: (int) $cronograma->id_instructor,
                idCausante: $user->id,
                titulo: "Actividad Pospuesta",
                mensaje: "La actividad '{$cronograma->actividad?->nombre}' programada originalmente para el {$fechaAntHum} ha sido pospuesta para el {$fechaNueHum}. Motivo: {$request->motivo}.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'posponer'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Actividad pospuesta con éxito! Estado actualizado a "Pospuesta" y reubicada en la nueva fecha.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al posponer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Criterio 9: Eliminar programación que aún no se ha realizado.
     * No permite eliminar si ya tiene asistencia registrada.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('controlecp.admin')) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $cronograma = Cronograma::with('recursos')->find($id);
        if (!$cronograma) {
            return response()->json(['success' => false, 'message' => 'Programación no encontrada.'], 404);
        }

        // Criterio 9: NO permitir eliminar si ya tiene asistencia registrada o si fue finalizada
        if ($cronograma->estado === 'Finalizada') {
            return response()->json([
                'success' => false,
                'message' => 'No es posible eliminar una programación que ya ha sido Finalizada.',
            ], 422);
        }

        // Verificar si existe registro de asistencia para este cronograma
        if (Schema::hasTable('asistencias') && Schema::hasColumn('asistencias', 'id_cronograma')) {
            $tieneAsistencia = DB::table('asistencias')->where('id_cronograma', $cronograma->id_cronograma)->exists();
            if ($tieneAsistencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la programación porque ya cuenta con asistencia registrada.',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Devolver cantidades reservadas de inventario
            foreach ($cronograma->recursos as $recurso) {
                $inv = Inventario::find($recurso->id_inventario);
                if ($inv) {
                    $inv->increment('cantidad_disponible', $recurso->cantidad);
                }
            }

            // Notificar al instructor antes de eliminar
            $fechaSesion = Carbon::parse($cronograma->fecha)->format('d/m/Y');
            Notificacion::crear(
                idReceptor: (int) $cronograma->id_instructor,
                idCausante: $user->id,
                titulo: "Actividad Cancelada / Eliminada",
                mensaje: "La programación de la actividad '{$cronograma->actividad?->nombre}' del {$fechaSesion} fue cancelada y eliminada por el administrador.",
                referenciaTipo: 'cronograma',
                referenciaId: $cronograma->id_cronograma,
                claveEvento: 'eliminar'
            );

            // Eliminar recursos asociados e historial
            CronogramaRecurso::where('id_cronograma', $cronograma->id_cronograma)->delete();
            CronogramaHistorial::where('id_cronograma', $cronograma->id_cronograma)->delete();

            $cronograma->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Programación eliminada con éxito y recursos liberados en el inventario!',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para verificar disponibilidad del instructor en tiempo real desde la UI.
     */
    public function verificarDisponibilidad(Request $request): JsonResponse
    {
        $instructorId = (int) $request->input('id_instructor');
        $fecha = $request->input('fecha');
        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');
        $excludeId = $request->filled('exclude_id') ? (int) $request->input('exclude_id') : null;

        if (!$instructorId || !$fecha || !$horaInicio || !$horaFin) {
            return response()->json(['disponible' => true]);
        }

        $conflicto = $this->validarDisponibilidadInstructor($instructorId, $fecha, $horaInicio, $horaFin, $excludeId);

        return response()->json([
            'disponible' => is_null($conflicto),
            'mensaje'    => $conflicto,
        ]);
    }

    // ── MÉTODOS AUXILIARES PRIVADOS ──────────────────────────────

    /**
     * Criterio 3: Validar que el instructor no tenga otra actividad, asesoría
     * o sesión de acompañamiento en el mismo horario.
     */
    private function validarDisponibilidadInstructor(
        int $idInstructor,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $excludeCronogramaId = null
    ): ?string {
        // 1. Validar en cronograma
        $query = Cronograma::where('id_instructor', $idInstructor)
            ->where('fecha', $fecha)
            ->whereNotIn('estado', ['Cancelada'])
            ->where(function ($q) use ($horaInicio, $horaFin) {
                // Solapamiento de rangos de tiempo
                $q->where(function ($sub) use ($horaInicio, $horaFin) {
                    $sub->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                });
            });

        if ($excludeCronogramaId) {
            $query->where('id_cronograma', '!=', $excludeCronogramaId);
        }

        $conflictoCronograma = $query->with('actividad')->first();
        if ($conflictoCronograma) {
            $nombreAct = $conflictoCronograma->actividad?->nombre ?? 'Actividad ECP';
            $horario = $conflictoCronograma->horario_formateado;
            return "El instructor ya tiene la actividad '{$nombreAct}' programada en esa fecha ({$horario}).";
        }

        // 2. Validar en asesorias (si existe la tabla)
        if (Schema::hasTable('asesorias') && Schema::hasColumn('asesorias', 'id_usuario_instructor')) {
            $conflictoAsesoria = DB::table('asesorias')
                ->where('id_usuario_instructor', $idInstructor)
                ->whereDate('fecha', $fecha)
                ->whereNotIn('estado', ['Cancelada', 'Rechazada'])
                ->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin)
                      ->where('hora_fin', '>', $horaInicio);
                })
                ->first();

            if ($conflictoAsesoria) {
                return "El instructor tiene una Asesoría programada en ese horario ({$conflictoAsesoria->hora_inicio} - {$conflictoAsesoria->hora_fin}).";
            }
        }

        // 3. Validar en acompañamiento_sesiones (si existe la tabla)
        $tablaAcomp = Schema::hasTable('acompaniamiento_sesiones') ? 'acompaniamiento_sesiones' :
                     (Schema::hasTable('acompañamiento_sesiones') ? 'acompañamiento_sesiones' : null);

        if ($tablaAcomp && Schema::hasTable('acompañamientos')) {
            $conflictoAcomp = DB::table($tablaAcomp . ' as s')
                ->join('acompañamientos as a', 's.id_acompañamiento', '=', 'a.id_acompañamiento')
                ->where('a.id_usuario_instructor', $idInstructor)
                ->whereDate('s.fecha', $fecha)
                ->whereNotIn('s.estado', ['Cancelada'])
                ->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('s.hora_inicio', '<', $horaFin)
                      ->where('s.hora_fin', '>', $horaInicio);
                })
                ->first();

            if ($conflictoAcomp) {
                return "El instructor tiene una sesión de Acompañamiento en ese horario.";
            }
        }

        return null;
    }

    /**
     * Criterio 4: Validar disponibilidad de recursos en inventario.
     */
    private function validarRecursosDisponibles(array $recursos): bool|string
    {
        foreach ($recursos as $item) {
            $idInv = (int) ($item['id_inventario'] ?? 0);
            $cantRequerida = (int) ($item['cantidad'] ?? 0);

            if ($cantRequerida <= 0) {
                return "La cantidad solicitada para cada recurso debe ser mayor a 0.";
            }

            $inventario = Inventario::find($idInv);
            if (!$inventario) {
                return "Uno de los recursos seleccionados no existe en el inventario.";
            }

            if ($inventario->estado === 'Inactivo') {
                return "El recurso '{$inventario->nombre}' está inactivo y no puede asignarse.";
            }

            if ($inventario->cantidad_disponible < $cantRequerida) {
                return "El recurso '{$inventario->nombre}' no tiene suficiente disponibilidad. Disponible: {$inventario->cantidad_disponible}, solicitado: {$cantRequerida}.";
            }
        }

        return true;
    }
}
