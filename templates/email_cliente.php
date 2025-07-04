<?php
/**
 * Template para el email al cliente
 */
function getEmailClienteTemplate($compra, $cart) {
    $whatsapp_link = "https://wa.me/5491126451148?text=Hola%20RepPan,%20consulto%20por%20mi%20compra%20#".$compra['id']."";
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Compra - RepPan</title>
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
                        <!-- Logo -->
                        <tr>
                            <td align="center" style="padding: 20px 0;">
                                <img src="https://www.reppan.com.ar/public/images/logito.png" alt="REPPAN Logo" style="max-width: 200px; height: auto;">
                            </td>
                        </tr>
                        
                        <!-- Encabezado -->
                        <tr>
                            <td align="center" style="background-color: #f8f9fa; padding: 20px;">
                                <h2 style="color: #333333; margin: 0;">¡Gracias por tu compra en RepPan!</h2>
                                <p style="color: #666666; margin: 10px 0 0;">Nro de compra #'.$compra['id'].'</p>
                            </td>
                        </tr>
                        
                        <!-- Contenido -->
                        <tr>
                            <td style="padding: 20px;">
                                <h3 style="color: #333333; margin-top: 0;">Resumen de tu pedido:</h3>
                                
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
                                
                                <p style="color: #666666; margin-bottom: 20px;">Nos pondremos en contacto contigo pronto para coordinar la entrega.</p>
                                
                                <!-- Botón WhatsApp -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center">
                                            <a href="'.$whatsapp_link.'" style="display: inline-block; background-color: #25D366; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-bottom: 20px;">Contactar por WhatsApp sobre mi compra</a>
                                        </td>
                                    </tr>
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