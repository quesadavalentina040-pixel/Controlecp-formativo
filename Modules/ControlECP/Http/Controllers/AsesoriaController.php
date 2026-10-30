<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\AsesoriaHistorial;
use Modules\ControlECP\Entities\AsesoriaRecurso;
use Modules\ControlECP\Entities\AsesoriaSeguimiento;
use Modules\ControlECP\Entities\CategoriaAsesoria;
use Modules\ControlECP\Entities\DisponibilidadInstructor;
use Modules\ControlECP\Entities\InstructorCategoria;
use Modules\ControlECP\Http\Requests\AsignarRecursoRequest;
use Modules\ControlECP\Http\Requests\CancelarAsesoriaRequest;
use Modules\ControlECP\Http\Requests\CorregirSeguimientoRequest;
use Modules\ControlECP\Http\Requests\ProgramarAsesoriaRequest;
use Modules\ControlECP\Http\Requests\ReprogramarAsesoriaRequest;
use Modules\ControlECP\Http\Requests\StoreAsesoriaRequest;
use Modules\ControlECP\Http\Requests\StoreSeguimientoRequest;
use Modules\SICA\Entities\Inventory;

class AsesoriaController extends Controller // Controlador administrativo para solicitudes, programación y seguimiento de asesorías.
{
    private function verificarAccesoAdmin() // Verifica acceso del rol Administrador | verificarAccesoAdmin(): Valida el rol administrador antes de permitir operaciones administrativas; se usa al entrar a cada acción protegida.
    {
        $usuario = Auth::user(); // Auth::user devuelve el usuario de la sesión actual o null cuando no hay sesión.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // hasRole comprueba el rol y, si falta, se devuelve una redirección; null significa que puede continuar.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al módulo de Asesorías.');
        }
        return null;
    }

    public function index(Request $request) // Listado principal con indicadores, filtros, tabla y gráficas | index(): Prepara el panel con asesorías, indicadores, filtros y relaciones; se usa al abrir el listado administrativo.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Guarda la respuesta del control de acceso para detener la acción solo si fue denegada.

        $indicadores = [ // Indicadores reales desde la base de datos | Cada where agrega una condición SQL y count ejecuta un conteo sin traer todas las filas.
            'solicitadas' => Asesoria::where('estado', 'Solicitada')->count(),
            'programadas' => Asesoria::where('estado', 'Programada')->count(),
            'realizadas'  => Asesoria::where('estado', 'Realizada')->count(),
            'sin_asignar' => Asesoria::whereNull('id_usuario_instructor')->where('estado', '!=', 'Cancelada')->count(),
            'canceladas'  => Asesoria::where('estado', 'Cancelada')->count(),
        ];

        $query = Asesoria::with([ // Construcción de consulta con filtros | with carga las relaciones de antemano para evitar una consulta adicional por cada asesoría.
            'categoria',
            'aprendiz.person',
            'instructor.person',
            'usuarioRegistro.person',
            'usuarioCancelador.person',
            'historial',
            'seguimientos',
            'recursos.inventario.element',
        ]);

        if ($request->filled('categoria_id')) { // Filtro por categoría | filled solo activa el filtro si categoría_id existe y tiene un valor no vacío.
            $query->where('id_categoria_asesoria', $request->categoria_id); // where agrega a la consulta la igualdad entre la columna y el filtro recibido.
        }

        if ($request->filled('instructor_id')) { // Filtro por instructor o sin asignar | Se comprueba que haya un filtro; la cadena especial representa filas sin instructor asignado.
            if ($request->instructor_id === 'sin_asignar') {
                $query->whereNull('id_usuario_instructor'); // whereNull filtra filas cuyo instructor está guardado como NULL.
            } else {
                $query->where('id_usuario_instructor', $request->instructor_id); // En otro caso, where busca coincidencia exacta con el id seleccionado.
            }
        }

        if ($request->filled('estado')) { // Filtro por estado | Solo incorpora la condición de estado cuando el formulario envió un valor.
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) { // Filtro por rango de fechas | whereDate compara únicamente la parte de fecha; >= incluye el límite inicial.
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) { // El límite final también es inclusivo, pues se compara con <=.
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('aprendiz')) { // Filtro buscador por nombre, apellido, correo o documento del aprendiz | filled evita aplicar una búsqueda vacía; trim quita espacios sobrantes al principio y al final.
            $buscar = trim($request->aprendiz);
            $query->whereHas('aprendiz', function ($q) use ($buscar) { // whereHas limita asesorías según columnas de su aprendiz relacionado.
                $q->where('nickname', 'like', "%{$buscar}%") // where inicia el grupo; orWhere permite que cualquiera de los campos coincida con el texto parcial.
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhereHas('person', function ($qp) use ($buscar) { // orWhereHas incluye coincidencias en la relación persona asociada al aprendiz.
                      $qp->where('first_name', 'like', "%{$buscar}%")
                         ->orWhere('first_last_name', 'like', "%{$buscar}%")
                         ->orWhere('second_last_name', 'like', "%{$buscar}%")
                         ->orWhere('document_number', 'like', "%{$buscar}%");
                  });
            });
        }

        $asesorias = $query->orderBy('fecha', 'desc') // orderBy ordena primero por fecha y luego por hora, paginate consulta una página y withQueryString conserva filtros en los enlaces.
            ->orderBy('hora_inicio', 'desc')
            ->paginate(10)
            ->withQueryString();

        $graficas = $this->obtenerDatosGraficas(); // Datos para gráficas (día, semana, trimestre) | El método auxiliar realiza los conteos agrupados que consumen las gráficas.

        $categorias = CategoriaAsesoria::withCount('instructores')->orderBy('nombre')->get(); // Listados para selectores y modales | withCount agrega el número de instructores relacionados; orderBy ordena y get ejecuta la consulta.

        $instructores = User::whereHas('roles', function ($q) { // whereHas limita usuarios por su relación de roles; el callback agrega la condición sobre slug. | with carga la persona asociada y get obtiene la colección de instructores.
            $q->where('slug', 'controlecp.instructor');
        })->with('person')->get();

        $aprendices = User::whereHas('roles', function ($q) { // Construye del mismo modo la lista de usuarios que tienen el rol de aprendiz.
            $q->where('slug', 'controlecp.apprentice');
        })->with('person')->get();

        $inventarios = Inventory::with('element')->orderBy('id')->get(); // Carga el elemento relacionado, ordena por id y get obtiene los registros para el selector.

        return view('controlecp::administrador.asesoria', compact( // Envía el conjunto de datos ya preparado a la vista administrativa.
            'indicadores',
            'asesorias',
            'graficas',
            'categorias',
            'instructores',
            'aprendices',
            'inventarios'
        ));
    }

    public function show($id) // Retorna datos de una asesoría para el modal de detalle | show(): Carga y serializa una asesoría con sus relaciones; se usa al solicitar el detalle para el modal.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Evita consultar detalles si el usuario no pasó la comprobación administrativa.

        $asesoria = Asesoria::with([ // with precarga relaciones y findOrFail busca por id o genera una respuesta 404.
            'categoria',
            'aprendiz.person',
            'instructor.person',
            'usuarioRegistro.person',
            'usuarioCancelador.person',
            'historial.usuario.person',
            'seguimientos.usuario.person',
            'seguimientos.seguimientoOriginal',
            'recursos.inventario.element',
        ])->findOrFail($id);

        return response()->json([ // json serializa el arreglo como respuesta HTTP para que la interfaz lo consuma.
            'success' => true,
            'asesoria' => [
                'id_asesoria'        => $asesoria->id_asesoria,
                'estado'             => $asesoria->estado,
                'motivo'             => $asesoria->motivo,
                'motivo_cancelacion' => $asesoria->motivo_cancelacion,
                'fecha'              => $asesoria->fecha ? $asesoria->fecha->format('Y-m-d') : null,
                'fecha_formateada'   => $asesoria->fecha ? $asesoria->fecha->format('d/m/Y') : null,
                'hora_inicio'        => substr($asesoria->hora_inicio, 0, 5),
                'hora_fin'           => substr($asesoria->hora_fin, 0, 5),
                'fecha_solicitud'    => $asesoria->fecha_solicitud ? $asesoria->fecha_solicitud->format('d/m/Y H:i') : null,
                'categoria'          => $asesoria->categoria ? $asesoria->categoria->nombre : 'Sin categoría',
                'id_categoria_asesoria' => $asesoria->id_categoria_asesoria,
                'aprendiz'           => [
                    'id'     => $asesoria->aprendiz ? $asesoria->aprendiz->id : null,
                    'nombre' => $asesoria->aprendiz ? $asesoria->aprendiz->full_name : 'No registrado',
                    'email'  => $asesoria->aprendiz ? $asesoria->aprendiz->email : '',
                ],
                'instructor'         => [
                    'id'     => $asesoria->instructor ? $asesoria->instructor->id : null,
                    'nombre' => $asesoria->instructor ? $asesoria->instructor->full_name : 'Sin asignar',
                    'email'  => $asesoria->instructor ? $asesoria->instructor->email : '',
                ],
                'registro_por'       => $asesoria->usuarioRegistro ? $asesoria->usuarioRegistro->full_name : 'Sistema',
                'cancelado_por'      => $asesoria->usuarioCancelador ? $asesoria->usuarioCancelador->full_name : null,
                'historial'          => $asesoria->historial->map(function ($h) { // map recorre la colección relacionada y transforma cada fila a los campos que necesita el modal.
                    return [
                        'accion'         => $h->accion,
                        'motivo'         => $h->motivo,
                        'fecha_anterior' => $h->fecha_anterior ? $h->fecha_anterior->format('d/m/Y') : null,
                        'hora_anterior'  => $h->hora_anterior ? substr($h->hora_anterior, 0, 5) : null,
                        'fecha_nueva'    => $h->fecha_nueva ? $h->fecha_nueva->format('d/m/Y') : null,
                        'hora_nueva'     => $h->hora_nueva ? substr($h->hora_nueva, 0, 5) : null,
                        'usuario'        => $h->usuario ? $h->usuario->full_name : 'Usuario',
                        'fecha'          => $h->fecha ? $h->fecha->format('d/m/Y H:i') : null,
                    ];
                }),
                'seguimientos'       => $asesoria->seguimientos->map(function ($s) { // Convierte los seguimientos a un arreglo simple; las relaciones opcionales se protegen con ternarios.
                    return [
                        'id_seguimiento'    => $s->id_asesoria_seguimiento,
                        'avances'           => $s->avances,
                        'compromisos'       => $s->compromisos,
                        'observaciones'     => $s->observaciones,
                        'motivo_correccion' => $s->motivo_correccion,
                        'es_correccion'     => !is_null($s->id_asesoria_seguimiento_corrige),
                        'usuario'           => $s->usuario ? $s->usuario->full_name : 'Usuario',
                        'fecha'             => $s->fecha ? $s->fecha->format('d/m/Y H:i') : null,
                    ];
                }),
                'recursos'           => $asesoria->recursos->map(function ($r) { // Recorre recursos y usa la relación inventario.element para mostrar el nombre disponible.
                    return [
                        'id_recurso' => $r->id_asesoria_recurso,
                        'nombre'     => $r->inventario && $r->inventario->element ? $r->inventario->element->name : 'Recurso #'.$r->id_inventario,
                        'cantidad'   => $r->cantidad,
                    ];
                }),
            ],
        ]);
    }

    private function obtenerDatosGraficas() // Calcula datos para las gráficas simples (día, semana, trimestre) | obtenerDatosGraficas(): Calcula las series resumidas de asesorías; se usa para alimentar las gráficas del panel.
    {
        $hoy = Carbon::today(); // today crea una fecha de hoy sin hora; year extrae el año que se usará en los conteos trimestrales.
        $anioActual = $hoy->year;

        $diasLabels = []; // 1. Por día (últimos 7 días)
        $diasValores = [];
        for ($i = 6; $i >= 0; $i--) { // El for itera del sexto día atrás hasta hoy para formar una serie de siete días.
            $f = $hoy->copy()->subDays($i);
            $diasLabels[] = $f->translatedFormat('D d');
            $diasValores[] = Asesoria::whereDate('fecha', $f->format('Y-m-d'))->count(); // whereDate filtra por día calendario y count devuelve el total de asesorías para esa fecha.
        }

        $inicioSemana = $hoy->copy()->startOfWeek(); // 2. Por semana (días Lunes a Sábado de la semana actual)
        $semanaLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $semanaValores = [];
        for ($d = 0; $d < 6; $d++) { // Repite el conteo para lunes a sábado, calculando la fecha de cada día desde el inicio de semana.
            $diaFecha = $inicioSemana->copy()->addDays($d);
            $semanaValores[] = Asesoria::whereDate('fecha', $diaFecha->format('Y-m-d'))->count();
        }

        $trimestres = [ // 3. Por trimestre del año actual (T1 a T4) | Cada cadena whereYear/whereMonth agrega límites de año y meses antes de ejecutar count.
            'T1 (Ene-Mar)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 1)->whereMonth('fecha', '<=', 3)->count(),
            'T2 (Abr-Jun)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 4)->whereMonth('fecha', '<=', 6)->count(),
            'T3 (Jul-Sep)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 7)->whereMonth('fecha', '<=', 9)->count(),
            'T4 (Oct-Dic)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 10)->whereMonth('fecha', '<=', 12)->count(),
        ];

        return [ // Devuelve etiquetas y series con la forma que espera el componente de gráficas.
            'dias'       => ['labels' => $diasLabels, 'valores' => $diasValores],
            'semana'     => ['labels' => $semanaLabels, 'valores' => $semanaValores],
            'trimestres' => $trimestres,
        ];
    }

    public function instructoresDisponibles(Request $request) // Busca instructores habilitados por categoría, disponibilidad y sin cruce de horarios (AJAX) | instructoresDisponibles(): Busca instructores elegibles según categoría y disponibilidad; se usa al asignar o programar una asesoría.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Un rechazo de acceso se retorna antes de leer parámetros o consultar instructores.

        $categoriaId = $request->get('categoria_id'); // get lee los valores recibidos (incluidos parámetros de consulta) sin aplicar reglas de validación.
        $fechaInput  = $request->get('fecha');
        $horaInicio  = $request->get('hora_inicio');
        $horaFin     = $request->get('hora_fin');
        $asesoriaId  = $request->get('asesoria_id');

        if (!$categoriaId || !$fechaInput || !$horaInicio || !$horaFin) { // La condición comprueba que todos los datos mínimos tengan valor antes de procesar fechas.
            return response()->json([ // json devuelve los datos y el mensaje en un formato consumible por la llamada AJAX.
                'success'      => false,
                'mensaje'      => 'Debes proporcionar categoría, fecha y rango de horas.',
                'instructores' => [],
            ]);
        }

        $fecha = Carbon::parse($fechaInput); // parse convierte el texto de fecha a Carbon; se extraen año, trimestre y día ISO para cruzarlos con la agenda.
        $anio = $fecha->year;
        $trimestre = ceil($fecha->month / 3);
        $diaSemana = $fecha->dayOfWeekIso; // 1 Lunes a 7 Domingo
        $hInicio = substr($horaInicio, 0, 5);
        $hFin = substr($horaFin, 0, 5);

        $instructoresIds = InstructorCategoria::where('id_categoria_asesoria', $categoriaId) // where filtra la tabla intermedia por categoría y pluck obtiene solo los ids vinculados.
            ->pluck('id_usuario');

        if ($instructoresIds->isEmpty()) { // Si la categoría aún no tiene instructores vinculados, permitimos a los instructores del sistema | isEmpty detecta colección vacía; whereHas filtra usuarios por rol y pluck trae únicamente sus ids.
            $instructoresIds = User::whereHas('roles', function ($q) {
                $q->where('slug', 'controlecp.instructor');
            })->pluck('id');
        }

        $conFranjaIds = DisponibilidadInstructor::whereIn('id_usuario', $instructoresIds) // 2. Instructores que tienen franja de disponibilidad habilitada y vigente para este horario | whereIn limita a los ids candidatos; las demás condiciones cotejan vigencia y que la franja cubra todo el horario.
            ->where('anio', $anio)
            ->where('trimestre', $trimestre)
            ->where('dia_semana', $diaSemana)
            ->where('habilitada', true)
            ->where('vigente', true)
            ->where('hora_inicio', '<=', $hInicio)
            ->where('hora_fin', '>=', $hFin)
            ->pluck('id_usuario');

        $ocupadosIds = Asesoria::whereIn('id_usuario_instructor', $instructoresIds) // 3. Filtrar instructores que tengan un cruce real de horario con otra asesoría activa | when añade una condición solo si ya se está editando una asesoría; el grupo de where verifica solapamiento.
            ->whereDate('fecha', $fecha->format('Y-m-d'))
            ->where('estado', '!=', 'Cancelada')
            ->when($asesoriaId, function ($q) use ($asesoriaId) {
                $q->where('id_asesoria', '!=', $asesoriaId);
            })
            ->where(function ($q) use ($hInicio, $hFin) {
                $q->where('hora_inicio', '<', $hFin)
                  ->where('hora_fin', '>', $hInicio);
            })
            ->pluck('id_usuario_instructor');

        $candidatosSinCruce = $instructoresIds->diff($ocupadosIds); // Instructores libres sin cruce de horario | diff resta de la colección de candidatos los ids que aparecen ocupados.

        $instructores = User::whereIn('id', $candidatosSinCruce)->with('person')->get()->map(function ($u) use ($conFranjaIds) { // Se construye el listado dando prioridad a los instructores con franja habilitada | whereIn consulta los candidatos, with precarga la persona, get ejecuta la búsqueda y map adapta cada usuario.
            $tieneFranja = $conFranjaIds->contains($u->id); // contains comprueba si el id está entre quienes tienen una franja coincidente.
            return [
                'id'              => $u->id,
                'nombre'          => $u->full_name . ($tieneFranja ? ' [Franja disponible]' : ''),
                'email'           => $u->email,
                'franja_coincide' => $tieneFranja,
            ];
        })->sortByDesc('franja_coincide')->values();

        return response()->json([ // Responde a AJAX con la lista final y el número de opciones disponibles.
            'success'      => true,
            'dia_semana'   => $diaSemana,
            'trimestre'    => $trimestre,
            'instructores' => $instructores,
            'total'        => $instructores->count(),
        ]);
    }

    public function store(StoreAsesoriaRequest $request) // Registra una nueva solicitud de asesoría | store(): Valida y registra una nueva asesoría; se usa al enviar el formulario de creación.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // StoreAsesoriaRequest valida los datos antes de entrar aquí; este control restringe además el rol administrador.

        $idInstructor = $request->filled('id_usuario_instructor') ? $request->id_usuario_instructor : null; // filled trata cadenas vacías como no informadas; en ese caso se guarda null como instructor pendiente.
        $estado = 'Solicitada';

        if ($idInstructor) { // Si se seleccionó un instructor, validar cruces antes de guardar
            $hInicio = substr($request->hora_inicio, 0, 5); // substr reduce la hora a HH:MM para comparar valores recibidos con el formato de base de datos.
            $hFin = substr($request->hora_fin, 0, 5);

            $hayCruce = Asesoria::where('id_usuario_instructor', $idInstructor) // where encadena restricciones SQL y exists pregunta si hay alguna fila coincidente, sin cargarla completa.
                ->whereDate('fecha', $request->fecha)
                ->where('estado', '!=', 'Cancelada')
                ->where(function ($q) use ($hInicio, $hFin) {
                    $q->where('hora_inicio', '<', $hFin)
                      ->where('hora_fin', '>', $hInicio);
                })
                ->exists();

            if ($hayCruce) {
                session()->flash('modal_abrir', 'modalNuevaSolicitud'); // flash conserva el modal que debe reabrirse; back devuelve al formulario y repone entrada y errores.
                return back()->withInput()->withErrors([
                    'id_usuario_instructor' => 'El instructor seleccionado tiene un cruce de horario con otra asesoría.'
                ])->with('error', 'Existe un cruce con otra asesoría o sesión en ese horario.');
            }

            $estado = 'Programada';
        }

        $asesoria = new Asesoria(); // new crea un modelo aún no persistido; estas asignaciones mapean datos validados a sus columnas.
        $asesoria->id_categoria_asesoria = $request->id_categoria_asesoria;
        $asesoria->id_usuario_aprendiz   = $request->id_usuario_aprendiz;
        $asesoria->id_usuario_instructor = $idInstructor;
        $asesoria->id_usuario_registro   = Auth::id();
        $asesoria->motivo                = $request->motivo;
        $asesoria->fecha                 = $request->fecha;
        $asesoria->hora_inicio           = $request->hora_inicio;
        $asesoria->hora_fin              = $request->hora_fin;
        $asesoria->estado                = $estado;
        $asesoria->fecha_solicitud       = Carbon::now();
        $asesoria->save(); // save inserta la asesoría y asigna su clave generada al modelo.

        if ($idInstructor) { // Si hubo instructor se envían dos avisos; redirect y with devuelven al listado con mensaje temporal.
            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesoría ha sido programada.');
            $this->notificarUsuario($asesoria->instructor, 'Se te ha asignado una nueva asesoría.');
            return redirect()->route('controlecp.administrador.asesorias.index')
                ->with('success', 'Solicitud registrada y programada exitosamente con el instructor asignado.');
        }

        return redirect()->route('controlecp.administrador.asesorias.index') // Sin instructor se confirma que la solicitud quedó pendiente en el mismo listado administrativo.
            ->with('success', 'Solicitud registrada como pendiente (sin asignar).');
    }

    public function update(Request $request, $id) // Actualiza los datos de una asesoría existente | update(): Actualiza los datos permitidos de una asesoría existente; se usa al guardar cambios administrativos.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Comprueba autorización y recupera la asesoría; findOrFail responde 404 para un id desconocido.

        $asesoria = Asesoria::findOrFail($id);

        $validator = Validator::make($request->all(), [ // all reúne entradas para Validator; make define reglas y mensajes personalizados sin guardarlos todavía.
            'id_usuario_aprendiz'   => 'required|exists:users,id',
            'id_categoria_asesoria' => 'required|exists:categorias_asesoria,id_categoria_asesoria',
            'fecha'                 => 'required|date',
            'hora_inicio'           => 'required|date_format:H:i',
            'hora_fin'              => 'required|date_format:H:i|after:hora_inicio',
            'id_usuario_instructor' => 'nullable|exists:users,id',
            'motivo'                => 'required|string|min:5',
        ], [
            'id_usuario_aprendiz.required'   => 'El aprendiz es obligatorio.',
            'id_categoria_asesoria.required' => 'La categoría temática es obligatoria.',
            'fecha.required'                 => 'La fecha es obligatoria.',
            'hora_inicio.required'           => 'La hora de inicio es obligatoria.',
            'hora_fin.required'              => 'La hora de fin es obligatoria.',
            'hora_fin.after'                 => 'La hora de fin debe ser posterior a la hora de inicio.',
            'motivo.required'                => 'El motivo es obligatorio.',
            'motivo.min'                     => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        if ($validator->fails()) { // fails detecta errores; flash conserva el modal e id, y back devuelve errores y datos previamente ingresados.
            session()->flash('modal_abrir', 'modalEditarAsesoria');
            session()->flash('modal_id', $id);
            return back()->withInput()->withErrors($validator)->with('error', 'Corrige los errores del formulario de edición.');
        }

        $idInstructor = $request->filled('id_usuario_instructor') ? $request->id_usuario_instructor : null; // filled distingue un instructor elegido de un campo vacío y normaliza este último a null.

        if ($idInstructor) { // Si se asignó un instructor, validar que no tenga cruce de horario con otra asesoría activa
            $hInicio = substr($request->hora_inicio, 0, 5); // Normaliza los valores de hora a cinco caracteres antes de comparar intervalos.
            $hFin = substr($request->hora_fin, 0, 5);

            $hayCruce = Asesoria::where('id_usuario_instructor', $idInstructor) // where aplica los criterios SQL y exists solo comprueba si el solapamiento existe.
                ->whereDate('fecha', $request->fecha)
                ->where('id_asesoria', '!=', $id)
                ->where('estado', '!=', 'Cancelada')
                ->where(function ($q) use ($hInicio, $hFin) {
                    $q->where('hora_inicio', '<', $hFin)
                      ->where('hora_fin', '>', $hInicio);
                })
                ->exists();

            if ($hayCruce) {
                session()->flash('modal_abrir', 'modalEditarAsesoria'); // Mantiene abierto el modal de edición y devuelve el mensaje asociado al campo conflictivo.
                session()->flash('modal_id', $id);
                return back()->withInput()->withErrors([
                    'id_usuario_instructor' => 'El instructor seleccionado tiene un cruce de horario con otra asesoría.'
                ])->with('error', 'Existe un cruce con otra asesoría o sesión en ese horario.');
            }
        }

        $asesoria->id_categoria_asesoria = $request->id_categoria_asesoria; // Copia los campos aceptados al modelo que ya fue consultado; no cambia aquí la identidad del registro.
        $asesoria->id_usuario_aprendiz   = $request->id_usuario_aprendiz;
        $asesoria->id_usuario_instructor = $idInstructor;
        $asesoria->motivo                = $request->motivo;
        $asesoria->fecha                 = $request->fecha;
        $asesoria->hora_inicio           = $request->hora_inicio;
        $asesoria->hora_fin              = $request->hora_fin;

        if ($asesoria->estado === 'Solicitada' && $idInstructor) { // Si estaba en Solicitada y ahora tiene instructor, pasa a Programada
            $asesoria->estado = 'Programada'; // Al asignar un instructor, la solicitud deja de estar pendiente y pasa a programación.
        } elseif ($asesoria->estado === 'Programada' && !$idInstructor) {
            $asesoria->estado = 'Solicitada'; // Si se retira el instructor, vuelve al estado pendiente de asignación.
        }

        $asesoria->save(); // save actualiza en la base de datos los atributos modificados del modelo.

        return redirect()->route('controlecp.administrador.asesorias.index') // Redirige al listado y agrega un mensaje de éxito de un solo uso.
            ->with('success', 'Asesoría #' . $id . ' actualizada exitosamente.');
    }

    public function programar(ProgramarAsesoriaRequest $request, $id) // Asigna y programa una asesoría validando cruces de horario | programar(): Asigna fecha, horario e instructor a una asesoría; se usa al confirmar su programación.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // El request dedicado ya validó campos; el helper comprueba adicionalmente que sea administrador.

        $asesoria = Asesoria::findOrFail($id); // Busca la asesoría objetivo o genera 404 antes de actualizarla.

        $hInicio = substr($request->hora_inicio, 0, 5); // Normaliza ambas horas al formato HH:MM que se emplea en el cruce de intervalos.
        $hFin = substr($request->hora_fin, 0, 5);

        $hayCruce = Asesoria::where('id_usuario_instructor', $request->id_usuario_instructor) // Validación de cruce con otra asesoría activa del mismo instructor | La consulta excluye el registro actual y las canceladas; exists solo necesita saber si hay coincidencia.
            ->whereDate('fecha', $request->fecha)
            ->where('id_asesoria', '!=', $id)
            ->where('estado', '!=', 'Cancelada')
            ->where(function ($q) use ($hInicio, $hFin) {
                $q->where('hora_inicio', '<', $hFin)
                  ->where('hora_fin', '>', $hInicio);
            })
            ->exists();

        if ($hayCruce) {
            session()->flash('modal_abrir', 'modalProgramar'); // Guarda qué modal reabrir y devuelve el formulario con el error de horario.
            session()->flash('modal_programar_id', $id);
            return back()->withInput()->withErrors([
                'id_usuario_instructor' => 'Existe un cruce con otra asesoría o sesión para este instructor en ese horario.'
            ])->with('error', 'Existe un cruce con otra asesoría o sesión en ese horario.');
        }

        DB::transaction(function () use ($asesoria, $request) { // transaction agrupa cambios y notificaciones: si una operación lanza error, la base de datos revierte el bloque.
            $asesoria->id_usuario_instructor = $request->id_usuario_instructor; // Asigna instructor, fecha y horas recibidas, cambia estado y persiste el modelo.
            $asesoria->fecha                 = $request->fecha;
            $asesoria->hora_inicio           = $request->hora_inicio;
            $asesoria->hora_fin              = $request->hora_fin;
            $asesoria->estado                = 'Programada';
            $asesoria->save();

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesoría ha sido programada.'); // Notifica a las dos personas relacionadas después de guardar la programación.
            $this->notificarUsuario($asesoria->instructor, 'Se te ha asignado una asesoría para acompañamiento.');
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Devuelve al listado con una confirmación temporal de la asignación.
            ->with('success', 'Asesoría asignada y programada exitosamente.');
    }

    private function notificarUsuario($usuario, $mensaje, $asesoria = null, $tipoAccion = 'actualizacion') // Registra notificación en la base de datos para el usuario destinatario | notificarUsuario(): Envía al usuario la notificación asociada a un cambio; se usa después de acciones sobre la asesoría.
    {
        if (!$usuario) return; // Evita intentar notificar una relación opcional que no tiene usuario asociado.

        try {
            $servicio = app(\Modules\ControlECP\Services\NotificacionService::class); // app resuelve el servicio desde el contenedor de Laravel para crear la notificación.
            $claveEvento = $asesoria // El identificador de evento diferencia cada aviso; se deriva de la asesoría o del usuario.
                ? "asesoria_{$asesoria->id_asesoria}_{$tipoAccion}_" . time()
                : "notif_user_{$usuario->id}_" . uniqid();

            $servicio->crear([ // crear recibe los datos para persistir destinatario, causante, mensaje y referencia al evento.
                'id_usuario_receptor' => $usuario->id,
                'id_usuario_causante' => Auth::id(),
                'modulo'              => 'Asesorías',
                'titulo'              => 'Novedad en Asesoría ECP',
                'mensaje'             => $mensaje,
                'referencia_tipo'     => 'asesoria',
                'referencia_id'       => $asesoria ? $asesoria->id_asesoria : null,
                'clave_evento'        => $claveEvento,
                'clave_agrupacion'    => $asesoria ? "asesoria_{$asesoria->id_asesoria}" : null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo registrar notificación: " . $e->getMessage()); // Registra el fallo en el log sin detener la operación principal de asesoría.
        }
    }

    public function reprogramar(ReprogramarAsesoriaRequest $request, $id) // reprogramar(): Valida y guarda una nueva programación; se usa cuando se modifica la fecha o el horario asignado.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Valida rol y datos mediante el helper y el request especializado.

        $asesoria = Asesoria::findOrFail($id); // Recupera la asesoría o retorna un 404 si no existe.

        if ($asesoria->estado === 'Cancelada' || $asesoria->estado === 'Realizada') { // No permite modificar una asesoría que ya terminó o fue cancelada.
            return back()->with('error', 'No se puede reprogramar una asesoría cancelada o realizada.');
        }

        if ($asesoria->id_usuario_instructor) { // Si tiene instructor asignado, validar cruces en la nueva fecha y horario
            $hInicio = substr($request->hora_inicio, 0, 5); // Normaliza las horas recibidas para comparar correctamente el intervalo nuevo.
            $hFin = substr($request->hora_fin, 0, 5);

            $hayCruce = Asesoria::where('id_usuario_instructor', $asesoria->id_usuario_instructor) // Busca otra asesoría activa del mismo instructor en la fecha nueva y con horas superpuestas.
                ->whereDate('fecha', $request->fecha)
                ->where('id_asesoria', '!=', $id)
                ->where('estado', '!=', 'Cancelada')
                ->where(function ($q) use ($hInicio, $hFin) {
                    $q->where('hora_inicio', '<', $hFin)
                      ->where('hora_fin', '>', $hInicio);
                })
                ->exists();

            if ($hayCruce) {
                session()->flash('modal_abrir', 'modalReprogramar'); // Mantiene el formulario con los datos ingresados y añade el error sobre la hora de inicio.
                session()->flash('modal_id', $id);
                return back()->withInput()->withErrors([
                    'hora_inicio' => 'El instructor tiene un cruce de horario con otra asesoría en esa fecha y horario.'
                ])->with('error', 'Existe un cruce de horario con otra sesión del instructor.');
            }
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacción hace atómicos el registro histórico, el cambio de fecha/hora y los avisos.
            $hist = new AsesoriaHistorial(); // Guardar trazabilidad en asesoria_historial sin perder el historial anterior | Crea una nueva fila de historial con usuario, motivo y valores de antes/después.
            $hist->id_asesoria    = $id;
            $hist->id_usuario     = Auth::id();
            $hist->accion         = 'Reprogramo';
            $hist->motivo         = $request->motivo;
            $hist->fecha_anterior = $asesoria->fecha;
            $hist->hora_anterior  = $asesoria->hora_inicio;
            $hist->fecha_nueva    = $request->fecha;
            $hist->hora_nueva     = $request->hora_inicio;
            $hist->fecha          = Carbon::now();
            $hist->save(); // save conserva el evento histórico como registro independiente.

            $asesoria->fecha       = $request->fecha; // Actualizar asesoría
            $asesoria->hora_inicio = $request->hora_inicio;
            $asesoria->hora_fin    = $request->hora_fin;
            $asesoria->save(); // Persiste los datos nuevos en la asesoría original.

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesoría ha sido reprogramada.'); // Avisa al aprendiz e instructor; el segundo aviso solo se envía si hay instructor relacionado.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'Una de tus asesorías ha sido reprogramada.');
            }
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Al terminar la transacción, regresa al panel indicando que el cambio y su historial se guardaron.
            ->with('success', 'Asesoría reprogramada exitosamente. Se guardó el historial del cambio.');
    }

    public function cancelar(CancelarAsesoriaRequest $request, $id) // cancelar(): Registra la cancelación y su motivo; se usa al cancelar una asesoría.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // El request ya valida el motivo y el helper restringe la acción al administrador.

        $asesoria = Asesoria::findOrFail($id); // Recupera la asesoría que se cancelará; si no existe, findOrFail responde 404.

        if ($asesoria->estado === 'Cancelada') { // Evita duplicar el evento de cancelación para una asesoría que ya está cancelada.
            return back()->with('error', 'La asesoría ya se encuentra cancelada.');
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacción garantiza que historial, estado y notificaciones se procesen como una sola operación.
            $hist = new AsesoriaHistorial(); // Registrar acción en asesoria_historial | Crea una fila de auditoría con el estado horario previo y quién ejecutó la acción.
            $hist->id_asesoria    = $id;
            $hist->id_usuario     = Auth::id();
            $hist->accion         = 'Cancelo';
            $hist->motivo         = $request->motivo;
            $hist->fecha_anterior = $asesoria->fecha;
            $hist->hora_anterior  = $asesoria->hora_inicio;
            $hist->fecha_nueva    = null;
            $hist->hora_nueva     = null;
            $hist->fecha          = Carbon::now();
            $hist->save(); // Inserta el registro de historial antes de cambiar la asesoría.

            $asesoria->estado                = 'Cancelada'; // Actualizar asesoría con motivo y cancelador
            $asesoria->motivo_cancelacion    = $request->motivo;
            $asesoria->id_usuario_cancelador = Auth::id();
            $asesoria->save(); // Guarda el nuevo estado, el motivo y el id del usuario que canceló.

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesoría ha sido cancelada.'); // Envía el aviso al aprendiz y, si existe, a la persona instructora asignada.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'Una asesoría que tenías asignada ha sido cancelada.');
            }
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Regresa al listado con una confirmación persistida solo para la siguiente petición.
            ->with('success', 'Asesoría cancelada exitosamente y registrada en el historial.');
    }

    public function finalizar(Request $request, $id) // finalizar(): Marca como realizada una asesoría y registra su cierre; se usa al confirmar que la sesión terminó.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Comprueba permisos antes de modificar el estado.

        $asesoria = Asesoria::findOrFail($id); // Busca la asesoría objetivo y falla con 404 si no existe.

        if ($asesoria->estado === 'Cancelada') { // Las ramas de salida temprana protegen estados en los que no debe cerrarse la asesoría.
            return back()->with('error', 'No se puede finalizar una asesoría que ha sido cancelada.');
        }

        if ($asesoria->estado === 'Realizada') {
            return back()->with('info', 'La asesoría ya se encuentra marcada como finalizada.');
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacción mantiene consistente el cierre, el seguimiento opcional y las notificaciones.
            $asesoria->estado = 'Realizada'; // Cambiar estado a Realizada
            $asesoria->save(); // Persiste el cierre antes de generar sus avisos relacionados.

            if ($request->filled('observaciones')) { // Si se suministraron observaciones o notas de finalización, se registra el seguimiento correspondiente | filled exige un valor no vacío, por lo que un formulario sin observaciones no crea seguimiento.
                $seg = new AsesoriaSeguimiento(); // new prepara un registro de seguimiento en memoria y las asignaciones completan sus columnas.
                $seg->id_asesoria                     = $id;
                $seg->id_usuario                      = Auth::id();
                $seg->id_asesoria_seguimiento_corrige = null;
                $seg->avances                         = $request->input('avances', 'Asesoría finalizada y completada con éxito.');
                $seg->compromisos                     = $request->input('compromisos', 'Cierre formal de la asesoría.');
                $seg->observaciones                   = $request->observaciones;
                $seg->fecha                           = Carbon::now();
                $seg->save(); // Inserta la nota de cierre relacionada con esta asesoría.
            }

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesoría ha sido marcada como finalizada exitosamente.', $asesoria, 'finalizada'); // Avisa a las personas relacionadas; la notificación al instructor depende de que haya asignación.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'La asesoría asignada ha sido completada y finalizada.', $asesoria, 'finalizada');
            }
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Redirige al panel con el identificador de la asesoría cerrada en el mensaje.
            ->with('success', "Asesoría #{$id} marcada como finalizada exitosamente.");
    }

    public function storeSeguimiento(StoreSeguimientoRequest $request, $id) // storeSeguimiento(): Valida y guarda una nota de seguimiento; se usa al documentar avances de la asesoría.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // El request especializado valida los campos y el helper comprueba el rol antes de continuar.

        $asesoria = Asesoria::findOrFail($id); // Asegura que la nota se asocie con una asesoría existente.

        DB::transaction(function () use ($asesoria, $request, $id) { // Agrupa la creación de la nota y el cambio de estado en una sola transacción.
            $seg = new AsesoriaSeguimiento(); // Crea una nueva fila de seguimiento y vincula su autor y asesoría.
            $seg->id_asesoria                     = $id;
            $seg->id_usuario                      = Auth::id();
            $seg->id_asesoria_seguimiento_corrige = null;
            $seg->avances                         = $request->avances;
            $seg->compromisos                     = $request->compromisos;
            $seg->observaciones                   = $request->observaciones;
            $seg->fecha                           = Carbon::now();
            $seg->save(); // Inserta la nota validada en la tabla de seguimientos.

            $asesoria->estado = 'Realizada'; // Pasa a estado Realizada
            $asesoria->save(); // Guarda el nuevo estado junto con la nota, dentro de la misma transacción.
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Informa al usuario que se guardó el seguimiento y se cerró la asesoría.
            ->with('success', 'Seguimiento registrado exitosamente. La asesoría pasó a estado Realizada.');
    }

    public function corregirSeguimiento(CorregirSeguimientoRequest $request, $id) // corregirSeguimiento(): Registra la corrección de un seguimiento previo; se usa al rectificar una nota existente.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // El formulario llega validado por el request especializado; el helper comprueba el rol administrador.

        $original = AsesoriaSeguimiento::findOrFail($id); // $id es el id_asesoria_seguimiento original a corregir | findOrFail localiza la nota base sin modificarla y devuelve 404 si no existe.

        DB::transaction(function () use ($original, $request) { // Envuelve la nueva corrección en una transacción para evitar una fila parcial.
            $nuevo = new AsesoriaSeguimiento(); // Crea un registro NUEVO que apunta al original, nunca borra ni sobrescribe | El nuevo modelo conserva la trazabilidad enlazando su id con el seguimiento original.
            $nuevo->id_asesoria                     = $original->id_asesoria;
            $nuevo->id_usuario                      = Auth::id();
            $nuevo->id_asesoria_seguimiento_corrige = $original->id_asesoria_seguimiento;
            $nuevo->avances                         = $request->avances;
            $nuevo->compromisos                     = $request->compromisos;
            $nuevo->observaciones                   = $request->observaciones;
            $nuevo->motivo_correccion               = $request->motivo_correccion;
            $nuevo->fecha                           = Carbon::now();
            $nuevo->save(); // Inserta la corrección como una fila independiente.
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Regresa al listado sin alterar la nota anterior y comunica el resultado.
            ->with('success', 'Corrección de seguimiento registrada exitosamente sin alterar el registro original.');
    }

    public function agregarRecurso(AsignarRecursoRequest $request, $id) // agregarRecurso(): Asocia un recurso de inventario a una asesoría; se usa al asignar materiales necesarios.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // El request valida cantidad y recurso; aquí se verifica permiso administrativo.

        $asesoria = Asesoria::findOrFail($id); // Recupera ambos modelos y devuelve 404 si la asesoría o el inventario no existen.
        $inventario = Inventory::findOrFail($request->id_inventario);

        if ($inventario->stock < $request->cantidad) { // Compara disponibilidad antes de iniciar el descuento para evitar asignar más de lo existente.
            session()->flash('modal_abrir', 'modalRecursos'); // Preserva el modal y los valores del formulario para que el usuario corrija la cantidad.
            session()->flash('modal_id', $id);
            return back()->withInput()->withErrors([
                'cantidad' => "Stock insuficiente en inventario. Stock disponible: {$inventario->stock}"
            ])->with('error', 'Stock insuficiente para este recurso.');
        }

        DB::transaction(function () use ($asesoria, $inventario, $request, $id) { // Agrupa descuento y vínculo del recurso: ambos cambios se confirman juntos o ninguno queda aplicado.
            $inventario->stock -= $request->cantidad; // Descontar del inventario | Ajusta la existencia disponible y evita que amount resulte negativo.
            $inventario->amount = max(0, $inventario->amount - $request->cantidad);
            $inventario->save(); // Persiste los nuevos valores del inventario.

            $recurso = AsesoriaRecurso::where('id_asesoria', $id) // Vincular recurso en asesoria_recursos | where limita por asesoría e inventario; first obtiene la primera relación o null.
                ->where('id_inventario', $inventario->id)
                ->first();

            if ($recurso) { // Si el vínculo ya existe, suma la cantidad; si no, crea y guarda uno nuevo.
                $recurso->cantidad += $request->cantidad;
                $recurso->save(); // Guarda el total acumulado de este recurso para la asesoría.
            } else {
                $recurso = new AsesoriaRecurso(); // Prepara un vínculo nuevo entre la asesoría y el artículo de inventario.
                $recurso->id_asesoria   = $id;
                $recurso->id_inventario = $inventario->id;
                $recurso->cantidad      = $request->cantidad;
                $recurso->save(); // Inserta la relación con la cantidad solicitada.
            }
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Regresa al panel y confirma la cantidad descontada.
            ->with('success', "Recurso vinculado exitosamente. Stock descontado: {$request->cantidad}.");
    }

    public function consultarDisponibilidad($usuario) // consultarDisponibilidad(): Consulta los horarios disponibles de un instructor; se usa al mostrar opciones de agenda.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Solo los administradores pueden consultar la agenda de otros instructores.

        $nombresDias = [ // Esta tabla traduce el número de día de la base de datos a un nombre legible.
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        $disponibilidades = DisponibilidadInstructor::where('id_usuario', $usuario) // Filtra las franjas vigentes, las ordena por calendario y get ejecuta la consulta. | map convierte cada modelo a un arreglo simple para la respuesta, usando relaciones/valores formateados.
            ->where('vigente', true)
            ->orderBy('anio', 'desc')
            ->orderBy('trimestre', 'asc')
            ->orderBy('dia_semana', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get()
            ->map(function ($d) use ($nombresDias) {
                return [
                    'id'          => $d->id_disponibilidad_instructor,
                    'anio'        => $d->anio,
                    'trimestre'   => 'T' . $d->trimestre,
                    'dia_semana'  => $d->dia_semana,
                    'dia_nombre'  => $nombresDias[$d->dia_semana] ?? "Día {$d->dia_semana}",
                    'hora_inicio' => substr($d->hora_inicio, 0, 5),
                    'hora_fin'    => substr($d->hora_fin, 0, 5),
                    'habilitada'  => (bool)$d->habilitada,
                ];
            });

        return response()->json([ // Devuelve JSON con el resultado transformado para el consumidor de la interfaz.
            'success'          => true,
            'disponibilidades' => $disponibilidades,
        ]);
    }

    public function guardarDisponibilidad(Request $request) // guardarDisponibilidad(): Valida y guarda la disponibilidad horaria de instructores; se usa al actualizar sus franjas disponibles.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // No procesa el formulario de agenda hasta confirmar el rol administrador.

        $validator = Validator::make($request->all(), [ // all recoge los datos enviados y Validator::make aplica reglas junto a textos de error legibles.
            'id_usuario'  => ['required', 'exists:users,id'],
            'anio'        => ['required', 'integer', 'min:2024', 'max:2035'],
            'trimestre'   => ['required', 'integer', 'min:1', 'max:4'],
            'dia_semana'  => ['required', 'integer', 'min:1', 'max:7'],
            'hora_inicio' => ['required'],
            'hora_fin'    => ['required', 'after:hora_inicio'],
        ], [
            'id_usuario.required' => 'Debes seleccionar un instructor.',
            'hora_inicio.required'=> 'La hora de inicio es requerida.',
            'hora_fin.required'   => 'La hora de fin es requerida.',
            'hora_fin.after'      => 'La hora de fin debe ser posterior a la de inicio.',
        ]);

        if ($validator->fails()) { // fails detecta entradas inválidas; flash permite reabrir el modal y back devuelve errores y datos.
            session()->flash('modal_abrir', 'modalDisponibilidad');
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) { // Una transacción protege la invalidación de franjas antiguas y el alta de la nueva.
            DisponibilidadInstructor::where('id_usuario', $request->id_usuario) // Franjas anteriores para el mismo instructor, año, trimestre y día pasan a vigente=false (conservando historial) | where encadena los cuatro criterios; update modifica en bloque las coincidencias sin borrarlas.
                ->where('anio', $request->anio)
                ->where('trimestre', $request->trimestre)
                ->where('dia_semana', $request->dia_semana)
                ->update(['vigente' => false]);

            $disp = new DisponibilidadInstructor(); // Construye una franja nueva marcada como vigente y habilitada.
            $disp->id_usuario     = $request->id_usuario;
            $disp->anio           = $request->anio;
            $disp->trimestre      = $request->trimestre;
            $disp->dia_semana     = $request->dia_semana;
            $disp->hora_inicio    = $request->hora_inicio;
            $disp->hora_fin       = $request->hora_fin;
            $disp->habilitada     = true;
            $disp->vigente        = true;
            $disp->fecha_registro = Carbon::now();
            $disp->save(); // save inserta la franja y conserva las anteriores como historial.
        });

        return redirect()->route('controlecp.administrador.asesorias.index') // Vuelve al listado y muestra confirmación temporal de la nueva franja.
            ->with('success', 'Franja de disponibilidad guardada. Las franjas anteriores pasaron a historial.');
    }

    public function storeCategoria(Request $request) // storeCategoria(): Valida y crea una categoría de asesoría; se usa al registrar una nueva opción de clasificación.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Impide que perfiles sin permisos administrativos modifiquen el catálogo.

        $validator = Validator::make($request->all(), [ // all entrega los campos enviados; Validator construye las reglas y mensajes personalizados.
            'nombre'         => ['required', 'string', 'max:100', 'unique:categorias_asesoria,nombre'],
            'instructores'   => ['nullable', 'array'],
            'instructores.*' => ['exists:users,id'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        if ($validator->fails()) { // Si hay errores, conserva los datos y solicita reabrir el modal de categorías.
            session()->flash('modal_abrir', 'modalCategorias');
            return back()->withErrors($validator)->withInput();
        }

        $categoria = new CategoriaAsesoria(); // Crea el modelo en memoria, limpia el nombre y asigna estado inicial.
        $categoria->nombre = trim($request->nombre);
        $categoria->estado = 'Activa';
        $categoria->save(); // save inserta la nueva categoría.

        if ($request->has('instructores') && is_array($request->instructores)) { // has verifica si llegó el campo y is_array confirma que contenga una lista antes de sincronizar la relación.
            $categoria->instructores()->sync($request->instructores); // sync mantiene la relación muchos-a-muchos exactamente con los ids recibidos.
        }

        return redirect()->route('controlecp.administrador.asesorias.index') // Vuelve a la pantalla administrativa con un aviso temporal.
            ->with('success', "Categoría '{$categoria->nombre}' creada exitosamente.");
    }

    public function updateCategoria(Request $request, $id) // updateCategoria(): Valida y actualiza una categoría existente; se usa al guardar su edición.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Requiere el rol administrador para cambiar categorías.

        $categoria = CategoriaAsesoria::findOrFail($id); // Busca la categoría o detiene la petición con 404.

        $validator = Validator::make($request->all(), [ // Valida campos y excluye el id actual de la regla unique para permitir que conserve su mismo nombre.
            'nombre'         => ['required', 'string', 'max:100', 'unique:categorias_asesoria,nombre,'.$id.',id_categoria_asesoria'],
            'instructores'   => ['nullable', 'array'],
            'instructores.*' => ['exists:users,id'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        if ($validator->fails()) { // Si la validación falla, preserva la entrada y devuelve al modal con los errores.
            session()->flash('modal_abrir', 'modalCategorias');
            return back()->withErrors($validator)->withInput();
        }

        $categoria->nombre = trim($request->nombre); // Limpia el nuevo nombre y save persiste el cambio.
        $categoria->save();

        if ($request->has('instructores') && is_array($request->instructores)) { // Si hay una lista explícita de instructores, sync reemplaza las relaciones por esa lista.
            $categoria->instructores()->sync($request->instructores);
        } else {
            $categoria->instructores()->sync([]); // Sin lista recibida se retiran todos los vínculos existentes.
        }

        return redirect()->route('controlecp.administrador.asesorias.index') // Regresa al panel para mostrar la confirmación de actualización.
            ->with('success', "Categoría '{$categoria->nombre}' actualizada exitosamente.");
    }

    public function toggleCategoriaEstado($id) // toggleCategoriaEstado(): Alterna el estado activo de una categoría; se usa al habilitarla o deshabilitarla desde el catálogo.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Solo una cuenta administradora puede activar o desactivar categorías.

        $categoria = CategoriaAsesoria::findOrFail($id); // Recupera la categoría o responde 404 si el id no existe.
        $categoria->estado = ($categoria->estado === 'Activa') ? 'Inactiva' : 'Activa'; // El operador ternario invierte el estado entre Activa e Inactiva.
        $categoria->save(); // Guarda el valor alternado en la base de datos.

        return redirect()->route('controlecp.administrador.asesorias.index') // Vuelve al catálogo y muestra cuál estado quedó aplicado.
            ->with('success', "Estado de la categoría cambiado a '{$categoria->estado}'.");
    }

    public function reporte(Request $request) // reporte(): Genera los datos de asesorías según filtros; se usa al consultar o exportar el reporte administrativo.
    {
        if ($res = $this->verificarAccesoAdmin()) return $res; // Rechaza perfiles no administrativos antes de preparar o exportar datos.

        $query = Asesoria::with([ // with carga las relaciones que se imprimirán junto con cada asesoría.
            'categoria',
            'aprendiz.person',
            'instructor.person',
            'usuarioRegistro.person',
            'usuarioCancelador.person',
        ]);

        if ($request->filled('categoria_id')) { // Cada bloque filled aplica su filtro solo cuando la petición contiene un valor no vacío.
            $query->where('id_categoria_asesoria', $request->categoria_id); // where agrega una igualdad a la consulta que se ejecutará al final.
        }
        if ($request->filled('instructor_id')) {
            if ($request->instructor_id === 'sin_asignar') {
                $query->whereNull('id_usuario_instructor'); // whereNull selecciona asesorías sin instructor.
            } else {
                $query->where('id_usuario_instructor', $request->instructor_id); // En los demás casos se filtra por el id del instructor solicitado.
            }
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado); // Añade la condición del estado solicitado.
        }
        if ($request->filled('trimestre')) {
            $t = (int)$request->trimestre; // Convierte el trimestre a entero para usarlo como selector de los límites de mes.
            $meses = match($t) { // match elige el rango de meses; el caso default cubre todo el año si el valor no coincide.
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
                default => [1, 12],
            };
            $query->whereMonth('fecha', '>=', $meses[0])->whereMonth('fecha', '<=', $meses[1]); // whereMonth agrega los límites inclusivos de inicio y fin para ese trimestre.
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde); // whereDate compara la fecha calendario con el límite inicial.
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta); // Aplica el límite final inclusivo.
        }

        $asesorias = $query->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'desc')->get(); // orderBy define el orden de salida y get ejecuta la consulta, cargando todas las filas que cumplen los filtros.

        if ($request->get('formato') === 'csv') { // 1. Exportación a CSV compatible con Excel | get recupera formato de la petición para seleccionar la representación de salida.
            $filename = 'reporte_asesorias_' . date('Ymd_His') . '.csv'; // Genera un nombre único con fecha y hora para el archivo descargable.

            $headers = [ // Estos encabezados indican al navegador el tipo, nombre y política de caché del archivo.
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $callback = function () use ($asesorias) { // La respuesta ejecuta este callback al transmitir el archivo en lugar de guardarlo en el servidor.
                $file = fopen('php://output', 'w'); // fopen abre el flujo de salida HTTP en modo escritura.
                fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8 para visualización correcta de caracteres especiales en Excel | fputs escribe la marca de codificación al inicio del CSV.

                fputcsv($file, [ // Encabezados con separador punto y coma (estándar Excel en español) | fputcsv escribe una fila escapando separadores y comillas según el formato CSV.
                    'ID Asesoria',
                    'Aprendiz',
                    'Documento Aprendiz',
                    'Correo Aprendiz',
                    'Categoria Tematica',
                    'Instructor',
                    'Fecha',
                    'Hora Inicio',
                    'Hora Fin',
                    'Estado',
                    'Motivo',
                    'Fecha Solicitud',
                    'Cancelado Por',
                    'Motivo Cancelacion',
                ], ';');

                foreach ($asesorias as $a) { // foreach recorre cada asesoría y escribe una fila con sus datos y valores relacionados.
                    fputcsv($file, [
                        $a->id_asesoria,
                        $a->aprendiz ? $a->aprendiz->full_name : 'No registrado',
                        $a->aprendiz && $a->aprendiz->person ? $a->aprendiz->person->document_number : '',
                        $a->aprendiz ? $a->aprendiz->email : '',
                        $a->categoria ? $a->categoria->nombre : 'Sin categoria',
                        $a->instructor ? $a->instructor->full_name : 'Sin asignar',
                        $a->fecha ? $a->fecha->format('d/m/Y') : '',
                        substr($a->hora_inicio, 0, 5),
                        substr($a->hora_fin, 0, 5),
                        $a->estado,
                        $a->motivo,
                        $a->fecha_solicitud ? $a->fecha_solicitud->format('d/m/Y H:i') : '',
                        $a->usuarioCancelador ? $a->usuarioCancelador->full_name : '',
                        $a->motivo_cancelacion ?? '',
                    ], ';');
                }

                fclose($file); // Cierra el flujo al terminar de escribir todas las filas.
            };

            return response()->stream($callback, 200, $headers); // stream envía el contenido generado por el callback con código HTTP y encabezados de descarga.
        }

        if ($request->get('formato') === 'pdf' || $request->get('formato') === 'print') { // 2. Vista de impresión limpia / PDF con window.print() | get lee el formato y la condición acepta tanto la exportación PDF imprimible como la vista print.
            $filasHtml = ''; // Acumula el HTML que se insertará como cuerpo de la tabla.
            foreach ($asesorias as $a) { // El foreach crea una fila por asesoría; e escapa texto dinámico para evitar que se interprete como HTML.
                $aprendizNom = e($a->aprendiz ? $a->aprendiz->full_name : 'No registrado');
                $catNom = e($a->categoria ? $a->categoria->nombre : 'Sin categoría');
                $instNom = e($a->instructor ? $a->instructor->full_name : 'Sin asignar');
                $fechaStr = $a->fecha ? $a->fecha->format('d/m/Y') : '-';
                $horasStr = substr($a->hora_inicio, 0, 5) . ' - ' . substr($a->hora_fin, 0, 5);
                $motivoStr = e($a->motivo);

                $filasHtml .= "<tr> // Concatena la fila ya preparada a la cadena de salida.
                    <td style='border:1px solid #cbd5e1; padding:6px; font-weight:bold;'>#{$a->id_asesoria}</td>
                    <td style='border:1px solid #cbd5e1; padding:6px;'>{$aprendizNom}</td>
                    <td style='border:1px solid #cbd5e1; padding:6px;'>{$catNom}</td>
                    <td style='border:1px solid #cbd5e1; padding:6px;'>{$instNom}</td>
                    <td style='border:1px solid #cbd5e1; padding:6px; white-space:nowrap;'>{$fechaStr} ({$horasStr})</td>
                    <td style='border:1px solid #cbd5e1; padding:6px; text-align:center;'><strong>{$a->estado}</strong></td>
                    <td style='border:1px solid #cbd5e1; padding:6px;'>{$motivoStr}</td>
                </tr>";
            }

            $html = "<!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <title>Reporte de Asesorías - ECP</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; color: #001A29; font-size: 12px; }
                    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #39A900; padding-bottom: 10px; margin-bottom: 15px; }
                    h2 { color: #001A29; margin: 0; font-size: 18px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th { background-color: #001A29; color: #ffffff; border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
                    @media print { .no-print { display: none; } body { margin: 0; } }
                </style>
            </head>
            <body onload='window.print()'>
                <div class='no-print' style='margin-bottom: 15px;'>
                    <button onclick='window.print()' style='background:#39A900; color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:bold;'>Imprimir / Guardar como PDF</button>
                    <button onclick='window.close()' style='background:#64748b; color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; margin-left:8px;'>Cerrar</button>
                </div>
                <div class='header'>
                    <div>
                        <h2>Escuela Cultura de Paz - Reporte de Asesorías</h2>
                        <small>Generado el: " . date('d/m/Y H:i') . " | Total asesorías listadas: " . $asesorias->count() . "</small>
                    </div>
                    <div style='color: #39A900; font-weight: bold; font-size: 16px;'>Control ECP</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Aprendiz</th>
                            <th>Categoría</th>
                            <th>Instructor</th>
                            <th>Fecha y Horario</th>
                            <th>Estado</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$filasHtml}
                    </tbody>
                </table>
            </body>
            </html>";

            return response($html); // Devuelve el documento generado como respuesta HTTP para que el navegador lo imprima o guarde como PDF.
        }

        return redirect()->route('controlecp.administrador.asesorias.index'); // Si no se solicita exportación, vuelve al listado principal de asesorías.
    }
}
