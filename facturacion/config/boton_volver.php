<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    .btn-volver-flotante {
        /* Posicionamiento Fijo */
        position: fixed;
        top: 85px; /* Ajustado para que quede debajo del navbar (que tiene 58-75px aprox) */
        left: 20px;
        z-index: 999; /* Para que siempre estÃ© por encima del contenido */

        /* DiseÃ±o Circular y PequeÃ±o */
        display: flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        
        font-family: "Poppins", sans-serif;
        font-size: 18px; /* TamaÃ±o de la flecha */
        font-weight: 600;

        border-radius: 50%; /* CÃ­rculo perfecto */
        border: none;
        background-color: #00000064; /* Tu azul original */
        color: #ffffff;

        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Sombra para que resalte al flotar */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Hover: Cambio a rojo y efecto de elevaciÃ³n */
    .btn-volver-flotante:hover {
        background-color: #DF3456; /* Tu rojo corporativo */
        transform: scale(1.1); /* Crece un poquito */
        box-shadow: 0 6px 15px rgba(223, 52, 86, 0.3);
    }

    .btn-volver-flotante:active {
        transform: scale(0.95);
    }

    .btn-volver-flotante:disabled,
    .btn-volver-flotante.is-disabled {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
        transform: none !important;
        box-shadow: none;
        background-color: #6c757d;
    }

    /* Ocultar texto en pantallas pequeÃ±as si fuera necesario, 
       pero aquÃ­ ya lo quitamos del HTML directamente */
</style>

<button type="button" id="btnVolverFlotante" onclick="if (!this.disabled) window.history.back();" class="btn-volver-flotante" title="Volver">
    <i class="fas fa-arrow-left"></i>
</button>

<script>
(function () {
    const btnVolver = document.getElementById('btnVolverFlotante');
    if (!btnVolver) return;

    const overlaySelector = [
        '#loading-overlay',
        '.loading-overlay',
        '.overlay',
        '[id*="overlay" i]',
        '[class*="overlay" i]',
        '[data-overlay]'
    ].join(',');

    function isOverlayVisible(el) {
        if (!el || el === btnVolver || btnVolver.contains(el)) return false;
        if (el.closest('.modal-backdrop')) return true;

        const text = `${el.id || ''} ${el.className || ''}`.toLowerCase();
        const looksLikeOverlay = text.includes('overlay') || el.hasAttribute('data-overlay');
        if (!looksLikeOverlay) return false;

        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity || '1') === 0) {
            return false;
        }

        return el.classList.contains('active')
            || el.classList.contains('show')
            || style.position === 'fixed'
            || style.position === 'absolute'
            || el.getAttribute('aria-hidden') === 'false';
    }

    function actualizarBotonVolver() {
        const hayOverlayActivo = Array.from(document.querySelectorAll(overlaySelector)).some(isOverlayVisible)
            || document.body.classList.contains('modal-open');

        btnVolver.disabled = hayOverlayActivo;
        btnVolver.classList.toggle('is-disabled', hayOverlayActivo);
        btnVolver.setAttribute('aria-disabled', hayOverlayActivo ? 'true' : 'false');
    }

    window.toggleBotonVolverOverlay = function (disabled) {
        btnVolver.disabled = !!disabled;
        btnVolver.classList.toggle('is-disabled', !!disabled);
        btnVolver.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    };

    const observer = new MutationObserver(actualizarBotonVolver);
    observer.observe(document.documentElement, {
        attributes: true,
        childList: true,
        subtree: true,
        attributeFilter: ['class', 'style', 'aria-hidden']
    });

    document.addEventListener('DOMContentLoaded', actualizarBotonVolver);
    window.addEventListener('load', actualizarBotonVolver);
    actualizarBotonVolver();
})();
</script>