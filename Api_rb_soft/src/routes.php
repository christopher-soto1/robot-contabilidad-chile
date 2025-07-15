<?php

// Funciones Utiles
require_once '../src/helpers/funciones_utiles.php';

//Ruta para obtener datos de Previsiones
$app->get('/api/previsiones', function ($request, $response, $args){
    try { 
        $params = $request->getQueryParams(); // Obtiene los parámetros de la URL

        // Verifica si los parámetros requeridos están presentes
        $required_params = ['codigo_sucursal'];
        foreach ($required_params as $param) {
            if (!isset($params[$param])) {
                throw new Exception("El parámetro $param es requerido.");
            }
        }
    
        // Accede a los valores de los parámetros
        $codigo_sucursal = $params['codigo_sucursal'];
    
        //Conexion a Base de datos dependiendo de la sucursal
        $servidor = "";
        if ($codigo_sucursal==2) {//Huerfanos
            $servidor='db14';
            $codigo_sucursal = 1;
        } else  if ($codigo_sucursal==3) {// La Florida
            $servidor='db16';
            $codigo_sucursal = 1;
        } else{//Cualquier sucursal 1 Los Leones,4 Buin,5 Maipu,6 Plaza Egaña
            $servidor='db15';
        }

        $conexion_db = $this->get($servidor);

        $sql = "SELECT p.codigo_prevision AS codigo, p.nombre_prevision AS nombre, p.nombre_abreviado AS nombre_abreviado, 
            tp.codigo_tipo_prevision AS codigo_tipo, tp.nombre_tipo_prevision AS tipo, 
            p.codigo_estado AS estado 
            FROM PREVISION AS p 
            LEFT JOIN TIPO_PREVISION as tp ON tp.codigo_tipo_prevision = p.codigo_tipo_prevision 
            WHERE p.codigo_estado = 1
            ORDER BY p.nombre_prevision 
        ";

        $stmt = $conexion_db->prepare($sql);

        //ejecutar la consulta preparada y obtener todos los resultados de la consulta en forma de un array asociativo.
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);//fetchAll() es un método de PDO que se utiliza para recuperar todos los resultados de una consulta 
                                                    // PDO::FETCH_ASSOC devuelve un array asociativo donde los nombres de las columnas de la base de datos son las claves del array y los valores son los datos correspondientes

        // Cerrar la conexión a db
        $conexion_db = null;

        // Recorrer array para limpiar cadenas
        $result = recorrer_array_limpiar_cadena($result);

        // print_r($result);exit();

        if ($result) {
            return $response->withJson($result, 200);
        } else {
            return $response->withJson(["error" => "No se encontraron registros."], 404);
        }


    } catch (Exception $e) {

        $this->logger->info("Error en archivo: ". __FILE__.", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
        return $response->withJson( array("error"=> "Excepción capturada - ".$e->getMessage() ), 400);
        // die();
    }

});




//RUTA PARA VALIDAR ATENCION DEL PACIENTE, AGREGADO 29-07-2024
$app->get('/api/validarPaciente', function ($request, $response, $args){

    try { 
        // $arreglo_paciente = array();
        $params = $request->getQueryParams(); // Obtiene los parámetros de la URL
        // Accede a los valores de los parámetros
        $rut_paciente = $params['rut'];
        $rut = strstr($rut_paciente, '-', true); // Obtiene todo antes del primer guion
        $cod_reserva = $params['num_reserva'];
        $servidores = array(
            'db14',
            'db15',
            'db16'
        );

        $arreglo_result = array();

        foreach ($servidores as $servidor){
            $conexion_db = $this->get($servidor);
            $sql ="SELECT EXISTS (
                    SELECT 1
                    FROM RESERVA_ATENCION
                    WHERE rut_pnatural = :rut
                    AND codigo_reserva_atencion = :cod_reserva
                ) AS registro_existe;";


            $stmt = $conexion_db->prepare($sql);
            $stmt->bindParam(':rut', $rut, PDO::PARAM_INT);
            $stmt->bindParam(':cod_reserva', $cod_reserva, PDO::PARAM_INT);

            //ejecutar la consulta preparada y obtener todos los resultados de la consulta en forma de un array asociativo.
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = recorrer_array_limpiar_cadena($result);
            // Verificar el valor del resultado
            if (isset($result[0]['registro_existe']) && $result[0]['registro_existe'] == 1) {
                // echo "Registro existe, saliendo del bucle.";
                break;
            }
            $conexion_db = null;

        }

        // echo "<pre>";
        // var_dump($result);
        // echo "</pre>";
        // exit();
        
        if ($result) {
            return $response->withJson($result, 200);
        } else {
            return $response->withJson(["error" => "No se encontraron registros."], 404);
        }

} catch (Exception $e) {
    // Manejo de excepciones
    $this->logger->info("Error en archivo: ". __FILE__.", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
    return $response->withJson(array("error"=> "Excepción capturada - ".$e->getMessage()), 400);
}


});





$app->get('/api/obtener_saludo', function ($request, $response, $args){
    $mensaje = 'esto es un saludo retornado desde routes';
    return $response->withJson($mensaje, 200);

});




// $app->get('/api/obtener_facturas', function ($request, $response, $args) {

//     try {
//         /*Obtiene los datos enviados en el cuerpo de la solicitud
//         *enviados atraves de la function llamarApi($endpoint, $metodo = 'GET', $data = null) {
//         * curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
//         * del sistema IOPA_pagoFacil.
//         */
//         $data = $request->getParsedBody(); 
    
//         $cod_prevision_soft = $data['cod_prevision_soft'];
//         $cod_prevision_rb = $data['cod_prevision_rb'];
//         $nombre_prevision = $data['nombre_prevision'];
//         // $servidores = 'dbSOFTLAND_PROD';  // Nombre del servidor de conexión a la base de datos
//         $servidores = 'dbSOFTLAND_DEV';  // Nombre del servidor de conexión desarrollo 
//         // Obtener la conexión a la base de datos desde el contenedor
//         $conexion_db = $this->get($servidores);

