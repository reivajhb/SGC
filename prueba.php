<!DOCTYPE html>
<html>
<head>
    <style>
        /* Estilo para las filas con estado "Pendiente" */
        .pendiente-row {
            background-color: yellow; /* Cambiar el color como desees */
        }
    </style>
</head>
<body>

<table id="miTabla">
    <tr>
        <th>Nombre</th>
        <th>Estado</th>
    </tr>
    <tr>
        <td>Elemento 1</td>
        <td>Pendiente</td>
    </tr>
    <tr>
        <td>Elemento 2</td>
        <td>Completado</td>
    </tr>
    <tr>
        <td>Elemento 3</td>
        <td>Pendiente</td>
    </tr>
</table>

<script>
    // Obtener la tabla
    var tabla = document.getElementById('miTabla');

    // Obtener todas las filas de la tabla excepto la primera (cabecera)
    var filas = tabla.getElementsByTagName('tr');

    // Recorrer las filas y cambiar el color de la fila si contiene "Pendiente"
    for (var i = 1; i < filas.length; i++) {
        var celdaEstado = filas[i].getElementsByTagName('td')[1]; // Segunda celda (índice 1)

        if (celdaEstado.innerHTML === 'Pendiente') {
            filas[i].classList.add('pendiente-row'); // Aplicar la clase CSS a la fila
        }
    }
</script>

</body>
</html>