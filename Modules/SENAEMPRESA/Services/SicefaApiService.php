<?php

namespace Modules\SENAEMPRESA\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SICA\Entities\Apprentice;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\Course;
use Modules\SENAEMPRESA\Entities\Asistencia;

/**
 * Servicio de integración con la API REST central del ERP SENA Empresa / SICEFA.
 *
 * Permite:
 *  - Sincronizar (descargar) aprendices desde SICEFA hacia la base local.
 *  - Enviar registros de asistencia a SICEFA.
 *  - Consultar el estado de la conexión.
 */
class SicefaApiService
{
    /** @var string URL base de la API */
    protected string $baseUrl;

    /** @var string Token de autenticación */
    protected string $token;

    /** @var int Timeout en segundos */
    protected int $timeout;

    /** @var string Prefijo de versión (ej: v1) */
    protected string $prefix;

    public function __construct()
    {
        $this->baseUrl = config('senaempresa.sicefa_api.base_url', 'https://sicefa.com.co/api');
        $this->token   = config('senaempresa.sicefa_api.token', '');
        $this->timeout = (int) config('senaempresa.sicefa_api.timeout', 30);
        $this->prefix  = config('senaempresa.sicefa_api.prefix', 'v1');
    }

    /**
     * Construye la URL completa de un endpoint.
     */
    protected function buildUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . trim($this->prefix, '/') . $endpoint;
    }

    /**
     * Crea una petición HTTP con los headers de autenticación.
     */
    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ]);
    }

    /**
     * Verifica si la API está disponible.
     *
     * @return array{status: bool, message: string}
     */
    public function checkConnection(): array
    {
        try {
            $response = $this->request()->get($this->buildUrl('/health'));

            if ($response->successful()) {
                return [
                    'status'  => true,
                    'message' => 'Conexión exitosa con la API de SICEFA.',
                ];
            }

            return [
                'status'  => false,
                'message' => 'La API respondió con código ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::warning('SICEFA API: error de conexión - ' . $e->getMessage());

            return [
                'status'  => false,
                'message' => 'No se pudo conectar: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza (descarga) los aprendices desde la API de SICEFA.
     *
     * @return array{created: int, updated: int, errors: array}
     */
    public function syncApprentices(): array
    {
        $created = 0;
        $updated = 0;
        $errors  = [];

        try {
            $response = $this->request()->get($this->buildUrl(
                config('senaempresa.endpoints.apprentices', '/aprendices')
            ));

            if (!$response->successful()) {
                $errors[] = 'Error HTTP ' . $response->status() . ': ' . $response->body();
                return compact('created', 'updated', 'errors');
            }

            $data = $response->json();
            $items = $data['data'] ?? $data['aprendices'] ?? $data ?? [];

            if (!is_array($items)) {
                $errors[] = 'Formato de respuesta inesperado.';
                return compact('created', 'updated', 'errors');
            }

            foreach ($items as $item) {
                try {
                    // Normalizar el formato del aprendiz desde la API
                    $personData = [
                        'document_type'   => $item['tipo_documento'] ?? $item['document_type'] ?? 'Cédula de ciudadanía',
                        'document_number' => $item['numero_documento'] ?? $item['document_number'] ?? null,
                        'first_name'       => $item['nombres'] ?? $item['first_name'] ?? null,
                        'first_last_name'  => $item['primer_apellido'] ?? $item['first_last_name'] ?? null,
                        'second_last_name' => $item['segundo_apellido'] ?? $item['second_last_name'] ?? null,
                        'misena_email'     => $item['correo_misena'] ?? $item['misena_email'] ?? null,
                        'telephone1'       => $item['telefono'] ?? $item['telephone1'] ?? null,
                    ];

                    if (!$personData['document_number']) {
                        $errors[] = 'Aprendiz sin número de documento, omitido.';
                        continue;
                    }

                    // Buscar o crear la persona
                    $person = Person::where('document_number', $personData['document_number'])->first();
                    if ($person) {
                        $person->update(array_filter($personData));
                    } else {
                        $person = Person::create(array_filter($personData));
                    }

                    // Buscar o crear el curso
                    $courseCode = $item['codigo_curso'] ?? $item['course_code'] ?? null;
                    $course = null;
                    if ($courseCode) {
                        $course = Course::where('code', $courseCode)->first();
                    }

                    // Buscar o crear el aprendiz
                    $apprentice = Apprentice::where('person_id', $person->id)->first();
                    $apprenticeData = [
                        'person_id'        => $person->id,
                        'course_id'        => $course?->id ?? 1,
                        'apprentice_status' => $item['estado'] ?? $item['apprentice_status'] ?? 'EN FORMACIÓN',
                    ];

                    if ($apprentice) {
                        $apprentice->update($apprenticeData);
                        $updated++;
                    } else {
                        Apprentice::create($apprenticeData);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Error procesando aprendiz: ' . $e->getMessage();
                    Log::error('SICEFA sync: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Error de conexión: ' . $e->getMessage();
            Log::error('SICEFA sync apprentices: ' . $e->getMessage());
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * Envía un registro de asistencia a la API de SICEFA.
     *
     * @param Asistencia $asistencia
     * @return array{success: bool, message: string, sicefa_id: int|null}
     */
    public function sendAttendance(Asistencia $asistencia): array
    {
        try {
            $payload = [
                'nombre'      => $asistencia->nombre,
                'descripcion' => $asistencia->descripcion,
                'fecha'       => $asistencia->fecha?->format('Y-m-d'),
                'hora_inicio'  => $asistencia->hora_inicio,
                'hora_fin'    => $asistencia->hora_fin,
                'tipo'        => $asistencia->tipo,
                'aprendices'  => $asistencia->apprentices->map(function ($a) {
                    return [
                        'document_number' => $a->person?->document_number,
                        'asistencia'      => $a->pivot->asistencia,
                        'observacion'     => $a->pivot->observacion,
                    ];
                })->toArray(),
            ];

            $response = $this->request()->post(
                $this->buildUrl(config('senaempresa.endpoints.attendance_send', '/asistencias/registrar')),
                $payload
            );

            if ($response->successful()) {
                $data = $response->json();
                $sicefaId = $data['id'] ?? $data['sicefa_id'] ?? null;

                // Marcar como sincronizada
                $asistencia->update([
                    'sicefa_id'       => $sicefaId,
                    'sicefa_synced_at' => now(),
                ]);

                return [
                    'success'   => true,
                    'message'   => 'Asistencia enviada exitosamente a SICEFA.',
                    'sicefa_id' => $sicefaId,
                ];
            }

            return [
                'success'   => false,
                'message'   => 'Error al enviar: HTTP ' . $response->status(),
                'sicefa_id'  => null,
            ];
        } catch (\Exception $e) {
            Log::error('SICEFA send attendance: ' . $e->getMessage());

            return [
                'success'   => false,
                'message'   => 'Error de conexión: ' . $e->getMessage(),
                'sicefa_id'  => null,
            ];
        }
    }

    /**
     * Sincroniza todas las asistencias pendientes (no enviadas a SICEFA).
     *
     * @return array{synced: int, errors: array}
     */
    public function syncPendingAttendance(): array
    {
        $synced = 0;
        $errors = [];

        $pending = Asistencia::whereNull('sicefa_id')
            ->orWhereNull('sicefa_synced_at')
            ->where('estado', '!=', 'CANCELADA')
            ->get();

        foreach ($pending as $asistencia) {
            $result = $this->sendAttendance($asistencia);
            if ($result['success']) {
                $synced++;
            } else {
                $errors[] = "Sesión {$asistencia->id} ({$asistencia->nombre}): {$result['message']}";
            }
        }

        return compact('synced', 'errors');
    }
}
