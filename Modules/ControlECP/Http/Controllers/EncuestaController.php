<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\ControlECP\Entities\Actividad;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Entities\OpcionPregunta;
use Modules\ControlECP\Entities\Pregunta;
use Modules\ControlECP\Http\Requests\EncuestaRequest;

class EncuestaController extends Controller // Controlador de administración de encuestas, preguntas y opciones de respuesta.
{
    /**
     * Verifica acceso exclusivo para rol Administrador de Control ECP
     */
    private function verificarAccesoAdmin() // verificarAccesoAdmin(): Comprueba el rol administrador antes de permitir la gestión; se usa en las acciones protegidas.
    {
        $usuario = Auth::user(); // Auth::user() obtiene el usuario que inició sesión; si no hay sesión, devuelve null.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Comprueba que exista usuario y tenga el rol de administrador de Control ECP.
            return redirect()
                ->route('controlecp.index') // route() crea la redirección usando el nombre de ruta, no una URL escrita manualmente.
                ->with('error', 'No tienes permisos para acceder a la gestión de Encuestas.'); // with() guarda un mensaje temporal para mostrarlo tras redirigir.
        }
        return null; // null indica que el usuario sí tiene acceso y la acción puede continuar.
    }

    /**
     * 1. Listado de Encuestas con filtros, buscador e indicadores (HU-009)
     */
    public function index(Request $request) // index(): Consulta encuestas, filtros, indicadores y actividades; se usa al abrir el repositorio administrativo. | Request $request es la petición HTTP inyectada por Laravel; permite leer filtros enviados en la URL o el formulario.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            return $res;
        }

        $query = Encuesta::with(['actividades', 'preguntas.opciones']) // $query es el constructor de consulta Eloquent; with() carga relaciones junto con cada encuesta.
            ->withCount(['preguntas', 'actividades']); // withCount() añade al resultado el total de preguntas y actividades asociadas.

        if ($request->filled('estado') && in_array(strtolower($request->estado), ['borrador', 'activa', 'inactiva'])) { // filled() verifica que estado exista y no esté vacío; in_array() limita valores aceptados. | Filtro por Estado (Borrador | Activa | Inactiva)
            $query->where('estado', ucfirst(strtolower($request->estado))); // where() agrega la condición SQL estado = valor; ucfirst() adapta el formato guardado.
        }

        if ($request->filled('buscar')) { // filled() comprueba que la petición traiga un texto de búsqueda no vacío. | Buscador por nombre o descripción
            $buscar = trim($request->buscar); // request contiene los datos enviados; trim() quita espacios al inicio y al final.
            $query->where(function ($q) use ($buscar) { // La función agrupa condiciones entre paréntesis; use ($buscar) comparte el término con el grupo.
                $q->where('nombre', 'LIKE', "%{$buscar}%") // where() inicia el grupo: buscar coincidencias parciales en el nombre.
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%"); // orWhere() agrega una alternativa OR: también buscar en la descripción.
            });
        }

        $encuestas = $query->orderBy('fecha_creacion', 'desc') // orderBy() ordena por fecha; desc significa descendente, primero lo más reciente.
                           ->orderBy('id_encuesta', 'desc') // Segundo criterio de orden cuando dos fechas coinciden: ID descendente.
                           ->get(); // get() ejecuta el SELECT y devuelve una colección con todas las filas coincidentes.

        $indicadores = [ // Indicadores cuantitativos para las tarjetas superiores
            'total'      => Encuesta::count(), // count() cuenta todos los registros de encuestas.
            'activas'    => Encuesta::where('estado', 'Activa')->count(), // where() filtra por estado y count() cuenta las activas.
            'borradores' => Encuesta::where('estado', 'Borrador')->count(), // Cuenta las encuestas cuyo estado es Borrador.
            'inactivas'  => Encuesta::where('estado', 'Inactiva')->count(), // Cuenta las encuestas cuyo estado es Inactiva.
        ];

        $actividadesDisponibles = Actividad::orderBy('numero', 'asc')->get(); // Ordena actividades por número ascendente y get() obtiene todos los resultados. | Catálogo de actividades formativas para asociar en modal

        return view('controlecp::administrador.repositorio.encuesta', compact('encuestas', 'indicadores', 'actividadesDisponibles')); // view() renderiza Blade; compact() prepara variables con esos nombres para la vista.
    }

    /**
     * 2. Crear nueva encuesta (Estado inicial: Borrador, fecha_creacion automática, id_usuario = auth)
     */
    public function store(EncuestaRequest $request) // store(): Valida y crea una encuesta junto con sus asociaciones; se usa al enviar el formulario de alta. | EncuestaRequest extiende Request y aplica sus reglas de validación antes de entrar a este método.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            return $res;
        }

        DB::beginTransaction(); // Inicia una transacción: los cambios se confirman juntos o se deshacen juntos.
        try {
            $encuesta = Encuesta::create([ // create() crea y guarda una encuesta usando los campos que siguen.
                'id_usuario'     => Auth::id() ?? 1, // Auth::id() obtiene el ID del usuario conectado; ?? usa 1 si no hay ID.
                'nombre'         => trim($request->nombre), // Toma nombre de la petición y quita espacios sobrantes.
                'descripcion'    => trim($request->descripcion ?? ''), // Usa la descripción enviada o texto vacío si falta, y elimina espacios externos.
                'estado'         => 'Borrador',
                'fecha_creacion' => now(),
            ]);

            if ($request->filled('actividades_ids') && is_array($request->actividades_ids)) { // Verifica que se enviaron IDs y que llegaron como arreglo. | Asociar actividades seleccionadas
                Actividad::whereIn('id', $request->actividades_ids)->update([ // whereIn() filtra actividades cuyos ID estén en la lista; update() guarda su nueva asociación.
                    'id_encuesta' => $encuesta->id_encuesta,
                    'encuesta_id' => $encuesta->id_encuesta,
                ]);
            }

            DB::commit(); // Confirma y persiste todos los cambios de la transacción.

            return redirect()
                ->route('controlecp.administrador.repositorio.encuesta.index', ['abrir' => $encuesta->id_encuesta]) // Redirige al listado y envía el ID para abrir el detalle de la nueva encuesta.
                ->with('success', 'La encuesta "' . $encuesta->nombre . '" fue creada en estado Borrador. Ahora puedes agregar sus preguntas.'); // with() adjunta un aviso temporal de éxito.

        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, revierte todos los cambios hechos desde beginTransaction().
            return redirect()
                ->back()
                ->withInput() // Conserva los valores del formulario para que no haya que escribirlos de nuevo.
                ->with('error', 'Ocurrió un error al crear la encuesta: ' . $e->getMessage()); // Muestra el mensaje de error devuelto por la excepción.
        }
    }

    /**
     * 5. Ver detalle de la encuesta (JSON para visualización modal / AJAX de solo lectura)
     */
    public function show($id) // show(): Carga el detalle y capacidades de una encuesta en JSON; se usa para la consulta modal o AJAX. | $id es el identificador de encuesta recibido desde el parámetro de la ruta.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $encuesta = Encuesta::with(['actividades', 'preguntas.opciones']) // with() carga actividades, preguntas y opciones relacionadas.
            ->withCount(['preguntas', 'actividades']) // Añade los conteos de relaciones al modelo consultado.
            ->findOrFail($id); // Busca por clave primaria; si no existe, Laravel responde con error 404.

        return response()->json([ // Convierte el arreglo en respuesta JSON para el navegador o una llamada AJAX.
            'success'                   => true,
            'encuesta'                  => $encuesta,
            'tiene_respuestas'          => $encuesta->tieneRespuestas(),
            'total_respuestas'          => $encuesta->totalRespuestas(),
            'puede_modificar_preguntas' => $encuesta->puedeModificarPreguntas(),
        ]);
    }

    /**
     * 6. Editar encuesta (Nombre, descripción y asociación de actividades)
     * Si la encuesta ya tiene respuestas: solo permite nombre y descripción.
     */
    public function update(EncuestaRequest $request, $id) // update(): Actualiza los datos y actividades vinculadas; se usa al guardar la edición de una encuesta. | Request $request contiene los datos nuevos; $id indica la encuesta que se va a editar.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            return $res;
        }

        $encuesta = Encuesta::findOrFail($id); // Recupera la encuesta por ID o detiene la solicitud con error 404.
        $tieneRespuestas = $encuesta->tieneRespuestas(); // Llama al método del modelo que comprueba si ya existen respuestas.

        DB::beginTransaction(); // Agrupa la edición de la encuesta y sus actividades en una sola transacción.
        try {
            $encuesta->update([ // update() cambia y guarda los campos permitidos de la encuesta existente.
                'nombre'      => trim($request->nombre),
                'descripcion' => trim($request->descripcion ?? ''),
            ]);

            $nuevasActividades = $request->input('actividades_ids', []); // input() lee ese dato enviado; el segundo argumento es el valor por defecto. | Actualizar relación con actividades

            Actividad::where('id_encuesta', $encuesta->id_encuesta) // Inicia consulta de actividades actualmente ligadas a esta encuesta. | Desvincular las que ya no están seleccionadas
                ->whereNotIn('id', $nuevasActividades) // whereNotIn() selecciona las actividades cuyo ID no aparece en la nueva lista.
                ->update([ // update() desvincula en una sola consulta todas las filas seleccionadas.
                    'id_encuesta' => null,
                    'encuesta_id' => null,
                ]);

            if (!empty($nuevasActividades)) { // Solo busca y asocia actividades si la lista recibida tiene elementos. | Vincular las seleccionadas
                Actividad::whereIn('id', $nuevasActividades)->update([ // whereIn() filtra por la lista de IDs y update() asigna la encuesta.
                    'id_encuesta' => $encuesta->id_encuesta,
                    'encuesta_id' => $encuesta->id_encuesta,
                ]);
            }

            DB::commit(); // Confirma los cambios después de guardar la encuesta y sus asociaciones.

            $mensaje = 'La encuesta "' . $encuesta->nombre . '" se actualizó correctamente.';
            if ($tieneRespuestas) { // Agrega una advertencia si la encuesta ya tiene respuestas y sus preguntas están protegidas.
                $mensaje .= ' (Nota: las preguntas y opciones no pueden ser modificadas porque ya existen respuestas de aprendices).';
            }

            return redirect()
                ->route('controlecp.administrador.repositorio.encuesta.index') // Vuelve a la página del listado de encuestas.
                ->with('success', $mensaje); // Entrega el mensaje temporal para mostrarlo en la siguiente página.

        } catch (\Exception $e) {
            DB::rollBack(); // Revierte toda la transacción si ocurrió una excepción durante la actualización.
            return redirect()
                ->back()
                ->withInput() // Devuelve al formulario conservando los valores ingresados.
                ->with('error', 'Error al actualizar la encuesta: ' . $e->getMessage()); // Muestra el motivo de la excepción.
        }
    }

    /**
     * 7. Activar encuesta
     * Regla: Solo si la encuesta tiene al menos 1 pregunta registrada
     */
    public function activar(Request $request, $id) // activar(): Comprueba requisitos y activa una encuesta; se usa al solicitar su publicación. | Request $request permite decidir si la respuesta debe ser JSON; $id identifica la encuesta.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            return $res;
        }

        $encuesta = Encuesta::withCount('preguntas')->findOrFail($id); // Cuenta preguntas sin cargarlas y busca la encuesta; falla con 404 si no existe.

        if ($encuesta->preguntas_count === 0) { // Bloquea la activación cuando withCount encontró cero preguntas. | Validación HU-009: al menos 1 pregunta
            $mensaje = 'No es posible activar la encuesta "' . $encuesta->nombre . '" porque no contiene ninguna pregunta registrada. Agrega al menos una pregunta antes de activarla.';
            if ($request->wantsJson()) { // wantsJson() detecta si quien llama espera respuesta JSON, por ejemplo una solicitud AJAX.
                return response()->json(['success' => false, 'message' => $mensaje], 422);
            }
            return redirect()->back()->with('error', $mensaje); // Para formularios normales, regresa a la página anterior con el error.
        }

        $encuesta->update(['estado' => 'Activa']); // update() cambia el estado en la base de datos.

        $mensaje = '¡La encuesta "' . $encuesta->nombre . '" ha sido activada exitosamente y ya puede ser vinculada a actividades!';
        if ($request->wantsJson()) { // Devuelve JSON si el cliente lo solicitó.
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        return redirect()->route('controlecp.administrador.repositorio.encuesta.index')->with('success', $mensaje); // Si es formulario, redirige al listado y envía el aviso de éxito.
    }

    /**
     * 8. Desactivar encuesta
     * Regla: NO permitir desactivar si está asociada a actividades con programaciones pendientes
     */
    public function desactivar(Request $request, $id) // desactivar(): Desactiva una encuesta; se usa al retirarla de disponibilidad. | Request $request contiene encabezados/datos de la llamada; $id identifica la encuesta solicitada.
    {
        if ($res = $this->verificarAccesoAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            return $res;
        }

        $encuesta = Encuesta::findOrFail($id); // Busca la encuesta indicada y devuelve 404 si el ID no corresponde a una.

        if ($encuesta->tieneProgramacionesPendientes()) { // Consulta el modelo para impedir desactivar si hay sesiones pendientes o en curso. | Validación HU-009: no desactivar si tiene programaciones pendientes en cronograma
            $actividades = $encuesta->nombresActividadesConProgramacionesPendientes()->implode(', '); // Obtiene nombres y los une en una sola cadena separados por comas.
            $mensaje = "No es posible desactivar la encuesta '{$encuesta->nombre}' porque está vinculada a la(s) actividad(es): [{$actividades}], la(s) cual(es) tienen sesiones pendientes o en proceso en el cronograma.";

            if ($request->wantsJson()) { // Elige respuesta JSON cuando la petición proviene de AJAX/API.
                return response()->json(['success' => false, 'message' => $mensaje], 422);
            }
            return redirect()->back()->with('error', $mensaje); // En navegación normal, vuelve al formulario con el mensaje.
        }

        $encuesta->update(['estado' => 'Inactiva']); // Guarda el cambio de estado sin borrar preguntas ni respuestas.

        $mensaje = 'La encuesta "' . $encuesta->nombre . '" fue desactivada (Inactiva). Sus preguntas y respuestas históricas se conservan intactas.';
        if ($request->wantsJson()) { // Responde en JSON para llamadas asíncronas.
            return response()->json(['success' => true, 'message' => $mensaje]);
        }

        return redirect()->route('controlecp.administrador.repositorio.encuesta.index')->with('success', $mensaje); // Para formulario, regresa al listado con confirmación.
    }

    /**
     * 3. Agregar pregunta a una encuesta
     * Tipos exactos: Opcion_multiple | Escala | Texto
     */
    public function agregarPregunta(Request $request, $id) // agregarPregunta(): Valida y agrega una pregunta con sus opciones; se usa al administrar preguntas de una encuesta. | Request $request trae los campos del formulario/AJAX; $id identifica a qué encuesta pertenecerá la pregunta.
    {
        $usuario = Auth::user(); // Lee quién está conectado para validar permisos de esta solicitud.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Rechaza la operación si no hay sesión o no tiene rol administrativo.
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $encuesta = Encuesta::findOrFail($id); // Busca el registro de encuesta; findOrFail detiene con 404 si no existe.

        if ($encuesta->tieneRespuestas()) { // Revisa mediante el modelo si cambiar preguntas alteraría una encuesta ya respondida. | Regla HU-009: Bloquear si ya tiene respuestas
            return response()->json([
                'success' => false,
                'message' => 'Esta encuesta ya tiene respuestas registradas de aprendices. Por integridad metodológica, no se pueden agregar nuevas preguntas.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [ // make() valida todos los datos de la petición usando las reglas declaradas. | Validación de datos
            'texto'       => ['required', 'string', 'max:1000'],
            'tipo'        => ['required', 'string'],
            'obligatoria' => ['nullable'],
            'opciones'    => ['nullable', 'array'],
            'opciones.*'  => ['nullable', 'string', 'max:255'],
        ], [
            'texto.required' => 'El texto de la pregunta es obligatorio.',
            'tipo.required'  => 'Debes seleccionar un tipo de respuesta.',
        ]);

        if ($validator->fails()) { // fails() devuelve true si uno o más valores no cumplen las reglas.
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // errors() obtiene errores de validación y first() selecciona el primero.
            ], 422);
        }

        $tipoNormalizado = match (strtolower(trim($request->tipo))) { // Limpia y pasa a minúsculas el tipo recibido para aceptar variantes de escritura. | Normalizar tipo a los valores exactos: Opcion_multiple | Escala | Texto
            'opcion_multiple', 'opcionmultiple' => 'Opcion_multiple',
            'escala'                            => 'Escala',
            'texto'                             => 'Texto',
            default                             => null,
        };

        if (!$tipoNormalizado) { // null significa que el texto recibido no coincidió con ningún tipo permitido.
            return response()->json([
                'success' => false,
                'message' => 'El tipo de pregunta no es válido. Debe ser Opcion_multiple, Escala o Texto.',
            ], 422);
        }

        if ($tipoNormalizado === 'Opcion_multiple') { // Las preguntas de opción múltiple requieren opciones para que se puedan responder. | Validación específica para Opcion_multiple: mínimo 2 opciones
            $opcionesLimpias = array_values(array_filter(array_map('trim', $request->input('opciones', [])))); // input() lee opciones; trim limpia; array_map aplica a cada una; array_filter quita vacías; array_values reindexa.
            if (count($opcionesLimpias) < 2) { // count() cuenta las opciones útiles y exige como mínimo dos.
                return response()->json([
                    'success' => false,
                    'message' => 'Las preguntas de opción múltiple deben incluir al menos 2 opciones de respuesta.',
                ], 422);
            }
        }

        DB::beginTransaction(); // Inicia una transacción para que pregunta y opciones se guarden como una sola operación.
        try {
            $siguienteOrden = ($encuesta->preguntas()->max('orden') ?? 0) + 1; // preguntas() obtiene la relación; max() consulta el orden mayor; si no hay, empieza en 1.
            $obligatoria = $request->has('obligatoria') ? filter_var($request->obligatoria, FILTER_VALIDATE_BOOLEAN) : true; // has() verifica si llegó el campo y filter_var convierte su valor en booleano.

            $pregunta = Pregunta::create([ // create() inserta la pregunta con los campos permitidos en el modelo.
                'id_encuesta' => $encuesta->id_encuesta,
                'texto'       => trim($request->texto),
                'tipo'        => $tipoNormalizado,
                'obligatoria' => $obligatoria,
                'orden'       => $siguienteOrden,
            ]);

            if ($tipoNormalizado === 'Opcion_multiple') { // Solo guarda opciones cuando la pregunta es de selección múltiple.
                $opcionesLimpias = array_values(array_filter(array_map('trim', $request->input('opciones', [])))); // Lee, limpia, elimina vacías y reindexa las opciones recibidas.
                foreach ($opcionesLimpias as $index => $textoOpcion) { // Recorre las opciones; index indica la posición actual.
                    OpcionPregunta::create([ // Inserta una fila de opción asociada a la pregunta nueva.
                        'id_pregunta' => $pregunta->id_pregunta,
                        'texto'       => $textoOpcion,
                        'orden'       => $index + 1,
                    ]);
                }
            }

            DB::commit(); // Confirma pregunta y opciones a la vez.

            $pregunta->load('opciones'); // load() consulta y adjunta la relación opciones para incluirla en la respuesta.

            return response()->json([ // Devuelve al navegador los datos de la pregunta que acaba de crearse.
                'success'         => true,
                'message'         => 'Pregunta agregada exitosamente.',
                'pregunta'        => $pregunta,
                'total_preguntas' => $encuesta->preguntas()->count(), // count() cuenta en la base de datos cuántas preguntas pertenecen a la encuesta.
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Deshace la pregunta y las opciones si alguna instrucción produjo una excepción.
            return response()->json([ // Informa del fallo al cliente en formato JSON.
                'success' => false,
                'message' => 'Error al guardar la pregunta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Editar una pregunta existente
     */
    public function editarPregunta(Request $request, $encuestaId, $preguntaId) // editarPregunta(): Valida y actualiza una pregunta y sus opciones; se usa al guardar su edición. | Request trae los cambios; $encuestaId limita el recurso y $preguntaId señala la pregunta a modificar.
    {
        $usuario = Auth::user(); // Obtiene el usuario autenticado para autorizar la edición.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Comprueba sesión y rol; sin ambos, no permite editar.
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $encuesta = Encuesta::findOrFail($encuestaId); // Busca la encuesta dueña de la pregunta; devuelve 404 si no existe.

        if ($encuesta->tieneRespuestas()) { // Impide editar preguntas cuando ya hay respuestas que dependen de su contenido. | Bloquear si la encuesta ya tiene respuestas
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar la pregunta porque la encuesta ya tiene respuestas registradas de aprendices.',
            ], 422);
        }

        $pregunta = Pregunta::where('id_encuesta', $encuesta->id_encuesta) // where() limita la búsqueda a preguntas que pertenecen a esta encuesta.
            ->findOrFail($preguntaId); // Busca esa pregunta por su clave primaria dentro del filtro; falla con 404 si no pertenece.

        $validator = Validator::make($request->all(), [ // make() crea el validador para todos los campos enviados por el cliente.
            'texto'       => ['required', 'string', 'max:1000'],
            'tipo'        => ['required', 'string'],
            'obligatoria' => ['nullable'],
            'opciones'    => ['nullable', 'array'],
            'opciones.*'  => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) { // Comprueba si hay errores antes de procesar los datos.
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // Devuelve el primer mensaje de validación que falló.
            ], 422);
        }

        $tipoNormalizado = match (strtolower(trim($request->tipo))) { // Limpia el tipo de la petición y compara sus variantes en minúsculas.
            'opcion_multiple', 'opcionmultiple' => 'Opcion_multiple',
            'escala'                            => 'Escala',
            'texto'                             => 'Texto',
            default                             => null,
        };

        if (!$tipoNormalizado) { // Rechaza tipos distintos de Opcion_multiple, Escala o Texto.
            return response()->json([
                'success' => false,
                'message' => 'El tipo de pregunta no es válido.',
            ], 422);
        }

        if ($tipoNormalizado === 'Opcion_multiple') { // Aplica la regla de opciones solo para preguntas de selección.
            $opcionesLimpias = array_values(array_filter(array_map('trim', $request->input('opciones', [])))); // Obtiene la lista; limpia sus elementos y elimina valores vacíos.
            if (count($opcionesLimpias) < 2) { // count() comprueba que hayan quedado al menos dos opciones válidas.
                return response()->json([
                    'success' => false,
                    'message' => 'Las preguntas de opción múltiple deben tener al menos 2 opciones.',
                ], 422);
            }
        }

        DB::beginTransaction(); // Inicia la transacción para actualizar pregunta y opciones de forma consistente.
        try {
            $obligatoria = $request->has('obligatoria') ? filter_var($request->obligatoria, FILTER_VALIDATE_BOOLEAN) : true; // has() detecta si llegó el campo; filter_var interpreta true/false.

            $pregunta->update([ // Actualiza y guarda los datos nuevos de la pregunta existente.
                'texto'       => trim($request->texto),
                'tipo'        => $tipoNormalizado,
                'obligatoria' => $obligatoria,
            ]);

            OpcionPregunta::where('id_pregunta', $pregunta->id_pregunta)->delete(); // where() selecciona las opciones de esta pregunta y delete() las elimina antes de recrearlas. | Re-sincronizar opciones
            if ($tipoNormalizado === 'Opcion_multiple') { // Las opciones solo existen para preguntas de selección múltiple.
                $opcionesLimpias = array_values(array_filter(array_map('trim', $request->input('opciones', [])))); // Lee, limpia y descarta opciones vacías enviadas.
                foreach ($opcionesLimpias as $index => $textoOpcion) { // Recorre las opciones para guardarlas en su orden actual.
                    OpcionPregunta::create([ // Inserta cada opción con vínculo a la pregunta.
                        'id_pregunta' => $pregunta->id_pregunta,
                        'texto'       => $textoOpcion,
                        'orden'       => $index + 1,
                    ]);
                }
            }

            DB::commit(); // Hace permanentes los cambios de pregunta y opciones.
            $pregunta->load('opciones'); // Carga la relación recién guardada para devolverla en el JSON.

            return response()->json([ // Responde al navegador con éxito y los datos actualizados.
                'success'  => true,
                'message'  => 'Pregunta actualizada correctamente.',
                'pregunta' => $pregunta,
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Deshace los cambios si falló cualquier paso de la actualización.
            return response()->json([ // Devuelve un error JSON al cliente.
                'success' => false,
                'message' => 'Error al actualizar la pregunta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una pregunta
     */
    public function eliminarPregunta($encuestaId, $preguntaId) // eliminarPregunta(): Elimina la pregunta indicada; se usa al quitarla de la encuesta. | $encuestaId y $preguntaId son los identificadores de la encuesta y de la pregunta de la ruta.
    {
        $usuario = Auth::user(); // Obtiene la identidad de la sesión actual.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Solo continúa si la sesión pertenece a un administrador.
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $encuesta = Encuesta::findOrFail($encuestaId); // Localiza la encuesta solicitada o genera respuesta 404.

        if ($encuesta->tieneRespuestas()) { // Conserva la integridad de resultados evitando borrar una pregunta ya respondida.
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la pregunta porque la encuesta ya tiene respuestas asociadas de aprendices.',
            ], 422);
        }

        $pregunta = Pregunta::where('id_encuesta', $encuesta->id_encuesta) // where() limita la búsqueda a preguntas de la encuesta indicada.
            ->findOrFail($preguntaId); // Comprueba que el ID exista y pertenezca a esa encuesta.

        DB::transaction(function () use ($pregunta, $encuesta) { // transaction() confirma al finalizar o revierte automáticamente si hay excepción.
            $pregunta->delete(); // Elimina la pregunta; las opciones relacionadas dependen de la regla de borrado de la base de datos.

            $restantes = $encuesta->preguntas()->orderBy('orden', 'asc')->get(); // preguntas() obtiene solo preguntas de esta encuesta; orderBy() ordena ascendente; get() ejecuta la consulta. | Reindexar el orden de las preguntas restantes
            foreach ($restantes as $pos => $preg) { // Recorre el resultado; pos es la posición empezando en cero.
                $preg->update(['orden' => $pos + 1]); // Guarda una posición consecutiva que empieza en uno.
            }
        });

        return response()->json([ // Devuelve confirmación y el nuevo total de preguntas.
            'success'         => true,
            'message'         => 'Pregunta eliminada correctamente.',
            'total_preguntas' => $encuesta->preguntas()->count(), // count() cuenta en la base de datos las preguntas que quedan.
        ]);
    }

    /**
     * Reordenar preguntas (Drag & Drop)
     */
    public function reordenarPreguntas(Request $request, $id) // reordenarPreguntas(): Valida y guarda el orden de las preguntas; se usa al reorganizarlas en el editor. | Request contiene la lista ordenada de IDs; $id identifica la encuesta a la que pertenecen.
    {
        $usuario = Auth::user(); // Obtiene quién envió la solicitud de reordenamiento.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Verifica que tenga una sesión válida y rol administrador.
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $encuesta = Encuesta::findOrFail($id); // Recupera la encuesta que contiene las preguntas que se moverán.

        if ($encuesta->tieneRespuestas()) { // No permite cambiar orden después de responder para mantener el contexto de resultados.
            return response()->json([
                'success' => false,
                'message' => 'No se pueden reordenar las preguntas porque la encuesta ya tiene respuestas registradas de aprendices.',
            ], 422);
        }

        $ids = $request->input('ids', []); // input() lee la lista ordenada de IDs; [] se usa si el cliente no envió el campo.
        if (empty($ids)) { // Detecta una lista ausente o vacía y evita ejecutar actualizaciones sin elementos.
            return response()->json(['success' => false, 'message' => 'No se enviaron identificadores de preguntas.'], 422);
        }

        DB::transaction(function () use ($ids, $encuesta) { // Agrupa cambios de orden; si uno falla, Laravel revierte todos.
            foreach ($ids as $posicion => $preguntaId) { // Recorre los IDs en el orden que envió la interfaz.
                Pregunta::where('id_encuesta', $encuesta->id_encuesta) // where() limita la consulta a la encuesta actual.
                    ->where('id_pregunta', $preguntaId) // Agrega una segunda condición AND para seleccionar una pregunta específica.
                    ->update(['orden' => $posicion + 1]); // update() asigna a esa pregunta la posición consecutiva empezando en uno.
            }
        });

        return response()->json([ // Confirma en JSON que el orden nuevo quedó guardado.
            'success' => true,
            'message' => 'Orden de preguntas actualizado correctamente.',
        ]);
    }
}
