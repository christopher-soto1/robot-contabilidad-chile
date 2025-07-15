<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once 'marcotest.php';
require_once __DIR__ . '/config/config.php';
require 'vendor/autoload.php';
set_time_limit(300); // aumenta el límite a 5 minutos (300 segundos)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function logAJson($cuenta, $fechaRuta)
{
    $rutaJson = "C:\\xampp\\htdocs\\robot-contabilidad-chile\\robotphp\\logs\\banco_chile\\$fechaRuta\\datos_$cuenta.json";

    if (!file_exists($rutaJson)) {
        return [];
    }

    $contenido = file_get_contents($rutaJson);
    $datos = json_decode($contenido, true);

    if (!is_array($datos)) {
        return [];
    }

    // Detecta si el archivo ya tiene clave "transacciones"
    $transacciones = isset($datos['transacciones']) ? $datos['transacciones'] : $datos;

    // Agrega la cuenta a cada transacción
    foreach ($transacciones as &$t) {
        $t['cuenta'] = $cuenta;
    }

    return $transacciones;
}

function enviarCorreo()
{
    try {
        $mail = new PHPMailer(true);
        $pwcorreo = constant('PWCORREO');
        $namecorreo = constant('CORREO');

        $mail->isSMTP();
        $mail->Host = 'mail.iopa.cl';
        $mail->SMTPAuth = true;
        $mail->Username = $namecorreo;
        $mail->Password = $pwcorreo;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom('soporte@iopa.cl', 'Registro de Ingresos - Banco de Chile');

        $mail->addAddress('christopher.soto@iopa.cl', 'Christopher Soto');
        #$mail->addAddress('maria.gaete@iopa.cl', 'Maria Gaete');
        #$mail->addAddress('marcos.huenchunir@iopa.cl', 'Marcos Huenchuñir');

        $mail->Subject = 'Informe - Banco de Chile - Ingresos Softland - ' . date('d-m-Y');
        $mail->isHTML(true);

        $fullReportBodyContent = '';
        $cuentas_a_reportar = ["33-05", "80-06"];

        foreach ($cuentas_a_reportar as $index => $cuenta) {
            $displayCuenta = $cuenta;
            if ($cuenta === "33-05") $displayCuenta = "00-178-02933-05";
            if ($cuenta === "80-06") $displayCuenta = "00-178-00980-06";

            $fechaRuta = date("d-m");
            $logFilePath = "C:/xampp/htdocs/robot-contabilidad-chile/robotphp/logs/banco_chile/{$fechaRuta}/log_{$cuenta}.log"; 
            $jsonFilePath = "C:/xampp/htdocs/robot-contabilidad-chile/robotphp/logs/banco_chile/{$fechaRuta}/datos_{$cuenta}.json"; 

            $emailHeaderInfo = ''; 
            $emailTableHtml = '<p style="margin-top: 10px; color: #888;">No se encontraron datos de transacciones para mostrar en la tabla.</p>';
            $combinedReportSummary = ''; 

            if (file_exists($logFilePath)) {
                $logContentFull = file_get_contents($logFilePath);

                if (preg_match('/(\*{2,}.*?Ejecución de robot.*?\nClínica:.*?\nFecha y hora:.*?\nRobot:.*?\nCuenta procesada \(Nro\. Cuenta Bancaria - últimos 4 dígitos\):.*?\n)/s', $logContentFull, $matches)) {
                    $emailHeaderInfo = htmlspecialchars($matches[1]);
                    $emailHeaderInfo = str_replace(htmlspecialchars($cuenta), htmlspecialchars($displayCuenta), $emailHeaderInfo);
                } else {
                    $emailHeaderInfo = '<p style="color: orange; font-size: 13px;">Información de encabezado no encontrada en el log para cuenta ' . htmlspecialchars($displayCuenta) . '.</p>';
                }

                if (preg_match('/Total de ingresos: (.*?)\n/s', $logContentFull, $matches)) {
                    $sumatoriaOnly = htmlspecialchars($matches[0]);
                    $combinedReportSummary .= '<p style="font-weight: bold; margin-top: 10px; font-size: 13px;">' . $sumatoriaOnly . '</p>';
                }

                if (preg_match('/Fecha del registro:\s*(.*?)\nNro\. Cuenta Procesada:\s*(.*?)\nNúmero de Comprobante:\s*(.*?)\nMonto Total de Depósito:\s*(.*?)\n/s', $logContentFull, $apiDetailsMatches)) {
                    $processedAccountSoftland = htmlspecialchars(trim($apiDetailsMatches[2]));
                    if ($cuenta === "33-05" && $processedAccountSoftland === "1-01-01-003") $processedAccountSoftland = $displayCuenta;
                    if ($cuenta === "80-06" && $processedAccountSoftland === "1-01-01-007") $processedAccountSoftland = $displayCuenta;

                    $combinedReportSummary .= '<div style="background-color: #f0f8ff; padding: 8px 12px; border-left: 3px solid #6cb6ff; border-radius: 4px; margin-top: 5px; margin-bottom: 5px;">';
                    $combinedReportSummary .= '<p style="font-size: 12px; margin: 2px 0;"><strong>Fecha del registro:</strong> ' . htmlspecialchars(trim($apiDetailsMatches[1])) . '</p>';
                    $combinedReportSummary .= '<p style="font-size: 12px; margin: 2px 0;"><strong>Nro. Cuenta Procesada:</strong> ' . $processedAccountSoftland . '</p>';
                    $combinedReportSummary .= '<p style="font-size: 12px; margin: 2px 0;"><strong>Número de Comprobante:</strong> ' . htmlspecialchars(trim($apiDetailsMatches[3])) . '</p>';
                    $combinedReportSummary .= '<p style="font-size: 12px; margin: 2px 0;"><strong>Monto Total de Depósito:</strong> ' . htmlspecialchars(trim($apiDetailsMatches[4])) . '</p>';
                    $combinedReportSummary .= '</div>';
                }
            }

            if (file_exists($jsonFilePath)) {
                $jsonContent = file_get_contents($jsonFilePath);
                $transactions = json_decode($jsonContent, true);

                if (is_array($transactions) && !empty($transactions) && !isset($transactions['mensaje'])) {
                    $emailTableHtml = '<table style="font-family: Arial, sans-serif; width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px;">';
                    $emailTableHtml .= '<tr style="background-color: #e9ecef;">';
                    $emailTableHtml .= '<th style="border: 1px solid #ddd; padding: 4px 6px; text-align: left; white-space: nowrap; font-weight: bold;">N°</th>';

                    if (!empty($transactions)) {
                        $firstRow = $transactions[0];
                        foreach ($firstRow as $key => $value) {
                            $displayName = ucwords(str_replace('_', ' ', $key));
                            $emailTableHtml .= '<th style="border: 1px solid #ddd; padding: 4px 6px; text-align: left; white-space: nowrap; font-weight: bold;">' . htmlspecialchars($displayName) . '</th>';
                        }
                    }
                    $emailTableHtml .= '</tr>';

                    $rowIndex = 1;
                    foreach ($transactions as $row) {
                        $emailTableHtml .= '<tr style="line-height: 1.2;">';
                        $emailTableHtml .= '<td style="border: 1px solid #ddd; padding: 4px 6px; text-align: center; font-weight: bold; background-color: #f5f5f5;">' . $rowIndex++ . '</td>';
                        foreach ($row as $key => $value) {
                            // Detectar si la clave actual es docto._nro.
                            $isDoctoNro = strtolower(trim($key)) === 'docto._nro.';

                            if (is_numeric($value) && !$isDoctoNro) {
                                $value = number_format($value, 0, ',', '.');
                            }

                            if ($key === 'cuenta') {
                                $emailTableHtml .= '<td style="border: 1px solid #ddd; padding: 4px 6px; white-space: nowrap;">' . htmlspecialchars($displayCuenta) . '</td>';
                            } else {
                                $emailTableHtml .= '<td style="border: 1px solid #ddd; padding: 4px 6px; white-space: nowrap;">' . htmlspecialchars($value) . '</td>';
                            }
                        }
                        $emailTableHtml .= '</tr>';
                    }
                    $emailTableHtml .= '</table>';
                }

            }

            $fullReportBodyContent .= "
                <div style='margin-bottom: 30px; padding: 15px; border: 1px solid #f0f0f0; border-radius: 8px;'>
                    <h2 style='font-size: 18px; color: #0056b3;'>Informe para Cuenta Bancaria: <strong>{$displayCuenta}</strong></h2>
                    {$emailTableHtml}
                    {$combinedReportSummary}
                </div>";
        }

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6;'>
                <p><strong>Estimado equipo de Contabilidad,</strong></p>
                <p>Este es un informe automático diario generado por el robot <strong>R_DPT_CHILE</strong> con el detalle de las transacciones y la información de registro en Softland, provenientes del <strong>Banco de Chile</strong>.</p>
                <hr style='border: none; border-top: 1px solid #ccc; margin: 20px 0;'>
                {$fullReportBodyContent}
                <hr style='border: none; border-top: 1px solid #ccc; margin: 30px 0;'>
                <p style='font-size: 13px; color: #999;'>Atentamente,<br><strong>Robot R_DPT_CHILE</strong><br>Equipo de Desarrollo y Automatización IOPA</p>
            </div>";

        if ($mail->send()) {
            echo "<pre>✅ Correo enviado correctamente para las cuentas: " . implode(', ', $cuentas_a_reportar) . "</pre>";
        } else {
            echo "<pre>❌ Error al enviar correo para las cuentas: " . implode(', ', $cuentas_a_reportar) . "</pre>";
        }

        return true;

    } catch (Exception $e) {
        return 'Excepción al enviar correo consolidado: ' . $e->getMessage();
    }
}