//         // Verificar si la conexión es exitosa
//         if ($conexion_db) {
//             /*Consulta que permite traer los registros de todas las facturas que no cuenten con un comprobante contable (facturas pendientes de pago)*/
//             $sql = "SELECT DISTINCT iwg.*, cvm.CpbNum
//                     FROM softland.iw_gsaen iwg
//                     LEFT JOIN softland.cwmovim cvm 
//                         ON cvm.CodAux = iwg.CodAux
//                         AND cvm.MovNumDocRef = iwg.Folio
//                         AND cvm.TtdCod = 'DP' 
//                     WHERE iwg.Tipo = 'F'
//                     AND iwg.CodAux = $cod_prevision_soft
//                     AND iwg.Fecha >= '2025-01-02'
//                     AND cvm.CodAux IS NULL;
//             ";

//             // echo "<pre>";
//             // var_dump($sql);
//             // echo "</pre>";
//             // exit();

//             // Preparar la consulta
//             $stmt = $conexion_db->prepare($sql);

//             // Ejecutar la consulta
//             $stmt->execute();
//             $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             // Verificar si hay resultados
//             if ($result) {
//                 return $response->withJson($result, 200);
//             } else {
//                 return $response->withJson(["error" => "No se encontraron facturas."], 404);
//             }
//         } else {
//             return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
//         }

//     } catch (Exception $e) {
//         // Manejo de excepciones en caso de errores
//         $this->logger->info("Error en archivo: ". __FILE__ .", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
//         return $response->withJson(["error" => "Excepción capturada - ".$e->getMessage()], 400);
//     }

// });




//VERSION 1
// $app->post('/api/pago_facturas', function ($request, $response, $args){

//     // $servidoresSoftland = 'dbSOFTLAND_PROD';  // Nombre del servidor de conexión a la base de datos
//     // $data = $request->getParsedBody(); 
//     // echo "<pre>";
//     // var_dump('pago_facturas softland rebsol');
//     // var_dump($data);
//     // echo "</pre>";
//     // exit();
//     // Definir el servidor de la base de datos
//     $servidorRebsol = 'db18'; 

//     // Obtener la configuración de la base de datos desde el archivo de configuración
//     $dbConfig = $this->get('settings')['db18'];

//     // Intentar realizar la conexión usando PDO
//     try {
//         // Crear la cadena DSN para la conexión PDO
//         $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";

//         // Crear una instancia PDO para la conexión
//         $conexion_db_rb = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);

//         // Configurar el modo de error de PDO para excepciones
//         $conexion_db_rb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//         /*PASO 1
//         *Esta consulta obtiene información de pacientes y sus pagos, asociando detalles de facturación y boletas.
//         */
//         $con = "
//             SELECT *, pcd.monto AS monto_pago, DATE_FORMAT(db.fecha_facturacion, '%d/%m/%Y') AS fecha_boleta 
//             FROM PACIENTE AS p
//             JOIN CUENTA_PACIENTE AS cp 
//                 ON p.rut_paciente = cp.rut_paciente 
//                 AND p.numero_hermano_gemelo = cp.numero_hermano_gemelo 
//                 AND p.evento = cp.evento
//             JOIN PAGO_CUENTA_DOCUMENTO AS pcd 
//                 ON cp.rut_paciente = pcd.rut_paciente 
//                 AND cp.numero_hermano_gemelo = pcd.numero_hermano_gemelo 
//                 AND cp.evento = pcd.evento 
//                 AND cp.codigo_cuenta = pcd.codigo_cuenta
//             JOIN DETALLE_PAGO_CUENTA AS dpc 
//                 ON pcd.rut_paciente = dpc.rut_paciente 
//                 AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo 
//                 AND pcd.evento = dpc.evento 
//                 AND pcd.codigo_cuenta = dpc.codigo_cuenta 
//                 AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta
//             JOIN DETALLE_BOLETA AS db 
//                 ON dpc.rut_paciente = db.rut_paciente 
//                 AND dpc.numero_hermano_gemelo = db.numero_hermano_gemelo 
//                 AND dpc.evento = db.evento 
//                 AND dpc.codigo_cuenta = db.codigo_cuenta 
//                 AND dpc.codigo_pago_cuenta = db.codigo_pago_cuenta
//             WHERE cp.codigo_estado_cuenta IN (15, 24, 25)
//                 AND db.codigo_control_facturacion = 0
//                 AND db.codigo_estado = 1
//                 AND db.codigo_tipo_documento IN (2, 6)
//                 AND pcd.estado_documento != 4
//                 AND dpc.codigo_forma_pago IN (18, 20, 22)
//                 AND db.numero_documento IN (89769)
//             GROUP BY pcd.rut_paciente, pcd.numero_hermano_gemelo, pcd.evento, pcd.codigo_cuenta, pcd.codigo_pago_cuenta
//             ORDER BY db.codigo_tipo_documento, db.numero_documento, db.fecha_facturacion
//         ";

//         // Preparar la consulta para evitar SQL injection
//         $stmt = $conexion_db_rb->prepare($con);

//         // Ejecutar la consulta sin los parámetros de fecha
//         $stmt->execute();

//         // Obtener el número de filas
//         $hay_reg = $stmt->rowCount();

//         // Verificar si hay registros
//         if ($hay_reg > 0) {
//             // Hacer algo con los resultados si es necesario
//             $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
//             return $response->withJson(["message" => "Registros encontrados.", "data" => $resultados], 200);
//         } else {
//             return $response->withJson(["message" => "No se encontraron registros."], 404);
//         }
//     } catch (PDOException $e) {
//         // Si hay un error en la conexión, mostrarlo
//         return $response->withJson(["error" => "Error de conexión a la base de datos: " . $e->getMessage()], 500);
//     }
// });



