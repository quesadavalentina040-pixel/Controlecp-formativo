<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\Material;

class MaterialController extends Controller
{
    /**
     * Verifica que el usuario tenga acceso al inventario (admin o instructor).
     */
    private function verificarAcceso()
    {
        $usuario = Auth::user();

        if (!$usuario->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder al inventario de Control ECP.');
        }

        return null;
    }

    /**
     * 1. LISTAR MATERIALES (con búsqueda y filtro por categoría).
     */
    public function index(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $query = Material::query();

        // Búsqueda por nombre o código
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        $materiales = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Estadísticas del inventario
        $totalMateriales = Material::count();
        $totalUnidades   = Material::sum('cantidad');
        $bajoStock       = Material::whereColumn('cantidad', '<=', 'stock_minimo')->count();
        $agotados        = Material::where('estado', 'Agotado')->count();

        return view('controlecp::administrador.inventario', compact(
            'materiales',
            'totalMateriales',
            'totalUnidades',
            'bajoStock',
            'agotados'
        ));
    }

    /**
     * 2. FORMULARIO DE CREACIÓN.
     */
    public function create()
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        return view('controlecp::administrador.inventario_create', [
            'categorias' => Material::CATEGORIAS,
            'unidades'   => Material::UNIDADES,
            'estados'    => Material::ESTADOS,
        ]);
    }

    /**
     * 3. GUARDAR MATERIAL.
     */
    public function store(Request $request)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $validated = $request->validate([
            'codigo'       => 'required|string|max:50|unique:materiales,codigo',
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'imagen'       => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'categoria'    => 'required|in:' . implode(',', Material::CATEGORIAS),
            'cantidad'     => 'required|integer|min:0',
            'unidad'       => 'required|in:' . implode(',', Material::UNIDADES),
            'stock_minimo' => 'required|integer|min:0',
            'ubicacion'    => 'nullable|string|max:255',
            'estado'       => 'required|in:' . implode(',', Material::ESTADOS),
        ]);

        // Guardar imagen si se subió
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('controlecp/materiales', 'public');
        }

        Material::create($validated);

        return redirect()
            ->route('controlecp.administrador.inventario')
            ->with('success', '¡Material registrado con éxito en el inventario!');
    }

    /**
     * 4. FORMULARIO DE EDICIÓN.
     */
    public function edit($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $material = Material::findOrFail($id);

        return view('controlecp::administrador.inventario_edit', [
            'material'   => $material,
            'categorias' => Material::CATEGORIAS,
            'unidades'   => Material::UNIDADES,
            'estados'    => Material::ESTADOS,
        ]);
    }

    /**
     * 5. ACTUALIZAR MATERIAL.
     */
    public function update(Request $request, $id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'codigo'       => 'required|string|max:50|unique:materiales,codigo,' . $material->id,
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'imagen'       => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'categoria'    => 'required|in:' . implode(',', Material::CATEGORIAS),
            'cantidad'     => 'required|integer|min:0',
            'unidad'       => 'required|in:' . implode(',', Material::UNIDADES),
            'stock_minimo' => 'required|integer|min:0',
            'ubicacion'    => 'nullable|string|max:255',
            'estado'       => 'required|in:' . implode(',', Material::ESTADOS),
        ]);

        // Reemplazar imagen si se subió una nueva
        if ($request->hasFile('imagen')) {
            if ($material->imagen) {
                Storage::disk('public')->delete($material->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('controlecp/materiales', 'public');
        } else {
            unset($validated['imagen']);
        }

        $material->update($validated);

        return redirect()
            ->route('controlecp.administrador.inventario')
            ->with('success', '¡Material actualizado correctamente!');
    }

    /**
     * 6. ELIMINAR MATERIAL.
     */
    public function destroy($id)
    {
        if ($redirect = $this->verificarAcceso()) {
            return $redirect;
        }

        $material = Material::findOrFail($id);

        if ($material->imagen) {
            Storage::disk('public')->delete($material->imagen);
        }

        $material->delete();

        return redirect()
            ->route('controlecp.administrador.inventario')
            ->with('success', '¡Material eliminado del inventario!');
    }
}
