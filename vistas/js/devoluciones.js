$(document).ready(function() {
    // Cuando se hace clic en el botón Ver
    $('.btnVerUsuario').click(function() {
        var idPrestamo = $(this).attr('data-id');

        // Realizar petición AJAX
        $.ajax({
            url: "ajax/devoluciones.ajax.php",
            method: "POST",
            data: {
                idPrestamo: idPrestamo
            },
            dataType: "json",
            success: function(respuesta) {
                // La respuesta ahora es un array de objetos (uno por equipo)
                if(respuesta && respuesta.length > 0) {
                    // Tomamos los datos del usuario y préstamo del primer objeto del array
                    // Asumimos que los datos del préstamo y usuario son los mismos para todos los equipos en el mismo préstamo
                    var datosPrestamo = respuesta[0];

                    // Imagen y datos básicos del usuario
                    $('#userImage').attr('src', datosPrestamo.foto ? datosPrestamo.foto : 'vistas/img/usuarios/default/anonymous.png');
                    $('#userName').text(datosPrestamo.nombre + ' ' + datosPrestamo.apellido);
                    $('#userRol').text(datosPrestamo.idrRol || 'No especificado');

                    // Información del préstamo
                    $('#prestamoIdentificacion').text(datosPrestamo.numero_documento || 'No disponible');
                    $('#prestamoNombre').text(datosPrestamo.nombre || 'No disponible');
                    $('#prestamoApellido').text(datosPrestamo.apellido || 'No disponible');
                    $('#prestamoTelefono').text(datosPrestamo.telefono || 'No disponible');
                    $('#prestamoFicha').text(datosPrestamo.ficha_codigo || 'No asignada');
                    $('#prestamoTipo').text(datosPrestamo.tipo_prestamo || 'No especificado');
                    $('#prestamoFechaInicio').text(datosPrestamo.fecha_inicio || 'No especificada');
                    $('#prestamoFechaFin').text(datosPrestamo.fecha_fin || 'No especificada');
                    $('#prestamoEstado').text(datosPrestamo.estado_prestamo || 'No especificado');

                    // Información de los equipos (iterar sobre el array)
                    var equiposHtml = '';
                    respuesta.forEach(function(equipo) {
                        equiposHtml += `
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header">
                                    <h5 class="card-title">Equipo: ${equipo.descripcion || 'Sin descripción'}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 40%">Serial:</th>
                                                        <td>${equipo.numero_serie || 'No disponible'}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Etiqueta:</th>
                                                        <td>${equipo.etiqueta || 'No disponible'}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 40%">Categoría:</th>
                                                        <td>${equipo.categoria_nombre || 'No disponible'}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Descripción:</th>
                                                        <td>${equipo.descripcion || 'No disponible'}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- Contenedor para los botones de devolución de este equipo -->
                                    <div class="col-12 text-center mt-3 equipo-buttons-container">`;

                        // Lógica para mostrar los botones de devolución según el tipo de préstamo
                        if (datosPrestamo.tipo_prestamo === 'Inmediato') {
                            equiposHtml += `
                                <button type="button" class="btn btn-success btn-devolver-equipo mr-2" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="buen_estado">
                                    <i class="fas fa-check-circle mr-2"></i>Buen Estado
                                </button>
                                <button type="button" class="btn btn-danger btn-devolver-equipo" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="mal_estado">
                                    <i class="fas fa-times-circle mr-2"></i>Mal Estado
                                </button>
                            `;
                        } else if (datosPrestamo.tipo_prestamo === 'Reservado') {
                            equiposHtml += `
                                <button type="button" class="btn btn-success btn-devolver-equipo" data-prestamo-id="${datosPrestamo.id_prestamo}" data-equipo-id="${equipo.equipo_id}" data-estado="devuelto">
                                    <i class="fas fa-check-circle mr-2"></i>Marcar como Devuelto
                                </button>
                            `;
                        }
                        equiposHtml += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#equiposListContainer').html(equiposHtml); // Insertar los detalles de los equipos en el contenedor

                    // Mostrar la modal consolidada
                    $('#modalVerDetallesPrestamo').modal('show');

                } else {
                    // Si no hay equipos asociados al préstamo o la respuesta está vacía
                    alert("No se encontraron datos de equipos para este préstamo.");
                    // Limpiar la sección de equipos si no hay datos
                    $('#equiposListContainer').html('<p>No hay equipos asociados a este préstamo.</p>');
                     // Mostrar la modal con la información del préstamo/usuario si existe
                    if(respuesta && respuesta.length === 0) {
                         // Si la respuesta es un array vacío, aún podemos mostrar la info del préstamo si la API lo permite
                         // (aunque el modelo actual devuelve un array vacío si no hay equipos, no la info del préstamo)
                         // Si la API pudiera devolver la info del préstamo aunque no haya equipos, la lógica iría aquí.
                         // Con el modelo actual, si no hay equipos, respuesta es [], así que no mostramos la modal.
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición AJAX:", error);
                alert("Error al cargar los datos del préstamo");
            }
        });
    });

    // Cuando se hace clic en los botones de devolución de cada equipo (delegación de eventos)
    // Usamos delegación porque los botones se añaden dinámicamente
    $(document).on('click', '.equipo-buttons-container .btn-devolver-equipo', function() {
        var prestamoId = $(this).attr('data-prestamo-id');
        var equipoId = $(this).attr('data-equipo-id'); // Obtener el ID del equipo
        var estado = $(this).attr('data-estado'); // 'buen_estado', 'mal_estado', 'devuelto'

        console.log("Botón de devolución de equipo clickeado:");
        console.log("ID del Préstamo:", prestamoId);
        console.log("ID del Equipo:", equipoId);
        console.log("Estado:", estado);

        // Lógica para mostrar la modal si el estado es 'mal_estado'
        if (estado === 'mal_estado') {
            // Guardar el ID del préstamo y del equipo en la modal para usarlo después
            $('#malEstadoPrestamoId').val(prestamoId);
            $('#malEstadoEquipoId').val(equipoId);
            $('#modalMalEstado').modal('show');
        } else {
            // Lógica para procesar la devolución en buen estado o la reserva devuelta
            // Aquí iría la llamada AJAX para procesar la devolución de este equipo específico
            console.log(`Procesando devolución del equipo ${equipoId} en estado: ${estado}...`);
            // TODO: Implementar llamada AJAX para actualizar el estado del equipo y/o préstamo

            // Added AJAX call for 'Marcar como Devuelto' (estado === 'devuelto')
            if (estado === 'devuelto') { // Asumimos que 'devuelto' aquí significa 'marcar para mantenimiento'
                $.ajax({
                    url: "ajax/devoluciones.ajax.php", 
                    method: "POST",
                    data: {
                        accion: "marcarMantenimientoDetalle", 
                        idPrestamo: prestamoId,
                        idEquipo: equipoId
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) { 
                            Swal.fire({
                                icon: "success",
                                title: "¡Equipo marcado para mantenimiento!",
                                text: respuesta.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Eliminar el contenedor del equipo de la modal
                                // Buscamos el botón que fue clickeado, subimos a su contenedor de tarjeta y lo eliminamos.
                                $(`.btn-devolver-equipo[data-equipo-id='${equipoId}'][data-prestamo-id='${prestamoId}']`).closest('.card.card-outline.card-secondary').remove();
                                
                                // Opcional: Verificar si no quedan más equipos y mostrar un mensaje
                                if ($('#equiposListContainer .card').length === 0) {
                                    $('#equiposListContainer').html('<p>Todos los equipos de este préstamo han sido procesados.</p>');
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error al marcar para mantenimiento",
                                text: respuesta.message || "Hubo un problema al procesar la solicitud."
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en la petición AJAX:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error de comunicación",
                            text: "No se pudo comunicar con el servidor."
                        });
                    }
                });
            }
        }
    });
});

/*=============================================            
Marcar equipo como devuelto
=============================================*/

$(".tablaDevoluciones tbody").on("click", ".btnMarcarDevuelto", function(){

    var idDetallePrestamo = $(this).attr("idDetallePrestamo");
    var idPrestamo = $(this).attr("idPrestamo"); // Asegúrate de que este atributo se esté pasando correctamente

    console.log("idDetallePrestamo", idDetallePrestamo);
    console.log("idPrestamo", idPrestamo);

    Swal.fire({
        title: '¿Está seguro de marcar este equipo como devuelto?',
        text: "¡Si no lo está puede cancelar la acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, marcar devuelto!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {

            var datos = new FormData();
            datos.append("idDetallePrestamoMarcar", idDetallePrestamo);
            datos.append("idPrestamoMarcar", idPrestamo); // Enviar también idPrestamo

            $.ajax({
                url: "ajax/devoluciones.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json", // Esperamos una respuesta JSON
                success: function(respuesta){
                    console.log("Respuesta AJAX:", respuesta);
                    if(respuesta.status == "success_prestamo_actualizado"){
                        Swal.fire(
                            '¡Hecho!',
                            'El equipo ha sido marcado como devuelto y el préstamo ha sido actualizado.',
                            'success'
                        ).then((result) => {
                            if (result.isConfirmed) {
                                window.location = "devoluciones";
                            }
                        });
                    } else if (respuesta.status == "success"){
                        Swal.fire(
                            '¡Hecho!',
                            'El equipo ha sido marcado como devuelto.',
                            'success'
                        ).then((result) => {
                            if (result.isConfirmed) {
                                window.location = "devoluciones";
                            }
                        });
                    } else if (respuesta.status == "no_change"){
                        Swal.fire(
                            'Información',
                            'No se realizaron cambios en el estado del equipo.',
                            'info'
                        );
                    } else if (respuesta.status == "error_actualizando_prestamo"){
                        Swal.fire(
                            'Error',
                            'El equipo fue marcado como devuelto, pero hubo un error al actualizar el estado del préstamo.',
                            'error'
                        );
                    } else if (respuesta.status == "error"){
                        Swal.fire(
                            'Error',
                            'No se pudo marcar el equipo como devuelto.',
                            'error'
                        );
                    } else {
                        Swal.fire(
                            'Error',
                            'Respuesta desconocida del servidor.',
                            'error'
                        );
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error en AJAX: ", textStatus, errorThrown);
                    Swal.fire(
                        'Error de Comunicación',
                        'No se pudo conectar con el servidor: ' + textStatus,
                        'error'
                    );
                }
            });
        }
    })
})

// Manejar el clic en el botón "Guardar Motivo y Enviar a Mantenimiento"
$('#btnGuardarMalEstado').click(function() {
    var prestamoId = $('#malEstadoPrestamoId').val();
    var equipoId = $('#malEstadoEquipoId').val();
    var motivo = $('#motivoMalEstado').val();

    if (!motivo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debes describir el motivo del mal estado'
        });
        return;
    }

    // Mostrar confirmación
    Swal.fire({
        title: '¿Confirmar envío a mantenimiento?',
        text: "El equipo será marcado como en mantenimiento y el motivo será registrado",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar a mantenimiento',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar datos al servidor
            $.ajax({
                url: 'ajax/devoluciones.ajax.php',
                method: 'POST',
                data: {
                    accion: 'enviarMantenimiento',
                    idPrestamo: prestamoId,
                    idEquipo: equipoId,
                    motivo: motivo
                },
                dataType: 'json',
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Equipo enviado a mantenimiento!',
                            text: respuesta.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Cerrar modales y actualizar interfaz
                            $('#modalMalEstado').modal('hide');
                            $('#modalVerDetallesPrestamo').modal('hide');
                            
                            // Recargar la página para ver cambios
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: respuesta.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor'
                    });
                }
            });
        }
    });
});