$app->post('/api/pago_facturas', function ($request, $response, $args) {

    

    //pago facturas sotfland 

     //pago facturas rebsol


    // Definir el servidor de la base de datos
    $servidorRebsol = 'db18'; 
    $numero_factura = "89545"; //NUMERO DE FACTURAS
    $codigo_prevision = '12'; // CODIGO PREVISION REBSOL
    $numero_factura_aux = "";
    $tipo_factura_aux = "";
    $x = 0; // Inicializa $x antes de usarlo
    $total_factura = 0; // INICIALIZAR

    $numero_deposito = 10101; 
    $rut_funcionario = "18089559"; // RUT FUNCIONARO REGISTRA
    // $rut_pjuridica = "76296619"; // RUT PREVISION 
    $forma_pago_tranf = 46; // FORMA PAGO TRANFERENCIA
    $rut_pjuridica_banco =""; // RUT BANCO

    // 632

    // Obtener la configuración de la base de datos desde el archivo de configuración
    // $dbConfig = $this->get('settings')['db18'];

    try {
       
        // Crear la conexión PDO
        // $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        // $conexion_db_rb = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
        // $conexion_db_rb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $servidores = 'db18';  // Nombre del servidor de conexión  
        // Obtener la conexión a la base de datos desde el contenedor
        $conexion_db_rb = $this->get($servidores);

        /*
        * PASO 1 Esta consulta obtiene información de pacientes y sus pagos, asociando detalles de facturación y boletas.
         */
        $sql1 = "
            SELECT *, pcd.monto AS monto_pago, DATE_FORMAT(db.fecha_facturacion, '%d/%m/%Y') AS fecha_boleta 
            FROM PACIENTE AS p
            JOIN CUENTA_PACIENTE AS cp 
                ON p.rut_paciente = cp.rut_paciente 
                AND p.numero_hermano_gemelo = cp.numero_hermano_gemelo 
                AND p.evento = cp.evento
            JOIN PAGO_CUENTA_DOCUMENTO AS pcd 
                ON cp.rut_paciente = pcd.rut_paciente 
                AND cp.numero_hermano_gemelo = pcd.numero_hermano_gemelo 
                AND cp.evento = pcd.evento 
                AND cp.codigo_cuenta = pcd.codigo_cuenta
            JOIN DETALLE_PAGO_CUENTA AS dpc 
                ON pcd.rut_paciente = dpc.rut_paciente 
                AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo 
                AND pcd.evento = dpc.evento 
                AND pcd.codigo_cuenta = dpc.codigo_cuenta 
                AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta
            JOIN DETALLE_BOLETA AS db 
                ON dpc.rut_paciente = db.rut_paciente 
                AND dpc.numero_hermano_gemelo = db.numero_hermano_gemelo 
                AND dpc.evento = db.evento 
                AND dpc.codigo_cuenta = db.codigo_cuenta 
                AND dpc.codigo_pago_cuenta = db.codigo_pago_cuenta
            WHERE cp.codigo_estado_cuenta IN (15, 24, 25)
                AND db.codigo_control_facturacion = 0
                AND db.codigo_estado = 1
                AND db.codigo_tipo_documento IN (2, 6)
                AND pcd.estado_documento != 4
                AND dpc.codigo_forma_pago IN (18,20,22)
                AND db.numero_documento IN ($numero_factura)
            GROUP BY pcd.rut_paciente, pcd.numero_hermano_gemelo, pcd.evento, pcd.codigo_cuenta, pcd.codigo_pago_cuenta
            ORDER BY db.codigo_tipo_documento, db.numero_documento, db.fecha_facturacion
        ";

        // echo "<pre>";
        // var_dump($sql1);
        // echo "</pre>";
        // exit();

        // Preparar y ejecutar la consulta
        $stmt = $conexion_db_rb->prepare($sql1);
        $stmt->execute();

        // Obtener los registros
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si hay resultados
        if (!empty($resultados)) {
            // $data = [];
            // Recorrer los resultados
            foreach ($resultados as $fila) {
                // $data[] = [
                //     "rut_paciente" => $fila["rut_paciente"],
                //     "evento" => $fila["evento"],
                //     "codigo_cuenta" => $fila["codigo_cuenta"],
                //     "monto_pago" => $fila["monto_pago"],
                //     "fecha_boleta" => $fila["fecha_boleta"],
                //     "codigo_tipo_documento" => $fila["codigo_tipo_documento"],
                //     "numero_documento" => $fila["numero_documento"]
                // ];
                $tipo_factura = $fila['codigo_tipo_documento'];
                $rut_paciente = $fila['rut_paciente'];
                $nhg =  $fila['numero_hermano_gemelo'];
                $evento =  $fila['evento'];
                $fecha_admision =  $fila['fecha_boleta'];
                $estado_cuenta =  $fila['codigo_estado_cuenta'];
                $pago_cuenta =  $fila['codigo_pago_cuenta'];
                $codigo_flujo =  $fila['codigo_flujo_caja'];
                $monto_factura =  $fila['monto'];
                $fecha_facturacion =  $fila['fecha_facturacion'];

                // Mostrar valores con formato <pre>
                // echo "<pre>valores retornados paso 1:
                // tipo_factura: $tipo_factura
                // Rut Paciente: $rut_paciente
                // Número Hermano Gemelo: $nhg
                // Evento: $evento
                // Fecha Admisión: $fecha_admision
                // Estado Cuenta: $estado_cuenta
                // Pago Cuenta: $pago_cuenta
                // Código Flujo Caja: $codigo_flujo
                // Monto Factura: $monto_factura
                // Fecha Facturacion: $fecha_facturacion
                // </pre>";
                // exit();

              
                /*
                PASO 2 La consulta recupera la información de una factura específica (89769) que está activa para obtener el codigo_tipo_facturacion  
                *Obtiene el registro resumido de la factura.
                */
                // Consulta principal
                $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                        FROM FACTURA_MULTIPLE 
                        WHERE numero_factura = :numero_factura 
                        AND codigo_tipo_documento = :tipo_factura 
                        AND codigo_estado = 1";
                $stmt_fm = $conexion_db_rb->prepare($sql);
                $stmt_fm->execute([':numero_factura' => $numero_factura, ':tipo_factura' => $tipo_factura]);

                if ($stmt_fm->rowCount() > 0) {
                    $row_fm = $stmt_fm->fetch(PDO::FETCH_ASSOC);
                    // echo "<pre>";
                    // var_dump('resultado consulta PASO 2');
                    // var_dump($row_fm);
                    // echo "</pre>";
                    // exit();
                    if ($numero_factura_aux != $numero_factura || $tipo_factura_aux != $tipo_factura) {
                        $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];
                        // echo "<pre>";
                        // var_dump('imprimir codigo tipo Factura');
                        // var_dump($tipo_prestacion);
                        // echo "</pre>";
                        if (($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
                            $rut_prevision = $row_fm['rut_prevision'];
                            // echo "<pre>";
                            // var_dump('imprimir rut prevision');
                            // var_dump($rut_prevision);
                            // echo "</pre>";
                
                            // Consulta para PREVISION
                            $sql_prev = "SELECT * FROM PREVISION WHERE rut_pjuridica = :rut_prevision AND codigo_estado = 1";
                            $stmt_prev = $conexion_db_rb->prepare($sql_prev);
                            $stmt_prev->execute([':rut_prevision' => $rut_prevision]);

                            // echo "<pre>";
                            // var_dump('PREVISION');
                            // var_dump($rut_prevision);
                            // echo "</pre>";
                
                            $verifica_prevision = 0;
                            while ($row_x2 = $stmt_prev->fetch(PDO::FETCH_ASSOC)) {
                                if ($codigo_prevision == $row_x2["codigo_prevision"]) {
                                    $verifica_prevision = 1;
                                }
                            }
                            // echo "<pre>";
                            // var_dump('consultar PREVISION');
                            // var_dump($verifica_prevision);
                            // echo "</pre>";
                
                            if (($verifica_prevision == 1) || $codigo_prevision == 999) {
                                $x++;
                                $hay_reg = 1;
                                // $rut_puntos = devuelve_puntos($rut_prevision);
                                // $digito = devuelve_digito($rut_prevision);
                                // $fecha_admision = $row['fecha_boleta'];
                                // $lugar_atencion = $row_fm['codigo_lugar_atencion'];
                                // $nombre_lugar = devuelve_nombre_tabla($conexion_db_rb, "ATENCION_CONSULTA", $lugar_atencion);
                
                                $tipo_cuenta = ($tipo_prestacion == 1) ? 2 : 1;
                                if ($tipo_prestacion == 2) $tipo_texto = "CONSULTA(S)";
                                if ($tipo_prestacion == 3) $tipo_texto = "EXAMEN(ES)";
                
                                $codigo_forma_pago = $row_fm['forma_pago'];
                                $monto_factura = $row_fm['monto'];
                                $monto_excedente = 0;
                
                                // Consulta para PAGO_CUENTA_DOCUMENTO
                                $sql_bono = "SELECT * 
                                             FROM PAGO_CUENTA_DOCUMENTO AS pcd 
                                             JOIN DETALLE_PAGO_CUENTA AS dpc ON pcd.rut_paciente = dpc.rut_paciente 
                                             AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo 
                                             AND pcd.evento = dpc.evento 
                                             AND pcd.codigo_cuenta = dpc.codigo_cuenta 
                                             AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta 
                                             JOIN DETALLE_BOLETA AS db ON dpc.rut_paciente = db.rut_paciente 
                                             AND dpc.numero_hermano_gemelo = db.numero_hermano_gemelo 
                                             AND dpc.evento = db.evento 
                                             AND dpc.codigo_cuenta = db.codigo_cuenta 
                                             AND dpc.codigo_pago_cuenta = db.codigo_pago_cuenta 
                                             WHERE db.numero_documento = :numero_factura 
                                             AND db.codigo_tipo_documento = :tipo_factura";
                                $stmt_bono = $conexion_db_rb->prepare($sql_bono);
                                $stmt_bono->execute([':numero_factura' => $numero_factura, ':tipo_factura' => $tipo_factura]);
                
                                $monto_bonificado = 0;
                                $monto_copago = 0;
                                $monto_excedente = 0;
                
                                if ($stmt_bono->rowCount() > 0) {
                                    while ($row_bono = $stmt_bono->fetch(PDO::FETCH_ASSOC)) {
                                        $forma_pago = $row_bono['codigo_forma_pago'];
                                        // $nombre_fp = devuelve_nombre_tabla($conexion_db_rb, "FORMA_PAGO", $forma_pago);
                                        // $nombre_fp_corto = formato_reducido($nombre_fp, 3);
                                        $codigo_caja = $row_bono['codigo_caja'];
                
                                        if ($forma_pago == 18 || $forma_pago == 20 || $forma_pago == 22) {
                                            $monto_bonificado += $row_bono['monto_pago_cuenta'];
                                        } elseif ($forma_pago == 48) {
                                            $monto_excedente += $row_bono['monto_pago_cuenta'];
                                        } else {
                                            $monto_copago += $row_bono['monto_pago_cuenta'];
                                        }
                                    }
                
                                    if ($monto_copago >= $monto_excedente) {
                                        $monto_copago -= $monto_excedente;
                                    }
                                }
                
                                if (empty($monto_bonificado)) {
                                    $monto_bonificado = $monto_factura;
                                }
                
                                $total_factura += $monto_factura;
                               

                                echo"<pre>montos factura if:
                                        monto_bonificado: $monto_bonificado
                                        monto_copago: $monto_copago
                                        monto_excedente: $monto_excedente
                                        Monto Factura: $monto_factura
                                        Total Factura: $total_factura
                                    </pre>";
                                // exit();
                
                                // Consulta para DETALLE_BOLETA
                                $sql_db = "SELECT * FROM DETALLE_BOLETA WHERE numero_documento = :numero_factura AND codigo_estado = 1 AND codigo_tipo_documento = :tipo_factura";
                                $stmt_db = $conexion_db_rb->prepare($sql_db);
                                $stmt_db->execute([':numero_factura' => $numero_factura, ':tipo_factura' => $tipo_factura]);
                                $total_registros = $stmt_db->rowCount();
                                $row_db = $stmt_db->fetch(PDO::FETCH_ASSOC);
                                $codigo_flujo = $row_db['codigo_flujo_caja'];
                
                                // if ($codigo_prevision == 999) {
                                //     $nombre_prevision_sub = devuelve_nombre_tabla($conexion_db_rb, "PREVISION", $row_prev['codigo_prevision']);
                                // }
                
                                // $nombre_paciente = "$total_registros BONO(S) $tipo_texto, SUCURSAL $nombre_lugar";
                            }
                        }
                        $numero_factura_aux = $numero_factura;
						$tipo_factura_aux = $tipo_factura;
                    }

                }else{
                    echo"<pre>opcion else:</pre>";
                    // exit();
                    // Inicializar variables
                    $tipo_cuenta = 0;
                    $codigo_prevision_paciente = 0;

                    // Determinar el tipo de cuenta y obtener el código de previsión del paciente
                    if ($estado_cuenta == 15) {
                        $tipo_cuenta = 1;
                        $stmt_res = $conexion_db_rb->prepare("SELECT * FROM RESERVA_ATENCION WHERE rut_pnatural = :rut_paciente AND numero_hermano_gemelo = :nhg AND evento = :evento AND codigo_pago_cuenta = :pago_cuenta");
                        $stmt_res->execute([
                            ':rut_paciente' => $rut_paciente,
                            ':nhg' => $nhg,
                            ':evento' => $evento,
                            ':pago_cuenta' => $pago_cuenta
                        ]);
                        $row_res = $stmt_res->fetch(PDO::FETCH_ASSOC);
                        $codigo_prevision_paciente = $row_res['codigo_prevision'];
                    } else {
                        $tipo_cuenta = 2;
                        $stmt_res = $conexion_db_rb->prepare("SELECT * FROM DATO_INGRESO WHERE rut_paciente = :rut_paciente AND numero_hermano_gemelo = :nhg AND evento = :evento");
                        $stmt_res->execute([
                            ':rut_paciente' => $rut_paciente,
                            ':nhg' => $nhg,
                            ':evento' => $evento
                        ]);
                        $row_res = $stmt_res->fetch(PDO::FETCH_ASSOC);
                        $codigo_prevision_paciente = $row_res['codigo_prevision'];
                    }

                    // Verificar si el código de previsión es el mismo
                    if ($codigo_prevision_paciente == $codigo_prevision) {
                        // Incrementar contador
                        $x++;
                        $hay_reg = 1;
                        $total_factura += $monto_factura;

                        // Inicializar los montos de bonificación, copago y excedente
                        $monto_bonificado = 0;
                        $monto_copago = 0;
                        $monto_excedente = 0;

                        // Consultar los detalles de pago
                        $stmt_bono = $conexion_db_rb->prepare("SELECT * FROM PAGO_CUENTA_DOCUMENTO AS pcd, DETALLE_PAGO_CUENTA AS dpc 
                                                            WHERE pcd.rut_paciente = dpc.rut_paciente 
                                                            AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo 
                                                            AND pcd.evento = dpc.evento 
                                                            AND pcd.codigo_cuenta = dpc.codigo_cuenta 
                                                            AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta 
                                                            AND dpc.rut_paciente = :rut_paciente 
                                                            AND dpc.numero_hermano_gemelo = :nhg 
                                                            AND dpc.evento = :evento 
                                                            AND dpc.codigo_pago_cuenta = :pago_cuenta");
                        $stmt_bono->execute([
                            ':rut_paciente' => $rut_paciente,
                            ':nhg' => $nhg,
                            ':evento' => $evento,
                            ':pago_cuenta' => $pago_cuenta
                        ]);

                        // Verificar si hay resultados de la consulta
                        if ($stmt_bono->rowCount() > 0) {
                            while ($row_bono = $stmt_bono->fetch(PDO::FETCH_ASSOC)) {
                                $forma_pago = $row_bono['codigo_forma_pago'];
                                // $nombre_fp = devuelve_nombre_tabla($conexion_db_rb, "FORMA_PAGO", $forma_pago);
                                // $nombre_fp_corto = formato_reducido($nombre_fp, 3);
                                $codigo_caja = $row_bono['codigo_caja'];

                                // Calcular los montos dependiendo de la forma de pago
                                if ($forma_pago == 18) {
                                    $monto_bonificado += $row_bono['monto_pago_cuenta'];
                                } elseif ($forma_pago == 22) {
                                    $monto_bonificado += $row_bono['monto_pago_cuenta'];
                                } elseif ($forma_pago == 48) {
                                    $monto_excedente += $row_bono['monto_pago_cuenta'];
                                } else {
                                    $monto_copago += $row_bono['monto_pago_cuenta'];
                                }
                            }

                            // Ajustar copago si excede el monto excedente
                            if ($monto_copago >= $monto_excedente) {
                                $monto_copago = $monto_copago - $monto_excedente;
                            }
                        }

                        // // Convertir los montos a puntos
                        // $monto_bonificado_p = devuelve_puntos($monto_bonificado);
                        // $monto_copago_p = devuelve_puntos($monto_copago);
                        // $monto_excedente_p = devuelve_puntos($monto_excedente);
                        // $monto_factura_p = devuelve_puntos($monto_factura);

                        echo"<pre>montos factura else:
                                monto_bonificado: $monto_bonificado
                                monto_copago: $monto_copago
                                monto_excedente: $monto_excedente
                                Monto Factura: $monto_factura
                            </pre>";
                        // exit();
                    }

                }

                /*
                *Consulta para obtener el ultimo codigo de control  de la tabla CONTROL_FACTURACION.
                *Sumamos 1 para generar el codigo correlativo para el control de la factura que se va actualizar y registrar el pago.
                */
                $query = "SELECT MAX(codigo_control_facturacion) FROM CONTROL_FACTURACION";
                $stmt = $conexion_db_rb->prepare($query);
                $stmt->execute();

                // Obtener el resultado
                $row_id = $stmt->fetch(PDO::FETCH_NUM);
                $codigo_control_facturacion = ($row_id[0] ?? 0) + 1;


                /*
                *Consulta para actualizar el codigo de control de la factura en la tabla DETALLE_BOLETA.
                *Actualizamos el codigo de control de la factura para el documento que se va actualizar y registrar el pago.
                */
                $query ="UPDATE DETALLE_BOLETA SET 
                        codigo_control_facturacion = :codigo_control_facturacion, 
                        codigo_flujo_caja = :codigo_flujo 
                    WHERE numero_documento = :numero_factura 
                    AND codigo_estado = 1 
                    AND codigo_tipo_documento = :tipo_factura 
                    AND codigo_control_facturacion = 0";

                $stmt = $conexion_db_rb->prepare($query);

                // Bind de parámetros para evitar inyección SQL
                $stmt->bindParam(':codigo_control_facturacion', $codigo_control_facturacion);
                $stmt->bindParam(':codigo_flujo', $codigo_flujo);
                $stmt->bindParam(':numero_factura', $numero_factura);
                $stmt->bindParam(':tipo_factura', $tipo_factura);

                // Ejecutar la consulta
                if ($stmt->execute()) {
                      // echo "Actualización exitosa";
                    $fecha_actual = date("Y-m-d H:i:s"); //fecha de registro pago de factura
                  
                    $query = "INSERT INTO CONTROL_FACTURACION (
                        codigo_control_facturacion, 
                        codigo_prevision, 
                        monto_factura, 
                        codigo_forma_pago, 
                        comprobante_deposito, 
                        fecha_registro, 
                        fecha_factura, 
                        rut_funcionario, 
                        rut_pjuridica, 
                        bonificado, 
                        copago, 
                        excedente
                    ) VALUES (
                        :codigo_control_facturacion, 
                        :codigo_prevision, 
                        :monto_factura, 
                        :codigo_forma_pago, 
                        :comprobante_deposito, 
                        :fecha_registro, 
                        :fecha_factura, 
                        :rut_funcionario, 
                        :rut_pjuridica, 
                        :bonificado, 
                        :copago, 
                        :excedente
                    )";
        
                    $stmt = $conexion_db_rb->prepare($query);
                    
                    // Asociar los valores a los parámetros de la consulta
                    $stmt->bindParam(':codigo_control_facturacion', $codigo_control_facturacion);
                    $stmt->bindParam(':codigo_prevision', $codigo_prevision);
                    $stmt->bindParam(':monto_factura', $monto_factura);
                    $stmt->bindParam(':codigo_forma_pago', $forma_pago_tranf);
                    $stmt->bindParam(':comprobante_deposito', $numero_deposito);
                    $stmt->bindParam(':fecha_registro', $fecha_actual);
                    $stmt->bindParam(':fecha_factura', $fecha_facturacion);
                    $stmt->bindParam(':rut_funcionario', $rut_funcionario);
                    $stmt->bindParam(':rut_pjuridica', $rut_pjuridica_banco);
                    $stmt->bindParam(':bonificado', $monto_bonificado);
                    $stmt->bindParam(':copago', $monto_copago);
                    $stmt->bindParam(':excedente', $monto_excedente);
                    
                    // Ejecutar la consulta
                    $stmt->execute();
                } //else {
                //     echo "Error en la actualización";
                // }
                    
            }

            return $response->withJson([
                "message" => "Registros de pago de facturas realizadas con éxito."
            ], 200);

        } else {
            return $response->withJson(["message" => "No se encontraron registros."], 404);
        }
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error de conexión a la base de datos: " . $e->getMessage()], 500);
    }
});








