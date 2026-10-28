<?php

namespace Modules\ControlECP\Services;

// User representa a las cuentas que crean o reciben acciones del POE.
use App\Models\User;
// Carbon permite calcular y comparar fechas.
use Carbon\Carbon;
// UploadedFile representa el archivo enviado por el formulario.
use Illuminate\Http\UploadedFile;
// DB permite ejecutar grupos de operaciones dentro de transacciones.
use Illuminate\Support\Facades\DB;
// Storage permite gestionar archivos guardados en discos de Laravel.
use Illuminate\Support\Facades\Storage;
// ValidationException permite devolver errores de validación al formulario.
use Illuminate\Validation\ValidationException;
// Poe representa un documento del repositorio.
use Modules\ControlECP\Entities\Poe;
// PoeLiderHistorial guarda los periodos en que cada usuario fue líder.
use Modules\ControlECP\Entities\PoeLiderHistorial;
// PoeProgramacion representa una fecha programada para entregar una versión.
use Modules\ControlECP\Entities\PoeProgramacion;
// PoeVersion representa una versión cargada de un documento POE.
use Modules\ControlECP\Entities\PoeVersion;

// Centraliza la lógica de negocio para documentos, versiones y programaciones POE.
class PoeService
{
    // Guarda el servicio encargado de registrar avisos.
    protected NotificacionService $notificacionService;

    // Recibe el servicio de notificaciones mediante inyección de dependencias.
    public function __construct(NotificacionService $notificacionService)
    {
        // Conserva la instancia para usarla en los métodos de esta clase.
        $this->notificacionService = $notificacionService;
    }

    /**
     * Obtiene los usuarios con rol Líder (o Instructores/Admins de ECP si no hay líderes específicos).
     */
    // Busca los posibles responsables que pueden seleccionarse como líderes.
    public function obtenerLideres()
    {
        // Busca usuarios con nombres o identificadores de rol relacionados con líder.
        $lideres = User::whereHas('roles', function ($q) {
            // Incluye roles cuyo slug contiene la palabra líder sin acento.
            $q->where('slug', 'like', '%lider%')
              // También admite el término equivalente en inglés.
              ->orWhere('slug', 'like', '%leader%')
              // Revisa el nombre del rol en busca de la palabra líder sin acento.
              ->orWhere('name', 'like', '%lider%')
              // Revisa también la escritura con acento.
              ->orWhere('name', 'like', '%líder%');
        })
        // Carga la información personal de cada usuario en la misma consulta optimizada.
        ->with('person')
        // Ejecuta la consulta y obtiene todos los líderes encontrados.
        ->get();

        // Si no existen o son muy pocos, permitir también instructores o usuarios activos para pruebas
        // Comprueba si la búsqueda de líderes no encontró usuarios.
        if ($lideres->isEmpty()) {
            // Como alternativa, busca instructores o administradores de ControlECP.
            $lideres = User::whereHas('roles', function ($q) {
                // Incluye solo los roles de instructor y administrador del módulo.
                $q->whereIn('slug', ['controlecp.instructor', 'controlecp.admin']);
            })
            // Carga la relación de datos personales para mostrar nombres.
            ->with('person')
            // Ejecuta la consulta alternativa.
            ->get();
        }

        // Devuelve la lista de candidatos encontrados.
        return $lideres;
    }

    /**
     * Formatea el nombre completo del usuario.
     */
    // Construye un nombre para mostrar, con respaldo a nickname o correo.
    public function nombreCompletoUsuario(User $usuario): string
    {
        // Comprueba si el usuario tiene datos de persona relacionados.
        if ($usuario->person) {
            // Guarda la relación en una variable corta para leer sus campos.
            $p = $usuario->person;
            // Combina nombres y apellidos; el segundo apellido es opcional.
            $nombres = trim("{$p->first_name} {$p->first_last_name} " . ($p->second_last_name ?? ''));
            // Devuelve el nombre si quedó con texto; de lo contrario usa nickname o correo.
            return $nombres ?: ($usuario->nickname ?: $usuario->email);
        }
        // Si no hay persona relacionada, devuelve nickname o correo como respaldo.
        return $usuario->nickname ?: $usuario->email;
    }

