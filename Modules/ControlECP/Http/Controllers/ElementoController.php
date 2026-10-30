<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ControlECP\Entities\Elemento;

class ElementoController extends Controller // Controlador para consultar y mantener el catálogo de elementos.
{
    public function index() // 1. LISTAR REGISTROS | index(): Consulta y pagina los elementos; se usa al abrir el listado del catálogo.
    {
        $elementos = Elemento::orderBy('created_at', 'desc')->paginate(10); // orderBy define el orden descendente y paginate consulta la página actual con 10 filas.

        return view('controlecp::elementos.index', compact('elementos')); // view entrega la plantilla y compact empaqueta la variable para que la vista la reciba.
    }

    public function create() // 2. FORMULARIO DE CREACIÓN | create(): Muestra el formulario para crear un elemento; se usa al solicitar el alta.
    {
        return view('controlecp::create'); // Devuelve el formulario vacío; no consulta ni modifica registros.
    }

    public function store(Request $request) // 3. GUARDAR EN BASE DE DATOS | store(): Valida y guarda un elemento nuevo; se usa al enviar el formulario de alta.
    {
        $validated = $request->validate([ // validate aplica las reglas al envío y devuelve solo los campos validados; si falla, Laravel redirige con errores.
            'codigo' => 'required|string|unique:elementos,codigo|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        Elemento::create($validated); // create inserta un elemento usando los campos permitidos por asignación masiva.

        return redirect() // redirect vuelve al listado y with conserva un mensaje temporal para la siguiente petición.
            ->route('controlecp.elementos')
            ->with('success', '¡Registro creado con éxito!');
    }

    public function edit($id) // 4. FORMULARIO DE EDICIÓN | edit(): Busca el elemento y muestra su formulario de edición; se usa al solicitar sus cambios.
    {
        $elemento = Elemento::findOrFail($id); // findOrFail busca por clave primaria y responde con 404 si el elemento no existe.

        return view('controlecp::edit', compact('elemento')); // Presenta el formulario de edición con el modelo encontrado.
    }

    public function update(Request $request, $id) // 5. ACTUALIZAR REGISTRO | update(): Valida y guarda los cambios del elemento; se usa al enviar su formulario de edición.
    {
        $elemento = Elemento::findOrFail($id); // Carga el registro que se actualizará; findOrFail evita editar un identificador inexistente.

        $validated = $request->validate([ // Comprueba los campos recibidos; la regla unique excluye el propio id para permitir conservar su código actual.
            'codigo' => 'required|string|max:50|unique:elementos,codigo,' . $elemento->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        $elemento->update($validated); // update persiste en el registro solo los valores que pasaron la validación.

        return redirect() // Redirige al catálogo con un aviso temporal de confirmación.
            ->route('controlecp.elementos')
            ->with('success', '¡Registro actualizado con éxito!');
    }

    public function destroy($id) // 6. ELIMINAR REGISTRO | destroy(): Busca y elimina el elemento; se usa al confirmar su eliminación.
    {
        $elemento = Elemento::findOrFail($id); // Recupera el modelo o detiene la acción con un 404 si no existe.

        $elemento->delete(); // delete elimina el registro correspondiente de la base de datos.

        return redirect() // Vuelve al listado y muestra el resultado mediante un mensaje de sesión.
            ->route('controlecp.elementos')
            ->with('success', '¡Registro eliminado correctamente!');
    }
}