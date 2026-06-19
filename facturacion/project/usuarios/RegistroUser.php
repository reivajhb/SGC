<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>Registro de Usuario Moderno</title>

    <style>
        /* Degradado de fondo vibrante y moderno */
        .gradient-custom {
            background: #0f2027;
            background: -webkit-linear-gradient(to right, #2c5364, #203a43, #0f2027);
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
        }

        /* Tarjeta con efecto Glassmorphism (Cristal) */
        .glass-card {
            background: rgba(33, 37, 41, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Estilización de los inputs */
        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #e0e0e0;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #a0a0a0;
        }

        .form-control, .form-control:focus {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff !important;
            transition: all 0.3s ease;
        }

        /* Estado activo o seleccionado de los campos */
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #54a0ff;
            box-shadow: 0 0 8px rgba(84, 160, 255, 0.5);
        }

        /* Estilo para las opciones del Select */
        select option {
            background-color: #212529;
            color: #fff;
        }

        /* Animación suave para el botón */
        .btn-custom {
            background: transparent;
            border: 2px solid #fff;
            color: #fff;
            font-weight: 600;
            transition: all 0.4s ease;
        }

        .btn-custom:hover {
            background: #fff;
            color: #0f2027;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Iconos de Redes Sociales */
        .social-icon {
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.6);
        }

        .social-icon:hover {
            color: #fff;
            transform: scale(1.2);
        }
    </style>
</head>
<body>

<section class="vh-100 gradient-custom" style="overflow-y: auto;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-9 col-lg-7 col-xl-6">
        
        <div class="card text-white shadow-lg glass-card my-4" style="border-radius: 1.5rem;">
          <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
              <h2 class="fw-bold text-uppercase" style="letter-spacing: 1px;">Crear Cuenta</h2>
              <p class="text-white-50">Ingresa tus datos para registrarte en la plataforma</p>
            </div>

            <form action="RegistroNew.php" method="post">
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="typeEmailX">Usuario*</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                      <input name="usuario" type="text" id="typeEmailX" class="form-control" required />
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label for="typeNombreX">Nombre completo*</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                      <input name="nombre" type="text" id="typeNombreX" class="form-control" required />
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label for="typePasswordX">Contraseña*</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                      <input name="contraseña" type="password" id="typePasswordX" class="form-control" required />
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label for="id_rol">Rol*</label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user-tag"></i></span></div>
                      <select name="id_rol" id="id_rol" class="form-control" required>
                        <?php
                        include "../../config/conexion.php"; 
                        $consulta = "SELECT * FROM tbl_roles ";
                        $ejecutar = mysqli_query($conn,$consulta);
                        
                        foreach ($ejecutar as $opciones): ?>
                          <option value="<?php echo $opciones['id']?>"><?php echo $opciones['descripcion']?></option>
                        <?php endforeach ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="typeCorreoX">Correo electrónico <small class="text-white-50">(opcional)</small></label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                      <input name="correo" type="email" id="typeCorreoX" class="form-control" />
                    </div>
                  </div>

                  <div class="form-group mb-3">
                    <label for="typeTelefonoX">Teléfono <small class="text-white-50">(opcional)</small></label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                      <input name="telefono" type="text" id="typeTelefonoX" class="form-control" />
                    </div>
                  </div>

                  <div class="form-group mb-4">
                    <label for="typeDireccionX">Dirección <small class="text-white-50">(opcional)</small></label>
                    <div class="input-group">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                      <input name="direccion" type="text" id="typeDireccionX" class="form-control" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-center mt-3">
                <button class="btn btn-custom btn-lg px-5 w-100" type="submit">Registrarse</button>
              </div>

            </form>

            <div class="d-flex justify-content-center text-center mt-4 pt-2">
              <a href="#!" class="social-icon mx-3"><i class="fab fa-facebook-f fa-lg"></i></a>
              <a href="#!" class="social-icon mx-3"><i class="fab fa-twitter fa-lg"></i></a>
              <a href="#!" class="social-icon mx-3"><i class="fab fa-google fa-lg"></i></a>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>