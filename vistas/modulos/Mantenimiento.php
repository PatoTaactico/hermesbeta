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
                <thead class="bg-dark">
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
                                    <button title="Finalizar mantenimiento" class="btn btn-success btn-sm btnFinalizarMantenimiento" data-id="' . $value["Id_mantenimiento"] . '">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button title="Ver detalles" class="btn btn-default btn-sm btnVerDetalles" data-id="' . $value["equipo_id"] . '" data-toggle="modal" data-target="#modalVerDetalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button title="Editar mantenimiento" class="btn btn-default btn-sm btnEditarMantenimiento" data-id="' . $value["Id_mantenimiento"] . '" data-toggle="modal" data-target="#modalEditarMantenimiento">
                                        <i class="fas fa-edit"></i>
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
  <div class="modal fade" id="modalFinalizarMantenimiento">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title text-white" id="modalFinalizarMantenimientoLabel">Detalles del Mantenimiento</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0">
          <div class="p-3">
            <!-- ... (contenido original igual) ... -->
          </div>

          <div class="p-3 bg-light">
            <h5 class="border-bottom pb-2">Estado del Mantenimiento</h5>
            <form id="formFinalizarMantenimiento" method="post">
              <input type="hidden" id="equipoId" name="equipoId">

              <div class="form-group">
                <label>Nivel de Gravedad:</label>
                <div class="d-flex">
                  <div class="custom-control custom-radio mr-4">
                    <input type="radio" id="sinNovedad" name="gravedad" value="ninguno" class="custom-control-input">
                    <label class="custom-control-label" for="sinNovedad">Sin novedad</label>
                  </div>
                  <div class="custom-control custom-radio mr-4">
                    <input type="radio" id="problemaLeve" name="gravedad" value="leve" class="custom-control-input">
                    <label class="custom-control-label" for="problemaLeve">Problema leve</label>
                  </div>
                  <div class="custom-control custom-radio">
                    <input type="radio" id="problemaGrave" name="gravedad" value="grave" class="custom-control-input">
                    <label class="custom-control-label" for="problemaGrave">Problema grave</label>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="tipoMante" class="form-label">Tipo de Mantenimiento:</label>
                <select name="tipoMantenimiento" id="tipoMante" class="form-control custom-select">
                  <option value="" disabled selected>Seleccione el tipo de mantenimiento</option>
                  <option value="preventivo">Preventivo</option>
                  <option value="correctivo">Correctivo</option>
                </select>
              </div>

              <div class="form-group">
                <label for="descripcionProblema">Descripción del problema:</label>
                <textarea class="form-control" id="descripcionProblema" name="detalles" rows="3" required></textarea>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-info px-4" id="btnGuardarMantenimiento">
                  <i class="fas fa-check-circle mr-2"></i>Marcar como Finalizado
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Ver Detalles -->
  <div class="modal fade" id="modalVerDetalles">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title">Detalles del Equipo</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Detalles del equipo en mantenimiento irían aquí</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Editar Mantenimiento -->
  <div class="modal fade" id="modalEditarMantenimiento">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title">Editar Mantenimiento</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Formulario para editar mantenimiento iría aquí</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Incluir el archivo JavaScript -->
  <script src="vistas/js/mantenimiento.js"></script>
</div>

