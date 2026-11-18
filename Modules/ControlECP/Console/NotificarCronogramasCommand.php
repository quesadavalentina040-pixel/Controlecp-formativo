<?php

namespace Modules\ControlECP\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Services\NotificacionService;
use Modules\SICA\Entities\Apprentice;

class NotificarCronogramasCommand extends Command
{
    protected $signature = 'controlecp:avisar-cronogramas-dos-dias';

    protected $description = 'Envía recordatorios de actividades del cronograma dos días antes.';

    public function handle(NotificacionService $notificaciones): int
    {
        $fechaRecordatorio = today()->addDays(2);
        $cronogramas = Cronograma::with(['actividad.momento', 'ficha'])
            ->whereDate('fecha', $fechaRecordatorio->toDateString())
            ->whereIn('estado', ['Pendiente', 'Pospuesta'])
            ->orderBy('hora_inicio')
            ->get();

        $enviadas = 0;
        foreach ($cronogramas as $cronograma) {
            $aprendices = User::whereIn(
                'person_id',
                Apprentice::where('course_id', $cronograma->id_ficha)->select('person_id')
            )->pluck('id');

            $destinatarios = collect([
                $cronograma->id_instructor,
                $cronograma->id_usuario_creador,
            ])->merge($aprendices)
                ->filter()
                ->map(fn ($idUsuario) => (int) $idUsuario)
                ->unique();

            $nombreActividad = $cronograma->actividad?->nombre ?? 'actividad programada';
            $nombreMomento = $cronograma->actividad?->momento?->nombre ?? 'sin momento';
            $codigoFicha = $cronograma->ficha?->code ?? (string) $cronograma->id_ficha;
            $fecha = $cronograma->fecha->format('d/m/Y');
            $horaInicio = substr((string) $cronograma->hora_inicio, 0, 5);
            $horaFin = substr((string) $cronograma->hora_fin, 0, 5);
            $lugar = $cronograma->lugar ?: 'sin lugar registrado';
            $mensaje = "En dos días tienes la actividad '{$nombreActividad}' del momento '{$nombreMomento}', " .
                "ficha {$codigoFicha}, el {$fecha} de {$horaInicio} a {$horaFin}, en {$lugar}.";

            foreach ($destinatarios as $idReceptor) {
                $notificaciones->crear([
                    'id_usuario_receptor' => $idReceptor,
                    'id_usuario_causante' => null,
                    'modulo' => 'Cronograma',
                    'titulo' => 'Recordatorio de actividad en dos días',
                    'mensaje' => $mensaje,
                    'referencia_tipo' => 'cronograma',
                    'referencia_id' => (int) $cronograma->id_cronograma,
                    'clave_evento' => "cronograma-recordatorio-2-dias-{$cronograma->id_cronograma}-{$cronograma->fecha->format('Ymd')}-{$idReceptor}",
                ]);
                $enviadas++;
            }
        }

        $this->info("Recordatorios de cronograma enviados: {$enviadas}.");

        return Command::SUCCESS;
    }
}
