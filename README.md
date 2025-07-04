# Rep-PAN

**Rep-PAN** es una plataforma web para la venta de repuestos, maquinaria y servicios técnicos para panaderías en Argentina. El sistema permite la gestión de productos, maquinaria con variantes, carrito de compras, integración con MercadoPago, administración y notificaciones automáticas por email.

[Ver en funcionamiento](https://reppan.com.ar)

---

## Características principales

- **Catálogo de productos y maquinaria** con imágenes, categorías y variantes (capacidades).
- **Carrito de compras** con control de stock en tiempo real.
- **Checkout integrado con MercadoPago** (SDK v3).
- **Panel de administración** para gestionar productos, maquinaria, imágenes y capacidades.
- **Notificaciones automáticas por email** usando PHPMailer (para clientes y administradores).
- **Webhooks de MercadoPago** para actualización de pagos.
- **Frontend responsivo** con filtros, búsqueda y navegación moderna.
- **Protección de rutas administrativas** mediante login de usuario.
- **Logs de errores** para debugging y monitoreo.

---

## Tecnologías utilizadas

- **PHP 8.2+**
- [FlightPHP](https://flightphp.com/) (micro-framework)
- [MercadoPago SDK v3](https://github.com/mercadopago/sdk-php)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- **MySQL** (gestión de productos, maquinaria, compras, usuarios)
- **HTML5, CSS3, JavaScript** (frontend)
- **Composer** (gestión de dependencias)

---

## Estructura del proyecto

```
/
├── app/
│   ├── config.php           # Configuración de base de datos y constantes
│   ├── email_sender.php     # Lógica de envío de emails con PHPMailer
│   ├── helpers.php          # Funciones auxiliares
│   └── routes.php           # Definición de rutas y lógica principal
├── public/
│   ├── *.php                # Endpoints públicos y administrativos (API, notificaciones, CRUD)
│   ├── images/              # Imágenes de productos y maquinaria
│   ├── script.js            # Lógica JS del frontend (carrito, filtros, AJAX)
│   └── styles.css           # Estilos principales
├── templates/
│   ├── email_cliente.php    # Plantilla de email para clientes
│   └── email_rep_pan.php    # Plantilla de email para administradores
├── views/
│   ├── *.php                # Vistas del frontend (home, maquinaria, repuestos, reparaciones)
│   └── header.php/footer.php
├── vendor/
│   └── ...                  # Dependencias instaladas por Composer (FlightPHP, MercadoPago, PHPMailer)
├── composer.json            # Dependencias y autoload
├── index.php                # Front controller (entrypoint)
└── README.md
```

## Uso

- **Frontend:** Los usuarios pueden navegar por repuestos y maquinaria, filtrar, buscar, agregar al carrito y comprar.
- **Admin:** Accede a `/public/admin/admin.php` (requiere login) para gestionar productos, maquinaria, imágenes y capacidades.
- **Notificaciones:** El sistema envía emails automáticos a clientes y administradores tras cada compra.
- **Webhooks:** MercadoPago notifica pagos a `/public/notification.php` para actualizar el estado de las compras.

---

## Créditos y licencias

- [FlightPHP](https://github.com/flightphp/core) - MIT License
- [MercadoPago SDK](https://github.com/mercadopago/sdk-php) - MIT License
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - LGPL License

---

## Contacto

- Web: [https://reppan.com.ar](https://reppan.com.ar)

---

¡Gracias por visitar Rep-Pan!