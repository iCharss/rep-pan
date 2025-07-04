<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$machineId = $_GET['id'] ?? null;

if (!$machineId) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de producto no proporcionado']));
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion, categoria, tipo_variante, capacidades, imagen FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$maquina) {
        http_response_code(404);
        die(json_encode(['error' => 'Máquina no encontrada']));
    }
    
    // Procesar capacidades e imágenes
    $maquina['capacidades'] = !empty($maquina['capacidades']) ? 
        (is_string($maquina['capacidades']) ? json_decode($maquina['capacidades'], true) : $maquina['capacidades']) : 
        [];
    
    $maquina['imagen'] = !empty($maquina['imagen']) ? json_decode($maquina['imagen'], true) : [];
    
    echo json_encode($maquina);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener máquina: ' . $e->getMessage()]);
}
?>