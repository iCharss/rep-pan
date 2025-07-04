<?php
require_once __DIR__ . '/../app/config.php';

function updateStock($pdo, $cart) {
    try {
        $pdo->beginTransaction();
        
        foreach ($cart as $item) {
            // Determinar la tabla según la categoría
            $table = ($item['category'] === 'Panaderia') ? 'maquinas' : 'productos';
            
            // Para productos con variantes (capacidades)
            if (!empty($item['variante']) && $table === 'maquinas') {
                // 1. Actualizar stock general de la máquina
                $stmt = $pdo->prepare("SELECT stock, capacidades FROM $table WHERE id = ?");
                $stmt->execute([$item['id']]);
                $producto = $stmt->fetch();
                
                if (!$producto) {
                    throw new Exception("Producto no encontrado: ID " . $item['id']);
                }
                
                // Verificar stock general
                if ($producto['stock'] < $item['quantity']) {
                    throw new Exception("Stock insuficiente para: " . $item['nombre']);
                }
                
                // Actualizar stock general
                $stmt = $pdo->prepare("UPDATE $table SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
                
                // 2. Actualizar stock específico de la variante
                $capacidades = json_decode($producto['capacidades'], true);
                if (is_array($capacidades)) {
                    foreach ($capacidades as &$capacidad) {
                        if ($capacidad['valor'] === $item['variante']['valor']) {
                            if ($capacidad['stock'] < $item['quantity']) {
                                throw new Exception("Stock insuficiente para: " . $item['nombre'] . " - " . $item['variante']['valor']);
                            }
                            $capacidad['stock'] -= $item['quantity'];
                            break;
                        }
                    }
                    
                    // Actualizar el JSON de capacidades
                    $stmt = $pdo->prepare("UPDATE $table SET capacidades = ? WHERE id = ?");
                    $stmt->execute([json_encode($capacidades), $item['id']]);
                }
                
            } else {
                // Productos normales sin variantes
                $stmt = $pdo->prepare("SELECT stock FROM $table WHERE id = ?");
                $stmt->execute([$item['id']]);
                $producto = $stmt->fetch();
                
                if (!$producto) {
                    throw new Exception("Producto no encontrado: ID " . $item['id']);
                }
                
                if ($producto['stock'] < $item['quantity']) {
                    throw new Exception("Stock insuficiente para: " . $item['nombre']);
                }
                
                $stmt = $pdo->prepare("UPDATE $table SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            error_log("Stock actualizado: " . $item['nombre'] . 
                     (isset($item['variante']['valor']) ? " ({$item['variante']['valor']})" : "") . 
                     " - Cantidad descontada: " . $item['quantity']);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error al actualizar stock: " . $e->getMessage());
        
        // Notificar a RepPan sobre el error de stock
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'no-repply@reppan.com.ar';
            $mail->Password = 'Rep.pan2020'; 
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            $mail->setFrom('no-repply@reppan.com.ar', 'RepPan');
            $mail->addAddress('rep.pan2020@gmail.com');
            $mail->Subject = 'Error en actualización de stock';
            $mail->Body = "Error al actualizar stock:<br><br>" .
                          "Error: " . $e->getMessage() . "<br><br>" .
                          "Detalles: " . json_encode($cart, JSON_PRETTY_PRINT);
            $mail->send();
        } catch (Exception $mailEx) {
            error_log("Error al enviar email de error: " . $mailEx->getMessage());
        }
        
        return false;
    }
}