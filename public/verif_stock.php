<?php
header("Content-Type: application/json");

// Incluir la conexión a la base de datos
require_once("../app/config.php");

// Decodificar JSON recibido
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id'])) {
    echo json_encode(["success" => false, "message" => "ID no recibido"]);
    exit;
}

$id = intval($input['id']); 
$categoria = $input['category'] ?? null; // Categoría ahora es opcional
$variante = $input['variante'] ?? null; // Nueva variable para la variante

try {
    // Buscar en la tabla "maquinas" primero
    $stmt = $pdo->prepare("SELECT stock, capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Si tiene capacidades (es un producto con variantes)
        if (!empty($row['capacidades'])) {
            $capacidades = json_decode($row['capacidades'], true);
            
            // Si se especificó una variante, buscar esa capacidad específica
            if ($variante && is_array($capacidades)) {
                foreach ($capacidades as $capacidad) {
                    if ($capacidad['valor'] === $variante) {
                        echo json_encode([
                            "success" => true, 
                            "stock" => intval($capacidad['stock']),
                            "precio" => floatval($capacidad['precio'])
                        ]);
                        exit;
                    }
                }
                // Si no encontró la variante
                echo json_encode(["success" => false, "message" => "Variante no encontrada"]);
                exit;
            }
            
            // Si no se especificó variante, devolver el stock general
            echo json_encode(["success" => true, "stock" => intval($row['stock'])]);
            exit;
        }
        
        // Producto sin variantes
        echo json_encode(["success" => true, "stock" => intval($row['stock'])]);
        exit;
    }
    
    // Si no encontró en maquinas, buscar en productos (solo si se especificó categoría)
    if ($categoria) {
        $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(["success" => true, "stock" => intval($row['stock'])]);
            exit;
        }
    }

    // Si no encontró en ninguna tabla
    echo json_encode(["success" => false, "message" => "No se encontró el producto o máquina"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
}