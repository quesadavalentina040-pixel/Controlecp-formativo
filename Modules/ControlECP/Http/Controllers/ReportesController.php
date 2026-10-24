<?php
// Declara que este archivo usa la sintaxis PHP (obligatorio en todo archivo PHP)

namespace Modules\ControlECP\Http\Controllers;
// Define el espacio de nombres para que Laravel pueda localizar y autocargar esta clase

use Illuminate\Http\Request;
// Importa la clase Request de Laravel para recibir y leer datos enviados desde formularios o URL

use Illuminate\Routing\Controller;
// Importa la clase base Controller de Laravel que provee funcionalidades comunes a todos los controladores

use Illuminate\Support\Facades\Auth;
// Importa el facade Auth para obtener el usuario actualmente autenticado en la sesión

use Modules\SICA\Entities\Course;
// Importa el modelo Course del módulo SICA — representa las fichas de formación académica del SENA

use App\Models\User;
// Importa el modelo User de la aplicación — representa los usuarios registrados (admins, instructores, aprendices)

use Modules\ControlECP\Entities\AsistenciaRegistro;
// Importa el modelo AsistenciaRegistro — almacena cada registro de asistencia individual por aprendiz y actividad

use Modules\ControlECP\Entities\Certificado;
// Importa el modelo Certificado — representa la certificación de una ficha por momento pedagógico (1, 2 o 3)

use Modules\ControlECP\Entities\Pqrs;
// Importa el modelo Pqrs — almacena las Peticiones, Quejas, Reclamos y Sugerencias radicadas por aprendices

use Modules\ControlECP\Entities\TipoPqrs;
// Importa el modelo TipoPqrs — catálogo de tipos de PQRS (Petición, Queja, Reclamo, Sugerencia, Felicitación)

use Modules\ControlECP\Entities\Inventario;
// Importa el modelo Inventario — representa cada recurso físico del inventario de la Escuela Cultura de Paz

use Modules\ControlECP\Entities\MovimientoInventario;
// Importa el modelo MovimientoInventario — registra cada entrega, devolución, cambio de estado o baja de un recurso

use Modules\ControlECP\Entities\RecursoNovedad;
// Importa el modelo RecursoNovedad — registra observaciones y novedades sobre el estado de un recurso del inventario

use Modules\ControlECP\Entities\CategoriaInventario;
// Importa el modelo CategoriaInventario — catálogo de categorías para clasificar los recursos del inventario

use Modules\ControlECP\Entities\ActividadEcp;
// Importa el modelo ActividadEcp — representa cada una de las 18 actividades pedagógicas de los 3 momentos ECP

use Modules\ControlECP\Entities\CalificacionEcp;
// Importa el modelo CalificacionEcp — almacena la calificación (1-5 estrellas) que un aprendiz da a una actividad

use Modules\ControlECP\Entities\Momento;
// Importa el modelo Momento — representa los 3 momentos pedagógicos del programa ECP (Autoconciencia, Convivencia, Resolución)

/**
 * ReportesController — Módulo Control ECP
 *
 * Controlador central que unifica los 8 tipos de reporte del módulo ECP.
 * Cada método corresponde a una historia de usuario específica:
 *
 *   HU-001 Panel principal de reportes    HU-008 Reporte POE
 *   HU-002 Reporte de asistencias         HU-009 Filtros por ficha y fecha
 *   HU-003 Reporte de encuestas           HU-010 Validación de rango de fechas
 *   HU-004 Reporte de asesorías           HU-011 Filtrar por responsable/instructor
 *   HU-005 Reporte de PQRS               HU-012 Mostrar totales e indicadores
 *   HU-006 Reporte de certificación       HU-013 Exportar reporte en PDF
 *   HU-007 Reporte de inventario          HU-014 Manejar reporte sin datos
 *
 * Integración: preparado para conectar con la API REST Central ERP SENA Empresa / SICEFA.
 * Los métodos getMockAsesorias() y getMockPoe() usan datos estructurados locales
 * que se reemplazarán con llamadas HTTP reales al endpoint de SICEFA.
 */
