<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\Pqr;

class PqrController extends Controller // Controlador para radicar, responder y administrar solicitudes PQR.
{
    /**
     * Verifica que el usuario pueda gestionar PQR (admin o instructor).
     */
    private function verificarAcceso() // verificarAcceso(): Comprueba permisos de administrador o instructor para PQR; se usa antes de las acciones protegidas.
    {
        if (!Auth::user()->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) { // Auth::user() devuelve la cuenta autenticada para consultar sus roles.
            return redirect() // route() crea el destino y with() almacena un aviso temporal para mostrarlo después de redirigir.
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al módulo PQR de Control ECP.');
        }

        return null; // null indica que el usuario tiene un rol permitido y puede continuar.
    }

    /**
     * 1. LISTADO DE PQR con filtros y estadísticas.
     */
    public function index(Request $request) // index(): Consulta PQR con filtros y estadísticas; se usa al abrir su listado administrativo.
    {
        if ($redirect = $this->verificarAcceso()) { // Si el control de acceso devuelve una redirección, no se ejecuta la consulta del listado.
            return $redirect;
        }

        $query = Pqr::query(); // Inicia una consulta componible a la tabla de PQR sin ejecutarla todavía.

        if ($request->filled('buscar')) { // Búsqueda por radicado, nombre o asunto | filled() comprueba que el parámetro buscar exista y no esté vacío.
            $buscar = $request->input('buscar'); // input() obtiene el texto ingresado para aplicarlo a la búsqueda.
            $query->where(function ($q) use ($buscar) { // Agrupa las condiciones para que la búsqueda coincida con cualquiera de estos tres campos.
                $q->where('radicado', 'like', "%{$buscar}%") // where() agrega la primera condición SQL LIKE con coincidencia parcial por radicado.
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%") // orWhere() agrega alternativas: también busca nombre o asunto.
                  ->orWhere('asunto', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo')) { // Aplica el filtro tipo solo cuando el formulario envió un valor no vacío.
            $query->where('tipo', $request->input('tipo')); // where() añade a la consulta la igualdad para el tipo seleccionado.
        }

        if ($request->filled('estado')) { // Aplica de igual manera el filtro opcional de estado.
            $query->where('estado', $request->input('estado')); // where() agrega el criterio de estado a la consulta aún no ejecutada.
        }

        $pqrs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString(); // orderBy() ordena de más reciente a más antigua; paginate() ejecuta por páginas de 10. | withQueryString() conserva los filtros en los enlaces de navegación de páginas.

        $total      = Pqr::count(); // Estadísticas | count() ejecuta conteos SQL globales para los indicadores del listado.
        $pendientes = Pqr::where('estado', 'Pendiente')->count(); // where() filtra cada conteo por estado antes de ejecutarlo con count().
        $enTramite  = Pqr::where('estado', 'En trámite')->count();
        $resueltas  = Pqr::whereIn('estado', ['Resuelto', 'Cerrado'])->count(); // whereIn() cuenta las PQR cuyo estado coincide con cualquiera de los valores indicados.

        return view('controlecp::administrador.pqr', compact( // Renderiza la vista administrativa y compact() reúne las variables bajo sus nombres.
            'pqrs', 'total', 'pendientes', 'enTramite', 'resueltas'
        ));
    }

    /**
     * 2. FORMULARIO DE RADICACIÓN.
     */
    public function create() // create(): Muestra los catálogos y datos para radicar una PQR; se usa al solicitar una nueva radicación.
    {
        if ($redirect = $this->verificarAcceso()) { // Impide presentar el formulario a quien no tenga acceso al módulo.
            return $redirect;
        }

        return view('controlecp::administrador.pqr_create', [ // Renderiza el formulario con catálogos definidos por el modelo y un nuevo radicado generado.
            'tipos'       => Pqr::TIPOS,
            'perfiles'    => Pqr::PERFILES,
            'prioridades' => Pqr::PRIORIDADES,
            'radicado'    => Pqr::generarRadicado(),
        ]);
    }

    /**
     * 3. GUARDAR NUEVA PQR.
     */
    public function store(Request $request) // store(): Valida y guarda la PQR y su anexo opcional; se usa al enviar la radicación.
    {
        if ($redirect = $this->verificarAcceso()) { // Comprueba permisos antes de procesar los datos recibidos.
            return $redirect;
        }

        $validated = $request->validate([ // validate() aplica reglas a cada campo; si alguna falla, Laravel vuelve con los errores. | required exige valor; nullable permite omitirlo; in limita opciones; mimes y max restringen el archivo.
            'tipo'               => 'required|in:' . implode(',', Pqr::TIPOS),
            'nombre_solicitante' => 'required|string|max:255',
            'documento'          => 'nullable|string|max:50',
            'email'              => 'required|email|max:255',
            'telefono'           => 'nullable|string|max:30',
            'perfil'             => 'required|in:' . implode(',', Pqr::PERFILES),
            'asunto'             => 'required|string|max:255',
            'mensaje'            => 'required|string',
            'prioridad'          => 'required|in:' . implode(',', Pqr::PRIORIDADES),
            'anexo'              => 'nullable|file|mimes:pdf,jpeg,jpg,png,webp,doc,docx|max:4096',
        ]);

        $validated['radicado'] = Pqr::generarRadicado(); // Genera el número de radicado y establece el estado inicial de la nueva solicitud.
        $validated['estado']   = 'Pendiente';

        if ($request->hasFile('anexo')) { // hasFile() verifica si se adjuntó archivo antes de intentar almacenarlo.
            $validated['anexo'] = $request->file('anexo')->store('controlecp/pqr', 'public'); // file() obtiene el archivo y store() lo guarda en la carpeta indicada del disco público.
        }

        $pqr = Pqr::create($validated); // create() inserta el registro usando los campos validados y preparados.

        return redirect() // Redirige al listado y guarda un mensaje temporal que incluye el número de radicado creado.
            ->route('controlecp.administrador.pqr')
            ->with('success', "¡PQR radicada con éxito! Número de radicado: {$pqr->radicado}");
    }

    /**
     * 4. VER DETALLE Y RESPONDER.
     */
    public function show($id) // show(): Carga el detalle de una PQR y sus datos de respuesta; se usa al abrir su ficha.
    {
        if ($redirect = $this->verificarAcceso()) { // Verifica que la persona tenga permiso para ver el detalle.
            return $redirect;
        }

        $pqr = Pqr::with('respondidoPor')->findOrFail($id); // with() carga la relación respondidoPor junto con la PQR; findOrFail devuelve 404 si no existe.

        return view('controlecp::administrador.pqr_show', [ // Entrega el modelo y el catálogo de estados a la vista de detalle.
            'pqr'     => $pqr,
            'estados' => Pqr::ESTADOS,
        ]);
    }

    /**
     * 5. REGISTRAR RESPUESTA / ACTUALIZAR ESTADO.
     */
    public function responder(Request $request, $id) // responder(): Valida y registra la respuesta y estado de una PQR; se usa al enviar la gestión de respuesta.
    {
        if ($redirect = $this->verificarAcceso()) { // Requiere permiso antes de consultar o modificar la solicitud.
            return $redirect;
        }

        $pqr = Pqr::findOrFail($id); // Recupera la PQR o responde 404 cuando el identificador no existe.

        $validated = $request->validate([ // validate() exige una respuesta de texto y limita el estado a los valores admitidos.
            'respuesta' => 'required|string',
            'estado'    => 'required|in:' . implode(',', Pqr::ESTADOS),
        ]);

        $pqr->update([ // update() persiste la respuesta, el estado, el instante actual y el identificador del usuario responsable. | Auth::id() obtiene quién respondió y now() genera la fecha y hora actuales.
            'respuesta'       => $validated['respuesta'],
            'estado'          => $validated['estado'],
            'fecha_respuesta' => now(),
            'respondido_por'  => Auth::id(),
        ]);

        return redirect() // Vuelve al detalle de esta misma PQR y muestra confirmación temporal.
            ->route('controlecp.administrador.pqr.show', $pqr->id)
            ->with('success', '¡Respuesta registrada correctamente!');
    }

    /**
     * 6. ELIMINAR PQR.
     */
    public function destroy($id) // destroy(): Elimina el anexo y la PQR; se usa al confirmar su eliminación.
    {
        if ($redirect = $this->verificarAcceso()) { // Detiene el borrado si la persona no tiene acceso al módulo.
            return $redirect;
        }

        $pqr = Pqr::findOrFail($id); // Carga el registro que se va a eliminar o responde 404 si no existe.

        if ($pqr->anexo) { // Comprueba que exista la ruta guardada antes de intentar borrar el archivo.
            Storage::disk('public')->delete($pqr->anexo); // Selecciona el disco público y elimina allí el archivo adjunto.
        }

        $pqr->delete(); // Elimina el registro PQR de la base de datos.

        return redirect() // Regresa al listado con un aviso temporal de que el registro fue eliminado.
            ->route('controlecp.administrador.pqr')
            ->with('success', '¡PQR eliminada del sistema!');
    }
}
