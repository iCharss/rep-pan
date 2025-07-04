<?php
/**
 * Template para el email a RepPan
 */
function getEmailRepPanTemplate($compra, $cart) {
    // Preparamos el enlace de WhatsApp con el mensaje predefinido
    $telefono = $compra['telefono'];
    $telefono_whatsapp = preg_replace('/[^0-9]/', '', $telefono);
    if (strlen($telefono_whatsapp) == 10) { // Si tiene 10 dígitos (11 sin el 9)
        $telefono_whatsapp = '549' . $telefono_whatsapp; // Agrega código de Argentina
    }
    $nombre = $compra['nombre_cliente'];
    $mensaje_whatsapp = rawurlencode("Hola, ".$nombre." te hablamos de RepPan, por tu compra nro #".$compra['id']." para coordinar la entrega.");
    $whatsapp_link = "https://wa.me/".$telefono_whatsapp."?text=".$mensaje_whatsapp;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Nueva Compra - RepPan</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5;">
        <!-- Contenedor principal -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f5f5;">
            <tr>
                <td align="center" valign="top">
                    <!-- Contenedor del email (600px máximo) -->
                    <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; margin: 20px auto;">
                        <!-- Encabezado -->
                        <!-- Logo -->
                        <tr>
                            <td align="center" style="padding: 20px 0;">
                                <img src="https://www.reppan.com.ar/public/images/logito.png" alt="REPPAN Logo" style="max-width: 200px; height: auto;">
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="background-color: #f8f9fa; padding: 20px;">
                                <h2 style="color: #333333; margin: 0;">¡Nueva Compra Recibida!</h2>
                                <p style="color: #666666; margin: 10px 0 0;">ID de Compra: '.$compra['id'].'</p>
                            </td>
                        </tr>
                        
                        <!-- Contenido -->
                        <tr>
                            <td style="padding: 20px;">
                                <!-- Datos del cliente -->
                                <h3 style="color: #333333; margin-top: 0; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">Datos del Cliente:</h3>
                                <table width="100%" cellpadding="5" cellspacing="0" border="0">
                                    <tr>
                                        <td width="30%" style="color: #666666;"><strong>Nombre:</strong></td>
                                        <td style="color: #333333;">'.$compra['nombre_cliente'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666;"><strong>Email:</strong></td>
                                        <td style="color: #333333;">'.$compra['email'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666;"><strong>Teléfono:</strong></td>
                                        <td style="color: #333333;">
                                            <a href="'.$whatsapp_link.'" style="color: black; text-decoration: none; font-weight: bold;">
                                                '.$telefono.' <span style="font-size: 12px;">(Click para contactar por WhatsApp)</span>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666;"><strong>Dirección:</strong></td>
                                        <td style="color: #333333;">'.$compra['direccion'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666;"><strong>Código Postal:</strong></td>
                                        <td style="color: #333333;">'.$compra['codigo_postal'].'</td>
                                    </tr>
                                </table>
                                
                                <!-- Detalles de la compra -->
                                <h3 style="color: #333333; margin-top: 20px; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">Detalles de la Compra:</h3>
                                
                                <!-- Tabla de productos -->
                                <table width="100%" cellpadding="8" cellspacing="0" border="0" style="border-collapse: collapse; margin-bottom: 20px;">
                                    <thead>
                                        <tr style="background-color: #f2f2f2;">
                                            <th style="text-align: left; padding: 8px; border: 1px solid #dddddd;">Producto</th>
                                            <th style="text-align: left; padding: 8px; border: 1px solid #dddddd;">Cantidad</th>
                                            <th style="text-align: left; padding: 8px; border: 1px solid #dddddd;">Precio Unitario</th>
                                            <th style="text-align: left; padding: 8px; border: 1px solid #dddddd;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    
                                    foreach ($cart as $item) {
                                        $html .= '
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dddddd;">'.($item['nombre_completo'] ?? $item['nombre']).'</td>
                                            <td style="padding: 8px; border: 1px solid #dddddd;">'.$item['quantity'].'</td>
                                            <td style="padding: 8px; border: 1px solid #dddddd;">$'.number_format($item['precio'], 0).'</td>
                                            <td style="padding: 8px; border: 1px solid #dddddd;">$'.number_format($item['precio'] * $item['quantity'], 0).'</td>
                                        </tr>';
                                    }
                                    
                                    $html .= '
                                        <tr style="font-weight: bold;">
                                            <td colspan="3" style="padding: 8px; border: 1px solid #dddddd;">Total</td>
                                            <td style="padding: 8px; border: 1px solid #dddddd;">$'.number_format($compra['total'], 0).'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Pie de página -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 15px; text-align: center;">
                                <p style="color: #666666; margin: 0; font-size: 12px;">© '.date('Y').' RepPan. Todos los derechos reservados.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return $html;
}