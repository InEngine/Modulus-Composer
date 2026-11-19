<?php

// config for InEngine/ModulusComposer
return [

    /*
    |--------------------------------------------------------------------------
    | Composer binary
    |--------------------------------------------------------------------------
    |
    | Invoked via Laravel Process from the host application root. Private
    | Packagist / Satis auth stays in Composer's auth.json / repositories —
    | this package does not invent a second credential store.
    |
    */
    'composer_binary' => 'composer',

    /*
    |--------------------------------------------------------------------------
    | Working directory
    |--------------------------------------------------------------------------
    |
    | Host project root that owns composer.json / composer.lock. Empty string
    | resolves to base_path() at runtime.
    |
    */
    'working_directory' => '',

    /*
    |--------------------------------------------------------------------------
    | Process timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => 600,

    /*
    |--------------------------------------------------------------------------
    | Non-interactive flags
    |--------------------------------------------------------------------------
    |
    | Appended to require / remove / update so CI and Artisan stay quiet.
    |
    */
    'non_interactive' => [
        '--no-interaction',
        '--no-ansi',
    ],

];
