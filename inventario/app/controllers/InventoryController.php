<?php
// app/controllers/InventoryController.php

require '../vendor/autoload.php'; // TCPDF
require_once __DIR__ . '/../models/Inventory.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;




class InventoryController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new Inventory($conn);
    }

    public function insert()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_equipo' => $_POST['nombre_equipo'],
                'numero_serie' => $_POST['numero_serie'],
                'sistema_operativo_nombre' => $_POST['sistema_operativo_nombre'],
                'serial_windows' => $_POST['serial_windows'],
                'version_office' => $_POST['version_office'],
                'serial_office' => $_POST['serial_office'],
                'localizaciones' => $_POST['localizaciones'],
                'usuario' => $_POST['usuario'],
                'area' => $_POST['area'],
                'licencia_win10_verificada' => isset($_POST['licencia_win10_verificada']) ? 1 : 0,
                'licencia_office_verificada' => isset($_POST['licencia_office_verificada']) ? 1 : 0,
                'softwarenolicenciado' => isset($_POST['softwarenolicenciado']) ? 1 : 0,
                'marquilla_licencia' => $_POST['marquilla_licencia'],
                'marquilla_pc' => $_POST['marquilla_pc'],
                'inventario_cpu' => $_POST['inventario_cpu'],
                'inventario_monitor' => $_POST['inventario_monitor'],
                'observaciones' => $_POST['observaciones']
            ];

            if ($this->model->insert($data)) {
                require __DIR__ . '/../views/inventory/success.php';
            } else {
                echo "Error: No se pudo insertar el registro";
            }
        } else {
            require __DIR__ . '/../views/inventory/insertardatoinv.php';
        }
    }

    public function showInventory()
    {
        // Obtener los filtros del formulario
        $filters = [
            'id' => $_GET['filter_id'] ?? '',
            'nombre_equipo' => $_GET['filter_nombre_equipo'] ?? '',
            'numero_serie' => $_GET['filter_numero_serie'] ?? '',
            'sistema_operativo_nombre' => $_GET['filter_sistema_operativo_nombre'] ?? '',
            'serial_windows' => $_GET['filter_serial_windows'] ?? '',
            'version_office' => $_GET['filter_version_office'] ?? '',
            'serial_office' => $_GET['filter_serial_office'] ?? '',
            'localizaciones' => $_GET['filter_localizaciones'] ?? '',
            'usuario' => $_GET['filter_usuario'] ?? '',
            'area' => $_GET['filter_area'] ?? '',
            'licencia_win10_verificada' => $_GET['filter_licencia_win10_verificada'] ?? '',
            'licencia_office_verificada' => $_GET['filter_licencia_office_verificada'] ?? '',
            'softwarenolicenciado' => $_GET['softwarenolicenciado'] ?? '',
            'marquilla_licencia' => $_GET['filter_marquilla_licencia'] ?? '',
            'marquilla_pc' => $_GET['filter_marquilla_pc'] ?? '',
            'inventario_cpu' => $_GET['filter_inventario_cpu'] ?? '',
            'inventario_monitor' => $_GET['filter_inventario_monitor'] ?? '',
            'observaciones' => $_GET['filter_observaciones'] ?? ''
        ];

        $inventoryItems = $this->model->getFiltered($filters); // Obtiene los elementos filtrados
        require __DIR__ . '/../views/inventory/consultarinventario.php'; // Muestra la vista con los datos filtrados
    }

    public function edit()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $item = $this->model->getById($id); // Obtiene el registro por ID
            if ($item) {
                require __DIR__ . '/../views/inventory/edit.php'; // Muestra la vista de edición
            } else {
                echo "Registro no encontrado.";
            }
        } else {
            echo "ID no especificado.";
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nombre_equipo' => $_POST['nombre_equipo'],
                'numero_serie' => $_POST['numero_serie'],
                'sistema_operativo_nombre' => $_POST['sistema_operativo_nombre'],
                'serial_windows' => $_POST['serial_windows'],
                'version_office' => $_POST['version_office'],
                'serial_office' => $_POST['serial_office'],
                'localizaciones' => $_POST['localizaciones'],
                'usuario' => $_POST['usuario'],
                'area' => $_POST['area'],
                'licencia_win10_verificada' => $_POST['licencia_win10_verificada'],
                'licencia_office_verificada' => $_POST['licencia_office_verificada'],
                'softwarenolicenciado' => $_POST['softwarenolicenciado'],
                'marquilla_licencia' => $_POST['marquilla_licencia'],
                'marquilla_pc' => $_POST['marquilla_pc'],
                'inventario_cpu' => $_POST['inventario_cpu'],
                'inventario_monitor' => $_POST['inventario_monitor'],
                'observaciones' => $_POST['observaciones']
            ];

            if ($this->model->update($data)) {
                echo "<script>
                    alert('Registro actualizado con éxito.');
                    window.location.href = '/facturacion/inventario/public/index.php?action=consultar';
                  </script>";
            } else {
                echo "<script>
                    alert('Error al actualizar el registro.');
                    window.location.href = '/facturacion/inventario/public/index.php?action=consultar';
                  </script>";
            }
        }
    }


    public function downloadExcel()
    {
        // Obtener los datos del inventario
        $inventoryItems = $this->model->getAll(); // Asegúrate de tener un método `getAll` en tu modelo

        // Crear una nueva instancia de Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer los encabezados de la hoja
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre del Equipo');
        $sheet->setCellValue('C1', 'Número de Serie');
        $sheet->setCellValue('D1', 'Sistema Operativo');
        $sheet->setCellValue('E1', 'Serial Windows');
        $sheet->setCellValue('F1', 'Versión Office');
        $sheet->setCellValue('G1', 'Serial Office');
        $sheet->setCellValue('H1', 'Localizaciones');
        $sheet->setCellValue('I1', 'Usuario');
        $sheet->setCellValue('J1', 'Área');
        $sheet->setCellValue('K1', 'Licencia Win10 Verificada');
        $sheet->setCellValue('L1', 'Licencia Office Verificada');
        $sheet->setCellValue('M1', 'Software Licenciado');
        $sheet->setCellValue('N1', 'Marquilla Licencia');
        $sheet->setCellValue('O1', 'Marquilla PC');
        $sheet->setCellValue('P1', 'Inventario CPU');
        $sheet->setCellValue('Q1', 'Inventario Monitor');

        // Agregar los datos
        $row = 2;
        foreach ($inventoryItems as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['nombre_equipo']);
            $sheet->setCellValue('C' . $row, $item['numero_serie']);
            $sheet->setCellValue('D' . $row, $item['sistema_operativo_nombre']);
            $sheet->setCellValue('E' . $row, $item['serial_windows']);
            $sheet->setCellValue('F' . $row, $item['version_office']);
            $sheet->setCellValue('G' . $row, $item['serial_office']);
            $sheet->setCellValue('H' . $row, $item['localizaciones']);
            $sheet->setCellValue('I' . $row, $item['usuario']);
            $sheet->setCellValue('J' . $row, $item['area']);
            $sheet->setCellValue('K' . $row, $item['licencia_win10_verificada']);
            $sheet->setCellValue('L' . $row, $item['licencia_office_verificada']);
            $sheet->setCellValue('M' . $row, $item['softwarenolicenciado']);
            $sheet->setCellValue('N' . $row, $item['marquilla_licencia']);
            $sheet->setCellValue('O' . $row, $item['marquilla_pc']);
            $sheet->setCellValue('P' . $row, $item['inventario_cpu']);
            $sheet->setCellValue('Q' . $row, $item['inventario_monitor']);
            $row++;
        }

        // Crear un escritor de Excel
        $writer = new Xlsx($spreadsheet);

        // Enviar el archivo Excel al navegador
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="inventario.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function showAll()
    {
        // Verifica que $this->model esté inicializado correctamente
        if ($this->model === null) {
            echo "Modelo de inventario no inicializado.";
            return;
        }

        // Obtener todos los datos del inventario
        $data = $this->model->getAll();

        // Verifica que $data tenga datos
        if ($data === false) {
            echo "Error al recuperar los datos.";
            return;
        }

        // Incluir la vista y pasar los datos
        include __DIR__ . '/../views/inventory/details.php';
    }

    // Método para mostrar el inventario filtrado
    public function filter_area()
    {
        $area = $_GET['filter_area'] ?? '';
        $data = !empty($area) ? $this->model->getByArea($area) : $this->model->getAll();
        include __DIR__ . '/../views/inventory/details.php';
    }
    // Método para generar PDF
   public function print_pdf()
    {
        $filter_area = $_GET['filter_area'] ?? '';
        $data = !empty($filter_area) ? $this->model->getByArea($filter_area) : $this->model->getAll();

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        // Capturar la vista mínima para PDF
        ob_start();
        require __DIR__ . '/../views/inventory/pdfs/labels_pdf.php';
        $html = ob_get_clean();

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('inventario.pdf', 'D'); // Forzar descarga
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if ($id && $this->model->delete($id)) {
                echo "<script>
                    alert('Registro eliminado con éxito.');
                    window.location.href = '/facturacion/inventario/public/index.php?action=consultar';
                  </script>";
            } else {
                echo "<script>
                    alert('Error al eliminar el registro.');
                    window.location.href = '/facturacion/inventario/public/index.php?action=consultar';
                  </script>";
            }
        }
    }

}









?>