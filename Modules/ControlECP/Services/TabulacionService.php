<?php

namespace Modules\ControlECP\Services;

// Carbon permite convertir y mostrar las fechas de las respuestas.
use Carbon\Carbon;
// Collection identifica una lista de modelos de Laravel.
use Illuminate\Support\Collection;
// DB permite construir consultas SQL con el Query Builder.
use Illuminate\Support\Facades\DB;
// Actividad representa las actividades del módulo ControlECP.
use Modules\ControlECP\Entities\Actividad;
// Encuesta representa una encuesta con sus preguntas y opciones.
use Modules\ControlECP\Entities\Encuesta;

// Esta clase consulta y organiza los resultados usados por la tabulación web y PDF.
class TabulacionService
{
    // Busca las encuestas relacionadas con actividades o que ya tienen respuestas registradas.
    public function obtenerEncuestasDisponibles(): Collection
    {
        // Inicia una consulta Eloquent sobre las encuestas.
        return Encuesta::query()
            // Agrupa dos maneras de considerar una encuesta disponible.
            ->where(function ($query) {
                // Incluye encuestas que ya tienen al menos una actividad relacionada.
                $query->whereHas('actividades')
                    // También incluye encuestas con respuestas aunque no tengan actividad visible.
                    ->orWhereExists(function ($subquery) {
                        // Selecciona un valor constante para comprobar que existe una fila.
                        $subquery->selectRaw('1')
                            // Consulta las actividades de ControlECP usando el alias a.
                            ->from('controlecp_actividades as a')
                            // Une las actividades con sus cronogramas.
                            ->join('cronograma as c', 'c.id_actividad', '=', 'a.id')
                            // Une los cronogramas con las respuestas asociadas.
                            ->join('encuesta_respuestas as er', 'er.id_cronograma', '=', 'c.id_cronograma')
                            // Relaciona la actividad consultada con la encuesta actual.
                            ->whereColumn('a.id_encuesta', 'encuestas.id_encuesta');
                    });
            })
            // Ordena alfabéticamente por el nombre de la encuesta.
            ->orderBy('nombre')
            // Ejecuta la consulta y devuelve los modelos encontrados.
            ->get();
    }

    // Obtiene las actividades y fichas que se pueden usar como filtros para una encuesta.
    public function obtenerFiltrosParaEncuesta(int $idEncuesta): array
    {
        // Inicia la consulta de actividades pertenecientes a la encuesta indicada.
        $actividades = Actividad::query()
            // Filtra actividades por el id de la encuesta.
            ->where('id_encuesta', $idEncuesta)
            // Limita las columnas obtenidas a id, número y nombre.
            ->select('id', 'numero', 'nombre')
            // Ordena las actividades por su número.
            ->orderBy('numero')
            // Ejecuta la consulta y obtiene los resultados.
            ->get();

        // Consulta cronogramas, actividades y cursos para obtener las fichas relacionadas.
        $fichas = DB::table('cronograma as c')
            // Une cada cronograma con su actividad de ControlECP.
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            // Une el id de ficha del cronograma con el curso correspondiente.
            ->join('courses as co', 'co.id', '=', 'c.id_ficha')
            // Limita las fichas a actividades de la encuesta elegida.
            ->where('a.id_encuesta', $idEncuesta)
            // Obtiene las columnas necesarias para mostrar el filtro de ficha.
            ->select('co.id', 'co.code', 'co.status')
            // Evita mostrar la misma ficha más de una vez.
            ->distinct()
            // Ordena las fichas por su código.
            ->orderBy('co.code')
            // Ejecuta la consulta y recupera las fichas.
            ->get();

        // Devuelve juntas las listas que necesita la interfaz de filtros.
        return [
            // Lista de actividades relacionadas con la encuesta.
            'actividades' => $actividades,
            // Lista de fichas relacionadas con las actividades.
            'fichas' => $fichas,
        ];
    }

