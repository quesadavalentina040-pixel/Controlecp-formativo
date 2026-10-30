<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\Material;

class MaterialController extends Controller // Controlador de consulta y mantenimiento del inventario de materiales.
{
    /**
     * Verifica que el usuario tenga acceso al inventario (admin o instructor).
     */
    private function verificarAcceso() // verificarAcceso(): Comprueba permisos de administrador o instructor; se usa antes de operar sobre el inventario.
    {
        $usuario = Auth::user(); // Recupera la cuenta autenticada para comprobar sus permisos.

        if (!$usuario->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) {
            return redirect()
                ->route('controlecp.index') // Envía al inicio del módulo cuando el rol no está autorizado.
                ->with('error', 'No tienes permisos para acceder al inventario de Control ECP.'); // Conserva un aviso para mostrarlo en la siguiente vista.
        }

        return null; // Indica que la comprobación pasó y se puede continuar.
    }

    /**
     * 1. LISTAR MATERIALES (con búsqueda y filtro por categoría).
     */
    public function index(Request $request) // index(): Consulta materiales con búsqueda, filtros y totales; se usa al abrir el inventario.
    {
        if ($redirect = $this->verificarAcceso()) { // Detiene la acción si la verificación devolvió una redirección.
            return $redirect; // Reutiliza la respuesta preparada por verificarAcceso().
        }

        $query = Material::query(); // Inicia una consulta de materiales que se completará según los filtros.

        if ($request->filled('buscar')) { // Comprueba que el formulario haya enviado una búsqueda no vacía. | Búsqueda por nombre o código
            $buscar = $request->input('buscar'); // Lee el texto de búsqueda recibido en la solicitud.
            $query->where(function ($q) use ($buscar) { // Agrupa las condiciones para que nombre o código coincidan.
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%"); // Busca el texto en cualquiera de esos dos campos.
            });
        }

        if ($request->filled('categoria')) { // Aplica este filtro solo si se seleccionó una categoría. | Filtro por categoría
            $query->where('categoria', $request->input('categoria')); // Limita resultados a la categoría solicitada.
        }

        $materiales = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString(); // Ordena los más recientes primero y pagina conservando filtros en los enlaces.

        $totalMateriales = Material::count(); // Cuenta todos los registros de materiales. | Estadísticas del inventario
        $totalUnidades   = Material::sum('cantidad'); // Suma las unidades registradas en el inventario.
        $bajoStock       = Material::whereColumn('cantidad', '<=', 'stock_minimo')->count(); // Cuenta materiales cuya cantidad no supera su mínimo.
        $agotados        = Material::where('estado', 'Agotado')->count(); // Cuenta los materiales marcados como agotados.

        return view('controlecp::administrador.inventario', compact(
            'materiales',
            'totalMateriales',
            'totalUnidades',
            'bajoStock',
            'agotados'
        )); // Renderiza la vista y le entrega resultados y estadísticas.
    }

    /**
     * 2. FORMULARIO DE CREACIÓN.
     */
    public function create() // create(): Muestra el formulario y catálogos para crear material; se usa al solicitar un alta.
    {
        if ($redirect = $this->verificarAcceso()) { // Comprueba permisos antes de mostrar el formulario.
            return $redirect;
        }

        return view('controlecp::administrador.inventario_create', [
            'categorias' => Material::CATEGORIAS, // Ofrece las categorías permitidas por el modelo.
            'unidades'   => Material::UNIDADES,
            'estados'    => Material::ESTADOS,
        ]); // Renderiza el alta con los catálogos permitidos.
    }

