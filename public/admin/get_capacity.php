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
    $stmt = $pdo->prepare("SELECT capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch();
    
    if (!$maquina) {
        http_response_code(404);
        die(json_encode(['error' => 'Máquina no encontrada']));
    }
    
    $capacidades = !empty($maquina['capacidades']) ? 
        (is_string($maquina['capacidades']) ? json_decode($maquina['capacidades'], true) : $maquina['capacidades']) : 
        [];
    
    $capacidadEncontrada = null;
    foreach ($capacidades as $capacidad) {
        if ($capacidad['valor'] === $capacityValue) {
            $capacidadEncontrada = $capacidad;
            break;
        }
    }
    
    if (!$capacidadEncontrada) {
        http_response_code(404);
        die(json_encode(['error' => 'Capacidad no encontrada']));
    }
    
    echo json_encode($capacidadEncontrada);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener capacidad: ' . $e->getMessage()]);
}
?>