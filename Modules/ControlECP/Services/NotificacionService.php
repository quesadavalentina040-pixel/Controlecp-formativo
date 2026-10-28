<?php

namespace Modules\ControlECP\Services;

// Carbon permite crear y dar formato a fechas y horas.
use Carbon\Carbon;
// LengthAwarePaginator es el tipo de resultado de una paginación con total de páginas.
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
// Collection representa listas de resultados de Eloquent.
use Illuminate\Support\Collection;
// Notification es el modelo que representa los avisos guardados.
use Modules\ControlECP\Entities\Notification;

// Esta clase concentra la creación, consulta y actualización de notificaciones.
class NotificacionService
{
    // Palabras y módulos sensibles de acompañamiento que nunca deben exponerse
    // Guarda los nombres de módulos cuyo contenido debe ocultarse por privacidad.
    private array $modulosSensibles = ['acompañamiento', 'acompaniamiento', 'psicología', 'psicologia', 'seguimiento_clinico'];
    // Guarda fragmentos de palabras sensibles que no deben aparecer en los avisos.
    private array $palabrasSensibles = ['psicolog', 'diagnost', 'depres', 'ansied', 'conducta', 'terapia', 'crisis', 'clinico', 'clinica'];

    // Registra una nueva notificación validando duplicados, agrupación y privacidad
    // $datos contiene receptor, evento y opcionalmente título, mensaje y referencias.
    public function crear(array $datos): Notification
    {
        // Lee el id del usuario que recibirá el aviso.
        $idReceptor      = $datos['id_usuario_receptor'];
        // Lee la clave que identifica de forma única el evento notificado.
        $claveEvento     = $datos['clave_evento'];
        // Lee la clave opcional que agrupa avisos relacionados.
        $claveAgrupacion = $datos['clave_agrupacion'] ?? null;
        // Usa el módulo enviado o "General" cuando no se especificó.
        $modulo          = $datos['modulo'] ?? 'General';
        // Usa el título enviado o un título genérico por defecto.
        $titulo          = $datos['titulo'] ?? 'Nueva notificación';
        // Lee el mensaje o conserva null si no se proporcionó.
        $mensaje         = $datos['mensaje'] ?? null;

        // Regla 11: Sanitizar si es acompañamiento o contiene información sensible
        // Sustituye el título y el mensaje por textos privados cuando el contenido es sensible.
        [$titulo, $mensaje] = $this->protegerContenidoAcompaniamiento($modulo, $titulo, $mensaje);

        // Regla 10: Evitar duplicados por clave de evento exacta para el mismo receptor
        // Busca si el mismo usuario ya tiene registrada esta clave de evento.
        $existentePorEvento = Notification::where('id_usuario_receptor', $idReceptor)
            // Limita la búsqueda a la clave concreta de este evento.
            ->where('clave_evento', $claveEvento)
            // Ejecuta la consulta y obtiene el primer aviso coincidente.
            ->first();

        // Comprueba si ya se había registrado este mismo evento para el receptor.
        if ($existentePorEvento) {
            // Devuelve el aviso existente y evita crear un duplicado.
            return $existentePorEvento;
        }

        // Evita incremento duplicado si el evento individual ya fue procesado en una agrupación
        // Construye una clave de caché distinta para cada receptor y evento.
        $cacheClaveEvento = "notif_evt_proc_{$idReceptor}_{$claveEvento}";
        // Revisa si la caché indica que el evento ya se procesó.
        if (\Illuminate\Support\Facades\Cache::has($cacheClaveEvento)) {
            // Solo puede buscar agrupaciones cuando se proporcionó una clave.
            if ($claveAgrupacion) {
                // Busca un aviso agrupado del mismo usuario con esa clave.
                $existenteAgrupada = Notification::where('id_usuario_receptor', $idReceptor)
                    // Restringe la consulta a la agrupación correspondiente.
                    ->where('clave_agrupacion', $claveAgrupacion)
                    // Obtiene el primer aviso de esa agrupación si existe.
                    ->first();
                // Si ya existe la agrupación, reutiliza ese aviso y termina.
                if ($existenteAgrupada) {
                    return $existenteAgrupada;
                }
            }
        }

        // Regla 7: Agrupar eventos repetidos no leídos con la misma clave de agrupación
        // Solo intenta agrupar cuando el evento trae una clave de agrupación.
        if ($claveAgrupacion) {
            // Busca un aviso no leído del mismo usuario y agrupación.
            $agrupada = Notification::where('id_usuario_receptor', $idReceptor)
                // Selecciona únicamente los avisos del receptor indicado.
                ->where('clave_agrupacion', $claveAgrupacion)
                // No suma eventos en avisos que el usuario ya leyó.
                ->where('leida', false)
                // Ejecuta la búsqueda del primer aviso agrupable.
                ->first();

            // Si la agrupación existe, actualiza el aviso existente.
            if ($agrupada) {
                // Suma uno a la cantidad de eventos incluidos en la notificación.
                $agrupada->cantidad += 1;
                // Actualiza la fecha de generación al momento actual.
                $agrupada->fecha_generacion = Carbon::now();

                // Reemplaza el marcador {cantidad} en el título o mensaje si existe
                // Comprueba si el título nuevo tiene el marcador de cantidad.
                if (str_contains($titulo, '{cantidad}')) {
                    // Cambia el marcador por el total actualizado.
                    $agrupada->titulo = str_replace('{cantidad}', (string)$agrupada->cantidad, $titulo);
                }
                // Comprueba que haya mensaje y que contenga el marcador.
                if ($mensaje && str_contains($mensaje, '{cantidad}')) {
                    // Cambia el marcador del mensaje por el total actualizado.
                    $agrupada->mensaje = str_replace('{cantidad}', (string)$agrupada->cantidad, $mensaje);
                }

                // Persiste los cambios del aviso agrupado en la base de datos.
                $agrupada->save();
                // Recuerda durante 30 días que este evento ya se procesó.
                \Illuminate\Support\Facades\Cache::put($cacheClaveEvento, true, now()->addDays(30));
                // Devuelve el aviso agrupado actualizado.
                return $agrupada;
            }
        }

        // Registra en caché que el evento ya fue procesado para prevenir repeticiones.
        \Illuminate\Support\Facades\Cache::put($cacheClaveEvento, true, now()->addDays(30));

        // Reemplazo inicial de {cantidad} para primera notificación
        // En el primer aviso, sustituye el marcador por la cantidad inicial de uno.
        $tituloFinal  = str_replace('{cantidad}', '1', $titulo);
        // Hace la misma sustitución en el mensaje, o deja null si no hay mensaje.
        $mensajeFinal = $mensaje ? str_replace('{cantidad}', '1', $mensaje) : null;

        // Crea y guarda el nuevo aviso, y devuelve el modelo recién creado.
        return Notification::create([
            // Identifica al usuario que recibe la notificación.
            'id_usuario_receptor' => $idReceptor,
            // Registra al usuario que causó el evento, si fue indicado.
            'id_usuario_causante' => $datos['id_usuario_causante'] ?? null,
            // Guarda el módulo donde ocurrió el evento.
            'modulo'              => $modulo,
            // Guarda el título con la cantidad ya reemplazada.
            'titulo'              => $tituloFinal,
            // Guarda el mensaje final, que puede ser null.
            'mensaje'             => $mensajeFinal,
            // Guarda el tipo del elemento al que dirige el aviso, si aplica.
            'referencia_tipo'     => $datos['referencia_tipo'] ?? null,
            // Guarda el id del elemento relacionado, si aplica.
            'referencia_id'       => $datos['referencia_id'] ?? null,
            // Guarda la programación POE relacionada, si aplica.
            'id_poe_programacion' => $datos['id_poe_programacion'] ?? null,
            // Guarda la clave que evita duplicar el mismo evento.
            'clave_evento'        => $claveEvento,
            // Guarda la clave usada para agrupar avisos relacionados.
            'clave_agrupacion'    => $claveAgrupacion,
            // Inicia el contador de eventos de este aviso en uno.
            'cantidad'            => 1,
            // Marca el aviso como no leído al crearlo.
            'leida'               => false,
            // Registra la fecha y hora actuales de generación.
            'fecha_generacion'    => Carbon::now(),
            // Deja vacía la fecha de lectura porque aún no ha sido leído.
            'fecha_lectura'       => null,
        ]);
    }

