<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$machineId = $_POST['machine_id'] ?? 0;
$valor = $_POST['valor'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;

try {
    // Validar datos
    if (empty($valor) || empty($machineId)) {
        throw new Exception("Datos incompletos");
    }
    
    // Obtener capacidades actuales
    $stmt = $pdo->prepare("SELECT capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch();
    
    if (!$maquina) {
        throw new Exception("Máquina no encontrada");
    }
    
    $capacidades = json_decode($maquina['capacidades'], true) ?: [];
    
    // Verificar si ya existe la capacidad
    $existe = false;
    foreach ($capacidades as &$capacidad) {
        if ($capacidad['valor'] === $valor) {
            $capacidad['precio'] = floatval($precio);
            $capacidad['stock'] = intval($stock);
            $existe = true;
            break;
        }
    }
    
    if (!$existe) {
        $capacidades[] = [
            'valor' => $valor,
            'precio' => floatval($precio),
            'stock' => intval($stock)
        ];
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