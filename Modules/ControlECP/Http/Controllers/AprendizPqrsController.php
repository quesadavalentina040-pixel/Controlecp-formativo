<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ControlECP\Entities\Notificacion;
use Modules\ControlECP\Entities\Pqrs;
use Modules\ControlECP\Entities\PqrsHistorial;
use Modules\ControlECP\Entities\TipoPqrs;

class AprendizPqrsController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('controlecp.apprentice'), 403);
        $datos = $request->validate([
            'id_tipo_pqrs' => ['required', 'exists:tipos_pqrs,id_tipo_pqrs'],
            'id_usuario_instructor_destino' => ['nullable', 'integer', 'exists:users,id'],
            'asunto' => ['required', 'string', 'min:10', 'max:200', function ($attribute, $value, $fail) {
                if (trim($value) === '') {
                    $fail('El asunto es obligatorio.');
                }
            }],
            'descripcion' => ['required', 'string', 'min:10', 'max:200', function ($attribute, $value, $fail) {
                if (mb_strlen(trim($value)) < 10) {
                    $fail('La descripción debe tener al menos 10 caracteres.');
                }
            }],
        ]);

        $instructorId = $datos['id_usuario_instructor_destino'] ?? null;
        if ($instructorId && !User::whereKey($instructorId)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'controlecp.instructor'))
            ->exists()) {
            return back()->withInput()->withErrors([
                'id_usuario_instructor_destino' => 'Selecciona un instructor válido.',
            ]);
        }

        DB::transaction(function () use ($datos, $instructorId) {
            $pqr = Pqrs::create([
                'radicado' => Pqrs::generarRadicado(),
                'id_usuario_aprendiz' => Auth::id(),
                'id_tipo_pqrs' => $datos['id_tipo_pqrs'],
                'id_usuario_instructor_destino' => $instructorId,
                'asunto' => trim($datos['asunto']),
                'descripcion' => trim($datos['descripcion']),
                'estado' => 'Pendiente',
                'fecha_radicacion' => now(),
            ]);

            PqrsHistorial::create([
                'id_pqrs' => $pqr->id,
                'id_usuario' => Auth::id(),
                'estado_anterior' => null,
                'estado_nuevo' => 'Pendiente',
                'observacion' => 'Solicitud radicada por el aprendiz.',
                'fecha' => now(),
            ]);

            if ($instructorId) {
                Notificacion::crear(
                    idReceptor: (int) $instructorId,
                    idCausante: (int) Auth::id(),
                    titulo: 'Nueva PQRS asignada',
                    mensaje: "Recibiste la solicitud {$pqr->radicado}: {$pqr->asunto}.",
                    referenciaTipo: 'pqr',
                    referenciaId: (int) $pqr->id,
                    claveEvento: 'pqr-nueva-' . $pqr->id
                );
            } else {
                $administradores = User::whereHas('roles', fn ($query) => $query
                    ->whereIn('slug', ['controlecp.admin', 'superadmin'])
                    ->orWhere('full_access', 'Si'))
                    ->pluck('id');

                foreach ($administradores as $administradorId) {
                    Notificacion::crear(
                        idReceptor: (int) $administradorId,
                        idCausante: (int) Auth::id(),
                        titulo: 'Nueva PQRS para administración',
                        mensaje: "Se radicó la solicitud {$pqr->radicado}: {$pqr->asunto}.",
                        referenciaTipo: 'pqr',
                        referenciaId: (int) $pqr->id,
                        claveEvento: 'pqr-nueva-' . $pqr->id . '-' . $administradorId
                    );
                }
            }
        });

        return redirect()->route('controlecp.aprendiz.pqr')
            ->with('success', 'Tu solicitud fue radicada correctamente.');
    }
}
