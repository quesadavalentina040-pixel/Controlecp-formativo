<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\ControlECP\Entities\CertificacionMomento;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Services\NotificacionService;
use Modules\SICA\Entities\Course;

class InstructorCertificacionController extends Controller
{
    public function index()
    {
        $instructorId = Auth::id();
        $filas = DB::table('cronograma as c')
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->join('controlecp_momentos as m', 'm.id', '=', 'a.momento_id')
            ->where('c.id_instructor', $instructorId)
            ->select('c.id_ficha', 'a.momento_id', 'c.id_actividad', 'c.estado', 'm.nombre as momento_nombre')
            ->get()
            ->groupBy(fn ($fila) => "{$fila->id_ficha}-{$fila->momento_id}");

        $fichas = Course::whereIn('id', $filas->pluck('*.id_ficha')->flatten()->unique())
            ->orderBy('code')
            ->get()
            ->keyBy('id');
        $momentos = Momento::whereIn('id', $filas->pluck('*.momento_id')->flatten()->unique())
            ->orderBy('orden')
            ->get()
            ->keyBy('id');
        $certificadas = CertificacionMomento::whereIn('id_ficha', $fichas->keys())
            ->whereIn('id_momento', $momentos->keys())
            ->get()
            ->keyBy(fn ($certificacion) => "{$certificacion->id_ficha}-{$certificacion->id_momento}");

        $disponibles = $filas->map(function ($registros, $clave) use ($fichas, $momentos, $certificadas) {
            [$fichaId, $momentoId] = array_map('intval', explode('-', $clave));
            $total = $registros->pluck('id_actividad')->unique()->count();
            $finalizadas = $registros->where('estado', 'Finalizada')->pluck('id_actividad')->unique()->count();

            return (object) [
                'clave' => $clave,
                'ficha' => $fichas->get($fichaId),
                'momento' => $momentos->get($momentoId),
                'total' => $total,
                'finalizadas' => $finalizadas,
                'pendientes' => $registros->where('estado', '!=', 'Finalizada')->pluck('id_actividad')->unique()->count(),
                'cumplido' => $total > 0 && $total === $finalizadas,
                'certificacion' => $certificadas->get($clave),
            ];
        })->values();

        $historial = CertificacionMomento::with(['ficha', 'momento'])
            ->where('id_instructor', $instructorId)
            ->latest('fecha_certificacion')
            ->get();

        return view('controlecp::instructor.certificados', compact('disponibles', 'historial'));
    }

    public function certificar(Request $request, string $clave)
    {
        $request->validate(['confirmar' => 'nullable|accepted']);
        [$fichaId, $momentoId] = array_map('intval', explode('-', $clave));
        $instructorId = Auth::id();

        $registros = DB::table('cronograma as c')
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->where('c.id_instructor', $instructorId)
            ->where('c.id_ficha', $fichaId)
            ->where('a.momento_id', $momentoId)
            ->select('c.id_actividad', 'c.estado')
            ->get()
            ->groupBy('id_actividad');

        if ($registros->isEmpty()) {
            abort(404);
        }
        $pendientes = $registros->filter(fn ($items) => !$items->contains('estado', 'Finalizada'))->keys();
        if ($pendientes->isNotEmpty()) {
            return back()->with('error', 'No se puede certificar: faltan actividades por finalizar (' . $pendientes->implode(', ') . ').');
        }
        if (CertificacionMomento::where(['id_ficha' => $fichaId, 'id_momento' => $momentoId])->exists()) {
            return back()->with('error', 'Este momento ya fue certificado para la ficha.');
        }

        DB::transaction(function () use ($fichaId, $momentoId, $instructorId) {
            CertificacionMomento::create([
                'id_ficha' => $fichaId,
                'id_momento' => $momentoId,
                'id_instructor' => $instructorId,
                'fecha_certificacion' => now(),
                'estado' => 'Certificada',
            ]);
        });

        $aprendices = Course::with('apprentices.person.users')->findOrFail($fichaId)->apprentices;
        $notificaciones = app(NotificacionService::class);
        foreach ($aprendices as $aprendiz) {
            $usuarioAprendiz = $aprendiz->person?->users?->first();
            if (! $usuarioAprendiz) {
                continue;
            }
            $notificaciones->crear([
                'id_usuario_receptor' => $usuarioAprendiz->id,
                'id_usuario_causante' => $instructorId,
                'titulo' => 'Momento certificado',
                'mensaje' => 'Tu ficha completó y certificó un momento de Control ECP.',
                'modulo' => 'Certificaciones',
                'clave_evento' => "certificacion-{$fichaId}-{$momentoId}-{$aprendiz->id}",
            ]);
        }
        foreach (User::whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.admin'))->pluck('id') as $adminId) {
            $notificaciones->crear([
                'id_usuario_receptor' => $adminId,
                'id_usuario_causante' => $instructorId,
                'titulo' => 'Momento certificado por instructor',
                'mensaje' => 'Se certificó un momento para una ficha del instructor.',
                'modulo' => 'Certificaciones',
                'clave_evento' => "certificacion-admin-{$fichaId}-{$momentoId}-{$adminId}",
            ]);
        }

        return back()->with('success', 'Momento certificado correctamente.');
    }
}
