<?php

namespace Modules\SENAEMPRESA\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SENAEMPRESA\Entities\Asistencia;

class SENAEMPRESADatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear una sesión de asistencia de ejemplo
        Asistencia::create([
            'nombre'      => 'SESIÓN DE EJEMPLO - ASISTENCIA SENA EMPRESA',
            'descripcion' => 'Sesión de prueba para verificar el funcionamiento del módulo de asistencias.',
            'fecha'       => now()->format('Y-m-d'),
            'hora_inicio' => '08:00',
            'hora_fin'    => '17:00',
            'tipo'        => 'PRESENCIAL',
            'estado'      => 'ACTIVA',
        ]);
    }
}
