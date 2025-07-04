<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REP PAN - Sitio en Construcción</title>
    <style>
        /* Reset y estilos base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        .construction-container {
            text-align: center;
            padding: 40px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            animation: fadeIn 1s ease-in-out;
        }
        
        .logo {
            width: 180px;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        h1 {
            color: #1a228c;
            margin-bottom: 20px;
            font-size: 2.2rem;
        }
        
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #555;
        }
        
        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .contact-info a {
            color: #1a228c;
            text-decoration: none;
            font-weight: bold;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .construction-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .logo {
                width: 140px;
            }
        }
    </style>
</head>
<body>
    <div class="construction-container">
        <img src="../public/images/logo2311.png" alt="REP PAN Logo" class="logo">
        <h1>Sitio en Construcción</h1>
        <p>Estamos trabajando para brindarte un mejor servicio en reparación , mantenimiento y venta de repuestos del rubro panaderil.</p>
        <p>Disculpá las molestias. Muy pronto estaremos online con nuestro nuevo sitio web.</p>
        
        <div class="contact-info">
            <p>Para consultas urgentes:</p>
            <p><a href="https://wa.me/5491126451148?text=¡Hola%20ARIEL%20REP!%20">📞 11 2645-1148</a></p>
            <p><a href="https://mail.google.com/mail/?view=cm&fs=1&to=rep.pan2020@gmail.com&su=Consulta%20desde%20sitio%20web" 
          target="_blank"
          class="gmail-link">
          ✉ rep.pan2020@gmail.com
       </a>
        </div>
    </div>
    <section style="display: none;">
        <h1>Repuestos y reparación de maquinaria para panaderías</h1>
        <p>En <strong>Rep-PAN</strong> nos especializamos en la <strong>venta de repuestos</strong> y <strong>servicio técnico</strong> para todo tipo de maquinaria de panadería: hornos rotativos, amasadoras, sobadoras, batidoras industriales y más.</p>
        <p>Atendemos panaderías en <strong>zona sur, CABA, y alrededores</strong>. ¡Consultanos sin compromiso!</p>
    </section>
</body>
</html>