    // Regla 11: Protege la privacidad asegurando que nunca se filtre texto íntimo de acompañamiento
    // Recibe módulo, título y mensaje; devuelve el contenido permitido.
    private function protegerContenidoAcompaniamiento(string $modulo, string $titulo, ?string $mensaje): array
    {
        // Normaliza el módulo para comparar sin distinguir mayúsculas ni espacios externos.
        $esModuloSensible = in_array(mb_strtolower(trim($modulo)), $this->modulosSensibles, true);
        // Combina y pasa a minúsculas el título y el mensaje para buscar términos sensibles.
        $textoCompleto = mb_strtolower($titulo . ' ' . ($mensaje ?? ''));

        // Inicializa el indicador de que aún no se encontró contenido sensible.
        $contieneTerminoSensible = false;
        // Revisa uno por uno los fragmentos de palabras sensibles configurados.
        foreach ($this->palabrasSensibles as $palabra) {
            // Busca el fragmento actual dentro del título y el mensaje normalizados.
            if (str_contains($textoCompleto, $palabra)) {
                // Indica que el texto incluye contenido que debe protegerse.
                $contieneTerminoSensible = true;
                // Deja de buscar porque ya encontró un término sensible.
                break;
            }
        }

        // Protege el texto si el módulo o su contenido se considera sensible.
        if ($esModuloSensible || $contieneTerminoSensible) {
            // Devuelve un título y mensaje genéricos que no exponen información íntima.
            return [
                'Novedad en acompañamiento',
                'Tienes una novedad registrada en tu proceso pedagógico. Ingresa de forma privada para revisar los detalles.'
            ];
        }

        // Si el contenido no es sensible, devuelve el título y mensaje originales.
        return [$titulo, $mensaje];
    }