// $app->post('/api/pago_facturas', function ($request, $response, $args) {
//     // Definir el servidor de la base de datos
//     $servidorRebsol = 'db18'; 
//     $numero_factura = "90321";

//     // Obtener la configuración de la base de datos desde el archivo de configuración
//     $dbConfig = $this->get('settings')['db18'];

//     try {
//         // Crear la conexión PDO
//         $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
//         $conexion_db_rb = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
//         $conexion_db_rb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//         /*
//          */
//         $sql1 = "
//             SELECT *, pcd.monto AS monto_pago, DATE_FORMAT(db.fecha_facturacion, '%d/%m/%Y') AS fecha_boleta 
//             FROM PACIENTE AS p
//             JOIN CUENTA_PACIENTE AS cp 
//                 ON p.rut_paciente = cp.rut_paciente 
//                 AND p.numero_hermano_gemelo = cp.numero_hermano_gemelo 
//                 AND p.evento = cp.evento
//             JOIN PAGO_CUENTA_DOCUMENTO AS pcd 
//                 ON cp.rut_paciente = pcd.rut_paciente 
//                 AND cp.numero_hermano_gemelo = pcd.numero_hermano_gemelo 
//                 AND cp.evento = pcd.evento 
//                 AND cp.codigo_cuenta = pcd.codigo_cuenta
//             JOIN DETALLE_PAGO_CUENTA AS dpc 
//                 ON pcd.rut_paciente = dpc.rut_paciente 
//                 AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo 
//                 AND pcd.evento = dpc.evento 
//                 AND pcd.codigo_cuenta = dpc.codigo_cuenta 
//                 AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta
//             JOIN DETALLE_BOLETA AS db 
//                 ON dpc.rut_paciente = db.rut_paciente 
//                 AND dpc.numero_hermano_gemelo = db.numero_hermano_gemelo 
//                 AND dpc.evento = db.evento 
//                 AND dpc.codigo_cuenta = db.codigo_cuenta 
//                 AND dpc.codigo_pago_cuenta = db.codigo_pago_cuenta
//             WHERE cp.codigo_estado_cuenta IN (15, 24, 25)
//                 AND db.codigo_control_facturacion = 0
//                 AND db.codigo_estado = 1
//                 AND db.codigo_tipo_documento IN (2, 6)
//                 AND pcd.estado_documento != 4
//                 AND dpc.codigo_forma_pago IN ($numero_factura)
//                 AND db.numero_documento IN (90321)
//             GROUP BY pcd.rut_paciente, pcd.numero_hermano_gemelo, pcd.evento, pcd.codigo_cuenta, pcd.codigo_pago_cuenta
//             ORDER BY db.codigo_tipo_documento, db.numero_documento, db.fecha_facturacion
//         ";

