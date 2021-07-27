<?php

class Archivos
{
    //Retorno un array vacio o con datos
    public static function CargarTxt($file_name)
    {
        $datos = [];

        if (file_exists($file_name) && filesize($file_name) > 0) {

            $file = fopen($file_name, "r");

            $data = fread($file, filesize($file_name));

            $datos = explode("\n", $data);

            fclose($file);
        }

        $ret = [];

        foreach ($datos as $mesa) {
            $atributos = explode(",", $mesa);

            $auxMesa = new stdClass();
            $auxMesa->id = $atributos[0];
            $auxMesa->estado_actual = $atributos[1];
            $auxMesa->codigo = $atributos[2];

            array_push($ret, $auxMesa);
        }

        return $ret;
    }

    public static function GuardarTxt($file_name, $datostxt, $flag)
    {
        $ret = 0;
        //El archivo no existe, creo uno nuevo
        if ($flag == 1) {

            $file = fopen($file_name, "w");

            $ret = fwrite($file, $datostxt);

            fclose($file);
        } else { //Ya existe, utilizo la "a" para escribir al final

            $file = fopen($file_name, "a");

            $ret = fwrite($file, "\n" . $datostxt);

            fclose($file);
        }
        //Retorno 1 = Se guardo, 0 = Hubo error
        return $ret;
    }
}