    /**
     * 3. GUARDAR MATERIAL.
     */
    public function store(Request $request) // store(): Valida y registra un material y su imagen opcional; se usa al enviar el formulario de alta.
    {
        if ($redirect = $this->verificarAcceso()) { // Evita procesar el alta a quien no tenga acceso.
            return $redirect;
        }

        $validated = $request->validate([ // Valida y obtiene únicamente los datos aceptados antes de guardarlos.
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
        ]); // Si alguna regla falla, Laravel responde con los errores de validación.

        if ($request->hasFile('imagen')) { // Comprueba si la solicitud contiene un archivo de imagen válido. | Guardar imagen si se subió
            $validated['imagen'] = $request->file('imagen')->store('controlecp/materiales', 'public'); // Guarda la imagen en el disco público y registra su ruta.
        }

        Material::create($validated); // Crea el registro usando solo los datos validados.

        return redirect()
            ->route('controlecp.administrador.inventario') // Regresa al listado después de guardar.
            ->with('success', '¡Material registrado con éxito en el inventario!'); // Envía un mensaje temporal de confirmación.
    }

    /**
     * 4. FORMULARIO DE EDICIÓN.
     */
    public function edit($id) // edit(): Busca el material y muestra sus datos editables; se usa al solicitar su edición.
    {
        if ($redirect = $this->verificarAcceso()) { // Comprueba permisos antes de buscar el material.
            return $redirect;
        }

        $material = Material::findOrFail($id); // Busca por identificador y genera un error 404 si no existe.

        return view('controlecp::administrador.inventario_edit', [
            'material'   => $material,
            'categorias' => Material::CATEGORIAS,
            'unidades'   => Material::UNIDADES,
            'estados'    => Material::ESTADOS,
        ]); // Entrega el material y las opciones de formulario a la vista.
    }

    /**
     * 5. ACTUALIZAR MATERIAL.
     */
    public function update(Request $request, $id) // update(): Valida y guarda cambios y la imagen del material; se usa al enviar la edición.
    {
        if ($redirect = $this->verificarAcceso()) { // Impide cambios si el usuario no tiene permisos.
            return $redirect;
        }

        $material = Material::findOrFail($id); // Recupera el registro que se actualizará o responde 404.

        $validated = $request->validate([ // Valida los campos; el código único ignora el material actual.
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
        ]); // Los datos fuera de las reglas no se usan para la actualización.

        if ($request->hasFile('imagen')) { // Solo reemplaza el archivo cuando se adjuntó uno nuevo. | Reemplazar imagen si se subió una nueva
            if ($material->imagen) { // Comprueba si ya había una imagen que deba retirarse.
                Storage::disk('public')->delete($material->imagen); // Elimina el archivo anterior del disco público.
            }
            $validated['imagen'] = $request->file('imagen')->store('controlecp/materiales', 'public'); // Guarda el reemplazo y conserva su ruta.
        } else {
            unset($validated['imagen']); // Evita borrar la ruta existente cuando no se envió una imagen nueva.
        }

        $material->update($validated); // Actualiza el material con los campos que pasaron la validación.

        return redirect()
            ->route('controlecp.administrador.inventario') // Regresa al listado tras guardar los cambios.
            ->with('success', '¡Material actualizado correctamente!'); // Informa el resultado en la siguiente vista.
    }

    /**
     * 6. ELIMINAR MATERIAL.
     */
    public function destroy($id) // destroy(): Elimina la imagen y el registro del material; se usa al confirmar su eliminación.
    {
        if ($redirect = $this->verificarAcceso()) { // Verifica permisos antes de eliminar el registro.
            return $redirect;
        }

        $material = Material::findOrFail($id); // Busca el material y detiene la acción si no existe.

        if ($material->imagen) { // Limpia el archivo asociado si el material tenía imagen.
            Storage::disk('public')->delete($material->imagen); // Borra la imagen del almacenamiento público.
        }

        $material->delete(); // Elimina el registro del inventario.

        return redirect()
            ->route('controlecp.administrador.inventario') // Vuelve al inventario después de eliminar.
            ->with('success', '¡Material eliminado del inventario!'); // Muestra la confirmación como mensaje temporal.
    }
}