    /**
     * Obtiene la lista completa de POE con sus relaciones optimizadas (sin N+1).
     */
    // Lista POE y permite una búsqueda opcional por nombre o código.
    public function listarPoes(?string $buscar = null)
    {
        // 1. Recalcula programaciones atrasadas al entrar al módulo
        // Actualiza el estado de programaciones vencidas y genera los avisos que correspondan.
        $this->actualizarEstadosAtrasadas();

        // Inicia la consulta de POE y precarga relaciones que se muestran en el listado.
        $query = Poe::with([
            // Carga el líder actual y sus datos personales.
            'liderActual.person',
            // Carga la versión actual, quién la subió y sus datos personales.
            'versionActual.usuario.person',
            // Carga la programación vigente, su nuevo líder y sus datos personales.
            'programacionVigente.nuevoLider.person',
            // Carga las versiones ordenadas de más reciente a más antigua junto con el usuario.
            'versiones' => fn($q) => $q->orderBy('id_poe_version', 'desc')->with('usuario.person'),
        ]);

        // Añade la búsqueda solo si se recibió un término no vacío.
        if (!empty($buscar)) {
            // Quita espacios externos del término de búsqueda.
            $termino = trim($buscar);
            // Agrupa la búsqueda por nombre o código para que no altere otros filtros.
            $query->where(function ($q) use ($termino) {
                // Busca el término dentro del nombre del POE.
                $q->where('nombre', 'like', "%{$termino}%")
                  // O dentro de su código.
                  ->orWhere('codigo', 'like', "%{$termino}%");
            });
        }

        // Ordena POE del id más alto al más bajo, ejecuta la consulta y devuelve sus resultados.
        return $query->orderBy('id_poe', 'desc')->get();
    }

    /**
     * Sube un nuevo POE con su versión inicial 1.0 y primer registro de líder en una sola transacción.
     */
    // Crea un documento inicial, su versión y el historial de liderazgo asociado.
    public function crearPoe(array $datos, UploadedFile $archivo, User $usuarioAuth): Poe
    {
        // DB::transaction confirma todos los cambios juntos o los revierte si ocurre una excepción.
        return DB::transaction(function () use ($datos, $archivo, $usuarioAuth) {
            // Guardar archivo en disco public/poe/
            // Detecta la extensión y guarda el formato legible para el registro.
            $formato = strtolower($archivo->getClientOriginalExtension()) === 'pdf' ? 'PDF' : 'Word';
            // Guarda el archivo en la carpeta poe del disco public y obtiene su ruta.
            $rutaArchivo = $archivo->store('poe', 'public');

            // Crear el POE
            // Crea el registro principal del documento con líder, código y nombre.
            $poe = Poe::create([
                // Guarda el id del usuario responsable indicado en los datos.
                'id_usuario' => $datos['id_usuario'],
                // Normaliza el código a mayúsculas y quita espacios laterales.
                'codigo'     => strtoupper(trim($datos['codigo'])),
                // Quita espacios laterales del nombre antes de guardarlo.
                'nombre'     => trim($datos['nombre']),
            ]);

            // Crear versión 1.0 inicial
            // Crea el primer registro de versión del documento.
            PoeVersion::create([
                // Relaciona la versión con el POE recién creado.
                'id_poe'              => $poe->id_poe,
                // Registra al usuario autenticado que cargó el archivo.
                'id_usuario'          => $usuarioAuth->id,
                // La versión inicial no corresponde a una programación de entrega.
                'id_poe_programacion' => null,
                // Asigna el número inicial de versión.
                'numero_version'      => '1.0',
                // Describe que este es el registro inicial.
                'cambio'              => 'Versión inicial',
                // Guarda la ruta devuelta por el almacenamiento.
                'archivo'             => $rutaArchivo,
                // Guarda si el archivo es PDF o Word.
                'formato'             => $formato,
                // Marca esta como la versión vigente.
                'es_actual'           => true,
                // Registra fecha y hora actuales.
                'fecha'               => Carbon::now(),
            ]);

            // Crear primer registro en historial de líderes
            // Registra el inicio del periodo del usuario asignado al POE.
            PoeLiderHistorial::create([
                // Relaciona el historial con el documento creado.
                'id_poe'       => $poe->id_poe,
                // Guarda el id del líder indicado para el documento.
                'id_usuario'   => $datos['id_usuario'],
                // Define hoy como inicio de su periodo.
                'fecha_inicio' => Carbon::today(),
                // Deja el fin vacío porque el periodo sigue vigente.
                'fecha_fin'    => null,
            ]);

            // Devuelve el documento recién creado al código que llamó el servicio.
            return $poe;
        });
    }