//         // Preparar y ejecutar la consulta
//         $stmt = $conexion_db_rb->prepare($sql1);
//         $stmt->execute();

//         // Obtener los registros
//         $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         // Verificar si hay resultados
//         if (!empty($resultados)) {
//             // $data = [];
//             // Recorrer los resultados
//             foreach ($resultados as $fila) {
//                 // $data[] = [
//                 //     "rut_paciente" => $fila["rut_paciente"],
//                 //     "evento" => $fila["evento"],
//                 //     "codigo_cuenta" => $fila["codigo_cuenta"],
//                 //     "monto_pago" => $fila["monto_pago"],
//                 //     "fecha_boleta" => $fila["fecha_boleta"],
//                 //     "codigo_tipo_documento" => $fila["codigo_tipo_documento"],
//                 //     "numero_documento" => $fila["numero_documento"]
//                 // ];
//                 $tipo_factura = $fila['codigo_tipo_documento'];
//                 $rut_paciente = $fila['rut_paciente'];
//                 $nhg =  $fila['numero_hermano_gemelo'];
//                 $evento =  $fila['evento'];
//                 $fecha_admision =  $fila['fecha_boleta'];
//                 $estado_cuenta =  $fila['codigo_estado_cuenta'];
//                 $pago_cuenta =  $fila['codigo_pago_cuenta'];
//                 $codigo_flujo =  $fila['codigo_flujo_caja'];
//                 $monto_factura =  $fila['monto'];

