<?php
set_time_limit(300); // Establece el límite de ejecución a 300 segundos (5 minutos)
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
class Rebsol{

    // protected $app;
    protected $container;

    // Constructor vacío
    public function __construct(\Slim\App $app) {
        $this->container = $app->getContainer();
    }

    public function get_rebsol(){
        return "test consultas rebsol";
    }


    public function conexion_rebsol() {
        try {
            // // Intentar una consulta simple para verificar la conexión
            // $conexion_db = $this->container->get('db18');
            
            // // Realizamos una consulta sencilla para verificar la conexión
            // $result = $conexion_db->query('SELECT 1');
            
            // if ($result) {
            //     return "Conexión exitosa.";
            // } else {
            //     return "La consulta no pudo ejecutarse.";
            // }
            // $servidores = array(
            //     'db18',
            //     'db250LF'
            // );


            $servidores = array(
                'db15',
                'db16',
                'dbMP'
            );

            $mensajes_conexion = [];

            foreach ($servidores as $servidor){
                $conexion_db =  $this->container->get($servidor);

                // Ejecuta una consulta simple para verificar la conexión
                $stmt = $conexion_db->query("SELECT 1");
                if ($stmt !== false) {
                    $mensajes_conexion[] = "Conexión e123123xitosa con el servidor: $servidor";
                } else {
                    $mensajes_conexion[] = "Fallo al ejecutar prueba en el servidor: $servidor";
                }

            }
            // Retornar todos los mensajes como un string separado por saltos de línea
            return implode("<br>", $mensajes_conexion);
        } catch (Exception $e) {
            // Si hay un error en la conexión
            return "Error de conexión: " . $e->getMessage();
        }

    }