class ReportesController extends Controller
// Define la clase ReportesController que hereda de Controller, agrupando todos los reportes del módulo ECP
{
    // ══════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS — métodos de soporte usados internamente por el controlador
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Verifica que el usuario autenticado tenga el rol de administrador ECP.
     * Si no tiene permisos, redirige al inicio del módulo con un mensaje de error.
     * Se llama al inicio de cada método público para proteger las rutas.
     */
    private function verificarAcceso() // Método privado — solo accesible dentro de este controlador
    {
        $u = Auth::user(); // Obtiene el usuario autenticado en la sesión actual
        if (!$u || !$u->hasRole('controlecp.admin')) { // Verifica si existe y si tiene el rol de administrador ECP
            return redirect()->route('controlecp.index') // Redirige al panel de inicio del módulo ECP
                ->with('error', 'Sin permisos para acceder al módulo de Reportes.'); // Envía mensaje de error flash a la vista
        }
        return null; // Retorna null si el acceso es válido — el método que llama continúa su ejecución
    }

    /**
     * Devuelve la colección de usuarios que tienen asignado un rol específico (por slug).
     * No usa Spatie Permission — usa la relación roles() definida en el modelo User del proyecto,
     * que consulta la tabla pivote role_user y el campo slug del modelo Role de SICA.
     *
     * @param string $slug  Identificador único del rol, ej: 'controlecp.instructor'
     * @return \Illuminate\Support\Collection  Colección de usuarios con su relación person cargada
     */
    private function porRol(string $slug): \Illuminate\Support\Collection // Método privado que recibe el slug del rol y devuelve una colección
    {
        return User::whereHas('roles', fn($q) => $q->where('slug', $slug)) // Filtra usuarios que tengan al menos un rol con ese slug
            ->with('person')    // Carga la relación person para acceder a nombre, documento y apellidos
            ->orderBy('nickname') // Ordena alfabéticamente por nickname para mostrar en dropdowns de filtros
            ->get();            // Ejecuta la consulta y devuelve todos los resultados como colección
    }

    /**
     * Calcula el porcentaje global de asistencia sobre todos los registros no anulados.
     * Se usa en el panel principal (HU-001) para mostrar el indicador general del sistema.
     *
     * @return float  Porcentaje con un decimal, ej: 87.3
     */
    private function calcularPctAsistenciaGlobal(): float // Método privado que retorna un número decimal
    {
        $total   = AsistenciaRegistro::where('estado', '!=', 'ANULADA')->count(); // Cuenta todos los registros activos excluyendo los anulados
        $asistio = AsistenciaRegistro::where('estado', 'ASISTIO')->count();        // Cuenta solo los registros donde el aprendiz asistió
        return $total > 0 ? round(($asistio / $total) * 100, 1) : 0; // Calcula el porcentaje con 1 decimal; retorna 0 si no hay registros
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-001 · PANEL PRINCIPAL — muestra los 8 tipos de reporte disponibles
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Muestra el panel de inicio del módulo de Reportes.
     * Calcula indicadores globales reales de la base de datos para mostrar
     * en las tarjetas de acceso rápido a cada tipo de reporte.
     */
    public function index() // Método público — accesible desde la ruta GET /control-ecp/administrador/reportes
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica permisos; si falla, redirige y detiene la ejecución

        $indicadores = [ // Array asociativo con los contadores globales del sistema para los KPIs del panel
            'total_asistencias'  => AsistenciaRegistro::where('estado', '!=', 'ANULADA')->count(), // Total de registros de asistencia activos en toda la BD
            'pct_asistencia'     => $this->calcularPctAsistenciaGlobal(),                          // Porcentaje global de asistencia calculado con el helper privado
            'total_certificados' => Certificado::where('estado', 'CERTIFICADA')->count(),          // Total de momentos certificados exitosamente en todas las fichas
            'total_pqrs'         => Pqrs::count(),                                                  // Total de PQRS radicadas (todos los estados, incluyendo soft-deleted no)
            'pqrs_pendientes'    => Pqrs::where('estado', 'Pendiente')->count(),                   // PQRS que aún no han sido atendidas — alerta para el administrador
            'total_inventario'   => Inventario::where('estado', '!=', 'Inactivo')->count(),        // Recursos activos en el inventario (excluye dados de baja)
            'inv_criticos'       => Inventario::where('estado', '!=', 'Inactivo')->get()           // Carga todos los recursos activos para evaluar su semáforo en PHP
                                        ->filter(fn($r) => $r->semaforo === 'rojo')->count(),      // Filtra en memoria los que tienen semáforo rojo (disponibilidad crítica)
            'fichas_activas'     => Course::where(fn($q) =>                                        // Cuenta fichas en estado Activo o sin estado registrado (null = activa por defecto)
                                        $q->where('status', 'Activo')->orWhereNull('status'))->count(),
        ];

        return view('controlecp::administrador.reportes', compact('indicadores')); // Renderiza la vista del panel enviando los indicadores calculados
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-002 · REPORTE DE ASISTENCIAS
    // Genera listado por ficha, actividad, momento, instructor y estado.
    // Sin gráficas en la pestaña principal; gráficas en pestaña separada.
    // ══════════════════════════════════════════════════════════════════════

    public function asistencias(Request $request) // Método público — recibe los filtros enviados por el formulario GET
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Bloquea el acceso si el usuario no es administrador ECP

        // HU-010: Validar que la fecha inicial no sea mayor a la fecha final
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // Verifica que ambas fechas estén presentes en la petición
            && $request->fecha_desde > $request->fecha_hasta) {                 // Compara las fechas como strings ISO (YYYY-MM-DD) — funciona correctamente
            return back()->withErrors(['fecha' => 'La fecha inicial no puede ser mayor a la fecha final.'])->withInput(); // Regresa al formulario con el error y conserva los valores ingresados
        }

        $filtros = $request->only([ // Extrae solo los campos de filtro del request — descarta cualquier campo no esperado
            'ficha','actividad_id','momento_id','instructor_id','fecha_desde','fecha_hasta','estado'
        ]);

        $fichas       = Course::select('id','code')->orderBy('code')->get();          // Lista de fichas para el selector del formulario de filtros
        $instructores = $this->porRol('controlecp.instructor');                       // Lista de instructores ECP para el filtro de responsable (HU-011)
        $momentos     = Momento::orderBy('orden')->get();                             // Lista de momentos pedagógicos ordenados para el selector de filtro
        $actividades  = ActividadEcp::select('id','numero','nombre','momento_id')     // Lista de actividades para el selector; solo los campos necesarios
                            ->orderBy('numero')->get();                               // Ordenadas por número de actividad (1 al 18)

        // HU-009: Construcción dinámica de la consulta aplicando los filtros seleccionados
        $query = AsistenciaRegistro::with(['course.program','instructor.person']) // Carga eager las relaciones necesarias para evitar N+1 queries
            ->where('estado', '!=', 'ANULADA'); // Excluye registros anulados — no deben aparecer en reportes de asistencia

        if (!empty($filtros['ficha']))           // Si el usuario seleccionó una ficha específica
            $query->whereHas('course', fn($q) => $q->where('code','like','%'.$filtros['ficha'].'%')); // Filtra por código de ficha usando LIKE para búsqueda parcial

        if (!empty($filtros['actividad_id']))    // Si el usuario seleccionó una actividad específica
            $query->where('actividad_id', $filtros['actividad_id']); // Filtra por ID exacto de la actividad ECP

        if (!empty($filtros['momento_id']))      // Si el usuario seleccionó un momento pedagógico
            $query->where('momento_id', $filtros['momento_id']); // Filtra registros del momento 1, 2 o 3

        if (!empty($filtros['instructor_id']))   // HU-011: Si el usuario filtró por instructor/responsable
            $query->where('instructor_id', $filtros['instructor_id']); // Muestra solo lo registrado por ese instructor

        if (!empty($filtros['estado']))          // Si el usuario seleccionó un estado de asistencia
            $query->where('estado', $filtros['estado']); // Filtra por ASISTIO, FALTO, EXCUSA o RETARDO

        if (!empty($filtros['fecha_desde']))     // Si hay fecha inicial definida
            $query->whereDate('fecha', '>=', $filtros['fecha_desde']); // Incluye registros desde esa fecha (inclusive)

        if (!empty($filtros['fecha_hasta']))     // Si hay fecha final definida
            $query->whereDate('fecha', '<=', $filtros['fecha_hasta']); // Incluye registros hasta esa fecha (inclusive)

        $registros = $query->orderByDesc('fecha')->get(); // Ejecuta la consulta y ordena del más reciente al más antiguo

        // HU-012: Calcular totales e indicadores para mostrar en la parte superior del reporte
        $total         = $registros->count();                                        // Total de registros que coinciden con los filtros aplicados
        $asistio       = $registros->where('estado','ASISTIO')->count();             // Cantidad de aprendices que asistieron
        $falto         = $registros->where('estado','FALTO')->count();               // Cantidad de aprendices que faltaron sin excusa
        $excusa        = $registros->where('estado','EXCUSA')->count();              // Cantidad de aprendices con excusa justificada
        $retardo       = $registros->where('estado','RETARDO')->count();             // Cantidad de aprendices que llegaron tarde
        $pctAsistencia = $total > 0 ? round(($asistio / $total) * 100, 1) : 0;      // Porcentaje de asistencia del conjunto filtrado con 1 decimal
        $pctFalta      = $total > 0 ? round(($falto  / $total) * 100, 1) : 0;       // Porcentaje de inasistencia del conjunto filtrado con 1 decimal

        // Agrupación por ficha para la pestaña "Por Ficha" del reporte
        $porFicha = $registros->groupBy('course_id')->map(function ($items) { // Agrupa todos los registros por ID de ficha
            $t = $items->count();                                              // Total de registros en esa ficha
            $a = $items->where('estado','ASISTIO')->count();                   // Total de asistencias en esa ficha
            return [
                'ficha'    => $items->first()->course?->code ?? 'N/A',        // Código numérico de la ficha, ej: 2977588
                'programa' => $items->first()->course?->program?->name ?? 'N/A', // Nombre del programa de formación de la ficha
                'total'    => $t,                                              // Total de registros de asistencia de la ficha
                'asistio'  => $a,                                              // Cantidad de aprendices que asistieron en la ficha
                'falto'    => $items->where('estado','FALTO')->count(),        // Cantidad que faltaron en la ficha
                'excusa'   => $items->where('estado','EXCUSA')->count(),       // Cantidad con excusa en la ficha
                'retardo'  => $items->where('estado','RETARDO')->count(),      // Cantidad con retardo en la ficha
                'pct'      => $t > 0 ? round(($a / $t) * 100, 1) : 0,        // Porcentaje de asistencia de la ficha con barra de progreso
            ];
        })->values(); // Reindexa el array para que sea una lista numerada (0,1,2...) en lugar de asociativa

        // Datos para la gráfica de distribución por estado (doughnut chart)
        $chartEstadosLabels = ['Asistió','Faltó','Excusa','Retardo']; // Etiquetas de los segmentos de la gráfica de estados
        $chartEstadosCounts = [$asistio,$falto,$excusa,$retardo];      // Valores numéricos de cada segmento de la gráfica

        // Datos para la gráfica de porcentaje de asistencia por momento (bar chart)
        $chartMomentosLabels = []; // Array que acumulará las etiquetas "Momento 1", "Momento 2", "Momento 3"
        $chartMomentosPct    = []; // Array que acumulará el % de asistencia por cada momento
        foreach ([1,2,3] as $m) {                                                   // Itera sobre los 3 momentos pedagógicos del programa ECP
            $mr = $registros->where('momento_id',$m);                               // Filtra en memoria los registros del momento actual
            $mt = $mr->count();                                                     // Total de registros de ese momento
            $ma = $mr->where('estado','ASISTIO')->count();                          // Total de asistencias de ese momento
            $chartMomentosLabels[] = 'Momento '.$m;                                // Agrega la etiqueta del momento al array de etiquetas
            $chartMomentosPct[]    = $mt > 0 ? round(($ma/$mt)*100,1) : 0;        // Agrega el % de asistencia calculado al array de valores
        }

        // Datos para la gráfica de porcentaje de asistencia por instructor (bar chart horizontal)
        $porInstructor = $registros->groupBy('instructor_id')->map(function ($items) { // Agrupa registros por ID de instructor
            $t = $items->count();                                                        // Total de registros del instructor
            $a = $items->where('estado','ASISTIO')->count();                            // Total de asistencias registradas por ese instructor
            $i = $items->first()->instructor;                                           // Obtiene el objeto User del instructor del primer registro
            return [
                'nombre' => $i?->person?->first_name                                   // Si tiene datos de persona, construye el nombre completo
                    ? $i->person->first_name.' '.$i->person->first_last_name           // Concatena primer nombre y primer apellido
                    : ($i?->nickname ?? 'Instructor'),                                 // Si no, usa el nickname o texto genérico
                'total' => $t,                                                          // Total de registros que ese instructor generó
                'pct'   => $t > 0 ? round(($a/$t)*100,1) : 0,                         // % de asistencia de los registros de ese instructor
            ];
        })->values()->take(10); // Reindexa y limita a los primeros 10 instructores para no saturar la gráfica

        return view('controlecp::administrador.reportes_asistencias', compact( // Renderiza la vista del reporte de asistencias
            'filtros','fichas','instructores','momentos','actividades',          // Variables de datos para los selectores del formulario de filtros
            'registros','total','asistio','falto','excusa','retardo',            // Datos del listado y contadores para indicadores (HU-012)
            'pctAsistencia','pctFalta','porFicha',                               // Porcentajes calculados y agrupación por ficha
            'chartEstadosLabels','chartEstadosCounts',                          // Datos de la gráfica de estados
            'chartMomentosLabels','chartMomentosPct','porInstructor'            // Datos de las gráficas de momentos e instructores
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-003 · REPORTE DE ENCUESTAS
    // Muestra tabulación consolidada con gráficas, promedio por actividad,
    // distribución de estrellas y tasa de participación.
    // ══════════════════════════════════════════════════════════════════════

    public function encuestas(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/encuestas
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica permisos de administrador antes de continuar

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: verifica que ambas fechas estén presentes
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida que la fecha inicial no supere a la final
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna error de validación conservando los datos del formulario
        }

        $filtros     = $request->only(['ficha','actividad_id','momento_id','fecha_desde','fecha_hasta']); // Extrae únicamente los filtros válidos del request
        $fichas      = Course::select('id','code')->orderBy('code')->get();   // Lista de fichas para el selector de filtros
        $momentos    = Momento::orderBy('orden')->get();                      // Lista de momentos para el selector de filtros
        $actividades = ActividadEcp::with('momento')->orderBy('numero')->get(); // Lista de actividades con su momento cargado para el selector

        // Construcción de la consulta de calificaciones con eager loading
        $query = CalificacionEcp::with(['actividad.momento','ficha.program','aprendiz.person']); // Precarga relaciones para evitar N+1 queries al iterar resultados

        if (!empty($filtros['ficha']))         // Si se filtró por ficha
            $query->whereHas('ficha', fn($q) => $q->where('code','like','%'.$filtros['ficha'].'%')); // Busca por código de ficha parcial

        if (!empty($filtros['actividad_id'])) // Si se filtró por actividad específica
            $query->where('actividad_id', $filtros['actividad_id']); // Filtra calificaciones de esa actividad únicamente

        if (!empty($filtros['momento_id']))   // Si se filtró por momento pedagógico
            $query->whereHas('actividad', fn($q) => $q->where('momento_id', $filtros['momento_id'])); // Filtra actividades que pertenezcan a ese momento

        if (!empty($filtros['fecha_desde']))  // Si hay fecha inicial
            $query->whereDate('created_at', '>=', $filtros['fecha_desde']); // Filtra calificaciones registradas desde esa fecha

        if (!empty($filtros['fecha_hasta']))  // Si hay fecha final
            $query->whereDate('created_at', '<=', $filtros['fecha_hasta']); // Filtra calificaciones registradas hasta esa fecha

        $calificaciones = $query->orderByDesc('created_at')->get(); // Ejecuta la consulta ordenando de más reciente a más antigua
        $total          = $calificaciones->count();                  // Cantidad total de calificaciones que coinciden con los filtros
        $promedioGlobal = $total > 0 ? round($calificaciones->avg('estrellas'), 2) : 0; // Promedio global de estrellas con 2 decimales; 0 si no hay datos

        // Tabulación consolidada: agrupa por actividad y calcula distribución de estrellas
        $tabulacion = $calificaciones->groupBy('actividad_id')->map(function ($items) { // Agrupa todas las calificaciones por ID de actividad
            $act = $items->first()->actividad;                                           // Obtiene el modelo ActividadEcp del primer elemento del grupo
            return [
                'actividad'  => $act ? 'Act.'.$act->numero.' — '.$act->nombre : 'N/A', // Etiqueta de la actividad con número y nombre para la tabla
                'momento'    => $act?->momento?->nombre ?? 'N/A',                       // Nombre del momento al que pertenece la actividad
                'total'      => $items->count(),                                         // Total de respuestas recibidas para esa actividad
                'promedio'   => round($items->avg('estrellas'), 2),                     // Promedio de estrellas de esa actividad con 2 decimales
                'dist'       => [                                                        // Distribución individual de respuestas por cantidad de estrellas
                    1 => $items->where('estrellas',1)->count(), // Cantidad de calificaciones de 1 estrella (muy malo)
                    2 => $items->where('estrellas',2)->count(), // Cantidad de calificaciones de 2 estrellas (malo)
                    3 => $items->where('estrellas',3)->count(), // Cantidad de calificaciones de 3 estrellas (regular)
                    4 => $items->where('estrellas',4)->count(), // Cantidad de calificaciones de 4 estrellas (bueno)
                    5 => $items->where('estrellas',5)->count(), // Cantidad de calificaciones de 5 estrellas (excelente)
                ],
            ];
        })->sortByDesc(fn($v) => $v['total'])->values(); // Ordena de mayor a menor participación y reindexa el array

        $chartActLabels   = $tabulacion->take(10)->pluck('actividad')->toArray(); // Etiquetas para la gráfica de barras (top 10 actividades con más respuestas)
        $chartActPromedio = $tabulacion->take(10)->pluck('promedio')->toArray();   // Promedios de cada actividad para la gráfica de barras horizontales

        $distGlobal = []; // Array que acumula la distribución global de estrellas (1 a 5) para la gráfica de distribución
        for ($i = 1; $i <= 5; $i++) {                                  // Itera del 1 al 5 para cada nivel de estrella
            $distGlobal[$i] = $calificaciones->where('estrellas', $i)->count(); // Cuenta cuántas calificaciones tienen exactamente i estrellas
        }

        return view('controlecp::administrador.reportes_encuestas', compact( // Renderiza la vista del reporte de encuestas
            'filtros','fichas','momentos','actividades',                       // Datos para los selectores del formulario
            'calificaciones','total','promedioGlobal','tabulacion',            // Datos del reporte: listado, totales e indicadores
            'chartActLabels','chartActPromedio','distGlobal'                  // Datos para las dos gráficas Chart.js
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-004 · REPORTE DE ASESORÍAS
    // Muestra listado por categoría, instructor, estado y fecha.
    // Usa dataset estructurado local listo para conectar con la API SICEFA.
    // ══════════════════════════════════════════════════════════════════════

    public function asesorias(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/asesorias
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Bloquea si no es administrador ECP

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: verifica presencia de ambas fechas
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida coherencia del rango de fechas
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna con error y conserva datos
        }

        $filtros      = $request->only(['ficha','instructor_id','fecha_desde','fecha_hasta','estado']); // Extrae filtros válidos del request
        $fichas       = Course::select('id','code')->orderBy('code')->get(); // Lista de fichas para el selector de filtros
        $instructores = $this->porRol('controlecp.instructor');              // Lista de instructores ECP para el filtro de responsable (HU-011)

        // Obtiene el dataset de asesorías: local ahora, API SICEFA cuando esté disponible
        // Para conectar SICEFA: reemplazar esta línea con Http::withToken($token)->get($url.'/asesorias')
        $asesoriasMock = $this->getMockAsesorias($filtros); // Genera el dataset estructurado aplicando los filtros seleccionados

        // HU-012: Calcular totales e indicadores para los KPIs del reporte
        $col         = collect($asesoriasMock);                          // Convierte el array en colección Laravel para usar métodos como where() y count()
        $total       = $col->count();                                    // Total de asesorías que coinciden con los filtros
        $solicitadas = $col->where('estado','Solicitada')->count();      // Asesorías solicitadas pero aún no programadas
        $programadas = $col->where('estado','Programada')->count();      // Asesorías ya agendadas pero no realizadas
        $realizadas  = $col->where('estado','Realizada')->count();       // Asesorías efectivamente llevadas a cabo
        $canceladas  = $col->where('estado','Cancelada')->count();       // Asesorías que fueron canceladas
        $sinAsignar  = $col->filter(fn($a) => empty($a['instructor_id']))->count(); // Asesorías que no tienen instructor asignado

        // Datos para la gráfica de distribución por categoría (pie chart)
        $porCat          = $col->groupBy('categoria')->map(fn($g) => $g->count())->sortDesc(); // Agrupa y cuenta asesorías por categoría, de mayor a menor
        $chartCatLabels  = $porCat->keys()->toArray();   // Array con los nombres de las categorías para las etiquetas de la gráfica
        $chartCatCounts  = $porCat->values()->toArray(); // Array con los conteos de cada categoría para los valores de la gráfica

        // Datos para la gráfica de asesorías por instructor (bar chart horizontal)
        $porInst          = $col->groupBy('instructor_nombre')->map(fn($g) => $g->count())->sortDesc()->take(8); // Top 8 instructores con más asesorías
        $chartInstLabels  = $porInst->keys()->toArray();   // Nombres de los instructores para etiquetas del eje Y
        $chartInstCounts  = $porInst->values()->toArray(); // Conteos de asesorías por instructor para valores del eje X

        // Datos para la gráfica de estado (doughnut chart)
        $chartEstadosLabels = ['Solicitada','Programada','Realizada','Cancelada']; // Etiquetas de los 4 estados posibles de una asesoría
        $chartEstadosCounts = [$solicitadas,$programadas,$realizadas,$canceladas]; // Valores numéricos de cada estado calculados arriba

        return view('controlecp::administrador.reportes_asesorias', compact( // Renderiza la vista del reporte de asesorías
            'filtros','fichas','instructores',                                 // Datos para los selectores del formulario de filtros
            'asesoriasMock','total','solicitadas','programadas','realizadas','canceladas','sinAsignar', // Datos del listado e indicadores
            'chartCatLabels','chartCatCounts',                                // Datos para la gráfica de categorías
            'chartInstLabels','chartInstCounts',                              // Datos para la gráfica de instructores
            'chartEstadosLabels','chartEstadosCounts'                         // Datos para la gráfica de estados
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-005 · REPORTE DE PQRS
    // Lista consolidada por tipo, estado, instructor y fechas con gráficas.
    // ══════════════════════════════════════════════════════════════════════

    public function pqrs(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/pqrs
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica que sea administrador ECP

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: verifica presencia de ambas fechas
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida coherencia del rango
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna al formulario con error
        }

        $filtros      = $request->only(['id_tipo_pqrs','estado','id_instructor','fecha_desde','fecha_hasta']); // Extrae los filtros válidos del request
        $tipos        = TipoPqrs::all();                         // Carga todos los tipos de PQRS para el selector del formulario
        $instructores = $this->porRol('controlecp.instructor');  // Lista de instructores para el filtro de responsable (HU-011)

        // Construcción de la consulta con eager loading y filtros dinámicos
        $query = Pqrs::with(['tipo','aprendiz','instructorDestino','respuestas']); // Precarga relaciones necesarias para la vista

        if (!empty($filtros['id_tipo_pqrs']))  // Si el usuario filtró por tipo de PQRS
            $query->where('id_tipo_pqrs', $filtros['id_tipo_pqrs']); // Filtra por tipo: Petición, Queja, Reclamo, etc.

        if (!empty($filtros['estado']))        // Si el usuario filtró por estado
            $query->where('estado', $filtros['estado']); // Filtra por: Pendiente, En trámite, Resuelto o Cerrado

        if (!empty($filtros['id_instructor'])) {  // HU-011: si el usuario filtró por responsable
            if ($filtros['id_instructor'] === 'admin') // Si el responsable es la administración general (sin instructor)
                $query->whereNull('id_usuario_instructor_destino'); // PQRS sin instructor asignado = dirigidas a Administración
            else
                $query->where('id_usuario_instructor_destino', $filtros['id_instructor']); // PQRS asignadas a ese instructor específico
        }

        if (!empty($filtros['fecha_desde']))   // Si hay fecha inicial
            $query->whereDate('fecha_radicacion', '>=', $filtros['fecha_desde']); // Filtra PQRS radicadas desde esa fecha

        if (!empty($filtros['fecha_hasta']))   // Si hay fecha final
            $query->whereDate('fecha_radicacion', '<=', $filtros['fecha_hasta']); // Filtra PQRS radicadas hasta esa fecha

        $pqrs           = $query->orderByDesc('fecha_radicacion')->get(); // Ejecuta la consulta ordenando por fecha de radicación descendente
        $total          = $pqrs->count();                                  // Total de PQRS que coinciden con los filtros aplicados

        // HU-012: indicadores de gestión para los KPIs del reporte
        $pendientes     = $pqrs->where('estado','Pendiente')->count();     // PQRS sin atender — requieren acción inmediata
        $enTramite      = $pqrs->filter(fn($p) => in_array($p->estado,['En trámite','En tramite']))->count(); // PQRS en proceso de resolución (acepta con y sin tilde)
        $resueltas      = $pqrs->filter(fn($p) => in_array($p->estado,['Resuelto','Resuelta','Cerrado','Cerrada']))->count(); // PQRS resueltas o cerradas
        $tasaResolucion = $total > 0 ? round(($resueltas/$total)*100,1) : 0; // Tasa de resolución: porcentaje de PQRS resueltas sobre el total

        // Datos para la gráfica de distribución por tipo de solicitud (doughnut chart)
        $reporteTiposLabels = $tipos->pluck('nombre')->toArray(); // Nombres de cada tipo de PQRS para las etiquetas de la gráfica
        $reporteTiposCounts = $tipos->map(fn($t) => $pqrs->where('id_tipo_pqrs',$t->id_tipo_pqrs)->count())->toArray(); // Conteo de PQRS por cada tipo

        // Datos para la gráfica de estado de gestión (doughnut chart con total en el centro)
        $reporteEstadosLabels = ['Pendiente','En trámite','Resuelta']; // Etiquetas de los 3 estados para la gráfica
        $reporteEstadosCounts = [$pendientes,$enTramite,$resueltas];   // Valores de cada estado calculados arriba

        // Datos para la gráfica de asignación por responsable (bar chart horizontal)
        $reporteInstructoresLabels = ['Administración'];                                              // Primera barra: PQRS dirigidas a Administración general
        $reporteInstructoresCounts = [$pqrs->whereNull('id_usuario_instructor_destino')->count()];   // Conteo de PQRS sin instructor asignado
        foreach ($instructores as $inst) {                                                            // Itera cada instructor registrado
            $cnt = $pqrs->where('id_usuario_instructor_destino',$inst->id)->count();                  // Cuenta PQRS asignadas a ese instructor
            if ($cnt > 0) {                                                                           // Solo agrega si tiene al menos una PQRS asignada
                $reporteInstructoresLabels[] = $inst->nickname ?? $inst->name;                       // Agrega el nombre del instructor como etiqueta
                $reporteInstructoresCounts[] = $cnt;                                                 // Agrega el conteo como valor de la barra
            }
        }

        return view('controlecp::administrador.reportes_pqrs', compact( // Renderiza la vista del reporte de PQRS
            'filtros','tipos','instructores','pqrs',                     // Datos para filtros y listado principal
            'total','pendientes','enTramite','resueltas','tasaResolucion', // Indicadores de gestión (HU-012)
            'reporteTiposLabels','reporteTiposCounts',                   // Datos para la gráfica de tipos
            'reporteEstadosLabels','reporteEstadosCounts',               // Datos para la gráfica de estados
            'reporteInstructoresLabels','reporteInstructoresCounts'      // Datos para la gráfica de responsables
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-006 · REPORTE DE CERTIFICACIÓN
    // Muestra por ficha, momento, responsable y estado los certificados emitidos.
    // Incluye indicador de fichas con los 3 momentos completados para SOFIA Plus.
    // ══════════════════════════════════════════════════════════════════════

    public function certificacion(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/certificacion
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica permisos de administrador

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: presencia de ambas fechas
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida coherencia del rango
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna con error de validación
        }

        $filtros      = $request->only(['ficha','momento_id','responsable_id','fecha_desde','fecha_hasta','estado']); // Extrae filtros del request
        $fichas       = Course::select('id','code')->orderBy('code')->get(); // Lista de fichas para el selector
        $responsables = $this->porRol('controlecp.admin');                   // HU-011: lista de admins que pueden certificar

        // Construcción de la consulta con filtros dinámicos
        $query = Certificado::with(['course.program','responsable.person']); // Precarga ficha, programa y datos del responsable

        if (!empty($filtros['ficha']))          // Si se filtró por ficha
            $query->whereHas('course', fn($q) => $q->where('code','like','%'.$filtros['ficha'].'%')); // Búsqueda parcial por código de ficha

        if (!empty($filtros['momento_id']))     // Si se filtró por momento pedagógico
            $query->where('momento_id', $filtros['momento_id']); // Filtra certificados del momento 1, 2 o 3

        if (!empty($filtros['responsable_id'])) // HU-011: si se filtró por responsable/administrador
            $query->where('certificado_por', $filtros['responsable_id']); // Muestra solo certificados emitidos por ese admin

        if (!empty($filtros['estado']))         // Si se filtró por estado del certificado
            $query->where('estado', $filtros['estado']); // CERTIFICADA o ANULADA

        if (!empty($filtros['fecha_desde']))    // Si hay fecha inicial
            $query->whereDate('fecha_certificacion', '>=', $filtros['fecha_desde']); // Filtra certificados emitidos desde esa fecha

        if (!empty($filtros['fecha_hasta']))    // Si hay fecha final
            $query->whereDate('fecha_certificacion', '<=', $filtros['fecha_hasta']); // Filtra certificados emitidos hasta esa fecha

        $certificados    = $query->orderByDesc('fecha_certificacion')->get(); // Ejecuta la consulta ordenando del certificado más reciente al más antiguo
        $total           = $certificados->count();                             // Total de certificados que coinciden con los filtros

        // HU-012: indicadores de certificación para los KPIs del reporte
        $completadas     = $certificados->where('estado','CERTIFICADA')->count(); // Total de momentos efectivamente certificados
        $pendientes      = $total - $completadas;                                 // Momentos registrados pero aún sin certificar
        $anuladas        = $certificados->where('estado','ANULADA')->count();     // Certificados que fueron anulados después de emitirse
        $fichasCompletas = 0; // Contador de fichas con los 3 momentos certificados — se incrementa en el map siguiente

        // Agrupación por ficha para la pestaña "Por Ficha" y el indicador de fichas completas
        $porFicha = $certificados->groupBy('course_id')->map(function ($certs) use (&$fichasCompletas) { // Agrupa por ID de ficha; pasa $fichasCompletas por referencia
            $momentosCert = $certs->where('estado','CERTIFICADA')->pluck('momento_id')->unique()->count(); // Cuenta momentos únicos certificados para esa ficha
            $completa     = $momentosCert >= 3; // Una ficha está completa cuando tiene los 3 momentos certificados
            if ($completa) $fichasCompletas++;  // Incrementa el contador global de fichas completamente certificadas
            $resp = $certs->first()->responsable; // Obtiene el objeto User del responsable que certificó
            return [
                'ficha'         => $certs->first()->course?->code ?? 'N/A',  // Código de la ficha certificada
                'programa'      => $certs->first()->course?->program?->name ?? 'N/A', // Nombre del programa de formación
                'momentos_cert' => $momentosCert,                             // Cantidad de momentos certificados (0, 1, 2 o 3)
                'completa'      => $completa,                                 // Booleano: true si los 3 momentos están certificados
                'responsable'   => $resp?->person?->first_name               // Si tiene datos de persona, muestra nombre completo
                    ? $resp->person->first_name.' '.$resp->person->first_last_name
                    : ($resp?->nickname ?? 'N/A'),                           // Si no, usa el nickname del usuario
                'ultima_fecha'  => $certs->max('fecha_certificacion'),       // Fecha del último certificado emitido para esa ficha
            ];
        })->values(); // Reindexa el array para obtener índices consecutivos

        // Datos para la gráfica de certificados por momento (bar chart)
        $chartMomentosLabels = ['Momento 1','Momento 2','Momento 3']; // Etiquetas de los 3 momentos para el eje X de la gráfica
        $chartMomentosCounts = [                                        // Cantidad de certificados emitidos por cada momento
            $certificados->where('estado','CERTIFICADA')->where('momento_id',1)->count(), // Certificados del Momento 1 (Autoconciencia)
            $certificados->where('estado','CERTIFICADA')->where('momento_id',2)->count(), // Certificados del Momento 2 (Convivencia)
            $certificados->where('estado','CERTIFICADA')->where('momento_id',3)->count(), // Certificados del Momento 3 (Resolución de conflictos)
        ];

        // Datos para la gráfica de estado general (doughnut chart con total en el centro)
        $chartEstadosLabels = ['Certificadas','Pendientes','Anuladas']; // Etiquetas de los 3 estados para la gráfica
        $chartEstadosCounts = [$completadas,$pendientes,$anuladas];     // Valores calculados para cada segmento

        return view('controlecp::administrador.reportes_certificacion', compact( // Renderiza la vista del reporte de certificación
            'filtros','fichas','responsables','certificados',                     // Datos para filtros y listado principal
            'total','completadas','pendientes','anuladas','fichasCompletas',      // Indicadores de gestión (HU-012)
            'porFicha','chartMomentosLabels','chartMomentosCounts',              // Agrupación por ficha y datos gráfica momentos
            'chartEstadosLabels','chartEstadosCounts'                            // Datos para la gráfica de estados
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-007 · REPORTE DE INVENTARIO
    // Semáforo de disponibilidad, movimientos, novedades y alertas
    // de devoluciones incompletas con quién registró cada movimiento.
    // ══════════════════════════════════════════════════════════════════════

    public function inventario(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/inventario
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica permisos de administrador

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: presencia de ambas fechas
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida coherencia del rango de fechas
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna con error de validación
        }

        $filtros    = $request->only(['categoria_id','estado_fisico','fecha_desde','fecha_hasta','id_usuario']); // Extrae filtros del request
        $categorias = CategoriaInventario::orderBy('nombre')->get(); // Lista de categorías de inventario para el selector de filtros
        $usuarios   = User::orderBy('nickname')->get();              // Lista de todos los usuarios para el filtro de responsable (HU-011)

        // Consulta de recursos del inventario con filtros aplicados
        $queryRec = Inventario::with(['categoria','fotos'])->where('estado','!=','Inactivo'); // Solo recursos activos; precarga categoría y fotos
        if (!empty($filtros['categoria_id']))  // Si se filtró por categoría
            $queryRec->where('id_categoria_inventario', $filtros['categoria_id']); // Solo recursos de esa categoría
        if (!empty($filtros['estado_fisico'])) // Si se filtró por estado físico del recurso
            $queryRec->where('estado_fisico', $filtros['estado_fisico']); // Bueno, Regular o Crítico
        $recursos = $queryRec->get(); // Ejecuta la consulta y obtiene todos los recursos que cumplen los filtros

        // Consulta de movimientos del inventario con filtros de fecha y usuario
        $queryMov = MovimientoInventario::with(['inventario','usuario']); // Precarga el recurso y el usuario de cada movimiento
        if (!empty($filtros['fecha_desde']))   // Si hay fecha inicial para movimientos
            $queryMov->whereDate('fecha', '>=', $filtros['fecha_desde']); // Movimientos desde esa fecha
        if (!empty($filtros['fecha_hasta']))   // Si hay fecha final para movimientos
            $queryMov->whereDate('fecha', '<=', $filtros['fecha_hasta']); // Movimientos hasta esa fecha
        if (!empty($filtros['id_usuario']))    // HU-011: si se filtró por usuario responsable
            $queryMov->where('id_usuario', $filtros['id_usuario']); // Solo movimientos registrados por ese usuario
        $movimientos = $queryMov->orderByDesc('fecha')->get(); // Ejecuta la consulta ordenando del más reciente al más antiguo

        // Consulta de novedades del inventario con filtros de fecha
        $queryNov = RecursoNovedad::with(['inventario','usuario']); // Precarga el recurso y el usuario de cada novedad
        if (!empty($filtros['fecha_desde']))   // Si hay fecha inicial para novedades
            $queryNov->whereDate('fecha', '>=', $filtros['fecha_desde']); // Novedades desde esa fecha
        if (!empty($filtros['fecha_hasta']))   // Si hay fecha final para novedades
            $queryNov->whereDate('fecha', '<=', $filtros['fecha_hasta']); // Novedades hasta esa fecha
        $novedades = $queryNov->orderByDesc('fecha')->get(); // Ejecuta ordenando de la más reciente a la más antigua

        // Detección de alertas: entregas cuya suma de devoluciones es menor a lo entregado
        $alertas = MovimientoInventario::with(['inventario','usuario']) // Precarga recurso y usuario para mostrar en la pestaña de alertas
            ->where('tipo','Entrega')    // Solo analiza movimientos de tipo Entrega (no Devolución ni Baja)
            ->get()                       // Carga todas las entregas para evaluar en PHP
            ->filter(function ($entrega) { // Filtra en memoria las entregas con devolución incompleta
                $devuelto = MovimientoInventario::where('id_inventario', $entrega->id_inventario) // Suma todas las devoluciones del mismo recurso
                    ->where('tipo','Devolución') // Solo movimientos de tipo Devolución
                    ->sum('cantidad');            // Suma total de unidades devueltas para ese recurso
                return $entrega->cantidad > $devuelto; // Alerta si se entregó más de lo que se ha devuelto
            })
            ->values()  // Reindexa para obtener índices consecutivos (0, 1, 2...)
            ->take(50); // Limita a 50 alertas para no sobrecargar la vista

        // HU-012: Cálculo de indicadores del semáforo de disponibilidad
        $total             = $recursos->count();                                        // Total de recursos activos en el inventario
        $verdeCount        = $recursos->filter(fn($r) => $r->semaforo === 'verde')->count();     // Recursos con buena disponibilidad (semáforo verde)
        $amarilloCount     = $recursos->filter(fn($r) => $r->semaforo === 'amarillo')->count();  // Recursos con disponibilidad moderada (semáforo amarillo)
        $rojoCount         = $recursos->filter(fn($r) => $r->semaforo === 'rojo')->count();      // Recursos en nivel crítico de disponibilidad (semáforo rojo)
        $entregasCount     = $movimientos->where('tipo','Entrega')->count();            // Total de movimientos de tipo Entrega en el periodo
        $devolucionesCount = $movimientos->where('tipo','Devolución')->count();         // Total de movimientos de tipo Devolución en el periodo
        $cambioEstadoCount = $movimientos->where('tipo','CambioEstado')->count();       // Total de cambios de estado físico registrados
        $bajasCount        = $movimientos->where('tipo','Baja')->count();               // Total de recursos dados de baja en el periodo

        // Datos para la gráfica de distribución por categoría (pie chart)
        $categoriasLabels = $categorias->pluck('nombre')->toArray(); // Nombres de las categorías para las etiquetas de la gráfica
        $categoriasCounts = $categorias->map(fn($c) =>               // Para cada categoría, cuenta cuántos recursos activos tiene
            $recursos->where('id_categoria_inventario', $c->id_categoria_inventario)->count() // Filtro en memoria — rápido porque los recursos ya están cargados
        )->toArray(); // Convierte la colección a array simple para pasarlo a Chart.js

        return view('controlecp::administrador.reportes_inventario', compact( // Renderiza la vista del reporte de inventario
            'filtros','categorias','usuarios','recursos','movimientos','novedades','alertas', // Datos para filtros, pestañas y tabla de alertas
            'total','verdeCount','amarilloCount','rojoCount',                                // Indicadores del semáforo (HU-012)
            'entregasCount','devolucionesCount','cambioEstadoCount','bajasCount',            // Conteos de tipos de movimiento para la gráfica
            'categoriasLabels','categoriasCounts'                                           // Datos para la gráfica de distribución por categoría
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // HU-008 · REPORTE DE POE (Plan Operativo de Ejecución)
    // Versiones por ficha, quién subió cada cambio y cumplimiento.
    // Usa dataset estructurado local listo para conectar con la API SICEFA.
    // ══════════════════════════════════════════════════════════════════════

    public function poe(Request $request) // Método público — accesible desde GET /control-ecp/administrador/reportes/poe
    {
        if ($redirect = $this->verificarAcceso()) return $redirect; // Verifica permisos de administrador

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta') // HU-010: presencia de ambas fechas
            && $request->fecha_desde > $request->fecha_hasta) {                 // Valida coherencia del rango
            return back()->withErrors(['fecha' => 'Rango de fechas inválido.'])->withInput(); // Retorna con error de validación
        }

        $filtros  = $request->only(['ficha','lider_id','estado','fecha_desde','fecha_hasta']); // Extrae filtros del request
        $fichas   = Course::select('id','code')->orderBy('code')->get(); // Lista de fichas para el selector de filtros
        $lideres  = $this->porRol('controlecp.admin');                   // HU-011: lista de administradores que pueden ser líderes del POE

        // Obtiene el dataset del POE: local ahora, API SICEFA cuando esté disponible
        // Para conectar SICEFA: reemplazar esta línea con Http::withToken($token)->get($url.'/poe')
        $registrosPoe = $this->getMockPoe($filtros, $fichas); // Genera el dataset estructurado del POE aplicando los filtros

        // HU-012: Calcular indicadores de cumplimiento del POE
        $col             = collect($registrosPoe);                            // Convierte el array en colección Laravel
        $total           = $col->count();                                     // Total de registros/versiones del POE que coinciden con los filtros
        $cumplidas       = $col->where('estado','Cumplida')->count();         // Subidas programadas que se realizaron en el plazo establecido
        $pendientes      = $col->where('estado','Pendiente')->count();        // Subidas programadas que aún no se han realizado
        $atrasadas       = $col->where('estado','Atrasada')->count();         // Subidas que no se realizaron en el plazo programado
        $pctCumplimiento = $total > 0 ? round(($cumplidas/$total)*100,1) : 0; // Porcentaje de cumplimiento del POE con 1 decimal

        // Agrupación por ficha para la pestaña "Versiones por Ficha"
        $porFicha = $col->groupBy('ficha_code')->map(function ($items) { // Agrupa los registros del POE por código de ficha
            return [
                'ficha'         => $items->first()['ficha_code'] ?? 'N/A',  // Código de la ficha formativa
                'programa'      => $items->first()['programa']   ?? 'N/A',  // Nombre del programa de formación
                'total_subidas' => $items->count(),                          // Total de versiones subidas del POE para esa ficha
                'ultima_version'=> $items->last()['version']     ?? '1.0',  // Número de la última versión subida, ej: 3.0
                'lider'         => $items->last()['lider']       ?? 'N/A',  // Nombre del líder que realizó la última subida
                'ultima_subida' => $items->last()['fecha_subida']?? null,   // Fecha de la última subida del POE para esa ficha
                'cumplidas'     => $items->where('estado','Cumplida')->count(),  // Versiones cumplidas de esa ficha
                'pendientes'    => $items->where('estado','Pendiente')->count(), // Versiones pendientes de esa ficha
                'atrasadas'     => $items->where('estado','Atrasada')->count(),  // Versiones atrasadas de esa ficha
            ];
        })->values(); // Reindexa para obtener índices consecutivos

        // Datos para la gráfica de cumplimiento general (doughnut chart)
        $chartEstadosLabels = ['Cumplidas','Pendientes','Atrasadas']; // Etiquetas de los 3 estados del POE para la gráfica
        $chartEstadosCounts = [$cumplidas,$pendientes,$atrasadas];    // Valores numéricos calculados para cada estado

        // Datos para la gráfica de subidas por ficha (bar chart)
        $chartFichasLabels  = $porFicha->take(8)->pluck('ficha')->toArray();        // Top 8 fichas para el eje X de la gráfica
        $chartFichasCounts  = $porFicha->take(8)->pluck('total_subidas')->toArray(); // Cantidad de versiones subidas por cada ficha

        return view('controlecp::administrador.reportes_poe', compact( // Renderiza la vista del reporte del POE
            'filtros','fichas','lideres','registrosPoe',                // Datos para filtros y bitácora completa
            'total','cumplidas','pendientes','atrasadas','pctCumplimiento', // Indicadores de cumplimiento (HU-012)
            'porFicha','chartEstadosLabels','chartEstadosCounts',       // Agrupación por ficha y datos gráfica de estados
            'chartFichasLabels','chartFichasCounts'                     // Datos para la gráfica de subidas por ficha
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // MOCKS ESTRUCTURADOS — datasets con contrato de integración SICEFA
    // Cuando el ERP esté disponible, reemplazar el contenido de cada método
    // con una llamada Http::withToken($token)->get($url.'/endpoint')
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Genera el dataset de asesorías con la misma estructura que devolvería
     * el endpoint /asesorias de la API REST Central ERP SENA Empresa / SICEFA.
     * Cada campo del array corresponde a un campo real del contrato de integración.
     *
     * @param array $filtros  Filtros seleccionados por el usuario (ficha, estado, fechas)
     * @return array          Lista de asesorías como array asociativo
     */
    private function getMockAsesorias(array $filtros): array // Método privado que genera datos de asesorías estructurados para el reporte
    {
        $fichasCodes  = Course::select('code')->orderBy('code')->pluck('code')->toArray(); // Obtiene los códigos de fichas reales de la BD para poblar el dataset
        $instructores = $this->porRol('controlecp.instructor')->take(8);                   // Obtiene hasta 8 instructores reales para asignar a las asesorías

        if (empty($fichasCodes)) $fichasCodes = ['2977588','2977589','2977590']; // Si no hay fichas en BD, usa códigos de ejemplo para no romper el dataset

        $categorias = ['Académica','Convivencia','Proyecto de vida','Salud mental','Emprendimiento','Orientación vocacional']; // Catálogo de categorías de asesorías ECP
        $estados    = ['Solicitada','Programada','Realizada','Cancelada'];                                                      // Catálogo de estados posibles de una asesoría
        $instNames  = $instructores->map(fn($i) =>                              // Construye array con los nombres completos de los instructores
            $i->person ? $i->person->first_name.' '.$i->person->first_last_name : ($i->nickname ?? 'Instructor') // Usa nombre completo si tiene Person, sino usa nickname
        )->toArray();
        if (empty($instNames)) $instNames = ['Instructor SENA']; // Si no hay instructores registrados, usa nombre genérico

        $data = [];                          // Array vacío que acumulará los registros de asesorías
        $base = strtotime('2026-01-01');     // Timestamp base para generar fechas distribuidas a lo largo del año 2026

        for ($i = 1; $i <= 40; $i++) {                                   // Genera 40 registros de asesorías ficticios con datos reales del sistema
            $fichaCode = $fichasCodes[array_rand($fichasCodes)];         // Selecciona aleatoriamente una ficha real de la BD
            $categoria = $categorias[$i % count($categorias)];           // Asigna categoría de forma rotatoria para distribución uniforme
            $estado    = $estados[$i % count($estados)];                 // Asigna estado de forma rotatoria para distribución uniforme
            $instName  = $instNames[$i % count($instNames)];             // Asigna instructor de forma rotatoria
            $fecha     = date('Y-m-d', $base + ($i * 6 * 24 * 3600));   // Genera fecha incrementando 6 días por cada registro

            // Aplica los filtros seleccionados por el usuario al dataset
            if (!empty($filtros['ficha']) && strpos($fichaCode, $filtros['ficha']) === false) continue; // Omite si la ficha no coincide
            if (!empty($filtros['estado']) && $estado !== $filtros['estado']) continue;                  // Omite si el estado no coincide
            if (!empty($filtros['fecha_desde']) && $fecha < $filtros['fecha_desde']) continue;           // Omite si es anterior a la fecha inicial
            if (!empty($filtros['fecha_hasta']) && $fecha > $filtros['fecha_hasta']) continue;           // Omite si es posterior a la fecha final

            $data[] = [                                                                                           // Agrega el registro al dataset con la estructura del contrato SICEFA
                'id'                => $i,                                                                        // Identificador local del registro de asesoría
                'ficha_code'        => $fichaCode,                                                                // Código real de la ficha formativa asociada
                'categoria'         => $categoria,                                                                // Categoría temática de la asesoría
                'instructor_nombre' => $instName,                                                                 // Nombre completo del instructor que la realiza
                'instructor_id'     => $instructores[$i % max(1, $instructores->count())]?->id ?? null,          // ID del usuario instructor para el filtro (HU-011)
                'fecha'             => $fecha,                                                                    // Fecha en formato YYYY-MM-DD (estándar ISO 8601)
                'estado'            => $estado,                                                                   // Estado actual de la asesoría
                'descripcion'       => "Asesoría {$categoria} — Ficha {$fichaCode} — Sesión #{$i}.",             // Descripción textual de la sesión de asesoría
                'total_asistentes'  => rand(5, 32),                                                               // Cantidad de aprendices que participaron en la asesoría
                'satisfaccion'      => round(rand(35, 50) / 10, 1),                                              // Calificación de satisfacción promedio (3.5 a 5.0)
                'duracion_min'      => rand(30, 120),                                                             // Duración de la sesión en minutos
                'sicefa_id'         => 'ASESORIA-ECP-2026-'.str_pad($i, 4, '0', STR_PAD_LEFT),                  // ID único con formato SICEFA para la integración ERP
                'fuente'            => 'local',                                                                   // Origen del dato: 'local' o 'api_central' cuando se conecte SICEFA
            ];
        }

        return $data; // Retorna el array completo de asesorías listo para usar en la vista del reporte
    }

    /**
     * Genera el dataset del POE con la misma estructura que devolvería
     * el endpoint /poe de la API REST Central ERP SENA Empresa / SICEFA.
     * Cada campo del array corresponde a un campo real del contrato de integración.
     *
     * @param array  $filtros  Filtros seleccionados por el usuario
     * @param mixed  $fichas   Colección de fichas del sistema para poblar el dataset
     * @return array           Lista de registros del POE como array asociativo
     */
    private function getMockPoe(array $filtros, $fichas): array // Método privado que genera datos del POE estructurados para el reporte
    {
        $fichasList = $fichas->take(8);                  // Toma hasta 8 fichas para generar versiones del POE
        $estados    = ['Cumplida','Pendiente','Atrasada']; // Estados posibles de una subida del POE
        $lideres    = $this->porRol('controlecp.admin'); // Obtiene los administradores reales para asignarlos como líderes
        $liderNames = $lideres->map(fn($u) =>            // Construye array con nombres completos de los líderes
            $u->person ? $u->person->first_name.' '.$u->person->first_last_name : ($u->nickname ?? 'Coordinador ECP')
        )->toArray();
        if (empty($liderNames)) $liderNames = ['Coordinador ECP']; // Si no hay administradores, usa nombre genérico

        $data = []; // Array vacío que acumulará los registros del POE
        $idx  = 0;  // Índice global para identificar cada registro y generar fechas

        foreach ($fichasList as $ficha) {              // Itera cada ficha para generar sus versiones del POE
            $versiones = rand(1, 4);                   // Genera entre 1 y 4 versiones por ficha (simula el historial de actualizaciones)
            for ($v = 1; $v <= $versiones; $v++) {     // Itera cada versión de esa ficha
                $estado = $estados[$idx % count($estados)];                            // Asigna estado de forma rotatoria
                $fecha  = date('Y-m-d', strtotime('2026-01-01') + ($idx * 12 * 24 * 3600)); // Genera fecha incrementando 12 días por cada registro
                $lider  = $liderNames[$idx % count($liderNames)];                      // Asigna líder de forma rotatoria

                // Aplica los filtros seleccionados por el usuario al dataset
                if (!empty($filtros['ficha']) && strpos($ficha->code ?? '', $filtros['ficha']) === false) { $idx++; continue; } // Omite si el código de ficha no coincide
                if (!empty($filtros['estado']) && $estado !== $filtros['estado']) { $idx++; continue; }                         // Omite si el estado no coincide
                if (!empty($filtros['fecha_desde']) && $fecha < $filtros['fecha_desde']) { $idx++; continue; }                  // Omite si es anterior a la fecha inicial
                if (!empty($filtros['fecha_hasta']) && $fecha > $filtros['fecha_hasta']) { $idx++; continue; }                  // Omite si es posterior a la fecha final

                $data[] = [                                                                                       // Agrega el registro al dataset con la estructura del contrato SICEFA
                    'id'               => $idx + 1,                                                               // Identificador secuencial del registro
                    'ficha_code'       => $ficha->code ?? 'N/A',                                                  // Código real de la ficha formativa
                    'programa'         => 'Programa de Formación',                                                // Nombre del programa (se completará con dato real de la API)
                    'version'          => "{$v}.0",                                                               // Número de versión del POE, ej: 1.0, 2.0, 3.0
                    'lider'            => $lider,                                                                  // Nombre del líder responsable de la subida
                    'fecha_subida'     => $fecha,                                                                  // Fecha en que se realizó la subida del POE
                    'descripcion'      => "Actualización v{$v}.0 del POE — Ficha {$ficha->code}. Cambio en cronograma de actividades ECP.", // Descripción del cambio realizado
                    'estado'           => $estado,                                                                 // Estado del cumplimiento de esa subida programada
                    'pct_cumplimiento' => match ($estado) {                                                        // Porcentaje de cumplimiento según el estado
                        'Cumplida'  => 100,          // 100% si la subida se realizó en el plazo
                        'Pendiente' => rand(20, 60), // Entre 20% y 60% si está en progreso
                        default     => rand(0, 30),  // Entre 0% y 30% si está atrasada
                    },
                    'sicefa_id'        => 'POE-ECP-2026-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT), // ID único con formato SICEFA para la integración ERP
                    'fuente'           => 'local', // Origen del dato: 'local' o 'api_central' cuando se conecte SICEFA
                ];
                $idx++; // Incrementa el índice global para el siguiente registro
            }
        }

        return $data; // Retorna el array completo de registros del POE listo para usar en la vista del reporte
    }
}