// Modificar el manejador existente para el botón de buen estado
$(document).on('click', '.equipo-buttons-container .btn-devolver-equipo[data-estado="buen_estado"]', function() {
    var prestamoId = $(this).attr('data-prestamo-id');
    var equipoId = $(this).attr('data-equipo-id');
    
    Swal.fire({
        title: '¿Confirmar devolución en buen estado?',
        text: "El equipo será marcado como disponible para nuevos préstamos",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, marcar como disponible',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax/devoluciones.ajax.php",
                method: "POST",
                data: {
                    accion: "marcarDisponible",
                    idPrestamo: prestamoId,
                    idEquipo: equipoId
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            icon: "success",
                            title: "¡Equipo disponible!",
                            text: respuesta.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Eliminar el contenedor del equipo de la modal
                            $(`.btn-devolver-equipo[data-equipo-id='${equipoId}'][data-prestamo-id='${prestamoId}']`)
                                .closest('.card.card-outline.card-secondary').remove();
                            
                            // Verificar si no quedan más equipos y cerrar modal
                            if ($('#equiposListContainer .card').length === 0) {
                                $('#modalVerDetallesPrestamo').modal('hide');
                                // Opcional: recargar la tabla principal
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: respuesta.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error de conexión",
                        text: "No se pudo completar la operación"
                    });
                }
            });
        }
    });
});