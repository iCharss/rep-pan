<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

require_once __DIR__ . '/../templates/email_rep_pan.php';
require_once __DIR__ . '/../templates/email_cliente.php';

require_once __DIR__ . '/../app/config.php';

function sendEmails($compra, $cart) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Configuración SMTP de Hostinger
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // Servidor SMTP de Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = ''; 
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port = 465; 

        // Configuración del email
        $mail->setFrom('no-repply@reppan.com.ar', 'RepPan');
        $mail->addReplyTo('rep.pan2020@gmail.com', 'RepPan'); // Para respuestas
        $mail->isHTML(true);
        
        // Procesar el carrito para incluir capacidades en los nombres
        $processedCart = array_map(function($item) {
            // Si tiene variante, agregar la capacidad al nombre
            if (!empty($item['variante']['valor'])) {
                $item['nombre_completo'] = $item['nombre'] . ' (' . $item['variante']['valor'] . ')';
            } else {
                $item['nombre_completo'] = $item['nombre'];
            }
            return $item;
        }, $cart);

        // Email para RepPan (a tu Gmail)
        $mail->addAddress('rep.pan2020@gmail.com');
        $mail->Subject = 'Nueva Compra - ID: ' . $compra['id'];
        $mail->Body = getEmailRepPanTemplate($compra, $processedCart);
        $mail->send();
        error_log("Email enviado a RepPan para compra ID: " . $compra['id']);

        // Email para el cliente
        $mail->clearAddresses();
        $mail->addAddress($compra['email'], $compra['nombre_cliente']);
        $mail->Subject = 'Rep Pan | Compra #' . $compra['id'];
        $mail->Body = getEmailClienteTemplate($compra, $processedCart);
        $mail->send();
        error_log("Email enviado al cliente para compra ID: " . $compra['id']);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar emails: " . $e->getMessage());
        return false;
    }
    error_log("Último error de PHPMailer: " . $mail->ErrorInfo);
}
