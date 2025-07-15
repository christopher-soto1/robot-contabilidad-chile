<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
// require __DIR__ . '/../src/routes.php';

// // Softland
require __DIR__ . '/../src/softland.php';

// // Rebsol
require __DIR__ . '/../src/rebsol.php';




/* $app->get('/test', function ($request, $response, $args) use ($app) {
    try {
        // Crear la instancia de Softland, pasando la instancia de Slim App
        $softland = new Softland($app);

         // Crear la instancia de Softland, pasando la instancia de Slim App
         $rebsol = new Rebsol($app);

        // Llamar al método
        // $result = $softland->get_iw_gsaen();
        $result = $softland->conexion_softland_st();

         // Llamar al método
        //$result = $rebsol->conexion_rebsol();

        // Devolver la respuesta
        return $response->withJson(["result" => $result], 200);
        
    } catch (Exception $e) {
        // Manejo de excepciones en caso de error
        return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
    }
}); */

// En tu archivo marcotest.php (Slim PHP)
$app->post('/test', function ($request, $response, $args) use ($app) { 
    try {
        // Leer el JSON enviado
        $softland = new Softland($app); // Ahora $app está definida en este ámbito

        $datosRecibidos = $request->getParsedBody();
        
        $respuesta = $softland->conexion_softland(); 

        // Retornar el JSON recibido para verificar
        return $response->withJson([
            "mensaje" => "Datos recibidos correctamente",
            "respuesta" => $respuesta,
        ], 200);

    } catch (Exception $e) {
        return $response->withJson(["error" => "Excepción: " . $e->getMessage()], 400);
    }
});

//ST 05-06-2025
$app->post('/api/registrar_deposito', function ($request, $response, $args) use ($app) {
    try {
        $data = $request->getParsedBody(); 
        // Crear la instancia de Softland, pasando la instancia de Slim App
        $softland = new Softland($app);


        // Devolver la respuesta
        return $softland->insertar_deposito($data, $response); 
        
    } catch (Exception $e) {
        // Manejo de excepciones en caso de error
        return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
    }
});



//NEW
$app->get('/api/obtener_facturas', function ($request, $response, $args) use ($app) {
    try {
        $data = $request->getParsedBody(); 
        // Crear la instancia de Softland, pasando la instancia de Slim App
        $softland = new Softland($app);
        // Devolver la respuesta
        return $softland->buscar_facturas_softland($data, $response);
        
    } catch (Exception $e) {
        // Manejo de excepciones en caso de error
        return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
    }
});

$app->post('/api/pago_facturas', function ($request, $response, $args) use ($app) {
    try {

        // Descomentar 
        $data = $request->getParsedBody(); 

        // return $response->withJson([
        //     "success" => "pago_facturas.",
        //     "data" => $data,
        // ], 200);
        // exit();
        // Crear la instancia de Softland, pasando la instancia de Slim App
        $softland = new Softland($app);
       
        //Descomentar
        // $result = $softland->insert_pago_facturas($data, $response);
        return $softland->insert_pago_facturas($data, $response);
        
    } catch (Exception $e) {
        // Manejo de excepciones en caso de error
        return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
    }
});




$app->post('/api/pago_facturas_rebsol', function ($request, $response, $args) use ($app) {
    try {

        // Descomentar 
        $data = $request->getParsedBody(); 

        // Crear la instancia de Softland, pasando la instancia de Slim App
        $rebsol = new Rebsol($app);
        // return $rebsol->insert_pago_facturas_rebsol($data, $response);
        // return $rebsol->insert_pago_facturas_rebsol_new($data, $response);
        // return $rebsol->insert_pago_facturas_rebsol_new_I($data, $response);
        return $rebsol->insert_pago_facturas_rebsol_new_II($data, $response);
        // return $rebsol->insert_pago_facturas_rebsol_new_III($data, $response);
        
        
    } catch (Exception $e) {
        // Manejo de excepciones en caso de error
        return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
    }
});





// Ruta para manejar la solicitud POST para crear un usuario
$app->post('/api/insert_softland', function ($request, $response, $args) use ($app){
    //$params = $request->getQueryParams(); // Obtiene los parámetros de la URL
    $params = $request->getParsedBody();
    // Verifica si se proporcionaron datos
    if (empty($params['codbodega'])) {
        return $response->withJson(['error' => 'codbodega es obligatorio'], 400);
    }

    if (empty($params['concepto'])){
        return $response->withJson(['error' => 'concepto es obligatorio'], 400);
    }

    if (empty($params['fecha'])){
        return $response->withJson(['error' => 'fecha es obligatorio'], 400);
    }

    if (empty($params['codaux'])){
        return $response->withJson(['error' => 'codaux es obligatorio'], 400);
    }

    if (empty($params['total'])){
        return $response->withJson(['error' => 'total es obligatorio'], 400);
    }

    // if (empty($params['codprod'])){
    //     return $response->withJson(['error' => 'codprod es obligatorio'], 400);
    // }

    if (empty($params['sucursal'])){
        return $response->withJson(['error' => 'sucursal es obligatorio'], 400);
    }

    if (empty($params['detDocumento'])){
        return $response->withJson(['error' => 'detDocumento es obligatorio'], 400);
    }

    $infoFactura = Array(
        'codbodega' => $params['codbodega'],
        'codcaja' => $params['codcaja'],
        'concepto' => $params['concepto'],
        'fecha' => $params['fecha'],
        'codaux' => $params['codaux'],
        'total' => $params['total'],
        // 'codprod' => $params['codprod'],
        // 'detprod' => $params['detprod'],
        'detDocumento' => $params['detDocumento'],
        'mle' => $params['mle'],
        'fechamle' => $params['fechamle']
    );

    // indica ambiente, produccion (PROD) o desarrollo (DEV)
    $ambiente = $params['ambiente'];
    $sucursal = $params['sucursal'];

    // Aquí puedes realizar la lógica de creación de usuario en tu aplicación
    // Por ejemplo, podrías almacenar los datos en una base de datos

    $softland = new Softland($app);
    $result = $softland->insert_iw_gsaen($infoFactura, $sucursal, $ambiente);
    // Verificar la instancia en Softland
    // $result = $softland->verificarInstancia();

    return $response->withJson(['mensaje' => $result]);
    // Accede a los valores devueltos
    // $data = $result['data'];
    // $status = $result['status'];

    // if ($status == 200) {
        // return $response->withJson($data, $status);
    // } else {
        // return $response->withJson($data, $status);
    // }

    // return $response->withJson('mensaje' => $test);

    // Simplemente devolvemos un mensaje de éxito para este ejemplo
    // return $response->withJson(['mensaje' => 'Usuario creado con éxito']);
});

// Run app
$app->run();
