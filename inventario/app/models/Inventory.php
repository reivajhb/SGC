<?php
// app/models/Inventory.php

class Inventory
{
    private $conn;
    private $table = 'tbl_inventarioeqc';

    public function __construct($db)
    {
        $this->conn = $db;
    }



    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $query = "UPDATE " . $this->table . " SET
            nombre_equipo = :nombre_equipo,
            numero_serie = :numero_serie,
            sistema_operativo_nombre = :sistema_operativo_nombre,
            serial_windows = :serial_windows,
            version_office = :version_office,
            serial_office = :serial_office,
            localizaciones = :localizaciones,
            usuario = :usuario,
            area = :area,
            licencia_win10_verificada = :licencia_win10_verificada,
            licencia_office_verificada = :licencia_office_verificada,
            softwarenolicenciado = :softwarenolicenciado,
            marquilla_licencia = :marquilla_licencia,
            marquilla_pc = :marquilla_pc,
            inventario_cpu = :inventario_cpu,
            inventario_monitor = :inventario_monitor,
            observaciones = :observaciones
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':nombre_equipo', $data['nombre_equipo']);
        $stmt->bindParam(':numero_serie', $data['numero_serie']);
        $stmt->bindParam(':sistema_operativo_nombre', $data['sistema_operativo_nombre']);
        $stmt->bindParam(':serial_windows', $data['serial_windows']);
        $stmt->bindParam(':version_office', $data['version_office']);
        $stmt->bindParam(':serial_office', $data['serial_office']);
        $stmt->bindParam(':localizaciones', $data['localizaciones']);
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':licencia_win10_verificada', $data['licencia_win10_verificada']);
        $stmt->bindParam(':licencia_office_verificada', $data['licencia_office_verificada']);
        $stmt->bindParam(':softwarenolicenciado', $data['softwarenolicenciado']);
        $stmt->bindParam(':marquilla_licencia', $data['marquilla_licencia']);
        $stmt->bindParam(':marquilla_pc', $data['marquilla_pc']);
        $stmt->bindParam(':inventario_cpu', $data['inventario_cpu']);
        $stmt->bindParam(':inventario_monitor', $data['inventario_monitor']);
        $stmt->bindParam(':observaciones', $data['observaciones']);


        return $stmt->execute();
    }

    public function insert($data)
    {
        $query = "INSERT INTO " . $this->table . " (nombre_equipo, numero_serie, sistema_operativo_nombre, serial_windows, version_office, serial_office, localizaciones, usuario, area, licencia_win10_verificada, licencia_office_verificada, softwarenolicenciado, marquilla_licencia, marquilla_pc, inventario_cpu, inventario_monitor, observaciones) VALUES (:nombre_equipo, :numero_serie, :sistema_operativo_nombre, :serial_windows, :version_office, :serial_office, :localizaciones, :usuario, :area, :licencia_win10_verificada, :licencia_office_verificada, :softwarenolicenciado, :marquilla_licencia, :marquilla_pc, :inventario_cpu, :inventario_monitor, :observaciones)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre_equipo', $data['nombre_equipo']);
        $stmt->bindParam(':numero_serie', $data['numero_serie']);
        $stmt->bindParam(':sistema_operativo_nombre', $data['sistema_operativo_nombre']);
        $stmt->bindParam(':serial_windows', $data['serial_windows']);
        $stmt->bindParam(':version_office', $data['version_office']);
        $stmt->bindParam(':serial_office', $data['serial_office']);
        $stmt->bindParam(':localizaciones', $data['localizaciones']);
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':licencia_win10_verificada', $data['licencia_win10_verificada']);
        $stmt->bindParam(':licencia_office_verificada', $data['licencia_office_verificada']);
        $stmt->bindParam(':softwarenolicenciado', $data['softwarenolicenciado']);
        $stmt->bindParam(':marquilla_licencia', $data['marquilla_licencia']);
        $stmt->bindParam(':marquilla_pc', $data['marquilla_pc']);
        $stmt->bindParam(':inventario_cpu', $data['inventario_cpu']);
        $stmt->bindParam(':inventario_monitor', $data['inventario_monitor']);
        $stmt->bindParam(':observaciones', $data['observaciones']);


        return $stmt->execute();
    }

    public function getFiltered($filters)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";

        // Construir la consulta SQL con los filtros proporcionados
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key === 'licencia_win10_verificada' || $key === 'licencia_office_verificada' || $key === 'softwarenolicenciado') {
                    $query .= " AND $key = :$key";
                } else {
                    $query .= " AND $key LIKE :$key";
                }
            }
        }

        $stmt = $this->conn->prepare($query);
        $this->bindFilters($stmt, $filters);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindFilters($stmt, $filters)
    {
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key === 'licencia_win10_verificada' || $key === 'licencia_office_verificada' || $key === 'softwarenolicenciado') {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", "%$value%", PDO::PARAM_STR);
                }
            }
        }
    }
    public function getByArea($area)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE area = :area");
        $stmt->execute(['area' => $area]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }




}
?>