<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
class Softland
{

    // protected $app;
    protected $container;

    // Constructor vacío
    public function __construct(\Slim\App $app)
    {
        $this->container = $app->getContainer();
    }

    public function get_iw_gsaen()
    {
        return "test insert";
    }


    public function conexion_softland()
    {
        try {

            // $conexion_db =  $this->container->get($servidor);
            $conexion_db = $this->container->get('dbSOFTLAND_PROD');

            // Ejecuta una consulta simple para verificar la conexión
            $stmt = $conexion_db->query("SELECT 1");
            if ($stmt !== false) {
                $mensajes_conexion[] = "Conexión1 exitosa con el servidor: dbSOFTLAND_PROD";
            } else {
                $mensajes_conexion[] = "Fallo al 1ejecutar prueba en el servidor: dbSOFTLAND_PROD";
            }


            // Retornar todos los mensajes como un string separado por saltos de línea
            return implode("<br>", $mensajes_conexion);
        } catch (Exception $e) {
            // Si hay un error en la conexión
            return "Error de conexión: " . $e->getMessage();
        }

    }

    //ROBOT ST
    public function conexion_softland_st()
    {
        try {

            // $conexion_db =  $this->container->get($servidor);
            #$conexion_db = $this->container->get('dbSOFTLAND_DEV');
            $conexion_db = $this->container->get('dbSOFTLAND_PROD');

            // Ejecuta una consulta simple para verificar la conexión
            $stmt = $conexion_db->query("SELECT 1");
            if ($stmt !== false) {
                $mensajes_conexion[] = "Cone2xión exitosa con el servidor: dbSOFTLAND_DEV";
            } else {
                $mensajes_conexion[] = "Fallo a2l ejecutar prueba en el servidor: dbSOFTLAND_DEV";
            }


            // Retornar todos los mensajes como un string separado por saltos de línea
            return implode("<br>", $mensajes_conexion);
        } catch (Exception $e) {
            // Si hay un error en la conexión
            return "Error de conexión: " . $e->getMessage();
        }

    }


    public function buscar_facturas_softland($data, $response)
    {
        try {

            // return "test insert2";
            /*Obtiene los datos enviados en el cuerpo de la solicitud
             *enviados atraves de la function llamarApi($endpoint, $metodo = 'GET', $data = null) {
             * curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
             * del sistema IOPA_pagoFacil.
             */
            // $data = $request->getParsedBody(); 

            $documentos_str = $data['documentos_str'];
            $cod_prevision = $data['cod_prevision'];
            //PARTE OPCION 2  DESCOEMNTAR DEPENDIENDO DE LA OPCION USADA ($monto_total)
            // $monto_total = $data['monto_total'];

            // $servidores = 'dbSOFTLAND_PROD';  // Nombre del servidor de conexión a la base de datos
            // $servidores = 'dbSOFTLAND_DEV';  // Nombre del servidor de conexión desarrollo 
            // Obtener la conexión a la base de datos desde el contenedor
            $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            // Verificar si la conexión es exitosa
            if ($conexion_db) {
                /*Consulta que permite traer los registros de todas las facturas que no cuenten con un comprobante contable (facturas pendientes de pago)*/
                //OPCION 1
                $sql = "SELECT 
                            iwg.*,
                            c.NomAux AS nombre_prevision
                        FROM softland.iw_gsaen AS iwg
                        LEFT JOIN softland.cwtauxi AS c ON iwg.CodAux = c.CodAux
                        WHERE iwg.Folio IN ($documentos_str)
                        AND iwg.Tipo = 'F'
                        AND iwg.CodAux = $cod_prevision
                        ORDER BY iwg.CodBode;
                ";
                // $sql = "SELECT * 
                //         FROM softland.iw_gsaen AS iwg
                //         WHERE iwg.Folio IN ($documentos_str)
                //         AND iwg.Tipo = 'F'
                //         AND iwg.CodAux = $cod_prevision
                //         ORDER BY iwg.CodBode;
                // ";

                //OPCION 2 
                // $sql = "SELECT *
                //         FROM softland.iw_gsaen AS iwg
                //         WHERE iwg.Folio IN ($documentos_str)
                //         AND iwg.Tipo = 'F'
                //         AND iwg.CodAux = $cod_prevision
                //         AND (
                //             SELECT SUM(i.Total)
                //             FROM softland.iw_gsaen AS i
                //             WHERE i.Folio IN ($documentos_str)
                //             AND i.Tipo = 'F'
                //             AND i.CodAux = $cod_prevision
                //         ) = 10000
                //         ORDER BY iwg.CodBode;
                // ";

                // echo "<pre>";
                // var_dump($sql);
                // echo "</pre>";
                // exit();
                //  return [
                //     "success" => "buscar_facturas_softland sql.",
                //     "data" => $sql,
                // ];
                // exit();

                // Preparar la consulta
                $stmt = $conexion_db->prepare($sql);

                // Ejecutar la consulta
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar si hay resultados
                if ($result) {
                    return $response->withJson($result, 200);
                } else {
                    return $response->withJson(["error" => "No se encontraron 'Documentos' en los registros de softland."], 404);
                }
            } else {
                return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos de softland."], 500);
            }

        } catch (Exception $e) {
            // Manejo de excepciones en caso de errores
            $this->logger->info("Error en archivo: " . __FILE__ . ", Linea: " . $e->getLine() . ", Error: " . $e->getMessage());
            return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
        }


    }

    public function listar_Facturas_Pendiente_Pago($data, $response)
    {
        try {

            // return "test insert2";
            /*Obtiene los datos enviados en el cuerpo de la solicitud
             *enviados atraves de la function llamarApi($endpoint, $metodo = 'GET', $data = null) {
             * curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
             * del sistema IOPA_pagoFacil.
             */
            // $data = $request->getParsedBody(); 

            $cod_prevision_soft = $data['cod_prevision_soft'];
            $cod_prevision_rb = $data['cod_prevision_rb'];
            $nombre_prevision = $data['nombre_prevision'];
            // $servidores = 'dbSOFTLAND_PROD';  // Nombre del servidor de conexión a la base de datos
            // $servidores = 'dbSOFTLAND_DEV';  // Nombre del servidor de conexión desarrollo 
            // Obtener la conexión a la base de datos desde el contenedor
            $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            // Verificar si la conexión es exitosa
            if ($conexion_db) {
                /*Consulta que permite traer los registros de todas las facturas que no cuenten con un comprobante contable (facturas pendientes de pago)*/
                $sql = "SELECT DISTINCT iwg.*, cvm.CpbNum
                        FROM softland.iw_gsaen iwg
                        LEFT JOIN softland.cwmovim cvm 
                            ON cvm.CodAux = iwg.CodAux
                            AND cvm.MovNumDocRef = iwg.Folio
                            AND cvm.TtdCod = 'DP' 
                        WHERE iwg.Tipo = 'F'
                        AND iwg.CodAux = $cod_prevision_soft
                        AND iwg.Fecha >= '2025-01-02'
                        AND cvm.CodAux IS NULL;
                ";

                // echo "<pre>";
                // var_dump($sql);
                // echo "</pre>";
                // exit();

                // Preparar la consulta
                $stmt = $conexion_db->prepare($sql);

                // Ejecutar la consulta
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar si hay resultados
                if ($result) {
                    return $response->withJson($result, 200);
                } else {
                    return $response->withJson(["error" => "No se encontraron facturas."], 404);
                }
            } else {
                return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
            }

        } catch (Exception $e) {
            // Manejo de excepciones en caso de errores
            $this->logger->info("Error en archivo: " . __FILE__ . ", Linea: " . $e->getLine() . ", Error: " . $e->getMessage());
            return $response->withJson(["error" => "Excepción capturada - " . $e->getMessage()], 400);
        }


    }

    //ST 05-06-2025 FUNCION PADRE
    public function insertar_deposito($data, $response)
    {
        try {
            $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            #$conexion_db = $this->container->get('dbSOFTLAND_PROD');

            if (!$conexion_db) {
                return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
            }

            $bancoDeChileReporte = []; 

            foreach ($data as $cuenta => $transacciones) {
                if (empty($transacciones))
                    continue;

                $fecha = $transacciones[0]["fecha"]; 
                $fecha_parts = explode("/", $fecha); 
                $mes = '05'; #para insertar en bd de desarrollo
                #$mes = $fecha_parts[1]; #para insertar en bd de produccion
                $anio = $fecha_parts[2];
                $periodo = $anio;

                $areaCod = 0; 
                $pctCod = '';
                switch ($cuenta) {
                    case '33-05':
                        $areaCod = '001'; 
                        $pctCod = '1-01-01-003';
                        break;
                    case '80-06':
                        $areaCod = '002'; 
                        $pctCod = '1-01-01-007';
                        break;
                    default:
                        $areaCod = '000'; 
                        $pctCod = '0';
                        break;
                }

                $cpbGlo = 'Banco de chile'; 

                $resultado = $this->Obtener_Comprobante_NumeroInterno(
                    $conexion_db,
                    $periodo,
                    $mes
                );

                $cpbnum = sprintf('%08d', $resultado['CpbNum']); 
                $cpbnui = sprintf('%08d', $resultado['CpbNui']); 

                $respuestainsert = $this->insertarComprobante(
                    $conexion_db,
                    $periodo,
                    $cpbnum,
                    $areaCod,
                    $fecha,
                    $mes,
                    $cpbnui,
                    $cpbGlo,
                    $response
                );

                // Nueva variable para recolectar los traspasos pendientes
                $traspaso_detalles_pendientes = []; 
                $sumaTotalDebe = 0; // Se resetea para cada cuenta
                $MovNum = 0; // Inicializamos el contador de movimientos para este comprobante
                
                // Bucle para insertar todas las transacciones generales (Punto 1)
                foreach ($transacciones as $t) {
                    $fechaOriginal = $t["fecha"]; 
                    $fechaObj = DateTime::createFromFormat('d/m/Y', $fechaOriginal);
                    $fechaSQL = $fechaObj ? $fechaObj->format('Y-m-d') : null; 

                    $detalleOriginal = $t["detalle_movimiento"];
                    $detalle = strtolower(str_replace('.', '', $detalleOriginal));

                    if (strpos($detalleOriginal, 'TRASPASO DE CUENTA:') === 0) {
                        $movGlosa = $detalleOriginal; 
                    } elseif (strpos($detalleOriginal, 'TRASPASO DE:') === 0) {
                        $movGlosa = $detalleOriginal;
                    } elseif (strpos($detalle, 'efectivo') !== false) {
                        $movGlosa = 'EFECTIVO';
                    } elseif (strpos($detalle, 'cheque') !== false || strpos($detalle, 'cheq') !== false) {
                        $movGlosa = 'CHEQUE';
                    } elseif (strpos($detalle, 'debito') !== false || strpos($detalle, 'credito') !== false || strpos($detalle, 'crédito') !== false) {
                        $movGlosa = 'TBK';
                    } else {
                        $movGlosa = 'ABONO';
                    }
                    
                    
                    // EDICIÓN AQUÍ: Solo sumar al abono total si NO es un traspaso de cuenta específico
                    if (!($pctCod == '1-01-01-003' && trim(strtolower($t["detalle_movimiento"])) === 'traspaso de cuenta:1780098006')) {
                        $sumaTotalDebe += $t["deposito_o_abono"];
                    }
                    
                    // Incrementar MovNum antes de cada inserción de detalle general
                    $MovNum++; 

                    $deposito = [
                        'cpbnui' => $cpbnui,
                        'cpbnum' => $cpbnum,
                        'areaCod' => $areaCod,
                        'pctCod' => $pctCod,
                        'FecPag' => $fechaSQL, 
                        'mes' => $mes,
                        'anio' => $periodo,
                        'fecha' => $fechaSQL, 
                        'detalle' => $t["detalle_movimiento"],
                        'abono' => $t["deposito_o_abono"],
                        'docto' => $t["docto._nro."],
                        'sucursal' => $t["sucursal"],
                        'cuenta' => $cuenta,
                        'MovNum' => $MovNum, // Pasa el MovNum actual
                        'MovGlosa' => $movGlosa
                    ];

                    // Siempre insertar el detalle general (Punto 1)
                    $this->insertar_cwmovim_deposito_detalle($conexion_db, $deposito);

                    // Si es un traspaso específico, guardarlo para procesar después
                    if ($pctCod == '1-01-01-003' && trim(strtolower($t["detalle_movimiento"])) === 'traspaso de cuenta:1780098006') {
                        $traspaso_detalles_pendientes[] = $deposito; // Guardar el array $deposito completo

                        /* return $response->withJson([
                            "success1" => "traspaso_detalles_pendientes",
                            "traspaso_detalles_pendientes" => $traspaso_detalles_pendientes,
                        ], 200); */
                    } 
                } // Fin del foreach ($transacciones as $t) para detalles generales

                // Bucle para procesar los movimientos de traspaso específicos (Punto 2)
                foreach ($traspaso_detalles_pendientes as $traspaso_deposito) {
                    // Incrementar MovNum antes de cada inserción de traspaso
                    $MovNum++; 
                    $traspaso_deposito['MovNum'] = $MovNum; // Actualiza MovNum en el array de depósito para pasarlo a la función
                    
                    $traspaso = $this->insertar_cwmovim_anticipo_cliente_traspaso_entre_cuentas(
                        $conexion_db, 
                        $cpbnui, 
                        $cpbnum, 
                        $traspaso_deposito, // Pasamos el array $deposito con el MovNum actualizado
                        $MovNum // Pasamos el MovNum actual como parámetro separado
                    );

                    /* return $response->withJson([
                            "success2" => "traspaso",
                            "traspaso" => $traspaso,
                        ], 200); */
                }

                // Incrementar MovNum antes de la inserción del anticipo (Punto 3)
                $MovNum++; 
                $resultadoAnticipo = $this->insertar_cwmovim_anticipo_cliente(
                    $conexion_db, 
                    $cpbnui, 
                    $cpbnum, 
                    $deposito, 
                    $sumaTotalDebe,
                    $MovNum // Pasa el MovNum actual para el anticipo
                );

                $bancoDeChileReporte[] = [
                    "cuenta" => $pctCod,
                    "numero_comprobante" => $cpbnum,
                    "monto_total_deposito" => $sumaTotalDebe,
                    "insertado_anticipo_cliente" => $resultadoAnticipo 
                ];

            } // Fin del bucle principal foreach ($data as $cuenta => $transacciones)

            return $response->withJson([
                "success" => "Se insertaron los depósitos exitosamente.",
                "banco_de_chile" => $bancoDeChileReporte 
            ], 200);

        } catch (Exception $e) {
            return $response->withJson(["error" => $e->getMessage()], 500);
        }
    }