//                 // Mostrar valores con formato <pre>
//                 echo "<pre>Consulta 2:
//                 tipo_factura: $tipo_factura
//                 Rut Paciente: $rut_paciente
//                 Número Hermano Gemelo: $nhg
//                 Evento: $evento
//                 Fecha Admisión: $fecha_admision
//                 Estado Cuenta: $estado_cuenta
//                 Pago Cuenta: $pago_cuenta
//                 Código Flujo Caja: $codigo_flujo
//                 Monto Factura: $monto_factura
//                 </pre>";
//                 exit();

//                 /*
//                 PASO 2 VALIDA PARA OBTENER EL DETALLE DE LA FACTURA SUS FORMAS DE PAGOS, COPAGOS, BONIFICADOS Y EXCEDENTES
//                 */
//                 while($row = mysql_fetch_array($res)) {
//                     $tipo_factura = $row['codigo_tipo_documento'];
//                     $numero_factura = $row['numero_documento'];
                    
//                     $nombre_tipo = str_replace("FACTURA ", "", devuelve_nombre_tabla($link, "TIPO_DOCUMENTO", $tipo_factura));
//                     /*
//                     PASO 3 La consulta recupera la información de una factura específica (89769) que está activa para obtener el codigo_tipo_facturacion  
//                     *Obtiene el registro resumido de la factura.
//                     */
//                     $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta FROM FACTURA_MULTIPLE WHERE numero_factura = $numero_factura AND codigo_tipo_documento = $tipo_factura AND codigo_estado = 1";
//                     $res_fm = mysql_query($sql, $link);
                    
