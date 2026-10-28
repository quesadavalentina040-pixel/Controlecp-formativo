<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Entities\CategoriaAsesoria;
use Modules\ControlECP\Entities\Material;
use Modules\ControlECP\Entities\Pqr;

class ControlECPController extends Controller // Controlador de entrada y secciones de inicio para los perfiles de Control ECP.
{
    /**
     * Display a listing of the resource.
     */
    public function index() // index(): Muestra la entrada general del módulo; se usa al acceder a la ruta principal de Control ECP.
    {
        return view('controlecp::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() // create(): Presenta el formulario de creación del recurso heredado; se usa al solicitar su pantalla de alta.
    {
        return view('controlecp::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {} // store(): Recibe el envío de creación del recurso heredado; se usa al guardar desde su formulario.

    /**
     * Show the specified resource.
     */
    public function show($id) // show(): Presenta el detalle del recurso identificado; se usa al abrir su ruta de consulta.
    {
        return view('controlecp::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) // edit(): Carga el formulario de edición del recurso identificado; se usa al solicitar su pantalla de cambios.
    {
        return view('controlecp::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {} // update(): Recibe cambios del recurso heredado; se usa al guardar su formulario de edición.

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {} // destroy(): Elimina el recurso heredado identificado; se usa al solicitar su eliminación.

    /**
     * Muestra el inicio del rol Administrador.
     * Protegido: solo usuarios con rol controlecp.admin (o superadmin) pueden entrar,
     * aunque conozcan la URL directamente.
     *
     * Las consultas de PQR y Materiales se protegen con Schema::hasTable()
     * para que el panel cargue aunque esas tablas no existan en la base de datos local.
     */
    public function inicioAdmin() // inicioAdmin(): Prepara el inicio del administrador; se usa al entrar al panel con rol administrativo.
    {
        $usuario = Auth::user(); // Auth::user obtiene la cuenta autenticada para comprobar su permiso.

        if (!$usuario->hasRole('controlecp.admin')) { // hasRole verifica el rol; si no coincide, se envía al inicio con un mensaje de error.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Administrador de Control ECP.');
        }

        $solicitudesPendientes = Schema::hasTable((new Pqr)->getTable()) // Solicitudes (PQR) pendientes | hasTable comprueba la existencia física de la tabla antes de consultar; count cuenta filas sin cargarlas.
            ? Pqr::where('estado', 'Pendiente')->count()
            : 0;

        if (Schema::hasTable((new Material)->getTable())) { // Materiales críticos (agotados o en stock mínimo) | La consulta solo se ejecuta si la tabla existe; orWhereColumn compara dos columnas en SQL.
            $materialesCriticos = Material::where('estado', 'Agotado')
                ->orWhereColumn('cantidad', '<=', 'stock_minimo')
                ->get();
            $recursosCriticos = $materialesCriticos->count();
        } else {
            $materialesCriticos = collect(); // Si no hay tabla, se pasan colecciones y contadores vacíos para que el panel pueda renderizarse.
            $recursosCriticos   = 0;
        }

        return view('controlecp::administrador.inicio', compact( // compact pasa al panel los datos calculados sin repetir los nombres de las variables.
            'solicitudesPendientes',
            'recursosCriticos',
            'materialesCriticos'
        ));
    }


    /**
     * Muestra el inicio del rol Instructor.
     */
    public function inicioInstructor() // inicioInstructor(): Prepara el inicio del instructor; se usa al entrar al panel con rol de instructor autorizado.
    {
        $usuario = Auth::user(); // Obtiene el usuario actual desde la sesión autenticada.

        if (!$usuario->hasRole('controlecp.instructor')) { // Comprueba el rol y redirige a quien no tenga acceso al panel.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Instructor de Control ECP.');
        }

        return view('controlecp::instructor.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Instructor
     */
    private function verificarAccesoInstructor() // verificarAccesoInstructor(): Comprueba que el usuario tenga acceso de instructor; se usa antes de mostrar sus secciones privadas.
    {
        $usuario = Auth::user(); // Consulta quién inició sesión para autorizar el área de instructor.
        if (!$usuario->hasRole('controlecp.instructor')) { // hasRole compara el rol asignado; null se devuelve cuando puede continuar.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Instructor.');
        }
        return null;
    }

    public function cronogramaInstructor() // cronogramaInstructor(): Muestra el cronograma del instructor; se usa al abrir la sección de agenda del instructor.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Si el helper devuelve una respuesta de redirección, se detiene aquí; null permite mostrar la vista.
        return view('controlecp::instructor.cronograma'); // view renderiza la sección protegida del cronograma.
    }

    public function momentosInstructor() // momentosInstructor(): Muestra los momentos formativos asignados; se usa al consultar esa sección como instructor.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Interrumpe la acción si la comprobación de rol produjo una redirección.
        return view('controlecp::instructor.momentos'); // Renderiza la vista de momentos formativos asignados.
    }

    public function actividadesInstructor() // actividadesInstructor(): Muestra las actividades formativas del instructor; se usa al abrir su listado de actividades.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Valida primero que el visitante sea instructor.
        return view('controlecp::instructor.actividades'); // Devuelve la pantalla de actividades.
    }

    public function asistenciaInstructor() // asistenciaInstructor(): Muestra la información de asistencia del instructor; se usa al consultar esa sección.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Detiene el flujo si no se autorizó al usuario.
        return view('controlecp::instructor.asistencia'); // Muestra la pantalla de asistencia.
    }

    public function asesoriasInstructor() // asesoriasInstructor(): Muestra las asesorías del instructor; se usa al abrir su sección de asesorías.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Protege la vista de asesorías con la verificación del rol.
        return view('controlecp::instructor.asesorias'); // Presenta las asesorías asignadas al instructor.
    }

    public function certificadosInstructor() // certificadosInstructor(): Muestra los certificados disponibles; se usa al consultar certificados como instructor.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Si no se confirma el rol requerido, se devuelve la redirección del helper.
        return view('controlecp::instructor.certificados'); // Renderiza la sección de certificados.
    }

    public function poeInstructor() // poeInstructor(): Muestra los POE relacionados con el instructor; se usa al abrir su repositorio de POE.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Verifica que la petición pertenezca a un instructor autorizado.
        return view('controlecp::instructor.poe'); // Muestra el repositorio de POE.
    }

    public function acompaniamientoInstructor() // acompaniamientoInstructor(): Muestra el acompañamiento del instructor; se usa al consultar esa sección de seguimiento.
    {
        if ($res = $this->verificarAccesoInstructor()) return $res; // Evita mostrar la sección si el helper devolvió una respuesta de acceso denegado.
        return view('controlecp::instructor.acompaniamiento'); // Renderiza el área de acompañamiento.
    }

    /**
     * Muestra el inicio del rol Aprendiz.
     */
    public function inicioAprendiz() // inicioAprendiz(): Prepara el inicio del aprendiz; se usa al entrar al panel con rol de aprendiz.
    {
        $usuario = Auth::user(); // Auth obtiene la identidad de la sesión para revisar su perfil.

        if (!$usuario->hasRole('controlecp.apprentice')) { // Solo el rol de aprendiz puede acceder al panel; los demás regresan al inicio con aviso.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al panel de Aprendiz de Control ECP.');
        }

        return view('controlecp::aprendiz.inicio');
    }

    /**
     * Métodos auxiliares para las secciones del Aprendiz
     */
    private function verificarAccesoAprendiz() // verificarAccesoAprendiz(): Comprueba que el usuario tenga acceso de aprendiz; se usa antes de mostrar sus secciones privadas.
    {
        $usuario = Auth::user(); // Obtiene la cuenta que realiza la petición.
        if (!$usuario->hasRole('controlecp.apprentice')) { // hasRole comprueba el rol de aprendiz antes de permitir el acceso.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al área de Aprendiz.');
        }
        return null;
    }

    public function cronogramaAprendiz() // cronogramaAprendiz(): Muestra el cronograma del aprendiz; se usa al abrir su agenda formativa.
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res; // Una respuesta de rechazo detiene el método; null indica que el aprendiz puede seguir.
        return view('controlecp::aprendiz.cronograma'); // Muestra la vista del cronograma.
    }

    public function encuestaAprendiz() // encuestaAprendiz(): Muestra las encuestas disponibles para responder; se usa al entrar a la sección de encuestas.
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res; // Comprueba el rol antes de exponer la pantalla de encuestas.
        return view('controlecp::aprendiz.encuesta'); // Renderiza el formulario/listado de encuestas.
    }

    public function asesoriasAprendiz() // asesoriasAprendiz(): Muestra las asesorías del aprendiz; se usa al abrir su sección de asesorías.
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res; // Impide que un usuario con otro rol acceda al historial del aprendiz.

        $categorias = CategoriaAsesoria::where('estado', 'Activa')->orderBy('nombre')->get(); // where agrega una condición SQL; orderBy ordena y get ejecuta la consulta de categorías activas.
        $asesorias = Asesoria::where('id_usuario_aprendiz', Auth::id()) // Filtra por el id autenticado, carga las relaciones en bloque y pagina los resultados ordenados.
            ->with(['categoria', 'instructor.person'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate(10);

        $indicadores = [ // Cada count ejecuta un conteo SQL de las asesorías del usuario en el estado indicado.
            'solicitadas' => Asesoria::where('id_usuario_aprendiz', Auth::id())->where('estado', 'Solicitada')->count(),
            'programadas' => Asesoria::where('id_usuario_aprendiz', Auth::id())->where('estado', 'Programada')->count(),
            'realizadas'  => Asesoria::where('id_usuario_aprendiz', Auth::id())->where('estado', 'Realizada')->count(),
            'canceladas'  => Asesoria::where('id_usuario_aprendiz', Auth::id())->where('estado', 'Cancelada')->count(),
        ];

        return view('controlecp::aprendiz.asesorias', compact('categorias', 'asesorias', 'indicadores')); // Entrega categorías, resultados paginados e indicadores a la vista.
    }

    public function storeAsesoriaAprendiz(Request $request) // storeAsesoriaAprendiz(): Valida y registra una solicitud de asesoría del aprendiz; se usa al enviar el formulario de solicitud.
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res; // Verifica el rol antes de aceptar datos del formulario.

        $request->validate([ // validate aplica reglas al cuerpo de la petición y devuelve errores automáticamente si algún campo no cumple.
            'id_categoria_asesoria' => 'required|exists:categorias_asesoria,id_categoria_asesoria',
            'fecha'                 => 'required|date',
            'hora_inicio'           => 'nullable',
            'hora_fin'              => 'nullable',
            'motivo'                => 'required|string|min:5',
        ], [
            'id_categoria_asesoria.required' => 'Debes seleccionar una temática para la asesoría.',
            'fecha.required'                 => 'Debes indicar la fecha deseada.',
            'motivo.required'                => 'El motivo es obligatorio.',
            'motivo.min'                     => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $hInicio = $request->filled('hora_inicio') ? $request->hora_inicio : '08:00:00'; // filled distingue un valor presente y no vacío; si falta, se asignan horas predeterminadas.
        $hFin = $request->filled('hora_fin') ? $request->hora_fin : '09:00:00';

        $asesoria = new Asesoria(); // Crea el modelo en memoria; las siguientes asignaciones preparan los datos que se guardarán.
        $asesoria->id_categoria_asesoria = $request->id_categoria_asesoria;
        $asesoria->id_usuario_aprendiz   = Auth::id();
        $asesoria->id_usuario_instructor = null;
        $asesoria->id_usuario_registro   = Auth::id();
        $asesoria->motivo                = $request->motivo;
        $asesoria->fecha                 = $request->fecha;
        $asesoria->hora_inicio           = $hInicio;
        $asesoria->hora_fin              = $hFin;
        $asesoria->estado                = 'Solicitada';
        $asesoria->fecha_solicitud       = Carbon::now();
        $asesoria->save(); // save inserta la solicitud nueva en la base de datos.

        return redirect()->route('controlecp.aprendiz.asesorias') // Redirige al área del aprendiz y agrega una notificación temporal de éxito.
            ->with('success', '¡Tu solicitud de asesoría ha sido registrada exitosamente! Se notificará cuando sea asignada.');
    }

    public function pqrAprendiz() // pqrAprendiz(): Muestra el acceso de PQR para el aprendiz; se usa al abrir esa sección del panel.
    {
        if ($res = $this->verificarAccesoAprendiz()) return $res; // La ruta se mantiene disponible únicamente para el rol de aprendiz.
        return view('controlecp::aprendiz.pqr'); // Entrega la pantalla de PQR al usuario autorizado.
    }
}