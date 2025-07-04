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
    
    // Insertar producto
    $stmt = $pdo->prepare("INSERT INTO productos 
                          (nombre, descripcion, categoria, precio, stock, imagen) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['descripcion'],
        $_POST['categoria'],
        $_POST['precio'],
        $_POST['stock'],
        json_encode($imagenes)
    ]);
    
    $lastInsertId = $pdo->lastInsertId();
    
    // Obtener el producto recién creado para devolver todos los datos
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$lastInsertId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Decodificar las imágenes
    if ($product) {
        $product['imagen'] = json_decode($product['imagen'], true) ?: [];
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al agregar producto: ' . $e->getMessage()]);
}
?>