    // Marca una notificación como leída
    // Recibe el id del aviso y el id del receptor para no cambiar avisos ajenos.
    public function marcarLeida(int $idNotificacion, int $idUsuario): bool
    {
        // Busca el aviso por su identificador.
        $notif = Notification::where('id_notificacion', $idNotificacion)
            // Verifica que pertenezca al usuario autenticado indicado.
            ->where('id_usuario_receptor', $idUsuario)
            // Ejecuta la consulta y devuelve el primer resultado o null.
            ->first();

        // Actualiza el aviso solo si fue encontrado para ese usuario.
        if ($notif) {
            // Cambia el estado a leído.
            $notif->leida = true;
            // Registra cuándo se leyó.
            $notif->fecha_lectura = Carbon::now();
            // Guarda los cambios y devuelve true si la base de datos los guardó.
            return $notif->save();
        }

        // Devuelve false cuando no existe un aviso accesible con esos identificadores.
        return false;
    }

    // Marca una notificación como no leída
    // Los dos identificadores limitan la operación al dueño de la notificación.
    public function marcarNoLeida(int $idNotificacion, int $idUsuario): bool
    {
        // Busca el aviso solicitado.
        $notif = Notification::where('id_notificacion', $idNotificacion)
            // Confirma que el aviso pertenezca al usuario receptor.
            ->where('id_usuario_receptor', $idUsuario)
            // Ejecuta la consulta y obtiene un resultado o null.
            ->first();

        // Modifica el estado únicamente si encontró el aviso del usuario.
        if ($notif) {
            // Cambia el estado a no leído.
            $notif->leida = false;
            // Borra la fecha de lectura porque ahora vuelve a estar pendiente.
            $notif->fecha_lectura = null;
            // Guarda el cambio y devuelve el resultado de la operación.
            return $notif->save();
        }

        // Informa que no se pudo modificar porque el aviso no existe o no pertenece al usuario.
        return false;
    }

    // Alterna el estado de lectura de una notificación
    // Devuelve el aviso actualizado o null si no existe para ese usuario.
    public function toggleLeida(int $idNotificacion, int $idUsuario): ?Notification
    {
        // Busca el aviso mediante su id.
        $notif = Notification::where('id_notificacion', $idNotificacion)
            // Evita que el usuario cambie notificaciones de otra persona.
            ->where('id_usuario_receptor', $idUsuario)
            // Ejecuta la consulta.
            ->first();

        // Solo alterna el estado si el aviso existe y es del receptor.
        if ($notif) {
            // Invierte el valor actual: leído pasa a no leído y viceversa.
            $notif->leida = !$notif->leida;
            // Si quedó leído asigna fecha actual; si no, deja la fecha vacía.
            $notif->fecha_lectura = $notif->leida ? Carbon::now() : null;
            // Persiste el nuevo estado en la base de datos.
            $notif->save();
            // Devuelve el aviso ya actualizado.
            return $notif;
        }

        // Devuelve null para señalar que no encontró un aviso autorizado.
        return null;
    }

    // Marca todas las notificaciones pendientes de un usuario como leídas
    // Devuelve el número de registros que fueron actualizados.
    public function marcarTodasLeidas(int $idUsuario): int
    {
        // Busca los avisos recibidos por el usuario.
        return Notification::where('id_usuario_receptor', $idUsuario)
            // Incluye solamente los avisos que aún no se han leído.
            ->where('leida', false)
            // Actualiza en una sola consulta y devuelve la cantidad de filas afectadas.
            ->update([
                // Cambia el indicador a leído.
                'leida'         => true,
                // Registra el momento de lectura.
                'fecha_lectura' => Carbon::now(),
            ]);
    }

