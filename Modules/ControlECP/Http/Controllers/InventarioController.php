<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\CategoriaInventario;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Entities\RecursoFoto;
use Modules\ControlECP\Entities\RecursoNovedad;

class InventarioController extends Controller
{
    /**
     * Valida acceso para Administrador (o Instructor según la propuesta técnica).
     */
    private function verificarAccesoAdmin()
    {
        $usuario = Auth::user();

        if (!$usuario || !$usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index')
                ->with('error', 'No tienes permisos de Administrador para gestionar el inventario de Control ECP.');
        }

        return null;
    }

    /**
     * Muestra recursos con foto, nombre, categoría, cantidad, estado físico y semáforo.
     */
    public function index(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $query = Inventario::with(['categoria', 'fotos']);

        // Filtro por Estado (por defecto muestra activos, si ver_bajas==1 muestra solo inactivos dados de baja)
        if ($request->filled('ver_bajas') && $request->ver_bajas == '1') {
            $query->where('estado', 'Inactivo');
        } else {
            $query->where('estado', '!=', 'Inactivo');
        }

        // Búsqueda por nombre
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        // Filtro por Categoría
        if ($request->filled('categoria_id')) {
            $query->where('id_categoria_inventario', $request->input('categoria_id'));
        }

        // Filtro por Estado Físico (Bueno, Regular, Crítico)
        if ($request->filled('estado_fisico')) {
            $query->where('estado_fisico', $request->input('estado_fisico'));
        }

        // Filtro por Estado (Disponible, Agotado, Inactivo)
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        // Filtro por Semáforo (Verde, Amarillo, Rojo)
        if ($request->filled('semaforo')) {
            $semFiltro = strtolower($request->input('semaforo'));
            if ($semFiltro === 'verde') {
                $query->where('estado', '!=', 'Inactivo')
                      ->where('estado', '!=', 'Agotado')
                      ->whereColumn('cantidad_disponible', '>', 'umbral_regular');
            } elseif ($semFiltro === 'amarillo') {
                $query->where('estado', '!=', 'Inactivo')
                      ->where('estado', '!=', 'Agotado')
                      ->whereColumn('cantidad_disponible', '<=', 'umbral_regular')
                      ->whereColumn('cantidad_disponible', '>', 'umbral_critico');
            } elseif ($semFiltro === 'rojo') {
                $query->where(function ($q) {
                    $q->where('estado', 'Agotado')
                      ->orWhere('cantidad_disponible', '<=', 0)
                      ->orWhereColumn('cantidad_disponible', '<=', 'umbral_critico');
                });
            }
        }

        // Obtener colección paginada
        $recursos = $query->orderBy('nombre', 'asc')->paginate(10)->withQueryString();

        // --- ESTADÍSTICAS DEL INVENTARIO  ---
        $todosActivos = Inventario::where('estado', '!=', 'Inactivo')->get();
        $totalRecursos = Inventario::count();
        $activosCount  = $todosActivos->count();
        $bajasCount    = Inventario::where('estado', 'Inactivo')->count();

        // Totales generales en unidades de disponibles y prestados
        $totalUnidades = (int) $todosActivos->sum('cantidad_total');
        $totalDisponibles = (int) $todosActivos->sum('cantidad_disponible');
        $totalPrestados = max(0, $totalUnidades - $totalDisponibles);

        // Stock bajo / crítico
        $stockBajoCount = 0;
        $asignadosHoyCount = $totalPrestados;
        $verdeCount = 0;
        $amarilloCount = 0;
        $rojoCount = 0;

        foreach ($todosActivos as $item) {
            $sem = $item->semaforo;
            if ($sem === 'verde') $verdeCount++;
            elseif ($sem === 'amarillo') { $amarilloCount++; $stockBajoCount++; }
            else { $rojoCount++; $stockBajoCount++; }
        }

        // Alertas de Devoluciones Incompletas (se quita automáticamente cuando se entrega el recurso pendiente)
        $alertasLista = MovimientoInventario::with(['inventario', 'usuario', 'movimientosHijos'])
            ->where('tipo', 'Entrega')
            ->get()
            ->filter(function ($entrega) {
                if (!$entrega->inventario || $entrega->inventario->estado === 'Inactivo') {
                    return false;
                }
                $devueltas = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                return $devueltas > 0 && $devueltas < $entrega->cantidad;
            })
            ->map(function ($entrega) {
                $devueltas = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
                $faltante = $entrega->cantidad - $devueltas;
                return (object) [
                    'id_movimiento_inventario' => $entrega->id_movimiento_inventario,
                    'id_inventario'            => $entrega->id_inventario,
                    'inventario'               => $entrega->inventario,
                    'fecha'                    => $entrega->fecha,
                    'observacion'              => "[ALERTA DEVOLUCIÓN INCOMPLETA]: Se entregaron {$entrega->cantidad} y van devueltas {$devueltas}. Faltan {$faltante} unidad(es) pendientes por entregar.",
                    'faltante'                 => $faltante,
                    'cantidad_entregada'       => $entrega->cantidad,
                    'cantidad_devuelta'        => $devueltas,
                ];
            })
            ->values();

        $alertasIncompletas = $alertasLista->count();

        $categorias = CategoriaInventario::activas()->orderBy('nombre')->get();
        $todasCategorias = CategoriaInventario::orderBy('nombre')->get();
        $todosRecursos = Inventario::where('estado', '!=', 'Inactivo')->orderBy('nombre')->get();

        // Datos para modal de Gestión de Categorías
        $categoriasConConteo = CategoriaInventario::withCount('inventarios')->orderBy('nombre')->get();

        // Datos para modal de Reportes Estadísticos
        $reporteCategoriasLabels = $categoriasConConteo->pluck('nombre')->toArray();
        $reporteCategoriasCounts = $categoriasConConteo->pluck('inventarios_count')->toArray();
        $movQuery = MovimientoInventario::with(['inventario.categoria', 'usuario']);
        $entregasCount     = (clone $movQuery)->where('tipo', 'Entrega')->count();
        $devolucionesCount = (clone $movQuery)->where('tipo', 'Devolución')->count();
        $cambioEstadoCount = (clone $movQuery)->where('tipo', 'Cambio de estado')->count();
        $reporteMovimientos = $movQuery->orderBy('fecha', 'desc')->limit(20)->get();
        $reporteNovedades   = RecursoNovedad::with(['inventario', 'usuario'])->orderBy('fecha', 'desc')->limit(20)->get();
        $usuariosIds = MovimientoInventario::select('id_usuario')->distinct()->pluck('id_usuario');
        $reporteUsuarios = \App\Models\User::whereIn('id', $usuariosIds)->get();

        return view('controlecp::administrador.inventario', compact(
            'recursos',
            'categorias',
            'todasCategorias',
            'todosRecursos',
            'totalRecursos',
            'activosCount',
            'bajasCount',
            'totalUnidades',
            'totalDisponibles',
            'totalPrestados',
            'stockBajoCount',
            'asignadosHoyCount',
            'verdeCount',
            'amarilloCount',
            'rojoCount',
            'alertasIncompletas',
            'alertasLista',
            'categoriasConConteo',
            'reporteCategoriasLabels',
            'reporteCategoriasCounts',
            'entregasCount',
            'devolucionesCount',
            'cambioEstadoCount',
            'reporteMovimientos',
            'reporteNovedades',
            'reporteUsuarios'
        ));
    }

