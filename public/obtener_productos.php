<?php
include '../app/config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM productos");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar imágenes para asegurar formato consistente
    $productos = array_map(function($producto) {
        if (!empty($producto['imagen'])) {
            // Si es string, convertir a array
            if (is_string($producto['imagen'])) {
                $producto['imagen'] = json_decode($producto['imagen'], true) ?: [$producto['imagen']];
            }
            // Si no es array, establecer como array vacío
            if (!is_array($producto['imagen'])) {
                $producto['imagen'] = [];
            }
        } else {
            $producto['imagen'] = [];
        }
        return $producto;
    }, $productos);
    
    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener productos: " . $e->getMessage()]);
}
?>