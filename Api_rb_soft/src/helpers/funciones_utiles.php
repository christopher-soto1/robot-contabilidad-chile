<?php

function recorrer_array_limpiar_cadena($array) {
    foreach ($array as &$valor) {
        if (is_array($valor)) {
            $valor = recorrer_array_limpiar_cadena($valor); // Si el valor es un arreglo, aplica la función de nuevo
        } else {

            $valor = limpiar_cadena($valor); // Aplica limpiar_cadena al valor
        }
    }
    return $array;
}

function limpiar_cadena($cadena){

    $cadena = utf8_encode($cadena);//convierte una cadena de texto que está en una codificación de caracteres diferente a UTF-8 en una cadena codificada en UTF-8
    $cadena = trim($cadena);  //elimina los espacios en blanco y otros caracteres de control del principio y el final de una cadena de texto. 
                            //Estos caracteres incluyen: Espacios en blanco, Saltos de línea(\n), Retornos de carro (\r), Nulos, tabulador(\t) y otros caracteres de control
      
    // Quitar tildes y ń (provenientes de BD que traen problemas)
    $cadena = str_replace(
    array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
    array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
    $cadena
    );

    $cadena = str_replace(
    array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
    array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
    $cadena );

    $cadena = str_replace(
    array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
    array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
    $cadena );

    $cadena = str_replace(
    array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
    array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
    $cadena );

    $cadena = str_replace(
    array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
    array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
    $cadena );

    $cadena = str_replace(
    array('ñ', 'Ñ', 'ç', 'Ç'),
    array('n', 'N', 'c', 'C'),
    $cadena
    );
    

    $cadena = strtoupper($cadena); //Convertir a mayusculas la cadena
  
    return $cadena;
}
