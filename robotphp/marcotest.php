<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
function llamarApi($endpoint, $metodo = 'GET', $data = null) {
       
    $baseUrl ='http://localhost/robot-contabilidad-chile/Api_rb_soft/public/api/';
    //$baseUrl ='http://localhost/Api_rb_soft/public/';

    // Inicializar cURL
    $ch = curl_init();
    
    // Configurar la URL completa
    $url = $baseUrl . $endpoint;
    curl_setopt($ch, CURLOPT_URL, $url);
    
    // Configurar el método
    if (strtoupper($metodo) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true); // Definir que es una petición POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Datos en formato JSON
    } elseif (strtoupper($metodo) === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    } elseif (strtoupper($metodo) === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
        
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($metodo)); // Para otros métodos
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Enviar datos si no es NULL
        }
    }

    // Configurar opciones generales
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desactivar la verificación SSL
    
    // Headers
    $headers = [
        "Authorization: Bearer ef67c3bc52c879bf724afff06bcda380",
        "Content-Type: application/json"
    ];

    // Encabezados HTTP que serán enviados junto con la solicitud cURL para asegurar que la API reciba la información en el formato adecuado y con la autorización requerida.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Esta línea desactiva la verificación del certificado del servidor al que se está haciendo la solicitud.
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Ejecutar la solicitud
    // $response = curl_exec($ch);
    $response = curl_exec($ch);

    // Imprime la respuesta para ver el contenido
    //echo "<pre>";
    //var_dump('imprime curl');
    //var_dump($response);
    //echo "</pre>";
    //exit();

    // Manejar errores
    if ($response === false) {
        echo "Error: " . curl_error($ch);
        return null;
    } else {
        // Retornar la respuesta decodificada
        return json_decode($response, true);
    }

    // Cerrar cURL
    curl_close($ch);
}


function insert_pagoFacturas_rebsol($comprobante_softlan, $folios_pagados_softland,  $cod_prevision, $rut_banco, $cod_prevision_rb, $fecha_banco){
    try {
        // echo "<pre>";
        // var_dump('insert_pagoFacturas');
        // var_dump($data);
        // echo "</pre>";
        // exit();

        $data = [
            'comprobante' => $comprobante_softlan,
            'folios' => $folios_pagados_softland,
            'rut_prevision' => $cod_prevision,
            'rut_banco' => $rut_banco,
            'cod_prevision_rb' => $cod_prevision_rb,
            'fecha_banco' => $fecha_banco
        ];
        
        // Llamar a la API
        $resultado = llamarApi("pago_facturas_rebsol", "POST", $data);

        // Validar la respuesta
        if ($resultado && is_array($resultado) && !isset($resultado['error'])){
            return $resultado;
        } else {
            return [];
        }
    } catch (Exception $e){  
        return [];
    }
}


//FIN ACCESO API--------------------------------------------------------------------------------------------------------------------------------------------
?>