function main()
{

    set_time_limit(500); // aumenta el límite a 5 minutos (300 segundos)
    $fechaRuta = date("d-m");
    $cuentas = ["33-05", "80-06"]; // Cuentas usadas para leer los JSON y generar los logs iniciales
    $dataPorCuenta = [];

    # ---------------------- ESTAS LINEAS EJECUTAN EL ARCHIVO DE PYTHON ----------------------

    $python = 'C:\Users\programadorll\AppData\Local\Programs\Python\Python312\python.exe';
    #$script = 'C:\Users\programadorll\Desktop\robotpy\test.py'; #Ruta antigua antes de unificacion
    $script = 'C:\xampp\htdocs\robot-contabilidad-chile\robotpy\test.py';
    $output = shell_exec("\"$python\" \"$script\" 2>&1");
    echo "<pre>$output</pre>";
    
    # ---------------------- ESTAS LINEAS EJECUTAN EL ARCHIVO DE PYTHON ----------------------

    // Asumimos que esta carpeta y los archivos log_*.log iniciales ya existen.
    $destinoWebLogsPhp = "C:\\xampp\\htdocs\\robot-contabilidad-chile\\robotphp\\logs\\banco_chile\\{$fechaRuta}";

    // Mapeo de la 'cuenta' devuelta por la API PHP (ej. '1-01-01-003')
    // al sufijo del nombre del archivo de log deseado (ej. '33-05')
    $accountLogFilenameMap = [
        "1-01-01-003" => "33-05", // La API devuelve este ID, adjuntamos al log_33-05.log
        "1-01-01-007" => "80-06",  // La API devuelve este ID, adjuntamos al log_80-06.log
        // Añade aquí otros mapeos si tu API devuelve diferentes IDs de cuenta
    ];

    foreach ($cuentas as $cuenta) {
        $transacciones = logAJson($cuenta, $fechaRuta);
        if (!empty($transacciones)) {
            // Asegúrate de que la estructura de $dataPorCuenta es la que la API espera
            // (ej. '33-05' => [transacciones], '80-06' => [transacciones])
            $dataPorCuenta[$cuenta] = $transacciones;
        }
    }

    // Llama a la API PHP Slim
    $apiRespuesta = llamarApi("registrar_deposito", "POST", $dataPorCuenta);
    /* echo "<pre>";
    var_dump($apiRespuesta);
    echo "</pre>"; */

    // --- INICIO DE LA LÓGICA PARA ADJUNTAR LA RESPUESTA DE LA API AL LOG ---
    if ($apiRespuesta && isset($apiRespuesta['banco_de_chile']) && is_array($apiRespuesta['banco_de_chile'])) {
        foreach ($apiRespuesta['banco_de_chile'] as $accountEntry) {
            $apiCuentaId = $accountEntry['cuenta'] ?? null; // ID de cuenta de la respuesta de la API (ej. '1-01-01-003')

            if ($apiCuentaId && isset($accountLogFilenameMap[$apiCuentaId])) {
                $logFilenameSuffix = $accountLogFilenameMap[$apiCuentaId]; // Obtener el sufijo del nombre del log (ej. '33-05')
                $rutaLogCuenta = "{$destinoWebLogsPhp}\\log_{$logFilenameSuffix}.log"; // Ruta completa al archivo de log

                $logContentAppend = "\n\n"; // Añadir saltos de línea para separar del contenido anterior
                $logContentAppend .= "************************************************\n";
                $logContentAppend .= "** Información de Comprobante Softland **\n";
                $logContentAppend .= "************************************************\n";
                $logContentAppend .= "Fecha del registro: " . (new DateTime())->format('d-m-Y') . "\n";
                $logContentAppend .= "Nro. Cuenta Procesada: {$accountEntry['cuenta']}\n";
                $logContentAppend .= "Número de Comprobante: {$accountEntry['numero_comprobante']}\n";

                // Formatear el monto total con punto como separador de miles
                $montoFormateado = number_format($accountEntry['monto_total_deposito'], 0, ',', '.');
                $logContentAppend .= "Monto Total de Depósito: {$montoFormateado}\n";

                // Verificar si el campo 'insertado_anticipo_cliente' existe
                if (isset($accountEntry['insertado_anticipo_cliente'])) {
                    $anticipoStatus = $accountEntry['insertado_anticipo_cliente'] ? 'Sí' : 'No';
                    $logContentAppend .= "Anticipo Cliente Insertado: {$anticipoStatus}\n";
                }
                $logContentAppend .= "************************************************\n";

                

                try {
                    // Abrir el archivo en modo 'a' (append) para añadir contenido
                    file_put_contents($rutaLogCuenta, $logContentAppend, FILE_APPEND);
                    error_log("main.php: Información de API para cuenta {$apiCuentaId} añadida a log en {$rutaLogCuenta}");
                } catch (Exception $e) {
                    error_log("main.php: Error al añadir información de API al log para {$apiCuentaId} ({$rutaLogCuenta}): " . $e->getMessage());
                }
            } else {
                error_log("main.php: No se encontró mapeo para la cuenta API '{$apiCuentaId}'. No se adjuntará información al log.");
            }
        }
    } else {
        echo("main.php: No se recibieron datos válidos de 'banco_de_chile' de la API PHP.");
    }
    // --- FIN DE LA LÓGICA PARA ADJUNTAR LA RESPUESTA DE LA API AL LOG ---

    $correoRespuesta = enviarCorreo();

    echo "<pre>";
    print_r($apiRespuesta);
    echo "</pre>";

    echo "<pre>";
    print_r($correoRespuesta);
    echo "</pre>";
    exit();
}

main();
//enviarCorreo();
?>