    // Calcula indicadores y resultados por pregunta para una encuesta y filtros opcionales.
    public function calcularResultados(int $idEncuesta, array $filtros = []): array
    {
        // Busca la encuesta y carga de antemano sus preguntas y opciones para evitar consultas adicionales.
        $encuesta = Encuesta::with(['preguntas.opciones'])->findOrFail($idEncuesta);
        // Consulta las actividades asociadas a la encuesta.
        $actividades = Actividad::query()
            // Limita las actividades a la encuesta elegida.
            ->where('id_encuesta', $idEncuesta)
            // Ordena las actividades por número.
            ->orderBy('numero')
            // Ejecuta la consulta.
            ->get();

        // Convierte el filtro de actividad a entero o deja null cuando no se eligió.
        $idActividad = ! empty($filtros['id_actividad']) ? (int) $filtros['id_actividad'] : null;
        // Convierte el filtro de ficha a entero o deja null si no está activo.
        $idFicha = ! empty($filtros['id_ficha']) ? (int) $filtros['id_ficha'] : null;
        // Lee la fecha inicial del filtro, si fue indicada.
        $fechaDesde = $filtros['fecha_desde'] ?? null;
        // Lee la fecha final del filtro, si fue indicada.
        $fechaHasta = $filtros['fecha_hasta'] ?? null;
        // Si hay actividad elegida, prepara una lista con solo ese id.
        $activityIds = $idActividad !== null
            // Usa el id seleccionado para que el resultado se limite a esa actividad.
            ? [$idActividad]
            // Si no se eligió actividad, usa los ids de todas las actividades de la encuesta.
            : $actividades->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Inicializa el arreglo que guardará el total de respuestas por actividad.
        $respuestasPorActividad = [];
        // Consulta la cantidad de respuestas distintas agrupada por actividad.
        foreach ($this->baseRespuestas($idEncuesta, $idActividad, $idFicha, $fechaDesde, $fechaHasta)
            // Incluye el id de la actividad en cada resultado agrupado.
            ->select('a.id as id_actividad')
            // Cuenta respuestas únicas para no duplicarlas por los joins.
            ->selectRaw('COUNT(DISTINCT er.id_encuesta_respuesta) as total')
            // Agrupa los resultados usando el id de la actividad.
            ->groupBy('a.id')
            // Ejecuta la consulta agregada.
            ->get() as $row) {
            // Guarda el total como entero, indexado por id de actividad.
            $respuestasPorActividad[(int) $row->id_actividad] = (int) $row->total;
        }

        // Cuenta la matrícula activa de la ficha por cada cronograma; ambas tablas se consultan en modo lectura.
        // Prepara un conteo de aprendices activos por cada ficha/c curso.
        $aprendicesPorFicha = DB::table('apprentices')
            // Excluye aprendices con borrado lógico.
            ->whereNull('deleted_at')
            // Selecciona la ficha a la que pertenece cada grupo de aprendices.
            ->select('course_id')
            // Cuenta aprendices distintos en cada ficha.
            ->selectRaw('COUNT(DISTINCT id) as total')
            // Agrupa el conteo por ficha.
            ->groupBy('course_id');

        // Inicializa el arreglo de convocados calculados por actividad.
        $convocadosPorActividad = [];
        // Consulta los cronogramas y suma la matrícula correspondiente a cada actividad.
        foreach ($this->baseCronogramas($idEncuesta, $idActividad, $idFicha)
            // Incluye el total agrupado de aprendices por ficha, si existe.
            ->leftJoinSub($aprendicesPorFicha, 'ap', 'ap.course_id', '=', 'c.id_ficha')
            // Selecciona el id de la actividad como índice del resultado.
            ->select('a.id as id_actividad')
            // Suma los aprendices; COALESCE convierte el resultado nulo en cero.
            ->selectRaw('COALESCE(SUM(ap.total), 0) as total')
            // Agrupa los convocados por actividad.
            ->groupBy('a.id')
            // Ejecuta la consulta.
            ->get() as $row) {
            // Guarda el total de convocados como entero por actividad.
            $convocadosPorActividad[(int) $row->id_actividad] = (int) $row->total;
        }

        // Prepara la consulta base de detalles de respuesta aplicando los filtros compartidos.
        $detalles = $this->baseDetalles($idEncuesta, $idActividad, $idFicha, $fechaDesde, $fechaHasta);

        // Inicializa el arreglo para contar respuestas de cada pregunta en cada actividad.
        $respuestasPorPregunta = [];
        // Agrupa los detalles por actividad y pregunta para contar respuestas.
        foreach ((clone $detalles)
            // Selecciona los identificadores de actividad y pregunta.
            ->select('a.id as id_actividad', 'p.id_pregunta')
            // Cuenta los detalles existentes para cada grupo.
            ->selectRaw('COUNT(erd.id_encuesta_respuesta_detalle) as total')
            // Agrupa por actividad y pregunta.
            ->groupBy('a.id', 'p.id_pregunta')
            // Ejecuta la consulta.
            ->get() as $row) {
            // Almacena el total en la posición actividad/pregunta.
            $respuestasPorPregunta[(int) $row->id_actividad][(int) $row->id_pregunta] = (int) $row->total;
        }

        // Inicializa el arreglo para contar cada opción seleccionada.
        $conteosOpciones = [];
        // Cuenta los detalles que tienen una opción seleccionada, por pregunta y actividad.
        foreach ((clone $detalles)
            // Descarta detalles sin id de opción.
            ->whereNotNull('erd.id_opcion_pregunta')
            // Selecciona actividad, pregunta y opción.
            ->select('a.id as id_actividad', 'p.id_pregunta', 'erd.id_opcion_pregunta')
            // Cuenta las selecciones de cada opción.
            ->selectRaw('COUNT(*) as total')
            // Agrupa el conteo por actividad, pregunta y opción.
            ->groupBy('a.id', 'p.id_pregunta', 'erd.id_opcion_pregunta')
            // Ejecuta la consulta agrupada.
            ->get() as $row) {
            // Guarda el total como entero en el arreglo anidado por actividad, pregunta y opción.
            $conteosOpciones[(int) $row->id_actividad][(int) $row->id_pregunta][(int) $row->id_opcion_pregunta] = (int) $row->total;
        }

        // Inicializa el arreglo que contará las respuestas de escala del uno al cinco.
        $conteosEscala = [];
        // Consulta los valores válidos de las preguntas de escala.
        foreach ((clone $detalles)
            // Limita las filas a preguntas cuyo tipo sea Escala.
            ->where('p.tipo', 'Escala')
            // Acepta únicamente los valores entre uno y cinco.
            ->whereIn('erd.valor', ['1', '2', '3', '4', '5'])
            // Selecciona actividad, pregunta y valor de escala.
            ->select('a.id as id_actividad', 'p.id_pregunta', 'erd.valor')
            // Cuenta las respuestas para cada valor.
            ->selectRaw('COUNT(*) as total')
            // Agrupa el conteo por actividad, pregunta y valor.
            ->groupBy('a.id', 'p.id_pregunta', 'erd.valor')
            // Ejecuta la consulta.
            ->get() as $row) {
            // Guarda el conteo como entero, indexado por actividad, pregunta y valor.
            $conteosEscala[(int) $row->id_actividad][(int) $row->id_pregunta][(int) $row->valor] = (int) $row->total;
        }

        // Inicializa el arreglo de promedios de escala calculados por actividad y pregunta.
        $promediosEscala = [];
        // Calcula el promedio SQL de respuestas válidas para cada pregunta de escala.
        foreach ((clone $detalles)
            // Limita las filas a preguntas de tipo Escala.
            ->where('p.tipo', 'Escala')
            // Ignora valores que no estén dentro del rango permitido.
            ->whereIn('erd.valor', ['1', '2', '3', '4', '5'])
            // Selecciona actividad y pregunta para identificar cada promedio.
            ->select('a.id as id_actividad', 'p.id_pregunta')
            // AVG calcula el promedio después de convertir el valor a decimal.
            ->selectRaw('AVG(CAST(erd.valor AS DECIMAL(10, 2))) as promedio')
            // Agrupa para obtener un promedio separado por actividad y pregunta.
            ->groupBy('a.id', 'p.id_pregunta')
            // Ejecuta la consulta.
            ->get() as $row) {
            // Guarda el promedio como número decimal en el arreglo.
            $promediosEscala[(int) $row->id_actividad][(int) $row->id_pregunta] = (float) $row->promedio;
        }

        // Inicializa el arreglo que contendrá las respuestas abiertas.
        $respuestasTexto = [];
        // Recupera respuestas no vacías de las preguntas de tipo Texto.
        foreach ((clone $detalles)
            // Selecciona solamente preguntas de respuesta abierta.
            ->where('p.tipo', 'Texto')
            // Excluye valores nulos.
            ->whereNotNull('erd.valor')
            // Excluye cadenas vacías.
            ->where('erd.valor', '!=', '')
            // Recupera actividad, pregunta, texto y fecha de cada respuesta.
            ->select('a.id as id_actividad', 'p.id_pregunta', 'erd.valor', 'er.fecha')
            // Ordena las respuestas de la más reciente a la más antigua.
            ->orderByDesc('er.fecha')
            // Ejecuta la consulta.
            ->get() as $row) {
            // Agrega cada respuesta al grupo de actividad y pregunta correspondiente.
            $respuestasTexto[(int) $row->id_actividad][(int) $row->id_pregunta][] = [
                // Conserva el contenido escrito por el aprendiz.
                'texto' => $row->valor,
                // Usa una etiqueta general sin exponer datos personales del encuestado.
                'autor' => 'Aprendiz',
                // Convierte la fecha a formato día/mes/año y hora, o la deja vacía si no existe.
                'fecha' => $row->fecha ? Carbon::parse($row->fecha)->format('d/m/Y H:i') : '',
                // Mantiene la fecha original temporalmente para ordenar las respuestas después.
                'fecha_orden' => $row->fecha,
            ];
        }

        // Construye indicadores y resultados conjuntos para todas las actividades seleccionadas.
        $consolidado = $this->crearDatosSubconjunto(
            // Entrega los ids de actividades que deben formar parte del consolidado.
            $activityIds,
            // Pasa las preguntas cargadas de la encuesta.
            $encuesta->preguntas,
            // Pasa los conteos de respuestas por actividad.
            $respuestasPorActividad,
            // Pasa los conteos de aprendices convocados por actividad.
            $convocadosPorActividad,
            // Pasa las respuestas agregadas por pregunta.
            $respuestasPorPregunta,
            // Pasa las cantidades de selecciones por opción.
            $conteosOpciones,
            // Pasa la distribución de valores de escala.
            $conteosEscala,
            // Pasa los promedios de escala.
            $promediosEscala,
            // Pasa las respuestas abiertas.
            $respuestasTexto
        );

        // Inicializa la lista que almacenará resultados independientes por actividad.
        $porActividad = [];
        // Separa resultados cuando hay varias actividades y no se filtró una sola.
        if ($actividades->count() > 1 && $idActividad === null) {
            // Recorre las actividades para generar un subconjunto por cada una.
            foreach ($actividades as $actividad) {
                // Convierte el id de actividad a entero para usarlo en los arreglos de resultados.
                $activityId = (int) $actividad->id;
                // Agrega al resultado la actividad y sus indicadores individuales.
                $porActividad[] = [
                    // Conserva el modelo de actividad para mostrar su nombre y otros datos.
                    'actividad' => $actividad,
                    // Calcula indicadores usando solo el id de esta actividad.
                    'datos' => $this->crearDatosSubconjunto(
                        // El subconjunto contiene únicamente la actividad actual.
                        [$activityId],
                        // Reutiliza las preguntas de la encuesta.
                        $encuesta->preguntas,
                        // Reutiliza los conteos de respuestas ya consultados.
                        $respuestasPorActividad,
                        // Reutiliza los conteos de convocados ya consultados.
                        $convocadosPorActividad,
                        // Reutiliza los conteos por pregunta.
                        $respuestasPorPregunta,
                        // Reutiliza los conteos de opciones.
                        $conteosOpciones,
                        // Reutiliza los conteos de escala.
                        $conteosEscala,
                        // Reutiliza los promedios de escala.
                        $promediosEscala,
                        // Reutiliza las respuestas de texto.
                        $respuestasTexto
                    ),
                ];
            }
        }

        // Devuelve encuesta, filtros aplicados, consolidado y resultados por actividad.
        return [
            // Datos generales de la encuesta seleccionada.
            'encuesta' => $encuesta,
            // Indica si la encuesta está relacionada con más de una actividad.
            'tiene_multiples_actividades' => $actividades->count() > 1,
            // Devuelve los filtros normalizados que se aplicaron en los cálculos.
            'filtros' => [
                // Id de actividad elegido, o null si se incluyeron todas.
                'id_actividad' => $idActividad,
                // Id de ficha elegido, o null si no se filtró por ficha.
                'id_ficha' => $idFicha,
                // Fecha inicial que se aplicó, si existe.
                'fecha_desde' => $fechaDesde,
                // Fecha final que se aplicó, si existe.
                'fecha_hasta' => $fechaHasta,
            ],
            // Resultados calculados para el conjunto de actividades seleccionado.
            'consolidado' => $consolidado,
            // Resultados separados por actividad cuando corresponde.
            'por_actividad' => $porActividad,
        ];
    }