//                     if(mysql_num_rows($res_fm) > 0) {
//                         if($numero_factura_aux != $numero_factura || $tipo_factura_aux != $tipo_factura) {
//                             $row_fm = mysql_fetch_array($res_fm);
//                             $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];
                            
//                             if(($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
//                                 $rut_prevision = $row_fm['rut_prevision'];
//                                 $res_prev = mysql_query("SELECT * FROM PREVISION WHERE rut_pjuridica = $rut_prevision AND codigo_estado=1", $link);
                                
//                                 $verifica_prevision = 0;
//                                 while($row_x2 = mysql_fetch_array($res_prev)) {
//                                     if ($codigo_prevision == $row_x2["codigo_prevision"]) {
//                                         $verifica_prevision = 1;
//                                     }
//                                 }
                                
//                                 if(($verifica_prevision == 1) || $codigo_prevision == 999) {
//                                     $x++;
//                                     $hay_reg = 1;
//                                     $rut_puntos = devuelve_puntos($rut_prevision);
//                                     $digito = devuelve_digito($rut_prevision);
//                                     $fecha_admision = $row['fecha_boleta'];
//                                     $lugar_atencion = $row_fm['codigo_lugar_atencion'];
//                                     $nombre_lugar = devuelve_nombre_tabla($link, "ATENCION_CONSULTA", $lugar_atencion);
                                    
