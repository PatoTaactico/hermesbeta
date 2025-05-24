<?php

require_once "../controladores/devoluciones.controlador.php";
require_once "../modelos/devoluciones.modelo.php";

class AjaxDevoluciones {
    public $idPrestamo;
    public $idEquipo;
    public $accion;

    public function ajaxObtenerDatosPrestamo() {
        $item = "id_prestamo";
        $valor = $this->idPrestamo;

        // La respuesta ahora será un array de equipos si el préstamo tiene varios
        $respuesta = ControladorDevoluciones::ctrMostrarDevoluciones($item, $valor);

        echo json_encode($respuesta);
    }

    /*=============================================
    MARCAR EQUIPO EN DETALLE_PRESTAMO COMO MANTENIMIENTO
    =============================================*/
    public function ajaxMarcarMantenimientoDetalle() {
        // No se necesita pasar id_estado desde aquí, ya que se define en el controlador
        $respuestaControlador = ControladorDevoluciones::ctrMarcarMantenimientoDetalle($this->idPrestamo, $this->idEquipo);
        
        if ($respuestaControlador == "ok") {
            echo json_encode(array("success" => true, "status" => "equipo_marcado", "message" => "Equipo marcado para mantenimiento correctamente."));
        } else if ($respuestaControlador == "ok_prestamo_actualizado") {
            echo json_encode(array("success" => true, "status" => "prestamo_actualizado", "message" => "Equipo marcado y préstamo actualizado a devuelto."));
        } else if ($respuestaControlador == "no_change") {
            echo json_encode(array("success" => false, "status" => "sin_cambios", "message" => "El equipo ya estaba en el estado deseado o no se encontró."));
        } else if ($respuestaControlador == "error_actualizando_prestamo") {
            echo json_encode(array("success" => false, "status" => "error_prestamo", "message" => "Equipo marcado, pero hubo un error al actualizar el préstamo."));
        } else { // Cubre el caso 'error' del marcado inicial y cualquier otro inesperado
            echo json_encode(array("success" => false, "status" => "error_marcado", "message" => "Error al actualizar el estado del equipo."));
        }
    }

    /*=============================================
ENVIAR EQUIPO A MANTENIMIENTO CON MOTIVO
=============================================*/
    public function ajaxEnviarMantenimiento() {
        $respuestaControlador = ControladorDevoluciones::ctrEnviarMantenimiento(
            $this->idPrestamo,
            $this->idEquipo,
            $_POST["motivo"]
        );
        
        echo json_encode($respuestaControlador);
    }

        /*=============================================
    MARCAR EQUIPO COMO DISPONIBLE
    =============================================*/
    public function ajaxMarcarDisponible() {
        $respuestaControlador = ControladorDevoluciones::ctrMarcarDisponible(
            $this->idPrestamo,
            $this->idEquipo
        );
        
        echo json_encode($respuestaControlador);
    }

}

// Obtener datos del préstamo
if(isset($_POST["idPrestamo"]) && !isset($_POST["accion"])) {
    $prestamo = new AjaxDevoluciones();
    $prestamo->idPrestamo = $_POST["idPrestamo"];
    $prestamo->ajaxObtenerDatosPrestamo();
}

// Marcar equipo en detalle_prestamo como Mantenimiento
if(isset($_POST["accion"]) && $_POST["accion"] === "marcarMantenimientoDetalle") {
    $devolucion = new AjaxDevoluciones();
    $devolucion->idPrestamo = $_POST["idPrestamo"];
    $devolucion->idEquipo = $_POST["idEquipo"];
    $devolucion->ajaxMarcarMantenimientoDetalle();
}

// Enviar equipo a mantenimiento con motivo
if(isset($_POST["accion"]) && $_POST["accion"] === "enviarMantenimiento") {
    $devolucion = new AjaxDevoluciones();
    $devolucion->idPrestamo = $_POST["idPrestamo"];
    $devolucion->idEquipo = $_POST["idEquipo"];
    $devolucion->ajaxEnviarMantenimiento();
}

// Marcar equipo como disponible
if(isset($_POST["accion"]) && $_POST["accion"] === "marcarDisponible") {
    $devolucion = new AjaxDevoluciones();
    $devolucion->idPrestamo = $_POST["idPrestamo"];
    $devolucion->idEquipo = $_POST["idEquipo"];
    $devolucion->ajaxMarcarDisponible();
}