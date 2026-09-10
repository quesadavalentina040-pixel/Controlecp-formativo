<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ControlECP\Entities\Elemento;

class ElementoController extends Controller
{
    // 1. LISTAR REGISTROS
    public function index()
    {
        $elementos = Elemento::orderBy('created_at', 'desc')->paginate(10);

        return view('controlecp::elementos.index', compact('elementos'));
    }

    // 2. FORMULARIO DE CREACIÓN
    public function create()
    {
        return view('controlecp::create');
    }

    // 3. GUARDAR EN BASE DE DATOS
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:elementos,codigo|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        Elemento::create($validated);

        return redirect()
            ->route('controlecp.elementos')
            ->with('success', '¡Registro creado con éxito!');
    }

    // 4. FORMULARIO DE EDICIÓN
    public function edit($id)
    {
        $elemento = Elemento::findOrFail($id);

        return view('controlecp::edit', compact('elemento'));
    }

    // 5. ACTUALIZAR REGISTRO
    public function update(Request $request, $id)
    {
        $elemento = Elemento::findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:elementos,codigo,' . $elemento->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        $elemento->update($validated);

        return redirect()
            ->route('controlecp.elementos')
            ->with('success', '¡Registro actualizado con éxito!');
    }

    // 6. ELIMINAR REGISTRO
    public function destroy($id)
    {
        $elemento = Elemento::findOrFail($id);

        $elemento->delete();

        return redirect()
            ->route('controlecp.elementos')
            ->with('success', '¡Registro eliminado correctamente!');
    }
}