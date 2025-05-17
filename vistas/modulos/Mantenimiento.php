<div class="content-wrapper">
  <!-- Encabezado de la pagina -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Mantenimiento</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">inicio</li>
          </ol>
        </div>
      </div>
    </div>
  </section>
  <!-- fin de encabezado -->

  <!-- Inicio de la tabla -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <table id="tblMantenimiento" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Numero de serie</th>
                    <th>Etiqueta</th>
                    <th>Descripcion</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $item = null;
                  $valor = null;
                  $mantenimientos = ControladorMantenimiento::ctrMostrarMantenimientos($item, $valor);
                  // var_dump($mantenimientos);

                  // Verificar si hay resultados
                  if (!empty($mantenimientos) && is_array($mantenimientos)) {
                    foreach ($mantenimientos as $key => $value) {
                      echo '
                        <tr>
                            <td>' . $value["equipo_id"] . '</td>
                            <td>' . $value["numero_serie"] . '</td>
                            <td>' . $value["etiqueta"] . '</td>
                            <td>' . $value["descripcion"] . '</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-success btn-sm btnFinalizarMantenimiento" 
                                            data-id="' . $value["Id_mantenimiento"] . '">
                                        <i class="fas fa-check"></i> Finalizar
                                    </button>
                                </div>
                            </td>
                        </tr>';
                    }
                  } else {
                    echo '<tr><td colspan="6" class="text-center">No hay equipos en mantenimiento</td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- Fin de la tabla -->

  <!-- Modal Finalizar Mantenimiento -->
  <!-- Modal Finalizar Mantenimiento -->
  <div class="modal fade" id="modalFinalizarMantenimiento" tabindex="-1" role="dialog" aria-labelledby="modalFinalizarMantenimientoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title text-white" id="modalFinalizarMantenimientoLabel">Detalles del Mantenimiento</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0">
          <!-- Información del Equipo -->
          <div class="p-3">
            <div class="row">
              <div class="col-md-4 text-center mb-3">
                <i class="fas fa-laptop text-info" style="font-size: 4rem;"></i>
                <h6 class="mt-2" id="equipoEtiqueta">Etiqueta</h6>
                <small class="text-muted" id="equipoRol">Equipo</small>
              </div>
              <div class="col-md-8">
                <h5 class="border-bottom pb-2">Información del Equipo</h5>
                <div class="row mb-2">
                  <div class="col-sm-5 text-muted">Identificación:</div>
                  <div class="col-sm-7" id="equipoId">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-sm-5 text-muted">Número de Serie:</div>
                  <div class="col-sm-7" id="equipoSerie">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-sm-5 text-muted">Descripción:</div>
                  <div class="col-sm-7" id="equipoDescripcion">-</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Formulario de Mantenimiento -->
          <div class="p-3 bg-light">
            <h5 class="border-bottom pb-2">Estado del Mantenimiento</h5>
            <form method="post">
              <input type="hidden" id="idMantenimiento" name="idMantenimiento">
              
              <!-- Selector de Gravedad -->
              <div class="form-group">
                <label>Nivel de Gravedad:</label>
                <div class="d-flex">
                  <div class="custom-control custom-radio mr-4">
                    <input type="radio" id="problemaLeve" name="gravedad" value="leve" class="custom-control-input" checked>
                    <label class="custom-control-label" for="problemaLeve">Problema leve</label>
                  </div>
                  <div class="custom-control custom-radio">
                    <input type="radio" id="problemaGrave" name="gravedad" value="grave" class="custom-control-input">
                    <label class="custom-control-label" for="problemaGrave">Problema grave</label>
                  </div>
                </div>
              </div>
              
              <!-- Descripción -->
              <div class="form-group">
                <label for="descripcionProblema">Descripción del problema:</label>
                <textarea class="form-control" id="descripcionProblema" name="descripcionProblema" rows="3" required></textarea>
              </div>
              
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-info px-4" name="finalizarMantenimiento">
                  <i class="fas fa-check-circle mr-2"></i>Marcar como Finalizado
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Script para manejar el botón de finalizar mantenimiento -->
  <script>
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
  </script>
  <!-- Fin del modal -->

  <!-- Incluir el archivo JavaScript -->
  <script src="vistas/js/mantenimiento.js"></script>
</div>