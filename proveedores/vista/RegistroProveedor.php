<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro de Proveedor</title>

  <link rel="icon" type="image/x-icon" href="/facturacion/img/favicon.jpg">
  <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    body,
    html {
      height: 100%;
      margin: 0;
      font-family: 'Roboto', sans-serif;
    }

    .bg-cover {
      background: url('../../img/fondo.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      border-radius: 1rem;
      background-color: #ffffff;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
      padding: 2rem;
    }

    .login-card h3 {
      font-weight: 700;
      color: #0044cc;
    }

    .login-card p {
      color: #555;
    }

    .form-control,
    .form-select {
      border-radius: 0.5rem;
      border: 1px solid #ccc;
      padding: 12px;
      color: #333;
      background-color: #ffffff;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #0044cc;
      box-shadow: 0 0 0 0.2rem rgba(0, 68, 204, .25);
      color: #333;
      background-color: #ffffff;
    }

    .form-label {
      font-size: 0.9rem;
      color: #333;
    }

    .btn-custom {
      background: linear-gradient(135deg, #0044cc, #003399);
      border: none;
      color: #fff;
      font-weight: 600;
      border-radius: 0.5rem;
      transition: all 0.3s ease-in-out;
    }

    .btn-custom:hover {
      background: linear-gradient(135deg, #003399, #002266);
      transform: scale(1.02);
      color: #fff;
    }

    .input-password-wrap {
      position: relative;
    }

    .input-password-wrap .form-control {
      padding-right: 45px;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
      font-size: 1.1rem;
      user-select: none;
      line-height: 1;
    }

    .toggle-password:hover {
      color: #0044cc;
    }

    .brand-logo {
      width: 72px;
      height: 72px;
      object-fit: contain;
      border-radius: .5rem;
      background: #ffffff;
    }

    .registro-link {
      color: #0044cc;
      font-weight: 600;
      text-decoration: none;
    }

    .registro-link:hover {
      color: #003399;
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="bg-cover">
    <div class="container">
      <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
          <div class="login-card">

            <div class="text-center mb-4">
              <img src="../../img/pnv.png" class="brand-logo mb-3" alt="PDV" />
              <h3 class="fw-bold mb-1">Registro de Proveedor</h3>
              <p class="text-muted mb-0">Completa el formulario para crear tu cuenta</p>
            </div>

            <form action="../controlador/RegistroNewProveedor.php" method="POST">
              <div class="row">

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="usuario">NIT / Usuario</label>
                    <input name="usuario" type="number" class="form-control" id="usuario" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="contraseña">Contraseña</label>
                    <div class="input-password-wrap">
                      <input name="contraseña" type="password" class="form-control" id="contraseña" required>
                      <span class="toggle-password" onclick="togglePassword()" aria-label="Mostrar u ocultar contraseña">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="nombre">Nombre del Hotel</label>
                    <input name="nombre" type="text" class="form-control" id="nombre" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="correo">Correo</label>
                    <input name="correo" type="email" class="form-control" id="correo" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input name="telefono" type="tel" class="form-control" id="telefono" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label class="form-label" for="direccion">Dirección</label>
                    <input name="direccion" type="text" class="form-control" id="direccion" required>
                  </div>
                </div>

                <!-- Roles -->
                <?php
                include '../../facturacion/config/conexion.php';
                $consulta = "SELECT * FROM tbl_roles WHERE id IN (6, 7)";
                $ejecutar = mysqli_query($conn, $consulta);
                ?>

                <div class="col-12">
                  <div class="form-group mb-3">
                    <label class="form-label" for="id_rol">Selecciona tu Rol</label>
                    <select name="id_rol" id="id_rol" class="form-control" required>
                      <option value="" disabled selected>-- Elige un rol --</option>
                      <?php while ($opcion = mysqli_fetch_assoc($ejecutar)): ?>
                        <option value="<?php echo $opcion['id']; ?>">
                          <?php echo htmlspecialchars($opcion['descripcion']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>

                <div class="col-12 text-center pt-2">
                  <button type="submit" class="btn btn-custom btn-block mt-3">
                    Registrarse
                  </button>
                </div>

              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById("contraseña");
      const eyeIcon = document.getElementById("eyeIcon");

      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove("bi-eye");
        eyeIcon.classList.add("bi-eye-slash");
      } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("bi-eye-slash");
        eyeIcon.classList.add("bi-eye");
      }
    }
  </script>
</body>

</html>