<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\Apprentice;
use Modules\ControlECP\Entities\Material;
use Modules\ControlECP\Entities\Pqr;

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

        // Solicitudes PQR y Materiales críticos
        $solicitudesPendientes = Pqr::where('estado', 'Pendiente')->count();
        $totalPqrs = Pqr::count();
        $recursosCriticos      = Material::where('estado', 'Agotado')->orWhereColumn('cantidad', '<=', 'stock_minimo')->count();
        $materialesCriticos    = Material::where('estado', 'Agotado')->orWhereColumn('cantidad', '<=', 'stock_minimo')->get();

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
        return view('controlecp::instructor.cronograma');
    }

    public function momentosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.momentos');
    }

    public function actividadesInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.actividades');
    }

    public function asistenciaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.asistencia');
    }

    public function asesoriasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.asesorias');
    }

    public function certificadosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.certificados');
    }

    public function poeInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.poe');
    }

    public function acompaniamientoInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return $res;
        return view('controlecp::instructor.acompaniamiento');
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
        return view('controlecp::aprendiz.cronograma');
    }

    public function encuestaAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.encuesta');
    }

    public function asesoriasAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.asesorias');
    }

    public function pqrAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res;
        return view('controlecp::aprendiz.pqr');
    }
}
