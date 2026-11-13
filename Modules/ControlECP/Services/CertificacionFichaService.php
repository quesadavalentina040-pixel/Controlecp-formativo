<?php

namespace Modules\ControlECP\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SICA\Entities\Course;

class CertificacionFichaService
{
    public function validarSiguienteMomento(int $courseId, int $momentoId): void
    {
        $momento = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->where('id', $momentoId)
            ->first(['id', 'orden']);

        if (!$momento) {
            abort(404);
        }

        $momentosAnteriores = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($momento) {
                $query->where('orden', '<', $momento->orden)
                    ->orWhere(function ($query) use ($momento) {
                        $query->where('orden', $momento->orden)
                            ->where('id', '<', $momento->id);
                    });
            })
            ->pluck('id');

        $certificacionesAnteriores = DB::table('controlecp_certificaciones')
            ->where('course_id', $courseId)
            ->whereIn('momento_id', $momentosAnteriores)
            ->distinct()
            ->count('momento_id');

        if ($certificacionesAnteriores !== $momentosAnteriores->count()) {
            throw ValidationException::withMessages([
                'certificacion' => 'Primero debes certificar el momento anterior de esta ficha.',
            ]);
        }

        if (DB::table('controlecp_certificaciones')
            ->where('course_id', $courseId)
            ->where('momento_id', $momentoId)
            ->exists()) {
            throw ValidationException::withMessages([
                'certificacion' => 'Este momento ya fue certificado para la ficha.',
            ]);
        }
    }

    public function descargarConstancia(Course $curso)
    {
        $momentos = DB::table('controlecp_momentos')
            ->whereNull('deleted_at')
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'orden']);
        $certificaciones = DB::table('controlecp_certificaciones as c')
            ->join('users as u', 'u.id', '=', 'c.responsable_id')
            ->where('c.course_id', $curso->id)
            ->whereIn('c.momento_id', $momentos->pluck('id'))
            ->orderBy('c.fecha_certificacion')
            ->get([
                'c.momento_id',
                'c.responsable_tipo',
                'c.fecha_certificacion',
                'u.nickname',
            ])
            ->groupBy('momento_id');

        if ($momentos->count() < 3 || $momentos->contains(
            fn ($momento) => $certificaciones->get($momento->id, collect())->isEmpty()
        )) {
            throw ValidationException::withMessages([
                'certificacion' => 'La constancia estará disponible cuando existan y se certifiquen al menos los tres momentos de la ficha.',
            ]);
        }

        $momentos = $momentos->map(function ($momento) use ($certificaciones) {
            $momento->certificaciones = $certificaciones->get($momento->id, collect());

            return $momento;
        });

        return Pdf::loadView('controlecp::aprendiz.certificado_ficha_pdf', compact('curso', 'momentos'))
            ->setPaper('a4', 'portrait')
            ->download('constancia-ficha-' . $curso->code . '.pdf');
    }
}
