<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$productId = $_POST['id'] ?? 0;

try {
    // Obtener imágenes existentes
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$productId]);
    $producto = $stmt->fetch();

    $imagenes = [];
    if ($producto && !empty($producto['imagen'])) {
        $imagenes = json_decode($producto['imagen'], true) ?: [];
    }

    // Filtrar las imágenes que siguen seleccionadas
    if (!empty($_POST['existing_images'])) {
        $imagenes = array_intersect($imagenes, $_POST['existing_images']);
    } else {
        $imagenes = [];
    }

    // Subir nuevas imágenes
    if (!empty($_FILES['imagenes'])) {
        $uploadDir = '../images/';
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['imagenes']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
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

    // Actualizar producto
    $stmt = $pdo->prepare("UPDATE productos SET 
                          nombre = ?, descripcion = ?, categoria = ?, 
                          precio = ?, stock = ?, imagen = ?
                          WHERE id = ?");
    $stmt->execute([
        $_POST['nombre'] ?? '',
        $_POST['descripcion'] ?? '',
        $_POST['categoria'] ?? '',
        $_POST['precio'] ?? 0,
        $_POST['stock'] ?? 0,
        json_encode($imagenes),
        $productId
    ]);

    // Traer el producto actualizado
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product['imagen'] = json_decode($product['imagen'], true) ?: [];
    }

    echo json_encode(['success' => true, 'product' => $product]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()]);
}
