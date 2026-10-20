<?php

namespace Modules\ControlECP\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\Momento;
use Modules\ControlECP\Entities\ActividadEcp;

class MomentoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Momento 1: Autoconciencia ─────────────────────────
        $m1 = Momento::updateOrCreate(
            ['nombre' => 'Autoconciencia'],
            [
                'descripcion' => 'Introspección, reconocimiento de emociones, autorreflexión y construcción del proyecto de vida.',
                'icono'       => 'fa-brain',
                'color'       => '#39A900',
                'orden'       => 1,
                'estado'      => 'Activo',
            ]
        );

        $actividadesM1 = [
            ['01', 'Reconocimiento y Gestión Emocional',         '2 Horas', 120, 'Identificar la rueda de emociones y técnicas de autorregulación emocional en el contexto formativo del aprendiz SENA.', 'Taller vivencial con rueda de emociones de Plutchik, ejercicios de respiración guiada, diario emocional y retroalimentación grupal.'],
            ['02', 'Taller de Autorreflexión y Autoconcepto',    '2 Horas', 120, 'Explorar la percepción personal, fortalezas individuales y áreas de mejora desde una mirada formativa constructiva.', 'Cuestionario de autoevaluación de fortalezas (VIA), conversatorio en triadas, mapa de identidad personal y socialización plenaria.'],
            ['03', 'Proyecto de Vida y Sueños Integrales',       '3 Horas', 180, 'Diseñar metas personales y profesionales a corto, mediano y largo plazo alineadas con el perfil SENA.', 'Línea de vida individual, construcción de metas SMART, tablero de visión personal, presentación ante el grupo.'],
            ['04', 'Identificación de Fortalezas y Oportunidades','2 Horas', 120, 'Construir la Matriz DOFA personal aplicada al contexto laboral y formativo del entorno SENA.', 'Aplicación guiada de la Matriz DOFA personal, análisis de entorno con el instructor, plan de mejora individual.'],
            ['05', 'Expresión Asertiva de Sentimientos',         '2 Horas', 120, 'Desarrollar la capacidad de comunicar emociones de manera clara, respetuosa y asertiva en el entorno formativo.', 'Role-playing de situaciones cotidianas del SENA, técnica del "Mensaje Yo", juego de roles asertivo, retroalimentación grupal.'],
            ['06', 'Mapeo Corporal y Autocuidado',               '2 Horas', 120, 'Reconocer somatizaciones del estrés y promover hábitos de salud mental y autocuidado integral.', 'Ejercicio de mapeo corporal en silueta humana, identificación de tensiones, técnicas de relajación progresiva de Jacobson.'],
            ['07', 'Control del Estrés e Impulsos',              '1.5 Horas', 90, 'Aplicar técnicas de respiración consciente y mindfulness básico para el manejo del estrés en contextos académicos y laborales.', 'Ejercicio 4-7-8 de respiración, body-scan guiado, mindfulness de 5 minutos, registro de disparadores emocionales.'],
            ['08', 'Historia Personal y Raíces Culturales',      '2 Horas', 120, 'Valorar el origen cultural, la historia familiar y su impacto en la construcción de la identidad del aprendiz.', 'Árbol genealógico familiar, línea del tiempo personal con hitos culturales, socialización en círculo de aprendizaje.'],
            ['09', 'Principios, Valores y Ética Personal',       '2 Horas', 120, 'Alinear los valores personales con el entorno laboral, institucional y el perfil del egresado SENA.', 'Clasificación de valores con tarjetas (card sorting), dilemas éticos situacionales, construcción de código personal de ética.'],
            ['10', 'Compromiso Conmigo Mismo',                   '1 Hora',   60, 'Formalizar la carta de compromiso personal y el plan de acción formativo para la ruta ECP.', 'Redacción individual de carta de compromiso, firma simbólica grupal, lectura en voz alta y archivado en portafolio de evidencias.'],
        ];

        foreach ($actividadesM1 as $a) {
            ActividadEcp::updateOrCreate(
                ['momento_id' => $m1->id, 'numero' => $a[0]],
                ['nombre' => $a[1], 'duracion' => $a[2], 'duracion_min' => $a[3], 'objetivo' => $a[4], 'metodologia' => $a[5], 'estado' => 'Activo']
            );
        }

        // ── Momento 2: Convivencia ────────────────────────────
        $m2 = Momento::updateOrCreate(
            ['nombre' => 'Convivencia'],
            [
                'descripcion' => 'Fomento de la empatía, trabajo en equipo, escucha activa y construcción comunitaria.',
                'icono'       => 'fa-people-holding-hands',
                'color'       => '#0070B8',
                'orden'       => 2,
                'estado'      => 'Activo',
            ]
        );

        $actividadesM2 = [
            ['01', 'Círculos de Diálogo y Empatía',          '2 Horas',   120, 'Desarrollar la escucha activa, la empatía y el diálogo respetuoso en la comunidad formativa del Centro.', 'Círculo de paz restaurativa (talking piece), preguntas reflexivas guiadas, escucha activa en parejas, cierre con compromisos grupales.'],
            ['02', 'Dinámicas de Trabajo Colaborativo',      '2.5 Horas', 150, 'Fortalecer la confianza grupal y las habilidades de trabajo en equipo en contextos de alta diversidad.', 'Retos colaborativos (torre de espaguetis, puente de papel), análisis de roles en equipo (Belbin), reflexión sobre dinámicas grupales.'],
            ['03', 'Comunicación No Violenta (CNV)',         '3 Horas',   180, 'Aplicar el lenguaje integrador de Rosenberg para transformar patrones comunicativos agresivos o pasivos.', 'Estudio del modelo CNV (Observación-Sentimiento-Necesidad-Petición), práctica con situaciones reales del aula, video-foro CNV.'],
            ['04', 'Acuerdos de Convivencia y Respeto',     '2 Horas',   120, 'Construir colectivamente normas de convivencia grupales, pactos de respeto y protocolo de manejo de conflictos internos.', 'Lluvia de ideas sobre normas de convivencia, construcción participativa del reglamento interno, firma del pacto grupal.'],
        ];

        foreach ($actividadesM2 as $a) {
            ActividadEcp::updateOrCreate(
                ['momento_id' => $m2->id, 'numero' => $a[0]],
                ['nombre' => $a[1], 'duracion' => $a[2], 'duracion_min' => $a[3], 'objetivo' => $a[4], 'metodologia' => $a[5], 'estado' => 'Activo']
            );
        }

        // ── Momento 3: Resolución ─────────────────────────────
        $m3 = Momento::updateOrCreate(
            ['nombre' => 'Resolución'],
            [
                'descripcion' => 'Estrategias, herramientas y mediación pacífica para la gestión y transformación de conflictos.',
                'icono'       => 'fa-scale-balanced',
                'color'       => '#6A1B9A',
                'orden'       => 3,
                'estado'      => 'Activo',
            ]
        );

        $actividadesM3 = [
            ['01', 'Taller de Mediación Pacífica',               '3 Horas', 180, 'Desarrollar herramientas prácticas para intervenir como mediadores neutrales en conflictos del entorno formativo.', 'Estudio del proceso de mediación escolar, práctica de fases de mediación (pre-mediación, sesión conjunta, acuerdo), simulación supervisada.'],
            ['02', 'Negociación y Estrategias Ganar-Ganar',      '3 Horas', 180, 'Aplicar técnicas de negociación basada en intereses (Harvard) para llegar a acuerdos sostenibles y equitativos.', 'Módulo teórico del Método Harvard de Negociación, simulación de negociación en díadas, análisis de estrategias, retroalimentación.'],
            ['03', 'Estudio de Casos y Juego de Roles',          '2 Horas', 120, 'Simular situaciones reales de tensión en entornos laborales del SENA para desarrollar respuestas asertivas y pacíficas.', 'Análisis de casos reales anonimizados, asignación de roles (víctima, agresor, mediador, testigo), dramatización y reflexión grupal.'],
            ['04', 'Plan de Acción y Pactos de Paz',             '3 Horas', 180, 'Cerrar el proceso formativo ECP con evaluación integral, elaboración del plan de paz personal y certificación.', 'Evaluación reflexiva del proceso ECP, construcción del plan de paz personal, ceremonia de compromisos grupales y entrega de reconocimiento.'],
        ];

        foreach ($actividadesM3 as $a) {
            ActividadEcp::updateOrCreate(
                ['momento_id' => $m3->id, 'numero' => $a[0]],
                ['nombre' => $a[1], 'duracion' => $a[2], 'duracion_min' => $a[3], 'objetivo' => $a[4], 'metodologia' => $a[5], 'estado' => 'Activo']
            );
        }
    }
}
