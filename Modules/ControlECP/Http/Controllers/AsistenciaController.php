<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ControlECP\Entities\Cronograma;
use Modules\ControlECP\Entities\CronogramaHistorial;
use Modules\ControlECP\Services\CertificacionNotificacionService;
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
                $query->whereDoesntHave('asistencia')
                    ->whereDate('fecha', '<=', Carbon::today());
            } elseif ($estado === 'Pendiente') {
                $query->whereDoesntHave('asistencia')
                    ->whereDate('fecha', '>', Carbon::today());
            } else {
                $query->whereHas('asistencia', fn ($q) => $q->where('estado', $estado));
            }
        }

        $cronogramas = $query->orderByDesc('fecha')->orderByDesc('hora_inicio')->paginate(10)->withQueryString();
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

    public function aprendices(int $fichaId): JsonResponse
    {
        $this->authorizeAdmin();

        Course::findOrFail($fichaId);
        $aprendices = Apprentice::with('person')
            ->where('course_id', $fichaId)
            ->orderBy('id')
            ->get()
            ->map(fn (Apprentice $aprendiz) => [
                'id' => $aprendiz->id,
                'documento' => $aprendiz->person?->document_number,
                'nombre' => $aprendiz->person?->full_name,
            ]);

        return response()->json(['data' => $aprendices]);
    }

    public function actividadesProgramadas(int $fichaId): JsonResponse
    {
        $this->authorizeAdmin();

        Course::findOrFail($fichaId);
        $actividades = Cronograma::with('actividad')
            ->where('id_ficha', $fichaId)
            ->whereDate('fecha', '<=', Carbon::today())
            ->whereNotIn('estado', ['Cancelada', 'Pospuesta'])
            ->whereDoesntHave('asistencia')
            ->orderByDesc('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->filter(fn (Cronograma $cronograma) => $cronograma->actividad)
            ->map(fn (Cronograma $cronograma) => [
                'id' => $cronograma->id_cronograma,
                'nombre' => $cronograma->actividad->numero . ' - ' . $cronograma->actividad->nombre,
                'fecha' => Carbon::parse($cronograma->fecha)->format('d/m/Y'),
            ])
            ->values();

        return response()->json(['data' => $actividades]);
    }

    public function storeManual(Request $request)
    {
        $this->authorizeAdmin();

        $datos = $request->validate([
            'id_ficha' => ['required', 'integer', 'exists:courses,id'],
            'cronograma_id' => ['required', 'integer', 'exists:cronograma,id_cronograma'],
            'asistencia' => ['required', 'array', 'min:1'],
            'asistencia.*.presente' => ['required', 'in:0,1'],
            'asistencia.*.justificada' => ['required', 'in:0,1'],
            'asistencia.*.observacion' => ['nullable', 'string', 'min:10', 'max:200'],
        ], [
            'cronograma_id.required' => 'Selecciona una actividad programada para esta ficha.',
            'asistencia.required' => 'Selecciona una ficha con aprendices para registrar la asistencia.',
        ]);

        $cronograma = Cronograma::with('actividad')
            ->where('id_cronograma', $datos['cronograma_id'])
            ->where('id_ficha', $datos['id_ficha'])
            ->first();
        if (!$cronograma) {
            throw ValidationException::withMessages([
                'cronograma_id' => 'La actividad programada no corresponde a la ficha seleccionada.',
            ]);
        }
        $this->validateChecklist($request, $cronograma);

        DB::transaction(function () use ($request, $cronograma) {
            $asistenciaId = DB::table('controlecp_asistencias')->insertGetId([
                'cronograma_id' => $cronograma->id_cronograma,
                'registrado_por' => Auth::id(),
                'estado' => 'Registrada',
                'registrada_en' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->saveDetails($request, $asistenciaId);
            $this->saveHistory($asistenciaId, 'Registrada', null);
            $this->saveEvidence($request, $cronograma);
        });

        return redirect()->route('controlecp.administrador.asistencia')
            ->with('success', 'Asistencia registrada correctamente.');
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
        $imagenesExcusaExistentes = DB::table('controlecp_asistencia_detalles')
            ->where('asistencia_id', $asistencia->id)
            ->pluck('excusa_imagen', 'aprendiz_id')
            ->all();
        $this->validateChecklist($request, $cronograma, true, $imagenesExcusaExistentes);
        $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:200']], [
            'motivo.required' => 'Debes indicar el motivo de la edición.',
        ]);

        DB::transaction(function () use ($request, $asistencia, $cronograma, $imagenesExcusaExistentes) {
            DB::table('controlecp_asistencia_detalles')->where('asistencia_id', $asistencia->id)->delete();
            $this->saveDetails($request, $asistencia->id, $imagenesExcusaExistentes);
            $this->saveHistory($asistencia->id, 'Editada', $request->input('motivo'));
            $this->saveEvidence($request, $cronograma);
        });

        return redirect()->route('controlecp.administrador.asistencia')
            ->with('success', 'Asistencia actualizada correctamente.');
    }

    public function annul(Request $request, int $id)
    {
        $this->authorizeAdmin();
        $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:200']], [
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

    public function finalizarActividad(int $cronogramaId)
    {
        $this->authorizeAdmin();

        DB::transaction(function () use ($cronogramaId) {
            $cronograma = Cronograma::query()
                ->whereKey($cronogramaId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cronograma->estado === 'Finalizada') {
                throw ValidationException::withMessages([
                    'cronograma' => 'Esta actividad de la ficha ya está finalizada.',
                ]);
            }
            if (!in_array($cronograma->estado, ['Pendiente', 'En proceso'], true)) {
                throw ValidationException::withMessages([
                    'cronograma' => 'Solo se pueden finalizar actividades pendientes o en proceso.',
                ]);
            }
            if (!DB::table('controlecp_asistencias')
                ->where('cronograma_id', $cronogramaId)
                ->where('estado', 'Registrada')
                ->exists()) {
                throw ValidationException::withMessages([
                    'cronograma' => 'Registra primero la asistencia de esta ficha para poder finalizar la actividad.',
                ]);
            }

            $cronograma->update([
                'estado' => 'Finalizada',
                'id_usuario_finalizador' => Auth::id(),
                'fecha_finalizacion' => now(),
            ]);

            CronogramaHistorial::create([
                'id_cronograma' => $cronograma->id_cronograma,
                'id_usuario_autor' => Auth::id(),
                'accion' => 'finalizar',
                'fecha' => now(),
            ]);

            app(CertificacionNotificacionService::class)->notificarSiSoloFaltaUnMomento(
                Course::findOrFail($cronograma->id_ficha),
                exigirListoParaCertificar: true
            );
        });

        return redirect()->route('controlecp.administrador.asistencia')
            ->with('success', 'Actividad finalizada para esta ficha. El avance ya se refleja en Certificados.');
    }

    private function validateChecklist(
        Request $request,
        Cronograma $cronograma,
        bool $editing = false,
        array $imagenesExcusaExistentes = []
    ): void
    {
        $request->validate([
            'evidencia_foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'evidencia_enlace' => ['nullable', 'url', 'max:500'],
            'asistencia.*.excusa_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'evidencia_foto.image' => 'La evidencia debe ser una imagen.',
            'evidencia_foto.mimes' => 'La foto debe estar en formato JPG o PNG.',
            'evidencia_foto.max' => 'La foto no puede superar 5 MB.',
            'evidencia_enlace.url' => 'El enlace de video no tiene un formato válido.',
            'asistencia.*.excusa_imagen.image' => 'La evidencia de la excusa debe ser una imagen.',
            'asistencia.*.excusa_imagen.mimes' => 'La evidencia de la excusa debe estar en formato JPG o PNG.',
            'asistencia.*.excusa_imagen.max' => 'La evidencia de la excusa no puede superar 5 MB.',
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
        $enviados = array_map('intval', array_keys($request->input('asistencia', [])));
        if (array_diff($aprendices, $enviados) || array_diff($enviados, $aprendices)) {
            throw ValidationException::withMessages(['asistencia' => 'La lista de aprendices debe estar completa.']);
        }
        foreach ($request->input('asistencia', []) as $aprendizId => $data) {
            $justificada = (string) ($data['justificada'] ?? '0') === '1';
            $presente = (string) ($data['presente'] ?? '0') === '1';
            $tieneImagen = $request->hasFile("asistencia.{$aprendizId}.excusa_imagen")
                || !empty($imagenesExcusaExistentes[$aprendizId]);
            if ($justificada && ($presente || empty(trim($data['observacion'] ?? '')) || !$tieneImagen)) {
                throw ValidationException::withMessages([
                    'asistencia' => 'Para justificar una ausencia, marca al aprendiz como ausente, escribe el motivo y adjunta una imagen de soporte.',
                ]);
            }
        }
    }

    private function saveDetails(Request $request, int $asistenciaId, array $imagenesExcusaExistentes = []): void
    {
        foreach ($request->input('asistencia', []) as $aprendizId => $data) {
            $justificada = !empty($data['justificada']);
            $imagenExcusa = $justificada ? ($imagenesExcusaExistentes[$aprendizId] ?? null) : null;
            if ($justificada && $request->hasFile("asistencia.{$aprendizId}.excusa_imagen")) {
                $imagenExcusa = $request->file("asistencia.{$aprendizId}.excusa_imagen")
                    ->store('controlecp/excusas', 'public');
            }
            DB::table('controlecp_asistencia_detalles')->insert([
                'asistencia_id' => $asistenciaId,
                'aprendiz_id' => $aprendizId,
                'presente' => !empty($data['presente']),
                'justificada' => $justificada,
                'observacion' => $data['observacion'] ?? null,
                'excusa_imagen' => $imagenExcusa,
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
            'origen' => 'asistencia',
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