//                                     $tipo_cuenta = ($tipo_prestacion == 1) ? 2 : 1;
//                                     if($tipo_prestacion == 2) $tipo_texto = "CONSULTA(S)";
//                                     if($tipo_prestacion == 3) $tipo_texto = "EXAMEN(ES)";
                                    
//                                     $codigo_forma_pago = $row_fm['forma_pago'];
//                                     $monto_factura = $row_fm['monto'];
//                                     $monto_excedente = 0;
                                    
//                                     $res_bono = mysql_query("SELECT * FROM PAGO_CUENTA_DOCUMENTO AS pcd, DETALLE_PAGO_CUENTA AS dpc, DETALLE_BOLETA as db WHERE pcd.rut_paciente = dpc.rut_paciente AND pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo AND pcd.evento = dpc.evento AND pcd.codigo_cuenta = dpc.codigo_cuenta AND pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta AND dpc.rut_paciente = db.rut_paciente AND dpc.numero_hermano_gemelo = db.numero_hermano_gemelo AND dpc.evento = db.evento AND dpc.codigo_cuenta = db.codigo_cuenta AND dpc.codigo_pago_cuenta = db.codigo_pago_cuenta AND db.numero_documento = $numero_factura AND db.codigo_tipo_documento = $tipo_factura", $link);
                                    
//                                     if(mysql_num_rows($res_bono) > 0) {
//                                         while($row_bono = mysql_fetch_array($res_bono)) {
//                                             $forma_pago = $row_bono['codigo_forma_pago'];
//                                             $nombre_fp = devuelve_nombre_tabla($link, "FORMA_PAGO", $forma_pago);
//                                             $nombre_fp_corto = formato_reducido($nombre_fp, 3);
//                                             $codigo_caja = $row_bono['codigo_caja'];
                                            
//                                             if($forma_pago == 18 || $forma_pago == 20 || $forma_pago == 22) {
//                                                 $monto_bonificado += $row_bono['monto_pago_cuenta'];
//                                             } elseif($forma_pago == 48) {
//                                                 $monto_excedente += $row_bono['monto_pago_cuenta'];
//                                             } else {
//                                                 $monto_copago += $row_bono['monto_pago_cuenta'];
//                                             }
//                                         }
//                                         if($monto_copago >= $monto_excedente) {
//                                             $monto_copago -= $monto_excedente;
//                                         }
//                                     }
                                    
//                                     if(empty($monto_bonificado)) {
//                                         $monto_bonificado = $monto_factura;
//                                     }
                                    
//                                     $total_factura += $monto_factura;
//                                     $monto_bonificado_p = devuelve_puntos($monto_bonificado);
//                                     $monto_copago_p = devuelve_puntos($monto_copago);
//                                     $monto_excedente_p = devuelve_puntos($monto_excedente);
//                                     $monto_factura_p = devuelve_puntos($monto_factura);
                                    
//                                     $res_db = mysql_query("SELECT * FROM DETALLE_BOLETA WHERE numero_documento = $numero_factura AND codigo_estado = 1 AND codigo_tipo_documento = $tipo_factura", $link);
//                                     $total_registros = mysql_num_rows($res_db);
//                                     $row_db = mysql_fetch_array($res_db);
//                                     $codigo_flujo = $row_db['codigo_flujo_caja'];
                                    
//                                     if($codigo_prevision == 999) {
//                                         $nombre_prevision_sub = devuelve_nombre_tabla($link, "PREVISION", $row_prev['codigo_prevision']);
//                                     }
                                    
//                                     $nombre_paciente = "$total_registros BONO(S) $tipo_texto, SUCURSAL $nombre_lugar";
//                                 }
//                             }
//                         }
//                     }
//                 }
                

                
//             }

//             return $response->withJson([
//                 "message" => "Registros encontrados.",
//                 "data" => $data
//             ], 200);
//         } else {
//             return $response->withJson(["message" => "No se encontraron registros."], 404);
//         }
//     } catch (PDOException $e) {
//         return $response->withJson(["error" => "Error de conexión a la base de datos: " . $e->getMessage()], 500);
//     }
// });




$app->get('/api/conexionSoftland', function ($request, $response, $args){

    try { 
       
        // $servidores = 'dbSOFTLAND_PROD';  // Nombre del servidor de conexión a la base de datos
        $servidores = 'dbSOFTLAND_DEV';  // Nombre del servidor de conexión a la base de datos
    
        // Obtener la conexión a la base de datos desde el contenedor
        $conexion_db = $this->get($servidores);

        // Verificar si la conexión es exitosa
        if ($conexion_db) {
            // Realizar una consulta simple para verificar la conexión
            $stmt = $conexion_db->query("SELECT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si la consulta tiene resultado, la conexión es válida
            if ($result) {
                return $response->withJson(["message" => "Conexión exitosa a la base de datos dbSOFTLAND_DEV."], 200);
            } else {
                return $response->withJson(["error" => "No se pudo verificar la conexión a la base de datos."], 500);
            }
        } else {
            return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
        }

    } catch (Exception $e) {
        // Manejo de excepciones en caso de errores al obtener la conexión
        $this->logger->info("Error en archivo: ". __FILE__ .", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
        return $response->withJson(["error" => "Excepción capturada - ".$e->getMessage()], 400);
    }

});






// $app->get('/api/test', function ($request, $response, $args) {
//     $fecha=date("Y-m-d");
//     return $response->withJson($fecha, 200);
// });

//Ruta para obtener datos de Paciente
$app->get('/api/{parametro}', function ($request, $response, $args) {
    $this->logger->info("Slim-Skeleton '/' route");
});

//* Agregado el 25-10-2023
// $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
//     $handler = $this->notFoundHandler; // Manejar utilizando el controlador predeterminado de Slim para páginas no encontradas
//     return $handler($req, $res);
// });