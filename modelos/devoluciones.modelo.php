<?php

require_once "conexion.php";

class ModeloDevoluciones
{
    static public function mdlMostrarDevoluciones($tabla, $item, $valor)
    {
        if ($item != null) {
            // Consulta para un registro específico con JOIN, devolviendo cada equipo por separado
            // Se añade la condición para que solo muestre equipos con detalle_prestamo.id_estado = 2 (Prestado)
            $stmt = Conexion::conectar()->prepare(
                "SELECT p.*, u.numero_documento, u.nombre, u.apellido, u.telefono, 
                        f.codigo as ficha_codigo, 
                        e.equipo_id, e.numero_serie, e.descripcion, e.etiqueta, 
                        c.nombre AS categoria_nombre, 
                        dp.id_estado AS estado_del_equipo_en_prestamo 
                 FROM $tabla p
                 JOIN usuarios u ON p.usuario_id = u.id_usuario
                 LEFT JOIN aprendices_ficha af ON u.id_usuario = af.id_usuario
                 LEFT JOIN fichas f ON af.id_ficha = f.id_ficha
                 LEFT JOIN detalle_prestamo dp ON p.id_prestamo = dp.id_prestamo
                 LEFT JOIN equipos e ON dp.equipo_id = e.equipo_id
                 LEFT JOIN categorias c ON e.categoria_id = c.categoria_id
                 WHERE p.$item = :$item
                 AND p.estado_prestamo IN ('Prestado', 'Autorizado')
                 AND dp.id_estado = 2" // Condición agregada aquí para filtrar equipos en estado 'Prestado'
            );

            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } else {
            // Consulta para todos los registros con JOIN (sin cambios aquí)
            $stmt = Conexion::conectar()->prepare(
                "SELECT p.id_prestamo, u.numero_documento, u.nombre, u.apellido, u.telefono,
                        f.codigo as ficha_codigo,
                        p.fecha_inicio, p.fecha_fin, p.tipo_prestamo,
                        CASE
                            WHEN p.tipo_prestamo = 'Inmediato' THEN 'Inmediato'
                            ELSE 'Reservado'
                        END as estado_prestamo
                 FROM $tabla p
                 JOIN usuarios u ON p.usuario_id = u.id_usuario
                 LEFT JOIN aprendices_ficha af ON u.id_usuario = af.id_usuario
                 LEFT JOIN fichas f ON af.id_ficha = f.id_ficha
                 WHERE p.estado_prestamo IN ('Prestado', 'Autorizado')
                 ORDER BY p.fecha_inicio DESC"
            );

            $stmt->execute();
            return $stmt->fetchAll();
        }

        // $stmt->close(); // PDOStatement::closeCursor() is called automatically when the statement is no longer referenced.
        $stmt = null;
    }


	/*============================================= 
	MARCAR EQUIPO EN DETALLE_PRESTAMO COMO MANTENIMIENTO (ACTUALIZANDO ID_ESTADO)
	=============================================*/
	static public function mdlMarcarMantenimientoDetalle($tabla, $datos){

	 	 // Ahora actualizamos la columna id_estado en lugar de la columna estado
	 	 // Asumimos que el id_estado para 'Mantenimiento' es 4.
	 	 // Si el id_estado para 'Mantenimiento' es diferente, debes cambiar el valor :id_estado aquí.
	 	 $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET id_estado = :id_estado WHERE id_prestamo = :id_prestamo AND equipo_id = :equipo_id");

	 	 $stmt->bindParam(":id_estado", $datos["id_estado"], PDO::PARAM_INT); // Cambiado de :estado a :id_estado y de PARAM_STR a PARAM_INT
	 	 $stmt->bindParam(":id_prestamo", $datos["id_prestamo"], PDO::PARAM_INT);
	 	 $stmt->bindParam(":equipo_id", $datos["equipo_id"], PDO::PARAM_INT);

	 	 if($stmt->execute()){ 
            error_log("MODELO: Update ejecutado con éxito. Filas afectadas: " . $stmt->rowCount()); 
            // Verificar si realmente se afectaron filas 
            if ($stmt->rowCount() > 0) { 
                return "ok"; 
            } else { 
                error_log("MODELO: Update ejecutado pero no afectó filas. ¿Coinciden id_prestamo y equipo_id?"); 
                return "no_change"; // O algún otro indicador 
            } 
	 	 }else{ 
            error_log("MODELO: Error en execute(): " . json_encode($stmt->errorInfo())); 
	 	 	 return "error"; 
	 	 
	 	 } 

	 	 // $stmt->closeCursor(); // No es necesario con $stmt = null; 
	 	 $stmt = null; 

	 } 

