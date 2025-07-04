<?php
include '../../app/config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'No autorizado']));
}

header('Content-Type: application/json');

$machineId = $_POST['id'] ?? 0;

try {
    // Obtener datos actuales de la máquina
    $stmt = $pdo->prepare("SELECT imagen, capacidades FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $maquina = $stmt->fetch();
    
    if (!$maquina) {
        throw new Exception("Máquina no encontrada");
    }
    
    // Procesar imágenes
    $imagenes = [];
    if (!empty($maquina['imagen'])) {
        $imagenes = json_decode($maquina['imagen'], true) ?: [];
    }
    
    // Procesar imágenes existentes que no se han eliminado
    if (!empty($_POST['existing_images'])) {
        $imagenes = array_intersect($imagenes, $_POST['existing_images']);
    } else {
        $imagenes = [];
    }
    
    // Procesar nuevas imágenes
    if (!empty($_FILES['imagenes'])) {
        $uploadDir = '../images/';
        
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['imagenes']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imagenes[] = $fileName;
                }
            }
        }
    }
    
    // Validar datos obligatorios
    if (empty($_POST['nombre']) || empty($_POST['descripcion']) || empty($_POST['categoria']) || empty($_POST['tipo_variante'])) {
        throw new Exception("Todos los campos son obligatorios");
    }
    
    // Actualizar máquina (sin modificar capacidades)
    $stmt = $pdo->prepare("UPDATE maquinas SET 
                          nombre = ?, descripcion = ?, categoria = ?, tipo_variante = ?,
                          imagen = ?
                          WHERE id = ?");
    $stmt->execute([
        $_POST['nombre'],
        $_POST['descripcion'],
        $_POST['categoria'],
        $_POST['tipo_variante'],
        json_encode($imagenes),
        $machineId
    ]);
    
    // Traer el producto actualizado
    $stmt = $pdo->prepare("SELECT * FROM maquinas WHERE id = ?");
    $stmt->execute([$machineId]);
    $machine = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($machine) {
        $machine['imagen'] = json_decode($machine['imagen'], true) ?: [];
    }
    
    if ($machine) {
        $machine['capacidades'] = json_decode($machine['capacidades'], true) ?: [];
    }

    echo json_encode(['success' => true, 'machine' => $machine]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>