<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$machineId = $_GET['machine_id'] ?? 0;
$capacityValue = $_GET['value'] ?? '';

try {
    // Obtener capacidades actuales
    $stmt = $pdo->prepare("SELECT capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch();
    
    if (!$maquina) {
        throw new Exception("Máquina no encontrada");
    }
    
    $capacidades = json_decode($maquina['capacidades'], true) ?: [];
    
    // Filtrar la capacidad a eliminar
    $nuevasCapacidades = array_filter($capacidades, function($capacidad) use ($capacityValue) {
        return $capacidad['valor'] !== $capacityValue;
    });
    
    // Reindexar el array
    $nuevasCapacidades = array_values($nuevasCapacidades);
    
    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE maquinas SET capacidades = ? WHERE id = ?");
    $stmt->execute([json_encode($nuevasCapacidades), $machineId]);
    
    echo json_encode(['success' => true, 'remaining' => count($nuevasCapacidades)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>