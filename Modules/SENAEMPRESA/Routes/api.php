<?php

use Illuminate\Support\Facades\Route;
use Modules\SENAEMPRESA\Http\Controllers\AsistenciaController;

/*
|--------------------------------------------------------------------------
| API Routes - SENAEMPRESA
|--------------------------------------------------------------------------
| Endpoints REST para que el ERP SICEFA consuma y envíe datos de asistencia.
*/

Route::prefix('senaempresa')->name('api.senaempresa.')->group(function () {

    // Listar sesiones de asistencia (JSON)
    Route::get('asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');

    // Obtener detalle de una sesión con sus aprendices
    Route::get('asistencias/{asistencia}', [AsistenciaController::class, 'show'])->name('asistencias.show');

    // Recibir asistencias desde el ERP SICEFA (webhook)
    Route::post('asistencias/receive', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:200',
            'fecha'        => 'required|date',
            'tipo'         => 'required|in:PRESENCIAL,VIRTUAL,MIXTA',
            'sicefa_id'    => 'nullable|integer',
            'aprendices'   => 'nullable|array',
        ]);

        $asistencia = \Modules\SENAEMPRESA\Entities\Asistencia::firstOrCreate(
            ['sicefa_id' => $data['sicefa_id'] ?? null],
            [
                'nombre' => $data['nombre'],
                'fecha'  => $data['fecha'],
                'tipo'   => $data['tipo'],
                'sicefa_synced_at' => now(),
            ]
        );

        if (!empty($data['aprendices'])) {
            $sync = [];
            foreach ($data['aprendices'] as $ap) {
                $person = \Modules\SICA\Entities\Person::where('document_number', $ap['document_number'] ?? '')->first();
                if ($person) {
                    $apprentice = \Modules\SICA\Entities\Apprentice::where('person_id', $person->id)->first();
                    if ($apprentice) {
                        $sync[$apprentice->id] = [
                            'asistencia' => $ap['asistencia'] ?? 'ASISTIO',
                            'observacion' => $ap['observacion'] ?? null,
                        ];
                    }
                }
            }
            $asistencia->apprentices()->syncWithoutDetaching($sync);
        }

        return response()->json([
            'success' => true,
            'message' => 'Asistencia recibida y procesada.',
            'data'    => $asistencia,
        ], 200);
    })->name('asistencias.receive');
});