    // Obtiene resumen para la campana flotante y sondeo en tiempo real
    // $ultimoId es opcional y permite identificar notificaciones nuevas desde el último sondeo.
    public function obtenerResumen(int $idUsuario, ?int $ultimoId = null): array
    {
        // Cuenta cuántas notificaciones sin leer tiene el usuario.
        $noLeidasTotal = Notification::where('id_usuario_receptor', $idUsuario)
            // Cuenta solo las pendientes de lectura.
            ->where('leida', false)
            // Ejecuta el conteo sin cargar todos los modelos.
            ->count();

        // Últimas 7 notificaciones para el desplegable de la campana
        // Prepara la consulta para avisos del usuario.
        $recientes = Notification::where('id_usuario_receptor', $idUsuario)
            // Ordena desde el aviso más reciente al más antiguo.
            ->orderBy('fecha_generacion', 'desc')
            // Limita el desplegable a siete resultados.
            ->limit(7)
            // Ejecuta la consulta y obtiene la colección.
            ->get();

        // Notificaciones nuevas generadas después del último sondeo del cliente
        // Inicializa la lista vacía cuando no se pidió comparar un id.
        $nuevas = collect();
        // Busca avisos posteriores solo si el cliente envió el id de su último aviso.
        if ($ultimoId) {
            // Consulta los avisos destinados al usuario.
            $nuevas = Notification::where('id_usuario_receptor', $idUsuario)
                // Selecciona únicamente ids superiores al último conocido.
                ->where('id_notificacion', '>', $ultimoId)
                // Ordena los más recientes primero.
                ->orderBy('fecha_generacion', 'desc')
                // Ejecuta la consulta y almacena los resultados.
                ->get();
        }

        // Devuelve los datos que la interfaz necesita para actualizar la campana.
        return [
            // Total de avisos pendientes de lectura.
            'total_no_leidas' => $noLeidasTotal,
            // Lista reciente usada en el menú desplegable.
            'recientes'       => $recientes,
            // Lista nueva recibida desde el último sondeo, si se solicitó.
            'nuevas'          => $nuevas,
            // Id del aviso más reciente o cero si no hay avisos.
            'ultimo_id'       => $recientes->first()?->id_notificacion ?? 0,
        ];
    }

    // Consulta el historial completo con filtros aplicados
    // Permite filtrar avisos y controlar el tamaño de cada página.
    public function listarHistorial(int $idUsuario, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        // Inicia una consulta restringida al usuario y ordenada de más nuevo a más antiguo.
        $query = Notification::where('id_usuario_receptor', $idUsuario)
            // Presenta primero las notificaciones generadas más recientemente.
            ->orderBy('fecha_generacion', 'desc');

        // Filtro por módulo
        // Aplica este filtro únicamente si se recibió un módulo no vacío.
        if (!empty($filtros['modulo'])) {
            // Añade a la consulta la condición del módulo elegido.
            $query->where('modulo', $filtros['modulo']);
        }

        // Filtro por estado
        // Revisa si se indicó un estado para filtrar.
        if (!empty($filtros['estado'])) {
            // Selecciona las notificaciones no leídas cuando ese estado fue solicitado.
            if ($filtros['estado'] === 'no_leidas') {
                $query->where('leida', false);
            // Selecciona las notificaciones leídas si ese es el estado elegido.
            } elseif ($filtros['estado'] === 'leidas') {
                $query->where('leida', true);
            }
        }

        // Filtro por rango de fechas
        // Aplica el límite inicial cuando se proporcionó fecha de inicio.
        if (!empty($filtros['fecha_desde'])) {
            // Conserva avisos generados en esa fecha o después.
            $query->whereDate('fecha_generacion', '>=', $filtros['fecha_desde']);
        }
        // Aplica el límite final cuando se proporcionó fecha de finalización.
        if (!empty($filtros['fecha_hasta'])) {
            // Conserva avisos generados en esa fecha o antes.
            $query->whereDate('fecha_generacion', '<=', $filtros['fecha_hasta']);
        }

        // Devuelve una página de resultados y conserva los filtros en los enlaces de paginación.
        return $query->paginate($perPage)->withQueryString();
    }

    // Lista única de módulos registrados para el usuario
    // Devuelve una colección con los nombres de módulo sin repetir.
    public function obtenerModulosDisponibles(int $idUsuario): Collection
    {
        // Limita la consulta a las notificaciones recibidas por el usuario.
        return Notification::where('id_usuario_receptor', $idUsuario)
            // Solicita solo la columna del módulo.
            ->select('modulo')
            // Elimina valores de módulo repetidos.
            ->distinct()
            // Ejecuta la consulta y devuelve los valores de esa columna como colección.
            ->pluck('modulo');
    }
}
