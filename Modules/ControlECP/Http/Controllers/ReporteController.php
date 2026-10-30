<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            return redirect()->route('controlecp.index')
                ->with('error', 'No tienes permisos para acceder a los reportes.');
        }

        $tipo = $request->input('tipo', 'asistencias');
        if (!array_key_exists($tipo, self::TIPOS)) {
            $tipo = 'asistencias';
        }

        $errores = [];
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')
            && $request->fecha_desde > $request->fecha_hasta) {
            $errores[] = 'La fecha inicial no puede ser mayor que la fecha final.';
        }

        $resultado = $errores ? $this->resultadoVacio('Corrige el rango de fechas para generar el reporte.') : $this->generar($tipo, $request);

        return view('controlecp::administrador.reportes', [
            'tipos' => self::TIPOS,
            'tipo' => $tipo,
            'resultado' => $resultado,
            'usuarios' => User::where('estado', 'Activo')->orderBy('nickname')->get(['id', 'nickname', 'email']),
            'errores' => $errores,
        ]);
    }

    private function generar(string $tipo, Request $request): array
    {
        return match ($tipo) {
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
            return $this->resultadoVacio('No existe información de inventario.');
        }
        $query = DB::table('inventario');
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        $rows = $query->orderBy('nombre')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Disponibles' => $rows->where('estado', 'Disponible')->count()]);
    }

    private function pqrs(Request $request): array
    {
        if (!Schema::hasTable('controlecp_pqrs')) return $this->resultadoVacio('No existe información de PQRS.');
        $query = DB::table('controlecp_pqrs');
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('fecha_desde')) $query->whereDate('fecha_radicacion', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->whereDate('fecha_radicacion', '<=', $request->fecha_hasta);
        $rows = $query->orderByDesc('fecha_radicacion')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Pendientes' => $rows->where('estado', 'Pendiente')->count()]);
    }

    private function asesorias(Request $request): array
    {
        if (!Schema::hasTable('asesorias')) return $this->resultadoVacio('No existe información de asesorías.');
        $query = DB::table('asesorias');
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('fecha_desde')) $query->whereDate('fecha', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->whereDate('fecha', '<=', $request->fecha_hasta);
        $rows = $query->orderByDesc('fecha')->get();
        return $this->resultado($rows, ['Total' => $rows->count()]);
    }

    private function encuestas(Request $request): array
    {
        if (!Schema::hasTable('controlecp_encuestas')) return $this->resultadoVacio('No existe información de encuestas.');
        $rows = DB::table('controlecp_encuestas')->orderByDesc('created_at')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Activas' => $rows->whereIn('estado', ['Activo', 'Activa'])->count()]);
    }

    private function certificacion(Request $request): array
    {
        if (!Schema::hasTable('cronograma')) return $this->resultadoVacio('No existe información de certificación.');
        $rows = DB::table('cronograma')->orderByDesc('fecha')->get();
        return $this->resultado($rows, ['Sesiones' => $rows->count(), 'Finalizadas' => $rows->where('estado', 'Finalizada')->count()]);
    }

    private function poe(Request $request): array
    {
        if (!Schema::hasTable('poe_programaciones')) return $this->resultadoVacio('No existe información de POE.');
        $rows = DB::table('poe_programaciones')->orderByDesc('created_at')->get();
        return $this->resultado($rows, ['Total' => $rows->count()]);
    }

    private function asistencias(Request $request): array
    {
        if (!Schema::hasTable('asistencias')) return $this->resultadoVacio('No existe información de asistencias.');
        $query = DB::table('asistencias');
        if ($request->filled('fecha_desde') && Schema::hasColumn('asistencias', 'fecha')) $query->whereDate('fecha', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta') && Schema::hasColumn('asistencias', 'fecha')) $query->whereDate('fecha', '<=', $request->fecha_hasta);
        $rows = $query->orderByDesc('id')->get();
        return $this->resultado($rows, ['Registros' => $rows->count()]);
    }

    private function resultado($rows, array $indicadores): array
    {
        $rows = collect($rows);
        return ['filas' => $rows, 'columnas' => $rows->isEmpty() ? [] : array_keys((array) $rows->first()), 'indicadores' => $indicadores, 'mensaje' => null];
    }

    private function resultadoVacio(string $mensaje): array
    {
        return ['filas' => collect(), 'columnas' => [], 'indicadores' => [], 'mensaje' => $mensaje];
    }
}
