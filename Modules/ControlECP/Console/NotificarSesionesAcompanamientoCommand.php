<?php

namespace Modules\ControlECP\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\AcompanamientoSesion;
use Modules\ControlECP\Entities\Notificacion;

class NotificarSesionesAcompanamientoCommand extends Command
{
    protected $signature = 'controlecp:avisar-sesiones-acompanamiento';

    protected $description = 'Envía recordatorios escalonados al aprendiz y avisos al instructor.';

    public function handle(): int
    {
        $learnerCandidates = AcompanamientoSesion::with('acompanamiento')
            ->where('estado', 'Programada')
            ->whereBetween('fecha', [today()->addDay()->toDateString(), today()->addDays(2)->toDateString()])
            ->whereNull('aviso_aprendiz_enviado_en')
            ->whereHas('acompanamiento', fn ($query) => $query->where('estado', 'Activo'))
            ->orderBy('acompanamiento_id')
            ->orderBy('fecha')
            ->orderBy('numero')
            ->get()
            ->groupBy('acompanamiento_id');

        $learnerReminders = 0;
        foreach ($learnerCandidates as $sessions) {
            $session = $sessions->first();
            $sent = DB::transaction(function () use ($session) {
                $locked = AcompanamientoSesion::with('acompanamiento')
                    ->whereKey($session->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked
                    || $locked->estado !== 'Programada'
                    || $locked->aviso_aprendiz_enviado_en
                    || !$locked->acompanamiento
                    || $locked->acompanamiento->estado !== 'Activo'
                    || $locked->fecha->lt(today()->addDay())
                    || $locked->fecha->gt(today()->addDays(2))) {
                    return false;
                }

                $nextSession = AcompanamientoSesion::where('acompanamiento_id', $locked->acompanamiento_id)
                    ->where('estado', 'Programada')
                    ->whereDate('fecha', '>=', today())
                    ->whereNull('aviso_aprendiz_enviado_en')
                    ->orderBy('fecha')
                    ->orderBy('numero')
                    ->first();

                if (!$nextSession || $nextSession->id !== $locked->id) {
                    return false;
                }

                $pendingCount = AcompanamientoSesion::where('acompanamiento_id', $locked->acompanamiento_id)
                    ->whereIn('estado', ['Programada', 'No asistio'])
                    ->count();
                $pendingText = $pendingCount === 1 ? '1 sesión' : "{$pendingCount} sesiones";

                Notificacion::crear(
                    idReceptor: (int) $locked->acompanamiento->aprendiz_id,
                    idCausante: null,
                    titulo: 'Próxima sesión de acompañamiento',
                    mensaje: "Tienes {$pendingText} de acompañamiento pendientes. La próxima es la sesión " .
                        "#{$locked->numero} el {$locked->fecha->format('d/m/Y')} de " .
                        substr($locked->hora_inicio, 0, 5) . ' a ' . substr($locked->hora_fin, 0, 5) . '.',
                    referenciaTipo: 'acompanamiento',
                    referenciaId: (int) $locked->acompanamiento_id,
                    claveEvento: "acompanamiento-proxima-sesion-{$locked->id}"
                );
                $locked->update(['aviso_aprendiz_enviado_en' => now()]);

                return true;
            });

            if ($sent) {
                $learnerReminders++;
            }
        }

        $reminders = AcompanamientoSesion::with('acompanamiento')
            ->where('estado', 'Programada')
            ->whereDate('fecha', today()->addDay())
            ->whereNull('aviso_un_dia_enviado_en')
            ->get();
        foreach ($reminders as $session) {
            DB::transaction(function () use ($session) {
                $locked = AcompanamientoSesion::whereKey($session->id)->lockForUpdate()->first();
                if (!$locked || $locked->estado !== 'Programada' || $locked->aviso_un_dia_enviado_en) return;
                Notificacion::crear(
                    idReceptor: (int) $session->acompanamiento->instructor_id,
                    idCausante: null,
                    titulo: 'Sesión de acompañamiento mañana',
                    mensaje: "Tienes la sesión #{$session->numero} mañana a las " . substr($session->hora_inicio, 0, 5) . '.',
                    referenciaTipo: 'acompanamiento',
                    referenciaId: (int) $session->acompanamiento_id,
                    claveEvento: "acompanamiento-recordatorio-{$session->id}"
                );
                $locked->update(['aviso_un_dia_enviado_en' => now()]);
            });
        }

        $overdue = AcompanamientoSesion::with('acompanamiento')
            ->where('estado', 'Programada')
            ->whereDate('fecha', '<', today())
            ->whereNull('aviso_sin_registro_enviado_en')
            ->get();
        foreach ($overdue as $session) {
            DB::transaction(function () use ($session) {
                $locked = AcompanamientoSesion::whereKey($session->id)->lockForUpdate()->first();
                if (!$locked || $locked->estado !== 'Programada' || $locked->aviso_sin_registro_enviado_en) return;
                Notificacion::crear(
                    idReceptor: (int) $session->acompanamiento->instructor_id,
                    idCausante: null,
                    titulo: 'Sesión de acompañamiento sin registrar',
                    mensaje: "La sesión #{$session->numero}, programada para {$session->fecha->format('d/m/Y')}, todavía no tiene registro.",
                    referenciaTipo: 'acompanamiento',
                    referenciaId: (int) $session->acompanamiento_id,
                    claveEvento: "acompanamiento-sin-registro-{$session->id}"
                );
                $locked->update(['aviso_sin_registro_enviado_en' => now()]);
            });
        }

        $this->info(
            'Recordatorios al aprendiz: ' . $learnerReminders .
            '; recordatorios al instructor: ' . $reminders->count() .
            '; sesiones pendientes notificadas al instructor: ' . $overdue->count() . '.'
        );
        return Command::SUCCESS;
    }
}