    /**
     * Calcula el siguiente número de versión (1.0 -> 2.0 -> 3.0).
     */
    // Obtiene el siguiente número mayor para la versión del documento.
    public function calcularSiguienteVersion(Poe $poe): string
    {
        // Consulta solo los números de versión de este POE.
        $versiones = PoeVersion::where('id_poe', $poe->id_poe)->pluck('numero_version');
        // Empieza en uno para que la siguiente versión sea al menos 2.0.
        $maxMajor = 1;

        // Revisa los números de versión existentes.
        foreach ($versiones as $v) {
            // Extrae los dígitos iniciales del número de versión.
            if (preg_match('/^(\d+)/', (string)$v, $matches)) {
                // Convierte el número extraído a entero.
                $major = (int)$matches[1];
                // Conserva el valor más alto encontrado.
                if ($major > $maxMajor) {
                    $maxMajor = $major;
                }
            }
        }

        // Devuelve el siguiente número principal en formato decimal terminado en .0.
        return ($maxMajor + 1) . '.0';
    }

    /**
     * Sube una nueva versión del POE y evalúa si cumple una programación pendiente.
     */
    // Guarda una versión nueva y, cuando corresponda, cierra la programación activa.
    public function subirNuevaVersion(Poe $poe, array $datos, UploadedFile $archivo, User $usuarioAuth): PoeVersion
    {
        // Agrupa cambios de archivo y base de datos en una transacción.
        return DB::transaction(function () use ($poe, $datos, $archivo, $usuarioAuth) {
            // Detecta la extensión para registrar el formato del nuevo archivo.
            $formato = strtolower($archivo->getClientOriginalExtension()) === 'pdf' ? 'PDF' : 'Word';
            // Guarda el archivo en el disco público y conserva su ruta.
            $rutaArchivo = $archivo->store('poe', 'public');
            // Calcula el siguiente número disponible para este POE.
            $siguienteVersion = $this->calcularSiguienteVersion($poe);

            // Desmarcar versión actual previa
            // Quita la marca de versión vigente a todas las versiones previas del documento.
            PoeVersion::where('id_poe', $poe->id_poe)->update(['es_actual' => false]);

            // Verificar si hay programación vigente (Pendiente o Atrasada, no cancelada)
            // Busca una entrega pendiente o atrasada que todavía no haya sido cancelada.
            $progVigente = PoeProgramacion::where('id_poe', $poe->id_poe)
                // Excluye las programaciones que fueron canceladas.
                ->where('cancelada', false)
                // Considera únicamente los estados que aún pueden cumplirse.
                ->whereIn('estado', ['Pendiente', 'Atrasada'])
                // Obtiene el primer registro que coincide, o null si no hay.
                ->first();

            // Inicializa el id de programación cumplida; null significa que no se cerrará ninguna.
            $idProgCumplida = null;
            // Inicializa el id del nuevo líder; se asigna al completar una programación.
            $nuevoLiderAsignado = null;

            // Se cumple la programación si:
            // 1. Quien sube es el nuevo líder indicado en la programación.
            // 2. O si el Administrador sube y se indicó expresamente o existe la entrega vigente del nuevo líder.
            // Continúa con la revisión solo si se encontró una programación activa.
            if ($progVigente) {
                // Comprueba si quien sube el archivo es el nuevo líder programado.
                $esNuevoLider = ($usuarioAuth->id == $progVigente->id_usuario_nuevo_lider);
                // Permite cumplir si se solicitó expresamente, si sube el nuevo líder o si sube un administrador.
                $forzarCumplimiento = !empty($datos['cumplir_programacion']) || $esNuevoLider || $usuarioAuth->hasRole('controlecp.admin');

                // Cierra la programación cuando se cumple alguna condición permitida.
                if ($forzarCumplimiento) {
                    // Conserva el id de la programación que será marcada como cumplida.
                    $idProgCumplida = $progVigente->id_poe_programacion;
                    // Conserva el id del usuario que pasará a ser el nuevo líder.
                    $nuevoLiderAsignado = $progVigente->id_usuario_nuevo_lider;

                    // 1. Marcar programación como cumplida con fecha real de subida
                    // Cambia el estado de la programación a cumplida.
                    $progVigente->estado = 'Cumplida';
                    // Registra el momento real en que se cumplió la entrega.
                    $progVigente->fecha_cumplimiento = Carbon::now();
                    // Persiste los cambios de estado y fecha.
                    $progVigente->save();

                    // 2. Cambio de líder en el POE
                    // Asigna al nuevo líder como responsable actual del documento.
                    $poe->id_usuario = $nuevoLiderAsignado;
                    // Guarda el nuevo responsable del POE.
                    $poe->save();

                    // 3. Cerrar el historial de líder vigente y abrir el nuevo
                    // Busca el periodo abierto del historial de este POE.
                    PoeLiderHistorial::where('id_poe', $poe->id_poe)
                        // Selecciona solo el periodo que todavía no tiene fecha de fin.
                        ->whereNull('fecha_fin')
                        // Cierra dicho periodo con la fecha de hoy.
                        ->update(['fecha_fin' => Carbon::today()]);

                    // Crea un registro de historial para el nuevo líder.
                    PoeLiderHistorial::create([
                        // Relaciona el nuevo periodo con el POE.
                        'id_poe'       => $poe->id_poe,
                        // Guarda al usuario que asume el liderazgo.
                        'id_usuario'   => $nuevoLiderAsignado,
                        // Define la fecha de inicio del nuevo periodo.
                        'fecha_inicio' => Carbon::today(),
                        // Deja el periodo abierto mientras este usuario sea el líder actual.
                        'fecha_fin'    => null,
                    ]);
                }
            }

            // Crear la nueva versión
            // Registra los datos de la versión recién subida.
            $nuevaVersion = PoeVersion::create([
                // Relaciona la versión con su documento POE.
                'id_poe'              => $poe->id_poe,
                // Guarda quién subió el archivo.
                'id_usuario'          => $usuarioAuth->id,
                // Relaciona la programación cumplida o deja null si no se cumplió ninguna.
                'id_poe_programacion' => $idProgCumplida,
                // Guarda el número de versión calculado.
                'numero_version'      => $siguienteVersion,
                // Guarda la descripción de cambios sin espacios externos.
                'cambio'              => trim($datos['cambio']),
                // Guarda la ruta del archivo nuevo.
                'archivo'             => $rutaArchivo,
                // Guarda el tipo del archivo.
                'formato'             => $formato,
                // Marca esta versión como la actual.
                'es_actual'           => true,
                // Registra la fecha y hora de subida.
                'fecha'               => Carbon::now(),
            ]);

            // Si se cumplió una programación, emitir notificación evento 5
            // Comprueba que realmente exista una programación cerrada para emitir el aviso.
            if ($progVigente && $idProgCumplida) {
                // Envía el evento 5 que informa que la entrega POE se cumplió.
                $this->enviarAvisosPoe(5, $progVigente, $usuarioAuth);
            }

            // Devuelve el registro de versión que se acaba de crear.
            return $nuevaVersion;
        });
    }

