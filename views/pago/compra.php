<?php
include __DIR__ . '/../../views/header.php';

// Función para formatear precios al estilo argentino
function formatPricePHP($price) {
    return number_format($price, 0, ',', '.');
}

// Validación mejorada del email
function validarEmail($email) {
    $dominiosPermitidos = [
        'gmail.com', 'hotmail.com', 'hotmail.com.ar', 'outlook.com', 
        'yahoo.com', 'yahoo.com.ar', 'yahoo.es', 'icloud.com',
        'live.com', 'live.com.ar', 'aol.com', 'protonmail.com'
    ];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $partes = explode('@', $email);
    $dominio = strtolower($partes[1] ?? '');
    
    return in_array($dominio, $dominiosPermitidos);
}

// Validación más robusta de los datos del carrito
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cart_data'])) {
    header("Location: https://www.reppan.com.ar");
    exit();
}

// Decodificar y validar el carrito
$cart = json_decode($_POST['cart_data'], true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($cart) || empty($cart)) {
    header("Location: https://www.reppan.com.ar");
    exit();
}

// Calcular total con validación
$total = 0;
foreach ($cart as $item) {
    if (!isset($item['precio'], $item['quantity']) || !is_numeric($item['precio']) || !is_numeric($item['quantity'])) {
        header("Location: https://www.reppan.com.ar");
        exit();
    }
    $total += $item['precio'] * $item['quantity'];
}
?>
<div class="container">
    <div class="logo-container">
        <img src="../public/images/logito.png" alt="REPPAN Logo" class="hero-logo">
    </div>
    <h2>Finalizar Compra</h2>
    
    <div class="checkout-layout">
        <section class="resumen-compra">
            <h3>Resumen de tu compra</h3>
            <div class="productos-grid">
                <?php foreach ($cart as $item): ?>
                    <div class="producto-card">
                        <strong><?= htmlspecialchars($item['nombre'] ?? 'Producto sin nombre') ?>
                        <?php if (!empty($item['variante']['valor'])): ?>
                            <span class="capacidad-seleccionada">
                                (<?= htmlspecialchars($item['variante']['valor']) ?>)
                            </span>
                        <?php endif; ?>
                </strong>
                        <div class="producto-info">
                            <span>Cantidad: <?= htmlspecialchars($item['quantity'] ?? 1) ?></span>
                            <span>$<?= formatPricePHP(($item['precio'] ?? 0) * ($item['quantity'] ?? 1)) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="total-amount">Total: $<?= formatPricePHP($total) ?></div>
            <div class="shipping-notice">
              <i class="fas fa-truck"></i>
              <span>El metodo de envío se acordará con el vendedor.</span>
            </div>
        </section>
        
        <section class="datos-cliente">
            <h3>Datos de facturación</h3>
            <form id="payment-form" onsubmit="return false;">
                <div class="form-group">
                    <label for="nombre">Nombre Completo*</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                        required minlength="5" maxlength="20"
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{5,}">
                </div>
    
                <div class="form-group">
                    <label for="direccion">Dirección*</label>
                    <input type="text" name="direccion" id="direccion" class="form-control"
                        required minlength="5" maxlength="30"
                        pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]{5,}">
                </div>
    
                <div class="form-group">
                    <label for="codigo_postal">Código Postal*</label>
                    <input type="text" name="codigo_postal" id="codigo_postal" class="form-control" 
                           pattern="\d{4,5}" maxlength="5" required>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono*</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control"
                           pattern="\d{10}" maxlength="10" required>
                </div>
    
                <div class="form-group">
                    <label for="email">Correo Electrónico*</label>
                    <input type="email" name="email" id="email" class="form-control"
                        required 
                        pattern="[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.(com|com\.ar)|outlook\.(com|es)|yahoo\.(com|com\.ar|es)|icloud\.com|live\.(com|com\.ar)|aol\.com|protonmail\.com)"
                        title="Debe ser un email válido (Gmail, Hotmail, Yahoo, etc.)">
                </div>
                
                <!-- Campos ocultos -->
                <input type="hidden" name="total" value="<?= htmlspecialchars($total) ?>">
                <input type="hidden" name="metodo_pago" value="mercado_pago">
                <input type="hidden" name="cart_data" value="<?= htmlspecialchars(json_encode($cart)) ?>">
                
                
                <button type="button" class="btn-pagar" id="verify-btn">
                    Verificar datos
                </button>
                
                <div id="payment-container" class="mt-3 mx-auto" style="display:none;"></div>
                
                <button type="button" class="btn-cancelar" onclick="window.location.href='/'">
                     Cancelar 
                </button>
    
                <div class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Verificando datos...
                </div>
            </form>
        </section>
    
    </div>
</div>

<script>
// Cambia el JavaScript por esto:
const mp = new MercadoPago('APP_USR-273862d5-0d9f-4723-bb61-a77eaad14e80', {
    locale: 'es-AR'
});

let paymentInitialized = false;

document.getElementById('verify-btn').addEventListener('click', async function() {
    const form = document.getElementById('payment-form');
    // 1. Validación manual del email
    const email = document.getElementById('email').value;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.(com|com\.ar)|outlook\.(com|es)|yahoo\.(com|com\.ar|es)|icloud\.com|live\.(com|com\.ar)|aol\.com|protonmail\.com)$/;
    
    if (!emailRegex.test(email)) {
        showNotification('Por favor ingrese un email válido (Gmail, Hotmail, Yahoo, etc.)', 'error');
        return false; // Detiene la ejecución
    }
    
    // 2. Validación del resto del formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    const btn = this;
    const loading = document.querySelector('.loading');
    
    btn.disabled = true;
    loading.style.display = 'block';
    
    try {
        const formData = new FormData(form);
        const response = await fetch('/procesar_pago', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!data.success || !data.id) {
            throw new Error(data.error || 'No se pudo obtener la preferencia');
        }
        
        btn.style.display = 'none';
        document.getElementById('payment-container').style.display = 'block';
        
        if (!paymentInitialized) {
            await initMercadoPago(data.id);
            paymentInitialized = true;
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
        return false; // Detiene la ejecución
    } finally {
        loading.style.display = 'none';
    }
});


async function initMercadoPago(preferenceId) {
    const bricksBuilder = mp.bricks();
    await bricksBuilder.create('wallet', 'payment-container', {
        initialization: {
            preferenceId: preferenceId,
        },
        callbacks: {
            onReady: () => console.log('Widget listo'),
            onError: (error) => {
                console.error('Error en el pago:', error);
                alert(`Error: ${error.message}`);
            }
        }
    });
}

// Manejar el mensaje de confirmación desde MercadoPago
window.addEventListener('message', function(event) {
    if (event.origin !== "https://www.reppan.com.ar") return;
    
    if (event.data.action === "paymentCompleted") {
        const compra_id = event.data.compra_id;
        
        if (event.data.status === 'approved') {
            window.location.href = '/pago/gracias?id=' + compra_id;
        } else {
            window.location.href = '/pago/error?id=' + compra_id;
        }
    }
});
</script>
<?php
include __DIR__ . '/../../views/footer.php';
?>