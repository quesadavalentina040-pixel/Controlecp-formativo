<?php

return [
    'name' => 'ControlECP',

    /*
    |--------------------------------------------------------------------------
    | URL base de la API SICEFA
    |--------------------------------------------------------------------------
    | Configura esta URL en el .env de tu proyecto:
    |   SICEFA_API_URL=https://tu-servidor-sicefa.sena.edu.co/api/v1
    |
    | Los endpoints que se consumirán son:
    |   GET {SICEFA_API_URL}/instructores
    |   GET {SICEFA_API_URL}/aprendices
    |
    | Deja en blanco para deshabilitar la sincronización (solo CRUD manual).
    */
    'sicefa_api_url' => env('SICEFA_API_URL', ''),
];
