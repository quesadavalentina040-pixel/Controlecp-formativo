<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.instructor')) {
            return /* Devuelve el resultado de este mï¿½todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Instructor de Control ECP.');
        }

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.inicio');
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

    public function cronogramaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.cronograma');
    }

    public function momentosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.momentos');
    }

    public function actividadesInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.actividades');
    }

    public function asistenciaInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.asistencia');
    }

    public function asesoriasInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.asesorias');
    }

    public function certificadosInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.certificados');
    }

    public function poeInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.poe');
    }

    public function acompaniamientoInstructor()
    {
        if ($res = $this->verificarAccesoInstructor()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::instructor.acompaniamiento');
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

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.inicio');
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
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.cronograma');
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

    public function pqrAprendiz()
    {
        if ($res = $this->verificarAccesoAprendiz()) return /* Devuelve el resultado de este método. */ $res;
        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::aprendiz.pqr');
    }
}