    //ST - FUNCION -> OBTIENE NUMERO INTERNO
    function Obtener_Comprobante_NumeroInterno($conexion_db, $periodo, $mes)
    {

        // return ["success" => "Obtener_Comprobante_NumeroInterno." ];
        // exit();

        // Obtener el siguiente número de comprobante
        $sql_1 = "SELECT TOP 1 CpbNum + 1 AS CpbNum FROM softland.cwcpbte 
                  WHERE CpbAno = :periodo AND CpbMes = :mes 
                  ORDER BY CpbNum DESC;";
        $stmt = $conexion_db->prepare($sql_1);
        $stmt->bindParam(':periodo', $periodo, PDO::PARAM_INT);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nuevoCpbNum = $result ? $result['CpbNum'] : 1;

        // Obtener el siguiente número interno
        $sql_2 = "SELECT TOP 1 CpbNui + 1 AS CpbNui FROM softland.cwcpbte 
                  WHERE CpbAno = :periodo AND CpbTip = 'I' AND Sistema = 'XW' AND CpbMes = :mes 
                  ORDER BY CpbNum DESC;";
        $stmt = $conexion_db->prepare($sql_2);
        $stmt->bindParam(':periodo', $periodo, PDO::PARAM_INT);
        $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nuevoCpbNui = $result ? $result['CpbNui'] : 1;

        // Retornar los valores en un array
        return [
            'CpbNum' => $nuevoCpbNum,
            'CpbNui' => $nuevoCpbNui
        ];
    }

