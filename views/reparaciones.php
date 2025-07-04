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
          <a href="/maquinaria" class="nav-item">
            <i class="fas fa-tools"></i>
            <span>Maquinaria</span>
          </a>
        </div>

        <div class="nav-section">
          <h4 class="nav-header">Soporte</h4>
          <a href="/reparaciones" class="nav-item active">
            <i class="fas fa-check-circle"></i>
            <span>Reparaciones</span>
          </a>
        </div>
      </div>


      <!-- Área principal de contenido -->
      <div class="main-content">
        <div class="header">
            <h1 class="page-title">Soporte y Reparación</h1>
        </div>

          <!-- Hero Section Técnica -->
          <section class="repair-hero">
              <div class="hero-content1">
                  <div class="hero-text1">
                      <span class="hero-badge">Servicio Técnico Especializado</span>
                      <h1>Reparaciones <span class="text-primary">Profesionales</span></h1>
                      <p class="hero-description">Soluciones técnicas rápidas y efectivas para mantener tu maquinaria en óptimas condiciones</p>
                      <div class="hero-cta">
                          <a href="#simple-contacto" class="btn-primary">Solicitar Diagnóstico</a>
                      </div>
                  </div>
              </div>
          </section>

          <!-- Servicios de Reparación -->
          <section class="repair-services">
              <div class="section-header">
                  <h2>Nuestros <span class="text-primary">Servicios</span></h2>
                  <p>Especialistas en reparación de equipos industriales para panaderías</p>
              </div>
              
              <div class="services-grid">
                  <!-- Servicio 1 -->
                  <div class="service-card">
                      <div class="service-icon">
                          <i class="fas fa-fire"></i>
                      </div>
                      <h3>Reparación de Hornos</h3>
                      <ul class="service-features">
                          <li>Rotativos y convectores</li>
                          <li>Sistema de calor</li>
                          <li>Controladores</li>
                          <li>Todas las marcas</li>
                      </ul>
                  </div>
                  
                  <!-- Servicio 2 -->
                  <div class="service-card">
                      <div class="service-icon">
                          <i class="fas fa-cogs"></i>
                      </div>
                      <h3>Componentes Mecánicos</h3>
                      <ul class="service-features">
                          <li>Quemadores</li>
                          <li>Reductores</li>
                          <li>Transmisiones</li>
                          <li>Rodamientos</li>
                      </ul>
                  </div>
                  
                  <!-- Servicio 3 -->
                  <div class="service-card">
                      <div class="service-icon">
                          <i class="fas fa-bolt"></i>
                      </div>
                      <h3>Sistemas Eléctricos</h3>
                      <ul class="service-features">
                          <li>Tableros de control</li>
                          <li>Circuitos eléctricos</li>
                          <li>Sensores</li>
                          <li>Automatización</li>
                      </ul>
                  </div>
              </div>
          </section>

          <!-- Contacto Simplificado -->
          <section id="simple-contacto" class="simple-contact">
              <div class="contact-cards">
                  <!-- Teléfono -->
                  <div class="contact-card">
                      <div class="contact-icon">
                          <i class="fas fa-phone"></i>
                      </div>
                      <div class="contact-details">
                          <h3>Teléfono de Emergencia</h3>
                          <p>11 2645-1148</p>
                      </div>
                  </div>
                  

                  
                  <!-- Horario -->
                  <div class="contact-card">
                      <div class="contact-icon">
                          <i class="fas fa-clock"></i>
                      </div>
                      <div class="contact-details">
                          <h3>Horario de Atención</h3>
                          <p>24H para emergencias</p>
                      </div>
                  </div>
              </div>
              
              <!-- Botón de WhatsApp -->
              <a href="https://wa.me/5491126451148?text=¡Hola%20REP%20PAN!%20" 
                class="whatsapp-btn" 
                target="_blank">
                  <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
              </a>
          </section>
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