    // Construye la consulta base de respuestas y agrega los filtros elegidos.
    private function baseRespuestas(
        // Recibe el id de la encuesta que se está tabulando.
        int $idEncuesta,
        // Recibe un id de actividad o null para incluir todas.
        ?int $idActividad,
        // Recibe un id de ficha o null para incluir todas.
        ?int $idFicha,
        // Recibe la fecha inicial o null cuando no se filtra por fecha.
        ?string $fechaDesde,
        // Recibe la fecha final o null cuando no se filtra por fecha.
        ?string $fechaHasta
    ) {
        // Inicia la consulta sobre las respuestas y asigna el alias er.
        return DB::table('encuesta_respuestas as er')
            // Une cada respuesta con el cronograma que la originó.
            ->join('cronograma as c', 'c.id_cronograma', '=', 'er.id_cronograma')
            // Une el cronograma con la actividad de ControlECP.
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            // Limita la consulta a actividades asociadas con esta encuesta.
            ->where('a.id_encuesta', $idEncuesta)
            // Añade filtro de actividad solo si el id fue proporcionado.
            ->when($idActividad, fn ($query) => $query->where('a.id', $idActividad))
            // Añade filtro de ficha solo si el id fue proporcionado.
            ->when($idFicha, fn ($query) => $query->where('c.id_ficha', $idFicha))
            // Incluye respuestas de la fecha inicial en adelante si esa fecha existe.
            ->when($fechaDesde, fn ($query) => $query->whereDate('er.fecha', '>=', $fechaDesde))
            // Incluye respuestas hasta la fecha final si esa fecha existe.
            ->when($fechaHasta, fn ($query) => $query->whereDate('er.fecha', '<=', $fechaHasta));
    }

