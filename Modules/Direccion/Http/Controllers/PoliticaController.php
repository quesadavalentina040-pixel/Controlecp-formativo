<?php

namespace Modules\Direccion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Direccion\Entities\Politica;

class PoliticaController extends Controller
{
    /**
     * Listar políticas / directrices con filtros y estadísticas.
     */
    public function index(Request $request)
    {
        $query = Politica::query();

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('responsable', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $politicas = $query->orderBy('created_at', 'desc')->paginate(10);

        // Métricas de resumen
        $totalPoliticas = Politica::count();
        $activas = Politica::where('estado', 'Activa')->count();
        $enRevision = Politica::where('estado', 'En Revisión')->count();
        $inactivas = Politica::where('estado', 'Inactiva')->count();

        return view('direccion::index', compact('politicas', 'totalPoliticas', 'activas', 'enRevision', 'inactivas'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        // Generar un código sugerido automático
        $ultimoId = Politica::withTrashed()->max('id') ?? 0;
        $codigoSugerido = 'POL-' . date('Y') . '-' . str_pad($ultimoId + 1, 3, '0', STR_PAD_LEFT);

        return view('direccion::create', compact('codigoSugerido'));
    }

    /**
     * Almacenar una nueva política.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:politicas,codigo',
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|in:Estratégica,Calidad,Seguridad,Operativa',
            'descripcion' => 'required|string',
            'vigencia' => 'required|integer|min:2020|max:2050',
            'estado' => 'required|string|in:Activa,En Revisión,Inactiva',
            'responsable' => 'required|string|max:255'
        ]);

        Politica::create($validated);

        return redirect()->route('direccion.politicas.index')->with('success', '¡Política directiva creada exitosamente!');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit($id)
    {
        $politica = Politica::findOrFail($id);
        return view('direccion::edit', compact('politica'));
    }

    /**
     * Actualizar política existente.
     */
    public function update(Request $request, $id)
    {
        $politica = Politica::findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:politicas,codigo,' . $politica->id,
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|in:Estratégica,Calidad,Seguridad,Operativa',
            'descripcion' => 'required|string',
            'vigencia' => 'required|integer|min:2020|max:2050',
            'estado' => 'required|string|in:Activa,En Revisión,Inactiva',
            'responsable' => 'required|string|max:255'
        ]);

        $politica->update($validated);

        return redirect()->route('direccion.politicas.index')->with('success', '¡Política directiva actualizada con éxito!');
    }

    /**
     * Eliminar política.
     */
    public function destroy($id)
    {
        $politica = Politica::findOrFail($id);
        $politica->delete();

        return redirect()->route('direccion.politicas.index')->with('success', '¡Política directiva eliminada correctamente!');
    }
}
