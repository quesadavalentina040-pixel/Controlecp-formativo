<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\EncuestaEcp;

class EncuestaSeeder extends Seeder
{
    /**
     * Encuestas del repositorio para asociar a actividades pedagógicas (HU7 / HU8).
     */
    public function run(): void
    {
        $encuestas = [
            [
                'titulo'      => 'Encuesta de Clima y Convivencia Formativa ECP',
                'descripcion' => 'Evalúa la percepción del clima de aula, respeto, empatía y resolución pacífica de conflictos.',
                'estado'      => 'Activo',
            ],
            [
                'titulo'      => 'Encuesta de Satisfacción y Apropiación de Cultura de Paz',
                'descripcion' => 'Mide el impacto pedagógico, metodológico y vivencial de las actividades de paz.',
                'estado'      => 'Activo',
            ],
            [
                'titulo'      => 'Encuesta de Habilidades Socioemocionales y Proyecto de Vida',
                'descripcion' => 'Seguimiento al autoconocimiento, regulación emocional y proyección del aprendiz SENA.',
                'estado'      => 'Activo',
            ],
        ];

        foreach ($encuestas as $e) {
            EncuestaEcp::firstOrCreate(
                ['titulo' => $e['titulo']],
                ['descripcion' => $e['descripcion'], 'estado' => $e['estado']]
            );
        }
    }
}