    // new function 24-04-2025
    public function insert_pago_facturas_rebsol_new($data, $response){
        try {
           

            // return $response->withJson([
            //     "success" => "Inserción exitosa pago_facturas_rebsol .",
            //     "data" => $data,
            // ], 200);
            // exit();

            // Definir el servidor de la base de datos
            // $servidorRebsol = 'db18'; 
            // $numero_factura = "92770,92771, 92772, 92773, 92775, 92781, 92801, 92812"; //NUMERO DE FACTURAS TEST LOS LEONES 
            // $numero_factura = "92536, 92543, 92538, 92451"; //NUMERO DE FACTURAS TEST LA FLORIDA 
            // $numero_factura = "92781, 92801, 92812"; //NUMERO DE FACTURAS
            // $numero_factura = "92801, 92812"; //NUMERO DE FACTURAS
            $numero_factura = $data["folios"]; //NUMERO DE FACTURAS
            $codigo_prevision = $data["cod_prevision_rb"]; // CODIGO PREVISION REBSOL
            $numero_factura_aux = "";
            $tipo_factura_aux = "";
            $x = 0; // Inicializa $x antes de usarlo
            $total_factura = 0; // INICIALIZAR
            $numero_deposito = $data["comprobante"];
            $rut_funcionario = "99999999"; // RUT FUNCIONARO REGISTRA
            $rut_pjuridica = $data["rut_prevision"]; // RUT PREVISION 
            $forma_pago_tranf = 46; // FORMA PAGO TRANFERENCIA
            $rut_pjuridica_banco = $data["rut_banco"]; // RUT BANCO
            // $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            // $fecha_transferencia = DateTime::createFromFormat('d/m/Y', $fecha_banco)->format('Y-m-d H:i:s');

            $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_banco);
            $fecha_obj->setTime(0, 0, 0); // Forzar hora a 00:00:00
            $fecha_transferencia = $fecha_obj->format('Y-m-d H:i:s');
            $arrayfacturas= array();
            $arrayfacturas_cirujias= array();
            $arrayfacturas_bonos= array();
            $arrayfacturas_montos= array();

            // return $response->withJson([
            //     "success" => "Inserción exitosa pago_facturas_rebsol LF .",
            //     "data" => $data,
               
            // ], 200);
            // exit();
            
            // db250LF
            // db18
            // Obtener la conexión a la base de datos
            $conexion_db_rb = $this->container->get('db250LF');
            // Verificar si la conexión es exitosa
            if (!$conexion_db_rb) {
                return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
            }

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

            $stmt = $conexion_db_rb->prepare($sql1);
            $stmt->execute();

            // Obtener los registros
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verificar si hay resultados
            if (!empty($resultados)) {
                // $data = [];
                $contador1 = 0;
                $contador2 = 0;
                // Recorrer los resultados
                foreach ($resultados as $fila) {
                    $contador1++;
                   
                    $tipo_factura = $fila['codigo_tipo_documento'];
                    $rut_paciente = $fila['rut_paciente'];
                    $nhg =  $fila['numero_hermano_gemelo'];
                    $evento =  $fila['evento'];
                    $fecha_admision =  $fila['fecha_boleta'];
                    $estado_cuenta =  $fila['codigo_estado_cuenta'];
                    $pago_cuenta =  $fila['codigo_pago_cuenta'];
                    $codigo_flujo =  $fila['codigo_flujo_caja'];
                    $monto_factura =  $fila['monto_pago'];
                    // $monto_factura = (int) $fila['monto'];
                    $fecha_facturacion =  $fila['fecha_facturacion'];
                    $numero_documento =  $fila['numero_documento'];

                    // $total_factura += $monto_factura;

                    if (!in_array($numero_documento, $arrayfacturas)) {
                        $arrayfacturas[] = $numero_documento;
                    }

                    /*
                    PASO 2 La consulta recupera la información de una factura específica que está activa para obtener el codigo_tipo_facturacion  
                    *Obtiene el registro resumido de la factura.
                    */
                    // Consulta principal OLD
                    $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                            FROM FACTURA_MULTIPLE 
                            WHERE numero_factura = :numero_factura 
                            AND codigo_tipo_documento = :tipo_factura 
                            AND codigo_estado = 1";
                    $stmt_fm = $conexion_db_rb->prepare($sql);
                    $stmt_fm->execute([':numero_factura' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                    $row = $stmt_fm->rowCount();

                    // $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                    //         FROM FACTURA_MULTIPLE 
                    //         WHERE numero_factura LIKE :numero_factura 
                    //         AND codigo_tipo_documento = :tipo_factura 
                    //         AND codigo_estado = 1";

                    // $stmt_fm = $conexion_db_rb->prepare($sql);
                    // $stmt_fm->execute([
                    //     ':numero_factura' => $numero_factura . '%',  // incluir derivadas
                    //     ':tipo_factura' => $tipo_factura
                    // ]);

                    if ($stmt_fm->rowCount() > 0) {
                        $contador1++;
                        
                        if (!in_array($numero_documento, $arrayfacturas_bonos)) {
                            $arrayfacturas_bonos[] = $numero_documento;
                        }

                        $row_fm = $stmt_fm->fetch(PDO::FETCH_ASSOC);

                        if ($numero_factura_aux != $numero_factura || $tipo_factura_aux != $tipo_factura) {
                            $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];
                           
                            if (($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
                                $rut_prevision = $row_fm['rut_prevision'];
                               
                                // Consulta para PREVISION
                                $sql_prev = "SELECT * FROM PREVISION WHERE rut_pjuridica = :rut_prevision AND codigo_estado = 1";
                                $stmt_prev = $conexion_db_rb->prepare($sql_prev);
                                $stmt_prev->execute([':rut_prevision' => $rut_prevision]);
                    
                                $verifica_prevision = 0;
                                while ($row_x2 = $stmt_prev->fetch(PDO::FETCH_ASSOC)) {
                                    if ($codigo_prevision == $row_x2["codigo_prevision"]) {
                                        $verifica_prevision = 1;
                                    }
                                }
                    
                                if (($verifica_prevision == 1) || $codigo_prevision == 999) {
                                    $x++;
                                    $hay_reg = 1;
                                    $tipo_cuenta = ($tipo_prestacion == 1) ? 2 : 1;
                                    if ($tipo_prestacion == 2) $tipo_texto = "CONSULTA(S)";
                                    if ($tipo_prestacion == 3) $tipo_texto = "EXAMEN(ES)";
                    
                                    $codigo_forma_pago = $row_fm['forma_pago'];
                                    // $monto_factura = $row_fm['monto'];
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
                    
                                    // Consulta para DETALLE_BOLETA
                                    $sql_db = "SELECT * FROM DETALLE_BOLETA WHERE numero_documento = :numero_factura AND codigo_estado = 1 AND codigo_tipo_documento = :tipo_factura";
                                    $stmt_db = $conexion_db_rb->prepare($sql_db);
                                    $stmt_db->execute([':numero_factura' => $numero_factura, ':tipo_factura' => $tipo_factura]);
                                    $total_registros = $stmt_db->rowCount();
                                    $row_db = $stmt_db->fetch(PDO::FETCH_ASSOC);
                                    $codigo_flujo = $row_db['codigo_flujo_caja'];
                    
                                  
                                }
                            }
                       
                        }

                    }else{
                        // Registros de cirujias
                        $contador2++;

                        if (!in_array($numero_documento, $arrayfacturas_cirujias)) {
                            $arrayfacturas_cirujias[] = $numero_documento;
                        }
                       
                        // Inicializar variables
                        $tipo_cuenta = 0;
                        $codigo_prevision_paciente = 0;

                        // Determinar el tipo de cuenta y obtener el código de previsión del paciente
                        if ($estado_cuenta == 15) { // Consulta pagada 
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

                        }

                    }
            
                }
               
            } else {
                return $response->withJson(["message" => "No se encontraron registros."], 404);
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

            $actualizacion  = false;
            // Recorrer array con las facturas obtenidas de la consulta inicial 
            foreach ($arrayfacturas as $factura) {
                // echo "Número de factura: " . $factura . "<br>";
                 /*
                *Consulta para actualizar el codigo de control de la factura en la tabla DETALLE_BOLETA.
                *Actualizamos el codigo de control de la factura para el documento (factura) que se va actualizar y registrar el pago.
                */

                $documento = $factura;
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
                $stmt->bindParam(':numero_factura', $documento);
                $stmt->bindParam(':tipo_factura', $tipo_factura);

                // $stmt->execute();
                if ($stmt->execute()) {
                    $actualizacion = true;

                }else{
                    return $response->withJson(["error" => "Error al actualizar DETALLE_BOLETA de rebsol."], 400);
                  
                }

            }
           
            // Ejecutar la consulta
            if ($actualizacion === true) {
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
                $stmt->bindParam(':monto_factura', $total_factura);
                $stmt->bindParam(':codigo_forma_pago', $forma_pago_tranf);
                $stmt->bindParam(':comprobante_deposito', $numero_deposito);
                $stmt->bindParam(':fecha_registro', $fecha_actual);
                $stmt->bindParam(':fecha_factura', $fecha_transferencia);
                $stmt->bindParam(':rut_funcionario', $rut_funcionario);
                $stmt->bindParam(':rut_pjuridica', $rut_pjuridica_banco);
                $stmt->bindParam(':bonificado', $monto_bonificado);
                $stmt->bindParam(':copago', $monto_copago);
                $stmt->bindParam(':excedente', $monto_excedente);
                
                
                // Ejecutar la consulta
                if ($stmt->execute()) {
                    return $response->withJson([
                        "success" => "El registro del pago de facturas se realizó exitosamente en rebsol.",
                        "total_factura" => $total_factura,
                        "codigo_control_facturacion" => $codigo_control_facturacion,
                        // "contador 2" => $contador2,
                        "fecha_transferencia" => $fecha_transferencia,
                        "arrayfacturas" => $arrayfacturas,
                        // "arrayfacturas_cirujias" => $arrayfacturas_cirujias,
                        // "arrayfacturas_bonos" => $arrayfacturas_bonos,
                    ], 200);
                } else {
                    $errorInfo = $stmt->errorInfo();
                    return $response->withJson([
                        "error" => "Error al insertar en CONTROL_FACTURACION de rebsol.",
                        "detalle" => $errorInfo[2]
                    ], 400);
                }
            }
              

           
    
        } catch (Exception $e) {
            return $response->withJson([
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ], 500);
        }
    }






    // new function 25-04-2025
    public function insert_pago_facturas_rebsol_new_I($data, $response){
        try {
           

            // return $response->withJson([
            //     "success" => "Inserción exitosa pago_facturas_rebsol .",
            //     "data" => $data,
            // ], 200);
            // exit();

            // Definir el servidor de la base de datos
            // $servidorRebsol = 'db18'; 
            // $numero_factura = "92770,92771, 92772, 92773, 92775, 92781, 92801, 92812"; //NUMERO DE FACTURAS TEST LOS LEONES 
            // $numero_factura = "92536, 92543, 92538, 92451"; //NUMERO DE FACTURAS TEST LA FLORIDA 
            // $numero_factura = "92781, 92801, 92812"; //NUMERO DE FACTURAS
            // $numero_factura = "92493, 92500, 92503, 92521"; //NUMERO DE FACTURAS I
            $numero_factura = $data["folios"]; //NUMERO DE FACTURAS
            $codigo_prevision = $data["cod_prevision_rb"]; // CODIGO PREVISION REBSOL
            if($codigo_prevision == '100'){
                // Definir el array de códigos de previsión válidos
                $codigo_prevision = [100, 756];

            }
            // $numero_factura_aux = "";
            // $tipo_factura_aux = "";
            // $x = 0; // Inicializa $x antes de usarlo
            // $total_factura = 0; // INICIALIZAR
            $numero_deposito = $data["comprobante"];
            $rut_funcionario = "99999999"; // RUT FUNCIONARO REGISTRA
            $rut_pjuridica = $data["rut_prevision"]; // RUT PREVISION 
            $forma_pago_tranf = 46; // FORMA PAGO TRANFERENCIA
            $rut_pjuridica_banco = $data["rut_banco"]; // RUT BANCO
            // $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            // $fecha_transferencia = DateTime::createFromFormat('d/m/Y', $fecha_banco)->format('Y-m-d H:i:s');

            $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_banco);
            $fecha_obj->setTime(0, 0, 0); // Forzar hora a 00:00:00
            $fecha_transferencia = $fecha_obj->format('Y-m-d H:i:s');
            // $arrayfacturas= array();
            // $arrayfacturas_cirujias= array();
            // $arrayfacturas_bonos= array();
            // $arrayfacturas_montos= array();

            //  return $response->withJson([
            //     "success" => "Inserción exitosa pago_facturas_rebsol LF .",
            //     "data" => $data,
            //     "numero_deposito" => $numero_deposito,
               
            // ], 200);
            // exit();


            // $comprobantes_rb = []; // Arreglo para almacenar los números de documentos por servidor
            // $arrayfonasa= array();
            // // Definir el servidor (ejemplo: servidor 1)
            // $servidor = 'servidor1'; // Asegúrate de tener un valor para $servidor

            // $arrayFonasa = [
            //     "control_facturacion" => '1001',
            //     "bonos" => '1,2,3,4,5,6,7,8,9',
            //     "cirujias" => '11,12,13,14',
            // ];


            // // Después lo asignamos dentro de $comprobantes_rb[$servidor]
            // $comprobantes_rb[$servidor]["prevision 100"] = $arrayFonasa;

            // $arrayFonasa = [
            //     "control_facturacion" => '1002',
            //     "bonos" => '21,22,23,24,25,26,27,28,29',
            //     "cirujias" => '31,32,33,34',
            // ];


            //  // Después lo asignamos dentro de $comprobantes_rb[$servidor]
            //  $comprobantes_rb[$servidor]["prevision 756"] = $arrayFonasa;


            //   return $response->withJson([
            //     "success" => "Inserción exitosa pago_facturas_rebsol LF.",
            //     "comprobantes_rb" => $comprobantes_rb
               
            // ], 200);
            // exit();



            // Servidores de conexion 
            $servidores = array(
                'db18',
                'db250LF'
            );

            $comprobantes_rb = []; // Arreglo para almacenar los números de documentos por servidor

            foreach ($servidores as $servidor){
                // Obtener la conexión a la base de datos
                $conexion_db_rb =  $this->container->get($servidor);
                if (!$conexion_db_rb) {
                    return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos rebsol en el servidor '$servidor'."], 500);
                }
                $arrayfacturas= array();
                $arrayfacturas_cirujias= array();
                $arrayfacturas_cirujias_lasik= array();
                $arrayfacturas_bonos= array();
                $arrayfacturas_montos= array();
                $arrayfonasa= array();
                $arrayfonasa_lasik= array();

                $numero_factura_aux = "";
                $tipo_factura_aux = "";
                $x = 0; // Inicializa $x antes de usarlo
                $total_factura = 0; // INICIALIZAR
                $cod_prevision_anterior = "";

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
                        ORDER BY dpc.codigo_prevision, db.codigo_tipo_documento, db.numero_documento, db.fecha_facturacion
                    ";


                    // return $response->withJson([
                    //     "success" => "SQL DETALLE FACTURAS I.",
                    //     "sql1" => $sql1,
                    // ], 200);
                    // exit();

                $stmt = $conexion_db_rb->prepare($sql1);
                $stmt->execute();

                // Obtener los registros
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar si hay resultados
                if (!empty($resultados)) {
                    // $data = [];
                    $contador1 = 0;
                    $contador2 = 0;
                    // Recorrer los resultados
                    foreach ($resultados as $fila) {
                        $contador1++;
                    
                        $tipo_factura = $fila['codigo_tipo_documento'];
                        $rut_paciente = $fila['rut_paciente'];
                        $nhg =  $fila['numero_hermano_gemelo'];
                        $evento =  $fila['evento'];
                        $fecha_admision =  $fila['fecha_boleta'];
                        $estado_cuenta =  $fila['codigo_estado_cuenta'];
                        $pago_cuenta =  $fila['codigo_pago_cuenta'];
                        $codigo_flujo =  $fila['codigo_flujo_caja'];
                        $monto_factura =  $fila['monto_pago'];
                        // $monto_factura = (int) $fila['monto'];
                        $fecha_facturacion =  $fila['fecha_facturacion'];
                        $numero_documento =  $fila['numero_documento'];
                        $codigo_prevision_rb =  $fila['codigo_prevision'];

                        if($codigo_prevision_rb != $cod_prevision_anterior){
                            /*REGISTRAR PAGO DE FACTURAS*/
                            // 1.Funcion obtener numero control_factura.
                            // 2.Funcion actualizar detalle boleta.
                            // 3.Funcion registrar control de factura.
                            
                            
                            //1
                            $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                            $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];

                            //2
                            $numerosControl = $this->actualizar_detalle_boleta($conexion_db_rb, $arrayfacturas, $codigo_control_facturacion, $codigo_flujo, $tipo_factura);
                            if ($numerosControl !== true) {
                                return $response->withJson($numerosControl, 400);
                                
                            }

                            //3
                            $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $cod_prevision_anterior, $total_factura, $forma_pago_tranf, $numero_deposito, 
                                                $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado,  $monto_copago, $monto_excedente);

                            $previsionNombre = "prevision " . $cod_prevision_anterior;
                            // Primero armamos el array temporal
                            $arrayFonasa = [
                                "control_facturacion" => $codigo_control_facturacion,
                                "bonos" => implode(",", $arrayfacturas_bonos),
                                "cirujias" => implode(",", $arrayfacturas_cirujias),
                            ];

                            $comprobantes_rb[$servidor][$previsionNombre] = $arrayFonasa;

                            // Inicializar los montos de bonificación, copago y excedente
                            $monto_bonificado = 0;
                            $monto_copago = 0;
                            $monto_excedente = 0;
                            $total_factura = 0;
                            $arrayfacturas= array();
                           
                        }

                        // $total_factura += $monto_factura;

                        if (!in_array($numero_documento, $arrayfacturas)) {
                            $arrayfacturas[] = $numero_documento;
                        }

                        /*
                        PASO 2 La consulta recupera la información de una factura específica que está activa para obtener el codigo_tipo_facturacion  
                        *Obtiene el registro resumido de la factura.
                        */
                        // Consulta principal OLD
                        $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                                FROM FACTURA_MULTIPLE 
                                WHERE numero_factura = :numero_factura 
                                AND codigo_tipo_documento = :tipo_factura 
                                AND codigo_estado = 1";
                        $stmt_fm = $conexion_db_rb->prepare($sql);
                        $stmt_fm->execute([':numero_factura' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                        $row = $stmt_fm->rowCount();

                        // $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                        //         FROM FACTURA_MULTIPLE 
                        //         WHERE numero_factura LIKE :numero_factura 
                        //         AND codigo_tipo_documento = :tipo_factura 
                        //         AND codigo_estado = 1";

                        // $stmt_fm = $conexion_db_rb->prepare($sql);
                        // $stmt_fm->execute([
                        //     ':numero_factura' => $numero_factura . '%',  // incluir derivadas
                        //     ':tipo_factura' => $tipo_factura
                        // ]);

                        if ($stmt_fm->rowCount() > 0) {
                            $contador1++;
                            
                            if (!in_array($numero_documento, $arrayfacturas_bonos)) {
                                $arrayfacturas_bonos[] = $numero_documento;
                            }

                            $row_fm = $stmt_fm->fetch(PDO::FETCH_ASSOC);

                            if ($numero_factura_aux != $numero_factura || $tipo_factura_aux != $tipo_factura) {
                                $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];
                            
                                if (($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
                                    $rut_prevision = $row_fm['rut_prevision'];
                                
                                    // Consulta para PREVISION
                                    $sql_prev = "SELECT * FROM PREVISION WHERE rut_pjuridica = :rut_prevision AND codigo_estado = 1";
                                    $stmt_prev = $conexion_db_rb->prepare($sql_prev);
                                    $stmt_prev->execute([':rut_prevision' => $rut_prevision]);
                        
                                    $verifica_prevision = 0;
                                    while ($row_x2 = $stmt_prev->fetch(PDO::FETCH_ASSOC)) {
                                        if ($codigo_prevision == $row_x2["codigo_prevision"]) {
                                            $verifica_prevision = 1;
                                        }
                                    }
                        
                                    if (($verifica_prevision == 1) || $codigo_prevision == 999) {
                                        $x++;
                                        $hay_reg = 1;
                                        $tipo_cuenta = ($tipo_prestacion == 1) ? 2 : 1;
                                        if ($tipo_prestacion == 2) $tipo_texto = "CONSULTA(S)";
                                        if ($tipo_prestacion == 3) $tipo_texto = "EXAMEN(ES)";
                        
                                        $codigo_forma_pago = $row_fm['forma_pago'];
                                        // $monto_factura = $row_fm['monto'];
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
                        
                                        // Consulta para DETALLE_BOLETA
                                        $sql_db = "SELECT * FROM DETALLE_BOLETA WHERE numero_documento = :numero_factura AND codigo_estado = 1 AND codigo_tipo_documento = :tipo_factura";
                                        $stmt_db = $conexion_db_rb->prepare($sql_db);
                                        $stmt_db->execute([':numero_factura' => $numero_factura, ':tipo_factura' => $tipo_factura]);
                                        $total_registros = $stmt_db->rowCount();
                                        $row_db = $stmt_db->fetch(PDO::FETCH_ASSOC);
                                        $codigo_flujo = $row_db['codigo_flujo_caja'];
                        
                                    
                                    }
                                }
                        
                            }

                        }else{
                            // Registros de cirujias
                            $contador2++;

                            if (!is_array($codigo_prevision)) {
                                $codigo_prevision = [$codigo_prevision]; // Lo convertir en array si no lo es
                            }

                            // if (!in_array($numero_documento, $arrayfacturas_cirujias)) {
                            //     $arrayfacturas_cirujias[] = $numero_documento;
                            // }
                        
                            // Inicializar variables
                            $tipo_cuenta = 0;
                            $codigo_prevision_paciente = 0;

                            // Determinar el tipo de cuenta y obtener el código de previsión del paciente
                            if ($estado_cuenta == 15) { // Consulta pagada 
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
                            // if ($codigo_prevision_paciente == $codigo_prevision) {
                            if (in_array($codigo_prevision_paciente, $codigo_prevision)){

                                if($codigo_prevision_paciente != $cod_prevision_anterior){

                                    $total_factura= 0;

                                }

                                // return $response->withJson([
                                //     "success" => "El registro  FONSA LASIK VALIDO.",
                                //     "codigo_prevision_paciente" => $codigo_prevision_paciente,
                                //     "codigo_prevision" => $codigo_prevision,
                                // ], 200);
                                // exit();
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

                            }

                        }
                        if($codigo_prevision_paciente == '756'){
                            //almacenar array fonasa lasik
                            
                            if (!in_array($numero_documento, $arrayfacturas_cirujias_lasik)) {
                                $arrayfacturas_cirujias_lasik[] = $numero_documento;
                            }

                        }else{
                            if (!in_array($numero_documento, $arrayfacturas_cirujias)){
                                $arrayfacturas_cirujias[] = $numero_documento;
                            }
                            //almacenar valores array fonasa

                        }
                        $cod_prevision_anterior = $codigo_prevision_rb;
                
                    }
                
                } else {
                    return $response->withJson(["message" => "No se encontraron registros."], 404);
                }

              

                // /*
                // *Consulta para obtener el ultimo codigo de control  de la tabla CONTROL_FACTURACION.
                // *Sumamos 1 para generar el codigo correlativo para el control de la factura que se va actualizar y registrar el pago.
                // */
                // $query = "SELECT MAX(codigo_control_facturacion) FROM CONTROL_FACTURACION";
                // $stmt = $conexion_db_rb->prepare($query);
                // $stmt->execute();

                // // Obtener el resultado
                // $row_id = $stmt->fetch(PDO::FETCH_NUM);
                // $codigo_control_facturacion = ($row_id[0] ?? 0) + 1;


                // return $response->withJson([
                //     "success" => "El registro del pago de facturas se realizó exitosamente en rebsol (FONASA LASIK).",
                //     "codigo_control_facturacion" => $codigo_control_facturacion,
                //     "codigo_prevision" => $codigo_prevision,
                //     "total_factura" => $total_factura,
                //     "forma_pago_tranf" => $forma_pago_tranf,
                //     "numero_deposito" => $numero_deposito,
                //     "fecha_transferencia" => $fecha_transferencia,
                //     "rut_funcionario" => $rut_funcionario,
                //     "rut_pjuridica_banco" => $rut_pjuridica_banco,
                //     "monto_bonificado" => $monto_bonificado,
                //     // "monto_copago" => $monto_copago,
                //     // "monto_excedente" => $monto_excedente,
                    
                // ], 200);
                // exit();





                /*REGISTRAR PAGO DE FACTURAS*/
                // 1.Funcion obtener numero control_factura.
                // 2.Funcion actualizar detalle boleta.
                // 3.Funcion registrar control de factura.
                            
                //1
                $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                if ($codigo_control_facturacion == 0){
                    return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);

                }

                //2
                $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $arrayfacturas, $codigo_control_facturacion, $codigo_flujo, $tipo_factura);
                if ($respuesta !== true) {
                    return $response->withJson($respuesta, 400);
                    
                }
                return $response->withJson([
                    "success" => "Inserción exitosa pago_facturas_rebsol NEW_II CODIGO PREVISION ACTUAL.",
                    "numerosControl" => $respuesta,
                ], 200);
                exit();

                //3
                $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision, $total_factura, $forma_pago_tranf, $numero_deposito, 
                                    $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado,  $monto_copago, $monto_excedente);

                if (!$respuesta) {
                    return $response->withJson($respuesta, 400);
                }

                $previsionNombre = "prevision " . $codigo_prevision;
                // Primero armamos el array temporal
                $arrayFonasa = [
                    "control_facturacion" => $codigo_control_facturacion,
                    "bonos" => implode(",", $arrayfacturas_bonos),
                    "cirujias" => implode(",", $arrayfacturas_cirujias),
                ];

                $comprobantes_rb[$servidor][$previsionNombre] = $arrayFonasa;

                // $actualizacion  = false;
                // // Recorrer array con las facturas obtenidas de la consulta inicial 
                // foreach ($arrayfacturas as $factura) {
                //     // echo "Número de factura: " . $factura . "<br>";
                //     /*
                //     *Consulta para actualizar el codigo de control de la factura en la tabla DETALLE_BOLETA.
                //     *Actualizamos el codigo de control de la factura para el documento (factura) que se va actualizar y registrar el pago.
                //     */

                //     $documento = $factura;
                //     $query ="UPDATE DETALLE_BOLETA SET 
                //             codigo_control_facturacion = :codigo_control_facturacion, 
                //             codigo_flujo_caja = :codigo_flujo 
                //             WHERE numero_documento = :numero_factura 
                //             AND codigo_estado = 1 
                //             AND codigo_tipo_documento = :tipo_factura 
                //             AND codigo_control_facturacion = 0";

                //     $stmt = $conexion_db_rb->prepare($query);

                //     // Bind de parámetros para evitar inyección SQL
                //     $stmt->bindParam(':codigo_control_facturacion', $codigo_control_facturacion);
                //     $stmt->bindParam(':codigo_flujo', $codigo_flujo);
                //     $stmt->bindParam(':numero_factura', $documento);
                //     $stmt->bindParam(':tipo_factura', $tipo_factura);

                //     // $stmt->execute();
                //     if ($stmt->execute()) {
                //         $actualizacion = true;

                //     }else{
                //         return $response->withJson(["error" => "Error al actualizar DETALLE_BOLETA de rebsol."], 400);
                    
                //     }

                // }

                // return $response->withJson([
                //     "success" => "El registro del pago de facturas se realizó exitosamente en rebsol.",
                //     "total_factura" => $actualizacion,
                // ], 200);
            
                // Ejecutar la consulta
                // if ($actualizacion === true) {
                //     // echo "Actualización exitosa";
                //     $fecha_actual = date("Y-m-d H:i:s"); //fecha de registro pago de factura

                //     // return $response->withJson([
                //     //     "success" => "El registro del pago de facturas se realizó exitosamente en rebsol.",
                //     //     "total_factura" => $actualizacion,
                //     //     // "fecha_actual" => $fecha_actual,
                //     //     "codigo_control_facturacion" => $codigo_control_facturacion,
                //     //     // "codigo_prevision" => $codigo_prevision,
                //     //     // "total_factura" => $total_factura,
                //     //     // "forma_pago_tranf" => $forma_pago_tranf,
                //     //     // "numero_deposito" => $numero_deposito,
                //     //     // "fecha_actual" => $fecha_actual,
                //     //     // "fecha_transferencia" => $fecha_transferencia,
                //     //     // "rut_funcionario" => $rut_funcionario,
                //     //     // "rut_pjuridica_banco" => $rut_pjuridica_banco,
                //     //     // "monto_bonificado" => $monto_bonificado,
                //     //     // "monto_copago" => $monto_copago,
                //     //     // "monto_excedente" => $monto_excedente,
                        
                //     // ], 200);
                    
                   

                
                
                //     $query = "INSERT INTO CONTROL_FACTURACION (
                //         codigo_control_facturacion, 
                //         codigo_prevision, 
                //         monto_factura, 
                //         codigo_forma_pago, 
                //         comprobante_deposito, 
                //         fecha_registro, 
                //         fecha_factura, 
                //         rut_funcionario, 
                //         rut_pjuridica, 
                //         bonificado, 
                //         copago, 
                //         excedente
                //     ) VALUES (
                //         :codigo_control_facturacion, 
                //         :codigo_prevision, 
                //         :monto_factura, 
                //         :codigo_forma_pago, 
                //         :comprobante_deposito, 
                //         :fecha_registro, 
                //         :fecha_factura, 
                //         :rut_funcionario, 
                //         :rut_pjuridica, 
                //         :bonificado, 
                //         :copago, 
                //         :excedente
                //     )";

                //     $stmt = $conexion_db_rb->prepare($query);
                    
                //     // Asociar los valores a los parámetros de la consulta
                //     $stmt->bindParam(':codigo_control_facturacion', $codigo_control_facturacion);
                //     $stmt->bindParam(':codigo_prevision', $codigo_prevision);
                //     $stmt->bindParam(':monto_factura', $total_factura);
                //     $stmt->bindParam(':codigo_forma_pago', $forma_pago_tranf);
                //     $stmt->bindParam(':comprobante_deposito', $numero_deposito);
                //     $stmt->bindParam(':fecha_registro', $fecha_actual);
                //     $stmt->bindParam(':fecha_factura', $fecha_transferencia);
                //     $stmt->bindParam(':rut_funcionario', $rut_funcionario);
                //     $stmt->bindParam(':rut_pjuridica', $rut_pjuridica_banco);
                //     $stmt->bindParam(':bonificado', $monto_bonificado);
                //     $stmt->bindParam(':copago', $monto_copago);
                //     $stmt->bindParam(':excedente', $monto_excedente);



                //     if (!$stmt->execute()) {
                //         $errorInfo = $stmt->errorInfo();
                //         return $response->withJson([
                //             "error" => "Error al insertar en CONTROL_FACTURACION de rebsol.",
                //             "detalle" => $errorInfo[2]
                //         ], 400);
                //     }
                    
                    
                //     // // Ejecutar la consulta
                //     // if ($stmt->execute()) {
                //     //     return $response->withJson([
                //     //         "success" => "El registro del pago de facturas se realizó exitosamente en rebsol.",
                //     //         "total_factura" => $total_factura,
                //     //         "codigo_control_facturacion" => $codigo_control_facturacion,
                //     //         // "contador 2" => $contador2,
                //     //         "fecha_transferencia" => $fecha_transferencia
                //     //         // "numeros_documentos_por_servidor" => $numeros_documentos_por_servidor,
                //     //         // "arrayfacturas_cirujias" => $arrayfacturas_cirujias,
                //     //         // "arrayfacturas_bonos" => $arrayfacturas_bonos,
                //     //     ], 200);
                //     // } else {
                //     //     $errorInfo = $stmt->errorInfo();
                //     //     return $response->withJson([
                //     //         "error" => "Error al insertar en CONTROL_FACTURACION de rebsol.",
                //     //         "detalle" => $errorInfo[2]
                //     //     ], 400);
                //     // }
                // }



                // Guardar sucursal , codigo_control_facturacion y folios
                $comprobantes_rb[$servidor] = [
                    'codigo_control_facturacion' => $codigo_control_facturacion,
                    'Folios' => implode(',', $arrayFolios)
                ];
            }
            
    
        } catch (Exception $e) {
            return $response->withJson([
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ], 500);
        }
    }






    // new function 28-04-2025
    public function insert_pago_facturas_rebsol_new_II($data, $response){
        try {
           
            // Definir el servidor de la base de datos
            // $numero_factura = "92536,92543,92538,92451"; //NUMERO DE FACTURAS TEST LA FLORIDA 
            // $numero_factura = "92452,92453,92455,92458,92537,92539,92541"; //NUMERO DE FACTURAS TEST 
            //NUMERO DE FACTURAS TEST 
            // $numero_factura = "93568,93581,93581,93575,93576,93577,93578,93579,93613,93614,93615,93616,93617,93618,93619,93620,93621,93622,93623,93624,93626,93627,93628,93630,93632";

            $numero_factura = $data["folios"]; //NUMERO DE FACTURAS
            $codigo_prevision = $data["cod_prevision_rb"];// CODIGO PREVISION REBSOL
            // $codigo_prevision ='100';
            if($codigo_prevision == '100'){
                // Definir el array de códigos de previsión válidos
                $codigo_prevision = [100, 756];

            }
          
            // $numero_deposito = '2000'; //TEST
            $numero_deposito = $data["comprobante"];
            $rut_funcionario = "99999999"; // RUT FUNCIONARO REGISTRA
            $rut_pjuridica = $data["rut_prevision"]; // RUT PREVISION 
            $forma_pago_tranf = 46; // FORMA PAGO TRANFERENCIA
            $rut_pjuridica_banco = $data["rut_banco"]; // RUT BANCO
            $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_banco);
            $fecha_obj->setTime(0, 0, 0); // Forzar hora a 00:00:00
            $fecha_transferencia = $fecha_obj->format('Y-m-d H:i:s');
           

            // Servidores de conexion 
            // $servidores = array(
            //     'db18',
            //     'db250LF'
            // );
            $servidores = array(
                'db15',
                'db16',
                'dbMP'
            );

            $comprobantes_rb = []; // Arreglo para almacenar los números de documentos por servidor
            $count_servidor = 0;
            foreach ($servidores as $servidor){
                // Obtener la conexión a la base de datos
                $conexion_db_rb =  $this->container->get($servidor);
                if (!$conexion_db_rb) {
                    return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos rebsol en el servidor '$servidor'."], 500);
                }
                $arrayfacturas= array();
                $arrayfacturas_cirujias= array();
                $arrayfacturas_cirujias_lasik= array();
                $arrayfacturas_bonos= array();
                $arrayfacturas_montos= array();
                $arrayfonasa= array();
                $arrayfonasa_lasik= array();

                $array_bonos= array();
                $array_cirujias= array();

                $total_factura_bono = 0;
                $monto_bonificado_bono_auxi = 0;
                $monto_copago_bono_auxi = 0;
                $total_factura_cirujia = 0;
                $monto_bonificado_cir_auxi = 0;
                $monto_copago_cir_auxi = 0;
               

                $numero_factura_aux = "";
                $tipo_factura_aux = "";
                $x = 0; // Inicializa $x antes de usarlo
                $total_factura = 0; // INICIALIZAR
                $cod_prevision_anterior = "";
                $count_tipo_prestacion = 0;
                $count_servidor++;

                $monto_bonificado = 0;
                $monto_copago = 0;
                $monto_bonificado1 = 0;
                $monto_copago1 = 0;

                $monto_bonificado_lasik =0;
                $monto_excedente_lasik =0;
                $monto_copago_lasik =0;

                $array_bono = [
                    "facturas" => "",
                    "monto_bonificado" => 0,
                    "monto_copago" => 0,
                    "monto_excedente" => 0,
                    "codigo_flujo" => 0,
                    "total_factura" => 0
                ];

                $array_cirujia_lasik = [
                    "facturas" => "",
                    "monto_bonificado" => 0,
                    "monto_copago" => 0,
                    "monto_excedente" => 0,
                    "codigo_flujo" => 0,
                    "total_factura" => 0
                ];
                
                $array_cirujia = [
                    "facturas" => "",
                    "monto_bonificado" => 0,
                    "monto_copago" => 0,
                    "monto_excedente" => 0,
                    "codigo_flujo" => 0,
                    "total_factura" => 0
                ];


                /*
                * PASO 1 Esta consulta obtiene información de pacientes y sus pagos, asociando detalles de facturación y boletas.
                */
                // Codigo que permite buscar facturas derivadas.
                // Convierte el string en un arreglo separado por comas
                $numeros_base = explode(',', $numero_factura);
                // Construir las condiciones LIKE dinámicamente
                $condiciones_like = array_map(function($num) {
                    return "db.numero_documento LIKE '{$num}%'";
                }, $numeros_base);
                // Unir todas con OR
                $condicion_where = implode(' OR ', $condiciones_like);

             
                // Consulta actualizada para buscar derivados de facturas
                $sql= "SELECT *, pcd.monto AS monto_pago, DATE_FORMAT(db.fecha_facturacion, '%d/%m/%Y') AS fecha_boleta 
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
                        AND (
                            $condicion_where
                        )
                    GROUP BY pcd.rut_paciente, pcd.numero_hermano_gemelo, pcd.evento, pcd.codigo_cuenta, pcd.codigo_pago_cuenta
                    ORDER BY dpc.codigo_prevision, db.codigo_tipo_documento, db.numero_documento, db.fecha_facturacion
                ";


                // return $response->withJson([
                //     "success" => "Inserción exitosa pago_facturas_rebsol NEW_II SQL.",
                //     "sql" => $sql,
                // ], 200);

                // $stmt = $conexion_db_rb->prepare($sql1);
                $stmt = $conexion_db_rb->prepare($sql);
                $stmt->execute();

                // Obtener los registros
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar si hay resultados
                if (!empty($resultados)) {
                    // $data = [];
                    $contador1 = 0;
                    $contador2 = 0;
                    // Recorrer los resultados
                    foreach ($resultados as $fila) {
                        $contador1++;
                    
                        $tipo_factura = $fila['codigo_tipo_documento'];
                        $rut_paciente = $fila['rut_paciente'];
                        $nhg =  $fila['numero_hermano_gemelo'];
                        $evento =  $fila['evento'];
                        $fecha_admision =  $fila['fecha_boleta'];
                        $estado_cuenta =  $fila['codigo_estado_cuenta'];
                        $pago_cuenta =  $fila['codigo_pago_cuenta'];
                        $codigo_flujo =  $fila['codigo_flujo_caja'];
                        $monto_factura =  $fila['monto_pago'];
                        // $monto_factura = $fila['monto'];
                        $fecha_facturacion =  $fila['fecha_facturacion'];
                        $numero_documento =  $fila['numero_documento'];
                        $codigo_prevision_rb =  $fila['codigo_prevision'];
                       

                        if (!in_array($numero_documento, $arrayfacturas)) {
                            $arrayfacturas[] = $numero_documento;
                        }

                        /*
                        PASO 2 La consulta recupera la información de una factura específica que está activa para obtener el codigo_tipo_facturacion  
                        *Obtiene el registro resumido de la factura.
                        */
                        $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                                FROM FACTURA_MULTIPLE 
                                WHERE numero_factura = :numero_factura 
                                AND codigo_tipo_documento = :tipo_factura 
                                AND codigo_estado = 1";
                        $stmt_fm = $conexion_db_rb->prepare($sql);
                        $stmt_fm->execute([':numero_factura' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                        $row = $stmt_fm->rowCount();

                        if ($stmt_fm->rowCount() > 0) {
                            // Registros de bonos
                            $contador1++;
                            $codigo_prevision = '100';
                            $row_fm = $stmt_fm->fetch(PDO::FETCH_ASSOC);

                            if ($numero_factura_aux != $numero_documento || $tipo_factura_aux != $tipo_factura) {
                                $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];
                              

                                if (!in_array($numero_documento, $arrayfacturas_bonos)) {
                                    $arrayfacturas_bonos[] = $numero_documento;
                                }

                               
                            
                                if (($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
                                    $rut_prevision = $row_fm['rut_prevision'];
                                
                                    // Consulta para PREVISION
                                    $sql_prev = "SELECT * FROM PREVISION WHERE rut_pjuridica = :rut_prevision AND codigo_estado = 1";
                                    $stmt_prev = $conexion_db_rb->prepare($sql_prev);
                                    $stmt_prev->execute([':rut_prevision' => $rut_prevision]);
                        
                                    $verifica_prevision = 0;
                                    while ($row_x2 = $stmt_prev->fetch(PDO::FETCH_ASSOC)) {
                                        if ($codigo_prevision == $row_x2["codigo_prevision"]) {
                                            $verifica_prevision = 1;
                                        }
                                    }

                                    if (($verifica_prevision == 1) || $codigo_prevision == 999) {
                                       
                                        $x++;
                                        $hay_reg = 1;
                                        $tipo_cuenta = ($tipo_prestacion == 1) ? 2 : 1;
                                        if ($tipo_prestacion == 2) $tipo_texto = "CONSULTA(S)";
                                        if ($tipo_prestacion == 3) $tipo_texto = "EXAMEN(ES)";
                        
                                        $codigo_forma_pago = $row_fm['forma_pago'];
                                        $monto_factura = $row_fm['monto'];
                                        $monto_excedente1 = 0;

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
                                                    WHERE db.numero_documento = :numero_documento 
                                                    AND db.codigo_tipo_documento = :tipo_factura";

                                        
                                        $stmt_bono = $conexion_db_rb->prepare($sql_bono);
                                        $stmt_bono->execute([':numero_documento' => $numero_documento, ':tipo_factura' => $tipo_factura]);

                                        if ($stmt_bono->rowCount() > 0) {
                                            while ($row_bono = $stmt_bono->fetch(PDO::FETCH_ASSOC)) {
                                                $forma_pago = $row_bono['codigo_forma_pago'];
                                                $codigo_caja = $row_bono['codigo_caja'];
                        
                                                if ($forma_pago == 18 || $forma_pago == 20 || $forma_pago == 22) {
                                                    $monto_bonificado1 += $row_bono['monto_pago_cuenta'];
                                                } elseif ($forma_pago == 48) {
                                                    $monto_excedente1 += $row_bono['monto_pago_cuenta'];
                                                } else {
                                                    $monto_copago1 += $row_bono['monto_pago_cuenta'];
                                                }
                                            }
                        
                                            if ($monto_copago1 >= $monto_excedente1) {
                                                $monto_copago1 -= $monto_excedente1;
                                            }

                                        }
                                       
                        
                                        if (empty($monto_bonificado1)) {
                                            $monto_bonificado1 = $monto_factura1;
                                        }

                                        $total_factura += $monto_factura;
                                        $total_factura_bono += $monto_factura;

                                      

                                        // Consulta para DETALLE_BOLETA
                                        $sql_db = "SELECT * FROM DETALLE_BOLETA WHERE numero_documento = :numero_documento AND codigo_estado = 1 AND codigo_tipo_documento = :tipo_factura";
                                        $stmt_db = $conexion_db_rb->prepare($sql_db);
                                        $stmt_db->execute([':numero_documento' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                                        $total_registros = $stmt_db->rowCount();
                                        $row_db = $stmt_db->fetch(PDO::FETCH_ASSOC);
                                        $codigo_flujo = $row_db['codigo_flujo_caja'];


                                        // Acumula los folios como string separado por coma
                                        if ($array_bono["facturas"] !== "") {
                                            $array_bono["facturas"] .= ",";
                                        }
                                        $array_bono["facturas"] .= $numero_documento;

                                       
                                        $array_bono["monto_bonificado"] = $monto_bonificado1;
                                        $array_bono["monto_copago"] = $monto_copago1;
                                        $array_bono["monto_excedente"] = $monto_excedente1;
                                        $array_bono["codigo_flujo"] = $codigo_flujo;
                                        $array_bono["total_factura"] += $monto_factura;
                        
                                    
                                    }
                                }
                                $numero_factura_aux = $numero_documento;
                                $tipo_factura_aux = $tipo_factura;
                        
                            }
                            if($codigo_prevision == '100'){
                                // Definir el array de códigos de previsión válidos
                                $codigo_prevision = [100, 756];
                
                            }
                            

                        }else {
                            // Registros de cirugías
                            $contador2++;
                        
                            if (!is_array($codigo_prevision)) {
                                $codigo_prevision = [$codigo_prevision]; // Convertir en array si no lo es
                            }
                        
                            if (!in_array($numero_documento, $arrayfacturas_cirujias)) {
                                $arrayfacturas_cirujias[] = $numero_documento;
                            }
                        
                            // Inicializar variables
                            $tipo_cuenta = 0;
                            $codigo_prevision_paciente = 0;
                        
                            // Determinar tipo de cuenta y obtener el código de previsión del paciente
                            if ($estado_cuenta == 15) {
                                $tipo_cuenta = 1;
                                $stmt_res = $conexion_db_rb->prepare("SELECT * FROM RESERVA_ATENCION WHERE rut_pnatural = :rut_paciente AND numero_hermano_gemelo = :nhg AND evento = :evento AND codigo_pago_cuenta = :pago_cuenta");
                                $stmt_res->execute([
                                    ':rut_paciente' => $rut_paciente,
                                    ':nhg' => $nhg,
                                    ':evento' => $evento,
                                    ':pago_cuenta' => $pago_cuenta
                                ]);
                            } else {
                                $tipo_cuenta = 2;
                                $stmt_res = $conexion_db_rb->prepare("SELECT * FROM DATO_INGRESO WHERE rut_paciente = :rut_paciente AND numero_hermano_gemelo = :nhg AND evento = :evento");
                                $stmt_res->execute([
                                    ':rut_paciente' => $rut_paciente,
                                    ':nhg' => $nhg,
                                    ':evento' => $evento
                                ]);
                            }
                        
                            $row_res = $stmt_res->fetch(PDO::FETCH_ASSOC);
                            $codigo_prevision_paciente = $row_res['codigo_prevision'];
                        
                            if (in_array($codigo_prevision_paciente, $codigo_prevision)) {
                                $x++;
                                $hay_reg = 1;
                                $total_factura += $monto_factura;
                                $total_factura_cirujia += $monto_factura;
                        
                                $monto_bonificado = 0;
                                $monto_copago = 0;
                                $monto_excedente = 0;
                        
                                $monto_bonificado_lasik = 0;
                                $monto_excedente_lasik = 0;
                                $monto_copago_lasik = 0;
                        
                                $stmt_bono = $conexion_db_rb->prepare("
                                    SELECT * FROM PAGO_CUENTA_DOCUMENTO AS pcd
                                    JOIN DETALLE_PAGO_CUENTA AS dpc ON 
                                        pcd.rut_paciente = dpc.rut_paciente AND
                                        pcd.numero_hermano_gemelo = dpc.numero_hermano_gemelo AND
                                        pcd.evento = dpc.evento AND
                                        pcd.codigo_cuenta = dpc.codigo_cuenta AND
                                        pcd.codigo_pago_cuenta = dpc.codigo_pago_cuenta
                                    WHERE dpc.rut_paciente = :rut_paciente 
                                    AND dpc.numero_hermano_gemelo = :nhg 
                                    AND dpc.evento = :evento 
                                    AND dpc.codigo_pago_cuenta = :pago_cuenta
                                ");
                                $stmt_bono->execute([
                                    ':rut_paciente' => $rut_paciente,
                                    ':nhg' => $nhg,
                                    ':evento' => $evento,
                                    ':pago_cuenta' => $pago_cuenta
                                ]);
                        
                                if ($stmt_bono->rowCount() > 0) {
                                    while ($row_bono = $stmt_bono->fetch(PDO::FETCH_ASSOC)) {
                                        $forma_pago = $row_bono['codigo_forma_pago'];
                                        $monto = $row_bono['monto_pago_cuenta'];
                        
                                        if ($codigo_prevision_paciente == 100) {
                                            if (in_array($forma_pago, [18, 22])) {
                                                $monto_bonificado += $monto;
                                            } elseif ($forma_pago == 48) {
                                                $monto_excedente += $monto;
                                            } else {
                                                $monto_copago += $monto;
                                            }
                                        } else {
                                            if (in_array($forma_pago, [18, 22])) {
                                                $monto_bonificado_lasik += $monto;
                                            } elseif ($forma_pago == 48) {
                                                $monto_excedente_lasik += $monto;
                                            } else {
                                                $monto_copago_lasik += $monto;
                                            }
                                        }
                                    }
                        
                                    // Ajustes de copago
                                    if ($codigo_prevision_paciente == 100) {
                                        if ($monto_copago >= $monto_excedente) {
                                            $monto_copago -= $monto_excedente;
                                        }
                                    } else {
                                        if ($monto_copago_lasik >= $monto_excedente_lasik) {
                                            $monto_copago_lasik -= $monto_excedente_lasik;
                                        }
                                    }
                                }
                        
                                // Asignación de facturas y montos
                                if ($codigo_prevision_paciente == 100) {
                                    if (!isset($array_cirujia["facturas"])) {
                                        $array_cirujia["facturas"] = "";
                                    }
                                    if ($array_cirujia["facturas"] !== "") {
                                        $array_cirujia["facturas"] .= ",";
                                    }
                                    $array_cirujia["facturas"] .= $numero_documento;
                        
                                    $array_cirujia["monto_bonificado"] += $monto_bonificado;
                                    $array_cirujia["monto_copago"] += $monto_copago;
                                    $array_cirujia["monto_excedente"] += $monto_excedente;
                                    $array_cirujia["codigo_flujo"] = $codigo_flujo;
                                    $array_cirujia["total_factura"] += $monto_factura;
                        
                                } else {
                                    if (!isset($array_cirujia_lasik["facturas"])) {
                                        $array_cirujia_lasik["facturas"] = "";
                                    }
                                    if ($array_cirujia_lasik["facturas"] !== "") {
                                        $array_cirujia_lasik["facturas"] .= ",";
                                    }
                                    $array_cirujia_lasik["facturas"] .= $numero_documento;
                        
                                    $array_cirujia_lasik["monto_bonificado"] += $monto_bonificado_lasik;
                                    $array_cirujia_lasik["monto_copago"] += $monto_copago_lasik;
                                    $array_cirujia_lasik["monto_excedente"] += $monto_excedente_lasik;
                                    $array_cirujia_lasik["codigo_flujo"] = $codigo_flujo;
                                    $array_cirujia_lasik["total_factura"] += $monto_factura;
                        
                                }
                            }
                        }
                        $monto_bonificado = 0;
                        $monto_copago = 0;
                        $monto_excedente = 0;
                
                        $monto_bonificado_lasik = 0;
                        $monto_excedente_lasik = 0;
                        $monto_copago_lasik = 0;
                       
                        $cod_prevision_anterior = $codigo_prevision_rb;
                
                    }

                    $comprobantes_rb[$servidor]['BONO'] = $array_bono;
                    $comprobantes_rb[$servidor]['CIRUJIA'] = $array_cirujia;
                    $comprobantes_rb[$servidor]['LASIK'] = $array_cirujia_lasik;

                    // return $response->withJson([
                    //     "success" => "El registro del pago de facturas se realizó exitosamente en rebsol new II bono.",
                    //     "comprobantes_rb" => $comprobantes_rb,
                    // ], 200);
                    // exit();

                    /*Registrar facturas por tipos y servidores rebsol*/
                    if (!empty($comprobantes_rb[$servidor])){
                        $contenido = $comprobantes_rb[$servidor];

                        if ($contenido !="no existen registros en 'REBSOL'."){
                            // $facturas_bono_db18 = $comprobantes_rb["db18"]["BONO"];
                            if (!empty($comprobantes_rb[$servidor]["BONO"]["facturas"])) {
                                //Obtenen datos del array comprobantes_rb
                                $facturas_auxi = $comprobantes_rb[$servidor]["BONO"]["facturas"];
                                $monto_bonificado_auxi = $comprobantes_rb[$servidor]["BONO"]["monto_bonificado"];
                                $monto_copago_auxi = $comprobantes_rb[$servidor]["BONO"]["monto_copago"];
                                $monto_excedente_auxi = $comprobantes_rb[$servidor]["BONO"]["monto_excedente"];
                                $codigo_flujo_auxi = $comprobantes_rb[$servidor]["BONO"]["codigo_flujo"];
                                $total_factura_auxi = $comprobantes_rb[$servidor]["BONO"]["total_factura"];
                                $codigo_prevision_rb = 100;
                                
                                /*REGISTRAR PAGO DE FACTURAS
                                //  1.Funcion obtener numero control_factura.
                                //  2.Funcion actualizar detalle boleta.
                                //  3.Funcion registrar control de factura.
                                // */

                                //1
                                $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                                $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                                if ($codigo_control_facturacion == 0){
                                    return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);

                                }

                                //2
                                $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $facturas_auxi, $codigo_control_facturacion, $codigo_flujo_auxi, '6');
                                if ($respuesta !== true) {
                                    return $response->withJson($numerosControl, 400);
                                    
                                }

                                //3
                                $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision_rb, $total_factura_auxi, $forma_pago_tranf, $numero_deposito, 
                                                    $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado_auxi,  $monto_copago_auxi, $monto_excedente_auxi);
                                if (!$respuesta) {
                                    return $response->withJson($respuesta, 400);
                                }

                            }


                            if (!empty($comprobantes_rb[$servidor]["CIRUJIA"]["facturas"])) {
                                //Obtenen datos del array comprobantes_rb
                                $facturas_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["facturas"];
                                $monto_bonificado_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["monto_bonificado"];
                                $monto_copago_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["monto_copago"];
                                $monto_excedente_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["monto_excedente"];
                                $codigo_flujo_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["codigo_flujo"];
                                $total_factura_auxi = $comprobantes_rb[$servidor]["CIRUJIA"]["total_factura"];
                                $codigo_prevision_rb = 100;
                                
                                /*REGISTRAR PAGO DE FACTURAS
                                //  1.Funcion obtener numero control_factura.
                                //  2.Funcion actualizar detalle boleta.
                                //  3.Funcion registrar control de factura.
                                // */

                                //1
                                $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                                $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                                if ($codigo_control_facturacion == 0){
                                    return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);

                                }

                                //2
                                $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $facturas_auxi, $codigo_control_facturacion, $codigo_flujo_auxi, '6');

                                if ($respuesta !== true) {
                                    return $response->withJson($numerosControl, 400);
                                    
                                }

                                //3
                                $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision_rb, $total_factura_auxi, $forma_pago_tranf, $numero_deposito, 
                                                    $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado_auxi,  $monto_copago_auxi, $monto_excedente_auxi);
                                if (!$respuesta) {
                                    return $response->withJson($respuesta, 400);
                                }


                            }

                            if (!empty($comprobantes_rb[$servidor]["LASIK"]["facturas"])) {
                                //Obtenen datos del array comprobantes_rb
                                $facturas_auxi = $comprobantes_rb[$servidor]["LASIK"]["facturas"];
                                $monto_bonificado_auxi = $comprobantes_rb[$servidor]["LASIK"]["monto_bonificado"];
                                $monto_copago_auxi = $comprobantes_rb[$servidor]["LASIK"]["monto_copago"];
                                $monto_excedente_auxi = $comprobantes_rb[$servidor]["LASIK"]["monto_excedente"];
                                $codigo_flujo_auxi = $comprobantes_rb[$servidor]["LASIK"]["codigo_flujo"];
                                $total_factura_auxi = $comprobantes_rb[$servidor]["LASIK"]["total_factura"];
                                $codigo_prevision_rb = 756;

                                /*REGISTRAR PAGO DE FACTURAS
                                //  1.Funcion obtener numero control_factura.
                                //  2.Funcion actualizar detalle boleta.
                                //  3.Funcion registrar control de factura.
                                // */

                                //1
                                $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                                $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                                if ($codigo_control_facturacion == 0){
                                    return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);

                                }

                                //2
                                $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $facturas_auxi, $codigo_control_facturacion, $codigo_flujo_auxi, '6');
                                if ($respuesta !== true) {
                                    return $response->withJson($numerosControl, 400);
                                    
                                }

                                //3
                                $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision_rb, $total_factura_auxi, $forma_pago_tranf, $numero_deposito, 
                                                    $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado_auxi,  $monto_copago_auxi, $monto_excedente_auxi);

                                if (!$respuesta) {
                                    return $response->withJson($respuesta, 400);
                                }

                            }

                            
                        }
                    
                    } 

                } else {
                    $comprobantes_rb[$servidor] = "no existen registros en 'REBSOL'."; 
                }

            }
            return $response->withJson([
                "success" => "El registro del pago de facturas se realizó exitosamente en rebsol new II.",
                "comprobantes_rb" => $comprobantes_rb,
                "numero_deposito" => $numero_deposito,
               
            ], 200);
            exit();

    
        } catch (Exception $e) {
            return $response->withJson([
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ], 500);
        }
    }








    // new function 08-05-2025
    public function insert_pago_facturas_rebsol_new_III($data, $response){
        try {
           
            // Definir el servidor de la base de datos
            // $numero_factura = "92536,92543,92538,92451"; 
            // $numero_factura = "92537,92539,92458,92452,92541,92453,92455"; 
            $numero_factura = $data["folios"]; //NUMERO DE FACTURAS
            $codigo_prevision = $data["cod_prevision_rb"];// CODIGO PREVISION REBSOL
            if($codigo_prevision == '100'){
                // Definir el array de códigos de previsión válidos
                $codigo_prevision = [100, 756];

            }
          
            $numero_deposito = $data["comprobante"];
            $rut_funcionario = "99999999"; // RUT FUNCIONARO REGISTRA
            $rut_pjuridica = $data["rut_prevision"]; // RUT PREVISION 
            $forma_pago_tranf = 46; // FORMA PAGO TRANFERENCIA
            $rut_pjuridica_banco = $data["rut_banco"]; // RUT BANCO
            $fecha_banco = $data["fecha_banco"]; // fecha transferencia banco 
            $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_banco);
            $fecha_obj->setTime(0, 0, 0); // Forzar hora a 00:00:00
            $fecha_transferencia = $fecha_obj->format('Y-m-d H:i:s');
           

            // Servidores de conexion 
            $servidores = array(
                'db18',
                'db250LF'
            );

            $comprobantes_rb = []; // Arreglo para almacenar los números de documentos por servidor
            $count_servidor = 0;
            foreach ($servidores as $servidor){
                // Obtener la conexión a la base de datos
                $conexion_db_rb =  $this->container->get($servidor);
                if (!$conexion_db_rb) {
                    return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos rebsol en el servidor '$servidor'."], 500);
                }
                $arrayfacturas= array();
                $arrayfacturas_cirujias= array();
                $arrayfacturas_cirujias_lasik= array();
                $arrayfacturas_bonos= array();
                $arrayfacturas_montos= array();
                $arrayfonasa= array();
                $arrayfonasa_lasik= array();

                $total_factura_bono = 0;
                $total_factura_cirujia = 0;

                $numero_factura_aux = "";
                $tipo_factura_aux = "";
                $x = 0; // Inicializa $x antes de usarlo
                $total_factura = 0; // INICIALIZAR
                $cod_prevision_anterior = "";
                $estado_tipo_prestacion_anterior = "";
              
                $count_servidor++;

                $monto_bonificado = 0;
                $monto_copago = 0;


                /*
                * PASO 1 Esta consulta obtiene información de pacientes y sus pagos, asociando detalles de facturación y boletas.
                */
                // Codigo que permite buscar facturas derivadas.
                // Convierte el string en un arreglo separado por comas
                $numeros_base = explode(',', $numero_factura);
                // Construir las condiciones LIKE dinámicamente
                $condiciones_like = array_map(function($num) {
                    return "db.numero_documento LIKE '{$num}%'";
                }, $numeros_base);
                // Unir todas con OR
                $condicion_where = implode(' OR ', $condiciones_like);

                // Consulta actualizada para buscar derivados de facturas
                 $sql= "SELECT *, 
                        pcd.monto AS monto_pago, 
                        -- IFNULL(ra.codigo_tipo_prestacion_agenda, 9) AS codigo_tipo_prestacion_agenda, 
                        IF(ra.codigo_tipo_prestacion_agenda IS NOT NULL, 1, 2) AS estado_tipo_prestacion,
                         dpc.codigo_prevision as prevision_final,
                        DATE_FORMAT(db.fecha_facturacion, '%d/%m/%Y') AS fecha_boleta 
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
                    LEFT JOIN RESERVA_ATENCION AS ra
                        ON dpc.rut_paciente = ra.rut_pnatural  
                        AND dpc.numero_hermano_gemelo = ra.numero_hermano_gemelo 
                        AND dpc.evento = ra.evento 
                    WHERE cp.codigo_estado_cuenta IN (15, 24, 25)
                        AND db.codigo_control_facturacion = 0
                        AND db.codigo_estado = 1
                        AND db.codigo_tipo_documento IN (2, 6)
                        AND pcd.estado_documento != 4
                        AND dpc.codigo_forma_pago IN (18,20,22)
                        AND (
                            $condicion_where
                        )
                    GROUP BY pcd.rut_paciente, pcd.numero_hermano_gemelo, pcd.evento, pcd.codigo_cuenta, pcd.codigo_pago_cuenta
                    ORDER BY dpc.codigo_prevision, db.codigo_tipo_documento,db.numero_documento,estado_tipo_prestacion, db.fecha_facturacion
                ";

                // return $response->withJson([
                //     "success" => "Inserción exitosa pago_facturas_rebsol NEW_III SQL.",
                //     "sql" => $sql,
                //  ], 200);

                   
                // $stmt = $conexion_db_rb->prepare($sql1);
                $stmt = $conexion_db_rb->prepare($sql);
                $stmt->execute();

                // Obtener los registros
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar si hay resultados
                if (!empty($resultados)) {
                    // $data = [];
                    $contador1 = 0;
                    $contador2 = 0;
                    // Recorrer los resultados
                    foreach ($resultados as $fila) {
                        $contador1++;
                    
                        $tipo_factura = $fila['codigo_tipo_documento'];
                        $rut_paciente = $fila['rut_paciente'];
                        $nhg =  $fila['numero_hermano_gemelo'];
                        $evento =  $fila['evento'];
                        $fecha_admision =  $fila['fecha_boleta'];
                        $estado_cuenta =  $fila['codigo_estado_cuenta'];
                        $pago_cuenta =  $fila['codigo_pago_cuenta'];
                        $codigo_flujo =  $fila['codigo_flujo_caja'];
                        $monto_factura =  $fila['monto_pago'];
                        // $monto_factura = $fila['monto'];
                        $fecha_facturacion =  $fila['fecha_facturacion'];
                        $numero_documento =  $fila['numero_documento'];
                        // $codigo_prevision_rb =  $fila['codigo_prevision'];
                        $codigo_prevision_rb =  $fila['prevision_final'];
                        $estado_tipo_prestacion = $fila['estado_tipo_prestacion'];

                        // return $response->withJson([
                        //     "success" => " antes Cambio de prestacion III.",
                        //     "estado_tipo_prestacion" => $estado_tipo_prestacion,
                        //     "codigo_prevision_rb" => $codigo_prevision_rb,
                        // ], 200);
                        // exit();
                        

                        // SEPARAR LA ASOCIACION DEL COMPROBANTE POR BONO Y CIRUJIAS EN LOS COMPROBANTES DE REBSOL.
                        // if ($codigo_prevision_rb != $cod_prevision_anterior && !empty($cod_prevision_anterior)) {
                        // if (($codigo_prevision_rb != $cod_prevision_anterior && !empty($cod_prevision_anterior)) ||
                        //     ($estado_tipo_prestacion != $estado_tipo_prestacion_anterior  && !empty($estado_tipo_prestacion_anterior))) {
                        if (
                            ($codigo_prevision_rb != $cod_prevision_anterior && !empty($cod_prevision_anterior)) ||
                            ($estado_tipo_prestacion != $estado_tipo_prestacion_anterior && !empty($estado_tipo_prestacion_anterior))
                        ) {

                                // return $response->withJson([
                                //     "success" => "1 III.",
                                //     "estado_tipo_prestacion" => $estado_tipo_prestacion,
                                //     "codigo_prevision_rb" => $codigo_prevision_rb,
                                //     "estado_tipo_prestacion_anterior" => $estado_tipo_prestacion_anterior,
                                //     "arrayfacturas" => $arrayfacturas,
                                // ], 200);
                                // exit();
                               
                            /*REGISTRAR PAGO DE FACTURAS*/
                            // 1.Funcion obtener numero control_factura.
                            // 2.Funcion actualizar detalle boleta.
                            // 3.Funcion registrar control de factura.

                            //1
                            $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                            $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                            if ($codigo_control_facturacion == 0){
                                return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);
            
                            }


                            // return $response->withJson([
                            //     "success" => "1 III.",
                            //     "estado_tipo_prestacion" => $estado_tipo_prestacion,
                            //     "codigo_prevision_rb" => $codigo_prevision_rb,
                            //     "estado_tipo_prestacion_anterior" => $estado_tipo_prestacion_anterior,
                            //     "arrayfacturas" => $arrayfacturas,
                            //     "codigo_control_facturacion" => $codigo_control_facturacion,
                            //     "codigo_flujo" => $codigo_flujo,
                            //     "tipo_factura" => $tipo_factura,
                            //     "servidor" => $servidor,
                            // ], 200);
                            // exit();

                            //2
                            $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $arrayfacturas, $codigo_control_facturacion, $codigo_flujo, $tipo_factura);
                            if ($respuesta !== true) {
                                return $response->withJson($respuesta, 400);
                                
                            }
                            
                            //3
                            $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $cod_prevision_anterior, $total_factura, $forma_pago_tranf, $numero_deposito, 
                                                $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado,  $monto_copago, $monto_excedente);

                            $previsionNombre = "prevision " . $cod_prevision_anterior;
                            // Primero armamos el array temporal
                            // $arrayFonasa = [
                            //     "control_facturacion" => $codigo_control_facturacion,
                            //     "bonos" => implode(",", $arrayfacturas_bonos),
                            //     "total_factura_bono" => $total_factura_bono,
                            //     "cirujias" => implode(",", $arrayfacturas_cirujias),
                            //     "total_factura_cirujia" => $total_factura_cirujia,
                            //     "total_factura" => $total_factura,
                            //     "monto_bonificado" => $monto_bonificado,
                            //     "monto_copago" => $monto_copago,
                            //     "monto_excedente" => $monto_excedente
                            // ];
                            $arrayFonasa = [
                                "control_facturacion" => $codigo_control_facturacion,
                                "bonos" => implode(",", $arrayfacturas_bonos),
                                "cirujias" => implode(",", $arrayfacturas_cirujias),
                                "total_factura" => $total_factura
                            ];
        

                            $comprobantes_rb[$servidor][$previsionNombre] = $arrayFonasa;

                            // return $response->withJson([
                            //     "success" => "El registro del pago de facturas III.",
                            //     "comprobantes_rb" => $comprobantes_rb,
                            // ], 200);
                            // exit();
                            
                            // Inicializar los montos de bonificación, copago y excedente
                            $monto_bonificado = 0;
                            $monto_copago = 0;
                            $monto_excedente = 0;
                            $total_factura = 0;
                            $arrayfacturas= array();
                            $arrayfacturas_cirujias= array();
                            $arrayfacturas_bonos= array();
                            $numero_factura_aux = "";
                            $tipo_factura_aux = "";
                           
                        }

                        // $total_factura += $monto_factura;

                        if (!in_array($numero_documento, $arrayfacturas)) {
                            $arrayfacturas[] = $numero_documento;
                        }

                        /*
                        PASO 2 La consulta recupera la información de una factura específica que está activa para obtener el codigo_tipo_facturacion  
                        *Obtiene el registro resumido de la factura.
                        */
                        $sql = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_boleta 
                                FROM FACTURA_MULTIPLE 
                                WHERE numero_factura = :numero_factura 
                                AND codigo_tipo_documento = :tipo_factura 
                                AND codigo_estado = 1";
                        $stmt_fm = $conexion_db_rb->prepare($sql);
                        $stmt_fm->execute([':numero_factura' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                        $row = $stmt_fm->rowCount();

                        if ($stmt_fm->rowCount() > 0) {
                            // Registros de bonos
                            // $contador1++;
                            $codigo_prevision = '100';
                            $row_fm = $stmt_fm->fetch(PDO::FETCH_ASSOC);

                            if ($numero_factura_aux != $numero_documento || $tipo_factura_aux != $tipo_factura) {
                                $tipo_prestacion = $row_fm['codigo_tipo_facturacion'];

                                $numero_factura_aux = $numero_documento;
                                $tipo_factura_aux = $tipo_factura;
                              
                                $contador1++;
                                // if ($contador1 == 2){
                                //     return $response->withJson([
                                //         "success" => "1 III.",
                                //         "numero_factura_aux" => $numero_factura_aux,
                                //         "numero_documento" => $numero_documento,
                                //         "tipo_factura_aux" => $tipo_factura_aux,
                                //         "tipo_factura" => $tipo_factura,
                                //         "contador1" => $contador1,
                                //     ], 200);
                                //     exit();

                                // }
                                if (!in_array($numero_documento, $arrayfacturas_bonos)) {
                                    $arrayfacturas_bonos[] = $numero_documento;
                                }

                               
                            
                                if (($tipo_prestacion < 5) || ($tipo_prestacion == 999)) {
                                    $rut_prevision = $row_fm['rut_prevision'];
                                
                                    // Consulta para PREVISION
                                    $sql_prev = "SELECT * FROM PREVISION WHERE rut_pjuridica = :rut_prevision AND codigo_estado = 1";
                                    $stmt_prev = $conexion_db_rb->prepare($sql_prev);
                                    $stmt_prev->execute([':rut_prevision' => $rut_prevision]);
                        
                                    $verifica_prevision = 0;
                                    while ($row_x2 = $stmt_prev->fetch(PDO::FETCH_ASSOC)) {
                                        if ($codigo_prevision == $row_x2["codigo_prevision"]) {
                                            $verifica_prevision = 1;
                                        }
                                    }

                                    if (($verifica_prevision == 1) || $codigo_prevision == 999) {
                                        
                                        $x++;
                                        $hay_reg = 1;
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
                                                    WHERE db.numero_documento = :numero_documento 
                                                    AND db.codigo_tipo_documento = :tipo_factura";

                                     
                                        $stmt_bono = $conexion_db_rb->prepare($sql_bono);
                                        $stmt_bono->execute([':numero_documento' => $numero_documento, ':tipo_factura' => $tipo_factura]);

                                        if ($stmt_bono->rowCount() > 0) {

                                            // return $response->withJson([
                                            //     "success" => "Inserción exitosa pago_facturas_rebsol NEW_II BONO ENTRO PAGO_CUENTA_DOCUMENTO.",
                                                
                                            // ], 200);
                                            // exit();
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
                                        $total_factura_bono += $monto_factura;
                                        // $total_factura_cirujia = 0;
                                        
                        
                                        // Consulta para DETALLE_BOLETA
                                        $sql_db = "SELECT * FROM DETALLE_BOLETA WHERE numero_documento = :numero_documento AND codigo_estado = 1 AND codigo_tipo_documento = :tipo_factura";
                                        $stmt_db = $conexion_db_rb->prepare($sql_db);
                                        $stmt_db->execute([':numero_documento' => $numero_documento, ':tipo_factura' => $tipo_factura]);
                                        $total_registros = $stmt_db->rowCount();
                                        $row_db = $stmt_db->fetch(PDO::FETCH_ASSOC);
                                        $codigo_flujo = $row_db['codigo_flujo_caja'];
                        
                                    
                                    }
                                }
                                // $numero_factura_aux = $numero_documento;
                                // $tipo_factura_aux = $tipo_factura;
                        
                            }
                            if($codigo_prevision == '100'){
                                // Definir el array de códigos de previsión válidos
                                $codigo_prevision = [100, 756];
                
                            }
                            

                        }else{
                            // Registros de cirujias
                            $contador2++;

                            if (!is_array($codigo_prevision)){
                                $codigo_prevision = [$codigo_prevision]; // Lo convertir en array si no lo es
                            }

                            if (!in_array($numero_documento, $arrayfacturas_cirujias)){
                                $arrayfacturas_cirujias[] = $numero_documento;
                            }
                        
                            // Inicializar variables
                            $tipo_cuenta = 0;
                            $codigo_prevision_paciente = 0;

                            // Determinar el tipo de cuenta y obtener el código de previsión del paciente
                            if ($estado_cuenta == 15) { // Consulta pagada 
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
                            // if ($codigo_prevision_paciente == $codigo_prevision) {
                            if (in_array($codigo_prevision_paciente, $codigo_prevision)){
                                // Incrementar contador
                                $x++;
                                $hay_reg = 1;
                                $total_factura += $monto_factura;
                                $total_factura_cirujia += $monto_factura;

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

                            }

                        }
                        $cod_prevision_anterior = $codigo_prevision_rb;
                        $estado_tipo_prestacion_anterior =  $estado_tipo_prestacion;
                       
                
                    }

                    // return $response->withJson([
                    //     "success" => "2 III.",
                    //     "estado_tipo_prestacion" => $estado_tipo_prestacion,
                    //     "codigo_prevision_rb" => $codigo_prevision_rb,
                    // ], 200);
                    // exit();

                    //1
                    $numerosControl = $this->obtener_codigo_controlFacturacion($conexion_db_rb);
                    $codigo_control_facturacion = $numerosControl['codigo_control_facturacion'];
                    if ($codigo_control_facturacion == 0){
                        return $response->withJson(["error" => "Error al obtener el codigo control facturacion de rebsol."], 400);

                    }

                    //2
                    $respuesta = $this->actualizar_detalle_boleta($conexion_db_rb, $arrayfacturas, $codigo_control_facturacion, $codigo_flujo, $tipo_factura);
                    if ($respuesta !== true) {
                        return $response->withJson($numerosControl, 400);
                        
                    }

                    //3
                    $respuesta = $this->registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision_rb, $total_factura, $forma_pago_tranf, $numero_deposito, 
                                        $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado,  $monto_copago, $monto_excedente);

                    if (!$respuesta) {
                        return $response->withJson($respuesta, 400);
                    }

                    $previsionNombre = "prevision " . $codigo_prevision_rb;
                    // Primero armamos el array temporal
                    $arrayFonasa = [
                        "control_facturacion" => $codigo_control_facturacion,
                        "bonos" => implode(",", $arrayfacturas_bonos),
                        "cirujias" => implode(",", $arrayfacturas_cirujias),
                        "total_factura" => $total_factura
                    ];

                    $comprobantes_rb[$servidor][$previsionNombre] = $arrayFonasa;

                    // $arrayFonasa = [
                    //     "control_facturacion" => $codigo_control_facturacion,
                    //     "bonos" => implode(",", $arrayfacturas_bonos),
                    //     "total_factura_bono" => $total_factura_bono,
                    //     "cirujias" => implode(",", $arrayfacturas_cirujias),
                    //     "total_factura_cirujia" => $total_factura_cirujia,
                    //     "total_factura" => $total_factura,
                    //     "monto_bonificado" => $monto_bonificado,
                    //     "monto_copago" => $monto_copago,
                    //     "monto_excedente" => $monto_excedente,
                    //     "contador1" => $contador1
                    // ];

                    // $comprobantes_rb[$servidor][$previsionNombre] = $arrayFonasa;

                    // return $response->withJson([
                    //     "success" => "El registro del pago de facturas III.",
                    //     "comprobantes_rb" => $comprobantes_rb,
                    // ], 200);
                    // exit();
                
                } 

            }
           
            return $response->withJson([
                "success" => "El registro del pago de facturas se realizó exitosamente en rebsol.",
                "comprobantes_rb" => $comprobantes_rb,
            ], 200);

    
        } catch (Exception $e) {
            return $response->withJson([
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ], 500);
        }
    }







    function obtener_codigo_controlFacturacion($conexion_db_rb){
        try{

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

             // Retornar los valores en un array
            return [
                'codigo_control_facturacion' => $codigo_control_facturacion
            ];
          
           

        } catch (Exception $e) {
            return[
                "error registrar_cwmovim_cuentaCliente:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }
        
    }



    function actualizar_detalle_boleta($conexion_db_rb, $arrayfacturas, $codigo_control_facturacion, $codigo_flujo, $tipo_factura){
        try{



            // return [
            //     "success" => "Inserción exitosa pago_facturas_rebsol NEW_II SQL LL.",
            //     "arrayfacturas" => $arrayfacturas,
            //     "codigo_control_facturacion" => $codigo_control_facturacion,
            //     "codigo_flujo" => $codigo_flujo,
            //     "tipo_factura" => $tipo_factura
            // ];

             /*
            *Consulta para obtener el ultimo codigo de control  de la tabla CONTROL_FACTURACION.
            *Sumamos 1 para generar el codigo correlativo para el control de la factura que se va actualizar y registrar el pago.
            */
          // Normalizamos el valor para extraer las facturas como arreglo
           if (is_array($arrayfacturas)) {
            // Si viene como array, contiene el string en la primera posición
                $facturas_string = reset($arrayfacturas); // equivale a $arrayfacturas[0]
            } else {
                // Si ya es un string
                $facturas_string = $arrayfacturas;
            }
            
            $arrayFacturas = explode(",", $facturas_string);

            // return $arrayFacturas;
                
        

            $facturas_fallidas = []; // Aquí almacenaremos las facturas que no se pudieron actualizar
            foreach ($arrayFacturas as $factura) {
                /*
                *Consulta para actualizar el codigo de control de la factura en la tabla DETALLE_BOLETA.
                *Actualizamos el codigo de control de la factura para el documento (factura) que se va actualizar y registrar el pago.
                */

                $documento = $factura;
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
                $stmt->bindParam(':numero_factura', $documento);
                $stmt->bindParam(':tipo_factura', $tipo_factura);

                // $stmt->execute();
                // Ejecutar y validar
                if (!$stmt->execute()) {
                    // Si falla, agregamos el documento al arreglo de fallidas
                    $facturas_fallidas[] = $documento;
                }

            }

            // Después del foreach, puedes devolver el resultado general
            if (!empty($facturas_fallidas)) {
                return [
                    "error" => "Algunas facturas no se pudieron actualizar en DETALLE_BOLETA de rebsol.",
                    "facturas_fallidas" => $facturas_fallidas
                ];
            }

            return true; // Todo se actualizó correctamente
          
           

        } catch (Exception $e) {
            return[
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }
        


    }


    
    function registrar_codigo_controlFactura($conexion_db_rb, $codigo_control_facturacion, $codigo_prevision, $total_factura, $forma_pago_tranf, $numero_deposito, 
                                                $fecha_transferencia, $rut_funcionario, $rut_pjuridica_banco, $monto_bonificado,  $monto_copago, $monto_excedente){
        try{

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
            $stmt->bindParam(':monto_factura', $total_factura);
            $stmt->bindParam(':codigo_forma_pago', $forma_pago_tranf);
            $stmt->bindParam(':comprobante_deposito', $numero_deposito);
            $stmt->bindParam(':fecha_registro', $fecha_actual);
            $stmt->bindParam(':fecha_factura', $fecha_transferencia);
            $stmt->bindParam(':rut_funcionario', $rut_funcionario);
            $stmt->bindParam(':rut_pjuridica', $rut_pjuridica_banco);
            $stmt->bindParam(':bonificado', $monto_bonificado);
            $stmt->bindParam(':copago', $monto_copago);
            $stmt->bindParam(':excedente', $monto_excedente);


            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                return [
                    "error" => "Error al insertar en CONTROL_FACTURACION de rebsol.",
                    "detalle" => $errorInfo[2]
                ];
            }else {
                return true;
            }
           

        } catch (Exception $e) {
            return[
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }
        


    }





    





}

?>