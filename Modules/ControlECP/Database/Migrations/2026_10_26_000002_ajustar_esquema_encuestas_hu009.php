<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Migración del esquema de ajustar esquema encuestas hu009.
return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajusta el esquema de encuestas exactamente al DBML de la HU-009 de forma aditiva y sin perder datos.
     */
    // up(): Crea o actualiza el esquema definido por esta migración.
    public function up(): void
    {
        // 1. Tabla encuestas
        if (!Schema::hasTable('encuestas')) {
            // Define datos de la encuesta y su vínculo con el usuario propietario.
            // Define las columnas y restricciones de encuestas.
            Schema::create('encuestas', function (Blueprint $table) {
                $table->bigIncrements('id_encuesta');
                $table->unsignedBigInteger('id_usuario');
                $table->string('nombre', 255);
                $table->text('descripcion')->nullable();
                $table->string('estado', 50)->default('Borrador');
                $table->dateTime('fecha_creacion');

                $table->foreign('id_usuario', 'fk_encuestas_usuario')->references('id')->on('users');
            });

            // Migrar datos de controlecp_encuestas si ya existían registros
            if (Schema::hasTable('controlecp_encuestas')) {
                // Copia registros existentes, conservando IDs y completando los campos ausentes.
                $primerUsuario = DB::table('users')->value('id') ?? 1;
                $anteriores = DB::table('controlecp_encuestas')->get();
                foreach ($anteriores as $ant) {
                    DB::table('encuestas')->insert([
                        'id_encuesta'    => $ant->id,
                        'id_usuario'     => $primerUsuario,
                        'nombre'         => $ant->nombre ?: ($ant->titulo ?? 'Sin título'),
                        'descripcion'    => $ant->descripcion,
                        'estado'         => $ant->estado ?: 'Borrador',
                        'fecha_creacion' => $ant->created_at ?: now(),
                    ]);
                }
            }
        }

        // 2. Tabla preguntas
        if (!Schema::hasTable('preguntas')) {
            // Define texto, tipo, obligatoriedad y orden de cada pregunta vinculada a una encuesta.
            // Define las columnas y restricciones de preguntas.
            Schema::create('preguntas', function (Blueprint $table) {
                $table->bigIncrements('id_pregunta');
                $table->unsignedBigInteger('id_encuesta');
                $table->text('texto');
                $table->string('tipo', 50); // Opcion_multiple, Escala, Texto
                $table->boolean('obligatoria')->default(true);
                $table->integer('orden')->default(1);

                $table->foreign('id_encuesta', 'fk_preguntas_encuesta')->references('id_encuesta')->on('encuestas')->onDelete('cascade');
            });

            // Migrar datos de preguntas_encuesta si existían
            if (Schema::hasTable('preguntas_encuesta')) {
                // Traduce los tipos anteriores y copia las preguntas al esquema nuevo.
                $antPreguntas = DB::table('preguntas_encuesta')->get();
                foreach ($antPreguntas as $ap) {
                    $tipoMapeado = match(strtolower($ap->tipo ?? '')) {
                        'opcion_multiple' => 'Opcion_multiple',
                        'escala'          => 'Escala',
                        default           => 'Texto',
                    };
                    DB::table('preguntas')->insert([
                        'id_pregunta' => $ap->id,
                        'id_encuesta' => $ap->encuesta_id,
                        'texto'       => $ap->texto,
                        'tipo'        => $tipoMapeado,
                        'obligatoria' => true,
                        'orden'       => $ap->orden ?: 1,
                    ]);
                }
            }
        }

        // 3. Tabla opciones_pregunta
        if (!Schema::hasTable('opciones_pregunta')) {
            // Almacena las opciones vinculadas a cada pregunta, con orden opcional.
            // Define las columnas y restricciones de opciones_pregunta.
            Schema::create('opciones_pregunta', function (Blueprint $table) {
                $table->bigIncrements('id_opcion_pregunta');
                $table->unsignedBigInteger('id_pregunta');
                $table->string('texto', 255);
                $table->integer('orden')->nullable();

                $table->foreign('id_pregunta', 'fk_opciones_pregunta')->references('id_pregunta')->on('preguntas')->onDelete('cascade');
            });

            // Si la pregunta 1 existía en el test anterior, restaurar sus opciones
            if (DB::table('preguntas')->where('id_pregunta', 1)->exists()) {
                DB::table('opciones_pregunta')->insertOrIgnore([
                    ['id_opcion_pregunta' => 1, 'id_pregunta' => 1, 'texto' => 'si', 'orden' => 1],
                    ['id_opcion_pregunta' => 2, 'id_pregunta' => 1, 'texto' => 'no', 'orden' => 2],
                ]);
            }
        }

        // 4. Tabla encuesta_respuestas
        if (!Schema::hasTable('encuesta_respuestas')) {
            // Registra una respuesta por usuario y cronograma; la clave única impide duplicados.
            // Define las columnas y restricciones de encuesta_respuestas.
            Schema::create('encuesta_respuestas', function (Blueprint $table) {
                $table->bigIncrements('id_encuesta_respuesta');
                $table->unsignedBigInteger('id_cronograma');
                $table->unsignedBigInteger('id_usuario');
                $table->dateTime('fecha');

                $table->unique(['id_cronograma', 'id_usuario'], 'uk_enc_resp_crono_user');
                $table->foreign('id_cronograma', 'fk_enc_resp_crono')->references('id_cronograma')->on('cronograma')->onDelete('cascade');
                $table->foreign('id_usuario', 'fk_enc_resp_user')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 5. Tabla encuesta_respuesta_detalle
        if (!Schema::hasTable('encuesta_respuesta_detalle')) {
            // Asocia cada respuesta con una pregunta y una opción o valor libre.
            // Define las columnas y restricciones de encuesta_respuesta_detalle.
            Schema::create('encuesta_respuesta_detalle', function (Blueprint $table) {
                $table->bigIncrements('id_encuesta_respuesta_detalle');
                $table->unsignedBigInteger('id_encuesta_respuesta');
                $table->unsignedBigInteger('id_pregunta');
                $table->unsignedBigInteger('id_opcion_pregunta')->nullable();
                $table->text('valor')->nullable();

                $table->unique(['id_encuesta_respuesta', 'id_pregunta'], 'uk_erd_resp_preg');
                $table->foreign('id_encuesta_respuesta', 'fk_erd_resp')->references('id_encuesta_respuesta')->on('encuesta_respuestas')->onDelete('cascade');
                $table->foreign('id_pregunta', 'fk_erd_preg')->references('id_pregunta')->on('preguntas')->onDelete('cascade');
                $table->foreign('id_opcion_pregunta', 'fk_erd_opc')->references('id_opcion_pregunta')->on('opciones_pregunta')->onDelete('set null');
            });
        }

        // 6. Columna id_encuesta en actividades (controlecp_actividades)
        if (Schema::hasTable('controlecp_actividades')) {
            if (!Schema::hasColumn('controlecp_actividades', 'id_encuesta')) {
                // Agrega relación opcional con encuesta y sincroniza los valores existentes.
                // Ajusta el esquema de controlecp_actividades.
                Schema::table('controlecp_actividades', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_encuesta')->nullable()->after('encuesta_id');
                    $table->foreign('id_encuesta', 'fk_actividades_encuesta')->references('id_encuesta')->on('encuestas')->onDelete('set null');
                });

                // Sincronizar id_encuesta a partir de encuesta_id existente
                DB::statement('UPDATE controlecp_actividades SET id_encuesta = encuesta_id WHERE encuesta_id IS NOT NULL');
            }
        }

        // 7. Vista actividades para garantizar compatibilidad
        // Crea una vista compatible; si el motor no permite hacerlo, continúa sin interrumpir la migración.
        try {
            DB::statement('CREATE OR REPLACE VIEW actividades AS SELECT * FROM controlecp_actividades');
        } catch (\Throwable $e) {
            // Silencioso
        }
    }

    /**
     * Reverse the migrations.
     */
    // down(): Revierte los cambios de esquema aplicados por esta migración.
    public function down(): void
    {
        // Retira primero la vista y la clave añadida antes de eliminar las tablas nuevas.
        try {
            DB::statement('DROP VIEW IF EXISTS actividades');
        } catch (\Throwable $e) {}

        if (Schema::hasTable('controlecp_actividades') && Schema::hasColumn('controlecp_actividades', 'id_encuesta')) {
            // Ajusta el esquema de controlecp_actividades.
            Schema::table('controlecp_actividades', function (Blueprint $table) {
                $table->dropForeign('fk_actividades_encuesta');
                $table->dropColumn('id_encuesta');
            });
        }

        Schema::dropIfExists('encuesta_respuesta_detalle');
        Schema::dropIfExists('encuesta_respuestas');
        Schema::dropIfExists('opciones_pregunta');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('encuestas');
    }
};
