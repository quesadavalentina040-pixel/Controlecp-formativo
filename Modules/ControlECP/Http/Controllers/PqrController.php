<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\Pqr;

class PqrController extends Controller
{
    /**
     * Verifica que el usuario pueda gestionar PQR (admin o instructor).
     */
    private function verificarAcceso()
    {
        if (!Auth::user()->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al módulo PQR de Control ECP.');
        }

        return null;
    }

    /**
     * 1. LISTADO DE PQR con filtros y estadísticas.
     */
    public function index(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $query = Pqr::query();

        // Búsqueda por radicado, nombre o asunto
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('radicado', 'like', "%{$buscar}%")
                  ->orWhere('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('asunto', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $pqrs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Estadísticas
        $total      = Pqr::count();
        $pendientes = Pqr::where('estado', 'Pendiente')->count();
        $enTramite  = Pqr::where('estado', 'En trámite')->count();
        $resueltas  = Pqr::whereIn('estado', ['Resuelto', 'Cerrado'])->count();

        return view('controlecp::administrador.pqr', compact(
            'pqrs', 'total', 'pendientes', 'enTramite', 'resueltas'
        ));
    }

    /**
     * 2. FORMULARIO DE RADICACIÓN.
     */
    public function create()
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        return view('controlecp::administrador.pqr_create', [
            'tipos'       => Pqr::TIPOS,
            'perfiles'    => Pqr::PERFILES,
            'prioridades' => Pqr::PRIORIDADES,
            'radicado'    => Pqr::generarRadicado(),
        ]);
    }

    /**
     * 3. GUARDAR NUEVA PQR.
     */
    public function store(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $validated = $request->validate([
            'tipo'               => 'required|in:' . implode(',', Pqr::TIPOS),
            'nombre_solicitante' => 'required|string|max:255',
            'documento'          => 'nullable|string|max:50',
            'email'              => 'required|email|max:255',
            'telefono'           => 'nullable|string|max:30',
            'perfil'             => 'required|in:' . implode(',', Pqr::PERFILES),
            'asunto'             => 'required|string|max:255',
            'mensaje'            => 'required|string',
            'prioridad'          => 'required|in:' . implode(',', Pqr::PRIORIDADES),
            'anexo'              => 'nullable|file|mimes:pdf,jpeg,jpg,png,webp,doc,docx|max:4096',
        ]);

        $validated['radicado'] = Pqr::generarRadicado();
        $validated['estado']   = 'Pendiente';

        if ($request->hasFile('anexo')) {
            $validated['anexo'] = $request->file('anexo')->store('controlecp/pqr', 'public');
        }

        $pqr = Pqr::create($validated);

        return redirect()
            ->route('controlecp.administrador.pqr')
            ->with('success', "¡PQR radicada con éxito! Número de radicado: {$pqr->radicado}");
    }

    /**
     * 4. VER DETALLE Y RESPONDER.
     */
    public function show($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $pqr = Pqr::with('respondidoPor')->findOrFail($id);

        return view('controlecp::administrador.pqr_show', [
            'pqr'     => $pqr,
            'estados' => Pqr::ESTADOS,
        ]);
    }

    /**
     * 5. REGISTRAR RESPUESTA / ACTUALIZAR ESTADO.
     */
    public function responder(Request $request, $id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $pqr = Pqr::findOrFail($id);

        $validated = $request->validate([
            'respuesta' => 'required|string',
            'estado'    => 'required|in:' . implode(',', Pqr::ESTADOS),
        ]);

        $pqr->update([
            'respuesta'       => $validated['respuesta'],
            'estado'          => $validated['estado'],
            'fecha_respuesta' => now(),
            'respondido_por'  => Auth::id(),
        ]);

        return redirect()
            ->route('controlecp.administrador.pqr.show', $pqr->id)
            ->with('success', '¡Respuesta registrada correctamente!');
    }

    /**
     * 6. ELIMINAR PQR.
     */
    public function destroy($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $pqr = Pqr::findOrFail($id);

        if ($pqr->anexo) {
            Storage::disk('public')->delete($pqr->anexo);
        }

        $pqr->delete();

        return redirect()
            ->route('controlecp.administrador.pqr')
            ->with('success', '¡PQR eliminada del sistema!');
    }
}