    /**
     * Programa la fecha límite para que un nuevo líder suba una versión del POE.
     */
    // Crea una programación de entrega y notifica a los usuarios involucrados.
    public function programarSubida(Poe $poe, array $datos, User $usuarioAuth): PoeProgramacion
    {
        // Convierte el valor recibido a fecha y normaliza la hora a medianoche.
        $fechaLimite = Carbon::parse($datos['fecha_limite'])->startOfDay();
        // Obtiene la fecha de hoy sin componente de hora para compararla por día.
        $hoy = Carbon::today();

        // if inicia una condición: pregunta si la fecha elegida es anterior a la fecha de hoy.
        if ($fechaLimite->lt($hoy)) {
            // throw detiene la operación y lanza un error para impedir que se guarde una fecha pasada.
            throw ValidationException::withMessages([
                // withMessages asocia este texto de error con el campo fecha_limite.
                'fecha_limite' => 'La fecha límite no puede ser anterior a hoy.',
            // Cierra el arreglo que contiene los mensajes de validación.
            ]);
        // Cierra la condición; si la fecha no es pasada, el código continúa normalmente.
        }

        // Verifica que el nuevo líder no sea el mismo que el líder actual.
        if ((int)$datos['id_usuario_nuevo_lider'] === (int)$poe->id_usuario) {
            // Lanza un error de validación asociado al campo del nuevo líder.
            throw ValidationException::withMessages([
                // Mensaje que se mostrará si se eligió al líder que ya estaba asignado.
                'id_usuario_nuevo_lider' => 'El nuevo líder debe ser diferente del líder actual del POE.',
            ]);
        }

        // Solo una programación vigente por POE
        // Busca si ya existe una programación no cancelada pendiente o atrasada para este POE.
        $existente = PoeProgramacion::where('id_poe', $poe->id_poe)
            // Excluye programaciones marcadas como canceladas.
            ->where('cancelada', false)
            // Considera las programaciones aún activas.
            ->whereIn('estado', ['Pendiente', 'Atrasada'])
            // Obtiene una coincidencia, si existe.
            ->first();

        // Evita crear una segunda programación activa para el mismo POE.
        if ($existente) {
            // Detiene el proceso y comunica los datos de la programación que ya existe.
            throw ValidationException::withMessages([
                // Asocia el mensaje al formulario del POE e incluye estado y fecha límite existente.
                'poe' => "El POE {$poe->codigo} ya tiene una programación vigente ({$existente->estado}) con fecha límite {$existente->fecha_limite->format('d/m/Y')}.",
            ]);
        }

        // Guarda programación y notificaciones como una sola operación atómica.
        return DB::transaction(function () use ($poe, $datos, $usuarioAuth) {
            // Crea la nueva programación de entrega.
            $programacion = PoeProgramacion::create([
                // Relaciona la programación con el POE.
                'id_poe'                  => $poe->id_poe,
                // Guarda el líder que tenía el POE antes de programar.
                'id_usuario_lider_actual' => $poe->id_usuario,
                // Guarda el líder que debe subir la nueva versión.
                'id_usuario_nuevo_lider'  => $datos['id_usuario_nuevo_lider'],
                // Registra la fecha límite elegida.
                'fecha_limite'            => $datos['fecha_limite'],
                // Aún no existe una fecha anterior porque es nueva.
                'fecha_original'          => null,
                // Inicia la programación como pendiente.
                'estado'                  => 'Pendiente',
                // Marca la programación como no cancelada.
                'cancelada'               => false,
                // No hay motivo de cambio inicial.
                'motivo_cambio'           => null,
                // Todavía no se ha cumplido, así que no hay fecha de cumplimiento.
                'fecha_cumplimiento'      => null,
            ]);

            // Notificación evento 1: Se programa una subida
            // Envía el aviso de nueva programación a los destinatarios correspondientes.
            $this->enviarAvisosPoe(1, $programacion, $usuarioAuth);

            // Devuelve el nuevo registro al controlador.
            return $programacion;
        });
    }