    // Construye la consulta base de cronogramas con los filtros de encuesta, actividad y ficha.
    private function baseCronogramas(int $idEncuesta, ?int $idActividad, ?int $idFicha)
    {
        // Consulta la tabla de cronogramas usando el alias c.
        return DB::table('cronograma as c')
            // Une cada cronograma con su actividad de ControlECP.
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            // Deja solo actividades asociadas a la encuesta indicada.
            ->where('a.id_encuesta', $idEncuesta)
            // Aplica el filtro de actividad si se eligió una.
            ->when($idActividad, fn ($query) => $query->where('a.id', $idActividad))
            // Aplica el filtro de ficha si se eligió una.
            ->when($idFicha, fn ($query) => $query->where('c.id_ficha', $idFicha));
    }

    // Construye una consulta de detalles de respuesta con sus relaciones y filtros.
    private function baseDetalles(
        // Id de la encuesta cuyos detalles se consultan.
        int $idEncuesta,
        // Actividad opcional para limitar resultados.
        ?int $idActividad,
        // Ficha opcional para limitar resultados.
        ?int $idFicha,
        // Fecha inicial opcional para limitar respuestas.
        ?string $fechaDesde,
        // Fecha final opcional para limitar respuestas.
        ?string $fechaHasta
    ) {
        // Inicia la consulta de detalles asignando el alias erd.
        return DB::table('encuesta_respuesta_detalle as erd')
            // Une cada detalle con la respuesta general que lo contiene.
            ->join('encuesta_respuestas as er', 'er.id_encuesta_respuesta', '=', 'erd.id_encuesta_respuesta')
            // Une la respuesta general con el cronograma relacionado.
            ->join('cronograma as c', 'c.id_cronograma', '=', 'er.id_cronograma')
            // Une el cronograma con la actividad de ControlECP.
            ->join('controlecp_actividades as a', 'a.id', '=', 'c.id_actividad')
            // Une el detalle con su pregunta y restringe esa pregunta a la encuesta seleccionada.
            ->join('preguntas as p', function ($join) use ($idEncuesta) {
                // Relaciona el id de pregunta del detalle con el id principal de preguntas.
                $join->on('p.id_pregunta', '=', 'erd.id_pregunta')
                    // Asegura que la pregunta pertenezca a la encuesta consultada.
                    ->where('p.id_encuesta', '=', $idEncuesta);
            })
            // Asegura que la actividad de la respuesta corresponda a la encuesta.
            ->where('a.id_encuesta', $idEncuesta)
            // Aplica el filtro de actividad cuando se proporcionó.
            ->when($idActividad, fn ($query) => $query->where('a.id', $idActividad))
            // Aplica el filtro de ficha cuando se proporcionó.
            ->when($idFicha, fn ($query) => $query->where('c.id_ficha', $idFicha))
            // Aplica el límite inicial de fecha cuando se proporcionó.
            ->when($fechaDesde, fn ($query) => $query->whereDate('er.fecha', '>=', $fechaDesde))
            // Aplica el límite final de fecha cuando se proporcionó.
            ->when($fechaHasta, fn ($query) => $query->whereDate('er.fecha', '<=', $fechaHasta));
    }

