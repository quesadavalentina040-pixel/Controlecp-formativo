<?php

return [

    'name' => 'SENAEMPRESA',

    /*
    |--------------------------------------------------------------------------
    | Configuración de la API REST central del ERP SICEFA
    |--------------------------------------------------------------------------
    | URL base, token de autenticación, timeout y prefijo de versión.
    | Los valores se toman del .env para no exponer credenciales en el repo.
    */
    'sicefa_api' => [
        'base_url'   => env('SICEFA_API_URL', 'https://sicefa.com.co/api'),
        'token'      => env('SICEFA_API_TOKEN', ''),
        'timeout'    => (int) env('SICEFA_API_TIMEOUT', 30),
        'prefix'     => env('SICEFA_API_PREFIX', 'v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoints de la API REST de SICEFA
    |--------------------------------------------------------------------------
    | Cada endpoint es relativo a base_url/prefix.
    */
    'endpoints' => [
        'apprentices'    => '/aprendices',        // GET lista de aprendices
        'apprentice'      => '/aprendices/{id}',   // GET un aprendiz
        'courses'         => '/cursos',            // GET cursos formativos
        'attendance_sync' => '/asistencias',       // POST sincronizar asistencias
        'attendance_send' => '/asistencias/registrar', // POST enviar registro de asistencia
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de asistencia permitidos
    |--------------------------------------------------------------------------
    */
    'attendance_types' => [
        'ASISTIO'  => 'Asistió',
        'FALTO'    => 'Faltó',
        'EXCUSA'   => 'Excusa',
        'RETARDO'  => 'Retardo',
    ],
];