    /**
     * Reprograma una fecha límite o nuevo líder de la programación.
     */
    // Actualiza la fecha, el responsable y el motivo de una programación existente.
    public function reprogramar(PoeProgramacion $prog, array $datos, User $usuarioAuth): PoeProgramacion
    {
        // Impide cambios en programaciones que ya fueron completadas.
        if ($prog->estado === 'Cumplida') {
            // Devuelve un error de validación asociado al estado.
            throw ValidationException::withMessages([
                // Explica por qué la programación cumplida no se puede reprogramar.
                'estado' => 'No se puede reprogramar una entrega que ya fue cumplida.',
            ]);
        }

        // Convierte la nueva fecha recibida y la normaliza al inicio del día.
        $nuevaFecha = Carbon::parse($datos['fecha_limite'])->startOfDay();
        // Obtiene el día actual para verificar que la nueva fecha no sea pasada.
        $hoy = Carbon::today();

        // Rechaza una nueva fecha anterior a hoy.
        if ($nuevaFecha->lt($hoy)) {
            // Lanza un error de validación dirigido al campo de fecha.
            throw ValidationException::withMessages([
                // Mensaje que verá el usuario si intenta elegir una fecha pasada.
                'fecha_limite' => 'La nueva fecha límite no puede ser anterior a hoy.',
            ]);
        }

        // Usa el líder nuevo enviado o conserva el que ya tenía la programación.
        $nuevoLider = $datos['id_usuario_nuevo_lider'] ?? $prog->id_usuario_nuevo_lider;
        // Valida que el nuevo líder no sea el líder actual del documento.
        if ((int)$nuevoLider === (int)$prog->id_usuario_lider_actual) {
            // Informa el error ligado al campo de selección de nuevo líder.
            throw ValidationException::withMessages([
                // Mensaje para indicar que debe elegirse otra persona.
                'id_usuario_nuevo_lider' => 'El nuevo líder debe ser diferente del líder actual del POE.',
            ]);
        }

        // Mantiene los cambios y sus notificaciones dentro de una transacción.
        return DB::transaction(function () use ($prog, $datos, $nuevaFecha, $nuevoLider, $hoy, $usuarioAuth) {
            // Guardar fecha previa en fecha_original si cambia
            // Solo compara fechas si la programación ya tenía una fecha límite.
            if ($prog->fecha_limite && $prog->fecha_limite->format('Y-m-d') !== $nuevaFecha->format('Y-m-d')) {
                // Conserva la fecha anterior como antecedente del cambio.
                $prog->fecha_original = $prog->fecha_limite;
            }

            // Asigna la nueva fecha límite.
            $prog->fecha_limite = $nuevaFecha;
            // Asigna el nuevo responsable de la entrega.
            $prog->id_usuario_nuevo_lider = $nuevoLider;
            // Guarda el motivo del cambio quitando espacios externos.
            $prog->motivo_cambio = trim($datos['motivo_cambio']);
            // Deja la programación como no cancelada.
            $prog->cancelada = false;

            // Si estaba atrasada y la nueva fecha es hoy o futura, vuelve a Pendiente
            // Comprueba si la entrega atrasada ahora tiene una fecha vigente.
            if ($prog->estado === 'Atrasada' && $nuevaFecha->gte($hoy)) {
                // Cambia el estado a pendiente porque aún puede cumplirse en el plazo.
                $prog->estado = 'Pendiente';
            }

            // Guarda en la base de datos los cambios de la programación.
            $prog->save();

            // Notificación evento 2: Se reprograma
            // Notifica a los destinatarios que cambió la fecha o el responsable.
            $this->enviarAvisosPoe(2, $prog, $usuarioAuth, 'reprogramada');

            // Devuelve la programación actualizada.
            return $prog;
        });
    }

