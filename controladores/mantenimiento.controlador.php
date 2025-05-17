<?php

class ControladorMantenimiento {
    
    // Mostrar mantenimientos

    static public function ctrMostrarMantenimientos($item, $valor)
    {
        $tabla = "mantenimiento";

        $respuesta = ModeloMantenimiento::mdlMostrarMantenimientos($tabla, $item, $valor);

        return $respuesta;
    }

}

?>