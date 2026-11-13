<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReporteController extends Controller
{
    private const TIPOS = [
        'asistencias' => 'Asistencias',
        'encuestas' => 'Encuestas',
        'asesorias' => 'Asesorías',
        'pqrs' => 'PQRS',
        'certificacion' => 'Certificación',
        'inventario' => 'Inventario',
        'poe' => 'POE',
    ];

    public function index(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) {
            return /* Envï¿½a al usuario a la ruta indicada despuï¿½s de terminar la acciï¿½n. */ redirect()->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a los reportes.');
        }

        $tipo = $request->input('tipo');
        if ($tipo === null || $tipo === '') {
            return view('controlecp::administrador.reportes', [
                'tipos' => self::TIPOS,
                'tipo' => null,
            ]);
        }

        if (!array_key_exists($tipo, self::TIPOS)) {
            $tipo = 'asistencias';
        }

        $responsables = $this->usuariosResponsables();
        $request->validate([
            'usuario_id' => ['nullable', 'integer', Rule::in($responsables->pluck('id')->all())],
        ]);

        $errores = [];
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')
            && $request->fecha_desde > $request->fecha_hasta) {
            $errores[] = 'La fecha inicial no puede ser mayor que la fecha final.';
        }

        $resultado = $errores ? $this->resultadoVacio('Corrige el rango de fechas para generar el reporte.') : $this->generar($tipo, $request);

        return /* Muestra la pantalla solicitada con los datos preparados. */ view('controlecp::administrador.reportes', [
            'tipos' => self::TIPOS,
            'tipo' => $tipo,
            'resultado' => $resultado,
            'usuarios' => $responsables,
            'errores' => $errores,
        ]);
    }

    public function exportarPdf(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasRole('controlecp.admin')) {
            return redirect()->route('controlecp.index')
                ->with('error', 'No tienes permisos para descargar reportes.');
        }

        $responsables = $this->usuariosResponsables();
        $filtros = $request->validate([
            'tipo' => ['required', 'string', 'in:' . implode(',', array_keys(self::TIPOS))],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'usuario_id' => ['nullable', 'integer', Rule::in($responsables->pluck('id')->all())],
            'estado' => ['nullable', 'string', 'max:100'],
        ]);
        $tipo = $filtros['tipo'];
        $resultado = $this->generar($tipo, $request);
        $rutaLogo = public_path('general/assets/img/logo-ecp.png');
        if (!is_readable($rutaLogo)) {
            throw new \RuntimeException('No se pudo leer el logo de Control ECP para generar el PDF.');
        }

        $pdf = Pdf::loadView('controlecp::administrador.reportes_pdf', [
            'tipo' => $tipo,
            'nombreReporte' => self::TIPOS[$tipo],
            'color' => $this->colores()[$tipo],
            'logo' => $rutaLogo,
            'resultado' => $resultado,
            'filtros' => $filtros,
            'responsable' => $filtros['usuario_id'] ?? null
                ? $responsables->firstWhere('id', $filtros['usuario_id'])
                : null,
            'generadoPor' => $usuario->nickname ?? $usuario->name,
            'generadoEn' => now()->format('d/m/Y H:i'),
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('reporte_' . Str::slug($tipo) . '_' . now()->format('Ymd_His') . '.pdf');
    }

    private function colores(): array
    {
        return [
            'asistencias' => '#39A900',
            'encuestas' => '#0070B8',
            'asesorias' => '#6A1B9A',
            'pqrs' => '#E65100',
            'certificacion' => '#00838F',
            'inventario' => '#C0392B',
            'poe' => '#2C3E50',
        ];
    }

    private function usuariosResponsables()
    {
        return User::query()
            ->where('estado', 'Activo')
            ->where('origen_registro', 'sistema')
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['controlecp.admin', 'controlecp.instructor']))
            ->orderBy('nickname')
            ->get(['id', 'nickname', 'email']);
    }

    private function generar(string $tipo, Request $request): array
    {
        return /* Devuelve el resultado de este mï¿½todo. */ match ($tipo) {
            'inventario' => $this->inventario($request),
            'pqrs' => $this->pqrs($request),
            'asesorias' => $this->asesorias($request),
            'encuestas' => $this->encuestas($request),
            'certificacion' => $this->certificacion($request),
            'poe' => $this->poe($request),
            default => $this->asistencias($request),
        };
    }

    private function inventario(Request $request): array
    {
        if (!Schema::hasTable('inventario')) {
            return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultadoVacio('No existe informaciÃ³n de inventario.');
        }
        $query = DB::table('inventario');
        $errorFiltro = $this->aplicarFiltros($query, 'inventario', $request, ['created_at', 'fecha_ingreso', 'fecha_registro'], ['id_usuario_responsable', 'id_usuario', 'user_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderBy('nombre')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Total' => $rows->count(), 'Disponibles' => $rows->where('estado', 'Disponible')->count()]);
    }

    private function pqrs(Request $request): array
    {
        if (!Schema::hasTable('controlecp_pqrs')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe informaciÃ³n de PQRS.');
        $query = DB::table('controlecp_pqrs');
        $errorFiltro = $this->aplicarFiltros($query, 'controlecp_pqrs', $request, ['fecha_radicacion', 'created_at'], ['respondido_por', 'id_usuario_responsable', 'usuario_id', 'user_id', 'id_usuario']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc(Schema::hasColumn('controlecp_pqrs', 'fecha_radicacion') ? 'fecha_radicacion' : 'created_at')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Total' => $rows->count(), 'Pendientes' => $rows->where('estado', 'Pendiente')->count()]);
    }

    private function asesorias(Request $request): array
    {
        if (!Schema::hasTable('asesorias')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe información de asesorías.');
        $query = DB::table('asesorias');
        $errorFiltro = $this->aplicarFiltros($query, 'asesorias', $request, ['fecha', 'created_at'], ['id_usuario_instructor', 'id_usuario_registro', 'id_usuario_responsable', 'id_usuario', 'user_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc('fecha')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Total' => $rows->count()]);
    }

    private function encuestas(Request $request): array
    {
        if (!Schema::hasTable('controlecp_encuestas')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe informaciÃ³n de encuestas.');
        $query = DB::table('controlecp_encuestas');
        $errorFiltro = $this->aplicarFiltros($query, 'controlecp_encuestas', $request, ['created_at', 'fecha'], ['id_usuario_creador', 'id_usuario', 'user_id', 'usuario_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc('created_at')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Total' => $rows->count(), 'Activas' => $rows->whereIn('estado', ['Activo', 'Activa'])->count()]);
    }

    private function certificacion(Request $request): array
    {
        if (!Schema::hasTable('cronograma')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe información de certificación.');
        $query = DB::table('cronograma');
        $errorFiltro = $this->aplicarFiltros($query, 'cronograma', $request, ['fecha', 'created_at'], ['id_instructor', 'id_usuario_creador', 'id_usuario_finalizador', 'id_usuario_responsable', 'id_usuario', 'user_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc('fecha')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Sesiones' => $rows->count(), 'Finalizadas' => $rows->where('estado', 'Finalizada')->count()]);
    }

    private function poe(Request $request): array
    {
        if (!Schema::hasTable('poe_programaciones')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe informaciÃ³n de POE.');
        $query = DB::table('poe_programaciones');
        $errorFiltro = $this->aplicarFiltros($query, 'poe_programaciones', $request, ['fecha_limite', 'fecha_original', 'fecha', 'created_at'], ['id_usuario_lider_actual', 'id_usuario_nuevo_lider', 'id_usuario_responsable', 'id_usuario', 'user_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc('id_poe_programacion')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Total' => $rows->count()]);
    }

    private function asistencias(Request $request): array
    {
        if (!Schema::hasTable('asistencias')) return /* Devuelve el resultado de este método. */ $this->resultadoVacio('No existe informaciÃ³n de asistencias.');
        $query = DB::table('asistencias');
        $errorFiltro = $this->aplicarFiltros($query, 'asistencias', $request, ['fecha', 'created_at'], ['id_usuario_responsable', 'id_usuario', 'user_id']);
        if ($errorFiltro) return $this->resultadoVacio($errorFiltro);
        $rows = $query->orderByDesc('id')->get();
        return /* Devuelve el resultado obtenido de la consulta o acciï¿½n. */ $this->resultado($rows, ['Registros' => $rows->count()]);
    }

    private function aplicarFiltros($query, string $tabla, Request $request, array $columnasFecha, array $columnasUsuario): ?string
    {
        if ($request->filled('estado')) {
            if (!Schema::hasColumn($tabla, 'estado')) {
                return 'El filtro Estado no está disponible para este tipo de reporte.';
            }
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            $columnaFecha = null;
            foreach ($columnasFecha as $columna) {
                if (Schema::hasColumn($tabla, $columna)) {
                    $columnaFecha = $columna;
                    break;
                }
            }
            if (!$columnaFecha) {
                return 'El filtro de fechas no está disponible para este tipo de reporte.';
            }
            if ($request->filled('fecha_desde')) {
                $query->whereDate($columnaFecha, '>=', $request->input('fecha_desde'));
            }
            if ($request->filled('fecha_hasta')) {
                $query->whereDate($columnaFecha, '<=', $request->input('fecha_hasta'));
            }
        }

        if ($request->filled('usuario_id')) {
            $columnaUsuario = null;
            foreach ($columnasUsuario as $columna) {
                if (Schema::hasColumn($tabla, $columna)) {
                    $columnaUsuario = $columna;
                    break;
                }
            }
            if (!$columnaUsuario) {
                return 'El filtro Responsable no está disponible para este tipo de reporte.';
            }
            $query->where($columnaUsuario, $request->input('usuario_id'));
        }

        return null;
    }

    private function resultado($rows, array $indicadores): array
    {
        $rows = collect($rows);
        return /* Devuelve el resultado de este mï¿½todo. */ ['filas' => $rows, 'columnas' => $rows->isEmpty() ? [] : array_keys((array) $rows->first()), 'indicadores' => $indicadores, 'mensaje' => null];
    }

    private function resultadoVacio(string $mensaje): array
    {
        return /* Devuelve el resultado de este mï¿½todo. */ ['filas' => collect(), 'columnas' => [], 'indicadores' => [], 'mensaje' => $mensaje];
    }
}