    /**
     * Cancela una programación con motivo obligatorio.
     */
    // Cancela una programación pendiente o atrasada y comunica el motivo.
    public function cancelar(PoeProgramacion $prog, string $motivo, User $usuarioAuth): PoeProgramacion
    {
        // Impide cancelar una programación que ya se cumplió.
        if ($prog->estado === 'Cumplida') {
            // Lanza un error de validación con explicación.
            throw ValidationException::withMessages([
                // Indica el motivo por el que no se permite cancelar.
                'estado' => 'No se puede cancelar una programación que ya fue cumplida.',
            ]);
        }

        // Actualiza la programación y crea sus notificaciones dentro de una transacción.
        return DB::transaction(function () use ($prog, $motivo, $usuarioAuth) {
            // Marca la programación como cancelada.
            $prog->cancelada = true;
            // Registra el motivo sin espacios sobrantes al principio o al final.
            $prog->motivo_cambio = trim($motivo);
            // Guarda los cambios en el registro.
            $prog->save();

            // Notificación evento 2: Se cancela
            // Genera el aviso indicando que el evento es una cancelación.
            $this->enviarAvisosPoe(2, $prog, $usuarioAuth, 'cancelada');

            // Devuelve el registro cancelado.
            return $prog;
        });
    }

    /**
     * Recalcula estados de programaciones atrasadas y envía avisos preventivos y de atraso.
     */
    // Cambia a atrasadas las programaciones vencidas y avisa cuando faltan tres días.
    public function actualizarEstadosAtrasadas(): int
    {
        // Obtiene la fecha de hoy para compararla con los vencimientos.
        $hoy = Carbon::today();
        // Inicializa el contador de programaciones que cambian a atrasadas.
        $cambiadas = 0;

        // 1. Marcar atrasadas las que pasaron de fecha límite y siguen pendientes
        // Busca programaciones no canceladas cuyo estado siga pendiente.
        $vencidas = PoeProgramacion::with(['poe', 'liderActual', 'nuevoLider'])
            // Excluye registros cancelados.
            ->where('cancelada', false)
            // Incluye solo los que todavía figuran como pendientes.
            ->where('estado', 'Pendiente')
            // Selecciona las fechas límite anteriores a hoy.
            ->whereDate('fecha_limite', '<', $hoy)
            // Ejecuta la consulta y precarga las relaciones indicadas.
            ->get();

        // Procesa una por una las programaciones que ya vencieron.
        foreach ($vencidas as $prog) {
            // Cambia el estado del registro vencido.
            $prog->estado = 'Atrasada';
            // Persiste el nuevo estado.
            $prog->save();
            // Suma una unidad al total de registros actualizados.
            $cambiadas++;

            // Evento 4: Pasó la fecha límite sin subir (Atrasada)
            // Envía el aviso de atraso, sin usuario causante porque es un proceso del sistema.
            $this->enviarAvisosPoe(4, $prog, null);
        }

        // 2. Avisos preventivos: Faltan 3 días para la fecha límite
        // Calcula el día que corresponde a tres días después de hoy.
        $tresDiasDespues = $hoy->copy()->addDays(3);
        // Busca programaciones pendientes con fecha límite exactamente dentro de tres días.
        $porVencer = PoeProgramacion::with(['poe', 'liderActual', 'nuevoLider'])
            // Excluye programaciones canceladas.
            ->where('cancelada', false)
            // Solo revisa entregas que siguen pendientes.
            ->where('estado', 'Pendiente')
            // Selecciona las que vencen en la fecha calculada.
            ->whereDate('fecha_limite', $tresDiasDespues)
            // Ejecuta la consulta.
            ->get();

        // Envía un aviso preventivo por cada programación próxima a vencer.
        foreach ($porVencer as $prog) {
            // Evento 3: Faltan 3 días
            // Notifica sin usuario causante, ya que el aviso lo genera la comprobación automática.
            $this->enviarAvisosPoe(3, $prog, null);
        }

        // Informa al llamador cuántas programaciones pasaron a estado atrasado.
        return $cambiadas;
    }