    //ST - FUNCION -> INSERTAR COMPROBANTE
    function insertarComprobante(
        $conexion_db,
        $periodo,
        $cpbnum,
        $areaCod,
        $fecha,
        $mes,
        $cpbnui,
        $cpbGlo,
        $response
    ) {

        $fechaHoy = date('Ymd') . ' 00:00:00';

        /* return $fechaHoy;
        exit(); */

        try {
            // SQL para insertar en cwcpbte
            $sql_cwcpbte = "INSERT INTO softland.cwcpbte 
                            (CpbAno, CpbNum, AreaCod, CpbFec, CpbMes, CpbEst, 
                            CpbTip, CpbNui, CpbGlo, CpbImp, CpbCon, Sistema, Proceso, Usuario, CpbNormaIFRS, 
                            CpbNormaTrib, CpbAnoRev, CpbNumRev, SistemaMod, ProcesoMod, FechaUlMod, TipoLog)
                            VALUES 
                            (:CpbAno, :CpbNum, :AreaCod, :CpbFec, :CpbMes, :CpbEst, :CpbTip, :CpbNui, :CpbGlo, 
                            :CpbImp, :CpbCon, :Sistema, :Proceso, :Usuario, :CpbNormaIFRS, :CpbNormaTrib, 
                            :CpbAnoRev, :CpbNumRev, :SistemaMod, :ProcesoMod, :FechaUlMod, :TipoLog)";

            // Preparar la consulta
            $stmt = $conexion_db->prepare($sql_cwcpbte);

            // Vincular los parámetros con los valores
            $stmt->bindValue(':CpbAno', $periodo, PDO::PARAM_STR);
            $stmt->bindValue(':CpbNum', $cpbnum, PDO::PARAM_STR);
            $stmt->bindValue(':AreaCod', $areaCod, PDO::PARAM_STR);
            $stmt->bindValue(':CpbFec', $fecha, PDO::PARAM_STR);
            $stmt->bindValue(':CpbMes', $mes, PDO::PARAM_STR);
            $stmt->bindValue(':CpbEst', 'V', PDO::PARAM_STR);
            $stmt->bindValue(':CpbTip', 'I', PDO::PARAM_STR);
            $stmt->bindValue(':CpbNui', $cpbnui, PDO::PARAM_STR);
            $stmt->bindValue(':CpbGlo', $cpbGlo, PDO::PARAM_STR);
            $stmt->bindValue(':CpbImp', 'S', PDO::PARAM_STR);
            $stmt->bindValue(':CpbCon', 'S', PDO::PARAM_STR);
            $stmt->bindValue(':Sistema', 'XW', PDO::PARAM_STR);
            $stmt->bindValue(':Proceso', 'Pago en línea', PDO::PARAM_STR);
            $stmt->bindValue(':Usuario', 'R_DPT_CH', PDO::PARAM_STR);
            $stmt->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt->bindValue(':CpbAnoRev', '0000', PDO::PARAM_STR);
            $stmt->bindValue(':CpbNumRev', '00000000', PDO::PARAM_STR);
            $stmt->bindValue(':SistemaMod', '', PDO::PARAM_STR);
            $stmt->bindValue(':ProcesoMod', '', PDO::PARAM_STR);
            $stmt->bindValue(':FechaUlMod', $fechaHoy, PDO::PARAM_STR);
            $stmt->bindValue(':TipoLog', 'U', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt->execute();
            // Verificar si la inserción fue exitosa
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            // Si ocurre un error, hacer rollback
            // $conexion_db->rollBack();
            // echo "Error: " . $e->getMessage();
            // return $response->withJson(["error" => $e->getMessage()], 500);
            return [
                "error" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }
    }



    //ST - FUNCION -> INSERT DE DETALLES
    public function insertar_cwmovim_deposito_detalle($conexion_db, $parametros)
    {
        try {


            //  echo " funcion registrar_cwmovim_cuentaBanco parametros:\n";
            //  var_dump($parametros);
            //  echo "</pre>";
            //  exit();


            /* return [
               "success" => "registrar_cwmovim_cuentaBanco SOFTLAND.",
               "parametros" => $parametros,
            ]; */

            //Array: Deposito
            //'cpbnui'    => $cpbnui,
            //'cpbnum'    => $cpbnum,
            //'areaCod'   => $areaCod,
            //'pctCod'    => $pctCod,
            //'FecPag'    => $fechaSQL,
            //'mes'       => $mes,
            //'anio'      => $periodo,
            //'fecha'     => $t["fecha"],
            //'detalle'   => $t["detalle_movimiento"],
            //'abono'     => $t["deposito_o_abono"],
            //'cargo'     => $t["cheque_o_cargo"],
            //'docto'     => $t["docto._nro."],
            //'sucursal'  => $t["sucursal"],
            //'cuenta'    => $cuenta,
            //'MovNum'    => $MovNum,
            //'MovGlosa'  => $movGlosa

            // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
            $MovNumDocRef = 0;
            $MovDebeMa = $parametros['abono'];
            $MovDebe = $parametros['abono'];
            $FecPag = $parametros['FecPag'];
            $MovNum = $parametros['MovNum'];
            $CpbAno = $parametros['anio'];
            $CpbNum = $parametros['cpbnum'];
            $AreaCod = $parametros['areaCod'];
            $PctCod = $parametros['pctCod'];
            $CpbFec = $parametros['fecha'];
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = 0;
            $MovFe = $parametros['fecha']; //deben ser el mismo pero distinto a $CpbFec = $parametros['fecha'];
            $MovFv = $parametros['fecha']; //deben ser el mismo pero distinto a $CpbFec = $parametros['fecha'];
            $MovNumDocRef = '0';
            $MovGlosa = $parametros['MovGlosa'];

            $TipDocCb = 'TR'; // No Definido;

            if ($MovGlosa === 'EFECTIVO') {
                $TipDocCb = 'DP';
            }
            elseif ($MovGlosa === 'TBK') {
                $TipDocCb = 'TR';
            }
            elseif (strpos($MovGlosa, 'TRASPASO DE CUENTA:') === 0 || strpos($MovGlosa, 'TRASPASO DE:') === 0) {
                $TipDocCb = 'TR';
            }


            //return true;

            //insertar detalle comprobante
            $sql_cwmovim = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES (
                :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, CONVERT(DATETIME, :CpbFec, 120), :CpbMes, :CvCod, :VendCod, :UbicCod,
                :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
                :TtdCod, :NumDoc, CONVERT(DATETIME, :MovFe, 120), CONVERT(DATETIME, :MovFv, 120), :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
                :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
                :MovIPr, :MovAEquiv, CONVERT(DATETIME, :FecPag, 120), :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
                :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
                :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
                :RowKey, :PartitionKey, :NumDocRef1
            )";

            $stmt_movim = $conexion_db->prepare($sql_cwmovim);

            // Vincular los parámetros de manera individual
            $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR); //listo
            $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR); //listo
            $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR); //pendiente
            $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR); //listo
            $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);  //listo
            $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR); //listo
            $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR); //listo
            $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
            #$stmt_movim->bindValue(':TipDocCb', 'TR', PDO::PARAM_STR); //INSERT ANTIGUO
            $stmt_movim->bindValue(':TipDocCb', $TipDocCb, PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocCb', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR); 
            $stmt_movim->bindValue(':TtdCod', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR); 
            $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR); 
            $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR); 
            $stmt_movim->bindValue(':MovTipDocRef', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR); 
            $stmt_movim->bindValue(':MovDebe', $MovDebe, PDO::PARAM_STR); //pendiente
            $stmt_movim->bindValue(':MovHaber', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovGlosa', $MovGlosa, PDO::PARAM_STR); //pendiente
            $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebeMa', $MovDebeMa, PDO::PARAM_STR);  //pendiente
            $stmt_movim->bindValue(':MovHaberMa', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR); //pendiete
            $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':fecEmisionch', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FormadePag', NULL, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt_movim->execute();
            // Imprimir el número de filas afectadas
            // echo "Número de filas afectadas: " . $stmt_movim->rowCount();
            if ($stmt_movim->rowCount() > 0) {
                //echo "Inserción exitosa.";
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            // Manejo de excepciones en caso de errores
            // $this->logger->info("Error en archivo: ". __FILE__ .", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
            // return $response->withJson(["error" => "Excepción capturada - ".$e->getMessage()], 400);
            // return $response->withJson([
            //     "error registrar_cwmovim_cuentaBanco:" => $e->getMessage(),
            //     "file" => $e->getFile(),    // Archivo donde ocurrió el error
            //     "line" => $e->getLine()     // Línea donde ocurrió el error
            // ], 500);

            return [
                "error registrar_cwmovim_cuentaBanco:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }

    }

    //ST - FUNCION -> INSERTAR ANTICIPO CLIENTE
    public function insertar_cwmovim_anticipo_cliente($conexion_db, $cpbnui, $cpbnum, $parametros, $sumaTotalDebe, $movNumParaAnticipo)
    {
        try {
            // return ["success" => "registrar_cwmovim_cuentaCliente." ];
            // exit();

            //Array: Deposito
            //'cpbnui'    => $cpbnui,
            //'cpbnum'    => $cpbnum,
            //'areaCod'   => $areaCod,
            //'pctCod'    => $pctCod,
            //'FecPag'    => $fechaSQL,
            //'mes'       => $mes,
            //'anio'      => $periodo,
            //'fecha'     => $t["fecha"],
            //'detalle'   => $t["detalle_movimiento"],
            //'abono'     => $t["deposito_o_abono"],
            //'cargo'     => $t["cheque_o_cargo"],
            //'docto'     => $t["docto._nro."],
            //'sucursal'  => $t["sucursal"],
            //'cuenta'    => $cuenta,
            //'MovNum'    => $MovNum,
            //'MovGlosa'  => $movGlosa

            if($parametros['pctCod'] == '1-01-01-007'){
                $movGlosaDescripcion = 'CHILE 06';
            }
            if($parametros['pctCod'] == '1-01-01-003'){
                $movGlosaDescripcion = 'CHILE 05';
            }
            

            // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
            $MovNum = $movNumParaAnticipo; 
            $CpbAno = $parametros['anio'];
            $CpbNum = $parametros['cpbnum'];
            $AreaCod = $parametros['areaCod'];
            $PctCod = '1-01-03-003';
            $CpbFec = $parametros['fecha'];
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = '0';
            $MovFe = $parametros['fecha'];
            $MovFv = $parametros['fecha'];
            $MovNumDocRef = '0';
            $MovHaber = $sumaTotalDebe;
            $MovGlosa = $parametros['MovGlosa'];
            $MovHaberMa = $sumaTotalDebe;
            $FecPag = $parametros['fecha'];
            $FormadePag = null;
            

            //insertar detalle comprobante
            $sql_cwmovim = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES (
                :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, CONVERT(DATETIME, :CpbFec, 120), :CpbMes, :CvCod, :VendCod, :UbicCod,
                :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
                :TtdCod, :NumDoc, CONVERT(DATETIME, :MovFe, 120), CONVERT(DATETIME, :MovFv, 120), :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
                :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
                :MovIPr, :MovAEquiv, CONVERT(DATETIME, :FecPag, 120), :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
                :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
                :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
                :RowKey, :PartitionKey, :NumDocRef1
            )";

            $stmt_movim = $conexion_db->prepare($sql_cwmovim);

            // Vincular los parámetros de manera individual
            $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR); //ok
            $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':TipDocCb', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocCb', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
            $stmt_movim->bindValue(':TtdCod', 'DP', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTipDocRef', 'FL', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebe', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaber', $MovHaber, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovGlosa', $movGlosaDescripcion, PDO::PARAM_STR); //descriopcioin banco chile 05 o 06
            $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebeMa', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaberMa', $MovHaberMa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':fecEmisionch', NULL, PDO::PARAM_STR);
            $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FormadePag', $FormadePag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt_movim->execute();
            if ($stmt_movim->rowCount() > 0) {
                //echo "Inserción exitosa.";
                return true;
            } else {
                return false;
            }


        } catch (Exception $e) {
            return [
                "error registrar_cwmovim_deposito:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }



    }

    //ST - FUNCION -> INSERTAR ANTICIPO CLIENTE
    public function insertar_cwmovim_anticipo_cliente_traspaso_entre_cuentas($conexion_db, $cpbnui, $cpbnum, $parametros, $movNumParaAnticipo)
    {
        try {
            // return ["success" => "registrar_cwmovim_cuentaCliente." ];
            // exit();

            //Array: Deposito
            //'cpbnui'    => $cpbnui,
            //'cpbnum'    => $cpbnum,
            //'areaCod'   => $areaCod,
            //'pctCod'    => $pctCod,
            //'FecPag'    => $fechaSQL,
            //'mes'       => $mes,
            //'anio'      => $periodo,
            //'fecha'     => $t["fecha"],
            //'detalle'   => $t["detalle_movimiento"],
            //'abono'     => $t["deposito_o_abono"],
            //'cargo'     => $t["cheque_o_cargo"],
            //'docto'     => $t["docto._nro."],
            //'sucursal'  => $t["sucursal"],
            //'cuenta'    => $cuenta,
            //'MovNum'    => $MovNum,
            //'MovGlosa'  => $movGlosa

            //if($parametros['pctCod'] == '1-01-01-007'){
            //    $movGlosaDescripcion = 'CHILE 06';
            //}
            //if($parametros['pctCod'] == '1-01-01-003'){
            //    $movGlosaDescripcion = 'CHILE 05';
            //}
            

            #tipodocCB : CB
            #numDocCB: 1106

            // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
            $movGlosaDescripcion = $parametros['detalle']; // traspaso de cuenta:1780098006
            $MovNum = $movNumParaAnticipo; 
            $CpbAno = $parametros['anio'];
            $CpbNum = $parametros['cpbnum'];
            $AreaCod = $parametros['areaCod'];
            $PctCod = '1-01-01-007'; //traspaso proveniente de la cuenta 06 (snegun documento entregado)
            $CpbFec = $parametros['fecha']; //2025-11-06 00:00:00.000
            $timestamp = strtotime($parametros['fecha']);      // convierte "2025-11-06 00:00:00.000" a UNIX time
            $NumDocCb  = date('md', $timestamp);               // date('m') => "11", date('d') => "06"
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = '0';
            $MovFe = $parametros['fecha'];
            $MovFv = $parametros['fecha'];
            $MovNumDocRef = '0';
            #$MovHaber = $sumaTotalDebe;
            $MovGlosa = $parametros['MovGlosa'];
            #$MovHaberMa = $sumaTotalDebe;
            $FecPag = $parametros['fecha'];
            $FormadePag = null;

            $MovHaber = $parametros['abono'];;
            $MovHaberMa = $parametros['abono'];;
            $MovDebe = $parametros['abono'];;
            $MovDebeMa = $parametros['abono'];;
            

            //insertar detalle comprobante
            $sql_cwmovim = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES (
                :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, CONVERT(DATETIME, :CpbFec, 120), :CpbMes, :CvCod, :VendCod, :UbicCod,
                :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
                :TtdCod, :NumDoc, CONVERT(DATETIME, :MovFe, 120), CONVERT(DATETIME, :MovFv, 120), :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
                :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
                :MovIPr, :MovAEquiv, CONVERT(DATETIME, :FecPag, 120), :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
                :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
                :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
                :RowKey, :PartitionKey, :NumDocRef1
            )";

            $stmt_movim = $conexion_db->prepare($sql_cwmovim);

            // Vincular los parámetros de manera individual
            $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR); //ok
            $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':TipDocCb', 'CB', PDO::PARAM_STR); //CB
            $stmt_movim->bindValue(':NumDocCb', $CpbNum, PDO::PARAM_STR); //Nro comprobante, se inserta medio mal porque es un float
            $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
            $stmt_movim->bindValue(':TtdCod', 'DP', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTipDocRef', 'FL', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebe', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaber', $MovHaber, PDO::PARAM_STR); //validar
            $stmt_movim->bindValue(':MovGlosa', $movGlosaDescripcion, PDO::PARAM_STR); // traspaso de cuenta:1780098006
            $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebeMa', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaberMa', $MovHaberMa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':fecEmisionch', NULL, PDO::PARAM_STR);
            $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FormadePag', $FormadePag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt_movim->execute();
            if ($stmt_movim->rowCount() > 0) {
                //echo "Inserción exitosa.";
                return true;
            } else {
                return false;
            }


        } catch (Exception $e) {
            return [
                "error registrar_cwmovim_deposito:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }



    }


  







    //marcos 
    public function registrar_cwmovim_anticipo_cliente($conexion_db, $parametros)
    {
        try {
            // return ["success" => "registrar_cwmovim_cuentaCliente." ];
            // exit();


            //'cpbnui'   => $cpbnui,
            //            'cpbnum'   => $cpbnum,
            //            'areaCod'   => $areaCod,
            //            'pctCod'   => $pctCod,
            //            'mes'   => $mes,
            //            'anio'   => $periodo,
            //            'fecha'    => $t["fecha"],
            //            'detalle'  => $t["detalle_movimiento"],
            //            'abono'    => $t["deposito_o_abono"],
            //            'cargo'    => $t["cheque_o_cargo"],
            //            'docto'    => $t["docto._nro."],
            //            'sucursal' => $t["sucursal"],
            //            'cuenta'   => $t["cuenta"],


            // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
            $MovNum = $parametros['cantidad_MovNum']; //correlativo (preguntar)
            $CpbAno = $parametros['periodo'];
            $CpbNum = $parametros['cpbnum'];
            $AreaCod = $parametros['areaCod'];
            $PctCod = $parametros['pctCod'];
            $CpbFec = $parametros['fecha'];
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = '0';
            $MovFe = $parametros['fecha'];//CpbFec
            $MovFv = $parametros['fecha'];//CpbFec
            $MovNumDocRef = '0';
            $MovHaber = $parametros['abono']; // pendiente sra mary //saldo = debe
            $MovGlosa = $parametros['nombre_prevision_format']; // pendiente sra mary //banco de chile trns effectivo cheque
            $MovHaberMa = $parametros['cargo']; // pendiente sra mary
            $FecPag = $parametros['fecha_pago_factura_completa']; // pendiente sra mary
            $FormadePag = null;

            //insertar detalle comprobante
            $sql_cwmovim = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES (
                :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, :CpbFec, :CpbMes, :CvCod, :VendCod, :UbicCod,
                :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
                :TtdCod, :NumDoc, :MovFe, :MovFv, :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
                :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
                :MovIPr, :MovAEquiv, :FecPag, :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
                :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
                :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
                :RowKey, :PartitionKey, :NumDocRef1
            )";

            $stmt_movim = $conexion_db->prepare($sql_cwmovim);

            // Vincular los parámetros de manera individual
            $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR); //ok
            $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':TipDocCb', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocCb', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
            $stmt_movim->bindValue(':TtdCod', 'DP', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTipDocRef', 'FL', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebe', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaber', $MovHaber, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovGlosa', $MovGlosa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebeMa', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaberMa', $MovHaberMa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':fecEmisionch', NULL, PDO::PARAM_STR);
            $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FormadePag', $FormadePag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt_movim->execute();
            if ($stmt_movim->rowCount() > 0) {
                //echo "Inserción exitosa.";
                return true;
            } else {
                return false;
            }


        } catch (Exception $e) {
            return [
                "error registrar_cwmovim_deposito:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }



    }



    public function insert_pago_facturas($data, $response)
    {
        try {


            // return $response->withJson([
            //     "success" => "Inserción exitosa.",
            //     "data" => $data,
            // ], 200);
            // exit();


            $data_softland = $data["data_softland"];
            $periodo = $data["periodo"];
            $fecha_banco = $data["fecha_banco"];
            $rut_banco_transferencia = $data["rut_banco"];
            $cod_forma_pago_trans = $data["cod_forma_pago"];
            $cod_cuenta_banco_trans = $data["cod_cuenta_banco"];
            $data_banco = $data["data_banco"];
            // $fecha_banco1 = $data_banco[0]["Fecha"];
            // return $response->withJson([
            //     "success" => "Inserción exitosa.",
            //     "fecha_banco1" => $fecha_banco1,
            //     "fecha_banco" => $fecha_banco,
            // ], 200);
            // exit();
            $monto_total_transferido = $data["monto_total_transferido"];
            $cod_prevision_rb = $data["cod_prevision_rb"];



            // return $response->withJson([
            //     "success" => "Inserción exitosa.",
            //     "data_banco" => $data_banco, 
            //     "monto_total_transferido" => $monto_total_transferido,
            //     // "facturas" => $facturas
            // ], 200);
            // exit();

            // Agrupar facturas por CodBode
            $agrupador_facturas = [];
            $arrayFolios_cuadre = array(); // Crear un arreglo vacío
            foreach ($data_softland as $item) {
                $id_area = $item['CodBode'] ?? null;

                if ($id_area !== null) {
                    // Si no existe la clave id_area, la inicializamos como un array vacío
                    if (!isset($agrupador_facturas[$id_area])) {
                        $agrupador_facturas[$id_area] = [];
                    }

                    // Agregamos el item al array correspondiente
                    $agrupador_facturas[$id_area][] = $item;
                }
            }



            // declarar variable
            $codigo_area_anterior = "";

            // Obtener la conexión a la base de datos
            // $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            // Verificar si la conexión es exitosa
            if (!$conexion_db) {
                return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
            }

            $length_data = count($agrupador_facturas);
            // Tabla de homologación
            $tabla_homologacion_area = [
                "0100" => "001",
                "0200" => "002",
                "005" => "005",
                "007" => "007"
            ];

            $codigosComprobantes = [];

            foreach ($agrupador_facturas as $codBode => $facturas) {
                $cantidad_MovNum = 0;
                $monto_total_facturas = 0;
                $arrayFolios = array(); // Crear un arreglo vacío

                // Obtener código de área homologado
                $codigo_area = $tabla_homologacion_area[$codBode] ?? null;

                // return $response->withJson([
                //     "success" => "obteniendo el codigo area.",
                //     "length_data" => $length_data, 
                //     "codigo_area" => $codigo_area,
                //     "codBode" => $codBode
                //     // "facturas" => $facturas
                // ], 200);
                // exit();


                // Procesar cada factura
                foreach ($facturas as $factura) {
                    // Extraer los valores de $data
                    $fecha_facturacion = $factura['Fecha'];
                    $fecha_facturacion_formateada = date('Ymd H:i:s', strtotime($fecha_facturacion));
                    $folio = $factura['Folio'];
                    $monto_factura = $factura['Total'];
                    $cod_prevision_soft = $factura['CodAux'];

                    $cod_prevision_rb = $cod_prevision_rb;

                    $nombre_prevision = $factura['nombre_prevision'];
                    $nombre_prevision_format = strtoupper('PAGO CLIENTES ' . $nombre_prevision);

                    // return $response->withJson([
                    //     "success" => "Inserción exitosa2."
                    //     // "facturas" => $facturas
                    // ], 200);
                    // exit();

                    // $rut_banco = $factura['rut_banco'];
                    $rut_banco = $rut_banco_transferencia;

                    // $cod_forma_pago = $factura['cod_forma_pago'];
                    $cod_forma_pago = $cod_forma_pago_trans;

                    // $cod_cuenta_banco = $factura['cod_cuenta_banco'];
                    $cod_cuenta_banco = $cod_cuenta_banco_trans;

                    // $codigoCliente = $factura['codigoCliente'];
                    $codigoCliente = '1-01-03-001';

                    // $periodo = $factura['periodo'];
                    $periodo = $periodo;
                    // $fecha_pago_factura = $factura['fecha_pago'];

                    // $fecha_pago_factura = $fecha_banco; //Fecha transferencia en el banco 
                    // $fecha_pago_factura_completa = date('Ymd H:i:s', strtotime($fecha_pago_factura));
                    // $mes = date("m", strtotime($fecha_pago_factura)); // mes facturado
                    $fecha_pago_factura = $fecha_banco; // Fecha de transferencia en el banco 
                    // Convertir la fecha de 'dd/mm/yyyy' a un formato compatible con strtotime()
                    $fecha_pago_factura_completa = date('Ymd H:i:s', strtotime(str_replace('/', '-', $fecha_pago_factura)));
                    $mes = date("m", strtotime(str_replace('/', '-', $fecha_pago_factura))); // mes facturado

                    if ($codigo_area != $codigo_area_anterior) {
                        // OBTENER NUMERO COMPROBANTE Y NUMERO INTERNO POR ID_AREA (SUCURSAL)
                        $numerosComprobante = $this->Obtener_Comprobante_NumeroInterno($conexion_db, $periodo, $mes);
                        $nuevoCpbNum = $numerosComprobante['CpbNum'];
                        $nuevoCpbNui = $numerosComprobante['CpbNui'];

                        // Formatear los números obtenidos
                        $numero_formateado_CpbNum = str_pad($nuevoCpbNum, 8, "0", STR_PAD_LEFT);
                        $numero_formateado_CpbNui = str_pad($nuevoCpbNui, 8, "0", STR_PAD_LEFT);

                        // Registrar comprobante softland.cwcpbte
                        $result_comprobante = $this->insertarComprobante(
                            $conexion_db,
                            $periodo,
                            $numero_formateado_CpbNum,
                            $codigo_area,
                            $fecha_facturacion_formateada,
                            $mes,
                            $numero_formateado_CpbNui,
                            $nombre_prevision_format,
                            $fecha_pago_factura_completa,
                            $response
                        );

                        // return $response->withJson([
                        //     "success" => "Inserción exitosa.",
                        //     "result_comprobante" => $result_comprobante,
                        //     "fecha_pago_factura_completa" => $fecha_pago_factura_completa,
                        // ], 200);
                        // exit();

                    }


                    // Verificar si se debe continuar con la inserción en cwmovim
                    if ($result_comprobante) {
                        // return $response->withJson([
                        //     "success" => "comprobante insertado correctamente.",
                        // ], 200);
                        // exit();

                        // SI $length_data ES MAYOR QUE 1 ENTONCES EXISTEN PAGOS A DISTINTAS SUCURSALES ASIGAMOS VALOR CUENTA CUADRE.
                        // EN CASO CONTRARIO CUENTA DE BANCO.
                        if ($length_data > 1) {
                            $cod_cuenta_banco = '6-01-01-001'; //CUENTA CUADRE
                        }


                        $parametros = [
                            'cantidad_MovNum' => $cantidad_MovNum,
                            'periodo' => $periodo,
                            'numero_formateado_CpbNum' => $numero_formateado_CpbNum,
                            'codigo_area' => $codigo_area,
                            'codigoCliente' => $codigoCliente,
                            'fecha_facturacion_formateada' => $fecha_facturacion_formateada,
                            'mes' => $mes,
                            'cod_prevision_soft' => $cod_prevision_soft,
                            'nuevoCpbNum' => $nuevoCpbNum,
                            'folio' => $folio,
                            'monto_factura' => $monto_factura,
                            'nombre_prevision_format' => $nombre_prevision_format,
                            'fecha_pago_factura_completa' => $fecha_pago_factura_completa,
                            'cod_forma_pago' => $cod_forma_pago,
                            'cod_cuenta_banco' => $cod_cuenta_banco
                        ];

                        // return $response->withJson([
                        //     "success" => "comprobante insertado correctamente 2.",
                        // ], 200);
                        // exit();


                        // Registrar en cwmovim detalle comprobante
                        $result = $this->registrar_cwmovim_cuentaCliente($conexion_db, $parametros);



                        // return $response->withJson([
                        //     "success" => "1.",
                        //     "result" => $result, 
                        // ], 200);
                        // exit();

                        if ($result) {
                            // return $response->withJson([
                            //     "success" => "2.",
                            //     "result" => $result, 
                            // ], 200);
                            // exit();

                            $cantidad_MovNum++;
                            $arrayFolios[] = $folio;
                            $arrayFolios_cuadre[] = $folio;
                            // Actualizar el valor del campo cantidad_MovNum
                            $parametros['cantidad_MovNum'] = $cantidad_MovNum;

                            // return $response->withJson([
                            //     "success" => "2.",
                            //     "result" => $result, 
                            //     "cantidad_MovNum" => $cantidad_MovNum, 
                            //     "arrayFolios" => $arrayFolios
                            // ], 200);
                            // exit();

                        } else {
                            return $response->withJson(["error" => "Error al insertar en cwmovim."], 400);
                            // return $response->withJson([
                            //     "success" => "comprobante insertado correctamente 5.",
                            // ], 200);
                            exit();
                        }
                    }
                    // Convertir el monto de la factura a float
                    $monto_factura_int = (float) $monto_factura;
                    // Suma el monto de las facturas
                    $monto_total_facturas += $monto_factura;
                    // ASIGNAR ID_AREA ACTUAL
                    $codigo_area_anterior = $codigo_area;
                }


                // return $response->withJson([
                //     "success" => "III",
                //     "result" => $result, 
                //     "arrayFolios" => $arrayFolios,
                //     "cantidad_MovNum" => $cantidad_MovNum, 
                //     "monto_total_facturas" => $monto_total_facturas,
                //     "nuevoCpbNum" => $nuevoCpbNum
                // ], 200);
                // exit();

                //REGISTRA CUENTA DE BANCO 
                $result = $this->registrar_cwmovim_cuentaBanco($conexion_db, $parametros, $monto_total_facturas, $response);

                // return $response->withJson([
                //     "success" => "registrar_cwmovim_cuentaBanco SOFTLAND.",
                //     "result" => $result,
                //  ], 200);
                // if ($result) {
                //     // echo "Inserción exitosa en cwmovim_cuentaBanco\n";

                // } else {
                //     return $response->withJson(["error" => "Error al insertar en cwmovim_cuentaBanco."], 400);
                // }

                if (!$result) {
                    // return $response->withJson(["error" => "Error al insertar en cwmovim_cuentaBanco."], 400);
                    return $response->withJson([
                        "error" => "Ocurrió un error al registrar el pago de facturas en Softland.",
                        "detalle" => $result // Puedes personalizar el detalle del error aquí
                    ], 500);
                }


                // Guardar CpbNum y folios
                $codigosComprobantes[$codBode] = [
                    'CpbNum' => $numero_formateado_CpbNum,
                    'Folios' => implode(',', $arrayFolios)
                ];


            }

            // // Retornar respuesta final
            // return $response->withJson([
            //     "success" => "Inserción exitosa final.",
            //     "codigos_comprobantes"  => $codigosComprobantes
            // ], 200);

            /*VALIDA LONGITUD DE LOS DATOS.
             * SI LA LONGITUD ES MAYOR  1 ENTONCES REGISTRA CUENTA CUADRE.
             */
            if ($length_data > 1) {
                // REGISTRA CUENTA CUADRE   
                // Paso 1 sumar 1 a cada variable.
                $nuevoCpbNum++; //--> corresponde al nuevo numero comprobante.
                $nuevoCpbNui++; //--> corresponde al nuevo numero interno.
                // Formatear los números obtenidos
                $numero_formateado_CpbNum = str_pad($nuevoCpbNum, 8, "0", STR_PAD_LEFT);
                $numero_formateado_CpbNui = str_pad($nuevoCpbNui, 8, "0", STR_PAD_LEFT);


                // Registrar comprobante softland.cwcpbte
                $result_comprobante = $this->insertarComprobante(
                    $conexion_db,
                    $periodo,
                    $numero_formateado_CpbNum,
                    $codigo_area,
                    $fecha_facturacion_formateada,
                    $mes,
                    $numero_formateado_CpbNui,
                    $nombre_prevision_format,
                    $fecha_pago_factura_completa,
                    $response
                );

                $result = $this->registrar_cwmovim_cuentaCuadre($conexion_db, $parametros, $numero_formateado_CpbNum, $numero_formateado_CpbNui, $data_banco, $monto_total_transferido, $response);
                // return $response->withJson([
                //     "success" => "registrar_cwmovim_cuentaCuadre I.",
                //     "result" => $result, 
                // ], 200);
                // exit();

                if ($result) {
                    // Retornar respuesta final
                    return $response->withJson([
                        "success" => "El registro del pago de facturas se realizó exitosamente en Softland.",
                        "codigos_comprobantes" => $codigosComprobantes,
                        "codigos_comprobantes_cuadre" => $numero_formateado_CpbNum,
                        'Folios_cuadre' => implode(',', $arrayFolios_cuadre)
                    ], 200);

                } else {
                    // return $response->withJson(["error" => "Error al insertar en cwmovim."], 400);

                    return $response->withJson([
                        "error" => "Ocurrió un error al registrar el pago de facturas en Softland.",
                        "detalle" => $result // Puedes personalizar el detalle del error aquí
                    ], 500);
                }

            }

            // Retornar respuesta final
            return $response->withJson([
                "success" => "El registro del pago de facturas se realizó exitosamente en Softland.",
                "codigos_comprobantes" => $codigosComprobantes
            ], 200);



        } catch (Exception $e) {
            // Si ocurre un error, hacer rollback
            // $conexion_db->rollBack();
            // echo "Error: " . $e->getMessage();
            return $response->withJson(["error" => $e->getMessage()], 500);
            // return $response->withJson([
            //     "error" => $e->getMessage(),
            //     "file" => $e->getFile(),    // Archivo donde ocurrió el error
            //     "line" => $e->getLine()     // Línea donde ocurrió el error
            // ], 500);
        }
    }


    public function registrar_cwmovim_cuentaBanco($conexion_db, $parametros, $monto_total_facturas, $response)
    {
        try {


            //  echo " funcion registrar_cwmovim_cuentaBanco parametros:\n";
            //  var_dump($parametros);
            //  echo "</pre>";
            //  exit();


            // return [
            //     "success" => "registrar_cwmovim_cuentaBanco SOFTLAND.",
            //     "parametros" => $parametros,
            //  ];



            // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
            $MovNum = $parametros['cantidad_MovNum'];
            $CpbAno = $parametros['periodo'];
            $CpbNum = $parametros['numero_formateado_CpbNum'];
            $AreaCod = $parametros['codigo_area'];
            $PctCod = $parametros['cod_cuenta_banco'];
            $CpbFec = $parametros['fecha_facturacion_formateada'];
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = 0;
            $MovFe = $parametros['fecha_facturacion_formateada'];
            $MovFv = $parametros['fecha_facturacion_formateada'];
            $MovNumDocRef = 0;
            // $MovDebe = $parametros['monto_factura'];
            $MovDebe = $monto_total_facturas;
            $MovGlosa = $parametros['nombre_prevision_format'];
            // $MovDebeMa = $parametros['monto_factura'];
            $MovDebeMa = $monto_total_facturas;
            $FecPag = $parametros['fecha_pago_factura_completa'];
            // $FormadePag = $parametros['cod_forma_pago'];

            //insertar detalle comprobante
            $sql_cwmovim = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES (
                :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, :CpbFec, :CpbMes, :CvCod, :VendCod, :UbicCod,
                :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
                :TtdCod, :NumDoc, :MovFe, :MovFv, :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
                :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
                :MovIPr, :MovAEquiv, :FecPag, :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
                :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
                :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
                :RowKey, :PartitionKey, :NumDocRef1
            )";

            $stmt_movim = $conexion_db->prepare($sql_cwmovim);

            // Vincular los parámetros de manera individual
            $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
            $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
            $stmt_movim->bindValue(':TipDocCb', 'TR', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocCb', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
            $stmt_movim->bindValue(':TtdCod', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTipDocRef', '00', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebe', $MovDebe, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaber', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovGlosa', $MovGlosa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovDebeMa', $MovDebeMa, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovHaberMa', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
            $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':fecEmisionch', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
            $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':FormadePag', NULL, PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
            $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
            $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt_movim->execute();
            // Imprimir el número de filas afectadas
            // echo "Número de filas afectadas: " . $stmt_movim->rowCount();
            if ($stmt_movim->rowCount() > 0) {
                //echo "Inserción exitosa.";
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            // Manejo de excepciones en caso de errores
            // $this->logger->info("Error en archivo: ". __FILE__ .", Linea: " . $e->getLine() . ", Error: ".$e->getMessage());
            // return $response->withJson(["error" => "Excepción capturada - ".$e->getMessage()], 400);
            // return $response->withJson([
            //     "error registrar_cwmovim_cuentaBanco:" => $e->getMessage(),
            //     "file" => $e->getFile(),    // Archivo donde ocurrió el error
            //     "line" => $e->getLine()     // Línea donde ocurrió el error
            // ], 500);

            return [
                "error registrar_cwmovim_cuentaBanco:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }

    }

    function registrar_cwmovim_cuentaCuadre($conexion_db, $parametros, $numero_formateado_CpbNum, $numero_formateado_CpbNui, $data_banco, $monto_total_transferido, $response)
    {

        $longitud = count($data_banco);
        if ($longitud === 0) {
            return ["error" => "El array data_banco está vacío"];
        }

        try {

            // Datos globales
            $CpbAno = $parametros['periodo'];
            $CpbNum = $numero_formateado_CpbNum;
            $AreaCod = $parametros['codigo_area'];
            $CpbFec = $parametros['fecha_facturacion_formateada'];
            $CpbMes = $parametros['mes'];
            $CodAux = '0000000000';
            $NumDoc = 0;
            $MovFe = $parametros['fecha_facturacion_formateada'];
            $MovFv = $parametros['fecha_facturacion_formateada'];
            $MovNumDocRef = 0;
            $FecPag = $parametros['fecha_pago_factura_completa'];
            $MovGlosaCliente = $parametros['nombre_prevision_format'];

            $sql_cwmovim = "";

            $MovNum = 0;
            // Banco
            $PctCodB = '6-01-01-001';
            $MovHaber = $monto_total_transferido;
            $MovHaberMa = $monto_total_transferido;

            $sql_cwmovim .= "(
                '$CpbAno', '$CpbNum', $MovNum, '$AreaCod', '$PctCodB', '$CpbFec', '$CpbMes', '000', '0000', '000',
                '0000000000', '000', 0, '00000000', 0, '00000000', '00', 0, '$CodAux',
                '00', '$NumDoc', '$MovFe', '$MovFv', '00', '$MovNumDocRef', 0, $MovHaber, 
                'Cuenta cuadre', '01', 1, 0, $MovHaberMa, '', '', '', '', '', '$FecPag', '', 
                0, 0, 'S', '', '', '', 0, 0, 0, 'N', NULL, '', 'N', '', '', NULL, 
                'S', 'S', '', '', ''
            ),";

            foreach ($data_banco as $item) {
                $MovNum++; // Incrementamos después de la línea banco
                // Cliente
                $PctCod = $parametros['codigoCliente'];
                // $MovDebe = $parametros['monto_factura'];
                $MovGlosa = $parametros['nombre_prevision_format'];
                // $MovDebeMa = $parametros['monto_factura'];

                $MovDebe = $item['Deposito o Abono'];
                $MovDebeMa = $item['Deposito o Abono'];

                $sql_cwmovim .= "(
                    '$CpbAno', '$CpbNum', $MovNum, '$AreaCod', '$PctCod', '$CpbFec', '$CpbMes', '000', '0000', '000',
                    '0000000000', '000', 0, '00000000', 0, '00000000', 'TR', 0, '$CodAux',
                    '00', '$NumDoc', '$MovFe', '$MovFv', '00', '$MovNumDocRef', $MovDebe, 0, 
                    '$MovGlosaCliente', '01', 1, $MovDebeMa, 0, '', '', '', '', '', '$FecPag', '', 
                    0, 0, 'S', '', '', '', 0, 0, 0, 'N', NULL, '', 'N', '', '', NULL, 
                    'S', 'S', '', '', ''
                ),";
            }

            // Eliminar la última coma
            $sql_cwmovim = rtrim($sql_cwmovim, ",");

            // Preparar y ejecutar
            $sql_final = "INSERT INTO softland.cwmovim (
                CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
                CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
                TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
                MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
                MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
                CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
                Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
                RowKey, PartitionKey, NumDocRef1
            ) VALUES $sql_cwmovim";

            // return ["success" => "registrar_cwmovim_cuentaCuadre II.",
            //         "sql_final" =>  $sql_final,
            //         ];
            // exit();

            $stmt_movim = $conexion_db->prepare($sql_final);
            $stmt_movim->execute();

            if ($stmt_movim->rowCount() > 0) {
                return true;
            } else {
                // return ["error" => "No se insertó ningún registro."];
                return false;
            }

        } catch (Exception $e) {
            return [
                "error registrar_cwmovim_cuentaCuadre:" => $e->getMessage(),
                "file" => $e->getFile(),    // Archivo donde ocurrió el error
                "line" => $e->getLine()     // Línea donde ocurrió el error
            ];
        }

    }

    public function insert_iw_gsaen($infoFactura, $sucursal, $ambiente = 'DEV')
    {

        try {

            $conexion_db;

            // $conexion_db = $this->app->get("db15");
            if ($ambiente == 'DEV') {
                $conexion_db = $this->container->get('dbSOFTLAND_DEV');
            }

            if ($ambiente == 'PROD') {
                if ($sucursal == 2) {
                    $conexion_db = $this->container->get('dbSOFTLAND_DEV_HF');
                } else {
                    $conexion_db = $this->container->get('dbSOFTLAND_DEV');
                }

            }

            // Iniciar la transacción
            $conexion_db->beginTransaction();

            $folio;
            $nroint;

            // obtener NroInt y Folio
            $sql = "
                SELECT coalesce(max(folio)+1, 1) as folio, coalesce(max(NroInt)+1, 1) as num 
                FROM softland.iw_gsaen  
                WHERE tipo ='F';";

            $stmt = $conexion_db->prepare($sql);
            $stmt->execute();

            // Obtener resultados
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Recorrer resultados
            foreach ($result as $row) {
                // Accede a los valores de cada columna de la fila
                $folio = $row["folio"];
                $nroint = $row["num"];
            }

            $sql_insert1 = "
                INSERT INTO softland.iw_gsaen (
                    Tipo, NroInt, CodBode, CodCaja, Folio, Concepto, Estado, Fecha, Glosa, AuxTipo, CodAux, CentroDeCosto, SubTipoDocto, 
                    PorcCredEmpConst, DescCredEmpConst, PagoConTarjeta, TipoTrans, FmaPago, EsImportacion,
                    NetoExento, Total, SubTotal, usuario, CodMoneda, CondPago, Proceso, FecHoraCreacion, TipoServicioSII, sistema
                ) 
                VALUES (
                    'F', :nroint, :codbodega, :codcaja, :folio, :concepto, 'V', :fecha, 'Generacion de Factura desde API', 'A', :codaux, '704-001', 'S', 
                    0, 0, 0, 1, 2, 0, 
                    :netoExento, :total, :subtotal, 'softland', '01', '002', 'Factura en Linea', :fechacreacion, 3, 'IW')";

            $stmt = $conexion_db->prepare($sql_insert1);

            $stmt->bindParam(':nroint', $nroint);
            $stmt->bindParam(':codbodega', $infoFactura['codbodega']);
            $stmt->bindParam(':codcaja', $infoFactura['codcaja']);
            $stmt->bindParam(':folio', $folio);
            $stmt->bindParam(':concepto', $infoFactura['concepto']);
            $stmt->bindParam(':fecha', $infoFactura['fecha']);
            $stmt->bindParam(':codaux', $infoFactura['codaux']);
            $stmt->bindParam(':netoExento', $infoFactura['total']);
            $stmt->bindParam(':total', $infoFactura['total']);
            $stmt->bindParam(':subtotal', $infoFactura['total']);
            $stmt->bindParam(':fechacreacion', $infoFactura['fecha']);
            $stmt->execute();


            // registro del detalle de la factura
            $sql_insert2 = "
            INSERT INTO softland.iw_gmovi (
                CodBode, Fecha, Tipo, NroInt, Linea, TipoOrigen, TipoDestino, AuxTipo, 
                CodAux, CodProd, CantFacturada, PreUniMB, TotLinea, DetProd) 
            VALUES (:codbodega, :fecha, 'F', :nroint, :linea, 'D', 'N', 'A',
            :codaux, :codprod, 1, :preunit, :totallinea, :detprod)";

            $stmt = $conexion_db->prepare($sql_insert2);

            // correlativo
            $linea = 1;
            foreach ($infoFactura['detDocumento'] as $key => $detalle) {
                $stmt->bindParam(':codbodega', $infoFactura['codbodega']);
                $stmt->bindParam(':fecha', $infoFactura['fecha']);
                $stmt->bindParam(':nroint', $nroint);
                $stmt->bindParam(':linea', $linea);
                $stmt->bindParam(':codaux', $infoFactura['codaux']);
                $stmt->bindParam(':codprod', $detalle['codigo']);
                $stmt->bindParam(':preunit', $detalle['total']);
                $stmt->bindParam(':totallinea', $detalle['total']);
                $stmt->bindParam(':detprod', $detalle['producto']);
                $stmt->execute();
                $linea++;
            }

            // $sql_insert3= " 
            // INSERT INTO softland.iw_loggsaen ( 
            //     Tipo, SubTipoDocto, Codbode , Folio , fecha, evento, usuario, sistema , accion, codaux) 
            // VALUES ('F', 'S', :codbodega, :folio, :fecha , 'Agregar' ,'softland', 'IW', '0', :codaux)";

            // $stmt =  $conexion_db->prepare($sql_insert3);

            // $stmt->bindParam(':codbodega', $infoFactura['codbodega']);
            // $stmt->bindParam(':folio', $folio);
            // $stmt->bindParam(':fecha', $infoFactura['fecha']);
            // $stmt->bindParam(':codaux', $infoFactura['codaux']);
            // $stmt->execute();

            // $sql_insert4= " 
            // INSERT INTO softland.iw_loggsaenvw (Tipo, subtipodocto, Codbode , Folio , fecha, evento, usuario, sistema , accion, equipo, aplicacion, Nroint ) 
            // VALUES ('F', 'S',  :codbodega, :folio, :fecha , :evento ,'softland', 'IW', '0', '192.168.1.15', 'INVENTARIO Y FACTURACIÓN', :nroint)";

            // $evento = 'Agregar ->-'.$nroint;

            // $stmt =  $conexion_db->prepare($sql_insert4);

            // $stmt->bindParam(':codbodega', $infoFactura['codbodega']);
            // $stmt->bindParam(':folio', $folio);
            // $stmt->bindParam(':fecha', $infoFactura['fecha']);
            // $stmt->bindParam(':evento', $evento);
            // $stmt->bindParam(':nroint', $nroint);
            // $stmt->execute();

            $sql6 = "select * from softland.cwtauxi where codaux ='" . $infoFactura['codaux'] . "'";
            $stmt = $conexion_db->prepare($sql6);
            $stmt->execute();

            // Obtener resultados
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $infoAux;

            // Recorrer resultados
            foreach ($result as $row) {
                // Accede a los valores de cada columna de la fila
                $infoAux = array(
                    'codaux' => $row["CodAux"],
                    'rutaux' => $row["RutAux"],
                    'giraux' => $row["GirAux"],
                    'comaux' => $row["ComAux"],
                    'ciuaux' => $row["CiuAux"],
                    'paiaux' => $row["PaiAux"],
                    'provaux' => $row["ProvAux"],
                    'diraux' => $row["DirAux"],
                    'region' => $row["Region"],
                    'nombre' => $row["NomAux"],
                    'dirnum' => $row["DirNum"],
                    'email' => $row["EMail"]
                );
            }

            $sql_insert6 = "insert INTO softland.iw_gsaen_tauxi (tipo, NroInt, nomaux, CodAux, RutAux, GirAux, ComAux, CiuAux, PaiAux, ProvAux, DirAux, Region, dirnum, email) 
            VALUES ('F', :nroint, :nombre, :codaux, :rutaux, :giraux, :comaux,	:ciuaux, :paiaux, :provaux,	:diraux, :region, :dirnum, :email)";

            $stmt = $conexion_db->prepare($sql_insert6);

            $stmt->bindParam(':nroint', $nroint);
            $stmt->bindParam(':nombre', $infoAux['nombre']);
            $stmt->bindParam(':codaux', $infoAux['codaux']);
            $stmt->bindParam(':rutaux', $infoAux['rutaux']);
            $stmt->bindParam(':giraux', $infoAux['giraux']);
            $stmt->bindParam(':comaux', $infoAux['comaux']);
            $stmt->bindParam(':ciuaux', $infoAux['ciuaux']);
            $stmt->bindParam(':paiaux', $infoAux['paiaux']);
            $stmt->bindParam(':provaux', $infoAux['provaux']);
            $stmt->bindParam(':diraux', $infoAux['diraux']);
            $stmt->bindParam(':region', $infoAux['region']);
            $stmt->bindParam(':dirnum', $infoAux['dirnum']);
            $stmt->bindParam(':email', $infoAux['email']);
            $stmt->execute();

            if ($infoFactura['mle'] != "") {

                $mle = 'MLE-' . $infoFactura['mle'];

                $sql_insert5 = "
                INSERT INTO softland.iw_gsaen_refdte (Tipo, Nroint, LineaRef, CodRefSII, FolioRef, FechaRef, cantDoctosLF, NetoAfectoLF, NetoExentoLF, TotalLF, Glosa) 
                VALUES ('F', :nroint, 1,'802', :mle, :fechamle ,0,0,0,0,'Nota de pedido')";

                $stmt = $conexion_db->prepare($sql_insert5);

                $stmt->bindParam(':nroint', $nroint);
                $stmt->bindParam(':mle', $infoFactura['mle']);
                $stmt->bindParam(':fechamle', $infoFactura['fechamle']);
                $stmt->execute();
            }

            // Si llegas hasta aquí sin excepciones, confirma la transacción
            $conexion_db->commit();

            // $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cerrar la conexión a la base de datos
            $conexion_db = null;

            return ['msje' => 'Factura Registrada Correctamente', 'status' => 200, 'folio' => $folio];

            // if ($result) {
            //     return ['data' => $result, 'status' => 200];
            // } else {
            //     // No se encontraron registros, devuelve un código de estado HTTP 404
            //     return ['error' => "No se encontraron registros.", 'status' => 404];
            // }
        } catch (PDOException $e) {
            // Manejo de errores de PDO
            return "Error de PDO: " . $e->getMessage();
            // return 'falla 1';
        } catch (Exception $e) {
            // Manejo de errores generales
            // echo "Error general: " . $e->getMessage();
            // Si hay algún error, revierte la transacción
            $conexion_db->rollBack();
            return 'falla 2';
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }

    }

    // Método de prueba para verificar la instancia
    public function verificarInstancia()
    {
        if ($this->app instanceof \Slim\App) {
            return "La instancia de la aplicación Slim se pasó correctamente a Softland.";
        } else {
            return "Problema al pasar la instancia de la aplicación Slim a Softland.";
        }
    }

}


/*  function insertarComprobante( 
         $conexion_db,
         $periodo,
         $numero_formateado_CpbNum,
         $codigo_area,
         $fecha_facturacion_formateada,
         $mes,
         $numero_formateado_CpbNui,
         $nombre_prevision_format,
         $fecha_pago_factura_completa,
         $response
    )
    {


         $fechaHoy = date('Ymd') . ' 00:00:00';
         try {
             // SQL para insertar en cwcpbte
             $sql_cwcpbte = "INSERT INTO softland.cjwcpbte 
                             (CpbAno, CpbNum, AreaCod, CpbFec, CpbMes, CpbEst, 
                             CpbTip, CpbNui, CpbGlo, CpbImp, CpbCon, Sistema, Proceso, Usuario, CpbNormaIFRS, 
                             CpbNormaTrib, CpbAnoRev, CpbNumRev, SistemaMod, ProcesoMod, FechaUlMod, TipoLog)
                             VALUES 
                             (:CpbAno, :CpbNum, :AreaCod, :CpbFec, :CpbMes, :CpbEst, :CpbTip, :CpbNui, :CpbGlo, 
                             :CpbImp, :CpbCon, :Sistema, :Proceso, :Usuario, :CpbNormaIFRS, :CpbNormaTrib, 
                             :CpbAnoRev, :CpbNumRev, :SistemaMod, :ProcesoMod, :FechaUlMod, :TipoLog)";

             // Preparar la consulta
             $stmt = $conexion_db->prepare($sql_cwcpbte);

             // Vincular los parámetros con los valores
             $stmt->bindValue(':CpbAno', $periodo, PDO::PARAM_STR);
             $stmt->bindValue(':CpbNum', $numero_formateado_CpbNum, PDO::PARAM_STR);
             $stmt->bindValue(':AreaCod', $codigo_area, PDO::PARAM_STR);
             // $stmt->bindValue(':CpbFec', $fecha_facturacion_formateada, PDO::PARAM_STR);
             $stmt->bindValue(':CpbFec', $fecha_pago_factura_completa, PDO::PARAM_STR);
             $stmt->bindValue(':CpbMes', $mes, PDO::PARAM_STR);
             $stmt->bindValue(':CpbEst', 'V', PDO::PARAM_STR);
             $stmt->bindValue(':CpbTip', 'I', PDO::PARAM_STR);
             $stmt->bindValue(':CpbNui', $numero_formateado_CpbNui, PDO::PARAM_STR);
             $stmt->bindValue(':CpbGlo', $nombre_prevision_format, PDO::PARAM_STR);
             $stmt->bindValue(':CpbImp', 'S', PDO::PARAM_STR);
             $stmt->bindValue(':CpbCon', 'S', PDO::PARAM_STR);
             $stmt->bindValue(':Sistema', 'XW', PDO::PARAM_STR);
             $stmt->bindValue(':Proceso', 'Pago en línea', PDO::PARAM_STR);
             $stmt->bindValue(':Usuario', 'RPA', PDO::PARAM_STR); //usuario1
             $stmt->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
             $stmt->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
             $stmt->bindValue(':CpbAnoRev', '0000', PDO::PARAM_STR);
             $stmt->bindValue(':CpbNumRev', '00000000', PDO::PARAM_STR);
             $stmt->bindValue(':SistemaMod', '', PDO::PARAM_STR);
             $stmt->bindValue(':ProcesoMod', '', PDO::PARAM_STR);
             // $stmt->bindValue(':FechaUlMod', $fecha_pago_factura_completa, PDO::PARAM_STR);
             $stmt->bindValue(':FechaUlMod', $fechaHoy, PDO::PARAM_STR);
             $stmt->bindValue(':TipoLog', 'U', PDO::PARAM_STR);

             // Ejecutar la consulta
             $stmt->execute();
             // Verificar si la inserción fue exitosa
             return $stmt->rowCount() > 0;

         } catch (Exception $e) {
             // Si ocurre un error, hacer rollback
             // $conexion_db->rollBack();
             // echo "Error: " . $e->getMessage();
             // return $response->withJson(["error" => $e->getMessage()], 500);
             return $response->withJson([
                 "error" => $e->getMessage(),
                 "file" => $e->getFile(),    // Archivo donde ocurrió el error
                 "line" => $e->getLine()     // Línea donde ocurrió el error
             ], 500);
         }
    } */

/* public function registrar_cwmovim_cuentaCliente($conexion_db, $parametros){
    try{
        // return ["success" => "registrar_cwmovim_cuentaCliente." ];
        // exit();

        // ASIGNAMOS LOS VALORES DE LOS PARAMETROS DINAMICOS
        $MovNum = $parametros['cantidad_MovNum'];
        $CpbAno = $parametros['periodo'];
        $CpbNum = $parametros['numero_formateado_CpbNum'];
        $AreaCod = $parametros['codigo_area'];
        $PctCod = $parametros['codigoCliente'];
        $CpbFec = $parametros['fecha_facturacion_formateada'];
        $CpbMes = $parametros['mes'];
        $CodAux = $parametros['cod_prevision_soft'];
        $NumDoc = $parametros['nuevoCpbNum']; 
        $MovFe = $parametros['fecha_facturacion_formateada'];
        $MovFv = $parametros['fecha_facturacion_formateada'];
        $MovNumDocRef = $parametros['folio'];
        $MovHaber = $parametros['monto_factura'];
        $MovGlosa = $parametros['nombre_prevision_format'];
        $MovHaberMa = $parametros['monto_factura'];
        $FecPag = $parametros['fecha_pago_factura_completa'];
        $FormadePag = $parametros['cod_forma_pago'];

        //insertar detalle comprobante
        $sql_cwmovim = "INSERT INTO softland.cwmovim (
            CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
            CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
            TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
            MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
            MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
            CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
            Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
            RowKey, PartitionKey, NumDocRef1
        ) VALUES (
            :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, :CpbFec, :CpbMes, :CvCod, :VendCod, :UbicCod,
            :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
            :TtdCod, :NumDoc, :MovFe, :MovFv, :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
            :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
            :MovIPr, :MovAEquiv, :FecPag, :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
            :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
            :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
            :RowKey, :PartitionKey, :NumDocRef1
        )";

        $stmt_movim = $conexion_db->prepare($sql_cwmovim);

        // Vincular los parámetros de manera individual
        $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
        $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
        $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CvCod', '000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':VendCod', '0000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':UbicCod', '000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CajCod', '0000000000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':IfCod', '000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovIfCant', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':DgaCod', '00000000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovDgCant', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CcCod', '00000000', PDO::PARAM_STR);
        $stmt_movim->bindValue(':TipDocCb', '00', PDO::PARAM_STR);
        $stmt_movim->bindValue(':NumDocCb', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
        $stmt_movim->bindValue(':TtdCod', 'DP', PDO::PARAM_STR);
        $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovTipDocRef', 'FL', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovDebe', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovHaber', $MovHaber, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovGlosa', $MovGlosa, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MonCod', '01', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovEquiv', 1, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovDebeMa', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovHaberMa', $MovHaberMa, PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovNumCar', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovTC', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovNC', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovIPr', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MovAEquiv', 'S', PDO::PARAM_STR);
        $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CODCPAG', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CbaNumMov', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CbaAnoC', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':GrabaDLib', 'S', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbOri', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CodBanco', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CodCtaCte', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':MtoTotal', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':Cuota', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CuotaRef', 0, PDO::PARAM_STR);
        $stmt_movim->bindValue(':Marca', 'N', PDO::PARAM_STR);
        $stmt_movim->bindValue(':fecEmisionch', NULL, PDO::PARAM_STR);
        $stmt_movim->bindValue(':paguesea', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':Impreso', 'N', PDO::PARAM_STR);
        $stmt_movim->bindValue(':dlicoint_aperturas', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':nro_operacion', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':FormadePag', $FormadePag, PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbNormaIFRS', 'S', PDO::PARAM_STR);
        $stmt_movim->bindValue(':CpbNormaTrib', 'S', PDO::PARAM_STR);
        $stmt_movim->bindValue(':RowKey', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':PartitionKey', '', PDO::PARAM_STR);
        $stmt_movim->bindValue(':NumDocRef1', '', PDO::PARAM_STR);

        // Ejecutar la consulta
        $stmt_movim->execute();
        if ($stmt_movim->rowCount() > 0) {
            //echo "Inserción exitosa.";
            return true;
        } else {
            return false;
        }
       

    } catch (Exception $e) {
        return[
            "error registrar_cwmovim_cuentaCliente:" => $e->getMessage(),
            "file" => $e->getFile(),    // Archivo donde ocurrió el error
            "line" => $e->getLine()     // Línea donde ocurrió el error
        ];
    }
    


} */


// try {

//     $conexion_db = $this->container->get('dbSOFTLAND_DEV');

//     // Verificar si la conexión es exitosa
//     if (!$conexion_db) {
//         return $response->withJson(["error" => "No se pudo obtener la conexión a la base de datos."], 500);
//     }


//     /*Consulta para obtener el valor de AreaCod de la base de datos de softland 
//     * Obtiene el valor AreaCod del registro comprobante de la factura centralizada
//     * que permite identificar el codigo de la sucursal donde se registro la factura 
//     */
//     $sql_0 ="SELECT
//                 AreaCod 
//             FROM softland.cwmovim
//             where MovNumDocRef in ($folio)
//             AND CodAux = $cod_prevision_soft
//             AND TtdCod = 'FL';";

//     $stmt = $conexion_db->prepare($sql_0);
//     $stmt->execute();
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($result) {
//         $codigo_area = $result['AreaCod'];


//     } else {
//         // Si no hay registros previos, asignar valores iniciales
//         $codigo_area = "";

//     }


//     if ($codigo_area != $codigo_area_anterior) {
//         // Generar nuevo número de comprobante y número interno ingresos
//         $codigo_area_anterior = $codigo_area;

//         /*Obtener el número de comprobante y el número interno del encabezado del comprobante.
//         * obtiene el ultimo valor registrado en el campo CpbNum y suma 1 -> numero de comprobante 
//         * obtiene el ultimo valor registrado en el campo CpbNui y suma 1 -> numero interno ingresos
//         */
//         $sql_1 ="SELECT TOP 1 
//                     CpbNum + 1 AS CpbNum
//                 FROM softland.cwcpbte 
//                 WHERE CpbAno = $periodo
//                 AND CpbMes = $mes
//                 ORDER BY CpbNum DESC;";

//         $stmt = $conexion_db->prepare($sql_1);
//         $stmt->execute();
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);

//         if ($result) {
//             $nuevoCpbNum = $result['CpbNum'];
//         } else {
//             // Si no hay registros previos, asignar valores iniciales
//             $nuevoCpbNum = "1";

//         }
//         /* Numero interno ingresos*/
//         $sql_2 ="SELECT TOP 1 
//                     CpbNui + 1 AS CpbNui
//                 FROM softland.cwcpbte 
//                 WHERE CpbAno = $periodo
//                 AND CpbTip = 'I'
//                 AND Sistema = 'XW'
//                 AND CpbMes = $mes
//                 ORDER BY CpbNum DESC;";

//         $stmt = $conexion_db->prepare($sql_2);
//         $stmt->execute();
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);

//         if ($result) {
//             $nuevoCpbNui = $result['CpbNui'];
//         }else{
//             $nuevoCpbNui = "1";
//         }

//         echo "<pre>";
//         echo "Nuevo CpbNum:\n";
//         var_dump($nuevoCpbNum);

//         echo "\nNuevo CpbNui:\n";
//         var_dump($nuevoCpbNui);
//         echo "</pre>";
//         //FORMATO DE DATOS
//         //COMPROBANTE
//         $numero_formateado_CpbNum = str_pad($nuevoCpbNum, 8, "0", STR_PAD_LEFT);
//         //NUMERO INTERNO
//         $numero_formateado_CpbNui = str_pad($nuevoCpbNui, 8, "0", STR_PAD_LEFT);




//     }else{

//     }

//     //FORMATO DE DATOS
//     // PAGO CLIENTES
//     $nombre_prevision_format = strtoupper('PAGO CLIENTES ' . $nombre_prevision);

//     // Consulta para insertar en la tabla `cwcpbte`
//     //parametros estaticos
//     $CpbEst ='V';
//     $CpbTip = 'I';
//     $CpbImp ='S';
//     $CpbCon ='S';
//     $Sistema = 'XW';
//     $Proceso = 'Pago en línea';
//     $CpbNormaIFRS ='N';
//     $CpbNormaTrib ='N';
//     $CpbAnoRev = '0000';
//     $CpbNumRev = '00000000';
//     $TipoLog ='U';

//     //param dinamicos
//     $CpbAno = $periodo; // año periodo contable
//     $CpbNum = $numero_formateado_CpbNum; //numero comprobante
//     $AreaCod = $codigo_area;
//     $CpbFec = $fecha_facturacion_formateada; // 0000-00-00 00:00:00.000
//     $CpbMes = $mes; // mes de facturacion
//     $CpbNui = $numero_formateado_CpbNui; // numero interno
//     $CpbGlo = $nombre_prevision_format; 
//     $Usuario = 'usuario1'; // usuario registra
//     $SistemaMod = '';
//     $ProcesoMod = '';
//     $FechaUlMod = $fecha_pago_factura_completa; // fecha real de registro
//     //$FechaUlMod = date('Y-m-d H:i:s'); // fecha real de registro

//     // Iniciar transacción
//     $conexion_db->beginTransaction();

//     $sql_cwcpbte = "INSERT INTO softland.cwcpbte (
//                         CpbAno, CpbNum, AreaCod, CpbFec, CpbMes, CpbEst, CpbTip, CpbNui, CpbGlo, 
//                         CpbImp, CpbCon, Sistema, Proceso, Usuario, CpbNormaIFRS, CpbNormaTrib, 
//                         CpbAnoRev, CpbNumRev, SistemaMod, ProcesoMod, FechaUlMod, TipoLog
//                     ) VALUES (
//                         :CpbAno, :CpbNum, :AreaCod, :CpbFec, :CpbMes, :CpbEst, :CpbTip, :CpbNui, :CpbGlo, 
//                         :CpbImp, :CpbCon, :Sistema, :Proceso, :Usuario, :CpbNormaIFRS, :CpbNormaTrib, 
//                         :CpbAnoRev, :CpbNumRev, :SistemaMod, :ProcesoMod, :FechaUlMod, :TipoLog
//                     )";

//     // Preparar la consulta
//     $stmt = $conexion_db->prepare($sql_cwcpbte);

//     // Parámetros a insertar
//    // Vincular los parámetros
//     $stmt->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR); 
//     $stmt->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbEst', $CpbEst, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbTip', $CpbTip, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbNui', $CpbNui, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbGlo', $CpbGlo, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbImp', $CpbImp, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbCon', $CpbCon, PDO::PARAM_STR); 
//     $stmt->bindValue(':Sistema', $Sistema, PDO::PARAM_STR); 
//     $stmt->bindValue(':Proceso', $Proceso, PDO::PARAM_STR); 
//     $stmt->bindValue(':Usuario', $Usuario, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbNormaIFRS', $CpbNormaIFRS, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbNormaTrib', $CpbNormaTrib, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbAnoRev', $CpbAnoRev, PDO::PARAM_STR); 
//     $stmt->bindValue(':CpbNumRev', $CpbNumRev, PDO::PARAM_STR);
//     $stmt->bindValue(':SistemaMod', $SistemaMod, PDO::PARAM_STR); 
//     $stmt->bindValue(':ProcesoMod', $ProcesoMod, PDO::PARAM_STR); 
//     $stmt->bindValue(':FechaUlMod', $FechaUlMod, PDO::PARAM_STR); 
//     $stmt->bindValue(':TipoLog', $TipoLog, PDO::PARAM_STR); 

//     // Ejecutar la consulta con los parámetros
//     $stmt->execute();

//     // Verificar si la inserción fue exitosa
//     if ($stmt->rowCount() > 0) {
//         echo "Inserción exitosa en softland.cwcpbte \n";
//         // Almacenando los valores en un arreglo asociativo usando los nombres de las variables
//         $parametros = [
//             'cantidad_MovNum' => $cantidad_MovNum,
//             'periodo' => $periodo,
//             'numero_formateado_CpbNum' => $numero_formateado_CpbNum,
//             'codigo_area' => $codigo_area,
//             'codigoCliente' => $codigoCliente,
//             'fecha_facturacion_formateada' => $fecha_facturacion_formateada,
//             'mes' => $mes,
//             'cod_prevision_soft' => $cod_prevision_soft,
//             'nuevoCpbNum' => $nuevoCpbNum,
//             'folio' => $folio,
//             'monto_factura' => $monto_factura,
//             'nombre_prevision_format' => $nombre_prevision_format,
//             'fecha_pago_factura_completa' => $fecha_pago_factura_completa,
//             'cod_forma_pago' => $cod_forma_pago
//         ];
//         $result = registrar_cwmovim_cuentaCliente($conexion_db, $parametros);
//         //suma mas uno a cada registro
//         $cantidad_MovNum++;

//         // exit();
//         //return $response->withJson(["success" => "Registro insertado correctamente."], 201);

//         // //PARAMETROS ESTATICOS REGISTRO DP -> LINEA DE DEPOSITO PAGO DE FACTURA
//         // $CvCod = '000';
//         // $VendCod ='0000';
//         // $UbicCod ='000';
//         // $CajCod = '0000000000';
//         // $IfCod = '000';
//         // $MovIfCant = 0;
//         // $DgaCod ='00000000';
//         // $MovDgCant = 0;
//         // $CcCod = '00000000';
//         // $TipDocCb ='00';
//         // $NumDocCb = 0;
//         // $TtdCod = 'DP';
//         // $MovTipDocRef = 'FL';
//         // $MovDebe = 0;
//         // $MonCod = '01';
//         // $MovEquiv = 1;
//         // $MovDebeMa = 0;
//         // $MovNumCar = '';
//         // $MovTC = '';
//         // $MovNC = '';
//         // $MovIPr = '';
//         // $MovAEquiv = 'S';
//         // $CODCPAG = '';
//         // $CbaNumMov = 0;
//         // $CbaAnoC = 0;
//         // $GrabaDLib = 'S';
//         // $CpbOri = '';
//         // $CodBanco = '';
//         // $CodCtaCte = '';
//         // $MtoTotal = 0;
//         // $Cuota = 0;
//         // $CuotaRef = 0;
//         // $Marca = 'N';
//         // $fecEmisionch = NULL;
//         // $paguesea = '';
//         // $Impreso = 'N';
//         // $dlicoint_aperturas = '';
//         // $nro_operacion = '';
//         // $CpbNormaIFRS = 'S';
//         // $CpbNormaTrib = 'S';
//         // $RowKey = '';
//         // $PartitionKey = '';
//         // $NumDocRef1 = ' ';



//         // // Parámetros dinámicos
//         // $MovNum = $cantidad_MovNum;
//         // $CpbAno = $periodo;
//         // $CpbNum = $numero_formateado_CpbNum;
//         // $AreaCod = $codigo_area;
//         // $PctCod = $codigoCliente;
//         // $CpbFec = $fecha_facturacion_formateada;
//         // $CpbMes = $mes;
//         // $CodAux = $cod_prevision_soft;
//         // $NumDoc = $nuevoCpbNum;
//         // $MovFe = $fecha_facturacion_formateada;
//         // $MovFv = $fecha_facturacion_formateada;
//         // $MovNumDocRef = $folio;
//         // $MovHaber = $monto_factura;
//         // $MovGlosa = $nombre_prevision_format;
//         // $MovHaberMa = $monto_factura;
//         // $FecPag = $fecha_pago_factura_completa;
//         // $FormadePag = $cod_forma_pago;




//         // //insertar detalle comprobante
//         // $sql_cwmovim = "INSERT INTO softland.cwmovim (
//         //     CpbAno, CpbNum, MovNum, AreaCod, PctCod, CpbFec, CpbMes, CvCod, VendCod, UbicCod, 
//         //     CajCod, IfCod, MovIfCant, DgaCod, MovDgCant, CcCod, TipDocCb, NumDocCb, CodAux, 
//         //     TtdCod, NumDoc, MovFe, MovFv, MovTipDocRef, MovNumDocRef, MovDebe, MovHaber, 
//         //     MovGlosa, MonCod, MovEquiv, MovDebeMa, MovHaberMa, MovNumCar, MovTC, MovNC, 
//         //     MovIPr, MovAEquiv, FecPag, CODCPAG, CbaNumMov, CbaAnoC, GrabaDLib, CpbOri, 
//         //     CodBanco, CodCtaCte, MtoTotal, Cuota, CuotaRef, Marca, fecEmisionch, paguesea, 
//         //     Impreso, dlicoint_aperturas, nro_operacion, FormadePag, CpbNormaIFRS, CpbNormaTrib, 
//         //     RowKey, PartitionKey, NumDocRef1
//         // ) VALUES (
//         //     :CpbAno, :CpbNum, :MovNum, :AreaCod, :PctCod, :CpbFec, :CpbMes, :CvCod, :VendCod, :UbicCod,
//         //     :CajCod, :IfCod, :MovIfCant, :DgaCod, :MovDgCant, :CcCod, :TipDocCb, :NumDocCb, :CodAux,
//         //     :TtdCod, :NumDoc, :MovFe, :MovFv, :MovTipDocRef, :MovNumDocRef, :MovDebe, :MovHaber,
//         //     :MovGlosa, :MonCod, :MovEquiv, :MovDebeMa, :MovHaberMa, :MovNumCar, :MovTC, :MovNC,
//         //     :MovIPr, :MovAEquiv, :FecPag, :CODCPAG, :CbaNumMov, :CbaAnoC, :GrabaDLib, :CpbOri,
//         //     :CodBanco, :CodCtaCte, :MtoTotal, :Cuota, :CuotaRef, :Marca, :fecEmisionch, :paguesea,
//         //     :Impreso, :dlicoint_aperturas, :nro_operacion, :FormadePag, :CpbNormaIFRS, :CpbNormaTrib,
//         //     :RowKey, :PartitionKey, :NumDocRef1
//         // )";

//         // $stmt_movim = $conexion_db->prepare($sql_cwmovim);

//         // // Vincular los parámetros de manera individual
//         // $stmt_movim->bindValue(':CpbAno', $CpbAno, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CpbNum', $CpbNum, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovNum', $MovNum, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':AreaCod', $AreaCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':PctCod', $PctCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CpbFec', $CpbFec, PDO::PARAM_STR);
//         // // echo "VALOR CpbFec:  \n";
//         // // var_dump($CpbFec);
//         // // echo "</pre>";
//         // // exit();
//         // $stmt_movim->bindValue(':CpbMes', $CpbMes, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CvCod', $CvCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':VendCod', $VendCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':UbicCod', $UbicCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CajCod', $CajCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':IfCod', $IfCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovIfCant', $MovIfCant, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':DgaCod', $DgaCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovDgCant', $MovDgCant, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CcCod', $CcCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':TipDocCb', $TipDocCb, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':NumDocCb', $NumDocCb, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CodAux', $CodAux, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':TtdCod', $TtdCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':NumDoc', $NumDoc, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovFe', $MovFe, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovFv', $MovFv, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovTipDocRef', $MovTipDocRef, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovNumDocRef', $MovNumDocRef, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovDebe', $MovDebe, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovHaber', $MovHaber, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovGlosa', $MovGlosa, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MonCod', $MonCod, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovEquiv', $MovEquiv, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovDebeMa', $MovDebeMa, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovHaberMa', $MovHaberMa, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovNumCar', $MovNumCar, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovTC', $MovTC, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovNC', $MovNC, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovIPr', $MovIPr, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MovAEquiv', $MovAEquiv, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':FecPag', $FecPag, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CODCPAG', $CODCPAG, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CbaNumMov', $CbaNumMov, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CbaAnoC', $CbaAnoC, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':GrabaDLib', $GrabaDLib, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CpbOri', $CpbOri, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CodBanco', $CodBanco, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CodCtaCte', $CodCtaCte, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':MtoTotal', $MtoTotal, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':Cuota', $Cuota, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CuotaRef', $CuotaRef, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':Marca', $Marca, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':fecEmisionch', $fecEmisionch, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':paguesea', $paguesea, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':Impreso', $Impreso, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':dlicoint_aperturas', $dlicoint_aperturas, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':nro_operacion', $nro_operacion, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':FormadePag', $FormadePag, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CpbNormaIFRS', $CpbNormaIFRS, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':CpbNormaTrib', $CpbNormaTrib, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':RowKey', $RowKey, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':PartitionKey', $PartitionKey, PDO::PARAM_STR);
//         // $stmt_movim->bindValue(':NumDocRef1', $NumDocRef1, PDO::PARAM_STR);

//         // // Ejecutar la consulta
//         // $stmt_movim->execute();
//         // if ($stmt_movim->rowCount() > 0) {
//         //     //suma mas uno a cada registro
//         //     $cantidad_MovNum++;
//         //     // Confirmar la transacción
//         //     $conexion_db->commit();
//         //     //echo "Inserción exitosa en ambas tablas.";
//         //     return $response->withJson(["success" => "Inserción exitosa en ambas tablas."], 201);
//         // } else {
//         //     // Revertir la transacción en caso de error
//         //     $conexion_db->rollBack();
//         //     return $response->withJson(["error" => "Error al insertar en cwmovim."], 400);
//         // }

//     } else {
//         return $response->withJson(["error" => "No se pudo insertar el registro."], 400);
//     }
// } catch (PDOException $e) {
//     // Revertir la transacción en caso de error
//     $conexion_db->rollBack();
//     return $response->withJson(["error" => "Error:" . $e->getMessage()], 500);
// }
?>