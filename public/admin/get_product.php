<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$productId = $_GET['id'] ?? null;

if (!$productId) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de producto no proporcionado']));
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion, categoria, precio, stock, imagen FROM productos WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        die(json_encode(['error' => 'Producto no encontrado']));
    }
    
    // Decodificar las imágenes
    $product['imagen'] = !empty($product['imagen']) ? json_decode($product['imagen'], true) : [];
    
    echo json_encode($product);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
}
?>