    /**
     * Envía notificaciones a Líder Actual, Nuevo Líder y Administradores según el evento.
     * Eventos:
     * 1. Se programa una subida
     * 2. Se reprograma o cancela
     * 3. Faltan 3 días para la fecha límite
     * 4. Pasó la fecha límite sin subir (atrasada)
     * 5. Se cumple la subida
     */
    // Prepara y crea avisos POE para los líderes y administradores relacionados.
    public function enviarAvisosPoe(int $evento, PoeProgramacion $prog, ?User $usuarioCausante = null, ?string $accion = null): void
    {
        // Usa la relación ya cargada o busca el POE por su id.
        $poe = $prog->poe ?: Poe::find($prog->id_poe);
        // Termina sin notificar si el documento ya no existe.
        if (!$poe) return;

        // Obtiene el código para incluirlo en los textos del aviso.
        $codigoPoe = $poe->codigo;
        // Conserva el id de la programación asociada a la notificación.
        $idProg = $prog->id_poe_programacion;
        // Convierte la fecha límite a formato día/mes/año o deja vacío si no existe.
        $fechaLimiteStr = $prog->fecha_limite ? $prog->fecha_limite->format('d/m/Y') : '';

        // Recolectar destinatarios: líder actual, nuevo líder y administradores
        // Inicializa los receptores con el líder actual y el nuevo líder.
        $destinatarios = collect([
            // Añade el id del líder actual de la programación.
            $prog->id_usuario_lider_actual,
            // Añade el id del líder que debe cumplir la entrega.
            $prog->id_usuario_nuevo_lider,
        ]);

        // Busca los ids de usuarios que tienen el rol administrador de ControlECP.
        $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.admin'))->pluck('id');
        // Une líderes y administradores, elimina ids repetidos y valores vacíos.
        $destinatarios = $destinatarios->merge($admins)->unique()->filter();

        // Determinar título, mensaje y clave única para no duplicar
        // Inicializa el título antes de asignarlo según el tipo de evento.
        $titulo = '';
        // Inicializa el mensaje antes de asignarlo según el tipo de evento.
        $mensaje = '';
        // Crea una base para identificar el POE, la programación y el evento.
        $claveBase = "poe_{$idProg}_evt_{$evento}";

        // Selecciona el contenido que corresponde al número de evento.
        switch ($evento) {
            // Caso 1: se creó una programación nueva.
            case 1:
                // Define el título del aviso de programación.
                $titulo = "Nueva programación de POE: {$codigoPoe}";
                // Explica la nueva entrega y su fecha límite.
                $mensaje = "Se ha programado la subida de una nueva versión del POE {$codigoPoe} con fecha límite para el {$fechaLimiteStr}.";
                // Añade un sufijo para identificar que el evento fue creado.
                $claveBase .= "_creada";
                // Termina este caso para no ejecutar los siguientes.
                break;

            // Caso 2: se modificó o canceló una programación.
            case 2:
                // Distingue la cancelación de una reprogramación.
                if ($accion === 'cancelada') {
                    // Define el título de la cancelación.
                    $titulo = "Programación cancelada: POE {$codigoPoe}";
                    // Explica la cancelación e incluye el motivo guardado.
                    $mensaje = "Se ha cancelado la programación del POE {$codigoPoe}. Motivo: {$prog->motivo_cambio}.";
                    // Añade una marca temporal para identificar esta cancelación específica.
                    $claveBase .= "_canc_" . time();
                // Si la acción no es cancelar, se comunica una reprogramación.
                } else {
                    // Define el título de la reprogramación.
                    $titulo = "Reprogramación de POE: {$codigoPoe}";
                    // Indica la nueva fecha límite y el motivo.
                    $mensaje = "La entrega del POE {$codigoPoe} fue reprogramada para el {$fechaLimiteStr}. Motivo: {$prog->motivo_cambio}.";
                    // Añade una marca temporal para identificar esta reprogramación.
                    $claveBase .= "_reprog_" . time();
                }
                // Termina el caso 2.
                break;

            // Caso 3: faltan tres días para el vencimiento.
            case 3:
                // Define el título del recordatorio de próximo vencimiento.
                $titulo = "Próximo vencimiento POE: {$codigoPoe}";
                // Informa cuántos días faltan y cuál es la fecha límite.
                $mensaje = "Faltan 3 días para la fecha límite ({$fechaLimiteStr}) de subida de la nueva versión del POE {$codigoPoe}.";
                // Añade un sufijo que identifica el aviso preventivo.
                $claveBase .= "_aviso3dias";
                // Termina el caso 3.
                break;

            // Caso 4: ya pasó la fecha límite y la entrega no se registró.
            case 4:
                // Define el título del aviso de atraso.
                $titulo = "POE atrasado: {$codigoPoe}";
                // Informa que venció la fecha límite sin entrega.
                $mensaje = "La fecha límite ({$fechaLimiteStr}) para la subida de la nueva versión del POE {$codigoPoe} ha vencido sin registrar entrega.";
                // Añade un sufijo que identifica el aviso de atraso.
                $claveBase .= "_atrasado";
                // Termina el caso 4.
                break;

            // Caso 5: se cumplió la subida programada.
            case 5:
                // Define el título de confirmación de cumplimiento.
                $titulo = "Subida de POE cumplida: {$codigoPoe}";
                // Confirma que la nueva versión fue subida y que se actualizó el liderazgo.
                $mensaje = "Se ha subido exitosamente la nueva versión del POE {$codigoPoe}, completando la programación y actualizando el cargo de líder.";
                // Añade un sufijo para identificar el cumplimiento.
                $claveBase .= "_cumplida";
                // Termina el caso 5.
                break;
        }

        // Crea una notificación para cada usuario destinatario.
        foreach ($destinatarios as $idDestinatario) {
            // Llama al servicio común para validar y guardar el aviso.
            $this->notificacionService->crear([
                // Define el usuario que recibirá este aviso.
                'id_usuario_receptor' => $idDestinatario,
                // Registra quién causó el evento o null si fue un proceso automático.
                'id_usuario_causante' => $usuarioCausante?->id,
                // Identifica la sección del módulo donde se originó.
                'modulo'              => 'Repositorio POE',
                // Guarda el título según el evento.
                'titulo'              => $titulo,
                // Guarda el mensaje explicativo.
                'mensaje'             => $mensaje,
                // Informa que el aviso dirige a un elemento de tipo POE.
                'referencia_tipo'     => 'poe',
                // Guarda el id del POE relacionado.
                'referencia_id'       => $poe->id_poe,
                // Guarda el id de la programación relacionada.
                'id_poe_programacion' => $idProg,
                // Genera una clave distinta por evento y destinatario para evitar duplicados.
                'clave_evento'        => "{$claveBase}_usr_{$idDestinatario}",
                // Agrupa notificaciones relacionadas con el mismo POE.
                'clave_agrupacion'    => "poe_{$poe->id_poe}",
            ]);
        }
    }

