<?php 
include "seguridad.php"
?>


<?php 
include "header.php"

?>


<!doctype html>


<html lang="en">

<html lang="en">

<div class="sidebar-content">
      <div class="sidebar-brand">
        
        <div id="close-sidebar">
          
        </div>
      </div>

      
            <strong><?php $user =  $_SESSION['usuario'] ?></strong>
          </span>
<br>
<br><br>
<br><br>
<br><br>
<br><br>
<br>


  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- SCRIPTS JS-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="peticion.js"></script>



    <title>Facturacion Clientes Corporativos!</title>

  </head>
  <body background="img/fondoindex.jpg">

<main class="container">
  <section class="card card-red">
    <div class="product-image">
      <img src="img/hotel.jpg" alt="OFF-white Red Edition" draggable="false" />
    </div>
    <div class="product-info">
      <h2>Facturas </h2>
      <p>Holes Alojamientos y Alimentación</p>
    </div>
    <div class="btn">
      <button class="buy-btn" onclick="location.href='consultaFacturasHAAPublic.php'">Consultar</button>
      <button class="fav">
        <?xml version="1.0" encoding="iso-8859-1"?>
        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
        <svg fill="#000000"  height="25px" width="25px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
           viewBox="0 0 512 512" xml:space="preserve">
        <g>
          <g>
            <g>
              <path d="M393.65,72.743h-46.809V16.624C346.841,7.443,339.398,0,330.217,0H181.783c-9.181,0-16.624,7.443-16.624,16.624v56.119
                H118.35c-9.181,0-16.624,7.443-16.624,16.624v406.009c0,9.181,7.443,16.624,16.624,16.624h275.3
                c9.181,0,16.624-7.443,16.624-16.624V89.367C410.274,80.186,402.832,72.743,393.65,72.743z M198.407,33.248h115.185v79.849
                H198.407V33.248z M290.521,478.752h-69.04v-39.908h69.04V478.752z M323.768,478.752V422.22c0-9.181-7.443-16.624-16.624-16.624
                H204.855c-9.181,0-16.624,7.443-16.624,16.624v56.532h-53.258V105.991h30.185v23.732c0,9.181,7.443,16.624,16.624,16.624h148.434
                c9.181,0,16.624-7.443,16.624-16.624v-23.732h30.185v372.761H323.768z"/>
              <path d="M193.937,161.038c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                s16.624-7.443,16.624-16.624v-21.057C210.561,168.481,203.118,161.038,193.937,161.038z"/>
              <path d="M256,161.038c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                s16.624-7.443,16.624-16.624v-21.057C272.624,168.481,265.181,161.038,256,161.038z"/>
              <path d="M318.063,161.038c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                c9.181,0,16.624-7.443,16.624-16.624v-21.057C334.688,168.481,327.244,161.038,318.063,161.038z"/>
              <path d="M193.937,243.05c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                s16.624-7.443,16.624-16.624v-21.057C210.561,250.494,203.118,243.05,193.937,243.05z"/>
              <path d="M256,243.05c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624s16.624-7.443,16.624-16.624
                v-21.057C272.624,250.494,265.181,243.05,256,243.05z"/>
              <path d="M318.063,243.05c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                c9.181,0,16.624-7.443,16.624-16.624v-21.057C334.688,250.494,327.244,243.05,318.063,243.05z"/>
              <path d="M193.937,325.063c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                s16.624-7.443,16.624-16.624v-21.057C210.561,332.506,203.118,325.063,193.937,325.063z"/>
              <path d="M256,325.063c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                s16.624-7.443,16.624-16.624v-21.057C272.624,332.506,265.181,325.063,256,325.063z"/>
              <path d="M318.063,325.063c-9.181,0-16.624,7.443-16.624,16.624v21.057c0,9.181,7.443,16.624,16.624,16.624
                c9.181,0,16.624-7.443,16.624-16.624v-21.057C334.688,332.506,327.244,325.063,318.063,325.063z"/>
              <path d="M275.304,39.867c-9,0-16.312,7.157-16.598,16.088h-5.414c-0.286-8.93-7.597-16.088-16.598-16.088
                c-9.181,0-16.624,7.443-16.624,16.624v34.473h0.001c0,9.181,7.443,16.624,16.624,16.624c9.181,0,16.624-7.443,16.624-16.624
                v-1.761h5.36v1.761c0,9.181,7.443,16.624,16.624,16.624c9.181,0,16.624-7.443,16.624-16.624V56.491
                C291.928,47.31,284.485,39.867,275.304,39.867z"/>
            </g>
          </g>
        </g>
        </svg>
      </button>
    </div>
  </section>
  <section class="card card-blue">
    <div class="product-image">
      <img src="img/tiquetes.jpg" alt="OFF-white Blue Edition" draggable="false" />
    </div>
    <div class="product-info">
      <h2>Facturas</h2>
      <p>Tiquetes Aereos</p>
      
    </div>
    <div class="btn">
      <button class="buy-btn" onclick="location.href='consultaFacturasCorpPublic.php'">Consultar</button>
      <button class="fav">
       <?xml version="1.0" encoding="utf-8"?>

<!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
<svg  height="25px" width="25px" version="1.1" id="Uploaded to svgrepo.com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
   width="800px" height="800px" viewBox="0 0 32 32" xml:space="preserve">
