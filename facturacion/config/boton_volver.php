<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    .btn-volver-flotante {
        /* Posicionamiento Fijo */
        position: fixed;
        top: 85px; /* Ajustado para que quede debajo del navbar (que tiene 58-75px aprox) */
        left: 20px;
        z-index: 999; /* Para que siempre esté por encima del contenido */

        /* Diseño Circular y Pequeño */
        display: flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        
        font-family: "Poppins", sans-serif;
        font-size: 18px; /* Tamaño de la flecha */
        font-weight: 600;

        border-radius: 50%; /* Círculo perfecto */
        border: none;
        background-color: #00000064; /* Tu azul original */
        color: #ffffff;

        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Sombra para que resalte al flotar */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Hover: Cambio a rojo y efecto de elevación */
    .btn-volver-flotante:hover {
        background-color: #DF3456; /* Tu rojo corporativo */
        transform: scale(1.1); /* Crece un poquito */
        box-shadow: 0 6px 15px rgba(223, 52, 86, 0.3);
    }

    .btn-volver-flotante:active {
        transform: scale(0.95);
    }

    /* Ocultar texto en pantallas pequeñas si fuera necesario, 
       pero aquí ya lo quitamos del HTML directamente */
</style>

<button onclick="window.history.back();" class="btn-volver-flotante" title="Volver">
    <i class="fas fa-arrow-left"></i>
</button>