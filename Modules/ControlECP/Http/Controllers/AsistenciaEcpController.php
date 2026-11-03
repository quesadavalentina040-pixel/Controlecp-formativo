<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ControlECP\Entities\AsistenciaEcp;
use Modules\ControlECP\Entities\AsistenciaEcpHistorial;
use Modules\ControlECP\Entities\Cronograma;

class AsistenciaEcpController extends Controller
{
    private function instructor()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('controlecp.instructor'), 403);
        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->instructor();
        $query = AsistenciaEcp::with(['cronograma.actividad.momento', 'cronograma.ficha.program'])
            ->where('id_instructor', $user->id);
        if ($request->filled('ficha_id')) $query->where('id_ficha', $request->ficha_id);
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('fecha')) $query->whereHas('cronograma', fn ($q) => $q->whereDate('fecha', $request->fecha));
        if ($request->filled('actividad')) $query->whereHas('cronograma.actividad', fn ($q) => $q->where('nombre', 'like', '%'.trim($request->actividad).'%'));
        $asistencias = $query->latest()->get();
        $cronogramas = Cronograma::with(['actividad.momento', 'ficha.program'])
            ->where('id_instructor', $user->id)->whereDate('fecha', '<=', today())
            ->whereDoesntHave('asistenciaEcp')->orderByDesc('fecha')->get();
        $fichas = $cronogramas->pluck('ficha')->filter()->unique('id')->sortBy('code');
        return view('controlecp::instructor.asistencia', compact('asistencias', 'cronogramas', 'fichas'));
    }

    public function checklist(int $id)
    {
        $user = $this->instructor();
        $cronograma = Cronograma::with(['actividad.momento', 'ficha.program', 'ficha.apprentices.person'])
            ->where('id_instructor', $user->id)->findOrFail($id);
        abort_if($cronograma->fecha->isFuture(), 422, 'La asistencia solo puede registrarse desde la fecha de la actividad.');
        abort_if($cronograma->asistenciaEcp()->exists(), 422, 'Esta actividad ya tiene asistencia registrada.');
        return view('controlecp::instructor.asistencia_checklist', compact('cronograma'));
    }

    public function store(Request $request, int $id)
    {
        $user = $this->instructor();
        $cronograma = Cronograma::with('ficha.apprentices')->where('id_instructor', $user->id)->findOrFail($id);
        abort_if($cronograma->fecha->isFuture(), 422, 'La fecha de la actividad aún no ha llegado.');
        abort_if($cronograma->asistenciaEcp()->exists(), 422, 'Esta actividad ya tiene asistencia registrada.');
        $data = $request->validate([
            'presentes' => ['nullable', 'array'],
            'presentes.*' => ['integer'],
            'observacion' => ['nullable', 'string', 'max:5000'],
            'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'enlace_video' => ['nullable', 'url', 'max:500'],
        ]);
        if ($request->hasFile('evidencia') && $request->filled('enlace_video')) {
            return back()->withErrors(['evidencia' => 'Adjunta una foto o un enlace de video, no ambos.'])->withInput();
        }
        $asistencia = DB::transaction(function () use ($data, $request, $cronograma, $user) {
            $evidencia = $request->file('evidencia');
            $asistencia = AsistenciaEcp::create([
                'id_cronograma' => $cronograma->id_cronograma, 'id_instructor' => $user->id, 'id_ficha' => $cronograma->id_ficha,
                'observacion' => $data['observacion'] ?? null,
                'evidencia_tipo' => $evidencia ? 'foto' : ($data['enlace_video'] ?? null ? 'video' : null),
                'evidencia_archivo' => $evidencia?->store('controlecp/evidencias', 'public'),
                'evidencia_enlace' => $data['enlace_video'] ?? null,
            ]);
            $presentes = collect($data['presentes'] ?? [])->map(fn ($value) => (int) $value);
            foreach ($cronograma->ficha->apprentices as $aprendiz) {
                $asistencia->detalles()->create(['id_aprendiz' => $aprendiz->id, 'presente' => $presentes->contains($aprendiz->id)]);
            }
            return $asistencia;
        });
        return redirect()->route('controlecp.instructor.asistencia')->with('success', 'Asistencia registrada correctamente.');
    }

    public function editar(Request $request, int $id)
    {
        $asistencia = $this->propia($id);
        $data = $request->validate(['motivo' => ['required', 'string', 'max:2000'], 'presentes' => ['nullable', 'array'], 'observacion' => ['nullable', 'string', 'max:5000']]);
        $anteriores = $asistencia->load('detalles')->toArray();
        $asistencia->update(['observacion' => $data['observacion'] ?? null]);
        $presentes = collect($data['presentes'] ?? [])->map(fn ($value) => (int) $value);
        $asistencia->detalles->each(fn ($detalle) => $detalle->update(['presente' => $presentes->contains($detalle->id_aprendiz)]));
        AsistenciaEcpHistorial::create(['id_asistencia' => $id, 'id_usuario' => Auth::id(), 'accion' => 'edicion', 'motivo' => $data['motivo'], 'datos_anteriores' => $anteriores]);
        return back()->with('success', 'Asistencia corregida y conservada en el historial.');
    }

    public function anular(Request $request, int $id)
    {
        $asistencia = $this->propia($id);
        $data = $request->validate(['motivo' => ['required', 'string', 'max:2000']]);
        $asistencia->update(['estado' => 'Anulada']);
        AsistenciaEcpHistorial::create(['id_asistencia' => $id, 'id_usuario' => Auth::id(), 'accion' => 'anulacion', 'motivo' => $data['motivo']]);
        return back()->with('success', 'Asistencia anulada correctamente.');
    }

    public function exportar(Request $request)
    {
        $user = $this->instructor();
        $registros = AsistenciaEcp::with(['cronograma.actividad', 'cronograma.ficha'])
            ->where('id_instructor', $user->id)->latest()->get();
        return response()->streamDownload(function () use ($registros) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ficha', 'Actividad', 'Fecha', 'Estado', 'Presentes', 'Ausentes']);
            foreach ($registros as $registro) {
                $detalles = $registro->detalles;
                fputcsv($handle, [
                    $registro->cronograma->ficha->code ?? '',
                    $registro->cronograma->actividad->nombre ?? '',
                    optional($registro->cronograma->fecha)->format('Y-m-d'),
                    $registro->estado,
                    $detalles->where('presente', true)->count(),
                    $detalles->where('presente', false)->count(),
                ]);
            }
            fclose($handle);
        }, 'reporte-asistencias-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function propia(int $id): AsistenciaEcp
    {
        return AsistenciaEcp::where('id_asistencia', $id)->where('id_instructor', Auth::id())->firstOrFail();
    }
}
