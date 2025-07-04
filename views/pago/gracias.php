<!DOCTYPE html>
<html>
<head>
    <title>Gracias por tu compra</title>
    <link rel="stylesheet" href="https://reppan.com.ar/public/styles.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="../public/images/logito.png" alt="REPPAN Logo" class="hero-logo">
        </div>
        <h1>¡Gracias por tu compra!</h1>
        
        <?php if ($compra['status'] === 'approved'): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Tu pago ha sido confirmado. Número de orden: #<?= $compra['id'] ?> | Hemos enviado un correo de confirmación a <?= htmlspecialchars($compra['email']) ?>
            </div>
        <?php else: ?>
            <div class="alert-warning">
                <i class="fas fa-info-circle"></i> Estamos procesando tu pago. Te notificaremos cuando sea confirmado.
            </div>
        <?php endif; ?>
        
        <section class="resumen-compra">
            <h3>Resumen de tu compra</h3>
            <div class="productos-grid">
                <?php foreach ($cart as $item): ?>
                    <div class="producto-card">
                        <strong><?= htmlspecialchars($item['nombre'] ?? 'Producto') ?>
                        <?php if (!empty($item['variante']['valor'])): ?>
                            <span class="capacidad-seleccionada">
                                (<?= htmlspecialchars($item['variante']['valor']) ?>)
                            </span>
                        <?php endif; ?>
                        </strong>
                        <div class="producto-info">
                            <span>Cantidad: <?= $item['quantity'] ?? 1 ?></span>
                            <span>$<?= number_format(($item['precio'] ?? 0) * ($item['quantity'] ?? 1), 0) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="total-amount">
                Total: $<?= number_format($compra['total'] ?? 0, 0) ?>
            </div>
            <div class="shipping-notice">
              <i class="fas fa-truck"></i>
              <span>El metodo de envío se acordará con el vendedor.</span>
            </div>
        </section>
        <button type="button" class="btn-pagar" onclick="window.location.href='/'">
            Volver al inicio 
        </button>
    </div>
    <script src="https://reppan.com.ar/public/script.js"></script>
</body>
</html>