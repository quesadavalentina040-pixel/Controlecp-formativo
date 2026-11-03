<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
        'acompanamientos' => 'Acompañamientos',
        'pqrs' => 'PQRS',
        'certificacion' => 'Certificación',
        'inventario' => 'Inventario',
        'poe' => 'POE',
    ];

    public function index(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasAnyRole(['controlecp.admin', 'controlecp.instructor'])) {
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

        $resultado = $errores
            ? $this->resultadoVacio('Corrige el rango de fechas para generar el reporte.')
            : ($usuario->hasRole('controlecp.instructor')
                ? $this->generarInstructor($tipo, $request)
                : $this->generar($tipo, $request));

        return view('controlecp::administrador.reportes', [
            'tipos' => self::TIPOS,
            'tipo' => $tipo,
            'resultado' => $resultado,
            'usuarios' => $usuario->hasRole('controlecp.admin')
                ? User::where('estado', 'Activo')->orderBy('nickname')->get(['id', 'nickname', 'email'])
                : collect(),
            'errores' => $errores,
        ]);
    }

    public function exportarPdf(Request $request)
    {
        $usuario = Auth::user();
        abort_unless($usuario && $usuario->hasAnyRole(['controlecp.admin', 'controlecp.instructor']), 403);
        abort_if($request->filled('fecha_desde') && $request->filled('fecha_hasta') && $request->fecha_desde > $request->fecha_hasta, 422, 'La fecha inicial no puede ser mayor que la final.');
        $tipo = array_key_exists($request->input('tipo'), self::TIPOS) ? $request->input('tipo') : 'asistencias';
        $resultado = $usuario->hasRole('controlecp.instructor')
            ? $this->generarInstructor($tipo, $request)
            : $this->generar($tipo, $request);
        abort_if(!$resultado['filas']->count(), 422, 'No se encontraron registros para exportar.');

        return Pdf::loadView('controlecp::administrador.reporte_pdf', [
            'tipo' => self::TIPOS[$tipo],
            'resultado' => $resultado,
            'usuario' => $usuario,
            'fechaDesde' => $request->fecha_desde,
            'fechaHasta' => $request->fecha_hasta,
        ])->download('reporte-' . $tipo . '.pdf');
    }

    private function generarInstructor(string $tipo, Request $request): array
    {
        return match ($tipo) {
            'asistencias' => $this->instructorAsistencias($request),
            'asesorias' => $this->instructorAsesorias($request),
            'acompanamientos' => $this->instructorAcompanamientos($request),
            'certificacion' => $this->instructorCertificaciones($request),
            'encuestas' => $this->instructorEncuestas($request),
            'inventario' => $this->instructorInventario($request),
            'pqrs' => $this->instructorPqrs($request),
            default => $this->resultadoVacio('Este reporte no está disponible para instructores.'),
        };
    }

    private function instructorCronogramas(Request $request)
    {
        return DB::table('cronograma as c')
            ->leftJoin('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->where('c.id_instructor', Auth::id())
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('c.fecha', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('c.fecha', '<=', $request->fecha_hasta))
            ->when($request->filled('id_ficha'), fn ($q) => $q->where('c.id_ficha', $request->id_ficha));
    }

    private function instructorAsistencias(Request $request): array
    {
        if (!Schema::hasTable('controlecp_asistencias')) return $this->resultadoVacio('No existe información de asistencias.');
        $rows = DB::table('controlecp_asistencias as ae')
            ->join('cronograma as c', 'c.id_cronograma', '=', 'ae.id_cronograma')
            ->leftJoin('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            ->where('ae.id_instructor', Auth::id())
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('ae.created_at', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('ae.created_at', '<=', $request->fecha_hasta))
            ->select('c.id_ficha as ficha', 'a.nombre as actividad', 'ae.estado', 'ae.created_at as fecha')
            ->orderByDesc('ae.created_at')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Registradas' => $rows->where('estado', 'Registrada')->count()]);
    }

    private function instructorAsesorias(Request $request): array
    {
        $rows = DB::table('asesorias')->where('id_usuario_instructor', Auth::id())
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta))
            ->select('id_asesoria', 'id_categoria_asesoria as categoria', 'fecha', 'hora_inicio', 'estado')->orderByDesc('fecha')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Realizadas' => $rows->where('estado', 'Realizada')->count(), 'Canceladas' => $rows->where('estado', 'Cancelada')->count()]);
    }

    private function instructorAcompanamientos(Request $request): array
    {
        if (!Schema::hasTable('controlecp_acompaniamientos')) return $this->resultadoVacio('No existe información de acompañamientos.');
        $rows = DB::table('controlecp_acompaniamientos as ac')
            ->where('ac.id_instructor', Auth::id())
            ->when($request->filled('estado'), fn ($q) => $q->where('ac.estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('ac.fecha_inicio', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('ac.fecha_inicio', '<=', $request->fecha_hasta))
            ->leftJoin('controlecp_acompanamiento_sesiones as s', 's.id_acompanamiento', '=', 'ac.id_acompanamiento')
            ->select('ac.id_acompanamiento', 'ac.categoria', 'ac.estado', DB::raw('COUNT(s.id_sesion) as sesiones'), DB::raw("SUM(CASE WHEN s.estado = 'Realizada' THEN 1 ELSE 0 END) as realizadas"), DB::raw("SUM(CASE WHEN s.estado = 'No asistio' THEN 1 ELSE 0 END) as faltas"))
            ->groupBy('ac.id_acompanamiento', 'ac.categoria', 'ac.estado')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Sesiones realizadas' => $rows->sum('realizadas'), 'Faltas' => $rows->sum('faltas')]);
    }

    private function instructorCertificaciones(Request $request): array
    {
        $rows = DB::table('controlecp_certificaciones_momento as cm')
            ->join('courses as f', 'f.id', '=', 'cm.id_ficha')
            ->join('controlecp_momentos as m', 'm.id', '=', 'cm.id_momento')
            ->where('cm.id_instructor', Auth::id())
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('cm.fecha_certificacion', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('cm.fecha_certificacion', '<=', $request->fecha_hasta))
            ->select('f.code as ficha', 'm.nombre as momento', 'cm.fecha_certificacion as fecha', 'cm.estado')->orderByDesc('cm.fecha_certificacion')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Certificadas' => $rows->where('estado', 'Certificada')->count()]);
    }

    private function instructorEncuestas(Request $request): array
    {
        $rows = $this->instructorCronogramas($request)
            ->join('encuesta_respuestas as er', 'er.id_cronograma', '=', 'c.id_cronograma')
            ->select('c.id_ficha as ficha', 'a.nombre as actividad', DB::raw('COUNT(DISTINCT er.id_encuesta_respuesta) as respuestas'), DB::raw('MAX(er.fecha) as ultima_respuesta'))
            ->groupBy('c.id_ficha', 'a.nombre')->get();
        return $this->resultado($rows, ['Respuestas' => $rows->sum('respuestas'), 'Actividades evaluadas' => $rows->count()]);
    }

    private function instructorInventario(Request $request): array
    {
        $rows = DB::table('movimientos_inventario as mi')->join('inventario as i', 'i.id_inventario', '=', 'mi.id_inventario')
            ->where('mi.id_usuario', Auth::id())
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('mi.fecha', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('mi.fecha', '<=', $request->fecha_hasta))
            ->select('i.nombre as recurso', 'mi.tipo', 'mi.cantidad', 'mi.fecha')->orderByDesc('mi.fecha')->get();
        return $this->resultado($rows, ['Movimientos' => $rows->count(), 'Unidades' => $rows->sum('cantidad')]);
    }

    private function instructorPqrs(Request $request): array
    {
        $rows = DB::table('controlecp_pqrs as p')->where('p.id_usuario_instructor_destino', Auth::id())
            ->when($request->filled('estado'), fn ($q) => $q->where('p.estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('p.fecha_radicacion', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('p.fecha_radicacion', '<=', $request->fecha_hasta))
            ->select('p.radicado', 'p.asunto', 'p.estado', 'p.fecha_radicacion')->orderByDesc('p.fecha_radicacion')->get();
        return $this->resultado($rows, ['Total' => $rows->count(), 'Pendientes' => $rows->where('estado', 'Pendiente')->count()]);
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