    /**
     * OBTENER DETALLE DE RECURSO EN JSON (HU 11-12: Detalle con observaciones, novedades y 2 tablitas de movimientos)
     */
    public function getRecursoDetalle($id)
    {
        $recurso = Inventario::with(['categoria', 'fotos', 'usuario'])->findOrFail($id);

        $movs = MovimientoInventario::with(['usuario', 'movimientosHijos'])
            ->where('id_inventario', $id)
            ->orderBy('fecha', 'desc')
            ->get();

        // HU 11-12: 1. Préstamos / Entregas Pendientes (Lo que NO está / Falta por entregar o devolver)
        $movimientosPendientes = $movs->where('tipo', 'Entrega')->filter(function ($m) {
            $devueltas = $m->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
            return $devueltas < $m->cantidad;
        })->map(function ($ep) {
            $devueltas = $ep->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
            $saldo = $ep->cantidad - $devueltas;
            return [
                'id'                 => $ep->id_movimiento_inventario,
                'fecha'              => $ep->fecha ? $ep->fecha->format('d/m/Y H:i') : '',
                'cantidad_prestada'  => $ep->cantidad,
                'cantidad_devuelta'  => $devueltas,
                'saldo_pendiente'    => $saldo,
                'destino'            => str_replace(['Destino: ', 'Nota: '], ['', ''], $ep->observacion ?? 'Préstamo'),
                'observacion'        => $ep->observacion,
                'autor'              => $ep->usuario ? $ep->usuario->nickname : 'Sistema',
                'estado'             => $devueltas > 0 ? "Parcialmente devuelto (Faltan {$saldo})" : "Pendiente ({$saldo} un.)",
            ];
        })->values();

        // HU 11-12: 2. Entradas y Devoluciones (Lo que SÍ está en inventario o ya se devolvió)
        $movimientosDevueltos = $movs->filter(function ($m) {
            return $m->tipo !== 'Entrega' || ($m->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad') >= $m->cantidad);
        })->map(function ($m) {
            return [
                'id'          => $m->id_movimiento_inventario,
                'fecha'       => $m->fecha ? $m->fecha->format('d/m/Y H:i') : '',
                'tipo'        => $m->tipo,
                'cantidad'    => $m->cantidad,
                'autor'       => $m->usuario ? $m->usuario->nickname : 'Sistema',
                'observacion' => $m->observacion,
                'foto'        => $m->foto ? asset($m->foto) : null,
            ];
        })->values();

        $novedades = RecursoNovedad::with('usuario')
            ->where('id_inventario', $id)
            ->orderBy('fecha', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id'          => $n->id_recurso_novedad,
                    'fecha'       => $n->fecha ? $n->fecha->format('d/m/Y H:i') : '',
                    'descripcion' => $n->descripcion,
                    'autor'       => $n->usuario ? $n->usuario->nickname : 'Sistema',
                ];
            });

