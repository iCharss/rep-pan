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
          <a href="/" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
          </a>
          <a href="/repuestos" class="nav-item">
            <i class="fas fa-cogs"></i>
            <span>Repuestos</span>
          </a>
          <a href="/maquinaria" class="nav-item">
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

        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h2>BAJO COSTO MENSUAL</h2>
                    <img src="../public/images/logito.png" alt="REPPAN Logo" class="hero-logo">
                    <p class="tagline">MANTENIMIENTO PREVENTIVO MENSUAL</p>
                    <p class="highlight">EVITE ROTURAS INESPERADAS</p>
                </div>

            </div>
        </section>

        <section class="services-section">
            <h2>EQUIPOS QUE REPARAMOS</h2>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-fire"></i>
                    <h3>HORNOS</h3>
                    <p>Rotativos y convectores</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-blender"></i>
                    <h3>AMASADORAS</h3>
                    <p>Batidoras industriales</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-utensils"></i>
                    <h3>SOBADORAS</h3>
                    <p>Trinchadoras</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-snowflake"></i>
                    <h3>CÁMARAS DE FRÍO</h3>
                    <p>Mantenimiento integral</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-bolt"></i>
                    <h3>QUEMADORES</h3>
                    <p>Tableros de potencia</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-phone"></i>
                    <h3>LÍNEAS CONTINUAS</h3>
                    <p>Mantenimiento especializado</p>
                </div>
            </div>
        </section>

        <section class="benefits-section">
            <div class="benefits-content">
                <div class="benefits-text">
                    <h2>CONTAMOS CON REPUESTOS DE TODAS LAS MARCAS</h2>
                    <ul class="benefits-list">
                        <li style="color: red;"><i class="fas fa-check"></i> Servicio de guardia las 24hs</li>
                        <li><i class="fas fa-check"></i> Paños y lonas especializadas</li>
                        <li><i class="fas fa-check"></i> Amplia financiación en restauraciones</li>
                    </ul>
                </div>
                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>CONTACTO</h3>
                    <p><i class="fas fa-phone"></i> 11 2645-1148</p>
                    <a href="https://wa.me/5491126451148?text=¡Hola%20ARIEL%20REP!%20" 
                       class="contact-btn" 
                       target="_blank">
                      Solicitar Presupuesto
                    </a>
                </div>
            </div>
        </section>
        <section style="display: none;">
            <h1>Repuestos y reparación de maquinaria para panaderías</h1>
            <p>En <strong>Rep-PAN</strong> nos especializamos en la <strong>venta de repuestos</strong> y <strong>servicio técnico</strong> para todo tipo de maquinaria de panadería: hornos rotativos, amasadoras, sobadoras, batidoras industriales y más.</p>
            <p>Atendemos panaderías en <strong>zona sur, CABA, y alrededores</strong>. ¡Consultanos sin compromiso!</p>
        </section>
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