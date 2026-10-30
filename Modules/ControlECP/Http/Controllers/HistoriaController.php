<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\BloqueHistoria;
use Modules\ControlECP\Http\Requests\HistoriaBloqueRequest;

class HistoriaController extends Controller // Controlador para administrar los bloques de contenido de la historia.
{
    /**
     * Verifica que el usuario tenga rol de administrador de Control ECP.
     */
    private function verificarAccesoAdmin() // verificarAccesoAdmin(): Comprueba el rol administrador antes de gestionar la historia; se usa en las acciones protegidas.
    {
        $usuario = Auth::user(); // Auth::user identifica la sesión actual; puede devolver null si no hay una cuenta autenticada.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Comprueba existencia y rol antes de continuar con la administración.
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a la gestión de la Historia.');
        }
        return null;
    }

    /**
     * Muestra la vista de gestión de la historia (vista en zigzag con panel de administración).
     */
    public function index(Request $request) // index(): Consulta y presenta los bloques de historia; se usa al abrir su panel de administración.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Si el control de acceso devuelve una redirección, no se ejecuta ninguna consulta.
            return $res;
        }

        $bloques = BloqueHistoria::orderBy('orden', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(3);

        $indicadores = [
            'total'     => BloqueHistoria::count(),
            'activos'   => BloqueHistoria::where('activo', true)->count(),
            'inactivos' => BloqueHistoria::where('activo', false)->count(),
        ];

        return view('controlecp::administrador.repositorio.historia', compact('bloques', 'indicadores')); // Renderiza el panel y entrega tanto los bloques como sus indicadores.
    }

    /**
     * Almacena un nuevo bloque de historia.
     */
    public function store(HistoriaBloqueRequest $request) // store(): Valida y guarda un nuevo bloque de historia; se usa al enviar el formulario de creación.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Reutiliza el control de acceso y termina pronto si el usuario no es administrador.
            return $res;
        }

        $rutaArchivo = null; // Inicializa ambos destinos para que el tipo de contenido determine cuál se completa.
        $urlVideo = null;

        $siguienteOrden = (BloqueHistoria::max('orden') ?? 0) + 1; // Asignar el siguiente número de orden | max consulta el valor más alto; si aún no hay filas, ?? 0 permite empezar la secuencia en 1.

        if ($request->tipo_contenido === 'imagen' && $request->hasFile('archivo_imagen')) { // Según el tipo indicado, hasFile comprueba que llegó un archivo y store lo guarda en el disco público.
            $rutaArchivo = $request->file('archivo_imagen')->store('historia/imagenes', 'public');
        } elseif ($request->tipo_contenido === 'audio' && $request->hasFile('archivo_audio')) {
            $rutaArchivo = $request->file('archivo_audio')->store('historia/audios', 'public');
        } elseif ($request->tipo_contenido === 'video') {
            $urlVideo = $request->url_video; // En videos se conserva la URL recibida en lugar de guardar un archivo.
        }

        BloqueHistoria::create([ // create inserta el bloque con los atributos preparados; los textos se limpian con trim.
            'titulo'         => trim($request->titulo),
            'texto'          => trim($request->texto),
            'tipo_contenido' => $request->tipo_contenido,
            'ruta_archivo'   => $rutaArchivo,
            'url_video'      => $urlVideo,
            'orden'          => $siguienteOrden,
            'activo'         => true,
        ]);

        return redirect() // Regresa al panel y guarda un mensaje temporal para mostrar la confirmación.
            ->route('controlecp.administrador.repositorio.historia.index')
            ->with('success', 'El bloque de historia fue creado y publicado exitosamente.');
    }

    /**
     * Actualiza un bloque de historia existente.
     */
    public function update(HistoriaBloqueRequest $request, $id) // update(): Valida y guarda cambios en un bloque existente; se usa al guardar su edición.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Comprueba permisos antes de modificar contenido.
            return $res;
        }

        $bloque = BloqueHistoria::findOrFail($id); // Busca el bloque por su clave y responde con 404 si no se encuentra.

        $rutaArchivo = $bloque->ruta_archivo; // Conserva la ruta anterior mientras se decide si se reemplaza o se limpia según el nuevo tipo.
        $urlVideo = null;

        if ($request->tipo_contenido === 'imagen') {
            if ($request->hasFile('archivo_imagen')) { // Si antes era otro tipo o se sube un nuevo archivo | hasFile evita borrar o sustituir el medio si el formulario no envió uno nuevo.
                if ($bloque->ruta_archivo && Storage::disk('public')->exists($bloque->ruta_archivo)) { // Eliminar archivo anterior si existe | disk selecciona el almacenamiento público; exists evita intentar borrar una ruta inexistente.
                    Storage::disk('public')->delete($bloque->ruta_archivo);
                }
                $rutaArchivo = $request->file('archivo_imagen')->store('historia/imagenes', 'public');
            }
        } elseif ($request->tipo_contenido === 'audio') {
            if ($request->hasFile('archivo_audio')) { // Para audio se sigue la misma política: solo reemplazar cuando llegó un archivo nuevo.
                if ($bloque->ruta_archivo && Storage::disk('public')->exists($bloque->ruta_archivo)) {
                    Storage::disk('public')->delete($bloque->ruta_archivo);
                }
                $rutaArchivo = $request->file('archivo_audio')->store('historia/audios', 'public');
            }
        } elseif ($request->tipo_contenido === 'video') {
            if ($bloque->ruta_archivo && Storage::disk('public')->exists($bloque->ruta_archivo)) { // Si cambió a video, borrar archivo anterior de imagen/audio | Un video sustituye el archivo anterior y guarda una URL en su lugar.
                Storage::disk('public')->delete($bloque->ruta_archivo);
            }
            $rutaArchivo = null;
            $urlVideo = $request->url_video;
        }

        $bloque->update([ // update persiste los atributos editados en el modelo ya encontrado.
            'titulo'         => trim($request->titulo),
            'texto'          => trim($request->texto),
            'tipo_contenido' => $request->tipo_contenido,
            'ruta_archivo'   => $rutaArchivo,
            'url_video'      => $urlVideo,
        ]);

        return redirect() // Vuelve al listado de historia con un mensaje de confirmación.
            ->route('controlecp.administrador.repositorio.historia.index')
            ->with('success', 'El bloque de historia fue actualizado exitosamente.');
    }

    /**
     * Persiste el nuevo orden de los bloques tras moverlos.
     */
    public function reordenar(Request $request) // reordenar(): Valida y guarda el orden de los bloques; se usa al reorganizar la historia.
    {
        $usuario = Auth::user(); // Obtiene la cuenta que envía el orden para aplicar la autorización también en esta ruta AJAX.
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) { // Sin sesión o rol administrador, responde JSON con HTTP 403 (prohibido).
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        $request->validate([ // validate comprueba que ids sea una lista y que cada valor identifique un bloque existente.
            'ids'   => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:bloques_historia,id'],
        ]);

        $ids = $request->input('ids', []); // input lee el parámetro y usa una lista vacía como valor predeterminado si no fue enviado.

        if (empty($ids)) { // Si la lista quedó vacía, responde JSON con HTTP 422 para indicar datos insuficientes.
            return response()->json([
                'success' => false,
                'message' => 'No se enviaron bloques para reordenar.',
            ], 422);
        }

        DB::transaction(function () use ($ids) { // La transacción hace que todos los cambios de orden se confirmen juntos o se reviertan ante un error.
            foreach ($ids as $posicion => $id) { // Recorre cada id con su posición y actualiza su orden a partir de 1.
                BloqueHistoria::where('id', $id)->update([ // where limita la fila afectada y update escribe el nuevo número de orden.
                    'orden' => $posicion + 1,
                ]);
            }
        });

        return response()->json([ // Devuelve resultado JSON exitoso para que la interfaz confirme el reordenamiento.
            'success' => true,
            'message' => '¡El nuevo orden de los bloques fue guardado exitosamente!',
        ]);
    }

    /**
     * Desactiva un bloque (NO elimina el registro ni los archivos).
     */
    public function desactivar(Request $request, $id) // desactivar(): Desactiva el bloque identificado; se usa al ocultarlo sin eliminarlo.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Un usuario no autorizado recibe JSON 403 si la petición lo solicita; de lo contrario, se conserva la redirección.
            if ($request->wantsJson()) { // wantsJson detecta si el cliente espera una respuesta JSON.
                return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
            }
            return $res;
        }

        $bloque = BloqueHistoria::findOrFail($id); // Busca el bloque o devuelve 404, y actualiza su bandera sin borrar el registro ni sus archivos.
        $bloque->update(['activo' => false]);

        if ($request->wantsJson()) { // Responde en el formato solicitado: JSON para AJAX o redirección con mensaje para navegación normal.
            return response()->json([
                'success' => true,
                'message' => 'El bloque "' . $bloque->titulo . '" ha sido desactivado. Ya no será visible para los aprendices.',
            ]);
        }

        return redirect() // Regresa al panel administrativo y conserva el aviso en la sesión.
            ->route('controlecp.administrador.repositorio.historia.index')
            ->with('success', 'El bloque "' . $bloque->titulo . '" ha sido desactivado exitosamente.');
    }

    /**
     * Reactiva un bloque previamente desactivado.
     */
    public function activar(Request $request, $id) // activar(): Activa el bloque identificado; se usa al volver a mostrarlo.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Aplica la misma autorización; el formato de la petición determina la respuesta de error.
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
            }
            return $res;
        }

        $bloque = BloqueHistoria::findOrFail($id); // Recupera el bloque y marca activo=true para volver a publicarlo.
        $bloque->update(['activo' => true]);

        if ($request->wantsJson()) { // El cliente puede recibir JSON para actualizar la pantalla sin recargarla.
            return response()->json([
                'success' => true,
                'message' => 'El bloque "' . $bloque->titulo . '" ha sido activado y publicado para los aprendices.',
            ]);
        }

        return redirect() // Para una navegación tradicional, vuelve a la lista con un mensaje temporal.
            ->route('controlecp.administrador.repositorio.historia.index')
            ->with('success', 'El bloque "' . $bloque->titulo . '" ha sido activado exitosamente.');
    }
}
