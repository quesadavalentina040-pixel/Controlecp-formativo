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

class UserController extends Controller
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
    private function verificarAdmin()
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de usuarios de Control ECP.');
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. LISTADO GENERAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Listado de usuarios con filtros por rol y estado, búsqueda por nombre/documento/correo.
     */
    public function index(Request $request)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        // Roles ECP disponibles para filtros
        $rolesEcp = Role::whereIn('slug', self::ROLES_ECP)->get();

        // Query base: users que tengan al menos un rol ECP
        $query = User::withTrashed()
            ->with(['person', 'roles'])
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP));

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('slug', $request->input('rol')));
        }

        // Filtro por estado: activo = sin deleted_at, inactivo = con deleted_at
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'activo') {
                $query->whereNull('users.deleted_at');
            } elseif ($request->input('estado') === 'inactivo') {
                $query->whereNotNull('users.deleted_at');
            }
        }

        // Búsqueda por nombre (en people), documento (en people), correo (en users)
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
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

        $usuarios = $query->orderBy('users.created_at', 'desc')->paginate(15)->withQueryString();

        // Contadores rápidos
        $totalActivos   = User::withTrashed()
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->count();
        $totalInactivos = User::withTrashed()
            ->whereNotNull('users.deleted_at')
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->count();

        return view('controlecp::administrador.usuarios.index', compact(
            'usuarios',
            'rolesEcp',
            'totalActivos',
            'totalInactivos'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. CREACIÓN MANUAL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Formulario para crear un usuario manualmente.
     */
    public function create()
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $rolesEcp = Role::whereIn('slug', self::ROLES_ECP)->get();

        return view('controlecp::administrador.usuarios.create', compact('rolesEcp'));
    }

    /**
     * Guarda un usuario creado manualmente (person + user + role_user en transacción).
     */
    public function store(Request $request)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $request->validate([
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
        ], [
            'document_number.unique' => 'Ya existe una persona con ese número de documento.',
            'email.unique'           => 'Ese correo ya está registrado en el sistema.',
            'nickname.unique'        => 'Ese nombre de usuario ya está en uso.',
        ]);

        // Validar que el rol seleccionado sea un rol ECP
        $rol = Role::whereIn('slug', self::ROLES_ECP)->findOrFail($request->input('rol_id'));

        DB::transaction(function () use ($request, $rol) {
            // 1. Crear registro en people
            $person = Person::create([
                'document_type'    => $request->input('document_type'),
                'document_number'  => $request->input('document_number'),
                'first_name'       => mb_strtoupper($request->input('first_name')),
                'first_last_name'  => mb_strtoupper($request->input('first_last_name')),
                'second_last_name' => $request->filled('second_last_name')
                    ? mb_strtoupper($request->input('second_last_name')) : null,
                'telephone1'       => $request->input('telephone1'),
                'misena_email'     => $request->input('email'),
            ]);

            // 2. Crear registro en users
            $user = User::create([
                'person_id' => $person->id,
                'email'     => $request->input('email'),
                'nickname'  => $request->input('nickname') ?: null,
                'password'  => Hash::make($request->input('password')),
            ]);

            // 3. Asignar rol (reemplaza cualquier rol ECP previo)
            $this->asignarRolEcp($user, $rol->id);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '¡Usuario creado correctamente en Control ECP!']);
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', '¡Usuario creado correctamente en Control ECP!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. EDICIÓN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Devuelve los datos del usuario como JSON para poblar el modal de edición.
     */
    public function editData($id)
    {
        if (Auth::user() && !Auth::user()->hasRole('controlecp.admin')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $usuario   = User::withTrashed()->with(['person', 'roles'])->findOrFail($id);
        $rolActual = $usuario->roles->whereIn('slug', self::ROLES_ECP)->first();

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
        ]);
    }

    /**
     * Actualiza rol, correo, nickname y, opcionalmente, la contraseña del usuario.
     */
    public function update(Request $request, $id)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $usuario = User::withTrashed()->with(['person', 'roles'])->findOrFail($id);

        $request->validate([
            'first_name'       => 'required|string|max:100',
            'first_last_name'  => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'telephone1'       => 'nullable|string|max:20',
            'email'            => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($usuario->id)],
            'nickname'         => ['nullable', 'string', 'max:60', Rule::unique('users', 'nickname')->ignore($usuario->id)],
            'password'         => 'nullable|string|min:8|confirmed',
            'rol_id'           => 'required|exists:roles,id',
        ]);

        // Validar que el rol sea ECP
        $rol = Role::whereIn('slug', self::ROLES_ECP)->findOrFail($request->input('rol_id'));

        DB::transaction(function () use ($request, $usuario, $rol) {
            // Actualizar datos de person si existe
            if ($usuario->person) {
                $usuario->person->update([
                    'first_name'       => mb_strtoupper($request->input('first_name')),
                    'first_last_name'  => mb_strtoupper($request->input('first_last_name')),
                    'second_last_name' => $request->filled('second_last_name')
                        ? mb_strtoupper($request->input('second_last_name')) : $usuario->person->second_last_name,
                    'telephone1'       => $request->input('telephone1'),
                    'misena_email'     => $request->input('email'),
                ]);
            }

            // Actualizar credenciales del user
            $dataUser = [
                'email'    => $request->input('email'),
                'nickname' => $request->input('nickname') ?: null,
            ];
            if ($request->filled('password')) {
                $dataUser['password'] = Hash::make($request->input('password'));
            }
            $usuario->update($dataUser);

            // Reasignar rol ECP
            $this->asignarRolEcp($usuario, $rol->id);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '¡Usuario actualizado correctamente!']);
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', '¡Usuario actualizado correctamente!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. ACTIVAR / DESACTIVAR (SoftDelete como mecanismo de estado)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Desactiva un usuario (soft-delete en users).
     * Valida que no sea el último admin activo.
     */
    public function desactivar(Request $request, $id)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $usuario = User::with('roles')->findOrFail($id); // Solo activos

        // Regla: no se puede desactivar al último administrador activo
        if ($usuario->hasRole('controlecp.admin')) {
            $adminsActivos = User::whereNull('users.deleted_at')
                ->whereHas('roles', fn($q) => $q->where('slug', 'controlecp.admin'))
                ->count();

            if ($adminsActivos <= 1) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'No se puede desactivar al único Administrador activo de Control ECP.'], 422);
                }
                return redirect()
                    ->route('controlecp.usuarios.index')
                    ->with('error', 'No se puede desactivar al único Administrador activo de Control ECP.');
            }
        }

        // No eliminar al admin que está logueado
        if ($usuario->id === Auth::id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 422);
            }
            return redirect()
                ->route('controlecp.usuarios.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->delete(); // SoftDelete — pone deleted_at

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Usuario «{$usuario->full_name}» desactivado."]);
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', "Usuario «{$usuario->full_name}» desactivado. Su historial se conserva íntegro.");
    }

    /**
     * Reactiva un usuario previamente desactivado (restore del SoftDelete).
     */
    public function activar(Request $request, $id)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $usuario = User::withTrashed()->findOrFail($id);

        if (!$usuario->trashed()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'El usuario ya está activo.']);
            }
            return redirect()->route('controlecp.usuarios.index')->with('info', 'El usuario ya está activo.');
        }

        $usuario->restore();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Usuario «{$usuario->full_name}» reactivado correctamente."]);
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', "Usuario «{$usuario->full_name}» reactivado correctamente.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. HISTORIAL DE USUARIOS DESACTIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Listado de usuarios desactivados — JSON para modal, vista para acceso directo.
     */
    public function historial(Request $request)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $inactivos = User::onlyTrashed()
            ->with(['person', 'roles'])
            ->whereHas('roles', fn($q) => $q->whereIn('slug', self::ROLES_ECP))
            ->orderBy('deleted_at', 'desc')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
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
                ]),
            ]);
        }

        // Acceso directo (sin modal): redirige al index con el modal abierto vía hash
        return redirect()->route('controlecp.usuarios.index', ['modal' => 'historial']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. SINCRONIZACIÓN CON SICEFA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Muestra la vista previa de lo que traería la API de SICEFA,
     * sin escribir nada en la BD todavía.
     */
    public function sincronizarPreview(Request $request)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $tipo = $request->input('tipo', 'instructores'); // 'instructores' | 'aprendices'

        [$registros, $error] = $this->consultarSicefa($tipo);

        if ($error) {
            return redirect()
                ->route('controlecp.usuarios.index')
                ->with('error', "Error al conectar con SICEFA: {$error}");
        }

        // Clasificar cada registro como nuevo o existente (para mostrar vista previa)
        $preview = [];
        foreach ($registros as $reg) {
            $docNum  = $reg['document_number'] ?? null;
            $emailEx = $reg['email'] ?? null;

            // Buscar si ya existe en people por número de documento
            $personaExistente = $docNum
                ? Person::withTrashed()->where('document_number', $docNum)->first()
                : null;

            $cambios = [];
            if ($personaExistente) {
                // Detectar cambios entre lo que trae la API y lo que hay en BD
                $mapeoCampos = [
                    'first_name'       => mb_strtoupper($reg['first_name'] ?? ''),
                    'first_last_name'  => mb_strtoupper($reg['first_last_name'] ?? ''),
                    'second_last_name' => mb_strtoupper($reg['second_last_name'] ?? ''),
                    'misena_email'     => $reg['email'] ?? null,
                ];
                foreach ($mapeoCampos as $campo => $valorApi) {
                    if ((string)($personaExistente->{$campo} ?? '') !== (string)($valorApi ?? '')) {
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
                    : false,
            ];
        }

        $rolesEcp = Role::whereIn('slug', ['controlecp.instructor', 'controlecp.apprentice'])->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'preview'  => $preview,
                'tipo'     => $tipo,
                'rolesEcp' => $rolesEcp->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug]),
            ]);
        }

        // Acceso directo: redirige al index con parámetro para abrir el modal
        return redirect()->route('controlecp.usuarios.index', ['modal' => 'sincronizar']);
    }

    /**
     * Aplica la sincronización confirmada por el admin:
     * escribe/actualiza en people, y si el admin asignó rol, crea también el user.
     */
    public function sincronizarAplicar(Request $request)
    {
        if ($redirect = $this->verificarAdmin()) {
            return $redirect;
        }

        $request->validate([
            'registros'              => 'required|array',
            'registros.*.aplicar'    => 'sometimes|boolean',
            'registros.*.doc_number' => 'required|string',
            'registros.*.rol_id'     => 'nullable|exists:roles,id',
        ]);

        $creados    = 0;
        $actualizados = 0;
        $sinRol     = 0;

        foreach ($request->input('registros') as $reg) {
            // Solo procesar los que el admin marcó para aplicar
            if (empty($reg['aplicar'])) {
                continue;
            }

            $docNum = $reg['doc_number'];

            DB::transaction(function () use ($reg, $docNum, &$creados, &$actualizados, &$sinRol) {
                $personaExistente = Person::withTrashed()->where('document_number', $docNum)->first();

                $datosPersona = [
                    'document_type'    => $reg['document_type'] ?? 'Cédula de ciudadanía',
                    'document_number'  => $docNum,
                    'first_name'       => mb_strtoupper($reg['first_name'] ?? ''),
                    'first_last_name'  => mb_strtoupper($reg['first_last_name'] ?? ''),
                    'second_last_name' => mb_strtoupper($reg['second_last_name'] ?? ''),
                    'misena_email'     => $reg['email'] ?? null,
                    'telephone1'       => $reg['telephone1'] ?? null,
                ];

                if ($personaExistente) {
                    // La API manda sobre los datos personales
                    $personaExistente->update($datosPersona);
                    if ($personaExistente->trashed()) {
                        $personaExistente->restore();
                    }
                    $person = $personaExistente;
                    $actualizados++;
                } else {
                    $person = Person::create($datosPersona);
                    $creados++;
                }

                // Si el admin asignó un rol, crear/actualizar el user
                if (!empty($reg['rol_id'])) {
                    $rol = Role::whereIn('slug', ['controlecp.instructor', 'controlecp.apprentice'])
                        ->find($reg['rol_id']);

                    if ($rol) {
                        $userExistente = User::withTrashed()->where('person_id', $person->id)->first();

                        if (!$userExistente) {
                            $email = $reg['email'] ?? ($docNum . '@sena.edu.co');
                            // Si ese email ya existe en users, usar documento como usuario
                            if (User::withTrashed()->where('email', $email)->exists()) {
                                $email = $docNum . '@controlecp.sena';
                            }

                            $userExistente = User::create([
                                'person_id' => $person->id,
                                'email'     => $email,
                                'nickname'  => $docNum,
                                'password'  => Hash::make($docNum), // Contraseña inicial = documento
                            ]);
                        } elseif ($userExistente->trashed()) {
                            $userExistente->restore();
                        }

                        $this->asignarRolEcp($userExistente, $rol->id);
                    }
                } else {
                    $sinRol++;
                }
            });
        }

        $msg = "Sincronización aplicada: {$creados} nuevos, {$actualizados} actualizados.";
        if ($sinRol > 0) {
            $msg .= " {$sinRol} personas sin rol asignado (solo se guardó su ficha en people, sin crear usuario).";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()
            ->route('controlecp.usuarios.index')
            ->with('success', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Asigna un rol ECP al usuario, eliminando antes cualquier otro rol ECP previo.
     * Regla de negocio: un usuario = un solo rol en Control ECP.
     */
    private function asignarRolEcp(User $user, int $rolId): void
    {
        // Obtener IDs de todos los roles ECP
        $rolesEcpIds = Role::whereIn('slug', self::ROLES_ECP)->pluck('id')->toArray();

        // Eliminar roles ECP existentes del pivote
        DB::table('role_user')
            ->where('user_id', $user->id)
            ->whereIn('role_id', $rolesEcpIds)
            ->delete();

        // Asignar el nuevo rol
        DB::table('role_user')->insert([
            'role_id'    => $rolId,
            'user_id'    => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Consulta la API de SICEFA según el tipo de persona.
     * Retorna [$registros, $errorMsg].
     *
     * Cuando la URL real de SICEFA esté disponible, reemplazar la constante
     * SICEFA_BASE_URL en el config del módulo y ajustar los campos del mapeo.
     */
    private function consultarSicefa(string $tipo): array
    {
        $baseUrl = config('controlecp.sicefa_api_url', '');

        if (empty($baseUrl)) {
            // URL no configurada: devolver array vacío con aviso
            return [[], 'La URL de la API SICEFA no está configurada en config/controlecp.php (clave sicefa_api_url).'];
        }

        $endpoint = $tipo === 'aprendices'
            ? rtrim($baseUrl, '/') . '/aprendices'
            : rtrim($baseUrl, '/') . '/instructores';

        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'header'  => 'Accept: application/json',
                ],
            ]);
            $raw = @file_get_contents($endpoint, false, $ctx);

            if ($raw === false) {
                return [[], "No se pudo conectar con {$endpoint}"];
            }

            $data = json_decode($raw, true);

            if (!is_array($data)) {
                return [[], 'Respuesta inesperada de la API SICEFA (no es JSON válido).'];
            }

            // Normalizar: si la API envuelve en una clave (ej: 'data')
            $registros = isset($data['data']) ? $data['data'] : $data;

            return [$registros, null];
        } catch (\Throwable $e) {
            return [[], $e->getMessage()];
        }
    }
}