    /**
     * Retorna el historial de líderes y las versiones subidas por cada uno para un POE.
     */
    // Obtiene historial de responsables y versiones del documento indicado.
    public function obtenerHistorialLideres(Poe $poe)
    {
        // Consulta los periodos de liderazgo de este POE.
        $periodosLider = PoeLiderHistorial::where('id_poe', $poe->id_poe)
            // Carga el usuario de cada periodo y sus datos personales.
            ->with(['usuario.person'])
            // Ordena los periodos desde el más reciente.
            ->orderBy('fecha_inicio', 'desc')
            // Ejecuta la consulta.
            ->get();

        // Consulta las versiones de este POE.
        $versiones = PoeVersion::where('id_poe', $poe->id_poe)
            // Carga quién subió cada versión y sus datos personales.
            ->with(['usuario.person'])
            // Ordena las versiones desde la más reciente.
            ->orderBy('id_poe_version', 'desc')
            // Ejecuta la consulta.
            ->get();

        // Devuelve juntos el POE, su historial de responsables y sus versiones.
        return [
            // Modelo del documento consultado.
            'poe'       => $poe,
            // Lista de periodos de liderazgo.
            'historial' => $periodosLider,
            // Lista de versiones cargadas.
            'versiones' => $versiones,
        ];
    }
}
