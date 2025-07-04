<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$machineId = $_POST['machine_id'] ?? 0;
$originalValue = $_POST['original_value'] ?? '';

try {
    // Validar datos obligatorios
    if (empty($_POST['valor']) || !isset($_POST['precio']) || !isset($_POST['stock'])) {
        throw new Exception("Todos los campos son obligatorios");
    }
    
    // Obtener capacidades actuales
    $stmt = $pdo->prepare("SELECT capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch();
    
    if (!$maquina) {
        throw new Exception("Máquina no encontrada");
    }
    
    $capacidades = json_decode($maquina['capacidades'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar capacidades: " . json_last_error_msg());
    }
    
    $capacidades = $capacidades ?: [];
    
    // Buscar y actualizar la capacidad
    $updated = false;
    foreach ($capacidades as &$capacidad) {
        if ($capacidad['valor'] === $originalValue) {
            $capacidad['valor'] = $_POST['valor'];
            $capacidad['precio'] = floatval($_POST['precio']);
            $capacidad['stock'] = intval($_POST['stock']);
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        throw new Exception("Capacidad no encontrada");
    }
    
    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE maquinas SET capacidades = ? WHERE id = ?");
    $stmt->execute([json_encode($capacidades), $machineId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>