    // Calcula los indicadores y los resultados detallados de un grupo de actividades.
    private function crearDatosSubconjunto(
        // Lista de actividades que se combinan en este cálculo.
        array $activityIds,
        // Preguntas de la encuesta que se deben presentar.
        Collection $preguntas,
        // Conteos de respuestas generales por actividad.
        array $respuestasPorActividad,
        // Conteos de aprendices convocados por actividad.
        array $convocadosPorActividad,
        // Conteos de respuestas por actividad y pregunta.
        array $respuestasPorPregunta,
        // Conteos por actividad, pregunta y opción elegida.
        array $conteosOpciones,
        // Conteos por actividad, pregunta y valor de escala.
        array $conteosEscala,
        // Promedios por actividad y pregunta de escala.
        array $promediosEscala,
        // Respuestas abiertas agrupadas por actividad y pregunta.
        array $respuestasTexto
    ): array {
        // Suma las respuestas de las actividades incluidas en este subconjunto.
        $totalRespuestas = $this->sumarPorActividad($respuestasPorActividad, $activityIds);
        // Suma los convocados de las actividades incluidas en este subconjunto.
        $totalConvocados = $this->sumarPorActividad($convocadosPorActividad, $activityIds);
        // Inicializa la lista de resultados de preguntas.
        $preguntasResultados = [];
        // Inicializa el contador de preguntas que tienen alguna respuesta.
        $preguntasConRespuesta = 0;
        // Inicializa la suma de valores válidos de todas las preguntas de escala.
        $sumaEscala = 0;
        // Inicializa la cantidad de respuestas válidas de escala.
        $totalEscala = 0;

        // Procesa una por una las preguntas cargadas de la encuesta.
        foreach ($preguntas as $pregunta) {
            // Convierte el identificador de la pregunta a entero.
            $questionId = (int) $pregunta->id_pregunta;
            // Suma respuestas de esta pregunta dentro de las actividades seleccionadas.
            $cantidadRespuestas = $this->sumarPregunta($respuestasPorPregunta, $activityIds, $questionId);
            // Cuenta la pregunta como evaluada si tiene una o más respuestas.
            if ($cantidadRespuestas > 0) {
                $preguntasConRespuesta++;
            }

            // Prepara los datos comunes y los espacios de resultados específicos por tipo.
            $resultadoPregunta = [
                // Guarda el identificador de la pregunta.
                'id_pregunta' => $questionId,
                // Guarda el texto que verá el usuario.
                'texto' => $pregunta->texto,
                // Guarda el tipo: opción múltiple, escala o texto.
                'tipo' => $pregunta->tipo,
                // Guarda su posición de presentación.
                'orden' => $pregunta->orden,
                // Guarda cuántos detalles respondieron esta pregunta.
                'cantidad_respuestas' => $cantidadRespuestas,
                // Inicializa las opciones para preguntas de selección.
                'opciones' => [],
                // Inicializa la distribución para preguntas de escala.
                'escala' => [],
                // Inicializa el promedio como null si aún no aplica.
                'promedio_escala' => null,
                // Inicializa la lista de respuestas abiertas.
                'respuestas_texto' => [],
            ];

            // Calcula los conteos y porcentajes para preguntas de opción múltiple.
            if ($pregunta->tipo === 'Opcion_multiple') {
                // Recorre las opciones definidas para la pregunta actual.
                foreach ($pregunta->opciones as $opcion) {
                    // Suma las selecciones de esta opción en las actividades del subconjunto.
                    $totalOpcion = $this->sumarOpcion(
                        // Conteos previamente agregados por actividad.
                        $conteosOpciones,
                        // Actividades que forman parte de este resultado.
                        $activityIds,
                        // Id de la pregunta actual.
                        $questionId,
                        // Id de la opción actual.
                        (int) $opcion->id_opcion_pregunta
                    );
                    // Agrega al resultado el conteo y el porcentaje de la opción actual.
                    $resultadoPregunta['opciones'][] = [
                        // Guarda el id de la opción.
                        'id_opcion_pregunta' => (int) $opcion->id_opcion_pregunta,
                        // Guarda el texto visible de la opción.
                        'texto' => $opcion->texto,
                        // Guarda cuántas personas eligieron esta opción.
                        'total' => $totalOpcion,
                        // Calcula el porcentaje sobre las respuestas de la pregunta; evita dividir por cero.
                        'porcentaje' => $cantidadRespuestas > 0
                            ? round(($totalOpcion / $cantidadRespuestas) * 100, 1)
                            : 0,
                    ];
                }
            // Calcula la distribución y el promedio para preguntas de escala.
            } elseif ($pregunta->tipo === 'Escala') {
                // Inicializa la cantidad de valores válidos de esta pregunta.
                $totalValoresValidos = 0;
                // Inicializa la suma ponderada de los valores de escala.
                $sumaPregunta = 0;
                // Recorre cada valor permitido de la escala, del uno al cinco.
                for ($valor = 1; $valor <= 5; $valor++) {
                    // Cuenta cuántas respuestas tienen el valor actual.
                    $cantidad = $this->sumarEscala($conteosEscala, $activityIds, $questionId, $valor);
                    // Añade el conteo actual al total de respuestas válidas.
                    $totalValoresValidos += $cantidad;
                    // Acumula valor multiplicado por cantidad para calcular el promedio ponderado.
                    $sumaPregunta += $valor * $cantidad;
                    // Inicializa los datos de este valor de escala.
                    $resultadoPregunta['escala'][$valor] = [
                        // Guarda el número de la escala.
                        'valor' => $valor,
                        // Guarda cuántas respuestas recibieron este valor.
                        'total' => $cantidad,
                        // Inicializa el porcentaje, que se calcula en el siguiente recorrido.
                        'porcentaje' => 0,
                    ];
                }

                // Vuelve a recorrer la escala para calcular el porcentaje de cada valor.
                for ($valor = 1; $valor <= 5; $valor++) {
                    // Calcula el porcentaje sobre respuestas válidas o asigna cero si no hay.
                    $resultadoPregunta['escala'][$valor]['porcentaje'] = $totalValoresValidos > 0
                        ? round(($resultadoPregunta['escala'][$valor]['total'] / $totalValoresValidos) * 100, 1)
                        : 0;
                }

                // Calcula y agrega el promedio solo si hay respuestas válidas.
                if ($totalValoresValidos > 0) {
                    // Reutiliza el promedio SQL para actividad individual; para consolidado usa suma ponderada.
                    $promedio = count($activityIds) === 1
                        ? ($promediosEscala[$activityIds[0]][$questionId] ?? ($sumaPregunta / $totalValoresValidos))
                        : $sumaPregunta / $totalValoresValidos;
                    // Guarda el promedio redondeado a un decimal.
                    $resultadoPregunta['promedio_escala'] = round($promedio, 1);
                    // Añade la suma de esta pregunta para el promedio general.
                    $sumaEscala += $sumaPregunta;
                    // Añade la cantidad de respuestas para el promedio general.
                    $totalEscala += $totalValoresValidos;
                }
            // Prepara la lista de respuestas para preguntas abiertas.
            } elseif ($pregunta->tipo === 'Texto') {
                // Recorre todas las actividades incluidas en este subconjunto.
                foreach ($activityIds as $activityId) {
                    // Une las respuestas de esta actividad con las ya reunidas.
                    $resultadoPregunta['respuestas_texto'] = array_merge(
                        // Conserva las respuestas de actividades anteriores.
                        $resultadoPregunta['respuestas_texto'],
                        // Añade las respuestas actuales, o una lista vacía si no hay.
                        $respuestasTexto[$activityId][$questionId] ?? []
                    );
                }
                // Ordena las respuestas abiertas desde la fecha más reciente.
                usort(
                    // Arreglo que se reordena directamente.
                    $resultadoPregunta['respuestas_texto'],
                    // Compara fechas como texto para dejar primero la fecha descendente.
                    fn ($left, $right) => strcmp((string) $right['fecha_orden'], (string) $left['fecha_orden'])
                );
                // Transforma las respuestas para quitar el campo interno usado solo para ordenar.
                $resultadoPregunta['respuestas_texto'] = array_map(
                    // Define qué hacer con cada respuesta.
                    function ($answer) {
                        // Elimina fecha_orden porque solo servía para organizar la lista.
                        unset($answer['fecha_orden']);

                        // Devuelve la respuesta ya limpia.
                        return $answer;
                    },
                    // Aplica la transformación a todas las respuestas.
                    $resultadoPregunta['respuestas_texto']
                );
            }

            // Añade el resultado completo de la pregunta a la lista final.
            $preguntasResultados[] = $resultadoPregunta;
        }

        // Calcula el promedio general de escalas o deja null si no hubo valores válidos.
        $promedioGeneral = $totalEscala > 0 ? round($sumaEscala / $totalEscala, 1) : null;
        // Calcula la participación porcentual o deja null cuando no hubo convocados.
        $tasaParticipacion = $totalConvocados > 0
            ? round(($totalRespuestas / $totalConvocados) * 100, 1)
            : null;

        // Devuelve los indicadores y la lista de resultados por pregunta.
        return [
            // Total de respuestas recibidas.
            'total_respuestas' => $totalRespuestas,
            // Total de aprendices convocados.
            'total_convocados' => $totalConvocados,
            // Cantidad de preguntas respondidas sobre el total de preguntas.
            'preguntas_evaluadas' => "{$preguntasConRespuesta} de {$preguntas->count()}",
            // Muestra la tasa calculada con signo de porcentaje o N/A.
            'tasa_participacion' => $tasaParticipacion !== null ? "{$tasaParticipacion}%" : 'N/A',
            // Muestra el promedio con escala de cinco o N/A si no hay preguntas de escala respondidas.
            'satisfaccion_promedio' => $promedioGeneral !== null
                ? number_format($promedioGeneral, 1).' / 5'
                : 'N/A',
            // Convierte el promedio sobre cinco a porcentaje, o null si no existe.
            'satisfaccion_porcentaje' => $promedioGeneral !== null
                ? round(($promedioGeneral / 5) * 100, 1)
                : null,
            // Devuelve los resultados detallados por pregunta.
            'preguntas' => $preguntasResultados,
        ];
    }

