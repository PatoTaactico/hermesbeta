<?php

require_once "../controladores/roles.controlador.php";
require_once "../modelos/roles.modelo.php";

class AjaxRoles
{
    /*=============================================
        EDITAR ROL
        =============================================*/
    public $idRol;
    public $estadoRol;

    public function ajaxEditarRol()
    {
        $item = "id_rol";
        $valor = $this->idRol;

        $respuesta = ControladorRoles::ctrMostrarRoles($item, $valor);

        echo json_encode($respuesta);
    }

    public function ajaxCambiarEstadoRol()
    {
        $valorId = intval($this->idRol);
        $valorEstado = $this->estadoRol;

        if ($valorEstado !== "activo" && $valorEstado !=="inactivo") {
            echo json_encode(["error" => "estado no valido"]);
            return;
    }

        $respuesta = ModeloRoles::mdlCambiarEstadoRol($valorId, $valorEstado);

        // Si el rol es del 1 al 9, también actualiza los usuarios asociados
        if ($valorId >= 1 && $valorId <= 9) {
            if ($valorEstado == "inactivo") {
                ModeloRoles::mdlDesactivarUsuariosPorRol($valorId);
            } else if ($valorEstado == "activo") {
                ModeloRoles::mdlActivarUsuariosPorRol($valorId);
            }
        }
        else{
            if ($valorEstado == "inactivo") {
                ModeloRoles::mdlDesactivarUsuariosPorRol($valorId);
            }
            else if ($valorEstado == "activo") {
                ModeloRoles::mdlActivarUsuariosPorRol($valorId);
            }
        }

        echo json_encode($respuesta);
    }

    public function ajaxMostrarRol()
    {
        $item = "id_rol";
        $valor = $this->idRol;

        $respuesta = ControladorRoles::ctrMostrarRoles($item, $valor);

        echo json_encode($respuesta);
    }
}

if (isset($_POST["idRol"]) && !isset($_POST["estadoRol"])) {
    $editar = new AjaxRoles();
    $editar->idRol = $_POST["idRol"];
    $editar->ajaxEditarRol();
}


if (isset($_POST["idRolActivar"]) && isset($_POST["estadoRol"])) {
    $activarRol = new AjaxRoles();
    $activarRol->idRol = $_POST["idRolActivar"];
    $activarRol->estadoRol = $_POST["estadoRol"];
    $activarRol->ajaxCambiarEstadoRol();
}

if (isset($_POST["idRolDescripcion"])) {
    $mostrar = new AjaxRoles();
    $mostrar->idRol = $_POST["idRolDescripcion"];
    $mostrar->ajaxMostrarRol();
}

if (isset($_POST["idRolEliminar"])) {
    ControladorRoles::ctrEliminarRol();
    exit();
}
