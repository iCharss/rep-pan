<?php
include '../app/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion, precio, stock, imagen, categoria, 
          tipo_variante, capacidades 
          FROM maquinas");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar imágenes y capacidades para asegurar formato consistente
    $productos = array_map(function($producto) {
        // Procesar imágenes
        if (!empty($producto['imagen'])) {
            if (is_string($producto['imagen'])) {
                // Intentar decodificar JSON, si falla crear array con el valor
                $decoded = json_decode($producto['imagen'], true);
                $producto['imagen'] = ($decoded !== null) ? $decoded : [$producto['imagen']];
            }
            // Asegurar que siempre sea array
            if (!is_array($producto['imagen'])) {
                $producto['imagen'] = [];
            }
        } else {
            $producto['imagen'] = [];
        }
        
        // Procesar capacidades (mantenemos tu lógica original)
        if (!empty($producto['capacidades'])) {
            if (is_string($producto['capacidades'])) {
                $producto['capacidades'] = json_decode($producto['capacidades'], true);
            }
            if (!is_array($producto['capacidades'])) {
                $producto['capacidades'] = [];
            }
        } else {
            $producto['capacidades'] = [];
        }
        
        return $producto;
    }, $productos);
    
    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Error al obtener productos",
        "details" => $e->getMessage()
    ]);
}
?>