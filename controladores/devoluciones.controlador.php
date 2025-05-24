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

		$tabla = "detalle_prestamo";
		// Asumimos que el id_estado para 'Mantenimiento' es 4.
		// Si es diferente, ajusta este valor.
		$datos = array("id_prestamo" => $idPrestamo,
					   "equipo_id" => $idEquipo,
					   "id_estado" => 4); // Cambiado de "estado" => "Mantenimiento"

		$respuestaMarcado = ModeloDevoluciones::mdlMarcarMantenimientoDetalle($tabla, $datos);

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
ENVIAR EQUIPO A MANTENIMIENTO CON MOTIVO
=============================================*/
	static public function ctrEnviarMantenimiento($idPrestamo, $idEquipo, $motivo) {
		// Primero marcamos el equipo en detalle_prestamo como Mantenimiento (id_estado = 4)
		$tablaDetalle = "detalle_prestamo";
		$datosDetalle = array(
			"id_prestamo" => $idPrestamo,
			"equipo_id" => $idEquipo,
			"id_estado" => 4 // Mantenimiento
		);
		
		$respuestaMarcado = ModeloDevoluciones::mdlMarcarMantenimientoDetalle($tablaDetalle, $datosDetalle);
		
		if($respuestaMarcado == "ok" || $respuestaMarcado == "ok_prestamo_actualizado") {
			// Registrar el motivo en la tabla mantenimiento
			$registroMantenimiento = ModeloDevoluciones::mdlRegistrarMantenimiento($idEquipo, $motivo);
			
			if($registroMantenimiento == "ok") {
				return array(
					"success" => true,
					"status" => "mantenimiento_registrado",
					"message" => "Equipo enviado a mantenimiento correctamente"
				);
			} else {
				return array(
					"success" => false,
					"status" => "error_registro_mantenimiento",
					"message" => "Equipo marcado pero error al registrar motivo"
				);
			}
		} else {
			return array(
				"success" => false,
				"status" => "error_marcado_equipo",
				"message" => "Error al marcar el equipo para mantenimiento"
			);
		}
	}

		/*=============================================
	MARCAR EQUIPO COMO DISPONIBLE
	=============================================*/
	static public function ctrMarcarDisponible($idPrestamo, $idEquipo) {
		// 1. Marcar el equipo en detalle_prestamo como Devuelto (estado = 'Devuelto') y disponible (id_estado = 1)
		$tablaDetalle = "detalle_prestamo";
		$datosDetalle = array(
			"id_prestamo" => $idPrestamo,
			"equipo_id" => $idEquipo,
			"id_estado" => 1 // Disponible
		);
		
		$respuestaMarcado = ModeloDevoluciones::mdlMarcarDisponible($tablaDetalle, $datosDetalle);
		
		if($respuestaMarcado == "ok") {
			// Verificar si todos los equipos del préstamo han sido devueltos
			$todosDevueltos = ModeloDevoluciones::mdlVerificarTodosEquiposDevueltos($idPrestamo);
			
			if($todosDevueltos) {
				// Actualizar estado del préstamo a "Devuelto"
				$respuestaPrestamo = ModeloDevoluciones::mdlActualizarPrestamoDevuelto($idPrestamo);
				
				if($respuestaPrestamo == "ok") {
					return array(
						"success" => true,
						"status" => "prestamo_completo_devuelto",
						"message" => "Equipo marcado como disponible y préstamo completado"
					);
				} else {
					return array(
						"success" => true,
						"status" => "equipo_devuelto_prestamo_no_actualizado",
						"message" => "Equipo disponible pero error al actualizar préstamo"
					);
				}
			} else {
				return array(
					"success" => true,
					"status" => "equipo_devuelto",
					"message" => "Equipo marcado como disponible correctamente"
				);
			}
		} else {
			return array(
				"success" => false,
				"status" => "error_marcado_equipo",
				"message" => "Error al marcar el equipo como disponible"
			);
		}
	}

}
?>