<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ControlECP\Entities\Notification;
use Modules\ControlECP\Services\NotificacionService;

class NotificacionController extends Controller // Controlador de historial, lectura y eliminación de notificaciones del usuario.
{
    protected NotificacionService $servicio;

    public function __construct(NotificacionService $servicio) // Inyección de dependencias del servicio de notificaciones | __construct(): Recibe y conserva el servicio de notificaciones; se ejecuta al construir el controlador.
    {
        $this->servicio = $servicio;
    }

    public function index(Request $request) // Muestra la vista de historial de notificaciones con filtros por módulo y fecha | index(): Consulta historial, filtros y contadores del usuario; se usa al abrir su bandeja de notificaciones.
    {
        $idUsuario = Auth::id(); // Auth::id() obtiene el identificador de la persona que inició sesión.
        $filtros = $request->only(['modulo', 'estado', 'fecha_desde', 'fecha_hasta']); // only() toma únicamente los filtros permitidos de la solicitud, sin aceptar otros parámetros.

        $notificaciones = $this->servicio->listarHistorial($idUsuario, $filtros); // El servicio aplica los filtros y recupera el historial perteneciente al usuario.
        $modulos = $this->servicio->obtenerModulosDisponibles($idUsuario); // Recupera los módulos disponibles para ofrecerlos como opciones de filtro.

        $totales = [ // Cuenta todas las notificaciones recibidas por este usuario.
            'total'     => Notification::where('id_usuario_receptor', $idUsuario)->count(),
            'no_leidas' => Notification::where('id_usuario_receptor', $idUsuario)->where('leida', false)->count(), // where() agrega una condición SQL; count() ejecuta el conteo sin cargar los registros.
            'leidas'    => Notification::where('id_usuario_receptor', $idUsuario)->where('leida', true)->count(), // La segunda condición limita el conteo a las notificaciones ya leídas.
        ];

        return view('controlecp::administrador.notificaciones', compact('notificaciones', 'modulos', 'totales', 'filtros')); // Renderiza la vista y compact() entrega variables con esos mismos nombres.
    }

    public function resumen(Request $request): JsonResponse // Retorna datos en formato JSON para la campana y sondeo en tiempo real | resumen(): Devuelve en JSON el resumen y las notificaciones recientes; se usa desde la campana y su sondeo periódico.
    {
        $idUsuario = Auth::id(); // Identifica al dueño de los datos que se devolverán.
        $ultimoId = $request->query('ultimo_id') ? (int)$request->query('ultimo_id') : null; // query() lee el parámetro de la URL; lo convierte a entero o deja null si no llegó.

        $datos = $this->servicio->obtenerResumen($idUsuario, $ultimoId); // El servicio calcula contadores y selecciona notificaciones nuevas y recientes.

        return response()->json([ // json() serializa este arreglo para que lo consuma la campana de notificaciones.
            'success'         => true,
            'total_no_leidas' => $datos['total_no_leidas'],
            'ultimo_id'       => $datos['ultimo_id'],
            'nuevas'          => $datos['nuevas']->map(fn($n) => [ // map() transforma cada modelo en un arreglo pequeño para la respuesta.
                'id'         => $n->id_notificacion,
                'modulo'     => $n->modulo,
                'titulo'     => $n->titulo,
                'mensaje'    => $n->mensaje,
                'url'        => $n->url_referencia,
                'fecha'      => $n->fecha_generacion->diffForHumans(), // Presenta el tiempo en forma relativa, por ejemplo «hace 5 minutos».
            ]),
            'recientes'       => $datos['recientes']->map(fn($n) => [ // Convierte también los registros recientes e incluye enlaces y fechas legibles.
                'id'         => $n->id_notificacion,
                'modulo'     => $n->modulo,
                'titulo'     => $n->titulo,
                'mensaje'    => $n->mensaje,
                'cantidad'   => $n->cantidad,
                'leida'      => $n->leida,
                'url'        => route('controlecp.notificaciones.ir', $n->id_notificacion),
                'fecha'      => $n->fecha_generacion->diffForHumans(),
                'fecha_hora' => $n->fecha_generacion->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function ir($id) // Marca como leída y redirige al recurso correspondiente | ir(): Marca como leída una notificación propia y redirige al destino; se usa al abrirla.
    {
        $idUsuario = Auth::id(); // Restringe la búsqueda a la persona autenticada para impedir abrir avisos ajenos.
        $notif = Notification::where('id_notificacion', $id) // where() agrega filtros SQL; firstOrFail() obtiene un registro o responde con error 404.
            ->where('id_usuario_receptor', $idUsuario)
            ->firstOrFail();

        $this->servicio->marcarLeida($notif->id_notificacion, $idUsuario); // Registra el aviso como leído antes de dirigirse a su recurso asociado.

        return redirect()->to($notif->url_referencia); // Envía el navegador a la URL guardada en la notificación.
    }

    public function toggleLeida($id): JsonResponse // Alterna el estado de una notificación entre leída y no leída | toggleLeida(): Alterna el estado de lectura y devuelve los contadores; se usa desde la acción AJAX de lectura.
    {
        $idUsuario = Auth::id(); // Obtiene el usuario actual para que el cambio afecte únicamente sus avisos.
        $notif = $this->servicio->toggleLeida((int)$id, $idUsuario); // Convierte el identificador recibido a entero y pide al servicio alternar su estado.

        if (!$notif) { // Si el servicio no encontró un aviso propio, devuelve JSON y estado HTTP 404.
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);
        }

        $noLeidas = Notification::where('id_usuario_receptor', $idUsuario)->where('leida', false)->count(); // Consulta cuántos avisos siguen sin leerse después del cambio.

        return response()->json([ // Devuelve el nuevo estado, la fecha formateada y el contador para actualizar la interfaz.
            'success'         => true,
            'leida'           => $notif->leida,
            'fecha_lectura'   => $notif->fecha_lectura ? $notif->fecha_lectura->format('d/m/Y H:i') : null,
            'total_no_leidas' => $noLeidas,
        ]);
    }

    public function marcarTodasLeidas(): JsonResponse // Marca todas las notificaciones del usuario como leídas | marcarTodasLeidas(): Marca como leídas todas las notificaciones del usuario; se usa al solicitar esa acción masiva.
    {
        $idUsuario = Auth::id(); // Identifica de quién son los avisos que se actualizarán.
        $afectadas = $this->servicio->marcarTodasLeidas($idUsuario); // El servicio marca los avisos propios y devuelve cuántos registros modificó.

        return response()->json([ // Responde en JSON; el contador queda en cero tras marcar todos como leídos.
            'success'         => true,
            'afectadas'       => $afectadas,
            'total_no_leidas' => 0,
        ]);
    }

    public function destroy($id): JsonResponse // Elimina una notificación específica del usuario | destroy(): Elimina una notificación del usuario y actualiza el contador; se usa desde su acción de borrado.
    {
        $idUsuario = Auth::id(); // Limita tanto el borrado como el conteo al usuario autenticado.
        $eliminado = Notification::where('id_notificacion', $id) // where() restringe el DELETE por identificador y receptor; delete() ejecuta el borrado.
            ->where('id_usuario_receptor', $idUsuario)
            ->delete();

        $noLeidas = Notification::where('id_usuario_receptor', $idUsuario)->where('leida', false)->count(); // Vuelve a contar los avisos no leídos para mantener actualizado el indicador.

        return response()->json([ // Indica si se eliminó algún registro y comunica el total pendiente.
            'success'         => (bool)$eliminado,
            'total_no_leidas' => $noLeidas,
        ]);
    }
}
