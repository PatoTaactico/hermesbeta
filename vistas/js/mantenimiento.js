$(document).ready(function(){
  // Cuando se hace clic en el botón de finalizar mantenimiento
  $(".btnFinalizarMantenimiento").click(function(){
    var idMantenimiento = $(this).attr("data-id");
    var fila = $(this).closest("tr");
    var equipoId = fila.find("td:eq(0)").text();
    var numeroSerie = fila.find("td:eq(1)").text();
    var etiqueta = fila.find("td:eq(2)").text();
    var descripcion = fila.find("td:eq(3)").text();
    
    // Establecer los valores en el modal
    $("#idMantenimiento").val(idMantenimiento);
    $("#equipoId").text(equipoId);
    $("#equipoSerie").text(numeroSerie);
    $("#equipoEtiqueta").text(etiqueta);
    $("#equipoDescripcion").text(descripcion);
    
    // Mostrar el modal
    $("#modalFinalizarMantenimiento").modal("show");
  });
});