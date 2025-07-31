$(document).ready(function () {
    // Cuando se hace clic en el botón Ver
    $(document).on("click", ".btnVerUsuario", function () {
        var idPrestamo = $(this).attr("data-id");

        // Realizar petición AJAX
        $.ajax({
            url: "ajax/devoluciones.ajax.php",
            method: "POST",
            data: {
                idPrestamo: idPrestamo,
            },
            dataType: "json",
            success: function (respuesta) {
                if (respuesta && respuesta.length > 0) {
                    var datosPrestamo = respuesta[0];

                    // Imagen y datos básicos del usuario
                    $("#userImage").attr(
                        "src",
                        datosPrestamo.foto
                            ? datosPrestamo.foto
                            : "vistas/img/usuarios/default/anonymous.png"
                    );
                    $("#userName").text(
                        datosPrestamo.nombre_usuario + " " + datosPrestamo.apellido_usuario
                    );
                    $("#userRol").text(datosPrestamo.nombre_rol || "No especificado"); // Updated to use nombre_rol

                    // Información del préstamo
                    $("#prestamoIdentificacion").text(
                        datosPrestamo.numero_documento || "No disponible"
                    );
                    $("#prestamoTelefono").text(datosPrestamo.telefono || "---");
                    $("#prestamoFicha").text(datosPrestamo.ficha_codigo || "No asignada");
                    $("#prestamoTipo").text(
                        datosPrestamo.tipo_prestamo || "No especificado"
                    );
                    $("#prestamoFechaInicio").text(
                        datosPrestamo.fecha_inicio || "No especificada"
                    );
                    $("#prestamoFechaFin").text(
                        datosPrestamo.fecha_fin || "No especificada"
                    );
                    $("#prestamoEstado").text(
                        datosPrestamo.estado_prestamo || "No especificado"
                    );

                    // Información de los equipos en una tabla
                    var equiposTableHtml = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tablaDevolucionesEquipos">
                                <thead class="bg-dark">
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Marca</th>
                                        <th>Placa</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            <tbody>
                    `;

                    respuesta.forEach(function (equipo) {
                        equiposTableHtml += `
                            <tr>
                                <td>${equipo.categoria_nombre || "No disponible"
                            }</td>
                                <td>${equipo.marca_equipo || "No disponible"
                            }</td> 
                                <td>${equipo.placa_equipo || "No disponible"
                            }</td>
                                <td class="equipo-buttons-container">`;

                        // Lógica para mostrar los botones de devolución según el tipo de préstamo
                        if (datosPrestamo.tipo_prestamo === "Inmediato") {
                            equiposTableHtml += `
                                <button type="button" class="btn btn-success btn-sm btn-devolver-equipo mr-1" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="buen_estado">
                                    <i class="fas fa-check-circle"></i> B. Estado
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-devolver-equipo" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="mal_estado">
                                    <i class="fas fa-undo-alt"></i> M. Estado
                                </button>
                            `;
                        } else if (datosPrestamo.tipo_prestamo === "Reservado") {
                            equiposTableHtml += `
                                <button type="button" class="btn btn-info btn-sm btn-devolver-equipo" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="devuelto">
                                    <i class="fas fa-undo-alt"></i> Devolver
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-devolver-equipo" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="robado">
                                    <i class="fas fa-times-circle"></i> Robado
                                </button>
                            `;
                        }
                        equiposTableHtml += `</td></tr>`;
                    });

                    equiposTableHtml += `
                            </tbody>
                            </table>
                        </div>
                    `;

                    $("#equiposListContainer").html(equiposTableHtml);
                    $("#idPrestamo").text(datosPrestamo.id_prestamo);
                    $("#numeroPrestamo").text(datosPrestamo.id_prestamo);
                    $("#modalVerDetallesPrestamo").modal("show");
                } else {
                    alert("No se encontraron datos de equipos para este préstamo.");
                    $("#equiposListContainer").html(
                        "<p>No hay equipos asociados a este préstamo.</p>"
                    );
                }
            },
            error: function (xhr, status, error) {
                console.error("Error en la petición AJAX:", error);
                alert("Error al cargar los datos del préstamo");
            },
        });
    });

    // Delegación de eventos para los botones de devolución
    $(document).on(
        "click",
        ".equipo-buttons-container .btn-devolver-equipo",
        function () {
            var prestamoId = $(this).attr("data-prestamo-id");
            var equipoId = $(this).attr("data-equipo-id");
            var estado = $(this).attr("data-estado");
            var $buttonPressed = $(this);

            console.log(
                "Botón de devolución clickeado - Préstamo:",
                prestamoId,
                "Equipo:",
                equipoId,
                "Estado:",
                estado
            );

            // Casos para "Mal Estado", "Buen Estado", "Devolver" y "Robado"
            if (
                estado === "mal_estado" ||
                estado === "devuelto" ||
                estado === "buen_estado" ||
                estado === "robado"
            ) {
                var accionAjax;
                if (estado === "mal_estado" || estado === "devuelto") {
                    accionAjax = "marcarMantenimientoDetalle";
                } else if (estado === "buen_estado") {
                    accionAjax = "marcarBuenEstado";
                } else if (estado === "robado") {
                    accionAjax = "marcarEquipoRobado";
                }

                $.ajax({
                    url: "ajax/devoluciones.ajax.php",
                    method: "POST",
                    data: {
                        accion: accionAjax,
                        idPrestamo: prestamoId,
                        idEquipo: equipoId,
                    },
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta && respuesta.success) {
                            // Usar Toast para notificaciones rápidas que desaparecen automáticamente
                            Toast.fire({
                                icon: "success",
                                title: respuesta.title || "¡Acción completada!",
                                text: respuesta.message,
                            }).then(() => {
                                $buttonPressed.closest("tr").fadeOut(500, function () {
                                    $(this).remove();

                                    if ($("#equiposListContainer tbody tr").length === 0) {
                                        $("#equiposListContainer").html(
                                            '<p class="text-center">Todos los equipos de este préstamo han sido procesados.</p>'
                                        );

                                        // Cierra el modal después de 500ms
                                        setTimeout(function () {
                                            $("#modalVerDetallesPrestamo").modal("hide");
                                        }, 500);

                                        // Recarga la tabla si el préstamo se ha actualizado completamente
                                        if (
                                            respuesta.status === "ok_prestamo_actualizado" ||
                                            respuesta.status === "prestamo_actualizado"
                                        ) {
                                            if (typeof tablaDevoluciones !== "undefined") {
                                                tablaDevoluciones.ajax.reload();
                                            } else {
                                                window.location.reload();
                                            }
                                        }
                                    }
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    (respuesta && respuesta.message) ||
                                    "Ocurrió un error al procesar la solicitud.",
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la petición AJAX:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error de comunicación",
                            text: "No se pudo comunicar con el servidor.",
                        });
                    },
                });
            }
        }
    );

    // Evento para marcar en mal estado directamente
    $(document).on("click", ".btnMarcarMalEstado", function () {
        var idDetallePrestamo = $(this).attr("id_detalle_prestamo");
        var idPrestamo = $(this).attr("id_prestamo");

        Swal.fire({
            title: "¿Está seguro?",
            text: "El equipo será marcado para mantenimiento y no podrá revertir esta acción.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, marcar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append("accion", "marcarMantenimientoDetalle");
                datos.append("id_detalle_prestamo", idDetallePrestamo);
                datos.append("id_prestamo", idPrestamo);

                $.ajax({
                    url: "ajax/devoluciones.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta.success) {
                            Swal.fire("¡Hecho!", respuesta.message, "success").then(() => {
                                // Actualizar la tabla o la fila específica
                                tablaDevoluciones.ajax.reload();
                            });
                        } else {
                            Swal.fire("Error", respuesta.message, "error");
                        }
                    },
                });
            }
        });
    });

    // Integrar la función alternativa dentro del document ready
    $(".tablaDevoluciones tbody").on("click", ".btnMarcarDevuelto", function () {
        var idDetallePrestamo = $(this).attr("idDetallePrestamo");
        var idPrestamo = $(this).attr("idPrestamo");

        Swal.fire({
            title: "¿Está seguro de marcar este equipo como devuelto?",
            text: "¡Si no lo está puede cancelar la acción!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, marcar devuelto!",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append("idDetallePrestamoMarcar", idDetallePrestamo);
                datos.append("idPrestamoMarcar", idPrestamo);

                $.ajax({
                    url: "ajax/devoluciones.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        console.log("Respuesta AJAX:", respuesta);
                        if (respuesta && respuesta.status == "success_prestamo_actualizado") {
                            Swal.fire({
                                icon: "success",
                                title: "¡Hecho!",
                                text: "El equipo ha sido marcado como devuelto y el préstamo ha sido actualizado.",
                                showConfirmButton: true,
                                timer: 2000
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                                    window.location = "devoluciones";
                                }
                            });
                        } else if (respuesta && respuesta.status == "success") {
                            Swal.fire({
                                icon: "success",
                                title: "¡Hecho!",
                                text: "El equipo ha sido marcado como devuelto.",
                                showConfirmButton: true,
                                timer: 2000
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                                    window.location = "devoluciones";
                                }
                            });
                        } else if (respuesta && respuesta.status == "no_change") {
                            Swal.fire({
                                icon: "info",
                                title: "Información",
                                text: "No se realizaron cambios en el estado del equipo.",
                                timer: 3000
                            });
                        } else if (
                            respuesta &&
                            respuesta.status == "error_actualizando_prestamo"
                        ) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "El equipo fue marcado como devuelto, pero hubo un error al actualizar el estado del préstamo."
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: (respuesta && respuesta.message) ||
                                "Respuesta desconocida del servidor."
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("Error en AJAX: ", textStatus, errorThrown);
                        Swal.fire({
                            icon: "error",
                            title: "Error de Comunicación",
                            text: "No se pudo conectar con el servidor: " + textStatus
                        });
                    },
                });
            }
        });
    });
});
