<?php
header('Content-Type: application/json');
header('X-Powered-By: RepPan');

// Configuración de errores
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mercado_pago_errors.log');

// Registrar el momento exacto de la ejecución
error_log("========== Notificación recibida ========== " . date('Y-m-d H:i:s'));

// Cargar el autoload de Composer y configurar Mercado Pago
require_once __DIR__ . '/../vendor/autoload.php';
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\MerchantOrder\MerchantOrderClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;

// Configurar credenciales
MercadoPagoConfig::setAccessToken("APP_USR-5289074047708329-041518-c0ad83e5ae0b975fd2bea513b7a00113-203442816");

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Error: Método no permitido");
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido']));
}

// Obtener datos
$input = file_get_contents('php://input');
error_log("Datos recibidos: " . $input);

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error: JSON inválido");
    http_response_code(400);
    die(json_encode(['error' => 'JSON inválido']));
}

try {
    // Manejar notificaciones de prueba
    if (isset($data['live_mode']) && $data['live_mode'] === false) {
        error_log("Notificación de prueba recibida - ID: " . ($data['id'] ?? ''));
        http_response_code(200);
        die(json_encode(['status' => 'OK (Test notification)']));
    }

    // Solo procesar notificaciones en el nuevo formato
    if (!isset($data['topic'])) {
        error_log("Notificación en formato antiguo ignorada");
        http_response_code(200);
        die(json_encode(['status' => 'OK (Old format ignored)']));
    }

    $topic = $data['topic'];
    $resource_id = null;

    // Extraer el ID del recurso según el tipo de notificación
    if ($topic === 'merchant_order') {
        $urlParts = explode('/', $data['resource']);
        $resource_id = (int)end($urlParts);
    } else {
        $resource_id = (int)$data['resource'];
    }
    
    error_log("Procesando notificación de tipo: " . $topic);

    // Validar que tenemos un ID de recurso válido
    if (empty($resource_id)) {
        error_log("Datos recibidos: " . print_r($data, true));
        throw new Exception("ID de recurso no proporcionado");
    }

    // Incluir configuración de base de datos
    require_once __DIR__ . '/../app/config.php';
    require_once __DIR__ . '/../app/email_sender.php';
    require_once __DIR__ . '/../public/stock_manager.php';

    // Procesar según el tipo de notificación
    if ($topic === 'payment') {
        // Procesar pago
        $client = new PaymentClient();
        $payment = $client->get($resource_id);
        
        error_log("Procesando pago ID: " . $payment->id);
        error_log("Estado del pago: " . $payment->status);
        error_log("External reference: " . $payment->external_reference);
        
        // Actualizar estado en la base de datos
        if (!empty($payment->external_reference)) {
            // Verificar si la compra existe primero
            $checkStmt = $pdo->prepare("SELECT id, status, stock_updated FROM compras WHERE external_reference = ?");
            $checkStmt->execute([$payment->external_reference]);
            
            if ($checkStmt->fetch()) {
                try {
                    // Obtener los datos completos de la compra
                    $stmt = $pdo->prepare("SELECT * FROM compras WHERE external_reference = ?");
                    $stmt->execute([$payment->external_reference]);
                    $compra = $stmt->fetch();
                    
                    if ($compra) {
                        // Decodificar el carrito
                        $cart = json_decode($compra['cart_data'], true);
                        
                        if (is_array($cart) && $payment->status === 'approved') {
                            // Descontar stock solo si el pago está aprobado
                            $stock_result = updateStock($pdo, $cart);
                            error_log("Resultado de updateStock: " . ($stock_result ? "Éxito" : "Fallo"));
                            
                            // Enviar emails
                            $email_result = sendEmails($compra, $cart);
                            error_log("Resultado de sendEmails: " . ($email_result ? "Éxito" : "Fallo"));
                            
                            if ($stock_result) {
                                $stmt = $pdo->prepare("UPDATE compras SET stock_updated = 1 WHERE external_reference = ?");
                                $stmt->execute([$payment->external_reference]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error al procesar compra aprobada: " . $e->getMessage());
                    // No detenemos el flujo aunque falle el email/stock
                }
                
                // Actualizar información del pago en la base de datos
                $stmt = $pdo->prepare("UPDATE compras SET 
                    status = ?,
                    payment_id = ?,
                    mp_response = ?,
                    updated_at = NOW()
                    WHERE external_reference = ?");
                
                $stmt->execute([
                    $payment->status,
                    $payment->id,
                    json_encode($payment),
                    $payment->external_reference
                ]);
                
                error_log("Pago actualizado en base de datos para external_reference: " . $payment->external_reference);
            }
        }
    } elseif ($topic === 'merchant_order') {
        // Procesar orden comercial
        $client = new MerchantOrderClient();
        $order = $client->get($resource_id);
        
        error_log("Procesando orden comercial ID: " . $order->id);
        error_log("Estado de la orden: " . $order->status);
        error_log("External reference: " . $order->external_reference);
        
        // Actualizar información adicional de la orden
        if (!empty($order->external_reference)) {
            // Verificar si la compra existe primero
            $checkStmt = $pdo->prepare("SELECT id FROM compras WHERE external_reference = ?");
            $checkStmt->execute([$order->external_reference]);
            
            if ($checkStmt->fetch()) {
                $stmt = $pdo->prepare("UPDATE compras SET 
                    merchant_order_id = ?,
                    mp_status = ?,
                    mp_response = ?,
                    updated_at = NOW()
                    WHERE external_reference = ?");
                
                $stmt->execute([
                    $order->id,
                    $order->status,
                    json_encode($order),
                    $order->external_reference
                ]);
                
                error_log("Orden comercial actualizada para external_reference: " . $order->external_reference);
            }
        }
    } else {
        error_log("Tipo de notificación no manejado: " . $topic);
        http_response_code(200);
        die(json_encode(['status' => 'OK (No action - unhandled type)']));
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'OK']);
    
} catch (MPApiException $e) {
    error_log("Error API MercadoPago: " . $e->getMessage());
    error_log("Detalles: " . json_encode($e->getApiResponse()));
    http_response_code(400);
    echo json_encode(['error' => 'Error API MercadoPago: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error procesando notificación: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}