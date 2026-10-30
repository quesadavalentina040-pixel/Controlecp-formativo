<?php

namespace Modules\ControlECP\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ControlECP\Entities\Encuesta;
use Modules\ControlECP\Services\TabulacionService;

class TabulacionController extends Controller // Controlador de consulta, filtrado y exportación de resultados de encuestas.
{
    protected TabulacionService $tabulacionService;

    public function __construct(TabulacionService $tabulacionService) // __construct(): Recibe y conserva el servicio de tabulación; se ejecuta al construir el controlador.
    {
        $this->tabulacionService = $tabulacionService; // Conserva el servicio para reutilizar sus consultas y cálculos.
    }

    /**
     * Verifica que el usuario tenga rol de administrador de Control ECP.
     */
    private function verificarAccesoAdmin() // verificarAccesoAdmin(): Comprueba el rol administrador antes de consultar resultados; se usa en los endpoints protegidos.
    {
        $usuario = Auth::user(); // Obtiene la cuenta autenticada para revisar su rol.
        if (! $usuario || ! $usuario->hasRole('controlecp.admin')) {
            return redirect()
                ->route('controlecp.index') // Envía al inicio del módulo si no hay permiso.
                ->with('error', 'No tienes permisos para acceder a la tabulación de encuestas.'); // Prepara un aviso para la siguiente página.
        }

        return null; // Indica que el acceso está autorizado.
    }

