<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

try {
    // Procesar imágenes
    $imagenes = [];
    if (!empty($_FILES['imagenes'])) {
        $uploadDir = '../images/';
        
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['imagenes']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                // Verificar si el archivo ya existe y generar un nombre único si es necesario
                $counter = 1;
                $originalName = pathinfo($fileName, PATHINFO_FILENAME);
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                
                while (file_exists($targetPath)) {
                    $fileName = $originalName . '_' . $counter . '.' . $extension;
                    $targetPath = $uploadDir . $fileName;
                    $counter++;
                }
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imagenes[] = $fileName;
                }
            }
        }
    }
    
    // Procesar capacidades
    $capacidades = isset($_POST['capacidades']) ? json_decode($_POST['capacidades'], true) : [];
    $precioPrincipal = 0;
    
    if (!empty($capacidades)) {
        // Tomar el precio de la primera capacidad como precio principal
        $precioPrincipal = $capacidades[0]['precio'] ?? 0;
    }
    
    // Insertar máquina
    $stmt = $pdo->prepare("INSERT INTO maquinas 
                          (nombre, descripcion, precio, stock, categoria, tipo_variante, capacidades, imagen) 
                          VALUES (?, ?, ?, 100, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['descripcion'],
        $precioPrincipal,
        $_POST['categoria'],
        $_POST['tipo_variante'],
        json_encode($capacidades),
        json_encode($imagenes)
    ]);
    
    $lastInsertId = $pdo->lastInsertId();
    
    // Obtener la máquina recién creada para devolver todos los datos
    $stmt = $pdo->prepare("SELECT * FROM maquinas WHERE id = ?");
    $stmt->execute([$lastInsertId]);
    $machine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Decodificar los campos JSON
    if ($machine) {
        $machine['imagen'] = json_decode($machine['imagen'], true) ?: [];
        $machine['capacidades'] = json_decode($machine['capacidades'], true) ?: [];
    }
    
    echo json_encode([
        'success' => true,
        'machine' => $machine
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al agregar máquina: ' . $e->getMessage()]);
}
?>