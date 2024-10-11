<?php
// Conexión a la base de datos
include 'conexion.php'; 



// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para obtener los datos de la tabla tbl_informepesos
$sql = "SELECT id, fecha, precio_venta_usd FROM tbl_informepesos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Iterar sobre los resultados
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $fecha = $row["fecha"];
        $precioVentaUSD = $row["precio_venta_usd"];

        // Consulta SQL para obtener la TRM de la tabla tbl_trm para la fecha actual
        $trmSql = "SELECT trm FROM tbl_trm WHERE fecha = ?";
        $stmt = $conn->prepare($trmSql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $trmResult = $stmt->get_result();

        if ($trmResult->num_rows > 0) {
            $trmRow = $trmResult->fetch_assoc();
            $trm = $trmRow["trm"];

            // Calcular el precio de venta en COP
            $totalpesos = $precioVentaUSD * $trm;

            // Actualizar la tabla tbl_informepesos con el precio de venta en COP y la TRM
            $updateSql = "UPDATE tbl_informepesos SET totalpesos = ?, trm = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ddi", $totalpesos, $trm, $id);
            $updateStmt->execute();

            echo "Fecha: $fecha, Precio USD: $precioVentaUSD, TRM: $trm, Total Pesos: $totalpesos\n";
        } else {
            echo "No se encontró la TRM para la fecha: $fecha\n";
        }

        $stmt->close();
    }
} else {
    echo "No se encontraron resultados.";
}

$conn->close();
?>
