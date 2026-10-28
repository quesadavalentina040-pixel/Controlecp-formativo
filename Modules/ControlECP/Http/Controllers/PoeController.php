<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\Poe;
use Modules\ControlECP\Entities\PoeProgramacion;
use Modules\ControlECP\Entities\PoeVersion;
use Modules\ControlECP\Http\Requests\PoeRequest;
use Modules\ControlECP\Http\Requests\PoeVersionRequest;
use Modules\ControlECP\Services\PoeService;

class PoeController extends Controller // Controlador de consulta y gestión del repositorio y las versiones de POE.
{
    protected PoeService $poeService;

    public function __construct(PoeService $poeService) // __construct(): Recibe y conserva el servicio de POE; se ejecuta al construir el controlador.
    {
        $this->poeService = $poeService; // Conserva el servicio inyectado para reutilizar su lógica de negocio en las acciones.
    }

    /**
     * Verifica que el usuario tenga rol de administrador de Control ECP.
     */
    private function verificarAccesoAdmin() // verificarAccesoAdmin(): Comprueba el rol administrador antes de gestionar POE; se usa en las acciones protegidas.
    {
        $usuario = Auth::user(); // Auth::user() devuelve el usuario autenticado o null si no hay sesión.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Comprueba sesión y rol; si alguno falta, devuelve una redirección de acceso denegado.
            return redirect() // route() crea la URL nombrada y with() guarda un mensaje temporal para la siguiente página.
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión del POE.');
        }
        return null; // null indica que la comprobación pasó y se puede continuar.
    }

    /**
     * 1. Lista de POE (Repositorio > POE)
     */
    public function index(Request $request) // index(): Consulta y presenta POE y sus datos relacionados; se usa al abrir el repositorio administrativo.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Detiene la acción y devuelve la redirección si el usuario no es administrador.
            return $res;
        }

        $buscar = $request->input('buscar'); // input() lee el término opcional de búsqueda enviado por el formulario.
        $poes = $this->poeService->listarPoes($buscar); // El servicio consulta los POE que coinciden con la búsqueda y sus datos.
        $lideres = $this->poeService->obtenerLideres(); // Obtiene el catálogo de líderes disponibles para la interfaz.

        $totalPoes = Poe::count(); // Contadores para badges e indicadores | count() ejecuta un conteo SQL de todos los POE.
        $programacionesVigentes = PoeProgramacion::where('cancelada', false) // where() agrega condiciones SQL; count() cuenta solo programaciones vigentes en esos estados.
            ->whereIn('estado', ['Pendiente', 'Atrasada'])
            ->count();
        $programacionesAtrasadas = PoeProgramacion::where('cancelada', false) // Cuenta por separado las programaciones que están atrasadas y no canceladas.
            ->where('estado', 'Atrasada')
            ->count();

        $todasProgramaciones = PoeProgramacion::with(['poe', 'liderActual.person', 'nuevoLider.person']) // Obtener todas las programaciones para el modal general de programaciones | with() carga relaciones junto con cada programación para evitar consultas repetidas.
            ->orderBy('id_poe_programacion', 'desc') // orderBy() ordena por identificador descendente, mostrando primero las más recientes.
            ->get(); // get() ejecuta la consulta y obtiene todos los resultados.

        return view('controlecp::administrador.repositorio.poe', compact( // Renderiza la vista; compact() empaqueta las variables anteriores usando sus nombres.
            'poes',
            'lideres',
            'buscar',
            'totalPoes',
            'programacionesVigentes',
            'programacionesAtrasadas',
            'todasProgramaciones'
        ));
    }

    /**
     * 3. Subir POE nuevo (modal)
     */
    public function store(PoeRequest $request) // store(): Valida y crea un POE; se usa al enviar el formulario de registro.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Si el usuario no tiene permisos, devuelve inmediatamente la respuesta de acceso.
            return $res;
        }

        try {
            $archivo = $request->file('archivo'); // file() extrae el archivo subido desde la solicitud validada.
            $poe = $this->poeService->crearPoe($request->validated(), $archivo, Auth::user()); // validated() entrega solo campos aprobados; el servicio crea el POE con archivo y usuario actual.

            return redirect() // Si el registro terminó bien, vuelve al listado y muestra un mensaje temporal.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('success', "¡POE {$poe->codigo} subido exitosamente con versión inicial 1.0!");
        } catch (\Throwable $e) {
            return redirect() // Captura cualquier error, conserva lo enviado y muestra la causa en el formulario anterior.
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar el POE: ' . $e->getMessage());
        }
    }

    /**
     * 6. Subir nueva versión de un POE
     */
    public function nuevaVersion(PoeVersionRequest $request, $id) // nuevaVersion(): Valida y registra una nueva versión de un POE; se usa al cargar una revisión actualizada.
    {
        $poe = Poe::with('programacionVigente')->findOrFail($id); // Carga el POE junto con su programación vigente; findOrFail produce 404 si no existe.
        $usuario = Auth::user(); // Recupera la cuenta autenticada para verificar permisos y registrar quién hace el cambio.

        if (!$usuario->hasRole('controlecp.admin') && $poe->id_usuario != $usuario->id) { // Permitir si es Admin o si es el líder asignado al POE | Solo permite continuar al administrador o al usuario asignado como responsable del POE.
            return redirect() // Redirige a la página principal con un aviso si no cumple ninguna de las dos condiciones.
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para subir una versión de este POE.');
        }

        try {
            $archivo = $request->file('archivo'); // Obtiene el documento nuevo que llegó en la solicitud.
            $nuevaVersion = $this->poeService->subirNuevaVersion($poe, $request->validated(), $archivo, $usuario); // El servicio valida y registra la versión vinculada al POE y al usuario actual.

            return redirect() // Regresa al repositorio y confirma qué versión se creó.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('success', "¡Nueva versión {$nuevaVersion->numero_version} del POE {$poe->codigo} registrada exitosamente!");
        } catch (\Throwable $e) {
            return redirect() // En caso de error, vuelve al formulario conservando los campos enviados y muestra el motivo.
                ->back()
                ->withInput()
                ->with('error', 'Error al subir la nueva versión: ' . $e->getMessage());
        }
    }

    /**
     * 5. Ver archivo en el navegador (PDF se previsualiza; Word se avisa)
     */
    public function ver($idVersion) // ver(): Busca y presenta el contenido de una versión; se usa al consultar un POE específico.
    {
        $version = PoeVersion::with('poe')->findOrFail($idVersion); // Carga la versión y su POE relacionado; findOrFail devuelve 404 si el identificador no existe.

        if ($version->formato === 'Word' || str_ends_with(strtolower($version->archivo), '.docx') || str_ends_with(strtolower($version->archivo), '.doc')) { // Detecta documentos Word por formato o extensión, pues el navegador no puede previsualizarlos aquí.
            return redirect() // Dirige al listado y explica que el archivo debe descargarse.
                ->route('controlecp.administrador.repositorio.poe.index')
                ->with('warning', 'Los archivos de Word no pueden previsualizarse en el navegador. Por favor utiliza el botón de descarga.');
        }

        if (!Storage::disk('public')->exists($version->archivo)) { // exists() revisa en el disco público que el archivo físico todavía esté disponible.
            abort(404, 'El archivo solicitado no se encuentra en el servidor.'); // abort(404) interrumpe la acción y responde que el recurso no fue encontrado.
        }

        $path = Storage::disk('public')->path($version->archivo); // Convierte la ruta relativa del almacenamiento público en una ruta local del servidor.
        return response()->file($path, [ // Envía el archivo como respuesta HTTP e indica al navegador que presente el PDF en línea.
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $version->poe->codigo . '_v' . $version->numero_version . '.pdf"',
        ]);
    }

    /**
     * 5. Descargar archivo con nombre amigable: [codigo]_v[numero].[ext]
     */
    public function descargar($idVersion) // descargar(): Entrega el archivo de la versión solicitada; se usa al descargar el documento del POE.
    {
        $version = PoeVersion::with('poe')->findOrFail($idVersion); // Carga la versión junto a su POE para obtener archivo, código y número.

        if (!Storage::disk('public')->exists($version->archivo)) { // Verifica primero que el documento exista en el disco público.
            abort(404, 'El archivo no existe en el almacenamiento.');
        }

        $ext = pathinfo($version->archivo, PATHINFO_EXTENSION) ?: ($version->formato === 'Word' ? 'docx' : 'pdf'); // Extrae la extensión original o elige una predeterminada según el formato registrado.
        $nombreDescarga = "{$version->poe->codigo}_v{$version->numero_version}.{$ext}"; // Construye un nombre reconocible con el código del POE y el número de versión.

        return Storage::disk('public')->download($version->archivo, $nombreDescarga); // download() responde con el archivo y el nombre sugerido al navegador.
    }

    /**
     * 7. Obtener historial de versiones en JSON para el modal
     */
    public function historial($id) // historial(): Consulta el historial de versiones de un POE; se usa al revisar sus cambios.
    {
        $poe = Poe::findOrFail($id); // Busca el POE y devuelve 404 si no existe.
        $versiones = PoeVersion::where('id_poe', $poe->id_poe) // where() filtra versiones por POE; with() carga autor y persona relacionada; orderBy() ordena de nuevo a antiguo.
            ->with('usuario.person')
            ->orderBy('id_poe_version', 'desc')
            ->get() // get() ejecuta la consulta; map() transforma cada resultado en los campos que necesita la interfaz.
            ->map(function ($v) {
                return [ // Entrega datos de versión y rutas nombradas para consultar o descargar cada archivo.
                    'id_poe_version' => $v->id_poe_version,
                    'numero_version' => $v->numero_version,
                    'fecha'          => $v->fecha ? $v->fecha->format('d/m/Y H:i') : '',
                    'usuario'        => $v->usuario ? $this->poeService->nombreCompletoUsuario($v->usuario) : 'Sistema',
                    'cambio'         => $v->cambio,
                    'formato'        => $v->formato,
                    'es_actual'      => $v->es_actual,
                    'url_ver'        => route('controlecp.administrador.repositorio.poe.ver', $v->id_poe_version),
                    'url_descargar'  => route('controlecp.administrador.repositorio.poe.descargar', $v->id_poe_version),
                ];
            });

        return response()->json([ // Serializa el POE y la colección transformada como respuesta JSON.
            'poe'       => $poe,
            'versiones' => $versiones,
        ]);
    }

    /**
     * 15. Historial de líderes de un POE (o de todos)
     */
    public function lideresHistorial($id) // lideresHistorial(): Consulta el historial de líderes asociados al POE; se usa al revisar sus asignaciones.
    {
        $poe = Poe::with('liderActual.person')->findOrFail($id); // Carga el POE con la relación del líder actual y falla con 404 si no existe.
        $datos = $this->poeService->obtenerHistorialLideres($poe); // El servicio reúne los períodos de liderazgo y las versiones vinculadas.

        $periodos = $datos['historial']->map(function ($h) { // map() convierte cada período a datos legibles; las fechas nulas indican vigencia actual.
            return [ // Prepara un arreglo con responsable, fechas y marca de asignación vigente.
                'id_usuario'   => $h->id_usuario,
                'nombre_lider' => $h->usuario ? $this->poeService->nombreCompletoUsuario($h->usuario) : 'N/A',
                'fecha_inicio' => $h->fecha_inicio ? $h->fecha_inicio->format('d/m/Y') : '',
                'fecha_fin'    => $h->fecha_fin ? $h->fecha_fin->format('d/m/Y') : 'Actual',
                'es_actual'    => is_null($h->fecha_fin),
            ];
        });

        $versiones = $datos['versiones']->map(function ($v) { // Convierte el historial de versiones a la estructura que consume la interfaz.
            return [ // Conserva los datos relevantes y presenta valores alternos si falta usuario o fecha.
                'id_usuario'     => $v->id_usuario,
                'numero_version' => $v->numero_version,
                'fecha'          => $v->fecha ? $v->fecha->format('d/m/Y H:i') : '',
                'cambio'         => $v->cambio,
                'usuario'        => $v->usuario ? $this->poeService->nombreCompletoUsuario($v->usuario) : 'Sistema',
                'formato'        => $v->formato,
            ];
        });

        return response()->json([ // Devuelve en JSON el POE, sus períodos de liderazgo y las versiones relacionadas.
            'poe'       => $poe,
            'lideres'   => $periodos,
            'versiones' => $versiones,
        ]);
    }
}