<style type="text/css">
  .duotone_twee{fill:#555D5E;}
  .duotone_een{fill:#0B1719;}
  .st0{fill:#FFF9F9;}
  .st1{fill:#808080;}
</style>
<g>
  <path class="duotone_een" d="M22.648,27.845c0.391-0.587,0.957-1.225,0.9-1.91l-1.088-12.972c-0.072-0.863,0.666-1.527,4.526-7.141
    c0.537-0.78,0.002-1.366-0.808-0.808C20.572,8.869,19.9,9.613,19.037,9.54L6.065,8.452c-0.692-0.057-1.332,0.514-1.91,0.9
    C3.926,9.504,3.954,9.694,4.218,9.773l11.229,3.369c0.263,0.079,0.329,0.312,0.146,0.517l-6.225,6.987
    c-0.183,0.205-0.558,0.373-0.833,0.373H5.411c-0.662,0-1.399,0.667-0.812,0.992l3.034,1.677c0.241,0.133,0.546,0.439,0.68,0.68
    l1.677,3.034c0.319,0.577,0.992-0.153,0.992-0.812v-3.125c0-0.275,0.168-0.65,0.373-0.833l6.987-6.225
    c0.206-0.183,0.438-0.116,0.517,0.146l3.369,11.229C22.306,28.046,22.496,28.074,22.648,27.845z"/>
  <path class="duotone_twee" d="M14.464,8.153l2.451-2.451c0.194-0.194,0.513-0.194,0.707,0l0.707,0.707
    c0.194,0.194,0.194,0.513,0,0.707l-1.256,1.256L14.464,8.153z M12.921,8.024l0.782-0.782c0.194-0.194,0.194-0.513,0-0.707
    l-0.707-0.707c-0.194-0.194-0.513-0.194-0.707,0l-1.978,1.978L12.921,8.024z M25.59,12.85c-0.194-0.194-0.513-0.194-0.707,0
    l-1.319,1.319l0.219,2.609l2.514-2.514c0.194-0.194,0.194-0.513,0-0.707L25.59,12.85z M25.497,17.756
    c-0.194-0.194-0.513-0.194-0.707,0l-0.853,0.853l0.219,2.61l2.048-2.048c0.194-0.194,0.194-0.513,0-0.707L25.497,17.756z"/>
</g>
</svg>
      </button>
    </div>
  </section>
</main>

<style type="text/css">
  
  /*===== GOOGLE FONTS =====*/
@import url("https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap");

/*===== VARIABLES CSS =====*/
:root {
  --dark-color-lighten: #f2f5ff;
  --red-card: #E3E3E3;
  --blue-card: #E3E3E3;
  --black-card: #000000;
  --btn: #141414;
  --btn-hover: #3a3a3a;
  --text: #fbf7f7;
}

/*===== RESET =====*/
*,
::before,
::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  height: 100vh;
  width: 100vw;
  background-color: var(--dark-color-lighten);
  font-family: "Montserrat", sans-serif;
}
button {
  font-family: "Montserrat", sans-serif;
  display: inline-block;
  border: none;
  outline: none;
  border-radius: 0.2rem;
  color: var(--text);
  cursor: pointer;
}

a {
  text-decoration: none;
}

img {
  max-width: 100%;
  height: 100%;
  user-select: none;
}

/*===== CARD =====*/
.container {
  height: 100%;
  width: 850px;
  margin: auto;
  display: flex;
  align-items: center;
  justify-content: space-evenly;
}
.card {
  position: relative;
  padding: 1rem;
  width: 350px;
  height: 450px;
  box-shadow: -1px 15px 30px -12px rgb(32, 32, 32);
  border-radius: 0.9rem;
  background-color: var(--red-card);
  color: var(--text);
  cursor: pointer;
}

.card-blue {
  background: var(--blue-card);
}

.card-black {
  background: var(--blue-black);
}
.product-image {
  height: 230px;
  width: 100%;
  transform: translate(0, -1.5rem);
  transition: transform 500ms ease-in-out;
  filter: drop-shadow(5px 10px 15px rgba(8, 9, 13, 0.4));
}
.product-info {
  text-align: center;
}

.card:hover .product-image {
  
}

.product-info h2 {
  font-size: 1.4rem;
  font-weight: 600;
  color: #000000;
}
.product-info p {
  margin: 0.4rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #000000;
}
.price {
  font-size: 1.2rem;
  font-weight: 500;

}
.btn {
  display: flex;
  justify-content: space-evenly;
  align-items: center;
  margin-top: 0.8rem;
}
.buy-btn {
  background-color: var(--btn);
  padding: 0.6rem 3.5rem;
  font-weight: 600;
  font-size: 1rem;
  transition: 300ms ease;
}
.buy-btn:hover {
  background-color: var(--btn-hover);
}
.fav {
  box-sizing: border-box;
  background: #fff;
  padding: 0.5rem 0.5rem;
  border: 1px solid#000;
  display: grid;
  place-items: center;
}

.svg {
  height: 25px;
  width: 25px;
  fill: #fff;
  transition: all 500ms ease;
}

.fav:hover .svg {
  fill: #000;
}

@media screen and (max-width: 800px) {
  body {
    height: auto;
  }
  .container {
    padding: 2rem 0;
    width: 100%;
    flex-direction: column;
    gap: 3rem;
  }
}
</style>
    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
window.addEventListener('popstate', function(event) {
  history.pushState(null, null, window.location.pathname);
  history.pushState(null, null, window.location.pathname);
  }, false);
</script>
  </body>
  
  
</html>
