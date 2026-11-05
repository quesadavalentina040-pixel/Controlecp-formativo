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

class AsesoriaController extends Controller // Controlador administrativo para solicitudes, programaciÃ³n y seguimiento de asesorÃ­as.
{
    private function verificarAccesoAdmin() // Verifica acceso del rol Administrador | verificarAccesoAdmin(): Valida el rol administrador antes de permitir operaciones administrativas; se usa al entrar a cada acciÃ³n protegida.
    {
        $usuario = Auth::user(); // Auth::user devuelve el usuario de la sesiÃ³n actual o null cuando no hay sesiÃ³n.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // hasRole comprueba el rol y, si falta, se devuelve una redirecciÃ³n; null significa que puede continuar.
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al mÃ³dulo de AsesorÃ­as.');
        }
        return /* Indica que no hay informaciï¿½n para devolver. */ null;
    }

    public function index(Request $request) // Listado principal con indicadores, filtros, tabla y grÃ¡ficas | index(): Prepara el panel con asesorÃ­as, indicadores, filtros y relaciones; se usa al abrir el listado administrativo.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Guarda la respuesta del control de acceso para detener la acciÃ³n solo si fue denegada.

        $indicadores = [ // Indicadores reales desde la base de datos | Cada where agrega una condiciÃ³n SQL y count ejecuta un conteo sin traer todas las filas.
            'solicitadas' => Asesoria::where('estado', 'Solicitada')->count(),
            'programadas' => Asesoria::where('estado', 'Programada')->count(),
            'realizadas'  => Asesoria::where('estado', 'Realizada')->count(),
            'sin_asignar' => Asesoria::whereNull('id_usuario_instructor')->where('estado', '!=', 'Cancelada')->count(),
            'canceladas'  => Asesoria::where('estado', 'Cancelada')->count(),
        ];

        $query = Asesoria::with([ // ConstrucciÃ³n de consulta con filtros | with carga las relaciones de antemano para evitar una consulta adicional por cada asesorÃ­a.
            'categoria',
            'aprendiz.person',
            'instructor.person',
            'usuarioRegistro.person',
            'usuarioCancelador.person',
            'historial',
            'seguimientos',
            'recursos.inventario.element',
        ]);

        if ($request->filled('categoria_id')) { // Filtro por categorÃ­a | filled solo activa el filtro si categorÃ­a_id existe y tiene un valor no vacÃ­o.
            $query->where('id_categoria_asesoria', $request->categoria_id); // where agrega a la consulta la igualdad entre la columna y el filtro recibido.
        }

        if ($request->filled('instructor_id')) { // Filtro por instructor o sin asignar | Se comprueba que haya un filtro; la cadena especial representa filas sin instructor asignado.
            if ($request->instructor_id === 'sin_asignar') {
                $query->whereNull('id_usuario_instructor'); // whereNull filtra filas cuyo instructor estÃ¡ guardado como NULL.
            } else {
                $query->where('id_usuario_instructor', $request->instructor_id); // En otro caso, where busca coincidencia exacta con el id seleccionado.
            }
        }

        if ($request->filled('estado')) { // Filtro por estado | Solo incorpora la condiciÃ³n de estado cuando el formulario enviÃ³ un valor.
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) { // Filtro por rango de fechas | whereDate compara Ãºnicamente la parte de fecha; >= incluye el lÃ­mite inicial.
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) { // El lÃ­mite final tambiÃ©n es inclusivo, pues se compara con <=.
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('aprendiz')) { // Filtro buscador por nombre, apellido, correo o documento del aprendiz | filled evita aplicar una bÃºsqueda vacÃ­a; trim quita espacios sobrantes al principio y al final.
            $buscar = trim($request->aprendiz);
            $query->whereHas('aprendiz', function ($q) use ($buscar) { // whereHas limita asesorÃ­as segÃºn columnas de su aprendiz relacionado.
                $q->where('nickname', 'like', "%{$buscar}%") // where inicia el grupo; orWhere permite que cualquiera de los campos coincida con el texto parcial.
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhereHas('person', function ($qp) use ($buscar) { // orWhereHas incluye coincidencias en la relaciÃ³n persona asociada al aprendiz.
                      $qp->where('first_name', 'like', "%{$buscar}%")
                         ->orWhere('first_last_name', 'like', "%{$buscar}%")
                         ->orWhere('second_last_name', 'like', "%{$buscar}%")
                         ->orWhere('document_number', 'like', "%{$buscar}%");
                  });
            });
        }

        $asesorias = $query->orderBy('fecha', 'desc') // orderBy ordena primero por fecha y luego por hora, paginate consulta una pÃ¡gina y withQueryString conserva filtros en los enlaces.
            ->orderBy('hora_inicio', 'desc')
            ->paginate(6)
            ->withQueryString();

        $graficas = $this->obtenerDatosGraficas(); // Datos para grÃ¡ficas (dÃ­a, semana, trimestre) | El mÃ©todo auxiliar realiza los conteos agrupados que consumen las grÃ¡ficas.

        $categorias = CategoriaAsesoria::withCount('instructores')->orderBy('nombre')->get(); // Listados para selectores y modales | withCount agrega el nÃºmero de instructores relacionados; orderBy ordena y get ejecuta la consulta.

        $instructores = User::whereHas('roles', function ($q) { // whereHas limita usuarios por su relaciÃ³n de roles; el callback agrega la condiciÃ³n sobre slug. | with carga la persona asociada y get obtiene la colecciÃ³n de instructores.
            $q->where('slug', 'controlecp.instructor');
        })->with('person')->get();

        $aprendices = User::whereHas('roles', function ($q) { // Construye del mismo modo la lista de usuarios que tienen el rol de aprendiz.
            $q->where('slug', 'controlecp.apprentice');
        })->with('person')->get();

        $inventarios = Inventory::with('element')->orderBy('id')->get(); // Carga el elemento relacionado, ordena por id y get obtiene los registros para el selector.

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.asesoria', compact( // EnvÃ­a el conjunto de datos ya preparado a la vista administrativa.
            'indicadores',
            'asesorias',
            'graficas',
            'categorias',
            'instructores',
            'aprendices',
            'inventarios'
        ));
    }

    public function show($id) // Retorna datos de una asesorÃ­a para el modal de detalle | show(): Carga y serializa una asesorÃ­a con sus relaciones; se usa al solicitar el detalle para el modal.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Evita consultar detalles si el usuario no pasÃ³ la comprobaciÃ³n administrativa.

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

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([ // json serializa el arreglo como respuesta HTTP para que la interfaz lo consuma.
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
                'categoria'          => $asesoria->categoria ? $asesoria->categoria->nombre : 'Sin categorÃ­a',
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
                'historial'          => $asesoria->historial->map(function ($h) { // map recorre la colecciÃ³n relacionada y transforma cada fila a los campos que necesita el modal.
                    return /* Devuelve el resultado de este mï¿½todo. */ [
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
                    return /* Devuelve el resultado de este mï¿½todo. */ [
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
                'recursos'           => $asesoria->recursos->map(function ($r) { // Recorre recursos y usa la relaciÃ³n inventario.element para mostrar el nombre disponible.
                    return /* Devuelve el resultado de este mï¿½todo. */ [
                        'id_recurso' => $r->id_asesoria_recurso,
                        'nombre'     => $r->inventario && $r->inventario->element ? $r->inventario->element->name : 'Recurso #'.$r->id_inventario,
                        'cantidad'   => $r->cantidad,
                    ];
                }),
            ],
        ]);
    }

    private function obtenerDatosGraficas() // Calcula datos para las grÃ¡ficas simples (dÃ­a, semana, trimestre) | obtenerDatosGraficas(): Calcula las series resumidas de asesorÃ­as; se usa para alimentar las grÃ¡ficas del panel.
    {
        $hoy = Carbon::today(); // today crea una fecha de hoy sin hora; year extrae el aÃ±o que se usarÃ¡ en los conteos trimestrales.
        $anioActual = $hoy->year;

        $diasLabels = []; // 1. Por dÃ­a (Ãºltimos 7 dÃ­as)
        $diasValores = [];
        for ($i = 6; $i >= 0; $i--) { // El for itera del sexto dÃ­a atrÃ¡s hasta hoy para formar una serie de siete dÃ­as.
            $f = $hoy->copy()->subDays($i);
            $diasLabels[] = $f->translatedFormat('D d');
            $diasValores[] = Asesoria::whereDate('fecha', $f->format('Y-m-d'))->count(); // whereDate filtra por dÃ­a calendario y count devuelve el total de asesorÃ­as para esa fecha.
        }

        $inicioSemana = $hoy->copy()->startOfWeek(); // 2. Por semana (dÃ­as Lunes a SÃ¡bado de la semana actual)
        $semanaLabels = ['Lun', 'Mar', 'MiÃ©', 'Jue', 'Vie', 'SÃ¡b'];
        $semanaValores = [];
        for ($d = 0; $d < 6; $d++) { // Repite el conteo para lunes a sÃ¡bado, calculando la fecha de cada dÃ­a desde el inicio de semana.
            $diaFecha = $inicioSemana->copy()->addDays($d);
            $semanaValores[] = Asesoria::whereDate('fecha', $diaFecha->format('Y-m-d'))->count();
        }

        $trimestres = [ // 3. Por trimestre del aÃ±o actual (T1 a T4) | Cada cadena whereYear/whereMonth agrega lÃ­mites de aÃ±o y meses antes de ejecutar count.
            'T1 (Ene-Mar)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 1)->whereMonth('fecha', '<=', 3)->count(),
            'T2 (Abr-Jun)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 4)->whereMonth('fecha', '<=', 6)->count(),
            'T3 (Jul-Sep)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 7)->whereMonth('fecha', '<=', 9)->count(),
            'T4 (Oct-Dic)' => Asesoria::whereYear('fecha', $anioActual)->whereMonth('fecha', '>=', 10)->whereMonth('fecha', '<=', 12)->count(),
        ];

        return /* Devuelve el resultado de este mï¿½todo. */ [ // Devuelve etiquetas y series con la forma que espera el componente de grÃ¡ficas.
            'dias'       => ['labels' => $diasLabels, 'valores' => $diasValores],
            'semana'     => ['labels' => $semanaLabels, 'valores' => $semanaValores],
            'trimestres' => $trimestres,
        ];
    }

    public function instructoresDisponibles(Request $request) // Busca instructores habilitados por categorÃ­a, disponibilidad y sin cruce de horarios (AJAX) | instructoresDisponibles(): Busca instructores elegibles segÃºn categorÃ­a y disponibilidad; se usa al asignar o programar una asesorÃ­a.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Un rechazo de acceso se retorna antes de leer parÃ¡metros o consultar instructores.

        $categoriaId = $request->get('categoria_id'); // get lee los valores recibidos (incluidos parÃ¡metros de consulta) sin aplicar reglas de validaciÃ³n.
        $fechaInput  = $request->get('fecha');
        $horaInicio  = $request->get('hora_inicio');
        $horaFin     = $request->get('hora_fin');
        $asesoriaId  = $request->get('asesoria_id');

        if (!$categoriaId || !$fechaInput || !$horaInicio || !$horaFin) { // La condiciÃ³n comprueba que todos los datos mÃ­nimos tengan valor antes de procesar fechas.
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([ // json devuelve los datos y el mensaje en un formato consumible por la llamada AJAX.
                'success'      => false,
                'mensaje'      => 'Debes proporcionar categorÃ­a, fecha y rango de horas.',
                'instructores' => [],
            ]);
        }

        $fecha = Carbon::parse($fechaInput); // parse convierte el texto de fecha a Carbon; se extraen aÃ±o, trimestre y dÃ­a ISO para cruzarlos con la agenda.
        $anio = $fecha->year;
        $trimestre = ceil($fecha->month / 3);
        $diaSemana = $fecha->dayOfWeekIso; // 1 Lunes a 7 Domingo
        $hInicio = substr($horaInicio, 0, 5);
        $hFin = substr($horaFin, 0, 5);

        $instructoresIds = InstructorCategoria::where('id_categoria_asesoria', $categoriaId) // where filtra la tabla intermedia por categorÃ­a y pluck obtiene solo los ids vinculados.
            ->pluck('id_usuario');

        if ($instructoresIds->isEmpty()) { // Si la categorÃ­a aÃºn no tiene instructores vinculados, permitimos a los instructores del sistema | isEmpty detecta colecciÃ³n vacÃ­a; whereHas filtra usuarios por rol y pluck trae Ãºnicamente sus ids.
            $instructoresIds = User::whereHas('roles', function ($q) {
                $q->where('slug', 'controlecp.instructor');
            })->pluck('id');
        }

        $conFranjaIds = DisponibilidadInstructor::whereIn('id_usuario', $instructoresIds) // 2. Instructores que tienen franja de disponibilidad habilitada y vigente para este horario | whereIn limita a los ids candidatos; las demÃ¡s condiciones cotejan vigencia y que la franja cubra todo el horario.
            ->where('anio', $anio)
            ->where('trimestre', $trimestre)
            ->where('dia_semana', $diaSemana)
            ->where('habilitada', true)
            ->where('vigente', true)
            ->where('hora_inicio', '<=', $hInicio)
            ->where('hora_fin', '>=', $hFin)
            ->pluck('id_usuario');

        $ocupadosIds = Asesoria::whereIn('id_usuario_instructor', $instructoresIds) // 3. Filtrar instructores que tengan un cruce real de horario con otra asesorÃ­a activa | when aÃ±ade una condiciÃ³n solo si ya se estÃ¡ editando una asesorÃ­a; el grupo de where verifica solapamiento.
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

        $candidatosSinCruce = $instructoresIds->diff($ocupadosIds); // Instructores libres sin cruce de horario | diff resta de la colecciÃ³n de candidatos los ids que aparecen ocupados.

        $instructores = User::whereIn('id', $candidatosSinCruce)->with('person')->get()->map(function ($u) use ($conFranjaIds) { // Se construye el listado dando prioridad a los instructores con franja habilitada | whereIn consulta los candidatos, with precarga la persona, get ejecuta la bÃºsqueda y map adapta cada usuario.
            $tieneFranja = $conFranjaIds->contains($u->id); // contains comprueba si el id estÃ¡ entre quienes tienen una franja coincidente.
            return /* Devuelve el resultado de este mï¿½todo. */ [
                'id'              => $u->id,
                'nombre'          => $u->full_name . ($tieneFranja ? ' [Franja disponible]' : ''),
                'email'           => $u->email,
                'franja_coincide' => $tieneFranja,
            ];
        })->sortByDesc('franja_coincide')->values();

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([ // Responde a AJAX con la lista final y el nÃºmero de opciones disponibles.
            'success'      => true,
            'dia_semana'   => $diaSemana,
            'trimestre'    => $trimestre,
            'instructores' => $instructores,
            'total'        => $instructores->count(),
        ]);
    }

    public function store(StoreAsesoriaRequest $request) // Registra una nueva solicitud de asesorÃ­a | store(): Valida y registra una nueva asesorÃ­a; se usa al enviar el formulario de creaciÃ³n.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // StoreAsesoriaRequest valida los datos antes de entrar aquÃ­; este control restringe ademÃ¡s el rol administrador.

        $idInstructor = $request->filled('id_usuario_instructor') ? $request->id_usuario_instructor : null; // filled trata cadenas vacÃ­as como no informadas; en ese caso se guarda null como instructor pendiente.
        $estado = 'Solicitada';

        if ($idInstructor) { // Si se seleccionÃ³ un instructor, validar cruces antes de guardar
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
                return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors([
                    'id_usuario_instructor' => 'El instructor seleccionado tiene un cruce de horario con otra asesorÃ­a.'
                ])->with('error', 'Existe un cruce con otra asesorÃ­a o sesiÃ³n en ese horario.');
            }

            $estado = 'Programada';
        }

        $asesoria = new Asesoria(); // new crea un modelo aÃºn no persistido; estas asignaciones mapean datos validados a sus columnas.
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
        $asesoria->save(); // save inserta la asesorÃ­a y asigna su clave generada al modelo.

        if ($idInstructor) { // Si hubo instructor se envÃ­an dos avisos; redirect y with devuelven al listado con mensaje temporal.
            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesorÃ­a ha sido programada.');
            $this->notificarUsuario($asesoria->instructor, 'Se te ha asignado una nueva asesorÃ­a.');
            return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index')
                ->with('success', 'Solicitud registrada y programada exitosamente con el instructor asignado.');
        }

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Sin instructor se confirma que la solicitud quedÃ³ pendiente en el mismo listado administrativo.
            ->with('success', 'Solicitud registrada como pendiente (sin asignar).');
    }

    public function update(Request $request, $id) // Actualiza los datos de una asesorÃ­a existente | update(): Actualiza los datos permitidos de una asesorÃ­a existente; se usa al guardar cambios administrativos.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Comprueba autorizaciÃ³n y recupera la asesorÃ­a; findOrFail responde 404 para un id desconocido.

        $asesoria = Asesoria::findOrFail($id);

        $validator = Validator::make($request->all(), [ // all reÃºne entradas para Validator; make define reglas y mensajes personalizados sin guardarlos todavÃ­a.
            'id_usuario_aprendiz'   => 'required|exists:users,id',
            'id_categoria_asesoria' => 'required|exists:categorias_asesoria,id_categoria_asesoria',
            'fecha'                 => 'required|date',
            'hora_inicio'           => 'required|date_format:H:i',
            'hora_fin'              => 'required|date_format:H:i|after:hora_inicio',
            'id_usuario_instructor' => 'nullable|exists:users,id',
            'motivo'                => 'required|string|min:5',
        ], [
            'id_usuario_aprendiz.required'   => 'El aprendiz es obligatorio.',
            'id_categoria_asesoria.required' => 'La categorÃ­a temÃ¡tica es obligatoria.',
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
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors($validator)->with('error', 'Corrige los errores del formulario de ediciÃ³n.');
        }

        $idInstructor = $request->filled('id_usuario_instructor') ? $request->id_usuario_instructor : null; // filled distingue un instructor elegido de un campo vacÃ­o y normaliza este Ãºltimo a null.

        if ($idInstructor) { // Si se asignÃ³ un instructor, validar que no tenga cruce de horario con otra asesorÃ­a activa
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
                session()->flash('modal_abrir', 'modalEditarAsesoria'); // Mantiene abierto el modal de ediciÃ³n y devuelve el mensaje asociado al campo conflictivo.
                session()->flash('modal_id', $id);
                return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors([
                    'id_usuario_instructor' => 'El instructor seleccionado tiene un cruce de horario con otra asesorÃ­a.'
                ])->with('error', 'Existe un cruce con otra asesorÃ­a o sesiÃ³n en ese horario.');
            }
        }

        $asesoria->id_categoria_asesoria = $request->id_categoria_asesoria; // Copia los campos aceptados al modelo que ya fue consultado; no cambia aquÃ­ la identidad del registro.
        $asesoria->id_usuario_aprendiz   = $request->id_usuario_aprendiz;
        $asesoria->id_usuario_instructor = $idInstructor;
        $asesoria->motivo                = $request->motivo;
        $asesoria->fecha                 = $request->fecha;
        $asesoria->hora_inicio           = $request->hora_inicio;
        $asesoria->hora_fin              = $request->hora_fin;

        if ($asesoria->estado === 'Solicitada' && $idInstructor) { // Si estaba en Solicitada y ahora tiene instructor, pasa a Programada
            $asesoria->estado = 'Programada'; // Al asignar un instructor, la solicitud deja de estar pendiente y pasa a programaciÃ³n.
        } elseif ($asesoria->estado === 'Programada' && !$idInstructor) {
            $asesoria->estado = 'Solicitada'; // Si se retira el instructor, vuelve al estado pendiente de asignaciÃ³n.
        }

        $asesoria->save(); // save actualiza en la base de datos los atributos modificados del modelo.

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Redirige al listado y agrega un mensaje de Ã©xito de un solo uso.
            ->with('success', 'AsesorÃ­a #' . $id . ' actualizada exitosamente.');
    }

    public function programar(ProgramarAsesoriaRequest $request, $id) // Asigna y programa una asesorÃ­a validando cruces de horario | programar(): Asigna fecha, horario e instructor a una asesorÃ­a; se usa al confirmar su programaciÃ³n.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // El request dedicado ya validÃ³ campos; el helper comprueba adicionalmente que sea administrador.

        $asesoria = Asesoria::findOrFail($id); // Busca la asesorÃ­a objetivo o genera 404 antes de actualizarla.

        $hInicio = substr($request->hora_inicio, 0, 5); // Normaliza ambas horas al formato HH:MM que se emplea en el cruce de intervalos.
        $hFin = substr($request->hora_fin, 0, 5);

        $hayCruce = Asesoria::where('id_usuario_instructor', $request->id_usuario_instructor) // ValidaciÃ³n de cruce con otra asesorÃ­a activa del mismo instructor | La consulta excluye el registro actual y las canceladas; exists solo necesita saber si hay coincidencia.
            ->whereDate('fecha', $request->fecha)
            ->where('id_asesoria', '!=', $id)
            ->where('estado', '!=', 'Cancelada')
            ->where(function ($q) use ($hInicio, $hFin) {
                $q->where('hora_inicio', '<', $hFin)
                  ->where('hora_fin', '>', $hInicio);
            })
            ->exists();

        if ($hayCruce) {
            session()->flash('modal_abrir', 'modalProgramar'); // Guarda quÃ© modal reabrir y devuelve el formulario con el error de horario.
            session()->flash('modal_programar_id', $id);
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors([
                'id_usuario_instructor' => 'Existe un cruce con otra asesorÃ­a o sesiÃ³n para este instructor en ese horario.'
            ])->with('error', 'Existe un cruce con otra asesorÃ­a o sesiÃ³n en ese horario.');
        }

        DB::transaction(function () use ($asesoria, $request) { // transaction agrupa cambios y notificaciones: si una operaciÃ³n lanza error, la base de datos revierte el bloque.
            $asesoria->id_usuario_instructor = $request->id_usuario_instructor; // Asigna instructor, fecha y horas recibidas, cambia estado y persiste el modelo.
            $asesoria->fecha                 = $request->fecha;
            $asesoria->hora_inicio           = $request->hora_inicio;
            $asesoria->hora_fin              = $request->hora_fin;
            $asesoria->estado                = 'Programada';
            $asesoria->save();

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesorÃ­a ha sido programada.'); // Notifica a las dos personas relacionadas despuÃ©s de guardar la programaciÃ³n.
            $this->notificarUsuario($asesoria->instructor, 'Se te ha asignado una asesorÃ­a para acompaÃ±amiento.');
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Devuelve al listado con una confirmaciÃ³n temporal de la asignaciÃ³n.
            ->with('success', 'AsesorÃ­a asignada y programada exitosamente.');
    }

    private function notificarUsuario($usuario, $mensaje, $asesoria = null, $tipoAccion = 'actualizacion') // Registra notificaciÃ³n en la base de datos para el usuario destinatario | notificarUsuario(): EnvÃ­a al usuario la notificaciÃ³n asociada a un cambio; se usa despuÃ©s de acciones sobre la asesorÃ­a.
    {
        if (!$usuario) return /* Devuelve el resultado de este método. */; // Evita intentar notificar una relaciÃ³n opcional que no tiene usuario asociado.

        try {
            $servicio = app(\Modules\ControlECP\Services\NotificacionService::class); // app resuelve el servicio desde el contenedor de Laravel para crear la notificaciÃ³n.
            $claveEvento = $asesoria // El identificador de evento diferencia cada aviso; se deriva de la asesorÃ­a o del usuario.
                ? "asesoria_{$asesoria->id_asesoria}_{$tipoAccion}_" . time()
                : "notif_user_{$usuario->id}_" . uniqid();

            $servicio->crear([ // crear recibe los datos para persistir destinatario, causante, mensaje y referencia al evento.
                'id_usuario_receptor' => $usuario->id,
                'id_usuario_causante' => Auth::id(),
                'modulo'              => 'AsesorÃ­as',
                'titulo'              => 'Novedad en AsesorÃ­a ECP',
                'mensaje'             => $mensaje,
                'referencia_tipo'     => 'asesoria',
                'referencia_id'       => $asesoria ? $asesoria->id_asesoria : null,
                'clave_evento'        => $claveEvento,
                'clave_agrupacion'    => $asesoria ? "asesoria_{$asesoria->id_asesoria}" : null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo registrar notificaciÃ³n: " . $e->getMessage()); // Registra el fallo en el log sin detener la operaciÃ³n principal de asesorÃ­a.
        }
    }

    public function reprogramar(ReprogramarAsesoriaRequest $request, $id) // reprogramar(): Valida y guarda una nueva programaciÃ³n; se usa cuando se modifica la fecha o el horario asignado.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Valida rol y datos mediante el helper y el request especializado.

        $asesoria = Asesoria::findOrFail($id); // Recupera la asesorÃ­a o retorna un 404 si no existe.

        if ($asesoria->estado === 'Cancelada' || $asesoria->estado === 'Realizada') { // No permite modificar una asesorÃ­a que ya terminÃ³ o fue cancelada.
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->with('error', 'No se puede reprogramar una asesorÃ­a cancelada o realizada.');
        }

        if ($asesoria->id_usuario_instructor) { // Si tiene instructor asignado, validar cruces en la nueva fecha y horario
            $hInicio = substr($request->hora_inicio, 0, 5); // Normaliza las horas recibidas para comparar correctamente el intervalo nuevo.
            $hFin = substr($request->hora_fin, 0, 5);

            $hayCruce = Asesoria::where('id_usuario_instructor', $asesoria->id_usuario_instructor) // Busca otra asesorÃ­a activa del mismo instructor en la fecha nueva y con horas superpuestas.
                ->whereDate('fecha', $request->fecha)
                ->where('id_asesoria', '!=', $id)
                ->where('estado', '!=', 'Cancelada')
                ->where(function ($q) use ($hInicio, $hFin) {
                    $q->where('hora_inicio', '<', $hFin)
                      ->where('hora_fin', '>', $hInicio);
                })
                ->exists();

            if ($hayCruce) {
                session()->flash('modal_abrir', 'modalReprogramar'); // Mantiene el formulario con los datos ingresados y aÃ±ade el error sobre la hora de inicio.
                session()->flash('modal_id', $id);
                return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors([
                    'hora_inicio' => 'El instructor tiene un cruce de horario con otra asesorÃ­a en esa fecha y horario.'
                ])->with('error', 'Existe un cruce de horario con otra sesiÃ³n del instructor.');
            }
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacciÃ³n hace atÃ³micos el registro histÃ³rico, el cambio de fecha/hora y los avisos.
            $hist = new AsesoriaHistorial(); // Guardar trazabilidad en asesoria_historial sin perder el historial anterior | Crea una nueva fila de historial con usuario, motivo y valores de antes/despuÃ©s.
            $hist->id_asesoria    = $id;
            $hist->id_usuario     = Auth::id();
            $hist->accion         = 'Reprogramo';
            $hist->motivo         = $request->motivo;
            $hist->fecha_anterior = $asesoria->fecha;
            $hist->hora_anterior  = $asesoria->hora_inicio;
            $hist->fecha_nueva    = $request->fecha;
            $hist->hora_nueva     = $request->hora_inicio;
            $hist->fecha          = Carbon::now();
            $hist->save(); // save conserva el evento histÃ³rico como registro independiente.

            $asesoria->fecha       = $request->fecha; // Actualizar asesorÃ­a
            $asesoria->hora_inicio = $request->hora_inicio;
            $asesoria->hora_fin    = $request->hora_fin;
            $asesoria->save(); // Persiste los datos nuevos en la asesorÃ­a original.

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesorÃ­a ha sido reprogramada.'); // Avisa al aprendiz e instructor; el segundo aviso solo se envÃ­a si hay instructor relacionado.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'Una de tus asesorÃ­as ha sido reprogramada.');
            }
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Al terminar la transacciÃ³n, regresa al panel indicando que el cambio y su historial se guardaron.
            ->with('success', 'AsesorÃ­a reprogramada exitosamente. Se guardÃ³ el historial del cambio.');
    }

    public function cancelar(CancelarAsesoriaRequest $request, $id) // cancelar(): Registra la cancelaciÃ³n y su motivo; se usa al cancelar una asesorÃ­a.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // El request ya valida el motivo y el helper restringe la acciÃ³n al administrador.

        $asesoria = Asesoria::findOrFail($id); // Recupera la asesorÃ­a que se cancelarÃ¡; si no existe, findOrFail responde 404.

        if ($asesoria->estado === 'Cancelada') { // Evita duplicar el evento de cancelaciÃ³n para una asesorÃ­a que ya estÃ¡ cancelada.
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->with('error', 'La asesorÃ­a ya se encuentra cancelada.');
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacciÃ³n garantiza que historial, estado y notificaciones se procesen como una sola operaciÃ³n.
            $hist = new AsesoriaHistorial(); // Registrar acciÃ³n en asesoria_historial | Crea una fila de auditorÃ­a con el estado horario previo y quiÃ©n ejecutÃ³ la acciÃ³n.
            $hist->id_asesoria    = $id;
            $hist->id_usuario     = Auth::id();
            $hist->accion         = 'Cancelo';
            $hist->motivo         = $request->motivo;
            $hist->fecha_anterior = $asesoria->fecha;
            $hist->hora_anterior  = $asesoria->hora_inicio;
            $hist->fecha_nueva    = null;
            $hist->hora_nueva     = null;
            $hist->fecha          = Carbon::now();
            $hist->save(); // Inserta el registro de historial antes de cambiar la asesorÃ­a.

            $asesoria->estado                = 'Cancelada'; // Actualizar asesorÃ­a con motivo y cancelador
            $asesoria->motivo_cancelacion    = $request->motivo;
            $asesoria->id_usuario_cancelador = Auth::id();
            $asesoria->save(); // Guarda el nuevo estado, el motivo y el id del usuario que cancelÃ³.

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesorÃ­a ha sido cancelada.'); // EnvÃ­a el aviso al aprendiz y, si existe, a la persona instructora asignada.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'Una asesorÃ­a que tenÃ­as asignada ha sido cancelada.');
            }
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Regresa al listado con una confirmaciÃ³n persistida solo para la siguiente peticiÃ³n.
            ->with('success', 'AsesorÃ­a cancelada exitosamente y registrada en el historial.');
    }

    public function finalizar(Request $request, $id) // finalizar(): Marca como realizada una asesorÃ­a y registra su cierre; se usa al confirmar que la sesiÃ³n terminÃ³.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Comprueba permisos antes de modificar el estado.

        $asesoria = Asesoria::findOrFail($id); // Busca la asesorÃ­a objetivo y falla con 404 si no existe.

        if ($asesoria->estado === 'Cancelada') { // Las ramas de salida temprana protegen estados en los que no debe cerrarse la asesorÃ­a.
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->with('error', 'No se puede finalizar una asesorÃ­a que ha sido cancelada.');
        }

        if ($asesoria->estado === 'Realizada') {
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->with('info', 'La asesorÃ­a ya se encuentra marcada como finalizada.');
        }

        DB::transaction(function () use ($asesoria, $request, $id) { // La transacciÃ³n mantiene consistente el cierre, el seguimiento opcional y las notificaciones.
            $asesoria->estado = 'Realizada'; // Cambiar estado a Realizada
            $asesoria->save(); // Persiste el cierre antes de generar sus avisos relacionados.

            if ($request->filled('observaciones')) { // Si se suministraron observaciones o notas de finalizaciÃ³n, se registra el seguimiento correspondiente | filled exige un valor no vacÃ­o, por lo que un formulario sin observaciones no crea seguimiento.
                $seg = new AsesoriaSeguimiento(); // new prepara un registro de seguimiento en memoria y las asignaciones completan sus columnas.
                $seg->id_asesoria                     = $id;
                $seg->id_usuario                      = Auth::id();
                $seg->id_asesoria_seguimiento_corrige = null;
                $seg->avances                         = $request->input('avances', 'AsesorÃ­a finalizada y completada con Ã©xito.');
                $seg->compromisos                     = $request->input('compromisos', 'Cierre formal de la asesorÃ­a.');
                $seg->observaciones                   = $request->observaciones;
                $seg->fecha                           = Carbon::now();
                $seg->save(); // Inserta la nota de cierre relacionada con esta asesorÃ­a.
            }

            $this->notificarUsuario($asesoria->aprendiz, 'Tu asesorÃ­a ha sido marcada como finalizada exitosamente.', $asesoria, 'finalizada'); // Avisa a las personas relacionadas; la notificaciÃ³n al instructor depende de que haya asignaciÃ³n.
            if ($asesoria->instructor) {
                $this->notificarUsuario($asesoria->instructor, 'La asesorÃ­a asignada ha sido completada y finalizada.', $asesoria, 'finalizada');
            }
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Redirige al panel con el identificador de la asesorÃ­a cerrada en el mensaje.
            ->with('success', "AsesorÃ­a #{$id} marcada como finalizada exitosamente.");
    }

    public function storeSeguimiento(StoreSeguimientoRequest $request, $id) // storeSeguimiento(): Valida y guarda una nota de seguimiento; se usa al documentar avances de la asesorÃ­a.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // El request especializado valida los campos y el helper comprueba el rol antes de continuar.

        $asesoria = Asesoria::findOrFail($id); // Asegura que la nota se asocie con una asesorÃ­a existente.

        DB::transaction(function () use ($asesoria, $request, $id) { // Agrupa la creaciÃ³n de la nota y el cambio de estado en una sola transacciÃ³n.
            $seg = new AsesoriaSeguimiento(); // Crea una nueva fila de seguimiento y vincula su autor y asesorÃ­a.
            $seg->id_asesoria                     = $id;
            $seg->id_usuario                      = Auth::id();
            $seg->id_asesoria_seguimiento_corrige = null;
            $seg->avances                         = $request->avances;
            $seg->compromisos                     = $request->compromisos;
            $seg->observaciones                   = $request->observaciones;
            $seg->fecha                           = Carbon::now();
            $seg->save(); // Inserta la nota validada en la tabla de seguimientos.

            $asesoria->estado = 'Realizada'; // Pasa a estado Realizada
            $asesoria->save(); // Guarda el nuevo estado junto con la nota, dentro de la misma transacciÃ³n.
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Informa al usuario que se guardÃ³ el seguimiento y se cerrÃ³ la asesorÃ­a.
            ->with('success', 'Seguimiento registrado exitosamente. La asesorÃ­a pasÃ³ a estado Realizada.');
    }

    public function corregirSeguimiento(CorregirSeguimientoRequest $request, $id) // corregirSeguimiento(): Registra la correcciÃ³n de un seguimiento previo; se usa al rectificar una nota existente.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // El formulario llega validado por el request especializado; el helper comprueba el rol administrador.

        $original = AsesoriaSeguimiento::findOrFail($id); // $id es el id_asesoria_seguimiento original a corregir | findOrFail localiza la nota base sin modificarla y devuelve 404 si no existe.

        DB::transaction(function () use ($original, $request) { // Envuelve la nueva correcciÃ³n en una transacciÃ³n para evitar una fila parcial.
            $nuevo = new AsesoriaSeguimiento(); // Crea un registro NUEVO que apunta al original, nunca borra ni sobrescribe | El nuevo modelo conserva la trazabilidad enlazando su id con el seguimiento original.
            $nuevo->id_asesoria                     = $original->id_asesoria;
            $nuevo->id_usuario                      = Auth::id();
            $nuevo->id_asesoria_seguimiento_corrige = $original->id_asesoria_seguimiento;
            $nuevo->avances                         = $request->avances;
            $nuevo->compromisos                     = $request->compromisos;
            $nuevo->observaciones                   = $request->observaciones;
            $nuevo->motivo_correccion               = $request->motivo_correccion;
            $nuevo->fecha                           = Carbon::now();
            $nuevo->save(); // Inserta la correcciÃ³n como una fila independiente.
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Regresa al listado sin alterar la nota anterior y comunica el resultado.
            ->with('success', 'CorrecciÃ³n de seguimiento registrada exitosamente sin alterar el registro original.');
    }

    public function agregarRecurso(AsignarRecursoRequest $request, $id) // agregarRecurso(): Asocia un recurso de inventario a una asesorÃ­a; se usa al asignar materiales necesarios.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // El request valida cantidad y recurso; aquÃ­ se verifica permiso administrativo.

        $asesoria = Asesoria::findOrFail($id); // Recupera ambos modelos y devuelve 404 si la asesorÃ­a o el inventario no existen.
        $inventario = Inventory::findOrFail($request->id_inventario);

        if ($inventario->stock < $request->cantidad) { // Compara disponibilidad antes de iniciar el descuento para evitar asignar mÃ¡s de lo existente.
            session()->flash('modal_abrir', 'modalRecursos'); // Preserva el modal y los valores del formulario para que el usuario corrija la cantidad.
            session()->flash('modal_id', $id);
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withInput()->withErrors([
                'cantidad' => "Stock insuficiente en inventario. Stock disponible: {$inventario->stock}"
            ])->with('error', 'Stock insuficiente para este recurso.');
        }

        DB::transaction(function () use ($asesoria, $inventario, $request, $id) { // Agrupa descuento y vÃ­nculo del recurso: ambos cambios se confirman juntos o ninguno queda aplicado.
            $inventario->stock -= $request->cantidad; // Descontar del inventario | Ajusta la existencia disponible y evita que amount resulte negativo.
            $inventario->amount = max(0, $inventario->amount - $request->cantidad);
            $inventario->save(); // Persiste los nuevos valores del inventario.

            $recurso = AsesoriaRecurso::where('id_asesoria', $id) // Vincular recurso en asesoria_recursos | where limita por asesorÃ­a e inventario; first obtiene la primera relaciÃ³n o null.
                ->where('id_inventario', $inventario->id)
                ->first();

            if ($recurso) { // Si el vÃ­nculo ya existe, suma la cantidad; si no, crea y guarda uno nuevo.
                $recurso->cantidad += $request->cantidad;
                $recurso->save(); // Guarda el total acumulado de este recurso para la asesorÃ­a.
            } else {
                $recurso = new AsesoriaRecurso(); // Prepara un vÃ­nculo nuevo entre la asesorÃ­a y el artÃ­culo de inventario.
                $recurso->id_asesoria   = $id;
                $recurso->id_inventario = $inventario->id;
                $recurso->cantidad      = $request->cantidad;
                $recurso->save(); // Inserta la relaciÃ³n con la cantidad solicitada.
            }
        });

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Regresa al panel y confirma la cantidad descontada.
            ->with('success', "Recurso vinculado exitosamente. Stock descontado: {$request->cantidad}.");
    }

    public function consultarDisponibilidad($usuario) // consultarDisponibilidad(): Consulta los horarios disponibles de un instructor; se usa al mostrar opciones de agenda.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Solo los administradores pueden consultar la agenda de otros instructores.

        $nombresDias = [ // Esta tabla traduce el nÃºmero de dÃ­a de la base de datos a un nombre legible.
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'MiÃ©rcoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'SÃ¡bado',
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
                return /* Devuelve el resultado de este mï¿½todo. */ [
                    'id'          => $d->id_disponibilidad_instructor,
                    'anio'        => $d->anio,
                    'trimestre'   => 'T' . $d->trimestre,
                    'dia_semana'  => $d->dia_semana,
                    'dia_nombre'  => $nombresDias[$d->dia_semana] ?? "DÃ­a {$d->dia_semana}",
                    'hora_inicio' => substr($d->hora_inicio, 0, 5),
                    'hora_fin'    => substr($d->hora_fin, 0, 5),
                    'habilitada'  => (bool)$d->habilitada,
                ];
            });

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([ // Devuelve JSON con el resultado transformado para el consumidor de la interfaz.
            'success'          => true,
            'disponibilidades' => $disponibilidades,
        ]);
    }

    public function guardarDisponibilidad(Request $request) // guardarDisponibilidad(): Valida y guarda la disponibilidad horaria de instructores; se usa al actualizar sus franjas disponibles.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // No procesa el formulario de agenda hasta confirmar el rol administrador.

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

        if ($validator->fails()) { // fails detecta entradas invÃ¡lidas; flash permite reabrir el modal y back devuelve errores y datos.
            session()->flash('modal_abrir', 'modalDisponibilidad');
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) { // Una transacciÃ³n protege la invalidaciÃ³n de franjas antiguas y el alta de la nueva.
            DisponibilidadInstructor::where('id_usuario', $request->id_usuario) // Franjas anteriores para el mismo instructor, aÃ±o, trimestre y dÃ­a pasan a vigente=false (conservando historial) | where encadena los cuatro criterios; update modifica en bloque las coincidencias sin borrarlas.
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

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Vuelve al listado y muestra confirmaciÃ³n temporal de la nueva franja.
            ->with('success', 'Franja de disponibilidad guardada. Las franjas anteriores pasaron a historial.');
    }

    public function storeCategoria(Request $request) // storeCategoria(): Valida y crea una categorÃ­a de asesorÃ­a; se usa al registrar una nueva opciÃ³n de clasificaciÃ³n.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Impide que perfiles sin permisos administrativos modifiquen el catÃ¡logo.

        $validator = Validator::make($request->all(), [ // all entrega los campos enviados; Validator construye las reglas y mensajes personalizados.
            'nombre'         => ['required', 'string', 'max:100', 'unique:categorias_asesoria,nombre'],
            'instructores'   => ['nullable', 'array'],
            'instructores.*' => ['exists:users,id'],
        ], [
            'nombre.required' => 'El nombre de la categorÃ­a es obligatorio.',
            'nombre.unique'   => 'Ya existe una categorÃ­a con ese nombre.',
        ]);

        if ($validator->fails()) { // Si hay errores, conserva los datos y solicita reabrir el modal de categorÃ­as.
            session()->flash('modal_abrir', 'modalCategorias');
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withErrors($validator)->withInput();
        }

        $categoria = new CategoriaAsesoria(); // Crea el modelo en memoria, limpia el nombre y asigna estado inicial.
        $categoria->nombre = trim($request->nombre);
        $categoria->estado = 'Activa';
        $categoria->save(); // save inserta la nueva categorÃ­a.

        if ($request->has('instructores') && is_array($request->instructores)) { // has verifica si llegÃ³ el campo y is_array confirma que contenga una lista antes de sincronizar la relaciÃ³n.
            $categoria->instructores()->sync($request->instructores); // sync mantiene la relaciÃ³n muchos-a-muchos exactamente con los ids recibidos.
        }

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Vuelve a la pantalla administrativa con un aviso temporal.
            ->with('success', "CategorÃ­a '{$categoria->nombre}' creada exitosamente.");
    }

    public function updateCategoria(Request $request, $id) // updateCategoria(): Valida y actualiza una categorÃ­a existente; se usa al guardar su ediciÃ³n.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Requiere el rol administrador para cambiar categorÃ­as.

        $categoria = CategoriaAsesoria::findOrFail($id); // Busca la categorÃ­a o detiene la peticiÃ³n con 404.

        $validator = Validator::make($request->all(), [ // Valida campos y excluye el id actual de la regla unique para permitir que conserve su mismo nombre.
            'nombre'         => ['required', 'string', 'max:100', 'unique:categorias_asesoria,nombre,'.$id.',id_categoria_asesoria'],
            'instructores'   => ['nullable', 'array'],
            'instructores.*' => ['exists:users,id'],
        ], [
            'nombre.required' => 'El nombre de la categorÃ­a es obligatorio.',
            'nombre.unique'   => 'Ya existe una categorÃ­a con ese nombre.',
        ]);

        if ($validator->fails()) { // Si la validaciÃ³n falla, preserva la entrada y devuelve al modal con los errores.
            session()->flash('modal_abrir', 'modalCategorias');
            return /* Regresa a la pantalla anterior para continuar el proceso. */ back()->withErrors($validator)->withInput();
        }

        $categoria->nombre = trim($request->nombre); // Limpia el nuevo nombre y save persiste el cambio.
        $categoria->save();

        if ($request->has('instructores') && is_array($request->instructores)) { // Si hay una lista explÃ­cita de instructores, sync reemplaza las relaciones por esa lista.
            $categoria->instructores()->sync($request->instructores);
        } else {
            $categoria->instructores()->sync([]); // Sin lista recibida se retiran todos los vÃ­nculos existentes.
        }

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Regresa al panel para mostrar la confirmaciÃ³n de actualizaciÃ³n.
            ->with('success', "CategorÃ­a '{$categoria->nombre}' actualizada exitosamente.");
    }

    public function toggleCategoriaEstado($id) // toggleCategoriaEstado(): Alterna el estado activo de una categorÃ­a; se usa al habilitarla o deshabilitarla desde el catÃ¡logo.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Solo una cuenta administradora puede activar o desactivar categorÃ­as.

        $categoria = CategoriaAsesoria::findOrFail($id); // Recupera la categorÃ­a o responde 404 si el id no existe.
        $categoria->estado = ($categoria->estado === 'Activa') ? 'Inactiva' : 'Activa'; // El operador ternario invierte el estado entre Activa e Inactiva.
        $categoria->save(); // Guarda el valor alternado en la base de datos.

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index') // Vuelve al catÃ¡logo y muestra cuÃ¡l estado quedÃ³ aplicado.
            ->with('success', "Estado de la categorÃ­a cambiado a '{$categoria->estado}'.");
    }

    public function reporte(Request $request) // reporte(): Genera los datos de asesorÃ­as segÃºn filtros; se usa al consultar o exportar el reporte administrativo.
    {
        if ($res = $this->verificarAccesoAdmin()) return /* Devuelve el resultado de este método. */ $res; // Rechaza perfiles no administrativos antes de preparar o exportar datos.

        $query = Asesoria::with([ // with carga las relaciones que se imprimirÃ¡n junto con cada asesorÃ­a.
            'categoria',
            'aprendiz.person',
            'instructor.person',
            'usuarioRegistro.person',
            'usuarioCancelador.person',
        ]);

        if ($request->filled('categoria_id')) { // Cada bloque filled aplica su filtro solo cuando la peticiÃ³n contiene un valor no vacÃ­o.
            $query->where('id_categoria_asesoria', $request->categoria_id); // where agrega una igualdad a la consulta que se ejecutarÃ¡ al final.
        }
        if ($request->filled('instructor_id')) {
            if ($request->instructor_id === 'sin_asignar') {
                $query->whereNull('id_usuario_instructor'); // whereNull selecciona asesorÃ­as sin instructor.
            } else {
                $query->where('id_usuario_instructor', $request->instructor_id); // En los demÃ¡s casos se filtra por el id del instructor solicitado.
            }
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado); // AÃ±ade la condiciÃ³n del estado solicitado.
        }
        if ($request->filled('trimestre')) {
            $t = (int)$request->trimestre; // Convierte el trimestre a entero para usarlo como selector de los lÃ­mites de mes.
            $meses = match($t) { // match elige el rango de meses; el caso default cubre todo el aÃ±o si el valor no coincide.
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
                default => [1, 12],
            };
            $query->whereMonth('fecha', '>=', $meses[0])->whereMonth('fecha', '<=', $meses[1]); // whereMonth agrega los lÃ­mites inclusivos de inicio y fin para ese trimestre.
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde); // whereDate compara la fecha calendario con el lÃ­mite inicial.
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta); // Aplica el lÃ­mite final inclusivo.
        }

        $asesorias = $query->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'desc')->get(); // orderBy define el orden de salida y get ejecuta la consulta, cargando todas las filas que cumplen los filtros.

        if ($request->get('formato') === 'csv') { // 1. ExportaciÃ³n a CSV compatible con Excel | get recupera formato de la peticiÃ³n para seleccionar la representaciÃ³n de salida.
            $filename = 'reporte_asesorias_' . date('Ymd_His') . '.csv'; // Genera un nombre Ãºnico con fecha y hora para el archivo descargable.

            $headers = [ // Estos encabezados indican al navegador el tipo, nombre y polÃ­tica de cachÃ© del archivo.
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $callback = function () use ($asesorias) { // La respuesta ejecuta este callback al transmitir el archivo en lugar de guardarlo en el servidor.
                $file = fopen('php://output', 'w'); // fopen abre el flujo de salida HTTP en modo escritura.
                fputs($file, "\xEF\xBB\xBF"); // BOM UTF-8 para visualizaciÃ³n correcta de caracteres especiales en Excel | fputs escribe la marca de codificaciÃ³n al inicio del CSV.

                fputcsv($file, [ // Encabezados con separador punto y coma (estÃ¡ndar Excel en espaÃ±ol) | fputcsv escribe una fila escapando separadores y comillas segÃºn el formato CSV.
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

                foreach ($asesorias as $a) { /* Recorre uno por uno los elementos de esta lista. */ // foreach recorre cada asesorÃ­a y escribe una fila con sus datos y valores relacionados.
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

            return /* Devuelve una respuesta HTTP con su contenido y estado. */ response()->stream($callback, 200, $headers); // stream envÃ­a el contenido generado por el callback con cÃ³digo HTTP y encabezados de descarga.
        }

        if ($request->get('formato') === 'pdf' || $request->get('formato') === 'print') { // 2. Vista de impresiÃ³n limpia / PDF con window.print() | get lee el formato y la condiciÃ³n acepta tanto la exportaciÃ³n PDF imprimible como la vista print.
            $filasHtml = ''; // Acumula el HTML que se insertarÃ¡ como cuerpo de la tabla.
            foreach ($asesorias as $a) { /* Recorre uno por uno los elementos de esta lista. */ // El foreach crea una fila por asesorÃ­a; e escapa texto dinÃ¡mico para evitar que se interprete como HTML.
                $aprendizNom = e($a->aprendiz ? $a->aprendiz->full_name : 'No registrado');
                $catNom = e($a->categoria ? $a->categoria->nombre : 'Sin categorÃ­a');
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
                <title>Reporte de AsesorÃ­as - ECP</title>
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
                        <h2>Escuela Cultura de Paz - Reporte de AsesorÃ­as</h2>
                        <small>Generado el: " . date('d/m/Y H:i') . " | Total asesorÃ­as listadas: " . $asesorias->count() . "</small>
                    </div>
                    <div style='color: #39A900; font-weight: bold; font-size: 16px;'>Control ECP</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Aprendiz</th>
                            <th>CategorÃ­a</th>
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

            return /* Devuelve una respuesta HTTP con su contenido y estado. */ response($html); // Devuelve el documento generado como respuesta HTTP para que el navegador lo imprima o guarde como PDF.
        }

        return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.administrador.asesorias.index'); // Si no se solicita exportaciÃ³n, vuelve al listado principal de asesorÃ­as.
    }
}