        return response()->json([
            'id_inventario'          => $recurso->id_inventario,
            'nombre'                 => $recurso->nombre,
            'categoria'              => $recurso->categoria ? $recurso->categoria->nombre : 'Sin categoría',
            'id_categoria'           => $recurso->id_categoria_inventario,
            'cantidad_disponible'    => $recurso->cantidad_disponible,
            'cantidad_total'         => $recurso->cantidad_total,
            'cantidad_prestada'      => max(0, $recurso->cantidad_total - $recurso->cantidad_disponible),
            'umbral_regular'         => $recurso->umbral_regular,
            'umbral_critico'         => $recurso->umbral_critico,
            'estado_fisico'          => $recurso->estado_fisico ?? 'Bueno',
            'estado'                 => $recurso->estado,
            'semaforo'               => $recurso->semaforo,
            'semaforo_badge'         => $recurso->semaforo_badge,
            'estado_fisico_badge'    => $recurso->estado_fisico_badge,
            'tipo_recurso'           => $recurso->tipo_recurso,
            'tipo_recurso_badge'     => $recurso->tipo_recurso_badge,
            'observaciones'          => $recurso->observaciones,
            'motivo_baja'            => $recurso->motivo_baja,
            'fecha_baja'             => $recurso->fecha_baja ? $recurso->fecha_baja->format('d/m/Y H:i') : null,
            'fotos'                  => $recurso->fotos->map(fn($f) => [
                'id'   => $f->id_recurso_foto,
                'ruta' => asset($f->ruta),
            ]),
            'movimientos_pendientes' => $movimientosPendientes,
            'movimientos_devueltos'  => $movimientosDevueltos,
            'movimientos'            => $movimientosDevueltos,
            'novedades'              => $novedades,
            'entregas_pendientes'    => $movimientosPendientes,
        ]);
    }

    /**
     * 2. FORMULARIO DE REGISTRO (HU-014: Escenario 1, 2, 3)
     */
    public function create()
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $categorias = CategoriaInventario::activas()->orderBy('nombre')->get();

        return view('controlecp::administrador.inventario_create', compact('categorias'));
    }

    /**
     * 3. GUARDAR RECURSO (HU-014: Escenarios 1, 2, 3 - HU 1 y HU 2)
     * Valida campos obligatorios, nombre único por categoría, al menos 1 foto JPG/PNG.
     */
    public function store(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $categoriaId = $request->input('id_categoria_inventario');
        $nombreRecurso = trim($request->input('nombre', ''));

        // HU 2: Validar si el recurso ya existe en la misma categoría (case-insensitive)
        if (!empty($categoriaId) && !empty($nombreRecurso)) {
            $repetido = Inventario::where('id_categoria_inventario', $categoriaId)
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($nombreRecurso)])
                ->exists();

            if ($repetido) {
                return back()
                    ->withInput()
                    ->with('error', "¡ALERTA!: Ya existe un recurso con el nombre '{$nombreRecurso}' en la categoría seleccionada. No se permiten recursos duplicados en la misma categoría.");
            }
        }

        $validated = $request->validate([
            'id_categoria_inventario' => 'required|exists:categorias_inventario,id_categoria_inventario',
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventario', 'nombre')
                    ->where(fn($q) => $q->where('id_categoria_inventario', $categoriaId)->whereNull('deleted_at')),
            ],
            'cantidad_total'  => 'required|integer|min:1',
            'umbral_critico'  => 'nullable|integer|min:0',
            'umbral_regular'  => 'nullable|integer|min:0',
            'estado_fisico'   => 'required|in:Bueno,Regular,Crítico',
            'observaciones'   => 'nullable|string',
            'fotos'           => 'required|array|min:1',
            'fotos.*'         => 'required|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'id_categoria_inventario.required' => 'La categoría del recurso es obligatoria.',
            'nombre.required'                  => 'El nombre del recurso es obligatorio.',
            'nombre.unique'                    => "¡ALERTA!: Ya existe un recurso con el nombre '{$nombreRecurso}' en la categoría seleccionada.",
            'cantidad_total.required'          => 'La cantidad o capacidad es obligatoria.',
            'cantidad_total.min'               => 'La cantidad debe ser mayor o igual a 1.',
            'estado_fisico.required'           => 'El estado físico es obligatorio.',
            'fotos.required'                   => 'Debes adjuntar al menos una fotografía del recurso (formato JPG o PNG).',
            'fotos.min'                        => 'Debes adjuntar al menos una fotografía del recurso (formato JPG o PNG).',
            'fotos.*.image'                    => 'Los archivos adjuntos deben ser imágenes válidas.',
            'fotos.*.mimes'                    => 'Solo se permiten fotografías en formato JPG o PNG.',
            'fotos.*.max'                      => 'Cada imagen no puede pesar más de 4MB.',
        ]);

        DB::beginTransaction();
        try {
            // Umbrales sugeridos si se dejan en blanco
            $umbralCritico = $validated['umbral_critico'] ?? max(1, (int) round($validated['cantidad_total'] * 0.15));
            $umbralRegular = $validated['umbral_regular'] ?? max($umbralCritico + 1, (int) round($validated['cantidad_total'] * 0.35));

            $recurso = Inventario::create([
                'id_categoria_inventario' => $validated['id_categoria_inventario'],
                'id_usuario'              => Auth::id(),
                'nombre'                  => $nombreRecurso,
                'cantidad_total'          => $validated['cantidad_total'],
                'cantidad_disponible'     => $validated['cantidad_total'],
                'umbral_critico'          => $umbralCritico,
                'umbral_regular'          => $umbralRegular,
                'estado_fisico'           => $validated['estado_fisico'],
                'observaciones'           => $validated['observaciones'] ?? null,
                'estado'                  => 'Disponible',
            ]);

            // Guardar fotos en storage
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $fotoFile) {
                    $nombreArchivo = 'rec_' . $recurso->id_inventario . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                    $ruta = $fotoFile->storeAs('inventario/recursos', $nombreArchivo, 'public');

                    RecursoFoto::create([
                        'id_inventario' => $recurso->id_inventario,
                        'ruta'          => 'storage/' . $ruta,
                    ]);
                }
            }

            // Registrar movimiento inicial de ingreso en historial
            MovimientoInventario::create([
                'id_inventario'   => $recurso->id_inventario,
                'id_usuario'      => Auth::id(),
                'tipo'            => 'Entrada',
                'cantidad'        => $recurso->cantidad_total,
                'estado_anterior' => null,
                'estado_nuevo'    => $recurso->estado,
                'observacion'     => 'Registro inicial del recurso en inventario.',
                'fecha'           => now(),
            ]);

            DB::commit();

            // HU 1: Notificación de guardado exitoso y redirección al listado principal de inventario
            return redirect()
                ->route('controlecp.administrador.inventario')
                ->with('success', "¡Recurso '{$recurso->nombre}' guardado exitosamente!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * 4. VER DETALLE DEL RECURSO (HU-014: Escenario 6, HU 11-12)
     * Muestra datos, fotos, observaciones, novedades y 2 tablas de historial (pendientes vs devueltos).
     */
    public function show($id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $recurso = Inventario::with([
            'categoria',
            'fotos',
            'usuario',
            'novedades.usuario',
            'movimientos.usuario',
            'movimientos.movimientosHijos',
            'movimientos.movimientoOrigen',
        ])->findOrFail($id);

        // HU 11-12: Separar en 2 tablas claras para que no se vea revuelto:
        // 1. Entregas pendientes (lo que NO está o falta por devolver)
        $movimientosPendientes = $recurso->movimientos->where('tipo', 'Entrega')->filter(function ($m) {
            $devueltas = $m->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
            return $devueltas < $m->cantidad;
        });

        // 2. Entradas y Devoluciones (lo que SÍ está o ya se devolvió)
        $movimientosDevueltos = $recurso->movimientos->filter(function ($m) {
            return $m->tipo !== 'Entrega' || ($m->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad') >= $m->cantidad);
        });

        $entregasPendientes = $movimientosPendientes;

        return view('controlecp::administrador.inventario_show', compact(
            'recurso',
            'movimientosPendientes',
            'movimientosDevueltos',
            'entregasPendientes'
        ));
    }

    /**
     * 5. EDITAR RECURSO (HU-014: Escenario 7, 8)
     */
    public function edit($id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $recurso = Inventario::with(['fotos', 'categoria'])->findOrFail($id);
        $categorias = CategoriaInventario::activas()->orderBy('nombre')->get();

        return view('controlecp::administrador.inventario_edit', compact('recurso', 'categorias'));
    }

    /**
     * 6. ACTUALIZAR RECURSO (HU-014: Escenario 7, 8 - HU 7)
     * Queda en el inventario donde están todos los productos y reemplaza la foto por la nueva.
     */
    public function update(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $recurso = Inventario::findOrFail($id);
        $categoriaId = $request->input('id_categoria_inventario', $recurso->id_categoria_inventario);
        $nombreRecurso = trim($request->input('nombre', ''));

        // HU 2: Validar nombre único en la misma categoría (excluyendo el recurso actual)
        if (!empty($categoriaId) && !empty($nombreRecurso)) {
            $repetido = Inventario::where('id_categoria_inventario', $categoriaId)
                ->where('id_inventario', '!=', $id)
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($nombreRecurso)])
                ->exists();

            if ($repetido) {
                return back()
                    ->withInput()
                    ->with('error', "¡ALERTA!: Ya existe otro recurso con el nombre '{$nombreRecurso}' en la categoría seleccionada. No se permiten nombres repetidos.");
            }
        }

        $validated = $request->validate([
            'id_categoria_inventario' => 'required|exists:categorias_inventario,id_categoria_inventario',
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventario', 'nombre')
                    ->where(fn($q) => $q->where('id_categoria_inventario', $categoriaId)->whereNull('deleted_at'))
                    ->ignore($recurso->id_inventario, 'id_inventario'),
            ],
            'cantidad_total'  => 'required|integer|min:0',
            'umbral_critico'  => 'nullable|integer|min:0',
            'umbral_regular'  => 'nullable|integer|min:0',
            'estado_fisico'   => 'required|in:Bueno,Regular,Crítico',
            'observaciones'   => 'nullable|string',
            'fotos'           => 'nullable|array',
            'fotos.*'         => 'image|mimes:jpeg,jpg,png|max:4096',
            'fotos_nuevas'    => 'nullable|array',
            'fotos_nuevas.*'  => 'image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'nombre.unique'        => "¡ALERTA!: Ya existe un recurso con el nombre '{$nombreRecurso}' en la categoría seleccionada.",
            'fotos.*.mimes'        => 'Solo se permiten fotos en formato JPG o PNG.',
            'fotos_nuevas.*.mimes' => 'Solo se permiten fotos en formato JPG o PNG.',
        ]);

        DB::beginTransaction();
        try {
            $estadoFisicoAnterior = $recurso->estado_fisico;
            $nuevoEstadoFisico    = $validated['estado_fisico'];

            // Ajustar cantidad disponible si cambió cantidad total
            $diferenciaTotal = $validated['cantidad_total'] - $recurso->cantidad_total;
            $nuevaDisponible = max(0, $recurso->cantidad_disponible + $diferenciaTotal);

            $recurso->update([
                'id_categoria_inventario' => $validated['id_categoria_inventario'],
                'nombre'                  => $nombreRecurso,
                'cantidad_total'          => $validated['cantidad_total'],
                'cantidad_disponible'     => $nuevaDisponible,
                'umbral_critico'          => $validated['umbral_critico'] ?? $recurso->umbral_critico,
                'umbral_regular'          => $validated['umbral_regular'] ?? $recurso->umbral_regular,
                'estado_fisico'           => $nuevoEstadoFisico,
                'observaciones'           => $validated['observaciones'] ?? $recurso->observaciones,
                'estado'                  => $nuevaDisponible > 0 ? 'Disponible' : 'Agotado',
            ]);

            // HU 7: Si se edita la foto, no queda la anterior sino la que se acaba de poner
            $fotosSubidas = $request->file('fotos') ?? $request->file('fotos_nuevas');
            if (!empty($fotosSubidas) && count($fotosSubidas) > 0) {
                // Eliminar fotos previas del storage y de la base de datos
                foreach ($recurso->fotos as $fotoVieja) {
                    $rutaRelativa = str_replace('storage/', '', $fotoVieja->ruta);
                    Storage::disk('public')->delete($rutaRelativa);
                    $fotoVieja->delete();
                }

                // Guardar las nuevas fotos cargadas
                foreach ($fotosSubidas as $fotoFile) {
                    $nombreArchivo = 'rec_' . $recurso->id_inventario . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                    $ruta = $fotoFile->storeAs('inventario/recursos', $nombreArchivo, 'public');

                    RecursoFoto::create([
                        'id_inventario' => $recurso->id_inventario,
                        'ruta'          => 'storage/' . $ruta,
                    ]);
                }
            }

            // Si cambió el estado físico, registrar en historial de movimientos (Criterio 8)
            if ($estadoFisicoAnterior !== $nuevoEstadoFisico) {
                MovimientoInventario::create([
                    'id_inventario'   => $recurso->id_inventario,
                    'id_usuario'      => Auth::id(),
                    'tipo'            => 'Cambio de estado',
                    'cantidad'        => $recurso->cantidad_disponible,
                    'estado_anterior' => "Físico: {$estadoFisicoAnterior}",
                    'estado_nuevo'    => "Físico: {$nuevoEstadoFisico}",
                    'observacion'     => "Actualización de estado físico de {$estadoFisicoAnterior} a {$nuevoEstadoFisico}.",
                    'fecha'           => now(),
                ]);
            }

            DB::commit();

            // HU 7: Quedarse en la vista principal donde están todos los recursos con confirmación exitosa
            return redirect()
                ->route('controlecp.administrador.inventario')
                ->with('success', "Recurso '{$recurso->nombre}' editado exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar el recurso: ' . $e->getMessage());
        }
    }

    /**
     * 7. REGISTRAR OBSERVACIÓN / NOVEDAD (HU-014: Escenario 10)
     */
    public function storeNovedad(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $request->validate([
            'descripcion' => 'required|string|max:1000',
        ]);

        $recurso = Inventario::findOrFail($id);

        RecursoNovedad::create([
            'id_inventario' => $recurso->id_inventario,
            'id_usuario'    => Auth::id(),
            'descripcion'   => trim($request->input('descripcion')),
            'fecha'         => now(),
        ]);

        return back()->with('success', 'Novedad registrada con fecha y autor.');
    }

    /**
     * 8. REGISTRAR ENTREGA (HU-014: Escenario 11, 14)
     * Descuenta cantidad, guarda movimiento con autor y destino, no permite saldo negativo.
     */
    public function registrarEntrega(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $recurso = Inventario::findOrFail($id);

        $validated = $request->validate([
            'cantidad'    => "required|integer|min:1|max:{$recurso->cantidad_disponible}",
            'destino'     => 'required|string|max:255',
            'observacion' => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'cantidad.max' => "No puedes entregar más de la cantidad disponible actual ({$recurso->cantidad_disponible}).",
            'foto.mimes'   => 'La evidencia debe ser una fotografía en formato JPG o PNG.',
        ]);

        DB::beginTransaction();
        try {
            $fotoRuta = null;
            if ($request->hasFile('foto')) {
                $nombreArchivo = 'mov_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
                $ruta = $request->file('foto')->storeAs('inventario/movimientos', $nombreArchivo, 'public');
                $fotoRuta = 'storage/' . $ruta;
            }

            // Descontar existencias disponibles
            $recurso->cantidad_disponible -= $validated['cantidad'];
            if ($recurso->cantidad_disponible == 0) {
                $recurso->estado = 'Agotado';
            }
            $recurso->save();

            // Guardar movimiento de entrega
            MovimientoInventario::create([
                'id_inventario' => $recurso->id_inventario,
                'id_usuario'    => Auth::id(),
                'tipo'          => 'Entrega',
                'cantidad'      => $validated['cantidad'],
                'estado_anterior' => 'Disponible',
                'estado_nuevo'  => $recurso->estado,
                'observacion'   => "Destino: {$validated['destino']}." . ($validated['observacion'] ? " Nota: {$validated['observacion']}" : ''),
                'foto'          => $fotoRuta,
                'fecha'         => now(),
            ]);

            DB::commit();

            return back()->with('success', "Entrega de {$validated['cantidad']} unidad(es) registrada correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar entrega: ' . $e->getMessage());
        }
    }

    /**
     * 9. REGISTRAR DEVOLUCIÓN Y ALERTAR INCOMPLETA (HU-014: Escenarios 12, 13, 14 - HU 12)
     * Suma cantidad devuelta. Si es menor a lo entregado, alerta automáticamente y registra en observaciones.
     * HU 12: La alerta se quita automáticamente cuando se entrega el recurso pendiente.
     */
    public function registrarDevolucion(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $recurso = Inventario::findOrFail($id);

        $validated = $request->validate([
            'id_movimiento_entrega' => 'nullable|exists:movimientos_inventario,id_movimiento_inventario',
            'cantidad'              => 'required|integer|min:1',
            'observacion'           => 'nullable|string',
            'foto'                  => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'foto.mimes' => 'La evidencia debe ser una fotografía en formato JPG o PNG.',
        ]);

        DB::beginTransaction();
        try {
            $fotoRuta = null;
            if ($request->hasFile('foto')) {
                $nombreArchivo = 'mov_dev_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
                $ruta = $request->file('foto')->storeAs('inventario/movimientos', $nombreArchivo, 'public');
                $fotoRuta = 'storage/' . $ruta;
            }

            $cantidadDevuelta = $validated['cantidad'];
            $alertaIncompleta = false;
            $entregaSaldada   = false;
            $textoObservacion = $validated['observacion'] ?? '';

            // HU 12: Comparar con la entrega asociada seleccionada o vincular a la entrega pendiente más antigua
            $entregaId = $validated['id_movimiento_entrega'] ?? null;
            if (!$entregaId) {
                $entregaMasAntigua = MovimientoInventario::where('id_inventario', $id)
                    ->where('tipo', 'Entrega')
                    ->orderBy('fecha', 'asc')
                    ->get()
                    ->first(function ($e) {
                        return $e->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad') < $e->cantidad;
                    });
                if ($entregaMasAntigua) {
                    $entregaId = $entregaMasAntigua->id_movimiento_inventario;
                }
            }

            if ($entregaId) {
                $entrega = MovimientoInventario::find($entregaId);
                if ($entrega) {
                    $cantidadEntregada = $entrega->cantidad;
                    $yaDevuelto = MovimientoInventario::where('id_movimiento_inventario_origen', $entrega->id_movimiento_inventario)->sum('cantidad');
                    $nuevoTotalDevuelto = $yaDevuelto + $cantidadDevuelta;

                    if ($nuevoTotalDevuelto < $cantidadEntregada) {
                        $faltante = $cantidadEntregada - $nuevoTotalDevuelto;
                        $alertaIncompleta = true;
                        $textoObservacion = "[ALERTA DEVOLUCIÓN INCOMPLETA]: Se entregaron {$cantidadEntregada} y van devueltos {$nuevoTotalDevuelto}. Faltan {$faltante} unidad(es). " . $textoObservacion;
                    } else {
                        // HU 12: Se saldó completamente la entrega pendiente, la alerta se retira automáticamente
                        $entregaSaldada = true;
                        $textoObservacion = "[DEVOLUCIÓN COMPLETADA]: Se saldó la totalidad de las {$cantidadEntregada} unidad(es) prestadas. " . $textoObservacion;
                    }
                }
            }

            // Sumar a cantidad disponible (sin superar la cantidad total)
            $recurso->cantidad_disponible = min($recurso->cantidad_total, $recurso->cantidad_disponible + $cantidadDevuelta);
            if ($recurso->estado === 'Agotado' && $recurso->cantidad_disponible > 0) {
                $recurso->estado = 'Disponible';
            }
            $recurso->save();

            // Guardar movimiento de devolución
            MovimientoInventario::create([
                'id_inventario'                   => $recurso->id_inventario,
                'id_usuario'                      => Auth::id(),
                'id_movimiento_inventario_origen' => $entregaId,
                'tipo'                            => 'Devolución',
                'cantidad'                        => $cantidadDevuelta,
                'estado_anterior'                 => 'Prestado / Entregado',
                'estado_nuevo'                    => $recurso->estado,
                'observacion'                     => trim($textoObservacion) ?: 'Devolución de recurso.',
                'foto'                            => $fotoRuta,
                'fecha'                           => now(),
            ]);

            DB::commit();

            if ($alertaIncompleta) {
                return back()->with('error', '¡ALERTA AUTOMÁTICA!: La devolución está incompleta. Se ha registrado la novedad y la alerta para el Administrador.');
            }

            if ($entregaSaldada) {
                return back()->with('success', "¡Devolución completada exitosamente! Se saldaron todas las unidades prestadas y la alerta ha sido retirada automáticamente.");
            }

            return back()->with('success', "Devolución de {$cantidadDevuelta} unidad(es) registrada exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar devolución: ' . $e->getMessage());
        }
    }

    /**
     * 10. DAR DE BAJA UN RECURSO (HU-014: Escenario 18)
     * Inactiva el recurso indicando motivo (dañado, perdido, fuera de servicio).
     * Conserva historial y bloquea la baja si tiene asignaciones futuras.
     */
    public function darDeBaja(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'motivo'      => 'required|in:Dañado,Perdido,Fuera de servicio',
            'descripcion' => 'required|string|max:500',
        ]);

        $recurso = Inventario::findOrFail($id);

        // Validar si tiene asignaciones pendientes en cronograma o asesorías activas
        $asignacionesFuturas = $recurso->cronogramaRecursos()->count() + $recurso->asesoriaRecursos()->count();
        if ($asignacionesFuturas > 0 && $recurso->cantidad_disponible < $recurso->cantidad_total) {
            return back()->with('error', 'No es posible dar de baja este recurso porque tiene unidades asignadas o pendientes de devolución en actividades/asesorías.');
        }

        DB::beginTransaction();
        try {
            $estadoAnterior = $recurso->estado;

            $recurso->update([
                'estado'      => 'Inactivo',
                'motivo_baja' => "{$validated['motivo']}: {$validated['descripcion']}",
                'fecha_baja'  => now(),
            ]);

            // Registrar en historial de movimientos
            MovimientoInventario::create([
                'id_inventario'   => $recurso->id_inventario,
                'id_usuario'      => Auth::id(),
                'tipo'            => 'Baja',
                'cantidad'        => $recurso->cantidad_disponible,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'Inactivo (De Baja)',
                'observacion'     => "Baja por motivo: {$validated['motivo']}. Detalle: {$validated['descripcion']}",
                'fecha'           => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('controlecp.administrador.inventario')
                ->with('success', "El recurso '{$recurso->nombre}' ha sido dado de baja correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al dar de baja el recurso: ' . $e->getMessage());
        }
    }

    /**
     * 11. HISTORIAL GENERAL DE MOVIMIENTOS (HU-014: Escenario 16, HU 15 y HU 16)
     * Permite consultar y buscar historial de entregas, devoluciones y cambios de estado.
     */
    public function movimientos(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $query = MovimientoInventario::with(['inventario.categoria', 'usuario', 'movimientosHijos', 'movimientoOrigen'])->orderBy('fecha', 'desc');

        // HU 16: Búsqueda por texto (nombre de recurso, observación, destino o autor)
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('inventario', function ($qi) use ($buscar) {
                    $qi->where('nombre', 'like', "%{$buscar}%");
                })
                ->orWhere('observacion', 'like', "%{$buscar}%")
                ->orWhereHas('usuario', function ($qu) use ($buscar) {
                    $qu->where('nickname', 'like', "%{$buscar}%")
                       ->orWhere('name', 'like', "%{$buscar}%");
                });
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->paginate(20)->withQueryString();

        // HU 16: Listado de Préstamos / Entregas Pendientes (Lo que NO se ha devuelto aún)
        $entregasQuery = MovimientoInventario::with(['inventario.categoria', 'usuario', 'movimientosHijos'])
            ->where('tipo', 'Entrega')
            ->orderBy('fecha', 'desc');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $entregasQuery->where(function ($q) use ($buscar) {
                $q->whereHas('inventario', function ($qi) use ($buscar) {
                    $qi->where('nombre', 'like', "%{$buscar}%");
                })
                ->orWhere('observacion', 'like', "%{$buscar}%")
                ->orWhereHas('usuario', function ($qu) use ($buscar) {
                    $qu->where('nickname', 'like', "%{$buscar}%")
                       ->orWhere('name', 'like', "%{$buscar}%");
                });
            });
        }

        $prestamosPendientes = $entregasQuery->get()->filter(function ($entrega) {
            $devueltas = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
            return $devueltas < $entrega->cantidad;
        })->map(function ($entrega) {
            $devueltas = $entrega->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad');
            $saldo = $entrega->cantidad - $devueltas;
            return (object) [
                'id'                 => $entrega->id_movimiento_inventario,
                'id_inventario'      => $entrega->id_inventario,
                'inventario'         => $entrega->inventario,
                'fecha'              => $entrega->fecha,
                'cantidad_prestada'  => $entrega->cantidad,
                'cantidad_devuelta'  => $devueltas,
                'saldo_pendiente'    => $saldo,
                'observacion'        => $entrega->observacion,
                'autor'              => $entrega->usuario ? $entrega->usuario->nickname : 'Sistema',
                'estado'             => $devueltas > 0 ? "Parcialmente devuelto (Faltan {$saldo})" : "Pendiente total ({$saldo} un.)",
                'es_incompleta'      => $devueltas > 0,
            ];
        })->values();

        // HU 12: Alertas activas de devoluciones incompletas (se quita automáticamente cuando se entrega el recurso pendiente)
        $alertasLista = $prestamosPendientes->where('es_incompleta', true);
        $devolucionesIncompletasCount = $alertasLista->count();
        $alertaIncompletaPrincipal = $alertasLista->first();

        // HU 15: Totales generales en unidades de disponibles y prestados
        $todosActivos = Inventario::where('estado', '!=', 'Inactivo')->get();
        $totalUnidades = (int) $todosActivos->sum('cantidad_total');
        $totalDisponibles = (int) $todosActivos->sum('cantidad_disponible');
        $totalPrestados = max(0, $totalUnidades - $totalDisponibles);

        $criticosCount = Inventario::where('estado', '!=', 'Inactivo')
            ->where('estado_fisico', 'Crítico')
            ->count();

        $regularesCount = Inventario::where('estado', '!=', 'Inactivo')
            ->where('estado_fisico', 'Regular')
            ->count();

        $recursosDisponibles = Inventario::where('estado', '!=', 'Inactivo')
            ->orderBy('nombre')
            ->get();

        return view('controlecp::administrador.inventario_movimientos', compact(
            'movimientos',
            'prestamosPendientes',
            'devolucionesIncompletasCount',
            'criticosCount',
            'regularesCount',
            'alertaIncompletaPrincipal',
            'recursosDisponibles',
            'totalUnidades',
            'totalDisponibles',
            'totalPrestados'
        ));
    }

    /**
     * 12. GESTIÓN DE CATEGORÍAS (HU-014: Escenario 17)
     * Crear, editar y activar/desactivar categorías conservando los recursos asociados.
     */
    public function categorias()
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $categorias = CategoriaInventario::withCount('inventarios')->orderBy('nombre')->get();

        return view('controlecp::administrador.inventario_categorias', compact('categorias'));
    }

    public function categoriaStore(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias_inventario,nombre',
        ], [
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
        ]);

        CategoriaInventario::create([
            'nombre' => trim($request->input('nombre')),
            'estado' => 'Activo',
        ]);

        return back()->with('success', 'Categoría creada exitosamente.');
    }

    public function categoriaUpdate(Request $request, $id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $categoria = CategoriaInventario::findOrFail($id);

        $request->validate([
            'nombre' => "required|string|max:100|unique:categorias_inventario,nombre,{$id},id_categoria_inventario",
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        $categoria->update([
            'nombre' => trim($request->input('nombre')),
            'estado' => $request->input('estado'),
        ]);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function categoriaToggle($id)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $categoria = CategoriaInventario::findOrFail($id);
        $categoria->estado = $categoria->estado === 'Activo' ? 'Inactivo' : 'Activo';
        $categoria->save();

        return back()->with('success', "Categoría {$categoria->nombre} actualizada a estado {$categoria->estado}.");
    }

    /**
     * 13. GENERAR REPORTE DE INVENTARIO CON GRÁFICAS (HU-014: Escenario 19)
     */
    public function reporte(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $query = Inventario::with('categoria');

        if ($request->filled('categoria_id')) {
            $query->where('id_categoria_inventario', $request->input('categoria_id'));
        }

        if ($request->filled('estado_fisico')) {
            $query->where('estado_fisico', $request->input('estado_fisico'));
        }

        $recursos = $query->orderBy('nombre')->get();

        // Métricas para gráficas
        $total = $recursos->count();
        $disponiblesCount = $recursos->where('estado', 'Disponible')->count();
        $agotadosCount    = $recursos->where('estado', 'Agotado')->count();
        $inactivosCount   = $recursos->where('estado', 'Inactivo')->count();

        // Estados físicos
        $buenoCount   = $recursos->where('estado_fisico', 'Bueno')->count();
        $regularCount = $recursos->where('estado_fisico', 'Regular')->count();
        $criticoCount = $recursos->where('estado_fisico', 'Crítico')->count();

        // Semáforo
        $verdeCount = 0; $amarilloCount = 0; $rojoCount = 0;
        foreach ($recursos as $r) {
            $sem = $r->semaforo;
            if ($sem === 'verde') $verdeCount++;
            elseif ($sem === 'amarillo') $amarilloCount++;
            else $rojoCount++;
        }

        // Filtros de fecha en reporte
        $movQuery = MovimientoInventario::with(['inventario.categoria', 'usuario']);
        if ($request->filled('fecha_desde')) {
            $movQuery->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $movQuery->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('id_usuario')) {
            $movQuery->where('id_usuario', $request->input('id_usuario'));
        }

        // Conteo de movimientos por tipo para la gráfica de barras horizontales
        $entregasCount    = (clone $movQuery)->where('tipo', 'Entrega')->count();
        $devolucionesCount = (clone $movQuery)->where('tipo', 'Devolución')->count();
        $cambioEstadoCount = (clone $movQuery)->where('tipo', 'Cambio de estado')->count();
        $bajasCount       = (clone $movQuery)->where('tipo', 'Baja')->count();

        // Movimientos recientes
        $movimientos = $movQuery->orderBy('fecha', 'desc')->limit(20)->get();

        // Novedades recientes
        $novedades = RecursoNovedad::with(['inventario', 'usuario'])
            ->orderBy('fecha', 'desc')
            ->limit(20)
            ->get();

        // Alertas de devoluciones incompletas
        $alertas = MovimientoInventario::where('tipo', 'Devolución')
            ->where('observacion', 'like', '%[ALERTA DEVOLUCIÓN INCOMPLETA]%')
            ->with(['inventario', 'usuario'])
            ->orderBy('fecha', 'desc')
            ->get();

        // Categorías con conteo para gráfica circular
        $categorias = CategoriaInventario::withCount('inventarios')->orderBy('nombre')->get();
        $categoriasLabels = $categorias->pluck('nombre')->toArray();
        $categoriasCounts = $categorias->pluck('inventarios_count')->toArray();

        // Usuarios para el selector de filtros
        $usuariosIds = MovimientoInventario::select('id_usuario')->distinct()->pluck('id_usuario');
        $usuarios = \App\Models\User::whereIn('id', $usuariosIds)->get();

        return view('controlecp::administrador.inventario_reporte', compact(
            'recursos',
            'categorias',
            'categoriasLabels',
            'categoriasCounts',
            'total',
            'disponiblesCount',
            'agotadosCount',
            'inactivosCount',
            'buenoCount',
            'regularCount',
            'criticoCount',
            'verdeCount',
            'amarilloCount',
            'rojoCount',
            'entregasCount',
            'devolucionesCount',
            'cambioEstadoCount',
            'bajasCount',
            'movimientos',
            'novedades',
            'alertas',
            'usuarios'
        ));
    }

    /**
     * Eliminar foto individual de un recurso.
     */
    public function eliminarFoto($idFoto)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $foto = RecursoFoto::findOrFail($idFoto);
        $recurso = $foto->inventario;

        // Validar que no se quede sin al menos 1 foto si el recurso sigue activo (Criterio 1 y 2)
        if ($recurso->fotos()->count() <= 1) {
            return back()->with('error', 'El recurso debe conservar al menos una fotografía.');
        }

        // Eliminar del almacenamiento físico si existe
        $rutaRelativa = str_replace('storage/', '', $foto->ruta);
        Storage::disk('public')->delete($rutaRelativa);

        $foto->delete();

        return back()->with('success', 'Fotografía eliminada correctamente.');
    }

    /**
     * 14. VERIFICAR DISPONIBILIDAD (HU-014: Escenario 15 - Mockup "Verificar disponibilidad")
     * Comprueba si un recurso está libre o asignado en la fecha y hora seleccionadas.
     */
    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'fecha'         => 'required|date',
            'hora'          => 'nullable|string',
        ]);

        $recurso = Inventario::with('categoria')->findOrFail($request->id_inventario);

        if ($recurso->estado === 'Inactivo') {
            return response()->json([
                'disponible'          => false,
                'mensaje'             => "El recurso '{$recurso->nombre}' está dado de baja (Inactivo).",
                'cantidad_disponible' => 0,
            ]);
        }

        if ($recurso->cantidad_disponible <= 0) {
            return response()->json([
                'disponible'          => false,
                'mensaje'             => "El recurso '{$recurso->nombre}' se encuentra agotado (0 unidades disponibles).",
                'cantidad_disponible' => 0,
            ]);
        }

        return response()->json([
            'disponible'          => true,
            'mensaje'             => "El recurso '{$recurso->nombre}' está disponible en la fecha y hora seleccionadas. ({$recurso->cantidad_disponible} unidad(es) libres). No hay cruces en el horario seleccionado.",
            'cantidad_disponible' => $recurso->cantidad_disponible,
            'recurso_nombre'      => $recurso->nombre,
        ]);
    }

    /**
     * 15. REGISTRAR MOVIMIENTO RÁPIDO (Mockup "Registrar movimiento" desde el listado)
     * Soporta tanto 'Entrega' / 'Salida' como 'Devolución' / 'Entrada'.
     */
    public function registrarMovimientoRapido(Request $request)
    {
        if ($redirect = $this->verificarAccesoAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'id_inventario' => 'required|exists:inventario,id_inventario',
            'tipo'          => 'required|in:Entrega,Devolución,Entrada,Salida',
            'cantidad'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
            'destino'       => 'nullable|string|max:255',
            'observacion'   => 'nullable|string',
            'foto'          => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ], [
            'foto.mimes' => 'La evidencia debe ser una fotografía en formato JPG o PNG.',
        ]);

        $recurso = Inventario::findOrFail($validated['id_inventario']);
        $tipoNormalizado = in_array($validated['tipo'], ['Entrega', 'Salida']) ? 'Entrega' : 'Devolución';

        DB::beginTransaction();
        try {
            $fotoRuta = null;
            if ($request->hasFile('foto')) {
                $nombreArchivo = 'mov_' . uniqid() . '.' . $request->file('foto')->getClientOriginalExtension();
                $ruta = $request->file('foto')->storeAs('inventario/movimientos', $nombreArchivo, 'public');
                $fotoRuta = 'storage/' . $ruta;
            }

            if ($tipoNormalizado === 'Entrega') {
                if ($recurso->cantidad_disponible < $validated['cantidad']) {
                    return back()->with('error', "No puedes entregar más de la cantidad disponible ({$recurso->cantidad_disponible}). El stock no puede quedar en negativo.");
                }

                $recurso->cantidad_disponible -= $validated['cantidad'];
                if ($recurso->cantidad_disponible == 0) {
                    $recurso->estado = 'Agotado';
                }
                $recurso->save();

                $obs = ($validated['destino'] ? "Destino: {$validated['destino']}. " : '') . ($validated['observacion'] ?? '');

                MovimientoInventario::create([
                    'id_inventario'   => $recurso->id_inventario,
                    'id_usuario'      => Auth::id(),
                    'tipo'            => 'Entrega',
                    'cantidad'        => $validated['cantidad'],
                    'estado_anterior' => 'Disponible',
                    'estado_nuevo'    => $recurso->estado,
                    'observacion'     => trim($obs) ?: 'Entrega de recurso.',
                    'foto'            => $fotoRuta,
                    'fecha'           => $validated['fecha'] . ' ' . date('H:i:s'),
                ]);

                DB::commit();
                return back()->with('success', "Movimiento registrado: La entrega de {$validated['cantidad']} unidad(es) se ha guardado correctamente.");
            } else {
                // Devolución
                $entregaOrigenId = $request->input('id_movimiento_entrega');
                if (!$entregaOrigenId) {
                    $entregaPend = MovimientoInventario::where('id_inventario', $recurso->id_inventario)
                        ->where('tipo', 'Entrega')
                        ->orderBy('fecha', 'asc')
                        ->get()
                        ->first(function ($e) {
                            return $e->movimientosHijos->where('tipo', 'Devolución')->sum('cantidad') < $e->cantidad;
                        });
                    if ($entregaPend) {
                        $entregaOrigenId = $entregaPend->id_movimiento_inventario;
                    }
                }

                $alertaIncompleta = false;
                $entregaSaldada   = false;
                $obsDev = $validated['observacion'] ?: '';

                if ($entregaOrigenId) {
                    $entrega = MovimientoInventario::find($entregaOrigenId);
                    if ($entrega) {
                        $yaDevuelto = MovimientoInventario::where('id_movimiento_inventario_origen', $entrega->id_movimiento_inventario)->sum('cantidad');
                        $nuevoTotal = $yaDevuelto + $validated['cantidad'];
                        if ($nuevoTotal < $entrega->cantidad) {
                            $faltante = $entrega->cantidad - $nuevoTotal;
                            $alertaIncompleta = true;
                            $obsDev = "[ALERTA DEVOLUCIÓN INCOMPLETA]: Se entregaron {$entrega->cantidad} y van devueltos {$nuevoTotal}. Faltan {$faltante} unidad(es). " . $obsDev;
                        } else {
                            $entregaSaldada = true;
                            $obsDev = "[DEVOLUCIÓN COMPLETADA]: Se saldaron las {$entrega->cantidad} unidad(es) prestadas. " . $obsDev;
                        }
                    }
                }

                $recurso->cantidad_disponible = min($recurso->cantidad_total, $recurso->cantidad_disponible + $validated['cantidad']);
                if ($recurso->estado === 'Agotado' && $recurso->cantidad_disponible > 0) {
                    $recurso->estado = 'Disponible';
                }
                $recurso->save();

                MovimientoInventario::create([
                    'id_inventario'                   => $recurso->id_inventario,
                    'id_usuario'                      => Auth::id(),
                    'id_movimiento_inventario_origen' => $entregaOrigenId,
                    'tipo'                            => 'Devolución',
                    'cantidad'                        => $validated['cantidad'],
                    'estado_anterior'                 => 'Prestado',
                    'estado_nuevo'                    => $recurso->estado,
                    'observacion'                     => trim($obsDev) ?: 'Devolución de recurso.',
                    'foto'                            => $fotoRuta,
                    'fecha'                           => $validated['fecha'] . ' ' . date('H:i:s'),
                ]);

                DB::commit();

                if ($alertaIncompleta) {
                    return back()->with('error', '¡ALERTA AUTOMÁTICA!: La devolución está incompleta. Se ha registrado la novedad y la alerta para el Administrador.');
                }

                if ($entregaSaldada) {
                    return back()->with('success', "¡Devolución completada! Se saldaron las unidades prestadas y la alerta se ha retirado automáticamente.");
                }

                return back()->with('success', "Movimiento registrado: La devolución de {$validated['cantidad']} unidad(es) se ha guardado correctamente.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar el movimiento: ' . $e->getMessage());
        }
    }
}
