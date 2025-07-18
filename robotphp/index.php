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
    // -------------------- VALIDACION DE DIAS HABILES Y FINES DE SEMANA --------------------
    date_default_timezone_set('America/Santiago'); // Zona horaria CL

    // ⚙️ Simular una fecha específica (para debug). Descomenta para testear:
    // $debugFecha = '2025-07-19'; // sábado
    // $debugFecha = '2025-07-16'; // feriado miércoles
    // $debugFecha = '2025-07-17'; // jueves normal

    $timestamp = isset($debugFecha)
        ? strtotime($debugFecha)
        : time();

    $fechaHoy = date('d/m/Y', $timestamp);           // Para comparación con feriados
    $fechaNombreArchivo = date('d_m_Y', $timestamp); // Para nombre del archivo
    $diaSemana = (int)date('N', $timestamp);         // 1 (lunes) a 7 (domingo)
    $diasEsp = ["lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo"];
    $nombreDia = $diasEsp[$diaSemana - 1];

    // Lista de feriados Chile
    $feriados_chile = [
        '01/01/2025', '18/04/2025', '19/04/2025', '01/05/2025',
        '21/05/2025', '20/06/2025', '29/06/2025', '16/07/2025',
        '15/08/2025', '18/09/2025', '19/09/2025', '12/10/2025',
        '31/10/2025', '01/11/2025', '08/12/2025', '25/12/2025',
    ];

    $esFinde = ($diaSemana >= 6);
    $esFeriado = in_array($fechaHoy, $feriados_chile);

    if ($esFinde || $esFeriado) {
        $motivo = $esFinde ? "fin de semana" : "día feriado";
        $rutaLogFeriado = "C:\\xampp\\htdocs\\robot-contabilidad-chile\\robotphp\\logs\\dias_feriados\\log_{$fechaNombreArchivo}.txt";

        $contenidoLog = "El robot no se ejecutó porque es {$motivo}.\n";
        $contenidoLog .= "Fecha: {$fechaHoy}\n";
        $contenidoLog .= "Día: {$nombreDia}\n";
        $contenidoLog .= "Este robot solo se ejecuta de lunes a viernes en días hábiles.";

        file_put_contents($rutaLogFeriado, $contenidoLog);

        echo "⏹️ El proceso no se ejecuta porque hoy es {$motivo} ({$nombreDia}).\n";
        error_log("main.php: Ejecución detenida. Hoy es {$nombreDia} ({$fechaHoy}). Se creó log en {$rutaLogFeriado}.");
        return;
    }


    // -------------------- INICIO DEL CODIGO --------------------
    set_time_limit(500); // aumenta el límite a 5 minutos (300 segundos)
    $fechaRuta = date("d-m");
    $cuentas = ["33-05", "80-06"];
    $dataPorCuenta = [];

    # ---------------------- Ejecutar script Python ----------------------
    $python = 'C:\Users\programadorll\AppData\Local\Programs\Python\Python312\python.exe';
    $script = 'C:\xampp\htdocs\robot-contabilidad-chile\robotpy\test.py';
    $output = shell_exec("\"$python\" \"$script\" 2>&1");
    echo "<pre>$output</pre>";
    
    # ---------------------- Leer JSONs generados ----------------------
    $destinoWebLogsPhp = "C:\\xampp\\htdocs\\robot-contabilidad-chile\\robotphp\\logs\\banco_chile\\{$fechaRuta}";

    $accountLogFilenameMap = [
        "1-01-01-003" => "33-05",
        "1-01-01-007" => "80-06",
    ];

    foreach ($cuentas as $cuenta) {
        $transacciones = logAJson($cuenta, $fechaRuta);
        if (!empty($transacciones)) {
            $dataPorCuenta[$cuenta] = $transacciones;
        }
    }

    $apiRespuesta = llamarApi("registrar_deposito", "POST", $dataPorCuenta);

    // --- Agregar info al log por cuenta ---
    if ($apiRespuesta && isset($apiRespuesta['banco_de_chile']) && is_array($apiRespuesta['banco_de_chile'])) {
        foreach ($apiRespuesta['banco_de_chile'] as $accountEntry) {
            $apiCuentaId = $accountEntry['cuenta'] ?? null;

            if ($apiCuentaId && isset($accountLogFilenameMap[$apiCuentaId])) {
                $logFilenameSuffix = $accountLogFilenameMap[$apiCuentaId];
                $rutaLogCuenta = "{$destinoWebLogsPhp}\\log_{$logFilenameSuffix}.log";

                $logContentAppend = "\n\n";
                $logContentAppend .= "************************************************\n";
                $logContentAppend .= "** Información de Comprobante Softland **\n";
                $logContentAppend .= "************************************************\n";
                $logContentAppend .= "Fecha del registro: " . (new DateTime())->format('d-m-Y') . "\n";
                $logContentAppend .= "Nro. Cuenta Procesada: {$accountEntry['cuenta']}\n";
                $logContentAppend .= "Número de Comprobante: {$accountEntry['numero_comprobante']}\n";
                $montoFormateado = number_format($accountEntry['monto_total_deposito'], 0, ',', '.');
                $logContentAppend .= "Monto Total de Depósito: {$montoFormateado}\n";

                if (isset($accountEntry['insertado_anticipo_cliente'])) {
                    $anticipoStatus = $accountEntry['insertado_anticipo_cliente'] ? 'Sí' : 'No';
                    $logContentAppend .= "Anticipo Cliente Insertado: {$anticipoStatus}\n";
                }
                $logContentAppend .= "************************************************\n";

                try {
                    file_put_contents($rutaLogCuenta, $logContentAppend, FILE_APPEND);
                    error_log("main.php: Info API añadida a log de cuenta {$apiCuentaId} ({$rutaLogCuenta})");
                } catch (Exception $e) {
                    error_log("main.php: Error al escribir log para cuenta {$apiCuentaId}: " . $e->getMessage());
                }
            } else {
                error_log("main.php: Cuenta API '{$apiCuentaId}' sin mapeo en \$accountLogFilenameMap.");
            }
        }
    } else {
        echo("main.php: No se recibieron datos válidos de 'banco_de_chile' de la API.");
    }

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