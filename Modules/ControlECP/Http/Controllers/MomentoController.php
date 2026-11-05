<?php

namespace Modules\ControlECP\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\ActividadEcp;

class MomentoController extends Controller
{
    /**
     * HU5 — Listado de momentos como tarjetas con nombre, descripción
     *        y cantidad de actividades.
     */
    public function index()
    {
        $usuario = Auth::user();

        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve el resultado de este m�todo. */ redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a Momentos de Control ECP.');
        }

        $momentos = Momento::withCount([
            'actividades',
            'actividades as actividades_activas_count' => fn($q) => $q->where('estado', 'Activo'),
        ])
        ->orderBy('orden')
        ->get();

        $stats = [
            'total_momentos'  => $momentos->count(),
            'activos'         => $momentos->where('estado', 'Activo')->count(),
            'inactivos'       => $momentos->where('estado', 'Inactivo')->count(),
            'total_actividades' => $momentos->sum('actividades_count'),
        ];

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.momentos', compact('momentos', 'stats'));
    }

    /**
     * HU1 / HU7 — Registra un nuevo momento.
     * Valida campos obligatorios antes de guardar.
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        // HU7 — Validar campos obligatorios
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'required|string|max:1000',
            'icono'       => ['nullable', 'string', 'max:80', 'regex:/^fa-[a-z0-9-]+$/'],
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'orden'       => 'nullable|integer|min:0|max:99',
        ], [
            'nombre.required' => 'El nombre del momento es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 120 caracteres.',
            'descripcion.required' => 'La descripción del momento es obligatoria.',
            'orden.max'      => 'El orden no puede ser superior a 99.',
        ]);

        $momento = Momento::create([
            'nombre'      => trim($request->nombre),
            'descripcion' => trim($request->descripcion ?? ''),
            'icono'       => $request->icono   ?? 'fa-star',
            'color'       => $request->color   ?? '#39A900',
            'orden'       => $request->orden   ?? (Momento::max('orden') + 1),
            'estado'      => 'Activo',
        ]);

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([
            'success' => true,
            'message' => "El momento \"{$momento->nombre}\" fue creado exitosamente.",
            'momento' => [
                'id'          => $momento->id,
                'nombre'      => $momento->nombre,
                'descripcion' => $momento->descripcion,
                'icono'       => $momento->icono,
                'color'       => $momento->color,
                'orden'       => $momento->orden,
                'estado'      => $momento->estado,
                'actividades_count' => 0,
                'actividades_activas_count' => 0,
            ],
        ]);
    }

    /**
     * HU2 / HU7 — Actualiza un momento existente.
     */
    public function update(Request $request, $id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $momento = Momento::findOrFail($id);

        // HU7 — Validar campos obligatorios
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'required|string|max:1000',
            'icono'       => ['nullable', 'string', 'max:80', 'regex:/^fa-[a-z0-9-]+$/'],
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'orden'       => 'nullable|integer|min:0|max:99',
        ], [
            'nombre.required' => 'El nombre del momento es obligatorio.',
            'descripcion.required' => 'La descripción del momento es obligatoria.',
            'orden.max'      => 'El orden no puede ser superior a 99.',
        ]);

        $momento->update([
            'nombre'      => trim($request->nombre),
            'descripcion' => trim($request->descripcion ?? ''),
            'icono'       => $request->icono  ?? $momento->icono,
            'color'       => $request->color  ?? $momento->color,
            'orden'       => $request->orden  ?? $momento->orden,
        ]);

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([
            'success' => true,
            'message' => "El momento \"{$momento->nombre}\" fue actualizado correctamente.",
            'momento' => [
                'id'          => $momento->id,
                'nombre'      => $momento->nombre,
                'descripcion' => $momento->descripcion,
                'icono'       => $momento->icono,
                'color'       => $momento->color,
                'orden'       => $momento->orden,
                'estado'      => $momento->estado,
            ],
        ]);
    }

    /**
     * HU3 — Desactiva un momento: cambia estado a 'Inactivo'.
     * El momento deja de aparecer en las opciones activas pero se
     * conserva su información y registros históricos.
     */
    public function deactivate($id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $momento = Momento::findOrFail($id);

        if ($momento->estado === 'Inactivo') {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'El momento ya está inactivo.']);
        }

        $momento->estado = 'Inactivo';
        $momento->save();

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([
            'success' => true,
            'message' => "El momento \"{$momento->nombre}\" fue desactivado. Se conserva su información y registros históricos.",
        ]);
    }

    /**
     * HU4 — Activa un momento: cambia estado a 'Activo'.
     */
    public function activate($id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $momento = Momento::findOrFail($id);

        if ($momento->estado === 'Activo') {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'El momento ya está activo.']);
        }

        $momento->estado = 'Activo';
        $momento->save();

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([
            'success' => true,
            'message' => "El momento \"{$momento->nombre}\" fue reactivado y está disponible nuevamente.",
        ]);
    }

    /**
     * HU6 — Devuelve la lista de actividades de un momento específico.
     */
    public function actividades($id)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole('controlecp.admin')) {
            return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        $momento = Momento::with('actividades')->findOrFail($id);

        $actividades = $momento->actividades->map(fn($a) => [
            'id'          => $a->id,
            'numero'      => $a->numero,
            'nombre'      => $a->nombre,
            'descripcion' => $a->descripcion,
            'objetivo'    => $a->objetivo,
            'duracion'    => $a->duracion,
            'duracion_min'=> $a->duracion_min,
            'metodologia' => $a->metodologia,
            'estado'      => $a->estado,
        ]);

        return /* Devuelve los datos en formato JSON para la interfaz. */ response()->json([
            'success'           => true,
            'momento_id'        => $momento->id,
            'momento_nombre'    => $momento->nombre,
            'momento_color'     => $momento->color,
            'momento_icono'     => $momento->icono,
            'total_actividades' => $actividades->count(),
            'actividades'       => $actividades,
        ]);
    }
}
