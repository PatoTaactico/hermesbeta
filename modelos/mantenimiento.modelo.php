<?php

require_once "conexion.php";


class ModeloMantenimiento
{
    // Mostrar mantenimientos

    static public function mdlMostrarMantenimientos($tabla, $item, $valor) {
        try {
            if ($item == null) 
            {
                $stmt = Conexion::conectar()->prepare("SELECT m.* , e.* FROM mantenimiento m JOIN equipos e ON m.equipo_id = e.equipo_id");
                $stmt->execute();
                return $stmt->fetchAll();
                
            } } catch (PDOException $e) {
                return "error";
            } finally {
                $stmt = null;
            }
    }
}