    // Suma los conteos de una lista de actividades.
    private function sumarPorActividad(array $conteos, array $activityIds): int
    {
        // Inicia el acumulador en cero.
        $total = 0;
        // Recorre los ids de actividad que participan en el resultado.
        foreach ($activityIds as $activityId) {
            // Suma el conteo existente; usa cero si esa actividad no tiene valor guardado.
            $total += $conteos[$activityId] ?? 0;
        }

        // Devuelve el total sumado.
        return $total;
    }

    // Suma las respuestas de una pregunta en varias actividades.
    private function sumarPregunta(array $conteos, array $activityIds, int $questionId): int
    {
        // Inicia el conteo acumulado de esta pregunta.
        $total = 0;
        // Recorre cada actividad del subconjunto.
        foreach ($activityIds as $activityId) {
            // Añade el conteo de la pregunta actual o cero si no hay respuestas.
            $total += $conteos[$activityId][$questionId] ?? 0;
        }

        // Devuelve la cantidad total de respuestas para la pregunta.
        return $total;
    }

    // Suma cuántas veces eligieron una opción en las actividades dadas.
    private function sumarOpcion(array $conteos, array $activityIds, int $questionId, int $optionId): int
    {
        // Inicia el total de selecciones en cero.
        $total = 0;
        // Recorre cada actividad incluida.
        foreach ($activityIds as $activityId) {
            // Acumula el conteo de la opción; usa cero cuando esa combinación no existe.
            $total += $conteos[$activityId][$questionId][$optionId] ?? 0;
        }

        // Devuelve la suma de selecciones de esa opción.
        return $total;
    }

    // Suma la cantidad de respuestas que tienen un valor concreto de escala.
    private function sumarEscala(array $conteos, array $activityIds, int $questionId, int $value): int
    {
        // Inicializa en cero la cantidad del valor de escala.
        $total = 0;
        // Recorre las actividades del resultado.
        foreach ($activityIds as $activityId) {
            // Suma el valor de escala de esa actividad/pregunta o cero si no existe.
            $total += $conteos[$activityId][$questionId][$value] ?? 0;
        }

        // Devuelve el total para el valor solicitado.
        return $total;
    }
}
