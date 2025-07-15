<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

//* Agregado el 25-10-2023 Middleware de CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// Middleware de CORS
$app->add(function ($request, $response, $next) {
    $response = $next($request, $response);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

