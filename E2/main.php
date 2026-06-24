<?php

 #quitamos tildes como por ejemplo para comunas que pueden hacer error al compararlas
function quitar_tildes($texto) {
    $con_tilde = ["á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "Ñ"];
    $sin_tilde = ["a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N"];
    return str_replace($con_tilde, $sin_tilde, $texto);
}

#calculamos el Digito verificador
function calcularDV($numero) {
    $suma = 0;
    $multiplicador = 2;
    $largo = strlen($numero);
    
    for ($i = $largo - 1; $i >= 0; $i--) {
        $suma = $suma + ($numero[$i] * $multiplicador);
        $multiplicador = $multiplicador + 1;
        if ($multiplicador > 7) {
            $multiplicador = 2;
        }
    }
    
    $resto = $suma % 11;
    $dv_calculado = 11 - $resto;
    
    if ($dv_calculado == 11) {
        return "0";
    } elseif ($dv_calculado == 10) {
        return "K";
    } else {
        return (string)$dv_calculado;
    }
}
# valida si el el digito verificador es el mismo que el de calcularDV()
function validarRUN($run) {
    $run = trim($run);
    $run = str_replace(".", "", $run); # quitamos puntos si esque tienen
    $partes = explode("-", $run);
    
    if (count($partes) != 2) {
        return false;
    }
    
    $numero = $partes[0];
    $dv_ingresado = strtoupper($partes[1]);
    
    #ocupamos la funcion aanterior
    $dv_calculado = calcularDV($numero);
    
    if ($dv_ingresado === $dv_calculado) {
        return true;
    } else {
        return false;
    }
}
# Cambiamos el DV si esta incorrecto
function arreglarRUN($run) {
    $run = trim($run);
    if ($run == "") return "";
    $run = str_replace(".", "", $run);
    $partes = explode("-", $run);
    if (count($partes) != 2) {
        return $run; # No se puede arreglar si le falta el guion
    }
    $numero = $partes[0];
    $dv_correcto = calcularDV($numero);
    return $numero . "-" . $dv_correcto;
}

# funcion corregir fechas
function arreglar_fecha($fecha) {
    $fecha = trim($fecha);
    if ($fecha == "") {
        return "";
    }
    
   # Si la fecha viene como un año negativo como  '-1990'
   # vemos si este es negativo con 4 digitos y ponemos 01-01 al final 
    if (preg_match('/^-(\d{4})$/', $fecha, $matches)) {
        return $matches[1] . "-01-01";
    }
    
   # Limpieza de distintos separadores 
    $fecha = str_replace("/", "-", $fecha);
    $fecha = str_replace(".", "-", $fecha);

    $pedazos_espacio = explode(" ", $fecha);
    $solo_fecha = $pedazos_espacio[0];
    $hora = "";
    
    if (count($pedazos_espacio) > 1) {
        $hora = " " . $pedazos_espacio[1];
    }

    $partes = explode("-", $solo_fecha);
    if (count($partes) == 3) {
        $p1 = $partes[0];
        $p2 = $partes[1];
        $p3 = $partes[2];
        
        # Si ya está en YYYY-MM-DD, no lo invertimos
        if (strlen($p1) == 4) {
            $resultado = $p1 . "-" . str_pad($p2, 2, "0", STR_PAD_LEFT) . "-" . str_pad($p3, 2, "0", STR_PAD_LEFT) . $hora;
        } else {
            # Si está en DD-MM-YYYY
            $dia = $p1;
            $mes = $p2;
            $anio = $p3;
            
            # si el año tiene 2 digitos lo cambiamos a 4
            if (strlen($anio) == 2) {
                if ((int)$anio > 26) {
                    $anio = "19" . $anio;
                } else {
                    $anio = "20" . $anio;
                }
            }
            $resultado = $anio . "-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-" . str_pad($dia, 2, "0", STR_PAD_LEFT) . $hora;
        }
    } elseif (count($partes) == 2) {
        #  fechas incompletas como 2024-11
        $p1 = $partes[0];
        $p2 = $partes[1];
        if (strlen($p1) == 4) {
            $resultado = $p1 . "-" . str_pad($p2, 2, "0", STR_PAD_LEFT) . "-01" . $hora;
        } else {
            $resultado = "20" . $p2 . "-" . str_pad($p1, 2, "0", STR_PAD_LEFT) . "-01" . $hora;
        }
    } else {
        $resultado = $fecha;
    }

    # Validacion final de "principiante" para atajar fechas sucias (2025-02-31, 2025-05-00, -10-10, letras, etc.)
    $solo_fecha_res = explode(" ", trim($resultado))[0];
    $partes_res = explode("-", $solo_fecha_res);
    
    if (count($partes_res) == 3 && is_numeric($partes_res[0]) && is_numeric($partes_res[1]) && is_numeric($partes_res[2])) {
        $anio_f = (int)$partes_res[0];
        $mes_f = (int)$partes_res[1];
        $dia_f = (int)$partes_res[2];
        
        # checkdate valida si la fecha existe en el calendario real
        if (!checkdate($mes_f, $dia_f, $anio_f)) {
            # Si no existe, entregamos una fecha por defecto para no romper la BD
            return "1900-01-01"; 
        }
    } else {
        # Si tiene letras, o no son 3 partes, etc.
        return "1900-01-01";
    }

    return $resultado;
}
 # arreglamos el precio texto de los csv 
function arreglar_precio_texto($precio) {
    $precio_limpio = trim($precio);
    $precio_sin_puntos = str_replace(".", "", $precio_limpio);
    if (is_numeric($precio_sin_puntos)) {
        return $precio_sin_puntos;
    }

    $precio_minuscula = strtolower($precio_limpio);
    # vi que en el csv hay precios como 'nueve mil'
    $diccionario_precios = [
        "nueve mil" => "9000",
        "diez mil" => "10000",
        "quince mil" => "15000",
        "veinte mil" => "20000"
    ];
    # si esta en el diccionario lo devolvemos
    if (isset($diccionario_precios[$precio_minuscula])) {
        return $diccionario_precios[$precio_minuscula];
    }
    
    return $precio_limpio; 
}


# diccionario con comunas y regiones para verificar

$diccionario_codigo_region = [];
$diccionario_nombre_region = [];

if (file_exists("regiones_comunas/regiones_comunas.csv")) {
    $archivo_regiones = fopen("regiones_comunas/regiones_comunas.csv", "r");
    fgetcsv($archivo_regiones, 0, ";");
    
    while (($linea = fgetcsv($archivo_regiones, 0, ";")) !== false) {
        $comuna_dicc = trim($linea[1]);
        $codigo_dicc = trim($linea[2]);
        $region_dicc = trim($linea[3]);
        
        $llave_comuna = strtolower(quitar_tildes($comuna_dicc));
        
        $diccionario_codigo_region[$llave_comuna] = $codigo_dicc;
        $diccionario_nombre_region[$llave_comuna] = quitar_tildes($region_dicc);
    }
    fclose($archivo_regiones);
}


#procesamos los archivos
$archivos_a_procesar = [
    "personas_socios.csv", 
    "sucursales_lugares.csv", 
    "reservas_arriendos.csv", 
    "eventos.csv", 
    "pagos_membresias.csv",
    "cargos_administrativos.csv"
];

$runs_validos_globales = [];

# procesamos los archivos
foreach ($archivos_a_procesar as $nombre_archivo) {
    
    $carpeta = str_replace(".csv", "", $nombre_archivo);
    $ruta_archivo = $carpeta . "/" . $nombre_archivo;
    
    $nombre_base = $carpeta . "/" . str_replace(".csv", "", $nombre_archivo);
    # abrimos los archivos 
    $archivo_in = fopen($ruta_archivo, "r");
    $archivo_ok = fopen($nombre_base . "OK.csv", "w");
    $archivo_err = fopen($nombre_base . "ERR.csv", "w");
    $archivo_log = fopen($nombre_base . "LOG.csv", "w");

    # Ponemos la cabecera para el LOG
    fputcsv($archivo_log, ["Fila", "Atributo", "Valor_Original", "Valor_Nuevo", "Justificacion"], ";");

    $cabecera = fgetcsv($archivo_in, 0, ";");
    fputcsv($archivo_ok, $cabecera, ";");
    fputcsv($archivo_err, $cabecera, ";");

    # empezamos de la segunda fila por la cabeceera
    $numero_fila = 2;

    while (($fila = fgetcsv($archivo_in, 0, ";")) !== false) {
        if (count($fila) < 2) continue; 

        #transformamos a UTF-8 
        foreach ($fila as &$valor) {
            if (!preg_match('//u', $valor)) {
                $valor = utf8_encode($valor);
            }
        }
        #guardar errores
        $errores_fila = []; 
        $identificador_para_log = ""; 

        #  para personas_socios.csv

        if ($nombre_archivo == "personas_socios.csv") {
            $identificador_para_log = trim($fila[0]);
            
            $columnas_obligatorias = [0, 1, 6, 7, 8, 9, 15, 18];
            for ($i = 0; $i < count($columnas_obligatorias); $i = $i + 1) {
                $col = $columnas_obligatorias[$i];
                if (trim($fila[$col]) == "") array_push($errores_fila, "Falta dato obligatorio: " . $cabecera[$col]);
            } # confirmamos si el run existe y es valido
            if (trim($fila[0]) != "" && !validarRUN($fila[0])) {
                $run_arreglado = arreglarRUN($fila[0]);
                fputcsv($archivo_log, [$numero_fila, "run", trim($fila[0]), $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[0] = $run_arreglado;
            }
            # revisamos el mail 
            $email = trim($fila[2]);
            if ($email != "") {
                $email_arreglado = str_replace("..", ".", $email);
                if (!filter_var($email_arreglado, FILTER_VALIDATE_EMAIL)) array_push($errores_fila, "Email mal formato");
                elseif ($email != $email_arreglado) {
                    fputcsv($archivo_log, [$numero_fila, "email", $email, $email_arreglado, "Quitar punto"], ";");
                    $fila[2] = $email_arreglado;
                }
            }
            # columnas donde estan los telefonos 
            $columnas_telefonos = [3, 4];
            foreach ($columnas_telefonos as $col_tel) {
                $tel = trim($fila[$col_tel]);
                if ($tel != "") {
                    $tel = str_replace(".0", "", $tel);
                    if (strlen($tel) == 8) {
                        $fila[$col_tel] = "9" . $tel;
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col_tel], trim($fila[$col_tel]), $fila[$col_tel], "Se agrego 9"], ";");
                    } elseif (strlen($tel) != 9) {
                        $fila[$col_tel] = "100000000";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col_tel], trim($fila[$col_tel]), $fila[$col_tel], "Dato malo, cambiado a 100000000"], ";");
                    } else {
                        $fila[$col_tel] = $tel;
                    }
                }
            }
            # revisamos parentesco
            $parentesco = trim($fila[11]);
            if ($parentesco != "") {
                $parentesco_arreglado = str_replace("c—nyuge", "conyuge", $parentesco);
                if ($parentesco != $parentesco_arreglado) {
                    fputcsv($archivo_log, [$numero_fila, "parentesco", $parentesco, $parentesco_arreglado, "Arreglo error tipeo"], ";");
                    $fila[11] = $parentesco_arreglado;
                }
            }
            # Columnas donde hay fechas            
            $columnas_fechas = [12, 13, 14];
            foreach ($columnas_fechas as $col_fecha) {
                $fecha = trim($fila[$col_fecha]);
                if ($fecha != "") {
                    $fecha_nueva = arreglar_fecha($fecha);
                    if ($fecha != $fecha_nueva) {
                        $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Cambio a YYYY-MM-DD";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col_fecha], $fecha, $fecha_nueva, $justificacion_log], ";");
                        $fila[$col_fecha] = $fecha_nueva;
                    }
                }
            }
            # revisamos comuna y region 
            $comuna_datos = trim($fila[6]);
            if ($comuna_datos != "") {
                $llave_buscar = strtolower(quitar_tildes($comuna_datos));
                if (array_key_exists($llave_buscar, $diccionario_codigo_region)) {
                    $codigo_real = $diccionario_codigo_region[$llave_buscar];
                    $nombre_real = $diccionario_nombre_region[$llave_buscar];
                    
                    if (trim($fila[7]) != $codigo_real) {
                        fputcsv($archivo_log, [$numero_fila, "region_codigo", trim($fila[7]), $codigo_real, "Corregido por diccionario"], ";");
                        $fila[7] = $codigo_real;
                    }
                    if (strtolower(quitar_tildes(trim($fila[8]))) != strtolower($nombre_real)) {
                        fputcsv($archivo_log, [$numero_fila, "region_nombre", trim($fila[8]), $nombre_real, "Corregido por diccionario"], ";");
                        $fila[8] = $nombre_real;
                    }
                } else {
                    array_push($errores_fila, "Comuna no oficial");
                }
            }

            $tipo = trim($fila[9]);
            $run_titular = trim($fila[10]);
            
            # Si es socio_titular u otro tipo de socio, la fecha_inicio es obligatoria
            if (in_array($tipo, ["socio_titular", "adicional", "beneficiario", "invitado"]) && trim($fila[13]) == "") {
                array_push($errores_fila, "Falta fecha_inicio para el socio ");
            }
            
            # Para la tabla membresia, fecha_fin es obligatoria si es socio_titular
            if ($tipo == "socio_titular" && trim($fila[14]) == "") {
                array_push($errores_fila, "Falta fecha_fin para el socio titular");
            }

            # Validar pertenencia de run titular
            if ($tipo == "socio_titular" && $run_titular != "") {
                array_push($errores_fila, "Socio titular no debe tener RUN asociado");
            }
            if ($tipo != "socio_titular" && in_array($tipo, ["adicional", "beneficiario", "invitado"]) && $run_titular == "") {
                array_push($errores_fila, "Socio dependiente debe tener RUN titular asociado");
            }
            if ($run_titular != "" && !validarRUN($run_titular)) {
                $run_arreglado = arreglarRUN($run_titular);
                fputcsv($archivo_log, [$numero_fila, "run_titular_asociado", $run_titular, $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[10] = $run_arreglado;
            }

            # validar los NOT NULL de usuarios
            if (trim($fila[15]) == "SI") {
                if (trim($fila[2]) == "") array_push($errores_fila, "Falta email_login para el usuario");
                if (trim($fila[16]) == "") array_push($errores_fila, "Falta tipo_usuario para el usuario");
                if (trim($fila[17]) == "") array_push($errores_fila, "Falta clave para el usuario");
            } elseif (trim($fila[15]) == "NO" && trim($fila[16]) != "") {
                array_push($errores_fila, "No es usuario pero tiene rol");
            }
        }

        # Para SUCURSALES_LUGARES.CSV

        if ($nombre_archivo == "sucursales_lugares.csv") {
            # Normalizar lugar_nombre (col 3) y sucursal_nombre (col 0) para evitar Ñ en codigos
            $fila[0] = quitar_tildes(trim($fila[0])); # sucursal_nombre
            $fila[3] = quitar_tildes(trim($fila[3])); # lugar_nombre
            $identificador_para_log = $fila[3]; 
            # tipo_precio es obligatorio
            $columnas_obligatorias = [0, 1, 2, 3, 4, 5, 6, 8];
            foreach ($columnas_obligatorias as $col) {
                if (trim($fila[$col]) == "") array_push($errores_fila, "Falta dato obligatorio: " . $cabecera[$col]);
            }
            #vemos comunas
            $comuna_datos = trim($fila[2]);
            if ($comuna_datos != "") {
                if (!array_key_exists(strtolower(quitar_tildes($comuna_datos)), $diccionario_codigo_region)) {
                    array_push($errores_fila, "Comuna no oficial");
                }
            }
            #revisamos precio
            $precio = trim($fila[6]);
            if ($precio != "") {
                $precio_arreglado = arreglar_precio_texto($precio);
                if ($precio != $precio_arreglado) {
                    fputcsv($archivo_log, [$numero_fila, "precio", $precio, $precio_arreglado, "Texto/Formato a numero"], ";");
                    $fila[6] = $precio_arreglado;
                }
                if (!is_numeric($fila[6])) array_push($errores_fila, "Precio invalido");
            }
            
            #arreglamos fechas
            foreach ([12, 13] as $col) {
                if (isset($fila[$col]) && trim($fila[$col]) != "") {
                    $fecha = trim($fila[$col]);
                    $fecha_nueva = arreglar_fecha($fecha);
                    if ($fecha != $fecha_nueva) {
                        $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Cambio a YYYY-MM-DD";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col], $fecha, $fecha_nueva, $justificacion_log], ";");
                        $fila[$col] = $fecha_nueva;
                    }
                }
            }
        }

        # Para RESERVAS_ARRIENDOS.CSV

        if ($nombre_archivo == "reservas_arriendos.csv") {
            # como hay error por como esta escrito cabaña (Cabaa) reconstruyo la palabra si esque empieza con "caba"
            $lugar_tmp = trim($fila[8]);
            if (stripos($lugar_tmp, 'caba') === 0) {
                # Extraemos  el numero y sufijo al final
                preg_match('/(\d+.*)$/i', $lugar_tmp, $m_lugar);
                $sufijo_lugar = isset($m_lugar[0]) ? trim($m_lugar[0]) : '';
                $fila[8] = 'Cabana' . ($sufijo_lugar !== '' ? ' ' . $sufijo_lugar : '');
            } else {
                $fila[8] = quitar_tildes($lugar_tmp); # eslugar_nombre normal
            }
            $fila[9] = quitar_tildes(trim($fila[9])); # es sucursal_nombre
            $identificador_para_log = trim($fila[5]); 
            #datos no nulos
            $columnas_obligatorias = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            foreach ($columnas_obligatorias as $col) {
                if (trim($fila[$col]) == "") array_push($errores_fila, "Falta dato obligatorio: " . $cabecera[$col]);
            }
            #vemos rut
            if (trim($fila[5]) != "" && !validarRUN(trim($fila[5]))) {
                $run_arreglado = arreglarRUN(trim($fila[5]));
                fputcsv($archivo_log, [$numero_fila, "run", trim($fila[5]), $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[5] = $run_arreglado;
            }

            #revisamos fechas
            foreach ([1, 2, 3, 13] as $col) {
                if (isset($fila[$col]) && trim($fila[$col]) != "") {
                    $fecha = trim($fila[$col]);
                    $fecha_nueva = arreglar_fecha($fecha);
                    if ($fecha != $fecha_nueva) {
                        $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Formato fecha";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col], $fecha, $fecha_nueva, $justificacion_log], ";");
                        $fila[$col] = $fecha_nueva;
                    }
                }
            }

            # Fechas de pago obligatorias si hay pago.
            if (trim($fila[11]) != "" && is_numeric(arreglar_precio_texto(trim($fila[11]))) && arreglar_precio_texto(trim($fila[11])) > 0) {
                if (trim($fila[13]) == "") {
                    array_push($errores_fila, "Falta fecha de pago para la reserva pagada (NOT NULL en BD)");
                }
            }
            #vemos los montos
            foreach ([10, 11] as $col) {
                if (isset($fila[$col]) && trim($fila[$col]) != "") {
                    $monto_arr = arreglar_precio_texto(trim($fila[$col]));
                    if (trim($fila[$col]) != $monto_arr) {
                        $fila[$col] = $monto_arr;
                    }
                    if (!is_numeric($fila[$col])) array_push($errores_fila, "Monto no es numero");
                }
            }
        }

        # Para EVENTOS.CSV

        if ($nombre_archivo == "eventos.csv") {
            # evitamos la Ñ en codigos
            $fila[4] = quitar_tildes($fila[4]); # lugar_nombre
            $fila[5] = quitar_tildes(trim($fila[5])); # sucursal_nombre
            $identificador_para_log = trim($fila[7]); 
            
            # si es empresa no es obligatorio el run_cliente, pero si el rut_contacto_empresa
            $columnas_obligatorias = [0, 1, 3, 4, 5, 6, 13];
            foreach ($columnas_obligatorias as $col) {
                if (trim($fila[$col]) == "") array_push($errores_fila, "Falta dato obligatorio: " . $cabecera[$col]);
            }

            #validamos el tipo de cliente
            if (trim($fila[7]) == "" && trim($fila[9]) == "") {
                array_push($errores_fila, "Falta run_cliente o rut_contacto_empresa");
            }

            if (trim($fila[7]) != "" && !validarRUN(trim($fila[7]))) {
                $run_arreglado = arreglarRUN(trim($fila[7]));
                fputcsv($archivo_log, [$numero_fila, "run_titular", trim($fila[7]), $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[7] = $run_arreglado;
            }
            if (trim($fila[9]) != "" && !validarRUN(trim($fila[9]))) {
                $run_arreglado = arreglarRUN(trim($fila[9]));
                fputcsv($archivo_log, [$numero_fila, "run_contacto", trim($fila[9]), $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[9] = $run_arreglado;
            }
            #revisamos fechas
            foreach ([2, 3] as $col) {
                if (trim($fila[$col]) != "") {
                    $fecha = trim($fila[$col]);
                    $fecha_nueva = arreglar_fecha($fecha);
                    if ($fecha != $fecha_nueva) {
                        $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Formato fecha";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col], $fecha, $fecha_nueva, $justificacion_log], ";");
                        $fila[$col] = $fecha_nueva;
                    }
                }
            }
            #revisamos los montos
            foreach ([13, 14, 15] as $col) {
                if (trim($fila[$col]) != "") {
                    $monto = trim($fila[$col]);
                    $monto_arr = arreglar_precio_texto($monto);
                    if ($monto != $monto_arr) {
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col], $monto, $monto_arr, "Texto/Formato a numero"], ";");
                        $fila[$col] = $monto_arr;
                    }
                    if (!is_numeric($fila[$col])) array_push($errores_fila, "Monto invalido");
                }
            }
            #si hay pago de reserva, fecha de contratacion no puede ser vacia
            if (trim($fila[14]) != "" && trim($fila[14]) != "0" && trim($fila[2]) == "") {
                array_push($errores_fila, "Falta fecha_contratacion para pago de reserva (NOT NULL en BD)");
            }


        }

        # PARA PAGOS_MEMBRESIAS.CSV

        if ($nombre_archivo == "pagos_membresias.csv") {
            $identificador_para_log = trim($fila[1]); 
            #datos no nulos
            $columnas_obligatorias = [0, 1, 2, 3, 4, 5, 6, 8, 9];
            foreach ($columnas_obligatorias as $col) {
                if (trim($fila[$col]) == "") array_push($errores_fila, "Falta dato obligatorio: " . $cabecera[$col]);
            }
            #revisamos el run
            if (trim($fila[1]) != "" && !validarRUN(trim($fila[1]))) {
                $run_arreglado = arreglarRUN(trim($fila[1]));
                fputcsv($archivo_log, [$numero_fila, "run", trim($fila[1]), $run_arreglado, "Dígito verificador corregido"], ";");
                $fila[1] = $run_arreglado;
            }
            
            #revisamos las fechas
            foreach ([5, 10] as $col) {
                $fecha = trim($fila[$col]);
                if ($fecha != "") {
                    $fecha_nueva = arreglar_fecha($fecha);
                    if ($fecha != $fecha_nueva) {
                        $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Formato fecha";
                        fputcsv($archivo_log, [$numero_fila, $cabecera[$col], $fecha, $fecha_nueva, $justificacion_log], ";");
                        $fila[$col] = $fecha_nueva;
                    }
                }
            }

        # habia hecho una que veia si no es ni pagado o atrasado pero no cambia nada entonces lo borre
        }

        # PARA CARGOS_ADMINISTRATIVOS.CSV

        if ($nombre_archivo == "cargos_administrativos.csv") {
            $identificador_para_log = trim($fila[0]); 
            # verificamos obligatorios
            $columnas_obligatorias = [0, 1, 2, 3];
            for ($i = 0; $i < count($columnas_obligatorias); $i = $i + 1) {
                $columna = $columnas_obligatorias[$i];
                if (trim($fila[$columna]) == "") {
                    array_push($errores_fila, "Falta dato obligatorio en columna: " . $cabecera[$columna]);
                }
            }
            #revisamos el run
            $run_persona = trim($fila[0]);
            if ($run_persona != "") {
                if (validarRUN($run_persona) == false) {
                    $run_arreglado = arreglarRUN($run_persona);
                    fputcsv($archivo_log, [$numero_fila, "run", $run_persona, $run_arreglado, "Dígito verificador corregido"], ";");
                    $fila[0] = $run_arreglado;
                }
            }
            #revisamos las fechas
            $columnas_fechas = [3, 4];
            foreach ($columnas_fechas as $col_fecha) {
                if (isset($fila[$col_fecha])) {
                    $fecha = trim($fila[$col_fecha]);
                    if ($fecha != "") {
                        $fecha_nueva = arreglar_fecha($fecha);
                        if ($fecha != $fecha_nueva) {
                            $justificacion_log = (strpos($fecha, '-') === 0) ? "Se cambio la fecha" : "Formato fecha arreglado";
                            fputcsv($archivo_log, [$numero_fila, $cabecera[$col_fecha], $fecha, $fecha_nueva, $justificacion_log], ";");
                            $fila[$col_fecha] = $fecha_nueva;
                        }
                    }
                }
            }
        }


        # Guardamos los archivos 

        if (count($errores_fila) == 0) {
            fputcsv($archivo_ok, $fila, ";");            
            if ($nombre_archivo == "personas_socios.csv") {
                $runs_validos_globales[trim($fila[0])] = true;
            }
        } else {
            fputcsv($archivo_err, $fila, ";");
            $mensaje = implode(", ", $errores_fila);
            fputcsv($archivo_log, [$numero_fila, "FILA DESCARTADA", $identificador_para_log, "N/A", $mensaje], ";");
        }

        $numero_fila = $numero_fila + 1;
    }

    fclose($archivo_in);
    fclose($archivo_ok);
    fclose($archivo_err);
    fclose($archivo_log);
}

echo "Listoko";

?>
