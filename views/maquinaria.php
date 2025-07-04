<?php
// Incluye el header
include 'header.php';
?>

<div class="dashboard">
    <!-- Barra lateral / Categorías -->
    <div class="sidebar">
        <div class="logo">
          <img
            src="../public/images/logo231.png"
            class="logo-icon"
            alt="Logo"
          />
        </div>

        <div class="nav-section">
          <h4 class="nav-header">Menú</h4>
          <a href="/" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
          </a>
          <a href="/repuestos" class="nav-item">
            <i class="fas fa-cogs"></i>
            <span>Repuestos</span>
          </a>
          <a href="/maquinaria" class="nav-item active">
            <i class="fas fa-tools"></i>
            <span>Maquinaria</span>
          </a>
        </div>

        <div class="nav-section">
          <h4 class="nav-header">Soporte</h4>
          <a href="/reparaciones" class="nav-item">
            <i class="fas fa-check-circle"></i>
            <span>Reparaciones</span>
          </a>
        </div>
      </div>


      <!-- Área principal de contenido -->
      <div class="main-content">
        <div class="header">
          <h1 class="page-title">Maquinaria pesada</h1>
        </div>
        
        <div class="categories-filters">
          <div class="filter-chip active">Todas las maquinas</div>
        </div>

        <!-- Lista de repuestos -->
        <div class="menu-items" id="maquinaria-container">
          
        </div>
      </div>

      <!-- Cart Section -->
      <div class="cart-section" id="cart-section">
          <div class="cart-title">
            <span>Carrito</span>
            <button id="cart-toggle" class="cart-icon">
              <i class="fas fa-shopping-cart"></i><span id="cart-count"></span>
            </button>
          </div>
        
          <!-- Contenedor para items del carrito -->
          <div class="cart-items-container">
            <!-- Mensaje de carrito vacío (fuera de cart-items) -->
            <div class="empty-cart">
              <div class="empty-cart-icon">
                <i class="fas fa-shopping-bag"></i>
              </div>
              <p>Tu carrito esta vacio</p>
              <p>Agrega algun repuesto para empezar</p>
            </div>
        
            <!-- Items del carrito (este sí se llena dinámicamente) -->
            <div class="cart-items"></div>
          </div>
        
          <div class="cart-total">
            <div class="total-row grand-total">
              <span class="total-title">Total</span>
              <span class="total-value" id="total">$0.00</span>
            </div>
          </div>
        
          <form action="/compra" method="POST" id="checkout-form">
            <input type="hidden" name="cart_data" id="cart-data">
            <button type="submit" class="checkout-btn">
              <i class="fas fa-credit-card"></i>
              Proceder al pago
            </button>
          </form>
        </div>
</div>
<div class="whatsapp-section">
    <div class="whatsapp-card">
        <div class="whatsapp-content">
            <a href="https://wa.me/5491126451148?text=¡Hola%20REP%20PAN!%20" class="whatsapp-button" target="_blank"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
</div>
<?php
// Incluye el footer
include 'footer.php';
?>
