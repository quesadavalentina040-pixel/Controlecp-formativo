<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\Role;

class UserController extends Controller // Controlador administrativo para gestionar cuentas y sincronizar usuarios.
{
    /**
     * Roles de Control ECP (slugs exactos usados en el proyecto).
     */
    private const ROLES_ECP = [
        'controlecp.admin',
        'controlecp.instructor',
        'controlecp.apprentice',
    ];

    /**
     * Verifica que el usuario autenticado sea admin de Control ECP.
     */
    private function verificarAdmin() // verificarAdmin(): Comprueba que el usuario tenga rol administrador; se usa antes de gestionar cuentas.
    {
        $usuario = Auth::user(); // Recupera la cuenta autenticada para revisar su rol.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Rechaza tanto a usuarios sin sesión como a quienes no son administradores.
            return redirect()
                ->route('controlecp.index') // Envía al inicio del módulo cuando no se permite el acceso.
                ->with('error', 'No tienes permisos para acceder a la gestión de usuarios de Control ECP.'); // Prepara un aviso para mostrarlo después de redirigir.
        }
        return null; // Indica que la acción puede continuar.
    }


    /**
     * Listado de usuarios con filtros por rol y estado, búsqueda por nombre/documento/correo.
     */
    public function index(Request $request) // ───────────────────────────────────────────────────────────────────────── | 1. LISTADO GENERAL | ───────────────────────────────────────────────────────────────────────── | index(): Consulta usuarios con filtros e indicadores; se usa al abrir el listado administrativo.
    {
        if ($redirect = $this->verificarAdmin()) { // Comprueba permisos antes de consultar cuentas.
            return $redirect; // Detiene el listado si la comprobación produjo una redirección.
        }

        $rolesEcp = Role::whereIn('slug', self::ROLES_ECP)->get(); // Obtiene los roles del módulo para mostrarlos en el filtro. | Roles ECP disponibles para filtros

        $query = User::withTrashed() // Query base: users que tengan al menos un rol ECP
            ->with(['person', 'roles'])
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP)); // Incluye relaciones y limita cuentas a quienes tienen un rol ECP.

        if ($request->filled('rol')) { // Aplica el filtro solo cuando se recibió un rol. | Filtro por rol
            $query->whereHas('roles', fn($q) => $q->where('slug', $request->input('rol'))); // Conserva usuarios asociados al rol solicitado.
        }

        if ($request->filled('estado')) { // Revisa el estado solo si el formulario envió un filtro. | Filtro por estado: activo = sin deleted_at, inactivo = con deleted_at
            if ($request->input('estado') === 'activo') { // Las cuentas activas no tienen fecha de baja.
                $query->whereNull('users.deleted_at'); // Excluye usuarios marcados como eliminados lógicamente.
            } elseif ($request->input('estado') === 'inactivo') { // Las cuentas inactivas conservan una fecha de baja.
                $query->whereNotNull('users.deleted_at'); // Limita la consulta a usuarios desactivados.
            }
        }

        if ($request->filled('buscar')) { // Añade búsqueda solo cuando hay texto recibido. | Búsqueda por nombre (en people), documento (en people), correo (en users)
            $buscar = $request->input('buscar'); // Lee el texto que se buscará en varios campos.
            $query->where(function ($q) use ($buscar) { // Agrupa las coincidencias alternativas para no alterar otros filtros.
                $q->where('users.email', 'like', "%{$buscar}%")
                  ->orWhere('users.nickname', 'like', "%{$buscar}%")
                  ->orWhereHas('person', function ($qp) use ($buscar) {
                      $qp->where('first_name', 'like', "%{$buscar}%")
                         ->orWhere('first_last_name', 'like', "%{$buscar}%")
                         ->orWhere('second_last_name', 'like', "%{$buscar}%")
                         ->orWhere('document_number', 'like', "%{$buscar}%")
                         ->orWhere('misena_email', 'like', "%{$buscar}%")
                         ->orWhere('sena_email', 'like', "%{$buscar}%")
                         ->orWhere('personal_email', 'like', "%{$buscar}%");
                  });
            });
        }

        $usuarios = $query->orderBy('users.created_at', 'desc')->paginate(15)->withQueryString(); // Ordena del más reciente al más antiguo y conserva filtros entre páginas.

        $totalActivos   = User::withTrashed() // Contadores rápidos
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->count(); // Cuenta todas las cuentas ECP activas, sin depender de los filtros de pantalla.
        $totalInactivos = User::withTrashed()
            ->whereNotNull('users.deleted_at')
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->count(); // Cuenta por separado las cuentas ECP desactivadas.

        return view('controlecp::administrador.usuarios.index', compact(
            'usuarios',
            'rolesEcp',
            'totalActivos',
            'totalInactivos'
        )); // Renderiza el listado con resultados, roles y contadores.
    }


    /**
     * Formulario para crear un usuario manualmente.
     */
    public function create() // ───────────────────────────────────────────────────────────────────────── | 2. CREACIÓN MANUAL | ───────────────────────────────────────────────────────────────────────── | create(): Muestra el formulario de registro de usuario; se usa al solicitar una nueva alta.
    {
        if ($redirect = $this->verificarAdmin()) { // Protege el formulario de alta con la misma comprobación administrativa.
            return $redirect;
        }

        $rolesEcp = Role::whereIn('slug', self::ROLES_ECP)->get(); // Carga las opciones de rol permitidas para el formulario.

        return view('controlecp::administrador.usuarios.create', compact('rolesEcp')); // Renderiza el formulario y le entrega sus roles.
    }

    /**
     * Guarda un usuario creado manualmente (person + user + role_user en transacción).
     */
    public function store(Request $request) // store(): Valida y crea una cuenta de usuario; se usa al enviar el formulario de registro.
    {
        if ($redirect = $this->verificarAdmin()) { // Impide que alguien sin rol admin cree cuentas.
            return $redirect;
        }

        $request->validate([ // Comprueba datos obligatorios, formatos, longitudes y valores únicos antes de persistir.
            'first_name'       => 'required|string|max:100',
            'first_last_name'  => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'document_type'    => 'required|string|max:60',
            'document_number'  => 'required|string|max:30|unique:people,document_number',
            'email'            => 'required|email|max:191|unique:users,email',
            'nickname'         => 'nullable|string|max:60|unique:users,nickname',
            'telephone1'       => 'nullable|string|max:20',
            'password'         => 'required|string|min:8|confirmed',
            'rol_id'           => 'required|exists:roles,id',
        ], [ // Usa mensajes entendibles para los conflictos de unicidad más comunes.
            'document_number.unique' => 'Ya existe una persona con ese número de documento.',
            'email.unique'           => 'Ese correo ya está registrado en el sistema.',
            'nickname.unique'        => 'Ese nombre de usuario ya está en uso.',
        ]); // Si la validación falla, Laravel no ejecuta la creación.

        $rol = Role::whereIn('slug', self::ROLES_ECP)->findOrFail($request->input('rol_id')); // Busca el rol elegido dentro del conjunto permitido o responde 404. | Validar que el rol seleccionado sea un rol ECP

        DB::transaction(function () use ($request, $rol) { // Agrupa altas relacionadas; si una falla, Laravel revierte todas.
            $person = Person::create([ // 1. Crear registro en people
                'document_type'    => $request->input('document_type'), // Toma el tipo documental validado.
                'document_number'  => $request->input('document_number'), // Guarda el documento que identifica a la persona.
                'first_name'       => mb_strtoupper($request->input('first_name')), // Normaliza el nombre a mayúsculas respetando caracteres multibyte.
                'first_last_name'  => mb_strtoupper($request->input('first_last_name')),
                'second_last_name' => $request->filled('second_last_name')
                    ? mb_strtoupper($request->input('second_last_name')) : null, // Convierte el segundo apellido si llegó con contenido; si no, guarda null.
                'telephone1'       => $request->input('telephone1'),
                'misena_email'     => $request->input('email'),
            ]);

            $user = User::create([ // 2. Crear registro en users
                'person_id' => $person->id,
                'email'     => $request->input('email'),
                'nickname'  => $request->input('nickname') ?: null, // Conserva el nickname recibido o guarda null si está vacío.
                'password'  => Hash::make($request->input('password')), // Guarda un hash seguro, no la contraseña original.
            ]);

            $this->asignarRolEcp($user, $rol->id); // 3. Asignar rol (reemplaza cualquier rol ECP previo)
        }); // Confirma la transacción si se crearon persona, cuenta y rol sin errores.

        if ($request->ajax() || $request->wantsJson()) { // Detecta si el cliente espera una respuesta asíncrona.
            return response()->json(['success' => true, 'message' => '¡Usuario creado correctamente en Control ECP!']); // Devuelve confirmación estructurada para JavaScript.
        }

        return redirect()
            ->route('controlecp.usuarios.index') // Regresa al listado para una navegación normal.
            ->with('success', '¡Usuario creado correctamente en Control ECP!'); // Comparte un mensaje temporal de éxito.
    }


    /**
     * Devuelve los datos del usuario como JSON para poblar el modal de edición.
     */
    public function editData($id) // ───────────────────────────────────────────────────────────────────────── | 3. EDICIÓN | ───────────────────────────────────────────────────────────────────────── | editData(): Carga los datos editables del usuario en el formato solicitado; se usa al abrir su edición.
    {
        if (Auth::user() && !Auth::user()->hasRole('controlecp.admin')) { // Comprueba el rol si hay una cuenta autenticada.
            return response()->json(['error' => 'Sin permisos'], 403); // Devuelve JSON con estado HTTP 403 para el acceso no autorizado.
        }

        $usuario   = User::withTrashed()->with(['person', 'roles'])->findOrFail($id); // Busca incluso cuentas inactivas y carga persona/roles para evitar consultas adicionales.
        $rolActual = $usuario->roles->whereIn('slug', self::ROLES_ECP)->first(); // Obtiene el primer rol del módulo asociado a la cuenta.

        return response()->json([
            'id'               => $usuario->id,
            'email'            => $usuario->email,
            'nickname'         => $usuario->nickname,
            'trashed'          => $usuario->trashed(),
            'first_name'       => $usuario->person->first_name       ?? '',
            'first_last_name'  => $usuario->person->first_last_name  ?? '',
            'second_last_name' => $usuario->person->second_last_name ?? '',
            'telephone1'       => $usuario->person->telephone1       ?? '',
            'document_type'    => $usuario->person->document_type    ?? '',
            'document_number'  => $usuario->person->document_number  ?? '',
            'rol_id'           => $rolActual?->id,
            'rol_name'         => $rolActual?->name,
        ]); // Entrega los datos del usuario en JSON para llenar el formulario de edición.
    }

    /**
     * Actualiza rol, correo, nickname y, opcionalmente, la contraseña del usuario.
     */
    public function update(Request $request, $id) // update(): Valida y guarda los cambios del usuario; se usa al enviar su formulario de edición.
    {
        if ($redirect = $this->verificarAdmin()) { // Valida permisos antes de modificar datos.
            return $redirect;
        }

        $usuario = User::withTrashed()->with(['person', 'roles'])->findOrFail($id); // Recupera al usuario activo o inactivo junto con sus relaciones.

        $request->validate([ // Revisa datos editables; las reglas únicas excluyen el registro que se está editando.
            'first_name'       => 'required|string|max:100',
            'first_last_name'  => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'telephone1'       => 'nullable|string|max:20',
            'email'            => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($usuario->id)],
            'nickname'         => ['nullable', 'string', 'max:60', Rule::unique('users', 'nickname')->ignore($usuario->id)],
            'password'         => 'nullable|string|min:8|confirmed',
            'rol_id'           => 'required|exists:roles,id',
        ]); // La actualización no continúa si algún campo incumple las reglas.

        $rol = Role::whereIn('slug', self::ROLES_ECP)->findOrFail($request->input('rol_id')); // Impide asignar un rol ajeno al módulo. | Validar que el rol sea ECP

        DB::transaction(function () use ($request, $usuario, $rol) { // Mantiene sincronizados persona, cuenta y asignación del rol.
            if ($usuario->person) { // Actualizar datos de person si existe
                $usuario->person->update([ // Actualiza la persona vinculada solo cuando existe esa relación.
                    'first_name'       => mb_strtoupper($request->input('first_name')),
                    'first_last_name'  => mb_strtoupper($request->input('first_last_name')),
                    'second_last_name' => $request->filled('second_last_name')
                        ? mb_strtoupper($request->input('second_last_name')) : $usuario->person->second_last_name, // Si se deja vacío, conserva el apellido que ya tenía.
                    'telephone1'       => $request->input('telephone1'),
                    'misena_email'     => $request->input('email'),
                ]); // Guarda los campos personales permitidos.
            }

            $dataUser = [ // Prepara los campos de cuenta que se actualizan siempre. | Actualizar credenciales del user
                'email'    => $request->input('email'),
                'nickname' => $request->input('nickname') ?: null,
            ];
            if ($request->filled('password')) { // Cambia la contraseña únicamente si se escribió una nueva.
                $dataUser['password'] = Hash::make($request->input('password')); // Almacena el hash de la nueva contraseña.
            }
            $usuario->update($dataUser); // Guarda correo y nickname, además de la contraseña si se incluyó.

            $this->asignarRolEcp($usuario, $rol->id); // Reasignar rol ECP
        }); // Confirma los cambios juntos cuando todas las operaciones terminan bien.

        if ($request->ajax() || $request->wantsJson()) { // Responde según el formato solicitado por el cliente.
            return response()->json(['success' => true, 'message' => '¡Usuario actualizado correctamente!']); // Devuelve el resultado para la interfaz AJAX.
        }

        return redirect()
            ->route('controlecp.usuarios.index') // Vuelve al listado para solicitudes de navegador.
            ->with('success', '¡Usuario actualizado correctamente!'); // Muestra un aviso temporal de confirmación.
    }


    /**
     * Desactiva un usuario (soft-delete en users).
     * Valida que no sea el último admin activo.
     */
    public function desactivar(Request $request, $id) // ───────────────────────────────────────────────────────────────────────── | 4. ACTIVAR / DESACTIVAR (SoftDelete como mecanismo de estado) | ───────────────────────────────────────────────────────────────────────── | desactivar(): Desactiva la cuenta indicada; se usa al suspender el acceso de un usuario.
    {
        if ($redirect = $this->verificarAdmin()) { // Comprueba permisos antes de suspender la cuenta.
            return $redirect;
        }

        $usuario = User::with('roles')->findOrFail($id); // Busca solo cuentas activas y carga sus roles para comprobar si es admin.

        if ($usuario->hasRole('controlecp.admin')) { // Cuenta administradores activos solo si la persona afectada tiene ese rol. | Regla: no se puede desactivar al último administrador activo
            $adminsActivos = User::whereNull('users.deleted_at')
                ->whereHas('roles', fn($q) => $q->where('slug', 'controlecp.admin'))
                ->count(); // Cuenta administradores que todavía no tienen fecha de baja.

            if ($adminsActivos <= 1) { // Evita dejar el módulo sin ningún administrador habilitado.
                if ($request->ajax() || $request->wantsJson()) { // Distingue la respuesta para llamadas asíncronas.
                    return response()->json(['success' => false, 'message' => 'No se puede desactivar al único Administrador activo de Control ECP.'], 422); // Informa el conflicto con estado HTTP 422.
                }
                return redirect()
                    ->route('controlecp.usuarios.index')
                    ->with('error', 'No se puede desactivar al único Administrador activo de Control ECP.'); // Muestra el motivo en el listado.
            }
        }

        if ($usuario->id === Auth::id()) { // Impide que la cuenta actual se quite su propio acceso. | No eliminar al admin que está logueado
            if ($request->ajax() || $request->wantsJson()) { // Conserva una respuesta compatible con AJAX.
                return response()->json(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 422); // Informa la regla de negocio con estado 422.
            }
            return redirect()
                ->route('controlecp.usuarios.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.'); // Regresa al listado con el motivo del rechazo.
        }

        $usuario->delete(); // Soft delete: marca deleted_at sin borrar físicamente la cuenta ni su historial.

        if ($request->ajax() || $request->wantsJson()) { // Devuelve JSON cuando la operación se inició desde la interfaz dinámica.
            return response()->json(['success' => true, 'message' => "Usuario «{$usuario->full_name}» desactivado."]); // Confirma la desactivación al cliente.
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', "Usuario «{$usuario->full_name}» desactivado. Su historial se conserva íntegro."); // Confirma la acción y avisa que el historial sigue guardado.
    }

    /**
     * Reactiva un usuario previamente desactivado (restore del SoftDelete).
     */
    public function activar(Request $request, $id) // activar(): Activa la cuenta indicada; se usa al restablecer su acceso.
    {
        if ($redirect = $this->verificarAdmin()) { // Restringe la reactivación a administradores.
            return $redirect;
        }

        $usuario = User::withTrashed()->findOrFail($id); // Busca también cuentas inactivas para poder restaurarlas.

        if (!$usuario->trashed()) { // Evita restaurar una cuenta que ya se encuentra activa.
            if ($request->ajax() || $request->wantsJson()) { // Responde en el formato solicitado por el cliente.
                return response()->json(['success' => false, 'message' => 'El usuario ya está activo.']); // Devuelve el estado actual en JSON.
            }
            return redirect()->route('controlecp.usuarios.index')->with('info', 'El usuario ya está activo.'); // Regresa al listado con un aviso informativo.
        }

        $usuario->restore(); // Quita la marca de baja para volver a habilitar la cuenta.

        if ($request->ajax() || $request->wantsJson()) { // Usa JSON si la petición espera una respuesta dinámica.
            return response()->json(['success' => true, 'message' => "Usuario «{$usuario->full_name}» reactivado correctamente."]); // Informa que la cuenta quedó activa.
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', "Usuario «{$usuario->full_name}» reactivado correctamente."); // Muestra la confirmación al volver al listado.
    }


    /**
     * Listado de usuarios desactivados — JSON para modal, vista para acceso directo.
     */
    public function historial(Request $request) // ───────────────────────────────────────────────────────────────────────── | 5. HISTORIAL DE USUARIOS DESACTIVADOS | ───────────────────────────────────────────────────────────────────────── | historial(): Consulta el historial de movimientos de usuarios; se usa al revisar cambios registrados.
    {
        if ($redirect = $this->verificarAdmin()) { // Protege el historial de usuarios inactivos.
            return $redirect;
        }

        $inactivos = User::onlyTrashed()
            ->with(['person', 'roles'])
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->orderBy('deleted_at', 'desc')
            ->get(); // Recupera todas las cuentas dadas de baja con sus relaciones, de la más reciente a la más antigua.

        if ($request->ajax() || $request->wantsJson()) { // Entrega datos serializados si la interfaz los solicita dinámicamente.
            return response()->json([
                'inactivos' => $inactivos->map(fn($u) => [
                    'id'           => $u->id,
                    'full_name'    => $u->full_name,
                    'initials'     => $u->initials,
                    'nickname'     => $u->nickname,
                    'email'        => $u->email,
                    'document_number' => $u->person?->document_number ?? '—',
                    'rol_name'     => $u->roles->whereIn('slug', self::ROLES_ECP)->first()?->name ?? '—',
                    'deleted_at'   => $u->deleted_at->format('d/m/Y H:i'),
                    'deleted_date' => $u->deleted_at->format('d/m/Y'),
                ]), // Transforma cada modelo en los campos simples que consume la interfaz.
            ]); // Devuelve la lista de inactivos como JSON.
        }

        return redirect()->route('controlecp.usuarios.index', ['modal' => 'historial']); // Abre el listado normal con indicación para mostrar el modal de historial. | Acceso directo (sin modal): redirige al index con el modal abierto vía hash
    }


    /**
     * Muestra la vista previa de lo que traería la API de SICEFA,
     * sin escribir nada en la BD todavía.
     */
    public function sincronizarPreview(Request $request) // ───────────────────────────────────────────────────────────────────────── | 6. SINCRONIZACIÓN CON SICEFA | ───────────────────────────────────────────────────────────────────────── | sincronizarPreview(): Prepara una vista previa de la sincronización con SICEFA; se usa antes de aplicar cambios.
    {
        if ($redirect = $this->verificarAdmin()) { // Solo un administrador puede consultar la vista previa.
            return $redirect;
        }

        $tipo = $request->input('tipo', 'instructores'); // Lee el tipo pedido y usa instructores si no se envió ninguno.

        [$registros, $error] = $this->consultarSicefa($tipo); // Consulta la fuente externa y separa los registros del mensaje de error.

        if ($error) { // No prepara una vista previa si la consulta externa informó un problema.
            return redirect()
                ->route('controlecp.usuarios.index')
                ->with('error', "Error al conectar con SICEFA: {$error}"); // Regresa con el detalle del error para mostrarlo.
        }

        $preview = []; // Clasificar cada registro como nuevo o existente (para mostrar vista previa)
        foreach ($registros as $reg) { // Revisa cada fila recibida para preparar lo que verá el administrador.
            $docNum  = $reg['document_number'] ?? null;
            $emailEx = $reg['email'] ?? null;

            $personaExistente = $docNum // Buscar si ya existe en people por número de documento
                ? Person::withTrashed()->where('document_number', $docNum)->first()
                : null; // Busca por documento incluyendo personas dadas de baja; sin documento no hay coincidencia que consultar.

            $cambios = [];
            if ($personaExistente) { // Compara datos de la API con la persona encontrada.
                $mapeoCampos = [ // Detectar cambios entre lo que trae la API y lo que hay en BD
                    'first_name'       => mb_strtoupper($reg['first_name'] ?? ''),
                    'first_last_name'  => mb_strtoupper($reg['first_last_name'] ?? ''),
                    'second_last_name' => mb_strtoupper($reg['second_last_name'] ?? ''),
                    'misena_email'     => $reg['email'] ?? null,
                ];
                foreach ($mapeoCampos as $campo => $valorApi) { // Revisa cada campo mapeado y detecta diferencias.
                    if ((string)($personaExistente->{$campo} ?? '') !== (string)($valorApi ?? '')) { // Compara ambos valores como texto, tratando valores ausentes como vacíos.
                        $cambios[$campo] = [
                            'actual' => $personaExistente->{$campo},
                            'nuevo'  => $valorApi,
                        ];
                    }
                }
            }

            $preview[] = [
                'datos'             => $reg,
                'persona_existente' => $personaExistente,
                'es_nuevo'          => !$personaExistente,
                'cambios'           => $cambios,
                'tiene_user'        => $personaExistente
                    ? User::withTrashed()->where('person_id', $personaExistente->id)->exists()
                    : false, // Comprueba si ya existe una cuenta vinculada, incluso si está inactiva.
            ];
        }

        $rolesEcp = Role::whereIn('slug', ['controlecp.instructor', 'controlecp.apprentice'])->get(); // Carga los roles seleccionables para instructores y aprendices.

        if ($request->ajax() || $request->wantsJson()) { // La interfaz de sincronización puede solicitar directamente el JSON.
            return response()->json([
                'preview'  => $preview,
                'tipo'     => $tipo,
                'rolesEcp' => $rolesEcp->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]), // Reduce cada rol a los datos que necesita el selector.
            ]); // Entrega la vista previa y opciones de rol como JSON.
        }

        return redirect()->route('controlecp.usuarios.index', ['modal' => 'sincronizar']); // Abre el listado indicando que debe mostrarse el modal de sincronización. | Acceso directo: redirige al index con parámetro para abrir el modal
    }

    /**
     * Aplica la sincronización confirmada por el admin:
     * escribe/actualiza en people, y si el admin asignó rol, crea también el user.
     */
    public function sincronizarAplicar(Request $request) // sincronizarAplicar(): Aplica la sincronización confirmada de usuarios con SICEFA; se usa tras revisar la vista previa.
    {
        if ($redirect = $this->verificarAdmin()) { // Exige permisos administrativos antes de aplicar cambios externos.
            return $redirect;
        }

        $request->validate([ // Valida la lista y los datos mínimos de cada fila antes de recorrerla.
            'registros'              => 'required|array',
            'registros.*.aplicar'    => 'sometimes|boolean',
            'registros.*.doc_number' => 'required|string',
            'registros.*.rol_id'     => 'nullable|exists:roles,id',
        ]); // Si el contenido de alguna fila no cumple las reglas, Laravel interrumpe el proceso.

        $creados    = 0;
        $actualizados = 0;
        $sinRol     = 0;

        foreach ($request->input('registros') as $reg) { // Procesa una por una las filas confirmadas por el administrador.
            if (empty($reg['aplicar'])) { // Omite las filas que no fueron seleccionadas. | Solo procesar los que el admin marcó para aplicar
                continue; // Pasa directamente al siguiente registro.
            }

            $docNum = $reg['doc_number']; // Usa el documento validado como clave para encontrar la persona.

            DB::transaction(function () use ($reg, $docNum, &$creados, &$actualizados, &$sinRol) { // Hace atómicas las escrituras de esta persona y su cuenta; captura contadores por referencia.
                $personaExistente = Person::withTrashed()->where('document_number', $docNum)->first(); // Busca la persona por documento, incluso si estaba inactiva.

                $datosPersona = [
                    'document_type'    => $reg['document_type'] ?? 'Cédula de ciudadanía',
                    'document_number'  => $docNum,
                    'first_name'       => mb_strtoupper($reg['first_name'] ?? ''),
                    'first_last_name'  => mb_strtoupper($reg['first_last_name'] ?? ''),
                    'second_last_name' => mb_strtoupper($reg['second_last_name'] ?? ''),
                    'misena_email'     => $reg['email'] ?? null,
                    'telephone1'       => $reg['telephone1'] ?? null,
                ];

                if ($personaExistente) { // Actualiza el registro coincidente en lugar de duplicar a la persona.
                    $personaExistente->update($datosPersona); // Sincroniza los campos personales recibidos. | La API manda sobre los datos personales
                    if ($personaExistente->trashed()) { // Comprueba si la persona estaba dada de baja.
                        $personaExistente->restore(); // La reactiva antes de continuar con su cuenta.
                    }
                    $person = $personaExistente; // Reutiliza el modelo actualizado para enlazar una cuenta.
                    $actualizados++; // Aumenta el contador de personas existentes modificadas.
                } else {
                    $person = Person::create($datosPersona); // Crea una persona cuando no existía ese documento.
                    $creados++; // Aumenta el contador de nuevas personas.
                }

                if (!empty($reg['rol_id'])) { // Solo administra una cuenta cuando se eligió un rol para esa fila. | Si el admin asignó un rol, crear/actualizar el user
                    $rol = Role::whereIn('slug', ['controlecp.instructor', 'controlecp.apprentice'])
                        ->find($reg['rol_id']); // Verifica que el rol indicado sea uno de los permitidos para sincronización.

                    if ($rol) { // Continúa con la cuenta solo si se encontró un rol válido.
                        $userExistente = User::withTrashed()->where('person_id', $person->id)->first(); // Busca la cuenta vinculada, aunque esté inactiva.

                        if (!$userExistente) { // Crea una cuenta únicamente cuando todavía no existe una asociada.
                            $email = $reg['email'] ?? ($docNum . '@sena.edu.co');
                            if (User::withTrashed()->where('email', $email)->exists()) { // Comprueba unicidad también entre cuentas inactivas. | Si ese email ya existe en users, usar documento como usuario
                                $email = $docNum . '@controlecp.sena'; // Usa una dirección alternativa para evitar duplicar el correo.
                            }

                            $userExistente = User::create([
                                'person_id' => $person->id,
                                'email'     => $email,
                                'nickname'  => $docNum,
                                'password'  => Hash::make($docNum), // Guarda como hash el documento usado aquí como contraseña inicial.
                            ]);
                        } elseif ($userExistente->trashed()) { // Si la cuenta existe pero está inactiva, la reactiva.
                            $userExistente->restore(); // Restaura la cuenta encontrada.
                        }

                        $this->asignarRolEcp($userExistente, $rol->id); // Reemplaza el rol ECP previo por el elegido.
                    }
                } else {
                    $sinRol++; // Cuenta personas que se guardarán sin crear o asignar una cuenta.
                }
            }); // Confirma esta fila completa o revierte sus cambios ante un error.
        }

        $msg = "Sincronización aplicada: {$creados} nuevos, {$actualizados} actualizados."; // Resume cuántas personas se crearon o actualizaron.
        if ($sinRol > 0) { // Añade detalle solo si hubo filas sin rol.
            $msg .= " {$sinRol} personas sin rol asignado (solo se guardó su ficha en people, sin crear usuario)."; // Explica qué ocurrió con esas personas.
        }

        if ($request->ajax() || $request->wantsJson()) { // Comprueba si el cliente espera respuesta asíncrona.
            return response()->json(['success' => true, 'message' => $msg]); // Envía el resumen al cliente en JSON.
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', $msg); // Regresa al listado y muestra el mismo resumen como mensaje temporal.
    }


    /**
     * Asigna un rol ECP al usuario, eliminando antes cualquier otro rol ECP previo.
     * Regla de negocio: un usuario = un solo rol en Control ECP.
     */
    private function asignarRolEcp(User $user, int $rolId): void // ───────────────────────────────────────────────────────────────────────── | HELPERS PRIVADOS | ───────────────────────────────────────────────────────────────────────── | asignarRolEcp(): Asigna al usuario el rol de Control ECP indicado; se usa al completar su clasificación de acceso.
    {
        $rolesEcpIds = Role::whereIn('slug', self::ROLES_ECP)->pluck('id')->toArray(); // Consulta solo los identificadores de roles ECP y los convierte en un arreglo. | Obtener IDs de todos los roles ECP

        DB::table('role_user') // Eliminar roles ECP existentes del pivote
            ->where('user_id', $user->id)
            ->whereIn('role_id', $rolesEcpIds)
            ->delete(); // Retira las asignaciones anteriores de este usuario dentro del módulo.

        DB::table('role_user')->insert([ // Asignar el nuevo rol
            'role_id'    => $rolId,
            'user_id'    => $user->id,
            'created_at' => now(), // Registra cuándo se creó la asociación del rol.
            'updated_at' => now(),
        ]); // Inserta la nueva relación en la tabla intermedia user-rol.
    }

    /**
     * Consulta la API de SICEFA según el tipo de persona.
     * Retorna [$registros, $errorMsg].
     *
     * Cuando la URL real de SICEFA esté disponible, reemplazar la constante
     * SICEFA_BASE_URL en el config del módulo y ajustar los campos del mapeo.
     */
    private function consultarSicefa(string $tipo): array // consultarSicefa(): Consulta registros de SICEFA por tipo; se usa para obtener los datos de sincronización.
    {
        $baseUrl = config('controlecp.sicefa_api_url', ''); // Lee la URL de SICEFA desde la configuración del módulo.

        if (empty($baseUrl)) { // Evita intentar conectar si no se configuró la dirección del servicio.
            return [[], 'La URL de la API SICEFA no está configurada en config/controlecp.php (clave sicefa_api_url).']; // URL no configurada: devolver array vacío con aviso
        }

        $endpoint = $tipo === 'aprendices'
            ? rtrim($baseUrl, '/') . '/aprendices'
            : rtrim($baseUrl, '/') . '/instructores'; // Quita la barra final de la URL y agrega la ruta correspondiente al tipo.

        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header'  => 'Accept: application/json',
                ],
            ]); // Prepara opciones HTTP: tiempo máximo de espera y tipo de respuesta esperado.
            $raw = @file_get_contents($endpoint, false, $ctx); // Solicita el endpoint con ese contexto; @ evita imprimir avisos de conexión.

            if ($raw === false) { // La lectura falla, por ejemplo, si el servicio no responde.
                return [[], "No se pudo conectar con {$endpoint}"];
            }

            $data = json_decode($raw, true); // Convierte el JSON recibido en arreglos asociativos de PHP.

            if (!is_array($data)) { // Rechaza respuestas inválidas o con una estructura distinta a un arreglo.
                return [[], 'Respuesta inesperada de la API SICEFA (no es JSON válido).'];
            }

            $registros = isset($data['data']) ? $data['data'] : $data; // Usa la colección anidada cuando la respuesta incluye la clave data. | Normalizar: si la API envuelve en una clave (ej: 'data')

            return [$registros, null]; // Devuelve filas y null para indicar que la consulta no tuvo error.
        } catch (\Throwable $e) {
            return [[], $e->getMessage()]; // Convierte cualquier excepción en el formato de error esperado por quien llama.
        }
    }
}
