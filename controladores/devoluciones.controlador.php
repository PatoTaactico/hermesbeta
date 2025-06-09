<?php
    class ControladorDevoluciones {
        /*=============================================
        MOSTRAR DEVOLUCIONES (LISTADO)
        =============================================*/
        static public function ctrMostrarDevoluciones($item, $valor) {
            $tabla = "prestamos";
            $respuesta = ModeloDevoluciones::mdlMostrarDevoluciones($tabla, $item, $valor);
            return $respuesta;
        }

	/*=============================================
	MARCAR EQUIPO EN DETALLE_PRESTAMO COMO MANTENIMIENTO (ACTUALIZANDO ID_ESTADO)
	=============================================*/
	static public function ctrMarcarMantenimientoDetalle($idPrestamo, $idEquipo){

		$tabla = "equipos"; // Cambiado de "detalle_prestamo" a "equipos"
		// Asumimos que el id_estado para 'Mantenimiento' es 4.
		// Si es diferente, ajusta este valor.
		$datos = array("equipo_id" => $idEquipo,
					   "id_estado" => 4); // Eliminado id_prestamo de los datos para mdlMarcarMantenimientoDetalle

		$respuestaMarcado = ModeloDevoluciones::mdlMarcarMantenimientoDetalle($datos);

		if($respuestaMarcado == "ok"){
			// Verificar si todos los equipos del préstamo han sido devueltos
			$todosDevueltos = ModeloDevoluciones::mdlVerificarTodosEquiposDevueltos($idPrestamo);

			if($todosDevueltos){
				// Si todos han sido devueltos, actualizar el estado del préstamo
				$respuestaActualizacionPrestamo = ModeloDevoluciones::mdlActualizarPrestamoDevuelto($idPrestamo);
				if($respuestaActualizacionPrestamo == "ok"){
					return "ok_prestamo_actualizado"; // Éxito al marcar equipo y actualizar préstamo
				} else {
					return "error_actualizando_prestamo"; // Error al actualizar el préstamo
				}
			} else {
				return "ok"; // Éxito al marcar el equipo, pero no todos han sido devueltos aún
			}
		} else {
			return $respuestaMarcado; // Retorna "no_change" o "error" del marcado inicial
		}

	}
	/*============================================= 
/*============================================= 
MARCAR EQUIPO EN DETALLE_PRESTAMO COMO DEVUELTO EN BUEN ESTADO
=============================================*/
static public function ctrMarcarDevueltoBuenEstado($idPrestamo, $idEquipo){
    $datos = array(
        "id_prestamo" => $idPrestamo,
        "equipo_id" => $idEquipo,
        "id_estado" => 1 // 1 = Disponible
    );

    // 1. Marcar equipo como devuelto
    $respuestaMarcado = ModeloDevoluciones::mdlMarcarDevueltoBuenEstado($datos);

    if($respuestaMarcado == "ok"){
        // 2. Actualizar fecha de devolución
        $respuestaFecha = ModeloDevoluciones::mdlActualizarFechaDevolucion($idPrestamo);
        
        if($respuestaFecha != "ok"){
            return "error_actualizando_fecha";
        }

        // 3. Verificar si todos los equipos están devueltos
        $todosDevueltos = ModeloDevoluciones::mdlVerificarTodosEquiposDevueltos($idPrestamo);

        if($todosDevueltos){
            $respuestaPrestamo = ModeloDevoluciones::mdlActualizarPrestamoDevuelto($idPrestamo);
            return ($respuestaPrestamo == "ok") ? "ok_prestamo_actualizado" : "error_actualizando_prestamo";
        }
        return "ok";
    }
    return $respuestaMarcado;
}

/*=============================================
MARCAR EQUIPO COMO ROBADO (BAJA)
=============================================*/
static public function ctrMarcarEquipoRobado($idPrestamo, $idEquipo){
    $datos = array(
        "equipo_id" => $idEquipo,
        "id_estado" => 8 // Estado 'baja' para equipos robados
    );

    $respuestaMarcado = ModeloDevoluciones::mdlMarcarMantenimientoDetalle($datos);

    if($respuestaMarcado == "ok"){
        $todosDevueltos = ModeloDevoluciones::mdlVerificarTodosEquiposDevueltos($idPrestamo);

        if($todosDevueltos){
            $respuestaActualizacionPrestamo = ModeloDevoluciones::mdlActualizarPrestamoDevuelto($idPrestamo);
            return ($respuestaActualizacionPrestamo == "ok") ? "ok_prestamo_actualizado" : "error_actualizando_prestamo";
        }
        return "ok";
    }
    return $respuestaMarcado;
}
    /*=============================================
    MARCAR EQUIPO PARA MANTENIMIENTO CON MOTIVO (versión mejorada)
    =============================================*/
    static public function ctrMarcarMantenimientoConMotivo($idPrestamo, $idEquipo, $motivo) {
        try {
            // 1. Actualizar estado del equipo
            $datosEquipo = array(
                "equipo_id" => $idEquipo,
                "id_estado" => 4 // Mantenimiento
            );
    
            $respuestaMarcado = ModeloDevoluciones::mdlMarcarMantenimientoDetalle($datosEquipo);
            
            if($respuestaMarcado != "ok") {
                return array(
                    "success" => false,
                    "status" => "error_actualizando_equipo",
                    "message" => "Error al actualizar el estado del equipo."
                );
            }
    
            // 2. Registrar motivo
            $respuestaMotivo = ModeloDevoluciones::mdlRegistrarMotivoMantenimiento($idEquipo, $motivo);
            
            if($respuestaMotivo != "ok") {
                return array(
                    "success" => false,
                    "status" => "error_registrando_motivo",
                    "message" => "Error al registrar el motivo."
                );
            }
    
            // 3. Actualizar detalle_prestamo
            $stmtDetalle = Conexion::conectar()->prepare(
                "UPDATE detalle_prestamo SET estado = 'Devuelto', 
                fecha_actualizacion = NOW()
                WHERE id_prestamo = :id_prestamo AND equipo_id = :equipo_id"
            );
            
            $stmtDetalle->bindParam(":id_prestamo", $idPrestamo, PDO::PARAM_INT);
            $stmtDetalle->bindParam(":equipo_id", $idEquipo, PDO::PARAM_INT);
            
            if(!$stmtDetalle->execute()) {
                return array(
                    "success" => false,
                    "status" => "error_actualizando_detalle",
                    "message" => "Error al actualizar el detalle."
                );
            }
    
            // 4. Actualizar fecha de devolución
            $respuestaFecha = ModeloDevoluciones::mdlActualizarFechaDevolucion($idPrestamo);
            if($respuestaFecha != "ok") {
                return array(
                    "success" => false,
                    "status" => "error_actualizando_fecha",
                    "message" => "Error al registrar fecha de devolución."
                );
            }
    
            // 5. Verificar si todos los equipos están devueltos
            $todosDevueltos = ModeloDevoluciones::mdlVerificarTodosEquiposDevueltos($idPrestamo);
            
            if($todosDevueltos) {
                $respuestaPrestamo = ModeloDevoluciones::mdlActualizarPrestamoDevuelto($idPrestamo);
                if($respuestaPrestamo == "ok") {
                    return array(
                        "success" => true,
                        "status" => "ok_prestamo_actualizado",
                        "message" => "Equipo en mantenimiento y préstamo completado."
                    );
                } else {
                    return array(
                        "success" => true,
                        "status" => "ok",
                        "message" => "Equipo en mantenimiento, pero error al actualizar préstamo."
                    );
                }
            }
    
            return array(
                "success" => true,
                "status" => "ok",
                "message" => "Equipo enviado a mantenimiento correctamente."
            );
    
        } catch (PDOException $e) {
            return array(
                "success" => false,
                "status" => "error_excepcion",
                "message" => "Error: " . $e->getMessage()
            );
        }
    }
}
?>