	/*============================================= 
	VERIFICAR SI TODOS LOS EQUIPOS DE UN PRÉSTAMO HAN SIDO DEVUELTOS (MARCADOS PARA MANTENIMIENTO)
	=============================================*/
	static public function mdlVerificarTodosEquiposDevueltos($idPrestamo){

		$stmt = Conexion::conectar()->prepare(
			"SELECT COUNT(*) as total_equipos, 
					SUM(CASE WHEN id_estado = 4 THEN 1 ELSE 0 END) as equipos_en_mantenimiento 
			 FROM detalle_prestamo 
			 WHERE id_prestamo = :id_prestamo"
		);

		$stmt->bindParam(":id_prestamo", $idPrestamo, PDO::PARAM_INT);
		$stmt->execute();
		$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

		if($resultado && $resultado["total_equipos"] > 0 && $resultado["total_equipos"] == $resultado["equipos_en_mantenimiento"]){
			return true; // Todos los equipos están en mantenimiento
		} else {
			return false; // No todos los equipos están en mantenimiento o no hay equipos
		}

		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR ESTADO DEL PRÉSTAMO A DEVUELTO Y REGISTRAR FECHA REAL DE DEVOLUCIÓN
	=============================================*/
	static public function mdlActualizarPrestamoDevuelto($idPrestamo){

		$stmt = Conexion::conectar()->prepare(
			"UPDATE prestamos 
			 SET estado_prestamo = 'Devuelto', fecha_devolucion_real = NOW() 
			 WHERE id_prestamo = :id_prestamo"
		);

		$stmt->bindParam(":id_prestamo", $idPrestamo, PDO::PARAM_INT);

		if($stmt->execute()){
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}

    /*=============================================
REGISTRAR MOTIVO DE MANTENIMIENTO
=============================================*/
    static public function mdlRegistrarMantenimiento($idEquipo, $motivo) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO mantenimiento (equipo_id, detalles) 
                VALUES (:equipo_id, :detalles)"
            );
            
            $stmt->bindParam(":equipo_id", $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(":detalles", $motivo, PDO::PARAM_STR);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                error_log("Error al registrar mantenimiento: " . json_encode($stmt->errorInfo()));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Excepción al registrar mantenimiento: " . $e->getMessage());
            return "error";
        }
        
        $stmt = null;
    }

        /*=============================================
    MARCAR EQUIPO COMO DISPONIBLE EN DETALLE_PRESTAMO
    =============================================*/
    static public function mdlMarcarDisponible($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE $tabla 
                SET estado = 'Devuelto', id_estado = :id_estado 
                WHERE id_prestamo = :id_prestamo AND equipo_id = :equipo_id"
            );
            
            $stmt->bindParam(":id_estado", $datos["id_estado"], PDO::PARAM_INT);
            $stmt->bindParam(":id_prestamo", $datos["id_prestamo"], PDO::PARAM_INT);
            $stmt->bindParam(":equipo_id", $datos["equipo_id"], PDO::PARAM_INT);
            
            if($stmt->execute()) {
                // Verificar si se actualizó alguna fila
                if($stmt->rowCount() > 0) {
                    return "ok";
                } else {
                    error_log("No se actualizó ninguna fila - ¿Existe el préstamo y equipo?");
                    return "no_change";
                }
            } else {
                error_log("Error en execute(): " . json_encode($stmt->errorInfo()));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Excepción en mdlMarcarDisponible: " . $e->getMessage());
            return "error";
        }
        
        $stmt = null;
    }

}
