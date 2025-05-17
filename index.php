<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/sedes.controlador.php";
require_once "controladores/fichas.controlador.php";
require_once "controladores/roles.controlador.php";
require_once "controladores/permisos.controlador.php";
require_once "controladores/modulos.controlador.php";
require_once "controladores/devoluciones.controlador.php";
require_once "controladores/mantenimiento.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/sedes.modelo.php";
require_once "modelos/fichas.modelo.php";
require_once "modelos/roles.modelo.php";
require_once "modelos/permisos.modelo.php";
require_once "modelos/modulos.modelo.php";
require_once "modelos/devoluciones.modelo.php";
require_once "modelos/mantenimiento.modelo.php";

$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();