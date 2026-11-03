<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\ActividadEcp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\Apprentice;
use Modules\ControlECP\Entities\Material;
use Modules\ControlECP\Entities\Pqr;
use Modules\ControlECP\Entities\BloqueHistoria;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\EncuestaRespuesta;
use Modules\ControlECP\Entities\EncuestaRespuestaDetalle;
use Modules\ControlECP\Entities\OpcionPregunta;
use Modules\ControlECP\Entities\Pregunta;
use Modules\ControlECP\Entities\CalificacionEcp;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\CalificacionAsesoria;
use Modules\ControlECP\Entities\CategoriaAsesoria;
use Modules\ControlECP\Entities\AsesoriaHistorial;
use Modules\ControlECP\Services\NotificacionService;

class ControlECPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('controlecp::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('controlecp::create');
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
        return view('controlecp::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('controlecp::edit');
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
            return redirect()
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

        // Fichas con programas para mostrar en el seguimiento de momentos
        $fichasDestacadas = Course::with('program')
            ->whereHas('program')
            ->take(6)
            ->get();

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

        // Fichas próximas a completar los 3 momentos
        $fichasProximasVencer = min(4, $totalFichasActivas);

        // Estado de integración con la API REST Central SICEFA
        $apiConfig = Cache::store('file')->get('controlecp_api_config', [
            'url' => route('controlecp.api.usuarios.mock'),
            'token' => '',
            'timeout' => 5,
        ]);
        $lastSync = Cache::store('file')->get('controlecp_last_sync', [
            'date' => now()->format('d/m/Y H:i'),
            'total_synced' => $totalUsuariosEcp,
            'status' => 'Conectado',
        ]);

        return view('controlecp::administrador.inicio', compact(
            'usuario',
            'totalUsuariosEcp',
            'totalUsuariosSistema',
            'conteoAdmins',
            'conteoInstructores',
            'conteoAprendices',
            'totalFichas',
            'totalFichasActivas',
            'fichasDestacadas',
            'actividadesHoy',
            'solicitudesPendientes',
            'totalPqrs',
            'recursosCriticos',
            'materialesCriticos',
            'fichasProximasVencer',
            'apiConfig',
            'lastSync'
        ));
    }

    /**
     * Muestra la vista de gestión de Usuarios del Administrador (Integración vía API).
     */
    public function usuariosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de usuarios de Control ECP.');
        }

        return view('controlecp::administrador.usuario');
    }

    /**
     * Muestra la vista de consulta de Fichas del Administrador (Integración vía API).
     */
    public function fichasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de fichas de Control ECP.');
        }

        return view('controlecp::administrador.fichas');
    }

    /**
     * Muestra la vista de Momentos del Administrador.
     */
    public function momentosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
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

        // Distribución pedagógica de fichas en la ruta de paz
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

        return view('controlecp::administrador.momentos', compact(
            'statsMomentos',
            'fichasMomento1',
            'fichasMomento2',
            'fichasMomento3',
            'catalogoFichas'
        ));
    }

    /**
     * Muestra la vista de Actividades del Administrador.
     * Incluye catálogo completo de 18 actividades pedagógicas ECP con metadata enriquecida
     * para futura integración con REST API del ERP SENA Empresa / SICEFA.
     */
    public function actividadesAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a Actividades de Control ECP.');
        }

        // ──────────────────────────────────────────────────────────────────────
        // CATÁLOGO OFICIAL DE 18 ACTIVIDADES PEDAGÓGICAS — ESCUELA CULTURA DE PAZ
        // Fuente: Ruta Formativa Control ECP – Centro La Angostura (SENA)
        // Cuando se conecte la API REST Central del ERP SENA Empresa / SICEFA,
        // estos datos serán reemplazados por: GET /api/v1/controlecp/actividades
        // ──────────────────────────────────────────────────────────────────────
        $actividades = [
            // ── MOMENTO 1: AUTOCONCIENCIA ─────────────────────────────────────
            [
                'id'            => 1,
                'numero'        => '01',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Reconocimiento y Gestión Emocional',
                'objetivo'      => 'Identificar la rueda de emociones y técnicas de autorregulación emocional en el contexto formativo del aprendiz SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Taller vivencial con rueda de emociones de Plutchik, ejercicios de respiración guiada, diario emocional y retroalimentación grupal.',
                'competencias'  => ['Autoconciencia emocional', 'Autorregulación', 'Inteligencia emocional'],
                'materiales'    => ['Hojas de trabajo — Rueda de Emociones', 'Cartulina A1', 'Marcadores de colores', 'Diario reflexivo personal'],
                'tags'          => ['emociones', 'autorregulación', 'mindfulness'],
            ],
            [
                'id'            => 2,
                'numero'        => '02',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Taller de Autorreflexión y Autoconcepto',
                'objetivo'      => 'Explorar la percepción personal, fortalezas individuales y áreas de mejora desde una mirada formativa constructiva.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Cuestionario de autoevaluación de fortalezas (VIA), conversatorio en triadas, mapa de identidad personal y socialización plenaria.',
                'competencias'  => ['Autoreconocimiento', 'Identidad personal', 'Proyecto de vida'],
                'materiales'    => ['Test de Fortalezas VIA impreso', 'Papel craft', 'Lápices de colores', 'Cinta adhesiva'],
                'tags'          => ['identidad', 'fortalezas', 'autoestima'],
            ],
            [
                'id'            => 3,
                'numero'        => '03',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Proyecto de Vida y Sueños Integrales',
                'objetivo'      => 'Diseñar metas personales y profesionales a corto, mediano y largo plazo alineadas con el perfil SENA.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Línea de vida individual, construcción de metas SMART, tablero de visión personal, presentación ante el grupo.',
                'competencias'  => ['Planificación personal', 'Visión de futuro', 'Emprendimiento'],
                'materiales'    => ['Revistas para recorte', 'Cartulina de colores', 'Tijeras y pegante', 'Post-it 3 colores'],
                'tags'          => ['metas', 'proyecto de vida', 'visión'],
            ],
            [
                'id'            => 4,
                'numero'        => '04',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Identificación de Fortalezas y Oportunidades',
                'objetivo'      => 'Construir la Matriz DOFA personal aplicada al contexto laboral y formativo del entorno SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Aplicación guiada de la Matriz DOFA personal, análisis de entorno con el instructor, plan de mejora individual.',
                'competencias'  => ['Análisis situacional', 'Pensamiento crítico', 'Autogestión'],
                'materiales'    => ['Plantilla DOFA impresa', 'Bolígrafos', 'Marcadores', 'Tablero o papelógrafo'],
                'tags'          => ['DOFA', 'análisis', 'estrategia personal'],
            ],
            [
                'id'            => 5,
                'numero'        => '05',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Expresión Asertiva de Sentimientos',
                'objetivo'      => 'Desarrollar la capacidad de comunicar emociones de manera clara, respetuosa y asertiva en el entorno formativo.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Role-playing de situaciones cotidianas del SENA, técnica del "Mensaje Yo", juego de roles asertivo, retroalimentación grupal.',
                'competencias'  => ['Comunicación asertiva', 'Expresión emocional', 'Relaciones interpersonales'],
                'materiales'    => ['Tarjetas de situaciones', 'Bolígrafos', 'Hojas de reflexión'],
                'tags'          => ['asertividad', 'comunicación', 'emociones'],
            ],
            [
                'id'            => 6,
                'numero'        => '06',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Mapeo Corporal y Autocuidado',
                'objetivo'      => 'Reconocer somatizaciones del estrés y promover hábitos de salud mental y autocuidado integral.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Ejercicio de mapeo corporal en silueta humana, identificación de tensiones, técnicas de relajación progresiva de Jacobson.',
                'competencias'  => ['Salud mental', 'Autocuidado', 'Bienestar integral'],
                'materiales'    => ['Siluetas humanas impresas A3', 'Crayolas', 'Música relajante', 'Alfombras o colchonetas'],
                'tags'          => ['salud mental', 'cuerpo', 'relajación'],
            ],
            [
                'id'            => 7,
                'numero'        => '07',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Control del Estrés e Impulsos',
                'objetivo'      => 'Aplicar técnicas de respiración consciente y mindfulness básico para el manejo del estrés en contextos académicos y laborales.',
                'duracion'      => '1.5 Horas',
                'duracion_min'  => 90,
                'metodologia'   => 'Ejercicio 4-7-8 de respiración, body-scan guiado, mindfulness de 5 minutos, registro de disparadores emocionales.',
                'competencias'  => ['Gestión del estrés', 'Atención plena', 'Regulación impulsiva'],
                'materiales'    => ['Aplicación de meditación (opcional)', 'Hoja de registro de disparadores', 'Espacio tranquilo'],
                'tags'          => ['estrés', 'mindfulness', 'respiración'],
            ],
            [
                'id'            => 8,
                'numero'        => '08',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Historia Personal y Raíces Culturales',
                'objetivo'      => 'Valorar el origen cultural, la historia familiar y su impacto en la construcción de la identidad del aprendiz.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Árbol genealógico familiar, línea del tiempo personal con hitos culturales, socialización en círculo de aprendizaje.',
                'competencias'  => ['Identidad cultural', 'Diversidad', 'Sentido de pertenencia'],
                'materiales'    => ['Plantilla árbol genealógico', 'Fotos (opcional)', 'Marcadores', 'Cartulina'],
                'tags'          => ['cultura', 'historia', 'identidad'],
            ],
            [
                'id'            => 9,
                'numero'        => '09',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Principios, Valores y Ética Personal',
                'objetivo'      => 'Alinear los valores personales con el entorno laboral, institucional y el perfil del egresado SENA.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Clasificación de valores con tarjetas (card sorting), dilemas éticos situacionales, construcción de código personal de ética.',
                'competencias'  => ['Ética profesional', 'Valores institucionales', 'Integridad'],
                'materiales'    => ['Tarjetas de valores', 'Dilemas éticos impresos', 'Hoja de código personal'],
                'tags'          => ['ética', 'valores', 'integridad'],
            ],
            [
                'id'            => 10,
                'numero'        => '10',
                'momento_id'    => 1,
                'momento'       => 'Momento 1: Autoconciencia',
                'momento_color' => '#39A900',
                'momento_icon'  => 'fa-brain',
                'nombre'        => 'Compromiso Conmigo Mismo',
                'objetivo'      => 'Formalizar la carta de compromiso personal y el plan de acción formativo para la ruta ECP.',
                'duracion'      => '1 Hora',
                'duracion_min'  => 60,
                'metodologia'   => 'Redacción individual de carta de compromiso, firma simbólica grupal, lectura en voz alta y archivado en portafolio de evidencias.',
                'competencias'  => ['Responsabilidad', 'Compromiso formativo', 'Autonomía'],
                'materiales'    => ['Formato oficial Carta de Compromiso ECP', 'Bolígrafo', 'Portafolio de evidencias', 'Sello institucional'],
                'tags'          => ['compromiso', 'plan de acción', 'cierre'],
            ],

            // ── MOMENTO 2: CONVIVENCIA ────────────────────────────────────────
            [
                'id'            => 11,
                'numero'        => '01',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'Círculos de Diálogo y Empatía',
                'objetivo'      => 'Desarrollar la escucha activa, la empatía y el diálogo respetuoso en la comunidad formativa del Centro.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Círculo de paz restaurativa (talking piece), preguntas reflexivas guiadas, escucha activa en parejas, cierre con compromisos grupales.',
                'competencias'  => ['Escucha activa', 'Empatía', 'Diálogo constructivo'],
                'materiales'    => ['Objeto hablador (talking piece)', 'Preguntas de círculo impresas', 'Sillas en disposición circular'],
                'tags'          => ['diálogo', 'empatía', 'círculo restaurativo'],
            ],
            [
                'id'            => 12,
                'numero'        => '02',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'Dinámicas de Trabajo Colaborativo',
                'objetivo'      => 'Fortalecer la confianza grupal y las habilidades de trabajo en equipo en contextos de alta diversidad.',
                'duracion'      => '2.5 Horas',
                'duracion_min'  => 150,
                'metodologia'   => 'Retos colaborativos (torre de espaguetis, puente de papel), análisis de roles en equipo (Belbin), reflexión sobre dinámicas grupales.',
                'competencias'  => ['Trabajo en equipo', 'Liderazgo colaborativo', 'Gestión de roles'],
                'materiales'    => ['Espaguetis, malvaviscos, cuerda, cinta adhesiva', 'Test de Belbin simplificado', 'Papelógrafo'],
                'tags'          => ['equipo', 'colaboración', 'confianza'],
            ],
            [
                'id'            => 13,
                'numero'        => '03',
                'momento_id'    => 2,
                'momento'       => 'Momento 2: Convivencia',
                'momento_color' => '#0070B8',
                'momento_icon'  => 'fa-people-holding-hands',
                'nombre'        => 'Comunicación No Violenta (CNV)',
                'objetivo'      => 'Aplicar el lenguaje integrador de Rosenberg para transformar patrones comunicativos agresivos o pasivos.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Estudio del modelo CNV (Observación-Sentimiento-Necesidad-Petición), práctica con situaciones reales del aula, video-foro CNV.',
                'competencias'  => ['CNV', 'Comunicación empática', 'Resolución verbal'],
                'materiales'    => ['Guía CNV de Rosenberg (resumen)', 'Tarjetas de situaciones', 'Proyector para video', 'Hojas de práctica'],
                'tags'          => ['CNV', 'Rosenberg', 'comunicación empática'],
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
                'metodologia'   => 'Lluvia de ideas sobre normas de convivencia, construcción participativa del reglamento interno, firma del pacto grupal.',
                'competencias'  => ['Democracia participativa', 'Respeto', 'Normas sociales'],
                'materiales'    => ['Papel kraft grande', 'Marcadores de colores', 'Post-it', 'Formato de pacto de convivencia'],
                'tags'          => ['convivencia', 'pacto', 'normas grupales'],
            ],

            // ── MOMENTO 3: RESOLUCIÓN ─────────────────────────────────────────
            [
                'id'            => 15,
                'numero'        => '01',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: Resolución',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Taller de Mediación Pacífica',
                'objetivo'      => 'Desarrollar herramientas prácticas para intervenir como mediadores neutrales en conflictos del entorno formativo.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Estudio del proceso de mediación escolar, práctica de fases de mediación (pre-mediación, sesión conjunta, acuerdo), simulación supervisada.',
                'competencias'  => ['Mediación', 'Neutralidad', 'Facilitación de diálogos'],
                'materiales'    => ['Manual de Mediación Escolar', 'Casos prácticos impresos', 'Acta de acuerdo de mediación', 'Sala privada de mediación'],
                'tags'          => ['mediación', 'conflictos', 'facilitación'],
            ],
            [
                'id'            => 16,
                'numero'        => '02',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: Resolución',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Negociación y Estrategias Ganar-Ganar',
                'objetivo'      => 'Aplicar técnicas de negociación basada en intereses (Harvard) para llegar a acuerdos sostenibles y equitativos.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Módulo teórico del Método Harvard de Negociación, simulación de negociación en díadas, análisis de estrategias, retroalimentación.',
                'competencias'  => ['Negociación', 'Pensamiento estratégico', 'Consenso'],
                'materiales'    => ['Resumen Método Harvard', 'Roles de negociación impresos', 'Cronómetro', 'Acta de acuerdo'],
                'tags'          => ['negociación', 'Harvard', 'ganar-ganar'],
            ],
            [
                'id'            => 17,
                'numero'        => '03',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: Resolución',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Estudio de Casos y Juego de Roles',
                'objetivo'      => 'Simular situaciones reales de tensión en entornos laborales del SENA para desarrollar respuestas asertivas y pacíficas.',
                'duracion'      => '2 Horas',
                'duracion_min'  => 120,
                'metodologia'   => 'Análisis de casos reales anonimizados, asignación de roles (víctima, agresor, mediador, testigo), dramatización y reflexión grupal.',
                'competencias'  => ['Análisis de situaciones', 'Empatía situacional', 'Toma de decisiones'],
                'materiales'    => ['Fichas de caso impresa', 'Tarjetas de rol', 'Espacio amplio para actuación', 'Hoja de reflexión post-rol'],
                'tags'          => ['role-playing', 'casos reales', 'dramatización'],
            ],
            [
                'id'            => 18,
                'numero'        => '04',
                'momento_id'    => 3,
                'momento'       => 'Momento 3: Resolución',
                'momento_color' => '#6A1B9A',
                'momento_icon'  => 'fa-scale-balanced',
                'nombre'        => 'Plan de Acción y Pactos de Paz',
                'objetivo'      => 'Cerrar el proceso formativo ECP con evaluación integral, elaboración del plan de paz personal y certificación.',
                'duracion'      => '3 Horas',
                'duracion_min'  => 180,
                'metodologia'   => 'Evaluación reflexiva del proceso ECP, construcción del plan de paz personal, ceremonia de compromisos grupales y entrega de reconocimiento.',
                'competencias'  => ['Evaluación formativa', 'Compromiso social', 'Paz y reconciliación'],
                'materiales'    => ['Formato Plan de Paz ECP', 'Diplomas/reconocimientos', 'Listado de verificación ECP', 'Cámara fotográfica (ceremonia)'],
                'tags'          => ['plan de paz', 'cierre', 'certificación', 'evaluación'],
            ],
        ];

        // ── ESTADÍSTICAS GENERALES DE ACTIVIDADES ─────────────────────────────
        $totalActividades  = count($actividades);
        $totalHoras        = collect($actividades)->sum('duracion_min') / 60; // en horas
        $totalMomentos     = 3;

        // Fichas activas del sistema (real desde DB)
        try {
            $fichasActivas = Cache::store('file')->remember('act_fichas_activas', 600, function () {
                return Course::where('status', 'Activo')->count();
            });
        } catch (\Exception $e) {
            $fichasActivas = Course::where('status', 'Activo')->count();
        }

        // Aprendices activos (real desde DB)
        try {
            $aprendicesActivos = Cache::store('file')->remember('act_aprendices_activos', 600, function () {
                return Apprentice::whereHas('course', function ($q) {
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

        return view('controlecp::administrador.actividades', compact('actividades', 'statsActividades'));
    }

    /**
     * Muestra la vista de Cronograma del Administrador.
     */
    public function cronogramaAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al Cronograma de Control ECP.');
        }

        return view('controlecp::administrador.cronograma');
    }

    /**
     * Muestra la vista de consulta y estado de Asistencia del Administrador (Flujo dependiente del Rol Instructor).
     */
    public function asistenciaAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de asistencia de Control ECP.');
        }

        return view('controlecp::administrador.asistencia');
    }

    /**
     * Muestra la vista de consulta y gestión de Asesorías del Administrador.
     */
    public function asesoriasAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de asesorías de Control ECP.');
        }

        return view('controlecp::administrador.asesorias');
    }

    /**
     * Muestra la vista de consulta y emisión de Certificados del Administrador.
     */
    public function certificadosAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de certificados de Control ECP.');
        }

        return view('controlecp::administrador.certificados');
    }


        /**
     * Muestra la vista de Historias dentro del Repositorio del Administrador.
     */
    public function repositorioHistoria()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.historia');
    }

    /**
     * Muestra la vista de Encuestas dentro del Repositorio del Administrador.
     */
    public function repositorioEncuesta()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.encuesta');
    }

    /**
     * Muestra la vista de Tabulación dentro del Repositorio del Administrador.
     */
    public function repositorioTabulacion()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.tabulacion');
    }

    /**
     * Muestra la vista de POE dentro del Repositorio del Administrador.
     */
    public function repositorioPoe()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al repositorio de Control ECP.');
        }

        return view('controlecp::administrador.repositorio.poe');
    }

    public function historiaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $bloques = BloqueHistoria::where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('controlecp::instructor.historia', compact('bloques'));
    }
    /**
     * Muestra el inicio del rol Instructor.
     */
    public function inicioInstructor()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.instructor')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Instructor de Control ECP.');
        }

        return view('controlecp::instructor.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Instructor
     */
    private function verificarAccesoInstructor()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.instructor')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Instructor.');
        }
        return null;
    }

    public function cronogramaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return app(\Modules\ControlECP\Http\Controllers\CronogramaController::class)->instructor(request());
    }

    public function momentosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $momentos = Momento::with([
            'actividades' => fn ($query) => $query
                ->where('estado', 'Activo')
                ->orderBy('numero'),
        ])
            ->withCount(['actividades' => fn ($query) => $query->where('estado', 'Activo')])
            ->orderBy('orden')
            ->get();

        return view('controlecp::instructor.momentos', compact('momentos'));
    }

    public function actividadesInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $momentos = Momento::with([
            'actividades' => fn ($query) => $query
                ->with('encuesta')
                ->where('estado', 'Activo')
                ->orderBy('numero'),
        ])
            ->withCount(['actividades' => fn ($query) => $query->where('estado', 'Activo')])
            ->orderBy('orden')
            ->get();

        return view('controlecp::instructor.actividades', compact('momentos'));
    }

    /**
     * Consulta únicamente las fichas que tienen actividades asignadas al instructor.
     */
    public function fichasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;

        $usuario = Auth::user();
        $asignaciones = DB::table('cronograma')
            ->where('id_instructor', $usuario->id)
            ->select('id_ficha', 'id_actividad', 'estado')
            ->get()
            ->groupBy('id_ficha');

        $fichas = Course::with(['program', 'apprentices.person'])
            ->whereIn('id', $asignaciones->keys()->all())
            ->orderBy('code')
            ->get();

        $actividadIds = $asignaciones->flatten()->pluck('id_actividad')->unique();
        $actividades = ActividadEcp::with('momento')->whereIn('id', $actividadIds)->get()->keyBy('id');

        $fichas->each(function ($ficha) use ($asignaciones, $actividades) {
            $registros = $asignaciones->get($ficha->id, collect());
            $ficha->actividades_asignadas = $registros->pluck('id_actividad')->unique()->count();
            $ficha->actividades_finalizadas = $registros->where('estado', 'Finalizada')->pluck('id_actividad')->unique()->count();
            $ficha->momentos_certificados = $actividades
                ->only($registros->pluck('id_actividad')->unique()->all())
                ->filter(fn ($actividad) => $actividad->momento)
                ->groupBy(fn ($actividad) => $actividad->momento->id)
                ->filter(function ($momentoActividades) use ($registros) {
                    return $momentoActividades->every(function ($actividad) use ($registros) {
                        return $registros->where('id_actividad', $actividad->id)
                            ->contains('estado', 'Finalizada');
                    });
                })
                ->count();
            $ficha->porcentaje_asistencia = 0;
        });

        return view('controlecp::instructor.fichas', compact('usuario', 'fichas'));
    }

    public function asistenciaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return app(\Modules\ControlECP\Http\Controllers\AsistenciaEcpController::class)->index(request());
    }

    public function asesoriasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return app(\Modules\ControlECP\Http\Controllers\InstructorAsesoriaController::class)->index(request());
    }

    public function certificadosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return app(\Modules\ControlECP\Http\Controllers\InstructorCertificacionController::class)->index();
    }

    public function poeInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.poe');
    }

    public function acompaniamientoInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return app(\Modules\ControlECP\Http\Controllers\AcompanamientoController::class)->index();
    }

    /**
     * Muestra el inicio del rol Aprendiz.
     */
    public function inicioAprendiz()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.apprentice')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Aprendiz de Control ECP.');
        }

        return view('controlecp::aprendiz.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Aprendiz
     */
    private function verificarAccesoAprendiz()
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.apprentice')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Aprendiz.');
        }
        return null;
    }

    public function cronogramaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $usuario = Auth::user()->load('person.apprentices.course');
        $fichaIds = $usuario->person?->apprentices
            ->pluck('course_id')
            ->filter()
            ->unique()
            ->values() ?? collect();

        $sesiones = Cronograma::with([
            'actividad.momento',
            'instructor.person',
            'ficha.program',
        ])
            ->whereIn('id_ficha', $fichaIds)
            ->get()
            ->sortBy(function (Cronograma $sesion) {
                $prioridad = $sesion->estado === 'Pendiente' ? 0 : 1;
                return [$prioridad, $sesion->fecha?->timestamp ?? PHP_INT_MAX, $sesion->hora_inicio ?? ''];
            })
            ->values();

        $fichas = $sesiones->pluck('ficha')->filter()->unique('id')->values();

        return view('controlecp::aprendiz.cronograma', compact('sesiones', 'fichas'));
    }

    public function historiaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $bloques = BloqueHistoria::where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('controlecp::aprendiz.historia', compact('bloques'));
    }

    public function encuestaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $usuario = Auth::user()->load('person.apprentices');
        $aprendizIds = $usuario->person?->apprentices->pluck('id')->filter()->values() ?? collect();
        $fichaIds = $usuario->person?->apprentices->pluck('course_id')->filter()->unique()->values() ?? collect();

        $cronogramasPresentes = DB::table('controlecp_asistencia_detalles as detalle')
            ->join('controlecp_asistencias as asistencia', 'asistencia.id_asistencia', '=', 'detalle.id_asistencia')
            ->whereIn('detalle.id_aprendiz', $aprendizIds)
            ->where('detalle.presente', true)
            ->pluck('asistencia.id_cronograma');

        $sesiones = \Modules\ControlECP\Entities\Cronograma::with([
            'actividad.encuesta.preguntas.opciones',
            'actividad.momento',
            'instructor.person',
        ])
            ->whereIn('id_ficha', $fichaIds)
            ->whereIn('id_cronograma', $cronogramasPresentes)
            ->where(function ($q) {
                $q->where('cronograma.estado', 'Finalizada')
                    ->orWhereHas('actividad', fn($actividad) => $actividad->where('estado', 'Finalizado'));
            })
            ->whereHas('actividad.encuesta', fn($q) => $q->where('estado', 'Activa'))
            ->orderByDesc('fecha')
            ->get();

        $respuestas = EncuestaRespuesta::where('id_usuario', $usuario->id)
            ->whereIn('id_cronograma', $sesiones->pluck('id_cronograma'))
            ->pluck('id_cronograma')
            ->flip();

        return view('controlecp::aprendiz.encuesta', compact('sesiones', 'respuestas'));
    }

    public function responderEncuestaAprendiz(int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $sesion = $this->sesionEncuestaAprendiz($id);
        $respuesta = EncuestaRespuesta::where('id_cronograma', $sesion->id_cronograma)
            ->where('id_usuario', Auth::id())
            ->exists();

        if ($respuesta) {
            return redirect()->route('controlecp.aprendiz.encuesta')
                ->with('error', 'Esta encuesta ya fue respondida.');
        }

        return view('controlecp::aprendiz.encuesta_responder', compact('sesion'));
    }

    public function guardarEncuestaAprendiz(Request $request, int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $sesion = $this->sesionEncuestaAprendiz($id);
        $encuesta = $sesion->actividad->encuesta;
        $preguntas = $encuesta->preguntas;
        $datos = $request->validate([
            'respuestas' => ['required', 'array'],
            'respuestas.*' => ['nullable', 'string', 'max:5000'],
        ]);

        if (EncuestaRespuesta::where('id_cronograma', $sesion->id_cronograma)
            ->where('id_usuario', Auth::id())->exists()) {
            return redirect()->route('controlecp.aprendiz.encuesta')
                ->with('error', 'Esta encuesta ya fue respondida.');
        }

        $respuestas = $datos['respuestas'];
        foreach ($preguntas as $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta->id_pregunta] ?? ''));
            if ($pregunta->obligatoria && $valor === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'respuestas.' . $pregunta->id_pregunta => 'Debes responder todas las preguntas obligatorias.',
                ]);
            }

            if ($valor !== '' && $pregunta->isOpcionMultiple()) {
                if (!OpcionPregunta::where('id_pregunta', $pregunta->id_pregunta)
                    ->where('id_opcion_pregunta', $valor)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'respuestas.' . $pregunta->id_pregunta => 'La opción seleccionada no es válida.',
                    ]);
                }
            }

            if ($valor !== '' && $pregunta->isEscala()
                && (!ctype_digit($valor) || (int) $valor < 1 || (int) $valor > 5)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'respuestas.' . $pregunta->id_pregunta => 'La calificación debe estar entre 1 y 5.',
                ]);
            }
        }

        DB::transaction(function () use ($sesion, $preguntas, $respuestas) {
            $cabecera = EncuestaRespuesta::create([
                'id_cronograma' => $sesion->id_cronograma,
                'id_usuario' => Auth::id(),
                'fecha' => now(),
            ]);

            foreach ($preguntas as $pregunta) {
                $valor = trim((string) ($respuestas[$pregunta->id_pregunta] ?? ''));
                if ($valor === '') continue;
                EncuestaRespuestaDetalle::create([
                    'id_encuesta_respuesta' => $cabecera->id_encuesta_respuesta,
                    'id_pregunta' => $pregunta->id_pregunta,
                    'id_opcion_pregunta' => $pregunta->isOpcionMultiple() ? $valor : null,
                    'valor' => $pregunta->isOpcionMultiple()
                        ? OpcionPregunta::find($valor)?->texto
                        : $valor,
                ]);
            }
        });

        return redirect()->route('controlecp.aprendiz.encuesta')
            ->with('success', 'Tu encuesta fue enviada correctamente.');
    }

    public function calificacionesAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $usuario = Auth::user()->load('person.apprentices');
        $aprendizIds = $usuario->person?->apprentices->pluck('id')->filter()->values() ?? collect();
        $fichaIds = $usuario->person?->apprentices->pluck('course_id')->filter()->unique()->values() ?? collect();

        $sesiones = $this->sesionesCalificablesAprendiz($aprendizIds, $fichaIds)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $calificaciones = CalificacionEcp::where('user_id', $usuario->id)
            ->whereIn('actividad_id', $sesiones->pluck('id_actividad'))
            ->whereIn('course_id', $sesiones->pluck('id_ficha'))
            ->get()
            ->keyBy(fn (CalificacionEcp $calificacion) => "{$calificacion->actividad_id}:{$calificacion->course_id}");

        $sesiones->each(function (Cronograma $sesion) use ($calificaciones): void {
            $sesion->calificacion_aprendiz = $calificaciones->get("{$sesion->id_actividad}:{$sesion->id_ficha}");
        });

        $asesorias = Asesoria::with(['categoria', 'instructor.person'])
            ->where('id_usuario_aprendiz', $usuario->id)
            ->where('estado', 'Realizada')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $calificacionesAsesoria = CalificacionAsesoria::where('user_id', $usuario->id)
            ->whereIn('id_asesoria', $asesorias->pluck('id_asesoria'))
            ->get()
            ->keyBy('id_asesoria');

        $asesorias->each(function (Asesoria $asesoria) use ($calificacionesAsesoria): void {
            $asesoria->calificacion_aprendiz = $calificacionesAsesoria->get($asesoria->id_asesoria);
        });

        return view('controlecp::aprendiz.calificaciones', compact('sesiones', 'asesorias'));
    }

    public function calificarActividadAprendiz(int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $sesion = $this->sesionCalificableAprendiz($id);
        $calificacion = CalificacionEcp::where('actividad_id', $sesion->id_actividad)
            ->where('course_id', $sesion->id_ficha)
            ->where('user_id', Auth::id())
            ->first();

        if ($calificacion) {
            return redirect()->route('controlecp.aprendiz.calificaciones')
                ->with('error', 'Esta actividad ya fue calificada.');
        }

        return view('controlecp::aprendiz.calificar_actividad', compact('sesion'));
    }

    public function guardarCalificacionAprendiz(Request $request, int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $sesion = $this->sesionCalificableAprendiz($id);
        $datos = $request->validate([
            'estrellas' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $yaCalificada = CalificacionEcp::where('actividad_id', $sesion->id_actividad)
            ->where('course_id', $sesion->id_ficha)
            ->where('user_id', Auth::id())
            ->exists();

        if ($yaCalificada) {
            return redirect()->route('controlecp.aprendiz.calificaciones')
                ->with('error', 'Esta actividad ya fue calificada.');
        }

        CalificacionEcp::create([
            'actividad_id' => $sesion->id_actividad,
            'course_id' => $sesion->id_ficha,
            'user_id' => Auth::id(),
            'estrellas' => $datos['estrellas'],
            'comentario' => $datos['comentario'] ?? null,
        ]);

        return redirect()->route('controlecp.aprendiz.calificaciones')
            ->with('success', 'Tu calificación fue guardada correctamente.');
    }

    public function calificarAsesoriaAprendiz(int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $asesoria = $this->asesoriaCalificableAprendiz($id);
        if (CalificacionAsesoria::where('id_asesoria', $asesoria->id_asesoria)
            ->where('user_id', Auth::id())
            ->exists()) {
            return redirect()->route('controlecp.aprendiz.calificaciones')
                ->with('error', 'Esta asesoría ya fue calificada.');
        }

        return view('controlecp::aprendiz.calificar_asesoria', compact('asesoria'));
    }

    public function guardarCalificacionAsesoriaAprendiz(Request $request, int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $asesoria = $this->asesoriaCalificableAprendiz($id);
        $datos = $request->validate([
            'estrellas' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        if (CalificacionAsesoria::where('id_asesoria', $asesoria->id_asesoria)
            ->where('user_id', Auth::id())
            ->exists()) {
            return redirect()->route('controlecp.aprendiz.calificaciones')
                ->with('error', 'Esta asesoría ya fue calificada.');
        }

        CalificacionAsesoria::create([
            'id_asesoria' => $asesoria->id_asesoria,
            'user_id' => Auth::id(),
            'estrellas' => $datos['estrellas'],
            'comentario' => $datos['comentario'] ?? null,
        ]);

        return redirect()->route('controlecp.aprendiz.calificaciones')
            ->with('success', 'Tu calificación de la asesoría fue guardada correctamente.');
    }

    private function sesionEncuestaAprendiz(int $id): Cronograma
    {
        $usuario = Auth::user()->load('person.apprentices');
        $aprendizIds = $usuario->person?->apprentices->pluck('id')->filter()->values() ?? collect();
        $fichaIds = $usuario->person?->apprentices->pluck('course_id')->filter()->unique()->values() ?? collect();

        $presente = DB::table('controlecp_asistencia_detalles as detalle')
            ->join('controlecp_asistencias as asistencia', 'asistencia.id_asistencia', '=', 'detalle.id_asistencia')
            ->whereIn('detalle.id_aprendiz', $aprendizIds)
            ->where('detalle.presente', true)
            ->where('asistencia.id_cronograma', $id)
            ->exists();

        abort_unless($presente, 403, 'Solo puedes responder encuestas de actividades a las que asististe.');

        return Cronograma::with(['actividad.encuesta.preguntas.opciones', 'actividad.momento', 'instructor.person'])
            ->whereIn('id_ficha', $fichaIds)
            ->where(function ($q) {
                $q->where('cronograma.estado', 'Finalizada')
                    ->orWhereHas('actividad', fn($actividad) => $actividad->where('estado', 'Finalizado'));
            })
            ->whereKey($id)
            ->whereHas('actividad.encuesta', fn($q) => $q->where('estado', 'Activa'))
            ->firstOrFail();
    }

    private function sesionesCalificablesAprendiz($aprendizIds, $fichaIds)
    {
        return Cronograma::with(['actividad.momento', 'ficha.program'])
            ->whereIn('id_ficha', $fichaIds)
            ->whereHas('asistenciaEcp', function ($asistencia) use ($aprendizIds): void {
                $asistencia->where('estado', 'Registrada')
                    ->whereHas('detalles', function ($detalle) use ($aprendizIds): void {
                        $detalle->whereIn('id_aprendiz', $aprendizIds)
                            ->where('presente', true);
                    });
            })
            ->where(function ($query): void {
                $query->where('cronograma.estado', 'Finalizada')
                    ->orWhereHas('actividad', fn ($actividad) => $actividad->where('estado', 'Finalizado'));
            });
    }

    private function sesionCalificableAprendiz(int $id): Cronograma
    {
        $usuario = Auth::user()->load('person.apprentices');
        $aprendizIds = $usuario->person?->apprentices->pluck('id')->filter()->values() ?? collect();
        $fichaIds = $usuario->person?->apprentices->pluck('course_id')->filter()->unique()->values() ?? collect();

        return $this->sesionesCalificablesAprendiz($aprendizIds, $fichaIds)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function asesoriaCalificableAprendiz(int $id): Asesoria
    {
        return Asesoria::with(['categoria', 'instructor.person'])
            ->where('id_asesoria', $id)
            ->where('id_usuario_aprendiz', Auth::id())
            ->where('estado', 'Realizada')
            ->firstOrFail();
    }

    public function asesoriasAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $categorias = CategoriaAsesoria::where('estado', 'Activo')->orderBy('nombre')->get();
        $asesorias = Asesoria::with(['categoria', 'instructor.person', 'historial'])
            ->where('id_usuario_aprendiz', Auth::id())
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        return view('controlecp::aprendiz.asesorias', compact('categorias', 'asesorias'));
    }

    public function instructoresDisponiblesAprendiz(Request $request)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $datos = $request->validate([
            'id_categoria_asesoria' => ['required', 'integer', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        $instructores = $this->instructoresDisponiblesParaAsesoria($datos);

        return response()->json([
            'instructores' => $instructores->map(fn (User $instructor) => [
                'id' => $instructor->id,
                'nombre' => $instructor->full_name,
            ])->values(),
            'mensaje' => $instructores->isEmpty() ? 'No hay instructores disponibles' : null,
        ]);
    }

    public function storeAsesoriaAprendiz(Request $request)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $datos = $request->validate([
            'id_categoria_asesoria' => ['required', 'integer', 'exists:categorias_asesoria,id_categoria_asesoria'],
            'id_usuario_instructor' => ['required', 'integer', 'exists:users,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'motivo' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $categoria = CategoriaAsesoria::where('id_categoria_asesoria', $datos['id_categoria_asesoria'])
            ->where('estado', 'Activo')
            ->firstOrFail();
        $instructores = $this->instructoresDisponiblesParaAsesoria($datos);
        abort_unless($instructores->contains('id', (int) $datos['id_usuario_instructor']), 422, 'El instructor no está disponible en ese horario.');

        $duplicada = Asesoria::where('id_usuario_aprendiz', Auth::id())
            ->where('id_categoria_asesoria', $categoria->id_categoria_asesoria)
            ->whereIn('estado', ['Solicitada', 'Programada'])
            ->exists();
        if ($duplicada) {
            return back()->withInput()->with('error', 'Ya tienes una solicitud pendiente para esta categoría.');
        }

        $asesoria = DB::transaction(function () use ($datos) {
            $ocupada = Asesoria::where('id_usuario_instructor', $datos['id_usuario_instructor'])
                ->whereDate('fecha', $datos['fecha'])
                ->whereIn('estado', ['Solicitada', 'Programada'])
                ->where('hora_inicio', '<', $datos['hora_fin'])
                ->where('hora_fin', '>', $datos['hora_inicio'])
                ->lockForUpdate()
                ->exists();
            $actividadOcupada = DB::table('cronograma')
                ->where('id_instructor', $datos['id_usuario_instructor'])
                ->whereDate('fecha', $datos['fecha'])
                ->where('estado', '!=', 'Cancelada')
                ->where('hora_inicio', '<', $datos['hora_fin'])
                ->where('hora_fin', '>', $datos['hora_inicio'])
                ->lockForUpdate()
                ->exists();
            if ($ocupada || $actividadOcupada) {
                abort(422, 'El horario seleccionado ya está ocupado.');
            }

            return Asesoria::create([
                'id_categoria_asesoria' => $datos['id_categoria_asesoria'],
                'id_usuario_aprendiz' => Auth::id(),
                'id_usuario_instructor' => $datos['id_usuario_instructor'],
                'id_usuario_registro' => Auth::id(),
                'motivo' => $datos['motivo'],
                'fecha' => $datos['fecha'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'estado' => 'Programada',
                'fecha_solicitud' => now(),
            ]);
        });

        app(NotificacionService::class)->crear([
            'id_usuario_receptor' => $asesoria->id_usuario_instructor,
            'id_usuario_causante' => Auth::id(),
            'modulo' => 'Asesorías',
            'titulo' => 'Nueva asesoría asignada',
            'mensaje' => 'Tienes una nueva asesoría programada.',
            'referencia_tipo' => 'asesoria',
            'referencia_id' => $asesoria->id_asesoria,
            'clave_evento' => 'asesoria-aprendiz-'.$asesoria->id_asesoria,
        ]);

        return redirect()->route('controlecp.aprendiz.asesorias')
            ->with('success', 'La asesoría fue reservada correctamente.');
    }

    public function cancelarAsesoriaAprendiz(Request $request, int $id)
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;

        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $asesoria = Asesoria::where('id_asesoria', $id)
            ->where('id_usuario_aprendiz', Auth::id())
            ->whereIn('estado', ['Solicitada', 'Programada'])
            ->firstOrFail();

        DB::transaction(function () use ($asesoria, $datos): void {
            $asesoria->update([
                'estado' => 'Cancelada',
                'motivo_cancelacion' => $datos['motivo'],
                'id_usuario_cancelador' => Auth::id(),
            ]);
            AsesoriaHistorial::create([
                'id_asesoria' => $asesoria->id_asesoria,
                'id_usuario' => Auth::id(),
                'accion' => 'cancelar',
                'motivo' => $datos['motivo'],
                'fecha' => now(),
            ]);
        });

        if ($asesoria->id_usuario_instructor) {
            app(NotificacionService::class)->crear([
                'id_usuario_receptor' => $asesoria->id_usuario_instructor,
                'id_usuario_causante' => Auth::id(),
                'modulo' => 'Asesorías',
                'titulo' => 'Asesoría cancelada',
                'mensaje' => 'Una asesoría programada fue cancelada por el aprendiz.',
                'referencia_tipo' => 'asesoria',
                'referencia_id' => $asesoria->id_asesoria,
                'clave_evento' => 'asesoria-aprendiz-cancelada-'.$asesoria->id_asesoria,
            ]);
        }

        return back()->with('success', 'La asesoría fue cancelada y el horario quedó disponible.');
    }

    private function instructoresDisponiblesParaAsesoria(array $datos)
    {
        $fecha = \Carbon\Carbon::parse($datos['fecha']);
        $dia = $fecha->dayOfWeekIso;
        $trimestre = (int) ceil($fecha->month / 3);

        return User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.instructor'))
            ->whereExists(function ($query) use ($datos): void {
                $query->select(DB::raw(1))
                    ->from('instructor_categoria')
                    ->whereColumn('instructor_categoria.id_usuario', 'users.id')
                    ->where('id_categoria_asesoria', $datos['id_categoria_asesoria']);
            })
            ->whereExists(function ($query) use ($fecha, $dia, $trimestre, $datos): void {
                $query->select(DB::raw(1))
                    ->from('disponibilidades_instructor')
                    ->whereColumn('disponibilidades_instructor.id_usuario', 'users.id')
                    ->where('anio', $fecha->year)
                    ->where('trimestre', $trimestre)
                    ->where('dia_semana', $dia)
                    ->where('vigente', true)
                    ->where('habilitada', true)
                    ->where('hora_inicio', '<=', $datos['hora_inicio'])
                    ->where('hora_fin', '>=', $datos['hora_fin']);
            })
            ->whereNotExists(function ($query) use ($datos): void {
                $query->select(DB::raw(1))
                    ->from('asesorias')
                    ->whereColumn('asesorias.id_usuario_instructor', 'users.id')
                    ->whereDate('fecha', $datos['fecha'])
                    ->whereIn('estado', ['Solicitada', 'Programada'])
                    ->where('hora_inicio', '<', $datos['hora_fin'])
                    ->where('hora_fin', '>', $datos['hora_inicio']);
            })
            ->whereNotExists(function ($query) use ($datos): void {
                $query->select(DB::raw(1))
                    ->from('cronograma')
                    ->whereColumn('cronograma.id_instructor', 'users.id')
                    ->whereDate('fecha', $datos['fecha'])
                    ->where('estado', '!=', 'Cancelada')
                    ->where('hora_inicio', '<', $datos['hora_fin'])
                    ->where('hora_fin', '>', $datos['hora_inicio']);
            })
            ->with('person')
            ->orderBy('id')
            ->get();
    }

    public function pqrAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.pqr');
    }
}
