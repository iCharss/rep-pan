<!DOCTYPE html>
<html>
<head>
    <title>Gracias por tu compra</title>
    <link rel="stylesheet" href="https://reppan.com.ar/public/styles.css">
</head>
<body>
    
    <div class="error-container">
        <div class="logo-container">
            <img src="../public/images/logito.png" alt="REPPAN Logo" class="hero-logo">
        </div>
        <h1>Error en el pago</h1>
        <br>
        
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>No se pudo procesar tu pago</strong>
                <p>Por favor, intenta nuevamente o contacta con nosotros si el problema persiste.</p>
            </div>
        </div>
        
        <?php if (isset($compra)): ?>
        <section class="resumen-compra">
            <h3>Resumen de tu compra</h3>
            <div class="productos-grid">
                <?php foreach ($cart as $item): ?>
                    <div class="producto-card">
                        <strong><?= htmlspecialchars($item['nombre'] ?? 'Producto') ?></strong>
                        <div class="producto-info">
                            <span>Cantidad: <?= $item['quantity'] ?? 1 ?></span>
                            <span>$<?= number_format(($item['precio'] ?? 0) * ($item['quantity'] ?? 1), 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="total-amount">
                Total: $<?= number_format($compra['total'] ?? 0, 2) ?>
            </div>
        </section>
        <?php endif; ?>
        
        <button type="button" class="btn-reintentar" onclick="window.location.href='/compra'">
            <i class="fas fa-redo"></i> Intentar nuevamente
        </button>
        
        <button type="button" class="btn-reintentar" onclick="window.location.href='/'" style="background-color: #6c757d; margin-left: 10px;">
            <i class="fas fa-home"></i> Volver al inicio
        </button>
    </div>
    
<?php include __DIR__ . '/../../views/footer.php';?>

</body>
</html>