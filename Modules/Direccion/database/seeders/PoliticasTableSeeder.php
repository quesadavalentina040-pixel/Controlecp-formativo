<?php

namespace Modules\Direccion\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Direccion\Entities\Politica;

class PoliticasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Politica::updateOrCreate(['codigo' => 'POL-2026-001'], [
            'titulo' => 'Política de Calidad y Mejora Continua en Unidades Didácticas',
            'tipo' => 'Calidad',
            'descripcion' => 'Garantizar estándares de calidad e inocuidad en los derivados lácteos, cárnicos y agroindustriales producidos por los aprendices en SENA Empresa.',
            'vigencia' => 2026,
            'estado' => 'Activa',
            'responsable' => 'Dirección General y Calidad'
        ]);

        Politica::updateOrCreate(['codigo' => 'POL-2026-002'], [
            'titulo' => 'Direccionamiento Estratégico y Sostenibilidad Financiera',
            'tipo' => 'Estratégica',
            'descripcion' => 'Asegurar la autosostenibilidad operativa y reinversión pedagógica a través de los puntos de comercialización y ventas del centro La Angostura.',
            'vigencia' => 2026,
            'estado' => 'Activa',
            'responsable' => 'Gerencia General'
        ]);

        Politica::updateOrCreate(['codigo' => 'POL-2026-003'], [
            'titulo' => 'Protocolo de Seguridad y Salud en el Trabajo para Turnos Rutinarios',
            'tipo' => 'Seguridad',
            'descripcion' => 'Implementación rigurosa de elementos de protección personal (EPP) y medidas de bioseguridad en labores pecuarias y agrícolas.',
            'vigencia' => 2026,
            'estado' => 'Activa',
            'responsable' => 'Comité SST y Dirección'
        ]);

        Politica::updateOrCreate(['codigo' => 'POL-2026-004'], [
            'titulo' => 'Plan de Transformación Digital e Integración ERP',
            'tipo' => 'Operativa',
            'descripcion' => 'Digitalización del 100% de los procesos de inventario, ventas y contabilidad mediante la plataforma integral SICEFA.',
            'vigencia' => 2026,
            'estado' => 'En Revisión',
            'responsable' => 'Líder de Sistemas y Dirección'
        ]);
    }
}
