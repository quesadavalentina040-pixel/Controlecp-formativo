<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Cronograma;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Course;
use Modules\ControlECP\Entities\EvidenciaEcp;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Cronograma::with(['actividad', 'ficha.program', 'instructor.person', 'asistencia.registradoPor'])
            ->withCount(['asistencia as asistencia_registrada' => fn ($q) => $q->where('estado', 'Registrada')]);

        if ($request->filled('ficha_id')) {
            $query->where('id_ficha', $request->input('ficha_id'));
        } elseif ($request->filled('ficha')) {
            $ficha = trim($request->input('ficha'));
            $fichaIds = Course::query()
                ->where('code', 'like', '%' . $ficha . '%')
                ->pluck('id');
            $query->whereIn('id_ficha', $fichaIds);
        }
        if ($request->filled('instructor_id')) {
            $query->where('id_instructor', $request->input('instructor_id'));
        }
        if ($request->filled('actividad_id')) {
            $query->where('id_actividad', $request->input('actividad_id'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'Sin asistencia registrada') {
                $query->whereDoesntHave('asistencia', fn ($q) => $q->where('estado', 'Registrada'))
                    ->whereDate('fecha', '<', Carbon::today());
            } else {
                $query->whereHas('asistencia', fn ($q) => $q->where('estado', $estado));
            }
        }

        $cronogramas = $query->orderByDesc('fecha')->orderByDesc('hora_inicio')->paginate(15)->withQueryString();
        $fichaIds = $cronogramas->getCollection()->pluck('id_ficha')->filter()->unique();
        $aprendicesPorFicha = Apprentice::with('person')->whereIn('course_id', $fichaIds)->get()->groupBy('course_id');
        $asistenciaIds = $cronogramas->getCollection()->pluck('asistencia.id')->filter();
        $historialPorAsistencia = DB::table('controlecp_asistencia_historial as h')
            ->join('users as u', 'u.id', '=', 'h.usuario_id')
            ->whereIn('h.asistencia_id', $asistenciaIds)
            ->orderByDesc('h.created_at')
            ->select('h.*', 'u.nickname')->get()->groupBy('asistencia_id');
        $fichas = Course::query()->orderBy('code')->get(['id', 'code']);
        $actividades = DB::table('controlecp_actividades')->whereNull('deleted_at')->orderBy('numero')->get(['id', 'numero', 'nombre']);
        $instructores = User::whereHas('roles', fn ($q) => $q->where('slug', 'controlecp.instructor'))
            ->with('person')->orderBy('nickname')->get();

        $stats = [
            'total' => Cronograma::count(),
            'registradas' => DB::table('controlecp_asistencias')->where('estado', 'Registrada')->count(),
            'pendientes' => Cronograma::whereDate('fecha', '<', Carbon::today())
                ->whereDoesntHave('asistencia', fn ($q) => $q->where('estado', 'Registrada'))->count(),
            'ausencias' => DB::table('controlecp_asistencia_detalles')
                ->join('controlecp_asistencias', 'controlecp_asistencia_detalles.asistencia_id', '=', 'controlecp_asistencias.id')
                ->where('controlecp_asistencias.estado', 'Registrada')->where('presente', false)->count(),
        ];

        return view('controlecp::administrador.asistencia', compact('cronogramas', 'aprendicesPorFicha', 'historialPorAsistencia', 'fichas', 'actividades', 'instructores', 'stats'));
    }

    public function store(Request $request, int $id)
    {
        $this->authorizeAdmin();
        $cronograma = Cronograma::with('actividad')->findOrFail($id);
        $this->validateChecklist($request, $cronograma);

        DB::transaction(function () use ($request, $cronograma) {
            $asistencia = DB::table('controlecp_asistencias')->insertGetId([
                'cronograma_id' => $cronograma->id_cronograma,
                'registrado_por' => Auth::id(),
                'estado' => 'Registrada',
                'registrada_en' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->saveDetails($request, $asistencia);
            $this->saveHistory($asistencia, 'Registrada', null);
            $this->saveEvidence($request, $cronograma);
        });

        return redirect()->route('controlecp.administrador.asistencia')
            ->with('success', 'Asistencia registrada correctamente.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAdmin();
        $asistencia = DB::table('controlecp_asistencias')->where('id', $id)->first();
        if (!$asistencia || $asistencia->estado !== 'Registrada') {
            return back()->with('error', 'La asistencia no está disponible para edición.');
        }
        $cronograma = Cronograma::with('actividad')->findOrFail($asistencia->cronograma_id);
        $this->validateChecklist($request, $cronograma, true);
        $request->validate(['motivo' => ['required', 'string', 'max:1000']], [
            'motivo.required' => 'Debes indicar el motivo de la edición.',
        ]);

        DB::transaction(function () use ($request, $asistencia, $cronograma) {
            DB::table('controlecp_asistencia_detalles')->where('asistencia_id', $asistencia->id)->delete();
            $this->saveDetails($request, $asistencia->id);
            $this->saveHistory($asistencia->id, 'Editada', $request->input('motivo'));
            $this->saveEvidence($request, $cronograma);
        });

        return redirect()->route('controlecp.administrador.asistencia')
            ->with('success', 'Asistencia actualizada correctamente.');
    }

    public function annul(Request $request, int $id)
    {
        $this->authorizeAdmin();
        $request->validate(['motivo' => ['required', 'string', 'max:1000']], [
            'motivo.required' => 'Debes indicar el motivo de la anulación.',
        ]);
        $asistencia = DB::table('controlecp_asistencias')->where('id', $id)->where('estado', 'Registrada')->first();
        if (!$asistencia) {
            return back()->with('error', 'La asistencia ya está anulada o no existe.');
        }

        DB::transaction(function () use ($request, $asistencia) {
            DB::table('controlecp_asistencias')->where('id', $asistencia->id)->update([
                'estado' => 'Anulada',
                'motivo_anulacion' => $request->input('motivo'),
                'updated_at' => now(),
            ]);
            $this->saveHistory($asistencia->id, 'Anulada', $request->input('motivo'));
        });

        return back()->with('success', 'Asistencia anulada correctamente.');
    }

    private function validateChecklist(Request $request, Cronograma $cronograma, bool $editing = false): void
    {
        $request->validate([
            'evidencia_foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'evidencia_enlace' => ['nullable', 'url', 'max:500'],
        ], [
            'evidencia_foto.image' => 'La evidencia debe ser una imagen.',
            'evidencia_foto.mimes' => 'La foto debe estar en formato JPG o PNG.',
            'evidencia_foto.max' => 'La foto no puede superar 5 MB.',
            'evidencia_enlace.url' => 'El enlace de video no tiene un formato válido.',
        ]);
        if (!$cronograma->actividad) {
            throw ValidationException::withMessages(['cronograma' => 'La actividad programada no existe.']);
        }
        if (Carbon::parse($cronograma->fecha)->isFuture()) {
            throw ValidationException::withMessages(['cronograma' => 'No se puede registrar asistencia antes de la fecha de la actividad.']);
        }
        if (!$editing && DB::table('controlecp_asistencias')->where('cronograma_id', $cronograma->id_cronograma)->where('estado', 'Registrada')->exists()) {
            throw ValidationException::withMessages(['cronograma' => 'Esta actividad ya tiene una asistencia registrada.']);
        }
        $aprendices = Apprentice::where('course_id', $cronograma->id_ficha)->pluck('id')->all();
        $enviados = array_keys($request->input('asistencia', []));
        if (array_diff($aprendices, array_map('intval', $enviados))) {
            throw ValidationException::withMessages(['asistencia' => 'La lista de aprendices debe estar completa.']);
        }
    }

    private function saveDetails(Request $request, int $asistenciaId): void
    {
        foreach ($request->input('asistencia', []) as $aprendizId => $data) {
            DB::table('controlecp_asistencia_detalles')->insert([
                'asistencia_id' => $asistenciaId,
                'aprendiz_id' => $aprendizId,
                'presente' => !empty($data['presente']),
                'observacion' => $data['observacion'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function saveHistory(int $asistenciaId, string $accion, ?string $motivo): void
    {
        DB::table('controlecp_asistencia_historial')->insert([
            'asistencia_id' => $asistenciaId,
            'usuario_id' => Auth::id(),
            'accion' => $accion,
            'motivo' => $motivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function saveEvidence(Request $request, Cronograma $cronograma): void
    {
        if (!$request->hasFile('evidencia_foto') && !$request->filled('evidencia_enlace')) {
            return;
        }
        $data = [
            'actividad_id' => $cronograma->id_actividad,
            'course_id' => $cronograma->id_ficha,
            'user_id' => Auth::id(),
            'tipo' => $request->hasFile('evidencia_foto') ? 'foto' : 'video',
            'enlace' => $request->input('evidencia_enlace'),
            'fecha_registro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($request->hasFile('evidencia_foto')) {
            $data['archivo'] = $request->file('evidencia_foto')->store('controlecp/evidencias', 'public');
        }
        EvidenciaEcp::create($data);
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('controlecp.admin'), 403);
    }
}
