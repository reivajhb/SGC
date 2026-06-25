<?php
include "../../config/seguridad.php";
include "../../config/conexion.php";

// ✅ Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ Validación admin primero
$isAdmin = isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 1;
if (!$isAdmin) {
    header("Location: buscarProveedor.php");
    exit();
}

// ✅ Sidebar admin
include "../../config/sidebar3.php";

// ✅ Usuarios (incluye id_rol para filtrar + modal)
$query = "SELECT u.id_usuario, u.usuario, u.estado, u.id_rol, r.descripcion AS rol
          FROM tbl_usuarios u
          INNER JOIN tbl_roles r ON u.id_rol = r.id
          ORDER BY u.id_usuario DESC";
$resultado = $conn->query($query);

// ✅ Roles para tabs + select
$query_roles = "SELECT id, descripcion FROM tbl_roles ORDER BY descripcion ASC";
$roles_result = $conn->query($query_roles);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">

  <!-- Tus estilos -->
  <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

  <title>Administrar Usuarios</title>
</head>

<body>
  <div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <h2 class="mb-1">Administrar Usuarios</h2>
        <small class="text-muted">Gestiona usuarios, roles y estados desde este panel.</small>
      </div>

      <a
        href="registrouser.php"
        class="btn btn-primary shadow-sm d-inline-flex align-items-center justify-content-center gap-2 px-4"
        title="Registrar nuevo usuario"
      >
        <span class="fw-bold">+</span>
        <span>Registrar nuevo usuario</span>
      </a>
    </div>

    <!-- Tabs por rol -->
    <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="#" data-rol="all">Todos</a>
      </li>
      <?php foreach ($roles as $rol) { ?>
        <li class="nav-item">
          <a class="nav-link" href="#" data-rol="<?php echo (int)$rol['id']; ?>">
            <?php echo htmlspecialchars($rol['descripcion']); ?>
          </a>
        </li>
      <?php } ?>
    </ul>

    <!-- Buscador -->
    <div class="row mb-3 align-items-center">
      <div class="col-md-6">
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar usuario, rol o estado...">
      </div>
      <div class="col-md-6 text-md-end pt-2 pt-md-0">
        <small class="text-muted" id="resultInfo"></small>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table id="userTable" class="table table-striped table-hover table-bordered align-middle">
        <thead class="table-dark">
          <tr>
            <th style="width: 80px;">ID</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th style="width: 140px;">Estado</th>
            <th style="width: 260px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($fila = $resultado->fetch_assoc()) { ?>
            <tr data-rol="<?php echo (int)$fila['id_rol']; ?>">
              <td><?php echo (int)$fila['id_usuario']; ?></td>

              <td><?php echo htmlspecialchars($fila['usuario']); ?></td>

              <td><?php echo htmlspecialchars($fila['rol']); ?></td>

              <td>
                <?php if ((int)$fila['estado'] === 1) { ?>
                  <span class="badge text-bg-success">Activo</span>
                <?php } else { ?>
                  <span class="badge text-bg-danger">Bloqueado</span>
                <?php } ?>
              </td>

              <td>
                <div class="d-flex gap-2 flex-wrap">
                  <!-- Editar -->
                  <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal"
                    data-id="<?php echo (int)$fila['id_usuario']; ?>"
                    data-usuario="<?php echo htmlspecialchars($fila['usuario']); ?>"
                    data-idrol="<?php echo (int)$fila['id_rol']; ?>"
                  >
                    Editar
                  </button>

                  <!-- Bloquear / Activar -->
                  <button
                    class="btn btn-<?php echo ((int)$fila['estado'] === 1) ? 'secondary' : 'success'; ?> btn-sm"
                    onclick="cambiarEstado(<?php echo (int)$fila['id_usuario']; ?>, <?php echo (int)$fila['estado']; ?>)"
                  >
                    <?php echo ((int)$fila['estado'] === 1) ? 'Bloquear' : 'Activar'; ?>
                  </button>
                </div>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal editar -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Editar Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <form action="actualizarUsuario.php" method="POST" id="editForm">
            <input type="hidden" name="id" id="userId">

            <div class="mb-3">
              <label class="form-label">Usuario:</label>
              <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Rol:</label>
              <select name="id_rol" id="id_rol" class="form-select" required>
                <?php foreach ($roles as $rol) { ?>
                  <option value="<?php echo (int)$rol['id']; ?>">
                    <?php echo htmlspecialchars($rol['descripcion']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <hr>

            <div class="mb-3">
              <label class="form-label">Cambiar contraseña (opcional):</label>
              <input type="password" name="password" id="password" class="form-control" placeholder="Nueva contraseña">
            </div>

            <div class="mb-3">
              <label class="form-label">Confirmar contraseña:</label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirmar nueva contraseña">
            </div>

            <button type="submit" class="btn btn-primary w-100">Actualizar</button>
          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- jQuery una sola vez -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <!-- Bootstrap 5 Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

  <script>
    // Estado actual de filtros
    var filtroRolActual = "all";

    function aplicarFiltros() {
      var texto = ($('#searchInput').val() || '').toLowerCase();
      var visibles = 0;

      $('#userTable tbody tr').each(function () {
        var rolFila = $(this).data('rol');
        var coincideRol = (filtroRolActual === "all" || rolFila == filtroRolActual);

        var coincideTexto = true;
        if (texto.length) {
          coincideTexto = $(this).text().toLowerCase().indexOf(texto) > -1;
        }

        if (coincideRol && coincideTexto) {
          $(this).show();
          visibles++;
        } else {
          $(this).hide();
        }
      });

      $('#resultInfo').text(visibles + ' usuario(s) mostrado(s)');
    }

    function cambiarEstado(id, estado) {
      if (estado == 1) {
        if (!confirm("¿Seguro que quieres bloquear este usuario?")) return;
      }

      var nuevoEstado = (estado == 1) ? 0 : 1;

      $.ajax({
        url: 'bloquearusuario.php',
        method: 'POST',
        data: { id: id, estado: nuevoEstado },
        success: function(response) {
          location.reload();
        },
        error: function() {
          alert('Error al cambiar el estado del usuario.');
        }
      });
    }

    $(document).ready(function () {
      // Inicial
      aplicarFiltros();

      // Tabs por rol
      $('#userTabs .nav-link').on('click', function(e){
        e.preventDefault();
        $('#userTabs .nav-link').removeClass('active');
        $(this).addClass('active');

        filtroRolActual = $(this).data('rol');
        aplicarFiltros();
      });

      // Buscador
      $('#searchInput').on('keyup', function () {
        aplicarFiltros();
      });

      // Cargar datos en modal (Bootstrap 5)
      var editModalEl = document.getElementById('editModal');
      editModalEl.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;

        var id = button.getAttribute('data-id');
        var usuario = button.getAttribute('data-usuario');
        var idrol = button.getAttribute('data-idrol');

        $('#userId').val(id);
        $('#usuario').val(usuario);
        $('#id_rol').val(idrol);

        // Limpia passwords
        $('#password').val('');
        $('#confirm_password').val('');
      });

      // Validación simple de contraseña (front)
      $('#editForm').on('submit', function(e){
        var p = $('#password').val();
        var c = $('#confirm_password').val();

        if (p.length > 0 && p !== c) {
          e.preventDefault();
          alert('Las contraseñas no coinciden.');
        }
      });
    });
  </script>
</body>

</html>