    /**
     * 1. Consulta y visualización de la tabulación de encuestas.
     */
    public function index(Request $request) // index(): Valida filtros y muestra resultados de la encuesta seleccionada; se usa al abrir la tabulación.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Comprueba permisos antes de consultar resultados.
            return $res; // Devuelve la redirección de acceso denegado si corresponde.
        }

        $encuestas = $this->tabulacionService->obtenerEncuestasDisponibles(); // Pide al servicio las encuestas que pueden tabularse.
        $request->validate([ // Valida que el identificador elegido corresponda a una encuesta disponible.
            'id_encuesta' => [
                'nullable',
                'integer',
                Rule::in($encuestas->pluck('id_encuesta')->all()),
            ],
        ]); // Laravel detiene la acción y devuelve errores si el filtro no cumple las reglas.

        $idEncuestaSeleccionada = $request->input('id_encuesta'); // Lee el identificador enviado por el formulario.
        if (empty($idEncuestaSeleccionada) && $encuestas->isNotEmpty()) { // Usa una opción inicial si falta el filtro y hay encuestas.
            $idEncuestaSeleccionada = $encuestas->first()->id_encuesta; // Selecciona la primera encuesta disponible.
        }

        $encuestaActual = null; // Inicializa los valores para que la vista también funcione sin una encuesta seleccionada.
        $filtrosDisponibles = ['actividades' => collect(), 'fichas' => collect()];
        $resultados = null;

        if (! empty($idEncuestaSeleccionada)) { // Solo consulta datos específicos cuando hay una encuesta elegida.
            $encuestaActual = Encuesta::findOrFail($idEncuestaSeleccionada); // Busca la encuesta o responde con error 404.
            $filtrosDisponibles = $this->tabulacionService->obtenerFiltrosParaEncuesta($encuestaActual->id_encuesta); // Obtiene actividades y fichas válidas para esa encuesta.
            $filtros = $this->validarFiltros($request, $filtrosDisponibles); // Valida los filtros solicitados contra esas opciones.
            $resultados = $this->tabulacionService->calcularResultados($encuestaActual->id_encuesta, $filtros); // Calcula los totales y detalles aplicando los filtros.
        }

        return view('controlecp::administrador.repositorio.tabulacion', compact(
            'encuestas',
            'encuestaActual',
            'filtrosDisponibles',
            'resultados'
        )); // Renderiza la tabulación y entrega la selección, filtros y resultados.
    }

    /**
     * Endpoint AJAX para cargar resultados y filtros dinámicamente.
     */
    public function datos(Request $request) // datos(): Calcula y devuelve resultados y filtros en JSON; se usa en las actualizaciones AJAX de la tabulación.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Revisa el permiso antes de entregar información por AJAX.
            return response()->json(['error' => 'No autorizado'], 403); // Devuelve JSON con estado HTTP 403 cuando no está autorizado.
        }

        $encuestas = $this->tabulacionService->obtenerEncuestasDisponibles(); // Obtiene la lista que servirá para validar el identificador.
        $datos = $request->validate([ // Valida el identificador requerido antes de consultar la encuesta.
            'id_encuesta' => ['required', 'integer', Rule::in($encuestas->pluck('id_encuesta')->all())],
        ]);
        $idEncuesta = (int) $datos['id_encuesta']; // Convierte el valor validado en entero para las consultas del servicio.
        $filtrosDisponibles = $this->tabulacionService->obtenerFiltrosParaEncuesta($idEncuesta); // Carga las opciones que pueden filtrarse.
        $filtros = $this->validarFiltros($request, $filtrosDisponibles); // Acepta únicamente fechas, actividades y fichas permitidas.
        $resultados = $this->tabulacionService->calcularResultados($idEncuesta, $filtros); // Calcula la información para la encuesta seleccionada.

        return response()->json([
            'resultados' => $resultados,
            'filtros_disponibles' => $filtrosDisponibles,
        ]); // Entrega los datos en JSON para que la interfaz actualice la página.
    }

    /**
     * 8. Exportación a PDF con filtros activos usando barryvdh/laravel-dompdf.
     */
    public function exportarPdf(Request $request) // exportarPdf(): Genera el informe PDF con filtros activos; se usa al solicitar la descarga del reporte.
    {
        if ($res = $this->verificarAccesoAdmin()) { // Bloquea la exportación si el usuario no es administrador.
            return $res;
        }

        $encuestas = $this->tabulacionService->obtenerEncuestasDisponibles(); // Recupera encuestas válidas para comprobar el filtro.
        $datos = $request->validate([ // Exige que el identificador pertenezca a la lista disponible.
            'id_encuesta' => ['required', 'integer', Rule::in($encuestas->pluck('id_encuesta')->all())],
        ]);

        $idEncuesta = (int) $datos['id_encuesta']; // Convierte el dato ya validado a entero.
        $filtrosDisponibles = $this->tabulacionService->obtenerFiltrosParaEncuesta($idEncuesta); // Consulta valores válidos para cada filtro.
        $filtros = $this->validarFiltros($request, $filtrosDisponibles); // Comprueba fechas, actividad y ficha antes del cálculo.

        $resultados = $this->tabulacionService->calcularResultados($idEncuesta, $filtros); // Obtiene los datos que formarán el informe.
        $encuesta = $resultados['encuesta']; // Separa la encuesta para pasarla a la plantilla PDF.

        if ($resultados['consolidado']['total_respuestas'] === 0) { // Evita descargar un informe sin respuestas. | Si no hay respuestas registradas
            return redirect()
                ->back()
                ->with('warning', 'No es posible exportar el informe porque la encuesta no tiene respuestas registradas con los filtros seleccionados.'); // Regresa con una advertencia visible.
        }

        $nombreActividad = null;
        if (! empty($filtros['id_actividad'])) { // Busca el nombre solo si se aplicó filtro de actividad.
            $actividad = $filtrosDisponibles['actividades']->firstWhere('id', $filtros['id_actividad']); // Encuentra la opción correspondiente dentro de la colección disponible.
            $nombreActividad = $actividad ? "Actividad: {$actividad->nombre}" : null; // Prepara la etiqueta o deja el valor vacío si no existe.
        }

        $codigoFicha = null;
        if (! empty($filtros['id_ficha'])) { // Busca el código solo si se seleccionó una ficha.
            $ficha = $filtrosDisponibles['fichas']->firstWhere('id', $filtros['id_ficha']); // Localiza la ficha en la colección filtrable.
            $codigoFicha = $ficha ? "Ficha: {$ficha->code}" : null; // Forma la etiqueta que aparecerá en el PDF.
        }

        $fechaGeneracion = Carbon::now()->format('d/m/Y H:i'); // Obtiene fecha y hora legibles para mostrar en el documento.
        $slugEncuesta = Str::slug($encuesta->nombre) ?: 'encuesta'; // Convierte el nombre a texto seguro para usarlo en el archivo.
        $fechaNombre = Carbon::now()->format('Ymd'); // Genera una fecha compacta para el nombre descargable.
        $nombreDescarga = "tabulacion_{$slugEncuesta}_{$fechaNombre}.pdf"; // Construye el nombre final del archivo PDF.

        $pdf = Pdf::loadView('controlecp::administrador.repositorio.tabulacion_pdf', compact(
            'encuesta',
            'resultados',
            'filtros',
            'nombreActividad',
            'codigoFicha',
            'fechaGeneracion'
        )); // Renderiza la plantilla con los resultados y filtros como documento PDF.

        $pdf->setPaper('a4', 'portrait'); // Configura tamaño A4 y orientación vertical. | Configuración de página A4 con márgenes adecuados

        return $pdf->download($nombreDescarga); // Envía el PDF como descarga con el nombre construido.
    }

    private function validarFiltros(Request $request, array $filtrosDisponibles): array // validarFiltros(): Valida fechas, actividad y ficha contra opciones disponibles; se usa antes de calcular o exportar resultados.
    {
        return $request->validate([ // Valida los filtros y devuelve solo los campos que cumplen las reglas.
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'id_actividad' => [
                'nullable',
                'integer',
                Rule::in($filtrosDisponibles['actividades']->pluck('id')->all()),
            ],
            'id_ficha' => [
                'nullable',
                'integer',
                Rule::in($filtrosDisponibles['fichas']->pluck('id')->all()),
            ],
        ], [ // Personaliza el mensaje de error para la regla de fecha final.
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]); // Laravel detiene la petición si una fecha u opción no es válida.
    }
}
