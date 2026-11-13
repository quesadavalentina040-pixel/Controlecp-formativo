<?php

namespace Modules\ControlECP\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\Notificacion;
use Modules\SICA\Entities\Course;

class CertificacionNotificacionService
{
    public function notificarSiSoloFaltaUnMomento(Course $curso, bool $exigirListoParaCertificar = false): void
    {
        $momentos = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->orderBy('orden')
            ->get(['id', 'nombre']);
        $certificadosIds = DB::table('controlecp_certificaciones')
            ->where('course_id', $curso->id)
            ->whereIn('momento_id', $momentos->pluck('id'))
            ->pluck('momento_id');
        $momentosPendientes = $momentos->reject(
            fn ($momento) => $certificadosIds->contains($momento->id)
        );

        if ($momentosPendientes->count() !== 1) {
            return;
        }

        $momentoPendiente = $momentosPendientes->first();
        if ($exigirListoParaCertificar) {
            $actividadIds = DB::table('controlecp_actividades')
                ->where('momento_id', $momentoPendiente->id)
                ->whereNull('deleted_at')
                ->where('estado', '!=', 'Inactivo')
                ->pluck('id');
            $actividadesFinalizadas = DB::table('cronograma')
                ->where('id_ficha', $curso->id)
                ->where('estado', 'Finalizada')
                ->whereIn('id_actividad', $actividadIds)
                ->distinct()
                ->count('id_actividad');

            if ($actividadIds->isEmpty() || $actividadesFinalizadas < $actividadIds->count()) {
                return;
            }
        }

        $claveEvento = "certificacion-un-momento-{$curso->id}-{$momentoPendiente->id}";
        $administradores = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.admin'))
            ->get(['id']);

        foreach ($administradores as $administrador) {
            $yaNotificado = DB::table('notificaciones')
                ->where('id_usuario_receptor', $administrador->id)
                ->where('clave_evento', $claveEvento)
                ->exists();
            if ($yaNotificado) {
                continue;
            }

            Notificacion::crear(
                idReceptor: (int) $administrador->id,
                idCausante: Auth::id(),
                titulo: 'Ficha con un momento pendiente de certificar',
                mensaje: "A la ficha {$curso->code} solo le falta certificar el momento {$momentoPendiente->nombre}. Revisa el avance para completar el proceso.",
                referenciaTipo: 'certificacion_pendiente',
                referenciaId: (int) $curso->id,
                claveEvento: $claveEvento
            );
        }
    }
}
