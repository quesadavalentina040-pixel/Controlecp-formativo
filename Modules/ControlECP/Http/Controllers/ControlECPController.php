<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\Apprentice;
use Modules\ControlECP\Entities\ActividadEcp;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\Asistencia;
use Modules\ControlECP\Entities\BloqueHistoria;
use Modules\ControlECP\Entities\CalificacionEcp;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\CronogramaHistorial;
use Modules\ControlECP\Rules\NotWeekendOrColombianHoliday;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\EvidenciaEcp;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\Material;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Entities\Pqr;
use Modules\ControlECP\Entities\Poe;
use Modules\ControlECP\Entities\Pqrs;
use Modules\ControlECP\Entities\TipoPqrs;
use Modules\ControlECP\Services\CertificacionNotificacionService;

class ControlECPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    /**
     * Muestra el inicio del rol Administrador.
     * Protegido: solo usuarios con rol controlecp.admin (o superadmin) pueden entrar,
     * aunque conozcan la URL directamente.
     */
    public function inicioAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Administrador de Control ECP.');
        }

        // Datos reales de usuarios sincronizados e institucionales
        $totalUsuariosEcp = User::whereHas('roles', function ($q) {
            $q->where('slug', 'like', 'controlecp%');
        })->count();

        $totalUsuariosSistema = User::count();

        $conteoAdmins = User::whereHas('roles', function ($q) {
            $q->where('slug', 'controlecp.admin');
        })->count();

        $conteoInstructores = User::whereHas('roles', function ($q) {
            $q->where('slug', 'controlecp.instructor');
        })->count();

        $conteoAprendices = User::whereHas('roles', function ($q) {
            $q->where('slug', 'controlecp.apprentice');
        })->count();

        // Datos reales de Fichas (Cursos) de SICEFA / SENA Empresa
        $totalFichas = Course::count();
        $totalFichasActivas = Course::where(function ($q) {
            $q->where('status', 'Activo')
              ->orWhereNull('status');
        })->count();

        // Actividades programadas para hoy (Cultura de Paz)
        $actividadesHoy = 3;

        $tablaPqrs = Schema::hasTable((new Pqr())->getTable());
        $solicitudesPendientes = $tablaPqrs
            ? Pqr::where('estado', 'Pendiente')->count()
            : 0;
        $totalPqrs = $tablaPqrs ? Pqr::count() : 0;

        if (Schema::hasTable((new Material())->getTable())) {
            $materialesCriticos = Material::where('estado', 'Agotado')
                ->orWhereColumn('cantidad', '<=', 'stock_minimo')
                ->get();
            $recursosCriticos = $materialesCriticos->count();
        } else {
            $materialesCriticos = collect();
            $recursosCriticos = 0;
        }

        // Fichas prÃ³ximas a completar los 3 momentos
        $fichasProximasVencer = min(4, $totalFichasActivas);

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.inicio', compact(
            'usuario',
            'totalUsuariosEcp',
            'totalUsuariosSistema',
            'conteoAdmins',
            'conteoInstructores',
            'conteoAprendices',
            'totalFichas',
            'totalFichasActivas',
            'actividadesHoy',
            'solicitudesPendientes',
            'totalPqrs',
            'recursosCriticos',
            'materialesCriticos',
            'fichasProximasVencer',
        ));
    }

    /**
     * Muestra la vista de gestiÃ³n de Usuarios del Administrador (IntegraciÃ³n vÃ­a API).
     */
    public function usuariosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestiÃ³n de usuarios de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.usuario');
    }

    /**
     * Muestra la vista de consulta de Fichas del Administrador (IntegraciÃ³n vÃ­a API).
     */
    public function fichasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestiÃ³n de fichas de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.fichas');
    }

    /**
     * Muestra la vista de Momentos del Administrador.
     */
    public function momentosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a Momentos de Control ECP.');
        }

        // Fichas activas del centro para vincularlas a los momentos
        $fichasActivas = Course::with(['program'])
            ->withCount('apprentices')
            ->where(function ($q) {
                $q->where('status', 'Activo')->orWhereNull('status');
            })
            ->get();

        $totalFichasActivas = $fichasActivas->count();
        $totalAprendicesActivos = Apprentice::count();

        // DistribuciÃ³n pedagÃ³gica de fichas en la ruta de paz
        $fichasMomento1 = $fichasActivas->slice(0, (int) ceil($totalFichasActivas * 0.45))->values();
        $fichasMomento2 = $fichasActivas->slice((int) ceil($totalFichasActivas * 0.45), (int) ceil($totalFichasActivas * 0.35))->values();
        $fichasMomento3 = $fichasActivas->slice((int) ceil($totalFichasActivas * 0.80))->values();

        if ($fichasMomento3->isEmpty() && $fichasActivas->isNotEmpty()) {
            $fichasMomento3 = $fichasActivas->take(4);
        }
        if ($fichasMomento2->isEmpty() && $fichasActivas->isNotEmpty()) {
            $fichasMomento2 = $fichasActivas->take(6);
        }
        if ($fichasMomento1->isEmpty() && $fichasActivas->isNotEmpty()) {
            $fichasMomento1 = $fichasActivas->take(8);
        }

        $statsMomentos = [
            'total_fichas' => $totalFichasActivas,
            'total_aprendices' => $totalAprendicesActivos,
            'm1_fichas' => $fichasMomento1->count(),
            'm1_aprendices' => $fichasMomento1->sum('apprentices_count'),
            'm2_fichas' => $fichasMomento2->count(),
            'm2_aprendices' => $fichasMomento2->sum('apprentices_count'),
            'm3_fichas' => $fichasMomento3->count(),
            'm3_aprendices' => $fichasMomento3->sum('apprentices_count'),
        ];

        // Todas las fichas activas para el buscador/consultor de avance
        $catalogoFichas = $fichasActivas->take(30);

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.momentos', compact(
            'statsMomentos',
            'fichasMomento1',
            'fichasMomento2',
            'fichasMomento3',
            'catalogoFichas'
        ));
    }

    /**
     * Muestra la vista de Actividades del Administrador.
     * Incluye catÃ¡logo completo de 18 actividades pedagÃ³gicas ECP con metadata enriquecida
     * para futura integraciÃ³n con REST API del ERP SENA Empresa / SICEFA.
     */
    public function actividadesAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a Actividades de Control ECP.');
        }

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // CATÃLOGO OFICIAL DE 18 ACTIVIDADES PEDAGÃ“GICAS â€” ESCUELA CULTURA DE PAZ
        // Fuente: Ruta Formativa Control ECP â€“ Centro La Angostura (SENA)
        // Cuando se conecte la API REST Central del ERP SENA Empresa / SICEFA,
        // estos datos serÃ¡n reemplazados por: GET /api/v1/controlecp/actividades
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $actividades = [
            // â”€â”€ MOMENTO 1: AUTOCONCIENCIA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            [
                'id'            => 1,
                'numero'        => '01',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Reconocimiento y GestiÃ³n Emocional',
                'objetivo'      => 'Identificar la rueda de emociones y tÃ©cnicas de autorregulaciÃ³n emocional en el contexto formativo del aprendiz SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Taller vivencial con rueda de emociones de Plutchik, ejercicios de respiraciÃ³n guiada, diario emocional y retroalimentaciÃ³n grupal.',
                'competencias'  => ['Autoconciencia emocional', 'AutorregulaciÃ³n', 'Inteligencia emocional'],
                'materiales'    => ['Hojas de trabajo â€” Rueda de Emociones', 'Cartulina A1', 'Marcadores de colores', 'Diario reflexivo personal'],
                'tags'          => ['emociones', 'autorregulaciÃ³n', 'mindfulness'],
            ],
            [
                'id'            => 2,
                'numero'        => '02',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Taller de AutorreflexiÃ³n y Autoconcepto',
                'objetivo'      => 'Explorar la percepciÃ³n personal, fortalezas individuales y Ã¡reas de mejora desde una mirada formativa constructiva.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Cuestionario de autoevaluaciÃ³n de fortalezas (VIA), conversatorio en triadas, mapa de identidad personal y socializaciÃ³n plenaria.',
                'competencias'  => ['Autoreconocimiento', 'Identidad personal', 'Proyecto de vida'],
                'materiales'    => ['Test de Fortalezas VIA impreso', 'Papel craft', 'LÃ¡pices de colores', 'Cinta adhesiva'],
                'tags'          => ['identidad', 'fortalezas', 'autoestima'],
            ],
            [
                'id'            => 3,
                'numero'        => '03',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Proyecto de Vida y SueÃ±os Integrales',
                'objetivo'      => 'DiseÃ±ar metas personales y profesionales a corto, mediano y largo plazo alineadas con el perfil SENA.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'LÃ­nea de vida individual, construcciÃ³n de metas SMART, tablero de visiÃ³n personal, presentaciÃ³n ante el grupo.',
                'competencias'  => ['PlanificaciÃ³n personal', 'VisiÃ³n de futuro', 'Emprendimiento'],
                'materiales'    => ['Revistas para recorte', 'Cartulina de colores', 'Tijeras y pegante', 'Post-it 3 colores'],
                'tags'          => ['metas', 'proyecto de vida', 'visiÃ³n'],
            ],
            [
                'id'            => 4,
                'numero'        => '04',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'IdentificaciÃ³n de Fortalezas y Oportunidades',
                'objetivo'      => 'Construir la Matriz DOFA personal aplicada al contexto laboral y formativo del entorno SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'AplicaciÃ³n guiada de la Matriz DOFA personal, anÃ¡lisis de entorno con el instructor, plan de mejora individual.',
                'competencias'  => ['AnÃ¡lisis situacional', 'Pensamiento crÃ­tico', 'AutogestiÃ³n'],
                'materiales'    => ['Plantilla DOFA impresa', 'BolÃ­grafos', 'Marcadores', 'Tablero o papelÃ³grafo'],
                'tags'          => ['DOFA', 'anÃ¡lisis', 'estrategia personal'],
            ],
            [
                'id'            => 5,
                'numero'        => '05',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'ExpresiÃ³n Asertiva de Sentimientos',
                'objetivo'      => 'Desarrollar la capacidad de comunicar emociones de manera clara, respetuosa y asertiva en el entorno formativo.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Role-playing de situaciones cotidianas del SENA, tÃ©cnica del "Mensaje Yo", juego de roles asertivo, retroalimentaciÃ³n grupal.',
                'competencias'  => ['ComunicaciÃ³n asertiva', 'ExpresiÃ³n emocional', 'Relaciones interpersonales'],
                'materiales'    => ['Tarjetas de situaciones', 'BolÃ­grafos', 'Hojas de reflexiÃ³n'],
                'tags'          => ['asertividad', 'comunicaciÃ³n', 'emociones'],
            ],
            [
                'id'            => 6,
                'numero'        => '06',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Mapeo Corporal y Autocuidado',
                'objetivo'      => 'Reconocer somatizaciones del estrÃ©s y promover hÃ¡bitos de salud mental y autocuidado integral.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Ejercicio de mapeo corporal en silueta humana, identificaciÃ³n de tensiones, tÃ©cnicas de relajaciÃ³n progresiva de Jacobson.',
                'competencias'  => ['Salud mental', 'Autocuidado', 'Bienestar integral'],
                'materiales'    => ['Siluetas humanas impresas A3', 'Crayolas', 'MÃºsica relajante', 'Alfombras o colchonetas'],
                'tags'          => ['salud mental', 'cuerpo', 'relajaciÃ³n'],
            ],
            [
                'id'            => 7,
                'numero'        => '07',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Control del EstrÃ©s e Impulsos',
                'objetivo'      => 'Aplicar tÃ©cnicas de respiraciÃ³n consciente y mindfulness bÃ¡sico para el manejo del estrÃ©s en contextos acadÃ©micos y laborales.',
                'duracion'      => '1.5 Horas',
                'duracion_min'  => 90,
                'metodologia'   => 'Ejercicio 4-7-8 de respiraciÃ³n, body-scan guiado, mindfulness de 5 minutos, registro de disparadores emocionales.',
                'competencias'  => ['GestiÃ³n del estrÃ©s', 'AtenciÃ³n plena', 'RegulaciÃ³n impulsiva'],
                'materiales'    => ['AplicaciÃ³n de meditaciÃ³n (opcional)', 'Hoja de registro de disparadores', 'Espacio tranquilo'],
                'tags'          => ['estrÃ©s', 'mindfulness', 'respiraciÃ³n'],
            ],
            [
                'id'            => 8,
                'numero'        => '08',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Historia Personal y RaÃ­ces Culturales',
                'objetivo'      => 'Valorar el origen cultural, la historia familiar y su impacto en la construcciÃ³n de la identidad del aprendiz.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Ãrbol genealÃ³gico familiar, lÃ­nea del tiempo personal con hitos culturales, socializaciÃ³n en cÃ­rculo de aprendizaje.',
                'competencias'  => ['Identidad cultural', 'Diversidad', 'Sentido de pertenencia'],
                'materiales'    => ['Plantilla Ã¡rbol genealÃ³gico', 'Fotos (opcional)', 'Marcadores', 'Cartulina'],
                'tags'          => ['cultura', 'historia', 'identidad'],
            ],
            [
                'id'            => 9,
                'numero'        => '09',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Principios, Valores y Ã‰tica Personal',
                'objetivo'      => 'Alinear los valores personales con el entorno laboral, institucional y el perfil del egresado SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'ClasificaciÃ³n de valores con tarjetas (card sorting), dilemas Ã©ticos situacionales, construcciÃ³n de cÃ³digo personal de Ã©tica.',
                'competencias'  => ['Ã‰tica profesional', 'Valores institucionales', 'Integridad'],
                'materiales'    => ['Tarjetas de valores', 'Dilemas Ã©ticos impresos', 'Hoja de cÃ³digo personal'],
                'tags'          => ['Ã©tica', 'valores', 'integridad'],
            ],
            [
                'id'            => 10,
                'numero'        => '10',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Compromiso Conmigo Mismo',
                'objetivo'      => 'Formalizar la carta de compromiso personal y el plan de acciÃ³n formativo para la ruta ECP.',
                'duracion'      => '1 Hora',
                'duracion_min'  => 60,
                'metodologia'   => 'RedacciÃ³n individual de carta de compromiso, firma simbÃ³lica grupal, lectura en voz alta y archivado en portafolio de evidencias.',
                'competencias'  => ['Responsabilidad', 'Compromiso formativo', 'AutonomÃ­a'],
                'materiales'    => ['Formato oficial Carta de Compromiso ECP', 'BolÃ­grafo', 'Portafolio de evidencias', 'Sello institucional'],
                'tags'          => ['compromiso', 'plan de acciÃ³n', 'cierre'],
            ],

            // â”€â”€ MOMENTO 2: CONVIVENCIA â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            [
                'id'            => 11,
                'numero'        => '01',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'CÃ­rculos de DiÃ¡logo y EmpatÃ­a',
                'objetivo'      => 'Desarrollar la escucha activa, la empatÃ­a y el diÃ¡logo respetuoso en la comunidad formativa del Centro.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'CÃ­rculo de paz restaurativa (talking piece), preguntas reflexivas guiadas, escucha activa en parejas, cierre con compromisos grupales.',
                'competencias'  => ['Escucha activa', 'EmpatÃ­a', 'DiÃ¡logo constructivo'],
                'materiales'    => ['Objeto hablador (talking piece)', 'Preguntas de cÃ­rculo impresas', 'Sillas en disposiciÃ³n circular'],
                'tags'          => ['diÃ¡logo', 'empatÃ­a', 'cÃ­rculo restaurativo'],
            ],
            [
                'id'            => 12,
                'numero'        => '02',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'DinÃ¡micas de Trabajo Colaborativo',
                'objetivo'      => 'Fortalecer la confianza grupal y las habilidades de trabajo en equipo en contextos de alta diversidad.',
                'duracion'      => '2.5 Horas',
                'duracion_min'  => 150,
                'metodologia'   => 'Retos colaborativos (torre de espaguetis, puente de papel), anÃ¡lisis de roles en equipo (Belbin), reflexiÃ³n sobre dinÃ¡micas grupales.',
                'competencias'  => ['Trabajo en equipo', 'Liderazgo colaborativo', 'GestiÃ³n de roles'],
                'materiales'    => ['Espaguetis, malvaviscos, cuerda, cinta adhesiva', 'Test de Belbin simplificado', 'PapelÃ³grafo'],
                'tags'          => ['equipo', 'colaboraciÃ³n', 'confianza'],
            ],
            [
                'id'            => 13,
                'numero'        => '03',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'ComunicaciÃ³n No Violenta (CNV)',
                'objetivo'      => 'Aplicar el lenguaje integrador de Rosenberg para transformar patrones comunicativos agresivos o pasivos.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Estudio del modelo CNV (ObservaciÃ³n-Sentimiento-Necesidad-PeticiÃ³n), prÃ¡ctica con situaciones reales del aula, video-foro CNV.',
                'competencias'  => ['CNV', 'ComunicaciÃ³n empÃ¡tica', 'ResoluciÃ³n verbal'],
                'materiales'    => ['GuÃ­a CNV de Rosenberg (resumen)', 'Tarjetas de situaciones', 'Proyector para video', 'Hojas de prÃ¡ctica'],
                'tags'          => ['CNV', 'Rosenberg', 'comunicaciÃ³n empÃ¡tica'],
            ],
            [
                'id'            => 14,
                'numero'        => '04',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'Acuerdos de Convivencia y Respeto',
                'objetivo'      => 'Construir colectivamente normas de convivencia grupales, pactos de respeto y protocolo de manejo de conflictos internos.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Lluvia de ideas sobre normas de convivencia, construcciÃ³n participativa del reglamento interno, firma del pacto grupal.',
                'competencias'  => ['Democracia participativa', 'Respeto', 'Normas sociales'],
                'materiales'    => ['Papel kraft grande', 'Marcadores de colores', 'Post-it', 'Formato de pacto de convivencia'],
                'tags'          => ['convivencia', 'pacto', 'normas grupales'],
            ],

            // â”€â”€ MOMENTO 3: RESOLUCIÃ“N â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            [
                'id'            => 15,
                'numero'        => '01',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: ResoluciÃ³n',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Taller de MediaciÃ³n PacÃ­fica',
                'objetivo'      => 'Desarrollar herramientas prÃ¡cticas para intervenir como mediadores neutrales en conflictos del entorno formativo.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Estudio del proceso de mediaciÃ³n escolar, prÃ¡ctica de fases de mediaciÃ³n (pre-mediaciÃ³n, sesiÃ³n conjunta, acuerdo), simulaciÃ³n supervisada.',
                'competencias'  => ['MediaciÃ³n', 'Neutralidad', 'FacilitaciÃ³n de diÃ¡logos'],
                'materiales'    => ['Manual de MediaciÃ³n Escolar', 'Casos prÃ¡cticos impresos', 'Acta de acuerdo de mediaciÃ³n', 'Sala privada de mediaciÃ³n'],
                'tags'          => ['mediaciÃ³n', 'conflictos', 'facilitaciÃ³n'],
            ],
            [
                'id'            => 16,
                'numero'        => '02',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: ResoluciÃ³n',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'NegociaciÃ³n y Estrategias Ganar-Ganar',
                'objetivo'      => 'Aplicar tÃ©cnicas de negociaciÃ³n basada en intereses (Harvard) para llegar a acuerdos sostenibles y equitativos.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'MÃ³dulo teÃ³rico del MÃ©todo Harvard de NegociaciÃ³n, simulaciÃ³n de negociaciÃ³n en dÃ­adas, anÃ¡lisis de estrategias, retroalimentaciÃ³n.',
                'competencias'  => ['NegociaciÃ³n', 'Pensamiento estratÃ©gico', 'Consenso'],
                'materiales'    => ['Resumen MÃ©todo Harvard', 'Roles de negociaciÃ³n impresos', 'CronÃ³metro', 'Acta de acuerdo'],
                'tags'          => ['negociaciÃ³n', 'Harvard', 'ganar-ganar'],
            ],
            [
                'id'            => 17,
                'numero'        => '03',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: ResoluciÃ³n',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Estudio de Casos y Juego de Roles',
                'objetivo'      => 'Simular situaciones reales de tensiÃ³n en entornos laborales del SENA para desarrollar respuestas asertivas y pacÃ­ficas.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'AnÃ¡lisis de casos reales anonimizados, asignaciÃ³n de roles (vÃ­ctima, agresor, mediador, testigo), dramatizaciÃ³n y reflexiÃ³n grupal.',
                'competencias'  => ['AnÃ¡lisis de situaciones', 'EmpatÃ­a situacional', 'Toma de decisiones'],
                'materiales'    => ['Fichas de caso impresa', 'Tarjetas de rol', 'Espacio amplio para actuaciÃ³n', 'Hoja de reflexiÃ³n post-rol'],
                'tags'          => ['role-playing', 'casos reales', 'dramatizaciÃ³n'],
            ],
            [
                'id'            => 18,
                'numero'        => '04',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: ResoluciÃ³n',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Plan de AcciÃ³n y Pactos de Paz',
                'objetivo'      => 'Cerrar el proceso formativo ECP con evaluaciÃ³n integral, elaboraciÃ³n del plan de paz personal y certificaciÃ³n.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'EvaluaciÃ³n reflexiva del proceso ECP, construcciÃ³n del plan de paz personal, ceremonia de compromisos grupales y entrega de reconocimiento.',
                'competencias'  => ['EvaluaciÃ³n formativa', 'Compromiso social', 'Paz y reconciliaciÃ³n'],
                'materiales'    => ['Formato Plan de Paz ECP', 'Diplomas/reconocimientos', 'Listado de verificaciÃ³n ECP', 'CÃ¡mara fotogrÃ¡fica (ceremonia)'],
                'tags'          => ['plan de paz', 'cierre', 'certificaciÃ³n', 'evaluaciÃ³n'],
            ],
        ];

        // â”€â”€ ESTADÃSTICAS GENERALES DE ACTIVIDADES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $totalActividades  = count($actividades);
        $totalHoras        = collect($actividades)->sum('duracion_min') / 60; // en horas
        $totalMomentos     = 3;

        // Fichas activas del sistema (real desde DB)
        try {
            $fichasActivas = Cache::store('file')->remember('act_fichas_activas', 600, function () {
                return /* Devuelve el resultado de este mï¿½todo. */ Course::where('status', 'Activo')->count();
            });
        } catch (\Exception $e) {
            $fichasActivas = Course::where('status', 'Activo')->count();
        }

        // Aprendices activos (real desde DB)
        try {
            $aprendicesActivos = Cache::store('file')->remember('act_aprendices_activos', 600, function () {
                return /* Devuelve el resultado de este mï¿½todo. */ Apprentice::whereHas('course', function ($q) {
                    $q->where('status', 'Activo');
                })->count();
            });
        } catch (\Exception $e) {
            $aprendicesActivos = 0;
        }

        $statsActividades = [
            'total'            => $totalActividades,
            'total_horas'      => number_format($totalHoras, 1),
            'total_momentos'   => $totalMomentos,
            'fichas_activas'   => $fichasActivas,
            'aprendices'       => $aprendicesActivos,
            'momento1_count'   => collect($actividades)->where('momento_id', 1)->count(),
            'momento2_count'   => collect($actividades)->where('momento_id', 2)->count(),
            'momento3_count'   => collect($actividades)->where('momento_id', 3)->count(),
            'horas_m1'         => number_format(collect($actividades)->where('momento_id', 1)->sum('duracion_min') / 60, 1),
            'horas_m2'         => number_format(collect($actividades)->where('momento_id', 2)->sum('duracion_min') / 60, 1),
            'horas_m3'         => number_format(collect($actividades)->where('momento_id', 3)->sum('duracion_min') / 60, 1),
        ];

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.actividades', compact('actividades', 'statsActividades'));
    }

    /**
     * Muestra la vista de Cronograma del Administrador.
     */
    public function cronogramaAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al Cronograma de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.cronograma');
    }

    /**
     * Muestra la vista de consulta y estado de Asistencia del Administrador (Flujo dependiente del Rol Instructor).
     */
    public function asistenciaAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestiÃ³n de asistencia de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.asistencia');
    }

    /**
     * Muestra la vista de consulta y gestiÃ³n de AsesorÃ­as del Administrador.
     */
    public function asesoriasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestiÃ³n de asesorÃ­as de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.asesorias');
    }

    /**
     * Muestra la vista de consulta y emisiÃ³n de Certificados del Administrador.
     */
    public function certificadosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestiÃ³n de certificados de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.certificados');
    }


        /**
     * Muestra la vista de Historias dentro del Repositorio del Administrador.
     */
    public function repositorioHistoria()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.repositorio.historia');
    }

    /**
     * Muestra la vista de Encuestas dentro del Repositorio del Administrador.
     */
    public function repositorioEncuesta()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.repositorio.encuesta');
    }

    /**
     * Muestra la vista de TabulaciÃ³n dentro del Repositorio del Administrador.
     */
    public function repositorioTabulacion()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.repositorio.tabulacion');
    }

    /**
     * Muestra la vista de POE dentro del Repositorio del Administrador.
     */
    public function repositorioPoe()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.repositorio.poe');
    }
    /**
     * Muestra el inicio del rol Instructor.
     */
    public function inicioInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $sesiones = $this->cronogramasDelInstructor()->whereDate('fecha', '>=', today())
            ->orderBy('fecha')->limit(5)->get();
        $resumen = [
            'fichas' => $this->cronogramasDelInstructor()->distinct()->count('id_ficha'),
            'sesiones_hoy' => $this->cronogramasDelInstructor()->whereDate('fecha', today())->count(),
            'asesorias' => Asesoria::where('id_usuario_instructor', Auth::id())->whereNotIn('estado', ['Cancelada', 'Rechazada'])->count(),
            'asistencias' => Asistencia::whereIn('cronograma_id', $this->cronogramasDelInstructor()->select('id_cronograma'))->count(),
        ];

        return view('controlecp::instructor.inicio', compact('sesiones', 'resumen'));
    }

    /**
     * MÃ©todos auxiliares para las secciones del Instructor
     */
    private function verificarAccesoInstructor()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.instructor')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al Ã¡rea de Instructor.');
        }
        return /* Indica que no hay informaciï¿½n para devolver. */ null;
    }

    private function cronogramasDelInstructor()
    {
        return Cronograma::with(['actividad.momento', 'ficha.program', 'asistencia'])
            ->where('id_instructor', Auth::id());
    }

    public function cronogramaInstructor(Request $request)
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;

        $request->validate([
            'mes' => 'nullable|date_format:Y-m',
            'semaforo' => 'nullable|in:rojo,amarillo,verde,sin_color,finalizadas,todos',
        ]);

        $cronogramasQuery = $this->cronogramasDelInstructor();
        if ($request->filled('search')) {
            $termino = trim($request->input('search'));
            $cronogramasQuery->where(function ($query) use ($termino) {
                $query->where('lugar', 'like', "%{$termino}%")
                    ->orWhereHas('actividad', function ($actividadQuery) use ($termino) {
                        $actividadQuery->where('nombre', 'like', "%{$termino}%")
                            ->orWhereHas('momento', function ($momentoQuery) use ($termino) {
                                $momentoQuery->where('nombre', 'like', "%{$termino}%");
                            });
                    })
                    ->orWhereHas('ficha', function ($fichaQuery) use ($termino) {
                        $fichaQuery->where('code', 'like', "%{$termino}%")
                            ->orWhereHas('program', function ($programQuery) use ($termino) {
                                $programQuery->where('name', 'like', "%{$termino}%");
                            });
                    });
            });
        }
        if ($request->filled('ficha_id')) {
            $cronogramasQuery->where('id_ficha', $request->input('ficha_id'));
        } elseif ($request->filled('ficha')) {
            $ficha = trim($request->input('ficha'));
            $cronogramasQuery->whereHas('ficha', function ($query) use ($ficha) {
                $query->where('code', 'like', "%{$ficha}%");
            });
        }
        if ($request->filled('momento_id')) {
            $momentoId = $request->input('momento_id');
            $cronogramasQuery->whereHas('actividad', function ($query) use ($momentoId) {
                $query->where('momento_id', $momentoId);
            });
        }
        if ($request->filled('estado')) {
            $cronogramasQuery->where('estado', $request->input('estado'));
        }
        if ($request->filled('fecha_desde')) {
            $cronogramasQuery->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $cronogramasQuery->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('semaforo') && $request->input('semaforo') !== 'todos') {
            $semaforo = $request->input('semaforo');
            if ($semaforo === 'sin_color') {
                $cronogramasQuery->where('estado', 'Pospuesta');
            } elseif ($semaforo === 'finalizadas') {
                $cronogramasQuery->where('estado', 'Finalizada');
            } else {
                $cronogramasQuery->where('estado', 'Pendiente');
                if ($semaforo === 'rojo') {
                    $cronogramasQuery->whereDate('fecha', '<=', now()->addDays(2)->toDateString());
                } elseif ($semaforo === 'amarillo') {
                    $cronogramasQuery->whereBetween('fecha', [
                        now()->addDays(3)->toDateString(),
                        now()->addDays(7)->toDateString(),
                    ]);
                } else {
                    $cronogramasQuery->whereDate('fecha', '>', now()->addDays(7)->toDateString());
                }
            }
        }

        $stats = [
            'rojo' => 0,
            'amarillo' => 0,
            'verde' => 0,
            'pospuestas' => 0,
            'finalizadas' => 0,
        ];
        foreach ($this->cronogramasDelInstructor()->get(['estado', 'fecha']) as $cronograma) {
            if ($cronograma->estado === 'Pospuesta') {
                $stats['pospuestas']++;
            } elseif ($cronograma->estado === 'Finalizada') {
                $stats['finalizadas']++;
            } elseif ($cronograma->semaforo) {
                $stats[$cronograma->semaforo]++;
            }
        }

        $mes = Carbon::createFromFormat('!Y-m', $request->input('mes', now()->format('Y-m')));
        $inicioCalendario = $mes->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $finCalendario = $mes->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $semanas = [[]];
        for ($dia = $inicioCalendario->copy(); $dia->lte($finCalendario); $dia->addDay()) {
            $semanas[count($semanas) - 1][] = $dia->copy();
            if ($dia->isSunday()) {
                $semanas[] = [];
            }
        }
        array_pop($semanas);

        $calendarEvents = (clone $cronogramasQuery)
            ->with(['recursos.inventario'])
            ->whereBetween('fecha', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy(fn ($cronograma) => $cronograma->fecha->toDateString());
        $cronogramas = (clone $cronogramasQuery)
            ->with(['recursos.inventario'])
            ->orderByDesc('fecha')
            ->orderBy('hora_inicio')
            ->paginate(10)
            ->withQueryString();
        $fichas = Course::whereIn('id', $this->cronogramasDelInstructor()->select('id_ficha'))
            ->orderBy('code')->get();
        $momentos = Momento::whereIn(
            'id',
            ActividadEcp::whereIn('id', $this->cronogramasDelInstructor()->select('id_actividad'))
                ->select('momento_id')
        )->orderBy('orden')->get();

        return view('controlecp::instructor.cronograma', compact(
            'cronogramas',
            'stats',
            'fichas',
            'momentos',
            'mes',
            'semanas',
            'calendarEvents'
        ));
    }

    public function posponerCronogramaInstructor(Request $request, int $id)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $cronograma = $this->cronogramasDelInstructor()
            ->with('actividad')
            ->where('id_cronograma', $id)
            ->firstOrFail();

        if (in_array($cronograma->estado, ['Finalizada', 'Cancelada'], true)) {
            return back()->withErrors([
                'cronograma' => 'No puedes posponer una actividad finalizada o cancelada.',
            ]);
        }

        $validated = $request->validate([
            'nueva_fecha' => ['required', 'date', 'after_or_equal:today', new NotWeekendOrColombianHoliday()],
            'motivo' => 'required|string|min:10|max:200',
        ], [
            'nueva_fecha.required' => 'La nueva fecha es obligatoria.',
            'nueva_fecha.after_or_equal' => 'La nueva fecha debe ser igual o posterior a hoy.',
            'motivo.required' => 'El motivo de posposición es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'motivo.max' => 'El motivo no puede superar los 200 caracteres.',
        ]);

        $conflicto = Cronograma::where('id_instructor', Auth::id())
            ->whereDate('fecha', $validated['nueva_fecha'])
            ->where('id_cronograma', '!=', $cronograma->id_cronograma)
            ->where('estado', '!=', 'Cancelada')
            ->where('hora_inicio', '<', $cronograma->hora_fin)
            ->where('hora_fin', '>', $cronograma->hora_inicio)
            ->exists();

        if ($conflicto) {
            return back()->withInput()->withErrors([
                'nueva_fecha' => 'Ya tienes otra actividad asignada en ese horario para la fecha seleccionada.',
            ]);
        }

        DB::transaction(function () use ($cronograma, $validated) {
            $fechaAnterior = $cronograma->fecha->copy();
            $cronograma->update([
                'fecha_original' => $cronograma->fecha_original ?: $fechaAnterior,
                'fecha' => $validated['nueva_fecha'],
                'estado' => 'Pospuesta',
                'motivo_posposicion' => trim($validated['motivo']),
            ]);

            CronogramaHistorial::create([
                'id_cronograma' => $cronograma->id_cronograma,
                'id_usuario_autor' => Auth::id(),
                'accion' => 'posponer',
                'fecha_anterior' => $fechaAnterior,
                'fecha_nueva' => $validated['nueva_fecha'],
                'motivo' => trim($validated['motivo']),
                'fecha' => now(),
            ]);

            $fechaAnteriorTexto = $fechaAnterior->format('d/m/Y');
            $fechaNuevaTexto = Carbon::parse($validated['nueva_fecha'])->format('d/m/Y');
            $nombreActividad = $cronograma->actividad?->nombre ?? 'Actividad ECP';
            $mensaje = "La actividad '{$nombreActividad}' de la ficha {$cronograma->id_ficha} fue pospuesta del {$fechaAnteriorTexto} al {$fechaNuevaTexto}. Motivo: " . trim($validated['motivo']);

            $administradores = User::whereHas('roles', function ($query) {
                $query->where('slug', 'controlecp.admin')
                    ->orWhere('full_access', 'Si');
            })->pluck('id');
            $aprendices = User::whereIn(
                'person_id',
                Apprentice::where('course_id', $cronograma->id_ficha)->select('person_id')
            )->pluck('id');

            foreach ($administradores->merge($aprendices)->unique()->reject(fn ($userId) => $userId === Auth::id()) as $receptorId) {
                Notificacion::crear(
                    idReceptor: (int) $receptorId,
                    idCausante: (int) Auth::id(),
                    titulo: 'Actividad pospuesta por el instructor',
                    mensaje: $mensaje,
                    referenciaTipo: 'cronograma',
                    referenciaId: (int) $cronograma->id_cronograma,
                    claveEvento: 'cronograma_pospuesto_' . $cronograma->id_cronograma . '_' . now()->timestamp . '_' . $receptorId
                );
            }
        });

        return redirect()->back()->with('success', 'Actividad pospuesta correctamente. Se notificó a administración y a los aprendices de la ficha.');
    }

    public function momentosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        $momentos = Momento::with('actividades')
            ->withCount('actividades')
            ->orderBy('orden')
            ->get();
        return view('controlecp::instructor.momentos', compact('momentos'));
    }

    public function actividadesInstructor(Request $request)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:150'],
            'momento_id' => ['nullable', 'integer', 'exists:controlecp_momentos,id'],
        ]);

        $termino = trim((string) $request->input('buscar'));
        $actividadesAsignadas = fn () => $this->cronogramasDelInstructor()
            ->select('id_actividad')
            ->distinct();
        $filtrarActividades = function ($query) use ($termino, $actividadesAsignadas) {
            $query->whereIn('id', $actividadesAsignadas())
                ->when($termino !== '', function ($actividadQuery) use ($termino) {
                    $actividadQuery->where(function ($searchQuery) use ($termino) {
                        $searchQuery->where('nombre', 'like', "%{$termino}%")
                            ->orWhere('numero', 'like', "%{$termino}%")
                            ->orWhere('objetivo', 'like', "%{$termino}%");
                    });
                });
        };
        $momentos = Momento::with(['actividades' => function ($query) use ($filtrarActividades) {
            $filtrarActividades($query);
            $query->with('encuesta')->withCount(['evidencias', 'calificaciones']);
        }])
            ->whereHas('actividades', $filtrarActividades)
            ->when($request->filled('momento_id'), fn ($query) => $query->whereKey($request->input('momento_id')))
            ->orderBy('orden')
            ->get();

        $momentos->each(function ($momento) {
            $momento->setAttribute('actividades_count', $momento->actividades->count());
        });
        $actividades = $momentos->flatMap(fn ($momento) => $momento->actividades);
        $stats = [
            'total' => $actividades->count(),
            'activas' => $actividades->where('estado', 'Activo')->count(),
            'inactivas' => $actividades->where('estado', 'Inactivo')->count(),
            'momentos' => $momentos->count(),
        ];
        $actividadesPorPagina = 4;
        $momentos->each(function ($momento) use ($request, $actividadesPorPagina) {
            $pageName = 'momento_' . $momento->id . '_page';
            $page = LengthAwarePaginator::resolveCurrentPage($pageName);
            $momentActivities = $momento->actividades;
            $momento->setAttribute('actividades_paginadas', new LengthAwarePaginator(
                $momentActivities->forPage($page, $actividadesPorPagina)->values(),
                $momentActivities->count(),
                $actividadesPorPagina,
                $page,
                ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $pageName]
            ));
        });
        $cronogramasPorActividad = $this->cronogramasDelInstructor()
            ->with(['actividad', 'ficha.program', 'asistencia.detalles'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('id_actividad');
        $encuestasActivas = Encuesta::with('usuario')
            ->where('estado', 'Activa')
            ->orderBy('nombre')
            ->get(['id_encuesta', 'id_usuario', 'nombre']);

        return view('controlecp::instructor.actividades', compact(
            'momentos',
            'cronogramasPorActividad',
            'encuestasActivas',
            'stats'
        ));
    }

    private function actividadAsignadaInstructor(int $actividadId): ActividadEcp
    {
        abort_unless(Auth::user()?->hasRole('controlecp.instructor'), 403);

        $asignada = $this->cronogramasDelInstructor()
            ->where('id_actividad', $actividadId)
            ->exists();
        abort_unless($asignada, 404);

        return ActividadEcp::with('momento')->findOrFail($actividadId);
    }

    public function encuestaActividadInstructor(Request $request, int $actividadId)
    {
        $actividad = $this->actividadAsignadaInstructor($actividadId);
        $datos = $request->validate([
            'id_encuesta' => ['required', 'integer', 'exists:encuestas,id_encuesta'],
        ]);
        $encuesta = Encuesta::where('id_encuesta', $datos['id_encuesta'])
            ->where('estado', 'Activa')
            ->first();
        if (!$encuesta) {
            return response()->json([
                'success' => false,
                'message' => 'La encuesta seleccionada no está activa en el repositorio.',
            ], 422);
        }

        if ((int) $actividad->id_encuesta !== (int) $encuesta->id_encuesta
            && DB::table('encuesta_respuestas')
                ->join('cronograma', 'cronograma.id_cronograma', '=', 'encuesta_respuestas.id_cronograma')
                ->where('cronograma.id_actividad', $actividadId)
                ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cambiar la encuesta porque esta actividad ya tiene respuestas registradas.',
            ], 422);
        }

        $actividad->update(['id_encuesta' => $encuesta->id_encuesta]);
        return response()->json(['success' => true, 'message' => 'Encuesta asociada correctamente.']);
    }

    public function evidenciasActividadInstructor(int $actividadId)
    {
        $this->actividadAsignadaInstructor($actividadId);
        $fichasIds = $this->cronogramasDelInstructor()
            ->where('id_actividad', $actividadId)
            ->select('id_ficha');
        $evidencias = EvidenciaEcp::with(['ficha.program', 'registradoPor'])
            ->where('actividad_id', $actividadId)
            ->whereIn('course_id', $fichasIds)
            ->orderByDesc('fecha_registro')
            ->get()
            ->map(fn ($evidencia) => [
                'tipo' => $evidencia->tipo,
                'url' => $evidencia->url,
                'descripcion' => $evidencia->descripcion,
                'fecha' => $evidencia->fecha_registro?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A'),
                'ficha' => $evidencia->ficha?->code ?? '—',
                'registrado_por' => $evidencia->registradoPor?->full_name ?? '—',
            ]);

        return response()->json(['success' => true, 'evidencias' => $evidencias]);
    }

    public function subirEvidenciaActividadInstructor(Request $request, int $actividadId)
    {
        $actividad = $this->actividadAsignadaInstructor($actividadId);
        abort_unless($actividad->tiene_evidencia, 422, 'Esta actividad no tiene habilitado el componente de evidencia.');

        $datos = $request->validate([
            'course_id' => ['required', 'integer'],
            'tipo' => ['required', 'in:foto,video'],
            'archivo' => ['required_if:tipo,foto', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'enlace' => ['required_if:tipo,video', 'nullable', 'url', 'max:500'],
            'descripcion' => ['nullable', 'string', 'min:10', 'max:200'],
        ], [
            'archivo.mimes' => 'Solo se aceptan fotos JPG o PNG.',
            'enlace.url' => 'Ingresa un enlace de video válido.',
        ]);

        $cronograma = $this->cronogramasDelInstructor()
            ->where('id_actividad', $actividadId)
            ->where('id_ficha', $datos['course_id'])
            ->firstOrFail();
        $evidencia = new EvidenciaEcp([
            'actividad_id' => $actividadId,
            'course_id' => $cronograma->id_ficha,
            'user_id' => Auth::id(),
            'tipo' => $datos['tipo'],
            'origen' => 'actividad',
            'enlace' => $datos['enlace'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'fecha_registro' => now(),
        ]);
        if ($datos['tipo'] === 'foto') {
            $evidencia->archivo = $request->file('archivo')->store(
                'controlecp/evidencias/' . $actividadId,
                'public'
            );
        }
        $evidencia->save();

        return response()->json(['success' => true, 'message' => 'Evidencia registrada correctamente.']);
    }

    public function calificacionesActividadInstructor(int $actividadId)
    {
        $this->actividadAsignadaInstructor($actividadId);
        $fichasIds = $this->cronogramasDelInstructor()
            ->where('id_actividad', $actividadId)
            ->select('id_ficha');
        $calificaciones = CalificacionEcp::with(['aprendiz.person', 'ficha'])
            ->where('actividad_id', $actividadId)
            ->whereIn('course_id', $fichasIds)
            ->orderByDesc('created_at')
            ->get();
        $total = $calificaciones->count();
        $distribucion = [];
        for ($estrellas = 5; $estrellas >= 1; $estrellas--) {
            $cantidad = $calificaciones->where('estrellas', $estrellas)->count();
            $distribucion[] = [
                'estrellas' => $estrellas,
                'cantidad' => $cantidad,
                'pct' => $total > 0 ? round(($cantidad / $total) * 100) : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'total' => $total,
            'promedio' => round((float) ($calificaciones->avg('estrellas') ?? 0), 1),
            'distribucion' => $distribucion,
            'calificaciones' => $calificaciones->map(fn ($calificacion) => [
                'estrellas' => $calificacion->estrellas,
                'comentario' => $calificacion->comentario,
                'aprendiz' => $calificacion->aprendiz?->full_name ?? 'Aprendiz',
                'ficha' => $calificacion->ficha?->code ?? '—',
                'fecha' => $calificacion->created_at?->format('d/m/Y'),
            ]),
        ]);
    }

    public function registrarAsistenciaInstructor(Request $request, int $cronogramaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $cronograma = $this->cronogramasDelInstructor()->with('actividad')->findOrFail($cronogramaId);
        $editando = $request->input('modo') === 'editar';
        $asistenciaExistente = DB::table('controlecp_asistencias')
            ->where('cronograma_id', $cronogramaId)
            ->first();
        if ($asistenciaExistente && $this->usuariosAdministrativos()->whereKey($asistenciaExistente->registrado_por)
            ->exists()) {
            abort(404);
        }
        if ($editando && (!$asistenciaExistente || $asistenciaExistente->estado !== 'Registrada')) {
            throw ValidationException::withMessages(['asistencia' => 'La asistencia ya no está disponible para edición.']);
        }
        if (!$editando && $asistenciaExistente && $asistenciaExistente->estado === 'Registrada') {
            throw ValidationException::withMessages(['cronograma' => 'Esta actividad ya tiene asistencia registrada.']);
        }

        $evidenciasExistentes = $editando
            ? DB::table('controlecp_asistencia_detalles')
                ->where('asistencia_id', $asistenciaExistente->id)
                ->pluck('excusa_imagen', 'aprendiz_id')
            : collect();

        $datos = $request->validate([
            'modo' => ['nullable', 'in:editar'],
            'motivo' => [$editando ? 'required' : 'nullable', 'string', 'min:10', 'max:200'],
            'asistencia' => ['required', 'array'],
            'asistencia.*.presente' => ['required', 'in:0,1'],
            'asistencia.*.justificada' => ['required', 'in:0,1'],
            'asistencia.*.observacion' => ['nullable', 'string', 'max:1000'],
            'asistencia.*.excusa_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'evidencia_foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'evidencia_enlace' => ['nullable', 'url', 'max:500'],
        ], [
            'asistencia.*.excusa_imagen.image' => 'La evidencia de la excusa debe ser una imagen.',
            'asistencia.*.excusa_imagen.mimes' => 'La evidencia de la excusa debe estar en formato JPG o PNG.',
            'asistencia.*.excusa_imagen.max' => 'La evidencia de la excusa no puede superar 5 MB.',
        ]);

        foreach ($datos['asistencia'] as $aprendizId => $detalle) {
            $justificada = (string) ($detalle['justificada'] ?? '0') === '1';
            $presente = (string) ($detalle['presente'] ?? '0') === '1';
            $tieneImagen = $request->hasFile("asistencia.{$aprendizId}.excusa_imagen");
            $tieneEvidenciaExistente = !empty($evidenciasExistentes->get($aprendizId));
            if ($justificada && ($presente || empty(trim((string) ($detalle['observacion'] ?? ''))) || (!$tieneImagen && !$tieneEvidenciaExistente))) {
                throw ValidationException::withMessages([
                    'asistencia' => 'Para justificar una ausencia, marca al aprendiz como ausente, escribe el motivo y adjunta una imagen de soporte.',
                ]);
            }
        }
        if (!$editando && ($cronograma->estado === 'Finalizada' || $cronograma->estado === 'Cancelada')) {
            throw ValidationException::withMessages(['cronograma' => 'No se puede registrar asistencia para esta actividad.']);
        }
        if (!$editando && $cronograma->fecha?->isFuture()) {
            throw ValidationException::withMessages(['cronograma' => 'No se puede registrar asistencia antes de la fecha de la actividad.']);
        }
        $aprendices = Apprentice::where('course_id', $cronograma->id_ficha)->pluck('id')->all();
        $enviados = array_map('intval', array_keys($datos['asistencia']));
        if (array_diff($aprendices, $enviados) || array_diff($enviados, $aprendices)) {
            throw ValidationException::withMessages([
                'asistencia' => 'La lista de aprendices debe estar completa para registrar la asistencia.',
            ]);
        }

        DB::transaction(function () use ($request, $datos, $cronograma, $asistenciaExistente, $editando, $evidenciasExistentes) {
            $antes = $asistenciaExistente && $editando
                ? DB::table('controlecp_asistencia_detalles')
                    ->where('asistencia_id', $asistenciaExistente->id)
                    ->orderBy('aprendiz_id')
                    ->get(['aprendiz_id', 'presente', 'observacion'])
                    ->toArray()
                : null;
            if ($asistenciaExistente) {
                $asistenciaId = $asistenciaExistente->id;
                $actualizacion = [
                    'registrado_por' => Auth::id(),
                    'estado' => 'Registrada',
                    'motivo_anulacion' => null,
                    'updated_at' => now(),
                ];
                if (!$editando) $actualizacion['registrada_en'] = now();
                DB::table('controlecp_asistencias')->where('id', $asistenciaId)->update($actualizacion);
                DB::table('controlecp_asistencia_detalles')->where('asistencia_id', $asistenciaId)->delete();
            } else {
                $asistenciaId = DB::table('controlecp_asistencias')->insertGetId([
                    'cronograma_id' => $cronograma->id_cronograma,
                    'registrado_por' => Auth::id(),
                    'estado' => 'Registrada',
                    'registrada_en' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ($datos['asistencia'] as $aprendizId => $detalle) {
                $justificada = (string) ($detalle['justificada'] ?? '0') === '1';
                $excusaImagen = $request->hasFile("asistencia.{$aprendizId}.excusa_imagen")
                    ? $request->file("asistencia.{$aprendizId}.excusa_imagen")->store('controlecp/excusas', 'public')
                    : $evidenciasExistentes->get($aprendizId);

                DB::table('controlecp_asistencia_detalles')->insert([
                    'asistencia_id' => $asistenciaId,
                    'aprendiz_id' => $aprendizId,
                    'presente' => $detalle['presente'] === '1',
                    'justificada' => $justificada,
                    'observacion' => $detalle['observacion'] ?? null,
                    'excusa_imagen' => $excusaImagen,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if ($request->hasFile('evidencia_foto')) {
                EvidenciaEcp::create([
                    'actividad_id' => $cronograma->id_actividad,
                    'course_id' => $cronograma->id_ficha,
                    'user_id' => Auth::id(),
                    'tipo' => 'foto',
                    'origen' => 'asistencia',
                    'archivo' => $request->file('evidencia_foto')->store('controlecp/evidencias', 'public'),
                    'fecha_registro' => now(),
                ]);
            }
            if ($request->filled('evidencia_enlace')) {
                EvidenciaEcp::create([
                    'actividad_id' => $cronograma->id_actividad,
                    'course_id' => $cronograma->id_ficha,
                    'user_id' => Auth::id(),
                    'tipo' => 'video',
                    'origen' => 'asistencia',
                    'enlace' => $request->input('evidencia_enlace'),
                    'fecha_registro' => now(),
                ]);
            }
            DB::table('controlecp_asistencia_historial')->insert([
                'asistencia_id' => $asistenciaId,
                'usuario_id' => Auth::id(),
                'accion' => $editando ? 'Editada' : 'Registrada',
                'motivo' => $editando ? $datos['motivo'] : null,
                'datos' => $editando ? json_encode([
                    'antes' => $antes,
                    'despues' => $datos['asistencia'],
                ]) : json_encode(['despues' => $datos['asistencia']]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('controlecp.instructor.asistencia')
            ->with('success', $editando ? 'Asistencia actualizada correctamente.' : 'Asistencia registrada correctamente.');
    }

    public function anularAsistenciaInstructor(Request $request, int $asistenciaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:200'],
        ], ['motivo.required' => 'Debes indicar el motivo de la anulación.']);
        $asistencia = DB::table('controlecp_asistencias as a')
            ->join('cronograma as c', 'c.id_cronograma', '=', 'a.cronograma_id')
            ->where('a.id', $asistenciaId)
            ->where('c.id_instructor', Auth::id())
            ->where('a.estado', 'Registrada')
            ->whereNotIn('a.registrado_por', $this->usuariosAdministrativos()->select('id'))
            ->select('a.*')
            ->first();
        if (!$asistencia) {
            throw ValidationException::withMessages(['asistencia' => 'La asistencia ya está anulada o no pertenece a tus actividades.']);
        }

        $snapshot = DB::table('controlecp_asistencia_detalles')
            ->where('asistencia_id', $asistenciaId)
            ->orderBy('aprendiz_id')
            ->get(['aprendiz_id', 'presente', 'observacion'])
            ->toArray();
        DB::transaction(function () use ($asistenciaId, $datos, $snapshot) {
            DB::table('controlecp_asistencias')->where('id', $asistenciaId)->update([
                'estado' => 'Anulada',
                'motivo_anulacion' => $datos['motivo'],
                'updated_at' => now(),
            ]);
            DB::table('controlecp_asistencia_historial')->insert([
                'asistencia_id' => $asistenciaId,
                'usuario_id' => Auth::id(),
                'accion' => 'Anulada',
                'motivo' => $datos['motivo'],
                'datos' => json_encode(['antes' => $snapshot]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('controlecp.instructor.asistencia')
            ->with('success', 'Asistencia anulada correctamente.');
    }

    public function finalizarActividadInstructor(int $cronogramaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $cronograma = $this->cronogramasDelInstructor()
            ->with(['actividad', 'ficha'])
            ->findOrFail($cronogramaId);
        if (!in_array($cronograma->estado, ['Pendiente', 'En proceso'], true)) {
            throw ValidationException::withMessages(['cronograma' => 'Solo se pueden finalizar actividades pendientes o en proceso.']);
        }
        if (!DB::table('controlecp_asistencias')
            ->where('cronograma_id', $cronogramaId)
            ->where('estado', 'Registrada')
            ->whereNotIn('registrado_por', $this->usuariosAdministrativos()->select('id'))
            ->exists()) {
            throw ValidationException::withMessages(['cronograma' => 'Registra primero la asistencia para poder finalizar la actividad.']);
        }

        DB::transaction(function () use ($cronograma) {
            $cronograma->update([
                'estado' => 'Finalizada',
                'id_usuario_finalizador' => Auth::id(),
                'fecha_finalizacion' => now(),
            ]);
            CronogramaHistorial::create([
                'id_cronograma' => $cronograma->id_cronograma,
                'id_usuario_autor' => Auth::id(),
                'accion' => 'finalizar',
                'fecha' => now(),
            ]);

            $actividad = $cronograma->actividad;
            $mensaje = "La actividad '{$actividad?->nombre}' de la ficha {$cronograma->ficha?->code} fue finalizada.";
            $administradores = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.admin'))
                ->pluck('id');
            $aprendices = User::whereIn(
                'person_id',
                Apprentice::where('course_id', $cronograma->id_ficha)->select('person_id')
            )->pluck('id');

            foreach ($administradores->merge($aprendices)->unique()->reject(fn ($userId) => (int) $userId === (int) Auth::id()) as $receptorId) {
                Notificacion::crear(
                    idReceptor: (int) $receptorId,
                    idCausante: (int) Auth::id(),
                    titulo: 'Actividad finalizada',
                    mensaje: $mensaje,
                    referenciaTipo: 'cronograma',
                    referenciaId: (int) $cronograma->id_cronograma,
                    claveEvento: 'cronograma_finalizado_' . $cronograma->id_cronograma . '_' . $receptorId
                );
            }

            if ($actividad?->id_encuesta && Encuesta::where('id_encuesta', $actividad->id_encuesta)
                ->where('estado', 'Activa')
                ->exists()) {
                $aprendicesAsistentes = DB::table('controlecp_asistencia_detalles as detalle')
                    ->join('controlecp_asistencias as asistencia', 'asistencia.id', '=', 'detalle.asistencia_id')
                    ->join('apprentices', 'apprentices.id', '=', 'detalle.aprendiz_id')
                    ->join('users', 'users.person_id', '=', 'apprentices.person_id')
                    ->where('asistencia.cronograma_id', $cronograma->id_cronograma)
                    ->where('asistencia.estado', 'Registrada')
                    ->where('apprentices.course_id', $cronograma->id_ficha)
                    ->where('detalle.presente', true)
                    ->distinct()
                    ->pluck('users.id');

                foreach ($aprendicesAsistentes as $receptorId) {
                    Notificacion::crear(
                        idReceptor: (int) $receptorId,
                        idCausante: (int) Auth::id(),
                        titulo: 'Nueva encuesta disponible',
                        mensaje: "Ya puedes responder la encuesta '{$actividad->nombre}' de tu ficha.",
                        referenciaTipo: 'encuesta',
                        referenciaId: (int) $cronograma->id_cronograma,
                        claveEvento: 'encuesta_disponible_' . $cronograma->id_cronograma . '_' . $receptorId
                    );
                }
            }

            if ($cronograma->ficha) {
                app(CertificacionNotificacionService::class)
                    ->notificarSiSoloFaltaUnMomento($cronograma->ficha, exigirListoParaCertificar: true);
            }
        });

        return redirect()->route('controlecp.instructor.actividades')
            ->with('success', 'Actividad finalizada correctamente; se notificó a los involucrados.');
    }

    public function asistenciaInstructor(Request $request)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $filtros = $request->validate([
            'ficha_id' => ['nullable', 'integer'],
            'actividad_id' => ['nullable', 'integer'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'estado' => ['nullable', 'in:Registrada,Anulada,Pendiente'],
            'aprendiz_id' => ['nullable', 'integer'],
        ]);
        $base = $this->cronogramasDelInstructor()
            ->whereDoesntHave('asistencia', fn ($query) => $this->asistenciaRegistradaPorAdministrador($query));
        $cronogramaQuery = (clone $base)->with([
            'ficha.program',
            'asistencia.detalles.aprendiz.person',
        ]);
        if (!empty($filtros['ficha_id'])) $cronogramaQuery->where('id_ficha', $filtros['ficha_id']);
        if (!empty($filtros['actividad_id'])) $cronogramaQuery->where('id_actividad', $filtros['actividad_id']);
        if (!empty($filtros['fecha_desde'])) $cronogramaQuery->whereDate('fecha', '>=', $filtros['fecha_desde']);
        if (!empty($filtros['fecha_hasta'])) $cronogramaQuery->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        if (($filtros['estado'] ?? null) === 'Registrada') {
            $cronogramaQuery->whereHas('asistencia', fn ($query) => $query->where('estado', 'Registrada'));
        } elseif (($filtros['estado'] ?? null) === 'Anulada') {
            $cronogramaQuery->whereHas('asistencia', fn ($query) => $query->where('estado', 'Anulada'));
        } elseif (($filtros['estado'] ?? null) === 'Pendiente') {
            $cronogramaQuery->where(fn ($query) => $query
                ->whereDoesntHave('asistencia')
                ->orWhereHas('asistencia', fn ($asistencia) => $asistencia->where('estado', 'Anulada')));
        }
        $cronogramas = $cronogramaQuery->orderByDesc('fecha')->paginate(10)->withQueryString();
        $fichaIdsPagina = $cronogramas->getCollection()->pluck('id_ficha')->filter()->unique();
        $aprendicesPorFicha = Apprentice::with('person')
            ->whereIn('course_id', $fichaIdsPagina)
            ->orderBy('person_id')
            ->get()
            ->groupBy('course_id');
        $fichas = Course::whereIn('id', $base->select('id_ficha'))->orderBy('code')->get();
        $actividades = ActividadEcp::whereIn('id', $base->select('id_actividad'))->orderBy('numero')->get();
        $historialPorAsistencia = collect();
        $asistenciaIds = $cronogramas->getCollection()->pluck('asistencia.id')->filter()->all();
        if ($asistenciaIds) {
            $historialPorAsistencia = DB::table('controlecp_asistencia_historial as h')
                ->leftJoin('users as u', 'u.id', '=', 'h.usuario_id')
                ->whereIn('h.asistencia_id', $asistenciaIds)
                ->orderBy('h.created_at')
                ->get(['h.*', 'u.nickname'])
                ->groupBy('asistencia_id');
        }

        $fichaSeleccionada = !empty($filtros['ficha_id'])
            ? $fichas->firstWhere('id', (int) $filtros['ficha_id'])
            : null;
        $aprendicesFicha = collect();
        $sesionesFicha = collect();
        $sesionesRegistradas = 0;
        if ($fichaSeleccionada) {
            $aprendicesFicha = Apprentice::with('person')->where('course_id', $fichaSeleccionada->id)
                ->orderBy('person_id')->get();
            $sesionesFicha = (clone $base)->where('id_ficha', $fichaSeleccionada->id)
                ->with([
                    'actividad',
                    'asistencia.detalles.aprendiz.person',
                ])
                ->orderByDesc('fecha')->get();
            $sesionesRegistradas = $sesionesFicha->filter(fn ($sesion) => $sesion->asistencia?->estado === 'Registrada')->count();
        }
        $sesionAprendiz = !empty($filtros['aprendiz_id'])
            ? $aprendicesFicha->firstWhere('id', (int) $filtros['aprendiz_id'])
            : null;
        if (!empty($filtros['aprendiz_id']) && !$sesionAprendiz) {
            throw ValidationException::withMessages(['aprendiz_id' => 'El aprendiz no pertenece a una ficha asignada a tu cronograma.']);
        }

        $stats = [
            'total' => (clone $base)->count(),
            'registradas' => (clone $base)->whereHas('asistencia', fn ($query) => $query->where('estado', 'Registrada'))->count(),
            'pendientes' => (clone $base)
                ->whereDate('fecha', '<', Carbon::today())
                ->where(fn ($query) => $query
                    ->whereDoesntHave('asistencia')
                    ->orWhereHas('asistencia', fn ($asistencia) => $asistencia->where('estado', 'Anulada')))
                ->count(),
            'ausencias' => DB::table('controlecp_asistencia_detalles as d')
                ->join('controlecp_asistencias as a', 'd.asistencia_id', '=', 'a.id')
                ->whereIn('a.cronograma_id', (clone $base)->select('id_cronograma'))
                ->where('a.estado', 'Registrada')
                ->where('d.presente', false)
                ->count(),
        ];

        return view('controlecp::instructor.asistencia', compact(
            'cronogramas', 'aprendicesPorFicha', 'fichas', 'actividades', 'historialPorAsistencia',
            'fichaSeleccionada', 'aprendicesFicha', 'sesionesFicha',
            'sesionesRegistradas', 'sesionAprendiz', 'filtros', 'stats'
        ));
    }

    private function asistenciaRegistradaPorAdministrador($query)
    {
        return $query->whereHas('registradoPor.roles', fn ($roles) => $roles->where(function ($roleQuery) {
            $roleQuery->whereIn('slug', ['controlecp.admin', 'superadmin'])
                ->orWhere('full_access', 'Si');
        }));
    }

    private function usuariosAdministrativos()
    {
        return User::whereHas('roles', fn ($roles) => $roles->where(function ($roleQuery) {
            $roleQuery->whereIn('slug', ['controlecp.admin', 'superadmin'])
                ->orWhere('full_access', 'Si');
        }));
    }

    public function exportarAsistenciaInstructor(Request $request)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $filtros = $request->validate([
            'ficha_id' => ['nullable', 'integer'],
            'actividad_id' => ['nullable', 'integer'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'estado' => ['nullable', 'in:Registrada,Anulada,Pendiente'],
        ]);
        $query = $this->cronogramasDelInstructor()
            ->whereDoesntHave('asistencia', fn ($asistencia) => $this->asistenciaRegistradaPorAdministrador($asistencia))
            ->with([
                'actividad',
                'ficha',
                'asistencia.detalles.aprendiz.person',
            ]);
        if (!empty($filtros['ficha_id'])) $query->where('id_ficha', $filtros['ficha_id']);
        if (!empty($filtros['actividad_id'])) $query->where('id_actividad', $filtros['actividad_id']);
        if (!empty($filtros['fecha_desde'])) $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
        if (!empty($filtros['fecha_hasta'])) $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        if (($filtros['estado'] ?? null) === 'Registrada') {
            $query->whereHas('asistencia', fn ($builder) => $builder->where('estado', 'Registrada'));
        } elseif (($filtros['estado'] ?? null) === 'Anulada') {
            $query->whereHas('asistencia', fn ($builder) => $builder->where('estado', 'Anulada'));
        } elseif (($filtros['estado'] ?? null) === 'Pendiente') {
            $query->where(fn ($builder) => $builder
                ->whereDoesntHave('asistencia')
                ->orWhereHas('asistencia', fn ($asistencia) => $asistencia->where('estado', 'Anulada')));
        }
        $sesiones = $query->orderBy('fecha')->get();

        return response()->streamDownload(function () use ($sesiones) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Ficha', 'Actividad', 'Fecha', 'Estado de asistencia', 'Aprendiz', 'Identificador', 'Resultado', 'Observación']);
            foreach ($sesiones as $sesion) {
                $registro = $sesion->asistencia;
                if (!$registro || $registro->estado !== 'Registrada') {
                    fputcsv($salida, [
                        $sesion->ficha?->code,
                        $sesion->actividad?->nombre,
                        $sesion->fecha?->format('Y-m-d'),
                        $registro?->estado ?? 'Pendiente',
                        '',
                        '',
                        '',
                        $registro?->motivo_anulacion ?? '',
                    ]);
                    continue;
                }
                foreach ($registro->detalles as $detalle) {
                    fputcsv($salida, [
                        $sesion->ficha?->code,
                        $sesion->actividad?->nombre,
                        $sesion->fecha?->format('Y-m-d'),
                        $registro->estado,
                        $detalle->aprendiz?->person?->full_name ?? 'Aprendiz',
                        $detalle->aprendiz?->person?->document_number ?? '',
                        $detalle->presente ? 'Presente' : 'Ausente',
                        $detalle->observacion ?? '',
                    ]);
                }
            }
            fclose($salida);
        }, 'reporte-asistencias-instructor.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function asesoriasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        $asesorias = Asesoria::with(['categoria', 'aprendiz.person', 'seguimientos'])
            ->where('id_usuario_instructor', Auth::id())->orderByDesc('fecha')->paginate(10);
        return view('controlecp::instructor.asesorias', compact('asesorias'));
    }

    public function certificadosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        $fichas = Course::with('program')
            ->whereIn('id', $this->cronogramasDelInstructor()->select('id_ficha'))
            ->orderBy('code')->paginate(10);
        $certificaciones = Schema::hasTable('controlecp_certificaciones')
            ? DB::table('controlecp_certificaciones')
                ->whereIn('course_id', $this->cronogramasDelInstructor()->select('id_ficha'))
                ->orderByDesc('fecha_certificacion')->get()
            : collect();
        return view('controlecp::instructor.certificados', compact('fichas', 'certificaciones'));
    }

    public function poeInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        $poes = Poe::with(['versionActual', 'programacionVigente'])
            ->where('id_usuario', Auth::id())->orderBy('codigo')->paginate(10);
        return view('controlecp::instructor.poe', compact('poes'));
    }

    public function acompaniamientoInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        $asesorias = Asesoria::with(['categoria', 'aprendiz.person', 'seguimientos'])
            ->where('id_usuario_instructor', Auth::id())->whereHas('seguimientos')
            ->orderByDesc('fecha')->paginate(10);
        return view('controlecp::instructor.acompaniamiento', compact('asesorias'));
    }

    public function encuestasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $encuestaIds = ActividadEcp::whereIn('id', $this->cronogramasDelInstructor()->select('id_actividad'))
            ->whereNotNull('id_encuesta')->select('id_encuesta');
        $encuestas = Encuesta::with('preguntas.opciones')
            ->whereIn('id_encuesta', $encuestaIds)->orderBy('nombre')->paginate(10);
        return view('controlecp::instructor.encuestas', compact('encuestas'));
    }

    public function fichasInstructor(Request $request)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $request->validate([
            'buscar_ficha' => ['nullable', 'string', 'max:100'],
            'buscar_programa' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:Activo,Inactivo'],
        ]);
        $cronogramasInstructor = $this->cronogramasDelInstructor();
        $query = Course::with('program')
            ->whereIn('id', $cronogramasInstructor->select('id_ficha'));
        if ($request->filled('buscar_ficha')) {
            $ficha = trim($request->input('buscar_ficha'));
            $query->where('code', 'like', "%{$ficha}%");
        }
        if ($request->filled('buscar_programa')) {
            $programa = trim($request->input('buscar_programa'));
            $query->whereHas('program', fn ($programQuery) => $programQuery->where('name', 'like', "%{$programa}%"));
        }
        if ($request->input('estado') === 'Activo') {
            $query->where(function ($courseQuery) {
                $courseQuery->where('status', 'Activo')->orWhereNull('status')->orWhere('status', '');
            });
        } elseif ($request->input('estado') === 'Inactivo') {
            $query->where('status', 'Inactivo');
        }
        $fichasResumen = (clone $query)->get(['id', 'status']);
        $fichasResumenIds = $fichasResumen->pluck('id');
        $fichasActivas = $fichasResumen->filter(fn ($ficha) => $ficha->status === 'Activo' || empty($ficha->status))->count();
        $stats = [
            'fichas' => $fichasResumen->count(),
            'fichas_activas' => $fichasActivas,
            'fichas_inactivas' => $fichasResumen->count() - $fichasActivas,
            'aprendices' => $fichasResumenIds->isNotEmpty()
                ? Apprentice::whereIn('course_id', $fichasResumenIds)->count()
                : 0,
        ];
        $fichas = $query->withCount('apprentices')->orderBy('code')->paginate(10)->withQueryString();
        return view('controlecp::instructor.fichas', compact('fichas', 'stats'));
    }

    public function detalleFichaInstructor(int $fichaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $ficha = Course::with('program')
            ->whereIn('id', $this->cronogramasDelInstructor()->select('id_ficha'))
            ->findOrFail($fichaId);

        return view('controlecp::instructor.ficha_detalle_modal', compact('ficha'));
    }

    public function nominaFichaInstructor(int $fichaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $ficha = Course::with(['program', 'apprentices.person'])
            ->withCount('apprentices')
            ->whereIn('id', $this->cronogramasDelInstructor()->select('id_ficha'))
            ->findOrFail($fichaId);

        return view('controlecp::instructor.ficha_nomina_modal', compact('ficha'));
    }

    public function historiaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $bloques = BloqueHistoria::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->paginate(10);
        return view('controlecp::instructor.historia', compact('bloques'));
    }

    public function inventarioInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $recursos = Inventario::with(['categoria', 'fotos'])->activos()
            ->orderBy('nombre')->paginate(10);
        return view('controlecp::instructor.inventario', compact('recursos'));
    }

    public function inventarioShowInstructor(int $id)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $recurso = Inventario::with(['categoria', 'fotos', 'novedades'])
            ->where('estado', '!=', 'Inactivo')->findOrFail($id);
        return view('controlecp::instructor.inventario_show', compact('recurso'));
    }

    public function pqrInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $pqrs = Pqrs::with(['tipoPqrs', 'aprendiz', 'respuestas', 'historial'])
            ->where('id_usuario_instructor_destino', Auth::id())
            ->orderByDesc('fecha_radicacion')->paginate(10);
        return view('controlecp::instructor.pqr', compact('pqrs'));
    }

    public function pqrShowInstructor(int $id)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $pqr = Pqrs::with(['tipoPqrs', 'aprendiz', 'respuestas', 'historial'])
            ->where('id_usuario_instructor_destino', Auth::id())->findOrFail($id);
        return view('controlecp::instructor.pqr_show', compact('pqr'));
    }

    public function asistenciaChecklistInstructor(Request $request, int $cronogramaId)
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        $cronograma = $this->cronogramasDelInstructor()->with('asistencia.detalles')->findOrFail($cronogramaId);
        if ($cronograma->asistencia && $this->usuariosAdministrativos()->whereKey($cronograma->asistencia->registrado_por)->exists()) {
            abort(404);
        }
        $editando = $request->boolean('editar');
        if ($editando && $cronograma->asistencia?->estado !== 'Registrada') {
            throw ValidationException::withMessages(['asistencia' => 'La asistencia ya no está disponible para edición.']);
        }
        $aprendices = Apprentice::with('person')->where('course_id', $cronograma->id_ficha)
            ->orderBy('person_id')->get();
        $detalles = $cronograma->asistencia?->detalles->keyBy('aprendiz_id') ?? collect();
        return view('controlecp::instructor.asistencia_checklist', compact('cronograma', 'aprendices', 'detalles', 'editando'));
    }

    /**
     * Muestra el inicio del rol Aprendiz.
     */
    public function inicioAprendiz()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.apprentice')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Aprendiz de Control ECP.');
        }

        return view('controlecp::aprendiz.inicio');
    }

    /**
     * MÃ©todos auxiliares para las secciones del Aprendiz
     */
    private function verificarAccesoAprendiz()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.apprentice')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al Ã¡rea de Aprendiz.');
        }
        return /* Indica que no hay informaciï¿½n para devolver. */ null;
    }

    public function cronogramaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return /* Devuelve el resultado de este método. */ $res;

        $usuario = Auth::user();
        $fichaIds = $usuario->person_id
            ? Apprentice::where('person_id', $usuario->person_id)
                ->whereHas('course', fn ($query) => $query->where('status', 'Activo'))
                ->pluck('course_id')
            : collect();

        $actividades = Cronograma::with([
            'actividad.momento',
            'instructor.person',
            'historial.instructorAnterior.person',
            'historial.instructorNuevo.person',
        ])
            ->whereIn('id_ficha', $fichaIds)
            ->orderByRaw("CASE WHEN estado IN ('Pendiente', 'En proceso') THEN 0 ELSE 1 END")
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $actividadesCalendario = $actividades->map(function (Cronograma $actividad) {
            $instructorPersona = $actividad->instructor?->person;
            $numeroActividad = $actividad->actividad?->numero
                ? 'Actividad ' . $actividad->actividad->numero
                : 'Actividad ' . ($actividad->actividad?->id ?? '');

            return [
                'id' => $actividad->id_cronograma,
                'fecha' => $actividad->fecha?->format('Y-m-d'),
                'titulo' => $numeroActividad . ' – ' . ($actividad->actividad?->momento?->nombre ?? 'Sin momento'),
                'nombre' => $actividad->actividad?->nombre ?? 'Actividad sin nombre',
                'momento' => $actividad->actividad?->momento?->nombre ?? 'Sin momento',
                'hora_inicio' => substr($actividad->hora_inicio, 0, 5),
                'hora_fin' => substr($actividad->hora_fin, 0, 5),
                'lugar' => $actividad->lugar ?: 'Sin lugar registrado',
                'instructor' => $instructorPersona
                    ? trim($instructorPersona->first_name . ' ' . $instructorPersona->first_last_name)
                    : ($actividad->instructor?->nickname ?? 'Sin instructor asignado'),
                'estado' => $actividad->estado,
                'semaforo' => $actividad->semaforo ?? 'neutro',
                'fecha_original' => $actividad->fecha_original?->format('Y-m-d'),
                'motivo_posposicion' => $actividad->motivo_posposicion,
                'cambios' => $actividad->historial->map(function (CronogramaHistorial $cambio) {
                    $anterior = $cambio->instructorAnterior?->person;
                    $nuevo = $cambio->instructorNuevo?->person;

                    return [
                        'accion' => $cambio->accion,
                        'fecha_anterior' => $cambio->fecha_anterior?->format('Y-m-d'),
                        'fecha_nueva' => $cambio->fecha_nueva?->format('Y-m-d'),
                        'motivo' => $cambio->motivo,
                        'instructor_anterior' => $anterior
                            ? trim($anterior->first_name . ' ' . $anterior->first_last_name)
                            : null,
                        'instructor_nuevo' => $nuevo
                            ? trim($nuevo->first_name . ' ' . $nuevo->first_last_name)
                            : null,
                    ];
                })->values(),
            ];
        })->values();

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.cronograma', compact('actividades', 'actividadesCalendario'));
    }

    public function historiaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $bloques = BloqueHistoria::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->paginate(10);

        return view('controlecp::aprendiz.historia', compact('bloques'));
    }

    public function encuestaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.encuesta');
    }

    public function asesoriasAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.asesorias');
    }

    public function pqrAprendiz(Request $request)
    {
        if ($res = $this->verificarAccesoAprendiz()) return /* Devuelve el resultado de este método. */ $res;
        $filtros = $request->validate([
            'id_tipo_pqrs' => ['nullable', 'integer', 'exists:tipos_pqrs,id_tipo_pqrs'],
            'estado' => ['nullable', 'in:Pendiente,En trámite,En tramite,Resuelto,Resuelta,Cerrado,Cerrada'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);
        if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
            $request->validate([
                'fecha_hasta' => ['after_or_equal:fecha_desde'],
            ]);
        }
        $consultaPqrsAprendiz = Pqrs::where('id_usuario_aprendiz', Auth::id());
        $consultaFiltrada = clone $consultaPqrsAprendiz;
        if (!empty($filtros['id_tipo_pqrs'])) {
            $consultaFiltrada->where('id_tipo_pqrs', $filtros['id_tipo_pqrs']);
        }
        if (!empty($filtros['estado'])) {
            $consultaFiltrada->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $consultaFiltrada->whereDate('fecha_radicacion', '>=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $consultaFiltrada->whereDate('fecha_radicacion', '<=', $filtros['fecha_hasta']);
        }
        $pqrs = $consultaFiltrada
            ->with(['tipoPqrs', 'instructorDestino.person', 'respuestas.usuario', 'historial.usuario'])
            ->orderByDesc('fecha_radicacion')->paginate(10)->withQueryString();
        $totalPqrs = (clone $consultaPqrsAprendiz)->count();
        $pqrsRespondidas = (clone $consultaPqrsAprendiz)->whereHas('respuestas')->count();
        $pqrsSinRespuesta = (clone $consultaPqrsAprendiz)->whereDoesntHave('respuestas')->count();
        $pqrsEnTramite = (clone $consultaPqrsAprendiz)->whereIn('estado', ['En trámite', 'En tramite'])->count();
        $tiposPqrs = TipoPqrs::orderBy('nombre')->get(['id_tipo_pqrs', 'nombre']);
        $instructores = User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.instructor'))
            ->orderBy('nickname')->get(['id', 'nickname', 'email']);
        return view('controlecp::aprendiz.pqr', compact(
            'pqrs',
            'tiposPqrs',
            'instructores',
            'totalPqrs',
            'pqrsRespondidas',
            'pqrsSinRespuesta',
            'pqrsEnTramite'
        ));
    }
}
