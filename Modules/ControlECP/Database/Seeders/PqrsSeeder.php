<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\Pqrs;
use Modules\ControlECP\Entities\PqrsRespuesta;
use Modules\ControlECP\Entities\PqrsHistorial;
use Modules\ControlECP\Entities\TipoPqrs;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class PqrsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar registros previos de prueba
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PqrsHistorial::truncate();
        PqrsRespuesta::truncate();
        Pqrs::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $aprendiz = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.apprentice'))->first() ?? User::find(126) ?? User::first();
        $instructor = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.instructor'))->first() ?? User::find(125);
        $admin = User::whereHas('roles', fn($q) => $q->where('slug', 'controlecp.admin'))->first() ?? User::find(1);

        $tipoPeticion     = TipoPqrs::where('nombre', 'like', '%Petición%')->first()->id_tipo_pqrs ?? 1;
        $tipoQueja        = TipoPqrs::where('nombre', 'like', '%Queja%')->first()->id_tipo_pqrs ?? 2;
        $tipoReclamo      = TipoPqrs::where('nombre', 'like', '%Reclamo%')->first()->id_tipo_pqrs ?? 3;
        $tipoSugerencia   = TipoPqrs::where('nombre', 'like', '%Sugerencia%')->first()->id_tipo_pqrs ?? 4;
        $tipoFelicitacion = TipoPqrs::where('nombre', 'like', '%Felicitación%')->first()->id_tipo_pqrs ?? 5;

        // 1. PQRS: Petición - Pendiente (Dirigida a Instructor)
        $p1 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0001',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoPeticion,
            'id_usuario_instructor_destino' => $instructor ? $instructor->id : null,
            'asunto'                        => 'Solicitud de acompañamiento en resolución de conflictos Ficha 2856471',
            'descripcion'                   => 'Cordial saludo. Solicito respetuosamente una sesión de mediación y diálogo para nuestro grupo en el Ambiente 204, debido a diferencias en el trabajo en equipo de nuestro proyecto formativo.',
            'estado'                        => 'Pendiente',
            'fecha_radicacion'              => now()->subDays(1),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p1->id_pqrs,
            'id_usuario'      => $aprendiz->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'Pendiente',
            'observacion'     => 'Radicación inicial realizada por el aprendiz.',
            'fecha'           => now()->subDays(1),
        ]);

        // 2. PQRS: Queja - En trámite (Dirigida a Instructor)
        $p2 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0002',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoQueja,
            'id_usuario_instructor_destino' => $instructor ? $instructor->id : null,
            'asunto'                        => 'Retraso en el inicio del taller de mediación comunitaria',
            'descripcion'                   => 'Presento inconformidad debido a que el taller programado para el día 15 de octubre a las 08:00 AM inició con más de 45 minutos de retraso sin previo aviso a los aprendices matriculados.',
            'estado'                        => 'En trámite',
            'fecha_radicacion'              => now()->subDays(3),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p2->id_pqrs,
            'id_usuario'      => $aprendiz->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'Pendiente',
            'observacion'     => 'Radicación inicial de queja.',
            'fecha'           => now()->subDays(3),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p2->id_pqrs,
            'id_usuario'      => $admin->id,
            'estado_anterior' => 'Pendiente',
            'estado_nuevo'    => 'En trámite',
            'observacion'     => 'Caso asumido por Administración. Se solicita reporte de novedades al instructor a cargo.',
            'fecha'           => now()->subDays(2),
        ]);

        // 3. PQRS: Reclamo - Resuelta (Directa a Administración)
        $p3 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0003',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoReclamo,
            'id_usuario_instructor_destino' => null,
            'asunto'                        => 'Inconsistencia en el registro de asistencia a la jornada de Cultura de Paz',
            'descripcion'                   => 'Asistí puntualmente a la jornada del 10 de octubre, firmé la planilla física pero en el sistema aparezco reportado como ausente, lo cual afecta mi porcentaje de cumplimiento.',
            'estado'                        => 'Resuelta',
            'fecha_radicacion'              => now()->subDays(5),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p3->id_pqrs,
            'id_usuario'      => $aprendiz->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'Pendiente',
            'observacion'     => 'Radicación de reclamo.',
            'fecha'           => now()->subDays(5),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p3->id_pqrs,
            'id_usuario'      => $admin->id,
            'estado_anterior' => 'Pendiente',
            'estado_nuevo'    => 'En trámite',
            'observacion'     => 'Verificando planilla física de firmas con el equipo de apoyo.',
            'fecha'           => now()->subDays(4),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p3->id_pqrs,
            'id_usuario'      => $admin->id,
            'estado_anterior' => 'En trámite',
            'estado_nuevo'    => 'Resuelta',
            'observacion'     => 'Se verificó la firma en planilla física. Asistencia corregida y respuesta emitida.',
            'fecha'           => now()->subDays(3),
        ]);
        PqrsRespuesta::create([
            'id_pqrs'    => $p3->id_pqrs,
            'id_usuario' => $admin->id,
            'respuesta'  => 'Estimado aprendiz: Hemos revisado minuciosamente las planillas de asistencia físicas de la jornada del 10 de octubre y constatamos su firma y participación. Su registro ha sido corregido en la base de datos de Control ECP con estado "Presente". Agradecemos su comprensión.',
            'fecha'      => now()->subDays(3),
        ]);

        // 4. PQRS: Sugerencia - Resuelta (Directa a Administración)
        $p4 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0004',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoSugerencia,
            'id_usuario_instructor_destino' => null,
            'asunto'                        => 'Propuesta de actividades al aire libre en zonas verdes del Centro',
            'descripcion'                   => 'Sugerimos que algunas dinámicas de mediación de paz se realicen en los quioscos y áreas verdes de La Angostura para favorecer la relajación y el intercambio respetuoso.',
            'estado'                        => 'Resuelta',
            'fecha_radicacion'              => now()->subDays(8),
        ]);
        PqrsRespuesta::create([
            'id_pqrs'    => $p4->id_pqrs,
            'id_usuario' => $admin->id,
            'respuesta'  => 'Excelente sugerencia. A partir de la próxima semana se habilitará el quiosco 2 para los círculos de diálogo de mediación pedagógica. ¡Gracias por contribuir a la Escuela Cultura de Paz!',
            'fecha'      => now()->subDays(7),
        ]);

        // 5. PQRS: Felicitación - Resuelta (Directa a Instructor)
        $p5 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0005',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoFelicitacion,
            'id_usuario_instructor_destino' => $instructor ? $instructor->id : null,
            'asunto'                        => 'Agradecimiento y felicitación por la jornada de convivencia',
            'descripcion'                   => 'Queremos expresar nuestro reconocimiento a los instructores por la excelente metodología aplicada en el taller de convivencia. Nos ayudó mucho a mejorar el ambiente de nuestro grupo.',
            'estado'                        => 'Resuelta',
            'fecha_radicacion'              => now()->subDays(10),
        ]);
        PqrsRespuesta::create([
            'id_pqrs'    => $p5->id_pqrs,
            'id_usuario' => $instructor ? $instructor->id : $admin->id,
            'respuesta'  => 'Muchísimas gracias por su mensaje. Nos motiva a continuar fortaleciendo los lazos de paz, respeto y trabajo colaborativo en el Centro La Angostura.',
            'fecha'      => now()->subDays(9),
        ]);

        // 6. PQRS: Queja - Pendiente (Directa a Administración)
        $p6 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0006',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoQueja,
            'id_usuario_instructor_destino' => null,
            'asunto'                        => 'Falta de sonido y micrófono en el auditorio principal',
            'descripcion'                   => 'Durante la charla de apertura de la Escuela Cultura de Paz no se escuchaba claramente en las filas posteriores por fallas en el bafle de sonido.',
            'estado'                        => 'Pendiente',
            'fecha_radicacion'              => now()->subHours(6),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p6->id_pqrs,
            'id_usuario'      => $aprendiz->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'Pendiente',
            'observacion'     => 'Radicación registrada.',
            'fecha'           => now()->subHours(6),
        ]);

        // 7. PQRS: Petición - En trámite (Dirigida a Instructor)
        $p7 = Pqrs::create([
            'radicado'                      => 'ECP-PQR-2026-0007',
            'id_usuario_aprendiz'           => $aprendiz->id,
            'id_tipo_pqrs'                  => $tipoPeticion,
            'id_usuario_instructor_destino' => $instructor ? $instructor->id : null,
            'asunto'                        => 'Petición de asesoría grupal de mediación formativa',
            'descripcion'                   => 'Requerimos agendar una asesoría de 2 horas para mediar un acuerdo de convivencia entre dos grupos de formación que comparten el mismo ambiente técnico.',
            'estado'                        => 'En trámite',
            'fecha_radicacion'              => now()->subDays(2),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p7->id_pqrs,
            'id_usuario'      => $aprendiz->id,
            'estado_anterior' => null,
            'estado_nuevo'    => 'Pendiente',
            'observacion'     => 'Radicación recibida.',
            'fecha'           => now()->subDays(2),
        ]);
        PqrsHistorial::create([
            'id_pqrs'         => $p7->id_pqrs,
            'id_usuario'      => $admin->id,
            'estado_anterior' => 'Pendiente',
            'estado_nuevo'    => 'En trámite',
            'observacion'     => 'Coordinando disponibilidad de horario en cronograma.',
            'fecha'           => now()->subDay(),
        ]);
    }
}
