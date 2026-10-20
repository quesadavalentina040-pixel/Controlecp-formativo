<?php

namespace Modules\SENAEMPRESA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Course;
use Modules\SICA\Entities\Person;
use Modules\SENAEMPRESA\Entities\Asistencia;
use Modules\SENAEMPRESA\Services\SicefaApiService;

class AsistenciaController extends Controller
{
    /**
     * Vista principal: lista de sesiones de asistencia.
     */
    public function index(Request $request)
    {
        $query = Asistencia::query();

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('descripcion', 'like', "%{$buscar}%");
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->input('fecha'));
        }

        $asistencias = $query->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->except('page'));

        return view('senaempresa::asistencia.index', [
            'title'       => 'Gestión de Asistencias - SENA Empresa',
            'asistencias' => $asistencias,
        ]);
    }

    /**
     * Formulario para crear una nueva sesión de asistencia.
     */
    public function create()
    {
        $cursos = Course::orderBy('code')->pluck('code', 'id');

        return view('senaempresa::asistencia.create', [
            'title'  => 'Nueva Sesión de Asistencia',
            'cursos'  => $cursos,
        ]);
    }

    /**
     * Almacena una nueva sesión de asistencia en la BD.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'fecha'       => 'required|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin'    => 'nullable|date_format:H:i|after_or_equal:hora_inicio',
            'tipo'        => 'required|in:PRESENCIAL,VIRTUAL,MIXTA',
        ], [
            'nombre.required'       => 'El nombre de la sesión es obligatorio.',
            'fecha.required'         => 'La fecha es obligatoria.',
            'hora_fin.after_or_equal' => 'La hora de fin debe ser posterior a la de inicio.',
            'tipo.required'          => 'El tipo de sesión es obligatorio.',
        ]);

        $asistencia = Asistencia::create($validated);

        return redirect()
            ->route('senaempresa.asistencia.show', $asistencia)
            ->with('message_result', 'Sesión de asistencia creada exitosamente.')
            ->with('typealert', 'success');
    }

    /**
     * Muestra el detalle de una sesión de asistencia con los aprendices registrados.
     */
    public function show(Asistencia $asistencia)
    {
        $asistencia->load('apprentices.person', 'apprentices.course');

        // Obtener aprendices disponibles para registrar (no vinculados todavía)
        $registeredIds = $asistencia->apprentices->pluck('id')->toArray();
        $availableApprentices = Apprentice::with('person', 'course')
            ->where('apprentice_status', 'EN FORMACIÓN')
            ->when($registeredIds, function ($q) use ($registeredIds) {
                $q->whereNotIn('id', $registeredIds);
            })
            ->orderBy('id')
            ->limit(100)
            ->get();

        return view('senaempresa::asistencia.show', [
            'title'               => 'Detalle de Asistencia',
            'asistencia'          => $asistencia,
            'availableApprentices' => $availableApprentices,
            'attendanceTypes'     => config('senaempresa.attendance_types', []),
        ]);
    }

    /**
     * Registra aprendices en una sesión de asistencia (masivo).
     */
    public function registerApprentices(Request $request, Asistencia $asistencia)
    {
        $validated = $request->validate([
            'apprentice_ids'    => 'required|array|min:1',
            'apprentice_ids.*'  => 'exists:apprentices,id',
            'asistencia_default' => 'required|in:ASISTIO,FALTO,EXCUSA,RETARDO',
        ], [
            'apprentice_ids.required' => 'Debe seleccionar al menos un aprendiz.',
            'apprentice_ids.min'      => 'Debe seleccionar al menos un aprendiz.',
        ]);

        $defaultStatus = $validated['asistencia_default'];
        $syncData = [];

        foreach ($validated['apprentice_ids'] as $apprenticeId) {
            $syncData[$apprenticeId] = ['asistencia' => $defaultStatus];
        }

        $asistencia->apprentices()->syncWithoutDetaching($syncData);

        return redirect()
            ->route('senaempresa.asistencia.show', $asistencia)
            ->with('message_result', count($syncData) . ' aprendiz(es) registrado(s) en la sesión.')
            ->with('typealert', 'success');
    }

    /**
     * Actualiza el estado de asistencia de un aprendiz específico.
     */
    public function updateApprenticeAttendance(Request $request, Asistencia $asistencia)
    {
        $validated = $request->validate([
            'apprentice_id' => 'required|exists:apprentices,id',
            'asistencia'    => 'required|in:ASISTIO,FALTO,EXCUSA,RETARDO',
            'observacion'   => 'nullable|string|max:500',
        ]);

        $asistencia->apprentices()->updateExistingPivot(
            $validated['apprentice_id'],
            [
                'asistencia'  => $validated['asistencia'],
                'observacion' => $validated['observacion'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Asistencia actualizada.',
        ]);
    }

    /**
     * Elimina un aprendiz de una sesión de asistencia.
     */
    public function removeApprentice(Request $request, Asistencia $asistencia)
    {
        $validated = $request->validate([
            'apprentice_id' => 'required|exists:apprentices,id',
        ]);

        $asistencia->apprentices()->detach($validated['apprentice_id']);

        return redirect()
            ->route('senaempresa.asistencia.show', $asistencia)
            ->with('message_result', 'Aprendiz removido de la sesión.')
            ->with('typealert', 'info');
    }

    /**
     * Cierra una sesión de asistencia (cambia estado a CERRADA).
     */
    public function close(Request $request, Asistencia $asistencia)
    {
        $asistencia->update(['estado' => 'CERRADA']);

        return redirect()
            ->route('senaempresa.asistencia.show', $asistencia)
            ->with('message_result', 'Sesión cerrada. Ya no se pueden agregar más aprendices.')
            ->with('typealert', 'info');
    }

    /**
     * Elimina una sesión de asistencia.
     */
    public function destroy(Request $request, Asistencia $asistencia)
    {
        $asistencia->delete();

        return redirect()
            ->route('senaempresa.asistencia.index')
            ->with('message_result', 'Sesión de asistencia eliminada.')
            ->with('typealert', 'danger');
    }

    // -----------------------------------------------------------------------
    // Integración con SICEFA API REST
    // -----------------------------------------------------------------------

    /**
     * Vista de sincronización con SICEFA.
     */
    public function syncView(SicefaApiService $api)
    {
        $connection = $api->checkConnection();
        $pendingCount = Asistencia::whereNull('sicefa_id')
            ->orWhereNull('sicefa_synced_at')
            ->where('estado', '!=', 'CANCELADA')
            ->count();

        return view('senaempresa::asistencia.sync', [
            'title'         => 'Sincronización con SICEFA ERP',
            'connection'    => $connection,
            'pendingCount'  => $pendingCount,
        ]);
    }

    /**
     * Sincroniza aprendices desde la API de SICEFA.
     */
    public function syncApprentices(SicefaApiService $api)
    {
        $result = $api->syncApprentices();

        $message = "Sincronización completada: {$result['created']} nuevos, {$result['updated']} actualizados.";
        if (!empty($result['errors'])) {
            $message .= ' Errores: ' . implode('; ', array_slice($result['errors'], 0, 5));
        }

        return redirect()
            ->route('senaempresa.asistencia.sync')
            ->with('message_result', $message)
            ->with('typealert', empty($result['errors']) ? 'success' : 'warning');
    }

    /**
     * Envía las asistencias pendientes a SICEFA.
     */
    public function syncAttendance(SicefaApiService $api)
    {
        $result = $api->syncPendingAttendance();

        $message = "{$result['synced']} sesión(es) enviada(s) a SICEFA.";
        if (!empty($result['errors'])) {
            $message .= ' Errores: ' . implode('; ', array_slice($result['errors'], 0, 5));
        }

        return redirect()
            ->route('senaempresa.asistencia.sync')
            ->with('message_result', $message)
            ->with('typealert', empty($result['errors']) ? 'success' : 'warning');
    }

    /**
     * Envía una sesión específica a SICEFA.
     */
    public function sendToSicefa(Asistencia $asistencia, SicefaApiService $api)
    {
        $result = $api->sendAttendance($asistencia);

        return redirect()
            ->route('senaempresa.asistencia.show', $asistencia)
            ->with('message_result', $result['message'])
            ->with('typealert', $result['success'] ? 'success' : 'danger');
    }
}
