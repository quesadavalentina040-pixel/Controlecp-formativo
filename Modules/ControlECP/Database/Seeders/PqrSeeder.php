<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\Pqr;

class PqrSeeder extends Seeder
{
    /**
     * PQR de ejemplo para el módulo Control ECP.
     */
    public function run(): void
    {
        $anio = date('Y');

        $registros = [
            [
                'radicado'           => "ECP-PQR-{$anio}-0001",
                'tipo'               => 'Petición',
                'nombre_solicitante' => 'María Fernanda Rojas',
                'documento'          => '1075123456',
                'email'              => 'mfrojas@misena.edu.co',
                'telefono'           => '3201234567',
                'perfil'             => 'Aprendiz',
                'asunto'             => 'Solicitud de taller adicional de mediación de conflictos',
                'mensaje'            => "Buen día,\n\nSolicito respetuosamente que se programe un taller adicional de mediación de conflictos para la ficha 2856471, ya que varios compañeros no pudimos asistir a la sesión anterior por cruce con la formación técnica.\n\nAgradezco su gestión.",
                'prioridad'          => 'Media',
                'estado'             => 'Pendiente',
            ],
            [
                'radicado'           => "ECP-PQR-{$anio}-0002",
                'tipo'               => 'Queja',
                'nombre_solicitante' => 'Carlos Andrés Pérez',
                'documento'          => '1075987654',
                'email'              => 'caperez@misena.edu.co',
                'telefono'           => '3115558899',
                'perfil'             => 'Aprendiz',
                'asunto'             => 'Ambiente de formación sin ventilación adecuada',
                'mensaje'            => "Presento queja por las condiciones del ambiente 204 donde se desarrollan las sesiones de la Escuela Cultura de Paz. El espacio no cuenta con ventilación adecuada y esto dificulta la concentración durante las actividades grupales.",
                'prioridad'          => 'Alta',
                'estado'             => 'En trámite',
            ],
            [
                'radicado'           => "ECP-PQR-{$anio}-0003",
                'tipo'               => 'Sugerencia',
                'nombre_solicitante' => 'Luz Adriana Gómez',
                'documento'          => '36301122',
                'email'              => 'lagomez@sena.edu.co',
                'telefono'           => '3009988776',
                'perfil'             => 'Instructor',
                'asunto'             => 'Incluir dinámicas al aire libre en el momento de Convivencia',
                'mensaje'            => "Sugiero incorporar dinámicas al aire libre en el momento pedagógico de Convivencia, aprovechando las zonas verdes del centro. Esto favorecería la participación y el trabajo colaborativo de los aprendices.",
                'prioridad'          => 'Baja',
                'estado'             => 'Resuelto',
                'respuesta'          => "Cordial saludo,\n\nAgradecemos su sugerencia. Fue acogida por el equipo pedagógico y a partir del próximo trimestre el momento de Convivencia incluirá dos sesiones prácticas en las zonas verdes del centro.\n\nEquipo Escuela Cultura de Paz.",
                'fecha_respuesta'    => now()->subDays(3),
            ],
            [
                'radicado'           => "ECP-PQR-{$anio}-0004",
                'tipo'               => 'Reclamo',
                'nombre_solicitante' => 'Jhon Steven Mora',
                'documento'          => '1080445566',
                'email'              => 'jsmora@misena.edu.co',
                'telefono'           => '3187744221',
                'perfil'             => 'Aprendiz',
                'asunto'             => 'Certificado de participación no expedido',
                'mensaje'            => "Reclamo la expedición de mi certificado de participación en la Escuela Cultura de Paz. Completé los tres momentos pedagógicos y la asistencia figura registrada, pero el certificado no aparece disponible en la plataforma.",
                'prioridad'          => 'Alta',
                'estado'             => 'Pendiente',
            ],
            [
                'radicado'           => "ECP-PQR-{$anio}-0005",
                'tipo'               => 'Felicitación',
                'nombre_solicitante' => 'Sandra Milena Cortés',
                'documento'          => '26501133',
                'email'              => 'smcortes@sena.edu.co',
                'telefono'           => '3145566778',
                'perfil'             => 'Funcionario',
                'asunto'             => 'Excelente gestión del taller de habilidades socioemocionales',
                'mensaje'            => "Quiero felicitar al equipo de la Escuela Cultura de Paz por la calidad del taller de habilidades socioemocionales realizado la semana pasada. La metodología vivencial tuvo muy buena acogida entre los aprendices.",
                'prioridad'          => 'Baja',
                'estado'             => 'Cerrado',
                'respuesta'          => "Muchas gracias por su reconocimiento. Lo compartiremos con todo el equipo pedagógico como motivación para seguir fortaleciendo la estrategia.",
                'fecha_respuesta'    => now()->subDays(8),
            ],
        ];

        foreach ($registros as $registro) {
            Pqr::updateOrCreate(['radicado' => $registro['radicado']], $registro);
        }
    }
}
