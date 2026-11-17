<?php

namespace Modules\ControlECP\Console;

use Illuminate\Console\Command;
use Modules\ControlECP\Entities\Asesoria;
use Modules\ControlECP\Services\NotificacionService;

class NotificarAsesoriasCommand extends Command
{
    protected $signature = 'controlecp:avisar-asesorias-dos-dias';

    protected $description = 'Envía recordatorios de asesorías dos días antes al instructor y al aprendiz.';

    public function handle(NotificacionService $notificaciones): int
    {
        $fechaRecordatorio = today()->addDays(2);
        $asesorias = Asesoria::with(['categoria', 'instructor', 'aprendiz'])
            ->where('estado', 'Programada')
            ->whereDate('fecha', $fechaRecordatorio->toDateString())
            ->whereNotNull('id_usuario_instructor')
            ->orderBy('hora_inicio')
            ->get();

        $enviadas = 0;
        foreach ($asesorias as $asesoria) {
            $fecha = $asesoria->fecha->format('d/m/Y');
            $horaInicio = substr((string) $asesoria->hora_inicio, 0, 5);
            $horaFin = substr((string) $asesoria->hora_fin, 0, 5);
            $categoria = $asesoria->categoria?->nombre;
            $tipoAsesoria = $categoria ? " de {$categoria}" : '';
            $destinatarios = [
                (int) $asesoria->id_usuario_instructor => $asesoria->aprendiz?->full_name ?? 'el aprendiz',
                (int) $asesoria->id_usuario_aprendiz => $asesoria->instructor?->full_name ?? 'el instructor',
            ];

            foreach ($destinatarios as $idReceptor => $nombreContacto) {
                $notificaciones->crear([
                    'id_usuario_receptor' => $idReceptor,
                    'id_usuario_causante' => null,
                    'modulo' => 'Asesorías',
                    'titulo' => 'Recordatorio de asesoría en dos días',
                    'mensaje' => "Tienes una asesoría{$tipoAsesoria} programada con {$nombreContacto} el {$fecha}, de {$horaInicio} a {$horaFin}.",
                    'referencia_tipo' => 'asesoria',
                    'referencia_id' => (int) $asesoria->id_asesoria,
                    'clave_evento' => "asesoria-recordatorio-2-dias-{$asesoria->id_asesoria}-{$asesoria->fecha->format('Ymd')}-{$idReceptor}",
                ]);
                $enviadas++;
            }
        }

        $this->info("Recordatorios de asesorías enviados: {$enviadas}.");

        return Command::SUCCESS;
    }
}
