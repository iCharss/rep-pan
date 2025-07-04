<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$productId = $_GET['id'] ?? 0;

try {
    // Eliminar producto
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$productId]);
    
    echo json_encode(['success' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar producto: ' . $e->getMessage()]);
}
?>