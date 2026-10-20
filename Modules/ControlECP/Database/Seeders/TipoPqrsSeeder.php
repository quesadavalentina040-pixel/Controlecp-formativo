<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\TipoPqrs;

class TipoPqrsSeeder extends Seeder
{
    /**
     * Catálogo inicial de Tipos de PQRS para Control ECP.
     */
    public function run(): void
    {
        $tipos = [
            'Petición',
            'Queja',
            'Reclamo',
            'Sugerencia',
            'Felicitación',
        ];

        foreach ($tipos as $tipo) {
            TipoPqrs::firstOrCreate(['nombre' => $tipo]);
        }
    }
}
