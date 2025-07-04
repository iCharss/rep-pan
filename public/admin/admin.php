<?php
session_start();
include '../../app/config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Obtener productos
$productos = [];
try {
    $stmt = $pdo->query("SELECT id, nombre, descripcion, categoria, precio, stock, imagen FROM productos");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_productos = "Error al cargar productos: " . $e->getMessage();
}

// Obtener maquinaria
$maquinaria = [];
try {
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen, categoria, tipo_variante, capacidades FROM maquinas");
    $maquinaria = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_maquinaria = "Error al cargar maquinaria: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Reppan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jQuery (Toastr depende de jQuery) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #1a3e72; /* Azul oscuro de Reppan */
            --secondary-color: #2a5ba7; /* Azul más claro */
            --accent-color: #f8b739; /* Amarillo/dorado de Reppan */
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #fd7e14;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            justify-content: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo img {
            height: 40px;
        }
        
        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: none;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            background-color: transparent;
            border: none;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tab.active {
            color: var(--primary-color);
            background-color: rgba(26, 62, 114, 0.1);
        }
        
        .tab.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .tab:hover:not(.active) {
            background-color: rgba(0,0,0,0.03);
        }
        
        .tab-content {
            display: none;
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-top: 1rem;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-accent {
            background-color: var(--accent-color);
            color: #333;
            margin-left: 50px;
        }
        
        .btn-accent:hover {
            background-color: #e0a730;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
        }
        
        .btn-warning:hover {
            background-color: #e67300;
        }
        
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.85rem;
        }
        
        .btn-add {
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        
        /* Estilo para las tarjetas de productos/maquinaria */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-header {
            padding: 1rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-text {
            color: #666;
            margin: 0.5rem 0 1rem;
            line-height: 1.5;
        }
        
        .card-category {
            display: inline-block;
            background-color: rgba(26, 62, 114, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        /* Capacidades mejoradas */
        .capacities {
            margin-top: 1rem;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        
        .capacity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background-color: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .capacity-item:hover {
            background-color: #f0f0f0;
        }
        
        .capacity-info {
            flex: 1;
        }
        
        .capacity-value {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .capacity-price {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .capacity-stock {
            color: #666;
            font-size: 0.9rem;
        }
        
        .capacity-actions {
            display: flex;
            gap: 5px;
        }
        
        /* Imágenes */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 1rem;
            justify-content: center;
        }
        
        .image-preview-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .image-preview-item:hover .remove-image {
            opacity: 1;
        }
        
        /* Modal mejorado */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.2);
            transform: translateY(-20px);
            opacity: 0;
            animation: modalOpen 0.4s ease forwards;
        }
        
        @keyframes modalOpen {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            margin: 0;
            color: var(--primary-color);
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .close:hover {
            color: var(--danger-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus, 
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 62, 114, 0.1);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        /* Alertas */
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Animaciones */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
    
            .card-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                border-radius: 0;
            }
            
            .modal-content {
                width: 60%;
                margin: 2% auto;
                max-height: 80vh;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/logito.png" alt="Reppan Logo">
            <h1>Panel de Administración</h1>
        </div>
        <a href="ventas.php" class="btn btn-accent">
            <i class="fas fa-sign-out-alt"></i> VENTAS
        </a>
        <a href="logout.php" class="btn btn-accent">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </header>
    
    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="productos">Productos</div>
            <div class="tab" data-tab="maquinaria">Maquinaria</div>
        </div>
        
        <!-- Tabla de Productos -->
        <div id="productos" class="tab-content active">
            <button class="btn btn-primary btn-add" onclick="openAddProductModal()">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
            
            <?php if (isset($error_productos)): ?>
                <div class="alert alert-danger"><?php echo $error_productos; ?></div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($productos as $producto): 
                        $imagenes = !empty($producto['imagen']) ? 
                            (is_string($producto['imagen']) ? json_decode($producto['imagen'], true) : $producto['imagen']) : 
                            [];
                    ?>
                        <div class="card" data-product-id="<?php echo $producto['id']; ?>">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <div class="card-actions">
                                    <button class="btn btn-warning btn-sm" onclick="openEditProductModal(<?php echo $producto['id']; ?>)">
                                        <i class="fas fa-edit"></i>Edit producto
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDeleteProduct(<?php echo $producto['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="card-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                                <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                
                                <div class="product-details">
                                    <div><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 0); ?></div>
                                    <div><strong>Stock:</strong> <?php echo htmlspecialchars($producto['stock']); ?></div>
                                </div>
                                
                                <?php if (!empty($imagenes)): ?>
                                    <div class="image-preview">
                                        <?php foreach ($imagenes as $img): ?>
                                            <div class="image-preview-item">
                                                <img src="../images/<?php echo htmlspecialchars($img); ?>" alt="Imagen producto">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #999; margin-top: 1rem;">Sin imágenes</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Tabla de Maquinaria -->
        <div id="maquinaria" class="tab-content">
            <button class="btn btn-primary btn-add" onclick="openAddMachineModal()">
                <i class="fas fa-plus"></i> Agregar Maquinaria
            </button>
            
            <?php if (isset($error_maquinaria)): ?>
                <div class="alert alert-danger"><?php echo $error_maquinaria; ?></div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($maquinaria as $maquina): 
                        $imagenes = !empty($maquina['imagen']) ? 
                            (is_string($maquina['imagen']) ? json_decode($maquina['imagen'], true) : $maquina['imagen']) : 
                            [];
                        $capacidades = !empty($maquina['capacidades']) ? 
                            (is_string($maquina['capacidades']) ? json_decode($maquina['capacidades'], true) : $maquina['capacidades']) : 
                            [];
                    ?>
                        <div class="card" data-machine-id="<?php echo $maquina['id']; ?>">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($maquina['nombre']); ?></h3>
                                <div class="card-actions">
                                    <button class="btn btn-warning btn-sm" onclick="openEditMachineModal(<?php echo $maquina['id']; ?>)">
                                        <i class="fas fa-edit"></i>Editar Maquina
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="confirmDeleteMachine(<?php echo $maquina['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="card-category"><?php echo htmlspecialchars($maquina['categoria']); ?></span>
                                <p class="card-text"><?php echo htmlspecialchars($maquina['descripcion']); ?></p>
                                
                                <?php if (!empty($capacidades)): ?>
                                    <div class="capacities">
                                        <h4>Capacidades</h4>
                                        <?php foreach ($capacidades as $capacidad): ?>
                                            <div class="capacity-item">
                                                <div class="capacity-info">
                                                    <span class="capacity-value"><?php echo htmlspecialchars($capacidad['valor']); ?></span>
                                                    <span class="capacity-price">$<?php echo number_format($capacidad['precio'], 0); ?></span>
                                                    <span class="capacity-stock">Stock: <?php echo htmlspecialchars($capacidad['stock']); ?></span>
                                                </div>
                                                <div class="capacity-actions">
                                                    <button class="btn btn-warning btn-sm" onclick="openEditCapacityModal(<?php echo $maquina['id']; ?>, '<?php echo htmlspecialchars($capacidad['valor']); ?>')">
                                                        <i class="fas fa-edit"></i>Editar
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="confirmDeleteCapacity(<?php echo $maquina['id']; ?>, '<?php echo htmlspecialchars($capacidad['valor']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #999;">Sin capacidades</div>
                                <?php endif; ?>
                                
                                <?php if (!empty($imagenes)): ?>
                                    <div class="image-preview">
                                        <?php foreach ($imagenes as $img): ?>
                                            <div class="image-preview-item">
                                                <img src="../images/<?php echo htmlspecialchars($img); ?>" alt="Imagen máquina">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #999; margin-top: 1rem;">Sin imágenes</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para agregar/editar producto - COMPLETO -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('productModal')">&times;</span>
            <h2 id="productModalTitle">Agregar Nuevo Producto</h2>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="id" value="">
                
                <div class="form-group">
                    <label for="productName">Nombre:</label>
                    <input type="text" id="productName" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Descripción:</label>
                    <textarea id="productDescription" name="descripcion" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="productCategory">Categoría:</label>
                    <input type="text" id="productCategory" name="categoria" required>
                </div>
                
                <div class="form-group">
                    <label for="productPrice">Precio:</label>
                    <input type="number" id="productPrice" name="precio" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="productStock">Stock:</label>
                    <input type="number" id="productStock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="productImages">Imágenes:</label>
                    <input type="file" id="productImages" name="imagenes[]" multiple accept="image/*">
                    <small>Puedes seleccionar múltiples imágenes</small>
                    <div class="image-preview" id="productImagesPreview"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('productModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para agregar/editar maquinaria - COMPLETO -->
    <div id="machineModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('machineModal')">&times;</span>
            <h2 id="machineModalTitle">Agregar Nueva Máquina</h2>
            <form id="machineForm" enctype="multipart/form-data">
                <input type="hidden" id="machineId" name="id" value="">
                
                <div class="form-group">
                    <label for="machineName">Nombre:</label>
                    <input type="text" id="machineName" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="machineDescription">Descripción:</label>
                    <textarea id="machineDescription" name="descripcion" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="machineCategory">Categoría:</label>
                    <input type="text" id="machineCategory" name="categoria" required>
                </div>
                
                    <input type="hidden" id="machineVariantType" name="tipo_variante" value="capacidad">
                
                <div class="form-group">
                    <label>Capacidades:</label>
                    <div id="capacitiesContainer">
                        <!-- Las capacidades se agregarán dinámicamente aquí -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="machineImages">Imágenes:</label>
                    <input type="file" id="machineImages" name="imagenes[]" multiple accept="image/*">
                    <small>Puedes seleccionar múltiples imágenes</small>
                    <div class="image-preview" id="machineImagesPreview"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('machineModal')">Cancelar</button>
                    <button type="button" id="saveMachineBtn" class="btn btn-primary">Guardar</button>
                    
                    <button type="button" id="saveMachineWithCapacityBtn" class="btn btn-primary" style="display:none;">Añadir maquina</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para editar capacidad -->
    <div id="capacityModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('capacityModal')">&times;</span>
            <h2>Editar Capacidad</h2>
            <form id="capacityForm">
                <input type="hidden" id="capacityMachineId" name="machine_id" value="">
                <input type="hidden" id="originalCapacityValue" name="original_value" value="">
                
                <div class="form-group">
                    <label for="capacityValue">Valor:</label>
                    <input type="text" id="capacityValue" name="valor" required>
                </div>
                
                <div class="form-group">
                    <label for="capacityPrice">Precio:</label>
                    <input type="number" id="capacityPrice" name="precio" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="capacityStock">Stock:</label>
                    <input type="number" id="capacityStock" name="stock" min="0" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('capacityModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
<script>
//Notificaciones
function showNotification(type, message) {
    switch(type) {
        case 'success':
            toastr.success(message);
            break;
        case 'error':
            toastr.error(message);
            break;
        case 'info':
            toastr.info(message);
            break;
        case 'warning':
            toastr.warning(message);
            break;
    }
}

function confirmAction(message, callback) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}
// Funciones globales que necesitan ser accesibles desde los onclick
function openAddProductModal() {
    document.getElementById('productModalTitle').textContent = 'Agregar Nuevo Producto';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productImagesPreview').innerHTML = '';
    document.getElementById('productModal').style.display = 'block';
}

function openAddMachineModal() {
    document.getElementById('machineModalTitle').textContent = 'Agregar Nueva Máquina';
    document.getElementById('machineForm').reset();
    document.getElementById('machineId').value = '';
    document.getElementById('machineImagesPreview').innerHTML = '';
    
    // Mostrar el botón de guardar con capacidad y ocultar el normal
    document.getElementById('saveMachineBtn').style.display = 'none';
    document.getElementById('saveMachineWithCapacityBtn').style.display = 'inline-block';
    
    // Configurar el contenedor de capacidades
    const container = document.getElementById('capacitiesContainer');
    container.innerHTML = '';
    
    // Agregar capacidad inicial (obligatoria)
    addInitialCapacityField();
    
    // Agregar botón para añadir más capacidades
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'btn btn-primary add-capacity';
    addButton.innerHTML = '<i class="fas fa-plus"></i> Agregar Otra Capacidad';
    addButton.onclick = addAdditionalCapacityField;
    container.appendChild(addButton);
    
    document.getElementById('machineModal').style.display = 'block';
}

// Funciones para abrir y cerrar modales
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden'; // Al abrir
}
            
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = ''; // Al cerrar

}

function addCapacityField(value = '', price = '', stock = '') {
    const container = document.getElementById('capacitiesContainer');
    
    // Si ya hay una lista de capacidades (modo edición), insertamos antes del botón
    const addButton = container.querySelector('.add-capacity');
    const insertBefore = addButton || null;
    
    const div = document.createElement('div');
    div.className = 'capacity-item';
    div.style.marginBottom = '15px';
    div.style.padding = '10px';
    div.style.backgroundColor = '#f0f8ff';
    div.style.borderRadius = '4px';
    div.style.border = '1px solid #d0e3ff';
    div.innerHTML = `
    <div class="inputs">
        <div class="form-group">
            <label>Valor:</label>
            <input type="text" class="capacity-value" value="${value}" required>
        </div>
        <div class="form-group">
            <label>Precio:</label>
            <input type="number" class="capacity-price" step="0.01" min="0" value="${price}" required>
        </div>
        <div class="form-group">
            <label>Stock:</label>
            <input type="number" class="capacity-stock" min="0" value="${stock}" required>
        </div>
        <div class="capacity-actions">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveCapacity(this.parentNode.parentNode)"><i class="fas fa-save"></i> Guardar
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
    `;
    
    container.insertBefore(div, insertBefore);
}

// Función para agregar la capacidad inicial (obligatoria)
function addInitialCapacityField() {
    const container = document.getElementById('capacitiesContainer');
    
    const div = document.createElement('div');
    div.className = 'capacity-item';
    div.style.marginBottom = '15px';
    div.style.padding = '10px';
    div.style.backgroundColor = '#f0f8ff';
    div.style.borderRadius = '4px';
    div.style.border = '1px solid #d0e3ff';
    div.innerHTML = `
    <div class="inputs">
        <div class="form-group">
            <label>Valor de la Capacidad:</label>
            <input type="text" class="capacity-value" required>
        </div>
        <div class="form-group">
            <label>Precio:</label>
            <input type="number" class="capacity-price" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label>Stock:</label>
            <input type="number" class="capacity-stock" min="0" required>
        </div>
    </div>
    `;
    
    container.insertBefore(div, container.querySelector('.add-capacity'));
}

// Función para agregar capacidades adicionales (con botón de cancelar)
function addAdditionalCapacityField() {
    const container = document.getElementById('capacitiesContainer');
    
    const div = document.createElement('div');
    div.className = 'capacity-item';
    div.style.marginBottom = '15px';
    div.style.padding = '10px';
    div.style.backgroundColor = '#f0f8ff';
    div.style.borderRadius = '4px';
    div.style.border = '1px solid #d0e3ff';
    div.innerHTML = `
    <div class="inputs">
        <div class="form-group">
            <label>Valor de la Capacidad:</label>
            <input type="text" class="capacity-value">
        </div>
        <div class="form-group">
            <label>Precio:</label>
            <input type="number" class="capacity-price" step="0.01" min="0">
        </div>
        <div class="form-group">
            <label>Stock:</label>
            <input type="number" class="capacity-stock" min="0">
        </div>
        <div class="capacity-actions">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">
                <i class="fas fa-times"></i> Cancelar
            </button>
        </div>
    </div>
    `;
    
    container.insertBefore(div, container.querySelector('.add-capacity'));
}

// Función para enviar el formulario de máquina
function submitMachineForm(validateCapacities) {
    const formData = new FormData(document.getElementById('machineForm'));
    const machineId = document.getElementById('machineId').value;
    const isNewMachine = !machineId; // Verdadero si es una nueva máquina
    
    if (validateCapacities) {
        // Validar capacidades solo si es necesario
        const capacities = [];
        document.querySelectorAll('#capacitiesContainer .capacity-item').forEach(item => {
            const valor = item.querySelector('.capacity-value').value;
            const precio = item.querySelector('.capacity-price').value;
            const stock = item.querySelector('.capacity-stock').value;
            
            if (valor && precio && stock) {
                capacities.push({
                    valor: valor,
                    precio: parseFloat(precio),
                    stock: parseInt(stock)
                });
            }
        });
        
        if (capacities.length === 0) {
            showNotification('warning', 'Debe agregar al menos una capacidad válida');
            return;
        }
        
        formData.append('capacidades', JSON.stringify(capacities));
    }
    
    // Agregar imágenes existentes al FormData
    document.querySelectorAll('#machineImagesPreview input[name="existing_images[]"]').forEach(input => {
        formData.append('existing_images[]', input.value);
    });
    
    const url = machineId ? `update_machine.php?id=${machineId}` : 'add_machine.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', machineId ? 'Máquina actualizada correctamente' : 'Máquina agregada correctamente');
            closeModal('machineModal'); // Actualiza la fila
            
            if (machineId) {
                updateMachineRow(machineId);
            } else {
                // Para nuevas máquinas, actualizamos la tabla sin recargar
                addNewMachineToTable(data.machine);
            }
        } else {
            showNotification('error', 'Error: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error al procesar la solicitud');
    });
}

// Función para agregar una nueva máquina a la tabla
function addNewMachineToTable(machineData) {
    const grid = document.querySelector('#maquinaria .card-grid');
    
    // Procesar imágenes
    const imagenes = machineData.imagen ? (typeof machineData.imagen === 'string' ? JSON.parse(machineData.imagen) : machineData.imagen) : [];
    let imagenesHTML = 'Sin imágenes';
    if (imagenes.length > 0) {
        imagenesHTML = '<div class="image-preview">';
        imagenes.forEach(img => {
            imagenesHTML += `
                <div class="image-preview-item">
                    <img src="../images/${img}" alt="Imagen máquina">
                </div>
            `;
        });
        imagenesHTML += '</div>';
    }
    
    // Procesar capacidades
    const capacidades = machineData.capacidades ? (typeof machineData.capacidades === 'string' ? JSON.parse(machineData.capacidades) : machineData.capacidades) : [];
    let capacidadesHTML = '';
    if (capacidades.length > 0) {
        capacidades.forEach(cap => {
            capacidadesHTML += `
                <div class="capacity-item">
                    <div class="capacity-info">
                        <span class="capacity-value">${cap.valor}</span> 
                        <span class="capacity-price">$${cap.precio.toFixed(0)}</span> 
                        <span class="capacity-stock">Stock: ${cap.stock}</span>
                    </div>
                    <div class="capacity-actions">
                        <button class="btn btn-warning btn-sm" onclick="openEditCapacityModal(${machineData.id}, '${cap.valor.replace(/'/g, "\\'")}')">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteCapacity(${machineData.id}, '${cap.valor.replace(/'/g, "\\'")}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    } else {
        capacidadesHTML = '<div style="color: #999;">Sin capacidades</div>';
    }
    
    // Crear la nueva tarjeta de maquinaria
    const card = document.createElement('div');
    card.className = 'card';
    card.setAttribute('data-machine-id', machineData.id);
    card.innerHTML = `
        <div class="card-header">
            <h3 class="card-title">${machineData.nombre || ''}</h3>
            <div class="card-actions">
                <button class="btn btn-warning btn-sm" onclick="openEditMachineModal(${machineData.id})">
                    <i class="fas fa-edit"></i> Editar Maquina
                </button>
                <button class="btn btn-danger btn-sm" onclick="confirmDeleteMachine(${machineData.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <span class="card-category">${machineData.categoria || ''}</span>
            <p class="card-text">${machineData.descripcion || ''}</p>
            <div class="capacities">
                <h4>Capacidades</h4>
                ${capacidadesHTML}
            </div>
    
            ${imagenesHTML}
        </div>
    `;
    
    // Agregar la nueva tarjeta al principio del contenedor de cards
    grid.insertBefore(card, grid.firstChild);

}


// Función para agregar un nuevo producto a la tabla
function addNewProductToTable(productData) {
    const grid = document.querySelector('#productos .card-grid');
    
    // Procesar imágenes
    const imagenes = productData.imagen ? (typeof productData.imagen === 'string' ? JSON.parse(productData.imagen) : productData.imagen) : [];
    let imagenesHTML = 'Sin imágenes';
    if (imagenes.length > 0) {
        imagenesHTML = '<div class="image-preview">';
        imagenes.forEach(img => {
            imagenesHTML += `
                <div class="image-preview-item">
                    <img src="../images/${img}" alt="Imagen producto">
                </div>
            `;
        });
        imagenesHTML += '</div>';
    }
    
    // Crear la nueva tarjeta
    const card = document.createElement('div');
    card.className = 'card';
    card.setAttribute('data-product-id', productData.id);
    card.innerHTML = `
        <div class="card-header">
            <h3 class="card-title">${productData.nombre || ''}</h3>
            <div class="card-actions">
                <button class="btn btn-warning btn-sm" onclick="openEditProductModal(${productData.id})">
                    <i class="fas fa-edit"></i> Editar producto
                </button>
                <button class="btn btn-danger btn-sm" onclick="confirmDeleteProduct(${productData.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <span class="card-category">${productData.categoria || ''}</span>
            <p class="card-text">${productData.descripcion || ''}</p>
            
            <div class="product-details">
                <div><strong>Precio:</strong> $${productData.precio ? numberFormat(productData.precio) : '0'}</div>
                <div><strong>Stock:</strong> ${productData.stock || '0'}</div>
            </div>
            
            ${imagenesHTML}
        </div>
    `;
    
    // Agregar la nueva tarjeta al principio del grid
    grid.insertBefore(card, grid.firstChild);
}

// Función para actualizar una tarjeta de producto existente
function updateProductRow(productId) {
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            const card = document.querySelector(`.card[data-product-id="${productId}"]`);
            if (!card) return;
            
            // Procesar imágenes
            const imagenes = data.imagen ? (typeof data.imagen === 'string' ? JSON.parse(data.imagen) : data.imagen) : [];
            let imagenesHTML = 'Sin imágenes';
            if (imagenes.length > 0) {
                imagenesHTML = '<div class="image-preview">';
                imagenes.forEach(img => {
                    imagenesHTML += `
                        <div class="image-preview-item">
                            <img src="../images/${img}" alt="Imagen producto">
                        </div>
                    `;
                });
                imagenesHTML += '</div>';
            }
            
            // Actualizar el contenido de la tarjeta
            card.querySelector('.card-title').textContent = data.nombre || '';
            card.querySelector('.card-text').textContent = data.descripcion || '';
            card.querySelector('.card-category').textContent = data.categoria || '';
            
            const details = card.querySelector('.product-details');
            details.innerHTML = `
                <div><strong>Precio:</strong> $${data.precio ? numberFormat(data.precio) : '0'}</div>
                <div><strong>Stock:</strong> ${data.stock || '0'}</div>
            `;
            
            const previewContainer = card.querySelector('.image-preview') || card.querySelector('[style*="color: #999"]');
            if (previewContainer) {
                previewContainer.replaceWith(createElementFromHTML(imagenesHTML));
            }
        })
        .catch(error => {
            console.error('Error al actualizar producto:', error);
            showNotification('error', 'Error al actualizar producto: ' + error.message);
        });
}

// Función auxiliar para crear elementos desde HTML string
function createElementFromHTML(htmlString) {
    const div = document.createElement('div');
    div.innerHTML = htmlString.trim();
    return div.firstChild;
}

// Función para formatear números
function numberFormat(number) {
    return parseFloat(number).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Modifica el manejador del formulario de productos
document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productId = document.getElementById('productId').value;
    const isNewProduct = !productId;
    
    // Agregar imágenes existentes al FormData
    document.querySelectorAll('#productImagesPreview input[name="existing_images[]"]').forEach(input => {
        formData.append('existing_images[]', input.value);
    });
    
    const url = productId ? `update_product.php?id=${productId}` : 'add_product.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', productId ? 'Producto actualizado correctamente' : 'Producto agregado correctamente');
            closeModal('productModal');
            
            if (productId) {
                updateProductRow(productId);
            } else {
                addNewProductToTable(data.product);
            }
        } else {
            showNotification('error', 'Error: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Error al procesar la solicitud');
    });
});


document.addEventListener('DOMContentLoaded', function() {
    // Funciones para manejar las pestañas
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // Remover clase active de todas las pestañas y contenidos
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Agregar clase active a la pestaña y contenido seleccionados
            tab.classList.add('active');
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Manejador del formulario de capacidad
    document.getElementById('capacityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const machineId = document.getElementById('capacityMachineId').value;
        const originalValue = document.getElementById('originalCapacityValue').value;
        
        fetch(`update_capacity.php?machine_id=${machineId}&original_value=${encodeURIComponent(originalValue)}`, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success','Capacidad actualizada correctamente');
                closeModal('capacityModal');
                updateMachineRow(machineId);
            } else {
                showNotification('error', 'Error: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error al procesar la solicitud');
        });
    });
    
    
    
    // Agregar manejadores para los botones de guardar
    document.getElementById('saveMachineBtn').addEventListener('click', function() {
        submitMachineForm(false); // No validar capacidades
    });
    
    document.getElementById('saveMachineWithCapacityBtn').addEventListener('click', function() {
        submitMachineForm(true); // Validar capacidades
    });

    // Funciones para previsualizar imágenes
    document.getElementById('productImages').addEventListener('change', function() {
        previewImages(this, 'productImagesPreview');
    });
    
    document.getElementById('machineImages').addEventListener('change', function() {
        previewImages(this, 'machineImagesPreview');
    });
    
    function previewImages(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';
        
        if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Previsualización">
                        <button type="button" class="remove-image" onclick="this.parentNode.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(div);
                }
                
                reader.readAsDataURL(input.files[i]);
            }
        }
    }
    
    // Función para guardar la capacidad individual (modificada)
    window.saveCapacity = function(capacityElement) {
        const machineId = document.getElementById('machineId').value;
        const valor = capacityElement.querySelector('.capacity-value').value;
        const precio = capacityElement.querySelector('.capacity-price').value;
        const stock = capacityElement.querySelector('.capacity-stock').value;
        
        if (!valor || !precio || !stock) {
            showNotification('success','Todos los campos son obligatorios');
            return;
        }
        
        const formData = new FormData();
        formData.append('machine_id', machineId);
        formData.append('valor', valor);
        formData.append('precio', precio);
        formData.append('stock', stock);
        
        fetch('add_capacity.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success','Capacidad guardada correctamente');
                // Cierra el modal si está abierto
                closeModal('machineModal');
                // Actualiza solo la fila de la máquina modificada
                updateMachineRow(machineId);
            } else {
                showNotification('error', 'Error: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error al procesar la solicitud');
        });
    };
    
    // Función para actualizar la tarjeta de máquina
    window.updateMachineRow = async function(machineId) {
        try {
            const response = await fetch(`get_machine.php?id=${machineId}`);
            if (!response.ok) throw new Error('Error al cargar máquina');
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Encuentra la tarjeta correspondiente
            const card = document.querySelector(`.card[data-machine-id="${machineId}"]`);
            if (!card) return;
            
            // Actualiza los datos básicos
            card.querySelector('.card-title').textContent = data.nombre || '';
            card.querySelector('.card-text').textContent = data.descripcion || '';
            card.querySelector('.card-category').textContent = data.categoria || '';
            
            // Procesar capacidades
            const capacidades = data.capacidades ? (typeof data.capacidades === 'string' ? JSON.parse(data.capacidades) : data.capacidades) : [];
            let capacidadesHTML = '<div class="capacities"><h4>Capacidades</h4>';
            
            if (capacidades.length > 0) {
                capacidades.forEach(cap => {
                    capacidadesHTML += `
                        <div class="capacity-item">
                            <div class="capacity-info">
                                <span class="capacity-value">${cap.valor}</span>
                                <span class="capacity-price">$${numberFormat(cap.precio)}</span>
                                <span class="capacity-stock">Stock: ${cap.stock}</span>
                            </div>
                            <div class="capacity-actions">
                                <button class="btn btn-warning btn-sm" onclick="openEditCapacityModal(${machineId}, '${escapeSingleQuote(cap.valor)}')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeleteCapacity(${machineId}, '${escapeSingleQuote(cap.valor)}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                capacidadesHTML += '<div style="color: #999;">Sin capacidades</div>';
            }
            capacidadesHTML += '</div>';
            
            // Procesar imágenes
            const imagenes = data.imagen ? (typeof data.imagen === 'string' ? JSON.parse(data.imagen) : data.imagen) : [];
            let imagenesHTML = '<div class="image-preview">';
            
            if (imagenes.length > 0) {
                imagenes.forEach(img => {
                    imagenesHTML += `
                        <div class="image-preview-item">
                            <img src="../images/${img}" alt="Imagen máquina">
                        </div>
                    `;
                });
            } else {
                imagenesHTML = '<div style="color: #999; margin-top: 1rem;">Sin imágenes</div>';
            }
            imagenesHTML += '</div>';
            
            // Actualizar el contenido del card-body
            const cardBody = card.querySelector('.card-body');
            const existingCapacities = cardBody.querySelector('.capacities');
            const existingImages = cardBody.querySelector('.image-preview') || cardBody.querySelector('[style*="color: #999"]');
            
            if (existingCapacities) {
                existingCapacities.outerHTML = capacidadesHTML;
            } else {
                // Insertar después del card-text
                const cardText = cardBody.querySelector('.card-text');
                cardText.insertAdjacentHTML('afterend', capacidadesHTML);
            }
            
            if (existingImages) {
                existingImages.outerHTML = imagenesHTML;
            } else {
                // Insertar al final
                cardBody.insertAdjacentHTML('beforeend', imagenesHTML);
            }
            
        } catch (error) {
            console.error('Error al actualizar máquina:', error);
            showNotification('error', 'Error al actualizar máquina: ' + error.message);
        }
    };
    
    // Función auxiliar para escapar comillas simples
    function escapeSingleQuote(str) {
        return str.replace(/'/g, "\\'");
    }
    
    // Función para abrir el modal de edición de producto
    window.openEditProductModal = async function(productId) {
        document.getElementById('productModalTitle').textContent = 'Editar Producto';
        document.getElementById('productId').value = productId;
        document.getElementById('productImagesPreview').innerHTML = '';
        
        try {
            const response = await fetch(`get_product.php?id=${productId}`);
            if (!response.ok) throw new Error('Error al cargar producto');
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Llenar el formulario con los datos
            document.getElementById('productName').value = data.nombre || '';
            document.getElementById('productDescription').value = data.descripcion || '';
            document.getElementById('productCategory').value = data.categoria || '';
            document.getElementById('productPrice').value = data.precio || '';
            document.getElementById('productStock').value = data.stock || '';
            
            // Procesar imágenes
            const preview = document.getElementById('productImagesPreview');
            const imagenes = data.imagen ? (typeof data.imagen === 'string' ? JSON.parse(data.imagen) : data.imagen) : [];
            if (imagenes.length > 0) {
                imagenes.forEach(img => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="../images/${img}" alt="Imagen existente">
                        <input type="hidden" name="existing_images[]" value="${img}">
                        <button type="button" class="remove-image" onclick="this.parentNode.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(div);
                });
            }
            
            openModal('productModal');
        } catch (error) {
            console.error('Error:', error);
            showNotification('error','Error al cargar datos del producto: ' + error.message);
        }
    };
    
    // Función para abrir el modal de edición de máquina
    window.openEditMachineModal = async function(machineId) {
        document.getElementById('machineModalTitle').textContent = 'Editar Máquina';
        document.getElementById('machineId').value = machineId;
        document.getElementById('capacitiesContainer').innerHTML = '';
        document.getElementById('machineImagesPreview').innerHTML = '';
        
        // Mostrar el botón de guardar normal y ocultar el de guardar con capacidad
        document.getElementById('saveMachineBtn').style.display = 'inline-block';
        document.getElementById('saveMachineWithCapacityBtn').style.display = 'none';
        
        try {
            const response = await fetch(`get_machine.php?id=${machineId}`);
            if (!response.ok) throw new Error('Error al cargar máquina');
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Llenar el formulario con los datos
            document.getElementById('machineName').value = data.nombre || '';
            document.getElementById('machineDescription').value = data.descripcion || '';
            document.getElementById('machineCategory').value = data.categoria || '';
            document.getElementById('machineVariantType').value = data.tipo_variante || '';
            
            // Procesar capacidades - SOLO PARA VISUALIZACIÓN
            const container = document.getElementById('capacitiesContainer');
            const capacidades = data.capacidades ? (typeof data.capacidades === 'string' ? JSON.parse(data.capacidades) : data.capacidades) : [];
            
            if (capacidades.length > 0) {
                const list = document.createElement('ul');
                list.style.paddingLeft = '0';
                list.style.listStyleType = 'none';
                
                capacidades.forEach(cap => {
                    const li = document.createElement('li');
                    li.style.marginBottom = '10px';
                    li.style.padding = '10px';
                    li.style.backgroundColor = '#f8f9fa';
                    li.style.borderRadius = '4px';
                    li.style.border = '1px solid #ddd';
                    li.innerHTML = `
                        <strong>${cap.valor}</strong> - 
                        $${cap.precio.toFixed(0)} - 
                        Stock: ${cap.stock}
                    `;
                    list.appendChild(li);
                });
                
                container.appendChild(list);
            }
            
            // Botón para agregar nueva capacidad
            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-primary add-capacity';
            addButton.innerHTML = '<i class="fas fa-plus"></i> Agregar Nueva Capacidad';
            addButton.onclick = function() {
                addCapacityField();
            };
            container.appendChild(addButton);
            
            // Procesar imágenes
            const preview = document.getElementById('machineImagesPreview');
            const imagenes = data.imagen ? (typeof data.imagen === 'string' ? JSON.parse(data.imagen) : data.imagen) : [];
            if (imagenes.length > 0) {
                imagenes.forEach(img => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="../images/${img}" alt="Imagen existente">
                        <input type="hidden" name="existing_images[]" value="${img}">
                        <button type="button" class="remove-image" onclick="this.parentNode.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(div);
                });
            }
            
            openModal('machineModal');
        } catch (error) {
            console.error('Error:', error);
            showNotification('error','Error al cargar datos de la máquina: ' + error.message);
        }
    };
    
    // Función para abrir el modal de edición de capacidad
    window.openEditCapacityModal = async function(machineId, capacityValue) {
        try {
            const response = await fetch(`get_capacity.php?machine_id=${machineId}&value=${encodeURIComponent(capacityValue)}`);
            if (!response.ok) throw new Error('Error al cargar capacidad');
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            document.getElementById('capacityMachineId').value = machineId;
            document.getElementById('originalCapacityValue').value = capacityValue;
            document.getElementById('capacityValue').value = data.valor || '';
            document.getElementById('capacityPrice').value = data.precio || '';
            document.getElementById('capacityStock').value = data.stock || '';
            
            document.getElementById('capacityModal').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            showNotification('error','Error al cargar datos de la capacidad: ' + error.message);
        }
    };
    
    // Funciones para confirmar eliminación
    window.confirmDeleteProduct = function(productId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este producto?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`delete_product.php?id=${productId}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', 'Producto eliminado correctamente');
                            document.querySelector(`.card[data-product-id="${productId}"]`).remove();
                        } else {
                            showNotification('error', 'Error al eliminar producto: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'Error al procesar la solicitud');
                    });
            }
        });
    };
    
    window.confirmDeleteMachine = function(machineId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar esta máquina y todas sus capacidades?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`delete_machine.php?id=${machineId}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Máquina eliminada correctamente');
                            document.querySelector(`.card[data-machine-id="${machineId}"]`).remove();
                        } else {
                            toastr.error('Error al eliminar máquina: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('Error al procesar la solicitud');
                    });
            }
        });
    };
    
    window.confirmDeleteCapacity = function(machineId, capacityValue) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la capacidad "${capacityValue}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`delete_capacity.php?machine_id=${machineId}&value=${encodeURIComponent(capacityValue)}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.remaining === 0) {
                                toastr.info('Esa era la última capacidad.');
                                updateMachineRow(machineId);
                            } else {
                                toastr.success('Capacidad eliminada correctamente');
                                updateMachineRow(machineId);
                            }
                        } else {
                            toastr.error('Error al eliminar capacidad: ' + (data.error || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('Error al procesar la solicitud');
                    });
            }
        });
    };
});
</script>
</body>
</html>