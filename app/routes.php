<?php

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php'; 
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Configuración de MercadoPago
MercadoPagoConfig::setAccessToken("TEST-ACCESS-TOKEN"); 

// Configuración de Flight
Flight::set('flight.base_url', 'https://www.reppan.com.ar');

Flight::route('/', function() {
    renderWithAssets('inicio');
});

Flight::route('/repuestos', function() {
    renderWithAssets('repuestos');
});

Flight::route('/maquinaria', function() {
    renderWithAssets('maquinaria');
});

Flight::route('/reparaciones', function() {
    renderWithAssets('reparaciones');
});

Flight::route('/compra', function() {
    // Solo verificar POST y cart_data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cart_data'])) {
        Flight::redirect('/');
        return;
    }

    renderWithAssets('pago/compra', ['cart_data' => $_POST['cart_data']]);
});

Flight::route('/procesar_pago', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            require_once __DIR__ . '/../app/config.php';
            
            // Validación mejorada
            $requiredFields = ['nombre', 'direccion', 'email', 'total', 'cart_data', 'codigo_postal', 'telefono'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            // Procesar el carrito
            $cart = json_decode($_POST['cart_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($cart)) {
                throw new Exception("Datos del carrito inválidos");
            }

            // Construir lista de nombres de productos para el resumen
            $productNames = array_map(function($item) {
                $name = $item['nombre'] ?? 'Producto';
                if (!empty($item['variante']['valor'])) {
                    $name .= ' (' . $item['variante']['valor'] . ')';
                }
                return $name;
            }, $cart);

            $combinedDescription = (count($cart) > 1) 
                ? (count($cart) . ' productos: ' . implode(', ', $productNames))
                : ($productNames[0] ?? 'Compra en REPPAN');

            // Crear items para la preferencia (cada uno con su propio nombre)
            $items = [];
            foreach ($cart as $item) {
                $itemTitle = $item['nombre'] ?? 'Producto';
                if (!empty($item['variante']['valor'])) {
                    $itemTitle .= ' (' . $item['variante']['valor'] . ')';
                }

                $items[] = [
                    'id' => (string)($item['id'] ?? uniqid()),
                    'title' => substr($itemTitle, 0, 256), // Nombre individual del producto
                    'quantity' => (int)($item['quantity'] ?? 1),
                    'unit_price' => (float)($item['precio'] ?? 0),
                    'currency_id' => 'ARS',
                    'category_id' => $item['categoria'] ?? 'repuestos',
                    'description' => substr($combinedDescription, 0, 256) // Descripción combinada aquí
                ];
            }
            
            // Guardar la compra en la base de datos
            $external_reference = 'compra_'.time();
            $stmt = $pdo->prepare("INSERT INTO compras 
                (nombre_cliente, email, direccion, telefono, codigo_postal, total, cart_data, external_reference, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([
                $_POST['nombre'],
                $_POST['email'],
                $_POST['direccion'],
                $_POST['telefono'],
                $_POST['codigo_postal'],
                $_POST['total'],
                $_POST['cart_data'],
                $external_reference
            ]);
            
            $compra_id = $pdo->lastInsertId();
            
            // Dividir el nombre completo en nombre y apellido
            $nombreCompleto = explode(' ', $_POST['nombre'], 2);
            $nombre = $nombreCompleto[0];
            $apellido = $nombreCompleto[1] ?? '';

            // Configuración de la preferencia para producción
            $preferenceRequest = [
                'items' => $items,
                'payer' => [
                    'name' => substr($nombre, 0, 100),
                    'surname' => substr($apellido, 0, 100),
                    'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                    'phone' => [
                        'area_code' => '11',
                        'number' => preg_replace('/[^0-9]/', '', $_POST['telefono'])
                    ],
                    'address' => [
                        'street_name' => substr($_POST['direccion'], 0, 200),
                        'zip_code' => $_POST['codigo_postal']
                    ]
                ],
                'back_urls' => [
                    'success' => Flight::get('flight.base_url').'/pago/success',
                    'failure' => Flight::get('flight.base_url').'/pago/failure',
                    'pending' => Flight::get('flight.base_url').'/pago/pending'
                ],
                'auto_return' => 'approved', 
                'notification_url' => Flight::get('flight.base_url').'/public/notification.php',
                'external_reference' => $external_reference,
                'statement_descriptor' => 'REPPAN',
                'additional_info' => $combinedDescription // Descripción combinada para el resumen
            ];

            error_log("Datos enviados a MercadoPago: " . print_r($preferenceRequest, true));
            
            // Crear la preferencia
            $client = new PreferenceClient();
            $preference = $client->create($preferenceRequest);

            if (!isset($preference->id)) {
                throw new Exception("No se pudo crear la preferencia de pago");
            }

            $redirectUrl = $preference->init_point;
            
            if (!$redirectUrl) {
                throw new Exception("No se obtuvo URL de redirección");
            }

            Flight::json([
                'success' => true,
                'id' => $preference->id,
                'message' => 'Preferencia creada correctamente'
            ]);

        } catch (MPApiException $e) {
            error_log('Error MercadoPago API: ' . $e->getMessage());
            error_log('Response: ' . json_encode($e->getApiResponse()));
            Flight::json(['success' => false, 'error' => 'Error al procesar el pago con Mercado Pago'], 500);
            
        } catch (Exception $e) {
            error_log('Error general: ' . $e->getMessage());
            Flight::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    } else {
        Flight::json(['success' => false, 'error' => 'Método no permitido'], 405);
    }
});

Flight::route('/pago/error', function() {
    $compra_id = $_GET['id'] ?? '';
    Flight::render('pago/error', ['compra_id' => $compra_id]);
});

Flight::route('/pago/pending', function() {
    $compra_id = $_GET['id'] ?? '';
    Flight::render('pago/pending', ['compra_id' => $compra_id]);
});

Flight::route('/pago/success', function() {
    error_log("Accediendo a /pago/success");
    try {
        require_once __DIR__ . '/../app/config.php';
        
        $payment_id = $_GET['payment_id'] ?? '';
        $external_ref = $_GET['external_reference'] ?? '';
        
        error_log("Payment ID: $payment_id");
        error_log("External Reference: $external_ref");
        
        if (empty($external_ref)) {
            throw new Exception("External reference vacía");
        }
        
        // Buscar compra en BD
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE external_reference = ?");
        $stmt->execute([$external_ref]);
        $compra = $stmt->fetch();
        
        if (!$compra) {
            throw new Exception("Compra no encontrada");
        }
        
        
         // Verificar y actualizar estado si está vacío
        if (empty($compra['status']) || $compra['status'] !== 'approved') {
            $stmt = $pdo->prepare("UPDATE compras SET status = 'approved' WHERE id = ?");
            $stmt->execute([$compra['id']]);
            error_log("Estado vacío actualizado a approved para compra ID: " . $compra['id']);
        }
        
        // Redirigir a gracias con ID
        Flight::redirect('/pago/gracias?id='.$compra['id']);
        
    } catch (Exception $e) {
        error_log("Error en /pago/success: " . $e->getMessage());
        Flight::redirect('/pago/error');
    }
});

Flight::route('/pago/failure', function() {
    $external_reference = $_GET['external_reference'] ?? '';
    
    if (empty($external_reference)) {
        Flight::redirect('/pago/error');
        return;
    }

    try {
        require_once __DIR__ . '/../app/config.php';
        // Buscar la compra
        $stmt = $pdo->prepare("SELECT id FROM compras WHERE external_reference = ?");
        $stmt->execute([$external_reference]);
        $compra = $stmt->fetch();
        
        if (!$compra) {
            Flight::redirect('/pago/error');
            return;
        }

        // Actualizar estado a rechazado
        $stmt = $pdo->prepare("UPDATE compras SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$compra['id']]);
        error_log("Estado actualizado a rejected para compra ID: " . $compra['id']);
        
        Flight::redirect('/pago/error?id='.$compra['id']);
    } catch (Exception $e) {
        error_log('Error en página de fallo: ' . $e->getMessage());
        Flight::redirect('/pago/error');
    }
});

Flight::route('/pago/pending', function() {
    $external_reference = $_GET['external_reference'] ?? '';
    
    if (empty($external_reference)) {
        Flight::redirect('/pago/error');
        return;
    }

    try {
        require_once __DIR__ . '/../app/config.php';
        // Buscar la compra
        $stmt = $pdo->prepare("SELECT id FROM compras WHERE external_reference = ?");
        $stmt->execute([$external_reference]);
        $compra = $stmt->fetch();
        
        if (!$compra) {
            Flight::redirect('/pago/error');
            return;
        }

        // Actualizar estado a pendiente
        $stmt = $pdo->prepare("UPDATE compras SET status = 'pending' WHERE id = ?");
        $stmt->execute([$compra['id']]);
        error_log("Estado actualizado a pending para compra ID: " . $compra['id']);
        
        Flight::redirect('/pago/pending?id='.$compra['id']);
    } catch (Exception $e) {
        error_log('Error en página pendiente: ' . $e->getMessage());
        Flight::redirect('/pago/error');
    }
});

Flight::route('/pago/gracias', function() {
    $compra_id = $_GET['id'] ?? '';
    
    if (empty($compra_id)) {
        error_log('Intento de acceso a gracias sin ID');
        Flight::redirect('/pago/error');
        return;
    }

    try {
        require_once __DIR__ . '/../app/config.php';
        
        // 1. Validar que la compra existe y está aprobada
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE id = ? AND (status = 'approved' OR payment_id IS NOT NULL)");
        $stmt->execute([$compra_id]);
        $compra = $stmt->fetch();
        
        if (!$compra) {
            error_log('Compra no encontrada o no aprobada: ' . $compra_id);
            Flight::redirect('/pago/error');
            return;
        }

        // 2. Decodificar el carrito con manejo de errores
        $cart = [];
        try {
            $cart = json_decode($compra['cart_data'], true) ?? [];
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error decodificando carrito");
            }
        } catch (Exception $e) {
            error_log('Error procesando carrito para compra ' . $compra_id . ': ' . $e->getMessage());
            $cart = [];
        }

        // 3. Preparar datos para la vista
        $viewData = [
            'compra' => $compra,
            'cart' => $cart,
            'payment_details' => [
                'fecha' => $compra['created_at'],
                'total' => number_format($compra['total'], 2),
                'metodo_pago' => $compra['payment_method'] ?? 'Mercado Pago'
            ]
        ];
        
        // 4. Renderizar vista
        Flight::render('pago/gracias', $viewData);
        
    } catch (Exception $e) {
        error_log('Error crítico en página de gracias: ' . $e->getMessage());
        Flight::redirect('/pago/error');
    }
});